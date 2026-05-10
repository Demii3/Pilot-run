<?php
/** @var mysqli $dbc */
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'POST required']);
    exit;
}

include __DIR__ . '/../Modules/dbcon.php';

$identifier = isset($_POST['email']) ? trim($_POST['email']) : '';

if (!$identifier) {
    echo json_encode(['success' => false, 'message' => 'Email/username required']);
    exit;
}

// Find user by login username first (same table used by login), then by profile email.
$userId = null;
$username = null;
$userEmail = null;

$stmt = mysqli_prepare($dbc, "SELECT User_id, Username FROM users WHERE Username = ? LIMIT 1");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 's', $identifier);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $userId, $username);
    if (!mysqli_stmt_fetch($stmt)) {
        $userId = null;
        $username = null;
    }
    mysqli_stmt_close($stmt);
}

if ($userId === null) {
    $stmt = mysqli_prepare($dbc, "SELECT id, name, email FROM employees WHERE email = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $identifier);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $userId, $username, $userEmail);
        if (!mysqli_stmt_fetch($stmt)) {
            $userId = null;
            $username = null;
            $userEmail = null;
        }
        mysqli_stmt_close($stmt);
    }
}

if ($userId === null) {
    echo json_encode(['success' => false, 'message' => 'Email/username not found']);
    exit;
}

// Get email if not already fetched
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

if ($userEmail === null) {
    echo json_encode(['success' => false, 'message' => 'User email not found in system']);
    exit;
}

// Generate reset token
$token = bin2hex(random_bytes(32));
$expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

$createTableSql = "CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `token` VARCHAR(255) NOT NULL UNIQUE,
    `expires_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_token` (`token`),
    KEY `idx_expires` (`expires_at`),
    CONSTRAINT `prt_fk_user` FOREIGN KEY (`user_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
$createResult = mysqli_query($dbc, $createTableSql);
if (!$createResult) {
    echo json_encode(['success' => false, 'message' => 'Failed to initialize reset token storage']);
    exit;
}

// Store token in DB
$insertStmt = mysqli_prepare($dbc, "INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
if (!$insertStmt) {
    echo json_encode(['success' => false, 'message' => 'Failed to generate token']);
    exit;
}

mysqli_stmt_bind_param($insertStmt, 'iss', $userId, $token, $expiresAt);
if (!mysqli_stmt_execute($insertStmt)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save token']);
    exit;
}
mysqli_stmt_close($insertStmt);

// Build reset link
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$resetLink = $protocol . $host . '/Pilot-run/Features/reset_password.php?token=' . urlencode($token);

// Prepare email
$subject = 'Password Reset Request - Chengshi Construction Corp';
$body = "Hi $username,\n\n";
$body .= "You requested a password reset. Click the link below to reset your password:\n\n";
$body .= "$resetLink\n\n";
$body .= "This link expires in 1 hour.\n\n";
$body .= "If you did not request this, please ignore this email.\n";

// Send email
$emailSent = false;
$emailError = '';

if (function_exists('mail')) {
    $headers = "From: noreply@chengshi-construction.com\r\n";
    $headers .= "Reply-To: support@chengshi-construction.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    $emailSent = @mail($userEmail, $subject, $body, $headers);
    
    if (!$emailSent) {
        $emailError = "XAMPP mail() function failed. SMTP not configured.";
        error_log("Failed to send password reset email to: $userEmail for user_id: $userId");
    }
} else {
    $emailError = "mail() function not available on this server.";
    error_log("mail() function not available for password reset to user_id: $userId");
}

// Check if running in development/localhost
$isDevelopment = ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === 'localhost:80' || 
                  $_SERVER['HTTP_HOST'] === '127.0.0.1' || $_SERVER['HTTP_HOST'] === '127.0.0.1:80');

// Return response
if ($emailSent) {
    echo json_encode([
        'success' => true,
        'message' => 'Password reset link sent to your email (valid for 1 hour)'
    ]);
} else {
    // In development, show the link for testing
    if ($isDevelopment) {
        echo json_encode([
            'success' => true,
            'message' => 'Development Mode: Email service not configured. Reset link created.',
            'dev_mode' => true,
            'dev_notice' => 'Click the link below to reset password (valid for 1 hour):',
            'reset_link' => $resetLink,
            'token' => $token,
            'debug_email' => $userEmail
        ]);
    } else {
        // In production, don't reveal the link
        error_log("Password reset token created but email failed to send. User: $userId, Error: $emailError, Link: $resetLink");
        echo json_encode([
            'success' => true,
            'message' => 'Reset link created but email service unavailable. Contact administrator.',
            'token' => $token
        ]);
    }
}
?>
