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
    $stmt = mysqli_prepare($dbc, "SELECT id, name FROM employees WHERE email = ? LIMIT 1");
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
}

if ($userId === null) {
    echo json_encode(['success' => false, 'message' => 'Email/username not found']);
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
$resetLink = 'http://localhost/geofence_test/Features/reset_password.php?token=' . urlencode($token);

// Send email (configure mail settings as needed)
$subject = 'Password Reset Request';
$body = "Hi $username,\n\n";
$body .= "You requested a password reset. Click the link below to reset your password:\n\n";
$body .= "$resetLink\n\n";
$body .= "This link expires in 1 hour.\n\n";
$body .= "If you did not request this, please ignore this email.\n";

// For local testing, just return success (email sending requires SMTP config)
// In production, configure mail() or use a library like PHPMailer
if (function_exists('mail')) {
    // Get email from db
    $emailStmt = mysqli_prepare($dbc, "SELECT email FROM employees WHERE id = ?");
    mysqli_stmt_bind_param($emailStmt, 'i', $userId);
    mysqli_stmt_execute($emailStmt);
    mysqli_stmt_bind_result($emailStmt, $toEmail);
    if (mysqli_stmt_fetch($emailStmt) && $toEmail !== null) {
        @mail($toEmail, $subject, $body);
    }
    mysqli_stmt_close($emailStmt);
}

// Return success (email may not actually send if mail() not configured)
echo json_encode([
    'success' => true,
    'message' => 'Check your email for password reset link (valid for 1 hour)',
    'debug_reset_link' => $resetLink  // Remove in production
]);
?>
