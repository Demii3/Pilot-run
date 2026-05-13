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

function payload_number(array $payload, ...$keys) {
    foreach ($keys as $key) {
        if (array_key_exists($key, $payload) && $payload[$key] !== null && $payload[$key] !== '') {
            return (float)$payload[$key];
        }
    }

    return 0.0;
}

function file_to_base64($absolutePath) {
    if (!is_file($absolutePath)) {
        return '';
    }

    $contents = file_get_contents($absolutePath);
    if ($contents === false || $contents === '') {
        return '';
    }

    return base64_encode($contents);
}

function get_mime_type($absolutePath) {
    $mimeType = function_exists('mime_content_type') ? mime_content_type($absolutePath) : 'application/octet-stream';
    return $mimeType ?: 'application/octet-stream';
}

function chunk_base64($base64String) {
    return trim(chunk_split($base64String, 76, "\r\n"));
}

function build_related_email($fromName, $fromEmail, $toEmail, $subject, $htmlMessage, $attachments) {
    $boundary = '=_RELATED_' . md5(uniqid((string)mt_rand(), true));

    $headers = [
        'From: ' . $fromName . ' <' . $fromEmail . '>',
        'To: <' . $toEmail . '>',
        'Subject: ' . $subject,
        'MIME-Version: 1.0',
        'Content-Type: multipart/related; boundary="' . $boundary . '"'
    ];

    $body = '';
    $body .= '--' . $boundary . "\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $body .= $htmlMessage . "\r\n";

    foreach ($attachments as $attachment) {
        if (empty($attachment['path']) || !is_file($attachment['path'])) {
            continue;
        }

        $encoded = file_to_base64($attachment['path']);
        if ($encoded === '') {
            continue;
        }

        $cid = trim((string)($attachment['cid'] ?? ''));
        if ($cid === '') {
            continue;
        }

        $filename = basename($attachment['path']);
        $mimeType = get_mime_type($attachment['path']);

        $body .= '--' . $boundary . "\r\n";
        $body .= 'Content-Type: ' . $mimeType . '; name="' . $filename . '"' . "\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n";
        $body .= 'Content-ID: <' . $cid . '>' . "\r\n";
        $body .= 'Content-Disposition: inline; filename="' . $filename . '"' . "\r\n\r\n";
        $body .= chunk_base64($encoded) . "\r\n";
    }

    $body .= '--' . $boundary . "--\r\n";

    return [
        'headers' => $headers,
        'body' => $body
    ];
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

function send_via_smtp($toEmail, $subject, $headers, $body, $config) {
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

    $logoPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Employee_attendance' . DIRECTORY_SEPARATOR . 'Images' . DIRECTORY_SEPARATOR . 'logo.jpg';
    $backgroundPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Employee_attendance' . DIRECTORY_SEPARATOR . 'Images' . DIRECTORY_SEPARATOR . 'bgimg.jpg';

    $cutoffSalary = payload_number($payload, 'cutoffSalary', 'grossPayPerMonth', 'salary');
    $grossPayPerDay = payload_number($payload, 'grossPayPerDay');
    $hoursWorked = payload_number($payload, 'hoursWorked', 'regularDays');
    $totalOt = payload_number($payload, 'totalOt', 'totalOtPay');
    $legalHoliday = payload_number($payload, 'legalHoliday');
    $specialHoliday = payload_number($payload, 'specialHoliday');
    $taxableAdditionalIncome = payload_number($payload, 'taxableAdditionalIncome', 'electricity');
    $nonTaxableAdditionalIncome = payload_number($payload, 'nonTaxableAdditionalIncome', 'riceSubsidy');
    $sss = payload_number($payload, 'sss', 'sssContribution');
    $phlth = payload_number($payload, 'phlth', 'philhealthContribution');
    $pagibig = payload_number($payload, 'pagibig', 'pagibigContribution');
    $tax = payload_number($payload, 'tax', 'withholdingTax');
    $personalCa = payload_number($payload, 'personalCa', 'additionalDeductions');
    $totalDeduction = payload_number($payload, 'totalDeduction');
    $netPay = payload_number($payload, 'netPay');

    // Additional fields for clearer breakdown (may be present in payload)
    $basicPay = payload_number($payload, 'basicPay', 'monthlySalary', 'salary');
    $taxableAllowance = payload_number($payload, 'taxableAllowance');
    $nonTaxableAllowance = payload_number($payload, 'nonTaxableAllowance');
    $nightDifferential = payload_number($payload, 'nightDifferential', 'nightDiff');
    $overtimePay = payload_number($payload, 'overtimePay', 'totalOt');
    $grossPay = payload_number($payload, 'grossPay', 'grossPayTotal', 'cutoffSalary');

    // Plain-text fallback for email clients that do not render HTML
    $plainLines = [
        'Payslip Breakdown',
        '-----------------------------',
        'Employee ID: ' . (string)($payload['id'] ?? ''),
        'Employee: ' . $employeeName,
        'Email: ' . $email,
        '',
        'Cutoff Salary: ' . currency_format($cutoffSalary),
        'Gross Pay Per Day: ' . currency_format($grossPayPerDay),
        'Hours Worked: ' . number_format($hoursWorked, 2),
        'Total OT: ' . currency_format($totalOt),
        'Legal Holiday: ' . currency_format($legalHoliday),
        'Special Holiday: ' . currency_format($specialHoliday),
        'Taxable Additional Income: ' . currency_format($taxableAdditionalIncome),
        'Non-Taxable Additional Income: ' . currency_format($nonTaxableAdditionalIncome),
        'SSS: ' . currency_format($sss),
        'PHLTH: ' . currency_format($phlth),
        'PAGIBIG: ' . currency_format($pagibig),
        'TAX: ' . currency_format($tax),
        'Personal CA: ' . currency_format($personalCa),
        'Total Deduction: ' . currency_format($totalDeduction),
        'Net Pay: ' . currency_format($netPay)
    ];

    $message = implode("\r\n", $plainLines);

        $heroStyle = 'background: linear-gradient(135deg, #304d6d 0%, #1f6fa6 52%, #2c8ecb 100%);';

        $logoMarkup = '<img src="cid:payroll-logo" alt="Chengshi Construction Corp" width="46" height="46" style="display:block;border-radius:14px;background:#ffffff;padding:6px;box-shadow:0 8px 18px rgba(0,0,0,0.20);">';
        $backgroundMarkup = '<img src="cid:payroll-banner" alt="Construction banner" style="display:block;width:100%;max-width:100%;height:auto;border:0;border-radius:0;">';

        // HTML email design (inline CSS for compatibility)
        $htmlMessage = '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
                . '<style>
                    body { margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; background: #6f86a2; color: #23374d; }
                    .shell { width: 100%; background: #6f86a2; padding: 24px 12px 32px; }
                    .container { max-width: 720px; margin: 0 auto; border-radius: 22px; overflow: hidden; box-shadow: 0 20px 45px rgba(16, 35, 56, 0.28); background: #eef4f9; }
                    .hero { ' . $heroStyle . ' color: #ffffff; padding: 24px 26px 28px; }
                    .hero-row { width: 100%; border-collapse: collapse; }
                    .hero-title { margin: 0; font-size: 14px; letter-spacing: 0.04em; text-transform: uppercase; opacity: 0.9; }
                    .hero-company { margin: 4px 0 0; font-size: 22px; line-height: 1.1; font-weight: 700; }
                    .hero-pill { display: inline-block; margin-top: 14px; padding: 6px 12px; border-radius: 999px; background: rgba(255,255,255,0.16); font-size: 12px; letter-spacing: 0.02em; }
                    .content { padding: 22px 24px 10px; background: rgba(255,255,255,0.92); }
                    .employee-card { border-radius: 18px; background: linear-gradient(180deg, #ffffff, #f2f7fb); border: 1px solid #d9e5ef; padding: 18px 18px 14px; margin-bottom: 18px; }
                    .employee-card table { width: 100%; border-collapse: collapse; }
                    .employee-card td { vertical-align: top; }
                    .meta-label { color: #5f7288; font-size: 12px; text-transform: uppercase; letter-spacing: 0.04em; display: block; margin-bottom: 4px; }
                    .meta-value { font-size: 14px; color: #223445; font-weight: 700; }
                    table.payslip { width: 100%; border-collapse: collapse; border-radius: 16px; overflow: hidden; }
                    table.payslip th, table.payslip td { text-align: left; padding: 10px 12px; border-bottom: 1px solid #e6edf3; }
                    table.payslip th { background: #edf4f9; color: #33485d; width: 62%; font-weight: 700; }
                    table.payslip td { background: #ffffff; color: #1f2f3f; }
                    table.payslip tr:last-child th, table.payslip tr:last-child td { border-bottom: none; }
                    table.payslip tr.highlight th, table.payslip tr.highlight td { background: #dcecf7; }
                    .section-spacer { height: 12px; line-height: 12px; font-size: 0; }
                    .footer { padding: 16px 24px 22px; font-size: 12px; color: #6b7f91; background: #f4f8fb; text-align: center; }
                    .banner-wrap { background: #ffffff; line-height: 0; }
                    .banner-wrap img { display: block; width: 100%; max-width: 100%; height: auto; }
                    @media only screen and (max-width: 600px) {
                        .shell { padding: 12px 8px 18px; }
                        .content { padding: 16px 14px 8px; }
                        .hero { padding: 18px 16px 20px; }
                        .hero-company { font-size: 18px; }
                        table.payslip th, table.payslip td { padding: 9px 10px; }
                    }
                </style>'
                . '</head><body>'
                . '<div class="shell">'
                . '<div class="container">'
                . '<div class="hero">'
                . '<table class="hero-row" role="presentation">'
                . '<tr>'
                . '<td style="width:64px;vertical-align:top;">' . $logoMarkup . '</td>'
                . '<td style="vertical-align:top;padding-left:14px;">'
                . '<div class="hero-title">Payslip Breakdown</div>'
                . '<div class="hero-company">Chengshi Construction Corp</div>'
                . '<div class="hero-pill">Employee Payroll Notification</div>'
                . '</td>'
                . '</tr>'
                . '</table>'
                . '</div>'
                . '<div class="banner-wrap">' . $backgroundMarkup . '</div>'
                . '<div class="content">'
                . '<div class="employee-card">'
                . '<table role="presentation">'
                . '<tr>'
                . '<td style="width:50%;padding-right:8px;">'
                . '<span class="meta-label">Employee ID</span>'
                . '<div class="meta-value">' . htmlspecialchars((string)($payload['id'] ?? ''), ENT_QUOTES) . '</div>'
                . '</td>'
                . '<td style="width:50%;padding-left:8px;">'
                . '<span class="meta-label">Employee</span>'
                . '<div class="meta-value">' . htmlspecialchars($employeeName, ENT_QUOTES) . '</div>'
                . '</td>'
                . '</tr>'
                . '<tr>'
                . '<td colspan="2" style="padding-top:14px;">'
                . '<span class="meta-label">Email</span>'
                . '<div class="meta-value" style="font-weight:600;">' . htmlspecialchars($email, ENT_QUOTES) . '</div>'
                . '</td>'
                . '</tr>'
                . '</table>'
                . '</div>'
                . '<div class="section-spacer">&nbsp;</div>'
                . '<table class="payslip" role="presentation">'
                . '<tbody>'
                . '<tr><th>Cutoff Salary</th><td style="text-align:right">' . htmlspecialchars(currency_format($cutoffSalary), ENT_QUOTES) . '</td></tr>'
                . '<tr><th>Gross Pay Per Day</th><td style="text-align:right">' . htmlspecialchars(currency_format($grossPayPerDay), ENT_QUOTES) . '</td></tr>'
                . '<tr><th>Hours of Work</th><td style="text-align:right">' . htmlspecialchars(number_format($hoursWorked, 2), ENT_QUOTES) . '</td></tr>'
                . '<tr><th>Total OT</th><td style="text-align:right">' . htmlspecialchars(currency_format($totalOt), ENT_QUOTES) . '</td></tr>'
                . '<tr><th>Legal Holiday</th><td style="text-align:right">' . htmlspecialchars(currency_format($legalHoliday), ENT_QUOTES) . '</td></tr>'
                . '<tr><th>Special Holiday</th><td style="text-align:right">' . htmlspecialchars(currency_format($specialHoliday), ENT_QUOTES) . '</td></tr>'
                . '<tr><th>Taxable Additional Income</th><td style="text-align:right">' . htmlspecialchars(currency_format($taxableAdditionalIncome), ENT_QUOTES) . '</td></tr>'
                . '<tr><th>Non-Taxable Additional Income</th><td style="text-align:right">' . htmlspecialchars(currency_format($nonTaxableAdditionalIncome), ENT_QUOTES) . '</td></tr>'
                . '<tr class="highlight"><th>Total Additions / Gross</th><th style="text-align:right">' . htmlspecialchars(currency_format($grossPay ?: $cutoffSalary), ENT_QUOTES) . '</th></tr>'
                . '<tr class="section-spacer"><td colspan="2">&nbsp;</td></tr>'
                . '<tr><th>SSS</th><td style="text-align:right">' . htmlspecialchars(currency_format($sss), ENT_QUOTES) . '</td></tr>'
                . '<tr><th>PHLTH</th><td style="text-align:right">' . htmlspecialchars(currency_format($phlth), ENT_QUOTES) . '</td></tr>'
                . '<tr><th>PAGIBIG</th><td style="text-align:right">' . htmlspecialchars(currency_format($pagibig), ENT_QUOTES) . '</td></tr>'
                . '<tr><th>Withholding Tax</th><td style="text-align:right">' . htmlspecialchars(currency_format($tax), ENT_QUOTES) . '</td></tr>'
                . '<tr><th>Additional Deductions</th><td style="text-align:right">' . htmlspecialchars(currency_format($personalCa), ENT_QUOTES) . '</td></tr>'
                . '<tr class="highlight"><th>Total Ded.</th><th style="text-align:right">' . htmlspecialchars(currency_format($totalDeduction), ENT_QUOTES) . '</th></tr>'
                . '<tr class="net-row"><th style="font-size:18px;padding-top:12px;">Net Pay</th><th style="text-align:right;font-size:22px;padding-top:12px;color:#0b6efd"><strong>' . htmlspecialchars(currency_format($netPay), ENT_QUOTES) . '</strong></th></tr>'
                . '</tbody></table>'
                . '</div>'
                . '<div class="footer">This message was generated by the Payroll System.</div>'
                . '</div></div></body></html>';

    $config = get_email_config();
    $sent = false;
    $mailError = null;
    $mimeParts = build_related_email(
        (string)($config['from_name'] ?? 'Payroll System'),
        (string)($config['from_email'] ?? 'no-reply@localhost'),
        $email,
        $subject,
        $htmlMessage,
        [
            ['path' => $logoPath, 'cid' => 'payroll-logo'],
            ['path' => $backgroundPath, 'cid' => 'payroll-banner']
        ]
    );

    try {
        // prefer HTML content; smtp handler should accept HTML body
        if (strtolower((string)$config['transport']) === 'smtp') {
            $sent = send_via_smtp($email, $subject, $mimeParts['headers'], $mimeParts['body'], $config);
        } else {
            $sent = @mail($email, $subject, $mimeParts['body'], implode("\r\n", $mimeParts['headers']));
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
