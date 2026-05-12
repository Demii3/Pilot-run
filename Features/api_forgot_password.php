<?php
/** @var mysqli $dbc */
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'POST required']);
    exit;
}

@include __DIR__ . '/../Modules/dbcon.php';

function load_email_config() {
    $configPath = __DIR__ . '/../HR/Employee_payroll/email_config.php';
    if (!file_exists($configPath)) {
        return null;
    }

    $config = include $configPath;
    return is_array($config) ? $config : null;
}

function smtp_read_response($socket) {
    $response = '';
    while (!feof($socket)) {
        $line = fgets($socket, 515);
        if ($line === false) {
            break;
        }

        $response .= $line;
        if (strlen($line) < 4 || $line[3] === ' ') {
            break;
        }
    }

    return $response;
}

function smtp_expect($socket, $expectedCode) {
    $response = smtp_read_response($socket);
    if (substr($response, 0, 3) !== (string)$expectedCode) {
        throw new Exception('SMTP expected ' . $expectedCode . ' but got: ' . trim($response));
    }
}

function smtp_send_command($socket, $command, $expectedCode) {
    fwrite($socket, $command . "\r\n");
    smtp_expect($socket, $expectedCode);
}

function send_reset_email($toEmail, $subject, $htmlBody, $plainBody) {
    $config = load_email_config();
    if (!$config) {
        return [false, 'Email configuration not found'];
    }

    $transport = strtolower((string)($config['transport'] ?? 'smtp'));
    $fromEmail = trim((string)($config['from_email'] ?? ''));
    $fromName = trim((string)($config['from_name'] ?? 'Chengshi Construction Corp'));

    if ($transport !== 'smtp') {
        return [false, 'SMTP transport is required'];
    }

    $host = trim((string)($config['smtp_host'] ?? ''));
    $port = (int)($config['smtp_port'] ?? 587);
    $encryption = strtolower(trim((string)($config['smtp_encryption'] ?? 'tls')));
    $username = trim((string)($config['smtp_username'] ?? ''));
    $password = (string)($config['smtp_password'] ?? '');

    if ($host === '' || $port <= 0 || $username === '' || $password === '' || $fromEmail === '') {
        return [false, 'SMTP is not fully configured'];
    }

    $remoteHost = ($encryption === 'ssl' ? 'ssl://' : '') . $host;
    $socket = @stream_socket_client($remoteHost . ':' . $port, $errno, $errstr, 20);
    if (!$socket) {
        return [false, 'SMTP connection failed: ' . $errstr . ' (' . $errno . ')'];
    }

    stream_set_timeout($socket, 20);

    try {
        smtp_expect($socket, 220);
        smtp_send_command($socket, 'EHLO localhost', 250);

        if ($encryption === 'tls') {
            smtp_send_command($socket, 'STARTTLS', 220);
            $tlsEnabled = @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if (!$tlsEnabled) {
                throw new Exception('Unable to establish TLS on SMTP connection');
            }
            smtp_send_command($socket, 'EHLO localhost', 250);
        }

        smtp_send_command($socket, 'AUTH LOGIN', 334);
        smtp_send_command($socket, base64_encode($username), 334);
        smtp_send_command($socket, base64_encode($password), 235);
        smtp_send_command($socket, 'MAIL FROM:<' . $fromEmail . '>', 250);
        smtp_send_command($socket, 'RCPT TO:<' . $toEmail . '>', 250);
        smtp_send_command($socket, 'DATA', 354);

        $headers = [];
        $headers[] = 'From: ' . $fromName . ' <' . $fromEmail . '>';
        $headers[] = 'To: <' . $toEmail . '>';
        $headers[] = 'Subject: ' . $subject;
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/html; charset=UTF-8';

        fwrite($socket, implode("\r\n", $headers) . "\r\n\r\n" . $htmlBody . "\r\n.\r\n");
        smtp_expect($socket, 250);
        smtp_send_command($socket, 'QUIT', 221);

        return [true, null];
    } catch (Exception $e) {
        error_log('SMTP Error: ' . $e->getMessage());
        return [false, $e->getMessage()];
    } finally {
        fclose($socket);
    }
}

