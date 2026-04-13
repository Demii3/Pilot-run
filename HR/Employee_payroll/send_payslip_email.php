<?php
ob_start();
header('Content-Type: application/json');

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

function respond($success, $data = null, $message = '', $statusCode = 200) {
    http_response_code($statusCode);
    if (ob_get_length()) {
        ob_end_clean();
    }

    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ]);
    exit;
}

function currency_format($value) {
    return 'PHP ' . number_format((float)$value, 2);
}

function queue_email_locally($email, $subject, $message) {
    $outboxDir = __DIR__ . DIRECTORY_SEPARATOR . 'email_outbox';
    if (!is_dir($outboxDir) && !mkdir($outboxDir, 0777, true) && !is_dir($outboxDir)) {
        return false;
    }

    $safeEmail = preg_replace('/[^a-zA-Z0-9_\-.@]/', '_', $email);
    $fileName = date('Ymd_His') . '_' . $safeEmail . '.txt';
    $filePath = $outboxDir . DIRECTORY_SEPARATOR . $fileName;

    $content = "To: {$email}\r\n";
    $content .= "Subject: {$subject}\r\n";
    $content .= "Generated At: " . date('Y-m-d H:i:s') . "\r\n\r\n";
    $content .= $message;

    return file_put_contents($filePath, $content) !== false;
}

function get_email_config() {
    $default = [
        'transport' => 'smtp',
        'from_email' => '',
        'from_name' => 'Payroll System',
        'smtp_host' => '',
        'smtp_port' => 465,
        'smtp_encryption' => 'ssl',
        'smtp_username' => '',
        'smtp_password' => ''
    ];

    $configPath = __DIR__ . DIRECTORY_SEPARATOR . 'email_config.php';
    if (!file_exists($configPath)) {
        return $default;
    }

    $loaded = include $configPath;
    if (!is_array($loaded)) {
        return $default;
    }

    return array_merge($default, $loaded);
}

function smtp_read_response($socket) {
    $response = '';
    while (!feof($socket)) {
        $line = fgets($socket, 515);
        if ($line === false) {
            break;
        }
        $response .= $line;
        if (strlen($line) < 4) {
            break;
        }
        // SMTP multiline response ends when 4th char is a space.
        if ($line[3] === ' ') {
            break;
        }
    }
    return $response;
}

function smtp_expect($socket, $expectedCode) {
    $response = smtp_read_response($socket);
    if (substr($response, 0, 3) !== (string)$expectedCode) {
        throw new RuntimeException('SMTP expected ' . $expectedCode . ' but got: ' . trim($response));
    }
}

function smtp_send_command($socket, $command, $expectedCode) {
    fwrite($socket, $command . "\r\n");
    smtp_expect($socket, $expectedCode);
}

