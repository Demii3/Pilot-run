<?php
/** @var mysqli $dbc */
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'POST required']);
    exit;
}

include __DIR__ . '/../Modules/dbcon.php';

$email = isset($_POST['email']) ? trim($_POST['email']) : '';

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Email/username required']);
    exit;
}

// Find user by email or username
$query = "SELECT id, username FROM employees WHERE email = ? OR username = ? LIMIT 1";
$stmt = mysqli_prepare($dbc, $query);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'DB error']);
    exit;
}

mysqli_stmt_bind_param($stmt, 'ss', $email, $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $userId, $username);

if (!mysqli_stmt_fetch($stmt)) {
    echo json_encode(['success' => false, 'message' => 'Email/username not found']);
    exit;
}
mysqli_stmt_close($stmt);

// Generate reset token
$token = bin2hex(random_bytes(32));
$expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

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