$identifier = isset($_POST['email']) ? trim($_POST['email']) : '';
// Normalize identifier for case-insensitive matching
$identifier_norm = mb_strtolower($identifier);

if (!$identifier) {
    echo json_encode(['success' => false, 'message' => 'Email/username required']);
    exit;
}

// Find user by username or email
$userId = null;
$userEmail = null;

// First, try to find by username in users table (case-insensitive)
$stmt = mysqli_prepare($dbc, "SELECT User_id FROM users WHERE LOWER(Username) = ? LIMIT 1");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 's', $identifier_norm);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $userId);
    if (!mysqli_stmt_fetch($stmt)) {
        $userId = null;
    }
    mysqli_stmt_close($stmt);
}

// If not found, try by email or name in employees table (case-insensitive)
if ($userId === null) {
    $stmt = mysqli_prepare($dbc, "SELECT id, email FROM employees WHERE TRIM(LOWER(email)) = ? OR TRIM(LOWER(name)) = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'ss', $identifier_norm, $identifier_norm);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $userId, $userEmail);
        if (!mysqli_stmt_fetch($stmt)) {
            $userId = null;
            $userEmail = null;
        }
        mysqli_stmt_close($stmt);
    }
}

if ($userId === null) {
    // Detailed debug info when not found
    $debugInfo = [];
    // Does a username match exist?
    $checkStmt = mysqli_prepare($dbc, "SELECT COUNT(*) FROM users WHERE LOWER(Username) = ?");
    if ($checkStmt) {
        mysqli_stmt_bind_param($checkStmt, 's', $identifier_norm);
        mysqli_stmt_execute($checkStmt);
        mysqli_stmt_bind_result($checkStmt, $u_cnt);
        mysqli_stmt_fetch($checkStmt);
        mysqli_stmt_close($checkStmt);
        $debugInfo[] = "usernames_match: $u_cnt";
    }
    // Does an employee email/name match exist?
    $checkStmt = mysqli_prepare($dbc, "SELECT COUNT(*) FROM employees WHERE LOWER(email) = ? OR LOWER(name) = ?");
    if ($checkStmt) {
        mysqli_stmt_bind_param($checkStmt, 'ss', $identifier_norm, $identifier_norm);
        mysqli_stmt_execute($checkStmt);
        mysqli_stmt_bind_result($checkStmt, $e_cnt);
        mysqli_stmt_fetch($checkStmt);
        mysqli_stmt_close($checkStmt);
        $debugInfo[] = "employees_match: $e_cnt";
    }

    error_log("Forgot Password lookup failed for identifier: '" . $identifier . "' (normalized: '" . $identifier_norm . "'). Debug: " . implode('; ', $debugInfo));

    echo json_encode(['success' => false, 'message' => 'Email/username not found', 'debug_info' => $debugInfo]);
    exit;
}

// Get email if not already fetched and normalize it
if ($userEmail === null) {
    $emailStmt = mysqli_prepare($dbc, "SELECT email FROM employees WHERE id = ?");
    if ($emailStmt) {
        mysqli_stmt_bind_param($emailStmt, 'i', $userId);
        mysqli_stmt_execute($emailStmt);
        mysqli_stmt_bind_result($emailStmt, $userEmail);
        mysqli_stmt_fetch($emailStmt);
        mysqli_stmt_close($emailStmt);
    }
}

if ($userEmail !== null) {
    $userEmail = trim(mb_strtolower($userEmail));
}

if ($userEmail === null) {
    echo json_encode(['success' => false, 'message' => 'User email not found in system']);
    exit;
}

// Create OTP tokens table if it doesn't exist
$createTableSql = "CREATE TABLE IF NOT EXISTS `otp_tokens` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `user_email` VARCHAR(255) NOT NULL,
    `otp_code` VARCHAR(10) NOT NULL,
    `is_verified` TINYINT(1) DEFAULT 0,
    `expires_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `verified_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_email` (`user_email`),
    KEY `idx_otp_code` (`otp_code`),
    KEY `idx_expires` (`expires_at`),
    CONSTRAINT `otp_fk_user` FOREIGN KEY (`user_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (!mysqli_query($dbc, $createTableSql)) {
    echo json_encode(['success' => false, 'message' => 'Failed to initialize OTP storage']);
    exit;
}