function send_via_smtp($toEmail, $subject, $body, $config) {
    $host = trim((string)$config['smtp_host']);
    $port = (int)$config['smtp_port'];
    $encryption = strtolower(trim((string)$config['smtp_encryption']));
    $username = trim((string)$config['smtp_username']);
    $password = (string)$config['smtp_password'];
    $fromEmail = trim((string)$config['from_email']);
    $fromName = trim((string)$config['from_name']);

    if ($host === '' || $username === '' || $password === '' || $fromEmail === '') {
        throw new RuntimeException('SMTP is not fully configured.');
    }

    $remoteHost = ($encryption === 'ssl' ? 'ssl://' : '') . $host;
    $socket = @stream_socket_client($remoteHost . ':' . $port, $errno, $errstr, 20);
    if (!$socket) {
        throw new RuntimeException('SMTP connection failed: ' . $errstr . ' (' . $errno . ')');
    }

    stream_set_timeout($socket, 20);

    try {
        smtp_expect($socket, 220);
        smtp_send_command($socket, 'EHLO localhost', 250);

        if ($encryption === 'tls') {
            smtp_send_command($socket, 'STARTTLS', 220);
            $tlsEnabled = @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if (!$tlsEnabled) {
                throw new RuntimeException('Unable to establish TLS on SMTP connection.');
            }
            smtp_send_command($socket, 'EHLO localhost', 250);
        }

        smtp_send_command($socket, 'AUTH LOGIN', 334);
        smtp_send_command($socket, base64_encode($username), 334);
        smtp_send_command($socket, base64_encode($password), 235);

        smtp_send_command($socket, 'MAIL FROM:<' . $fromEmail . '>', 250);
        smtp_send_command($socket, 'RCPT TO:<' . $toEmail . '>', 250);
        smtp_send_command($socket, 'DATA', 354);

        $headers = [
            'From: ' . $fromName . ' <' . $fromEmail . '>',
            'To: <' . $toEmail . '>',
            'Subject: ' . $subject,
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit'
        ];

        $data = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.";
        fwrite($socket, $data . "\r\n");
        smtp_expect($socket, 250);

        smtp_send_command($socket, 'QUIT', 221);
    } finally {
        fclose($socket);
    }

    return true;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        respond(false, null, 'Method not allowed.', 405);
    }

    $payload = json_decode(file_get_contents('php://input'), true);
    if (!is_array($payload)) {
        respond(false, null, 'Invalid request payload.', 400);
    }

    $employeeName = trim((string)($payload['name'] ?? ''));
    $email = trim((string)($payload['email'] ?? ''));

    if ($employeeName === '' || $email === '') {
        respond(false, null, 'Employee name and email are required.', 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond(false, null, 'Invalid email address.', 400);
    }

    $subject = 'Payslip Breakdown - ' . $employeeName;

    $lines = [
        'Payslip Breakdown',
        '-----------------------------',
        'Employee ID: ' . (string)($payload['id'] ?? ''),
        'Employee: ' . $employeeName,
        'Email: ' . $email,
        '',
        'Gross Pay Per Month: ' . currency_format($payload['grossPayPerMonth'] ?? 0),
        'Gross Pay Per Day: ' . currency_format($payload['grossPayPerDay'] ?? 0),
        'Regular Days: ' . number_format((float)($payload['regularDays'] ?? 0), 2),
        'Total OT: ' . currency_format($payload['totalOt'] ?? 0),
        'Legal Holiday: ' . currency_format($payload['legalHoliday'] ?? 0),
        'Special Holiday: ' . currency_format($payload['specialHoliday'] ?? 0),
        'Rice Subsidy: ' . currency_format($payload['riceSubsidy'] ?? 0),
        'Electricity: ' . currency_format($payload['electricity'] ?? 0),
        'SSS: ' . currency_format($payload['sss'] ?? 0),
        'PHLTH: ' . currency_format($payload['phlth'] ?? 0),
        'PAGIBIG: ' . currency_format($payload['pagibig'] ?? 0),
        'TAX: ' . currency_format($payload['tax'] ?? 0),
        'Personal CA: ' . currency_format($payload['personalCa'] ?? 0),
        'Total Deduction: ' . currency_format($payload['totalDeduction'] ?? 0),
        'Net Pay: ' . currency_format($payload['netPay'] ?? 0)
    ];

    $message = implode("\r\n", $lines);

    $config = get_email_config();
    $sent = false;
    $mailError = null;

    try {
        if (strtolower((string)$config['transport']) === 'smtp') {
            $sent = send_via_smtp($email, $subject, $message, $config);
        } else {
            $headers = [
                'MIME-Version: 1.0',
                'Content-Type: text/plain; charset=UTF-8',
                'From: Payroll System <no-reply@localhost>'
            ];
            $sent = @mail($email, $subject, $message, implode("\r\n", $headers));
        }
    } catch (Throwable $mailThrowable) {
        $mailError = $mailThrowable->getMessage();
        $sent = false;
    }

    if ($sent) {
        respond(true, null, 'Payslip breakdown sent successfully to ' . $email . '.');
    }

    $queued = queue_email_locally($email, $subject, $message);
    if ($queued) {
        $messageText = 'Email transport not available, but the payslip was queued locally in HR/Employee_payroll/email_outbox for ' . $email . '.';
        if ($mailError) {
            $messageText .= ' Reason: ' . $mailError;
        }
        respond(true, null, $messageText);
    }

    $fallbackMessage = 'Email sending failed and local queue could not be written.';
    if ($mailError) {
        $fallbackMessage .= ' Mail error: ' . $mailError;
    }
    respond(false, null, $fallbackMessage, 500);
} catch (Throwable $e) {
    respond(false, null, 'Server error: ' . $e->getMessage(), 500);
}