// Delete any existing unexpired OTP for this user (to prevent multiple codes)
$deleteStmt = mysqli_prepare($dbc, "DELETE FROM otp_tokens WHERE LOWER(user_email) = LOWER(?) AND is_verified = 0");
if ($deleteStmt) {
    mysqli_stmt_bind_param($deleteStmt, 's', $userEmail);
    mysqli_stmt_execute($deleteStmt);
    mysqli_stmt_close($deleteStmt);
}

// Generate 6-digit OTP
$otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

// Store OTP in database - use MySQL NOW() function for consistency
$insertStmt = mysqli_prepare($dbc, "INSERT INTO otp_tokens (user_id, user_email, otp_code, expires_at) VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))");
if (!$insertStmt) {
    echo json_encode(['success' => false, 'message' => 'Failed to generate OTP']);
    exit;
}

// Ensure stored email is normalized
mysqli_stmt_bind_param($insertStmt, 'iss', $userId, $userEmail, $otp);
if (!mysqli_stmt_execute($insertStmt)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save OTP']);
    exit;
}
mysqli_stmt_close($insertStmt);

// Get employee name for email
$nameStmt = mysqli_prepare($dbc, "SELECT name FROM employees WHERE id = ? LIMIT 1");
$employeeName = 'User';
if ($nameStmt) {
    mysqli_stmt_bind_param($nameStmt, 'i', $userId);
    mysqli_stmt_execute($nameStmt);
    mysqli_stmt_bind_result($nameStmt, $name);
    if (mysqli_stmt_fetch($nameStmt)) {
        $employeeName = $name;
    }
    mysqli_stmt_close($nameStmt);
}

// Prepare OTP email
$subject = 'Password Reset OTP - Chengshi Construction Corp';
$plainBody = "Hi {$employeeName},\n\nYou requested a password reset for your Chengshi Construction Corp account.\n\nUse the following One-Time Password (OTP) to reset your password:\n\n{$otp}\n\nThis code is valid for 10 minutes only.\n\nIf you did not request this, please ignore this email.";
$htmlBody = '
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #2c3e50; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f5f5f5; padding: 20px; border-radius: 0 0 5px 5px; }
        .otp-code { 
            background: white; 
            padding: 20px; 
            text-align: center; 
            margin: 20px 0; 
            border: 2px solid #4CAF50; 
            border-radius: 5px; 
        }
        .otp-code strong { font-size: 32px; letter-spacing: 5px; color: #4CAF50; }
        .warning { color: #f44336; font-size: 12px; margin-top: 10px; }
        .footer { text-align: center; color: #999; font-size: 12px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Password Reset Request</h2>
        </div>
        <div class="content">
            <p>Hi <strong>' . htmlspecialchars($employeeName) . '</strong>,</p>
            
            <p>You requested a password reset for your Chengshi Construction Corp account.</p>
            
            <p>Use the following One-Time Password (OTP) to reset your password:</p>
            
            <div class="otp-code">
                <strong>' . htmlspecialchars($otp) . '</strong>
            </div>
            
            <p><strong>OTP Validity:</strong> This code is valid for <strong>10 minutes</strong> only.</p>
            
            <p class="warning">
                <strong>⚠️ Security Warning:</strong><br>
                - Never share this OTP with anyone<br>
                - We will never ask for this code via email or phone<br>
                - If you did not request this, please ignore this email
            </p>
            
            <div class="footer">
                <p>© 2024 Chengshi Construction Corp. All rights reserved.</p>
                <p>This is an automated message, please do not reply.</p>
            </div>
        </div>
    </div>
</body>
</html>
';

[$emailSent, $emailError] = send_reset_email($userEmail, $subject, $htmlBody, $plainBody);

if (!$emailSent) {
    error_log("Forgot Password email send failed for {$userEmail}: {$emailError}");
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send OTP email: ' . $emailError
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'OTP sent to your email (valid for 10 minutes)',
    'actual_email' => $userEmail
]);
?>
