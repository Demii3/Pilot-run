<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'POST required']);
    exit;
}

include __DIR__ . '/../Modules/dbcon.php';

$token = isset($_POST['token']) ? $_POST['token'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (!$token || !$password) {
    echo json_encode(['success' => false, 'message' => 'Token and password required']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit;
}

// Validate token and get user_id
$tokenStmt = mysqli_prepare($dbc, "SELECT user_id FROM password_reset_tokens WHERE token = ? AND expires_at > NOW()");
if (!$tokenStmt) {
    echo json_encode(['success' => false, 'message' => 'DB error']);
    exit;
}

mysqli_stmt_bind_param($tokenStmt, 's', $token);
mysqli_stmt_execute($tokenStmt);
mysqli_stmt_bind_result($tokenStmt, $userId);

if (!mysqli_stmt_fetch($tokenStmt)) {
    echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
    exit;
}
mysqli_stmt_close($tokenStmt);

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Update users table
$updateUsersStmt = mysqli_prepare($dbc, "UPDATE users SET Password = ? WHERE User_id = ?");
if (!$updateUsersStmt) {
    echo json_encode(['success' => false, 'message' => 'Failed to update password']);
    exit;
}

mysqli_stmt_bind_param($updateUsersStmt, 'si', $hashedPassword, $userId);
$usersUpdated = mysqli_stmt_execute($updateUsersStmt);
mysqli_stmt_close($updateUsersStmt);

// Also update employees table password if it exists
$updateEmpsStmt = mysqli_prepare($dbc, "UPDATE employees SET password = ? WHERE id = ?");
if ($updateEmpsStmt) {
    mysqli_stmt_bind_param($updateEmpsStmt, 'si', $hashedPassword, $userId);
    mysqli_stmt_execute($updateEmpsStmt);
    mysqli_stmt_close($updateEmpsStmt);
}

if (!$usersUpdated) {
    echo json_encode(['success' => false, 'message' => 'Failed to update password']);
    exit;
}

// Delete the used token
$deleteStmt = mysqli_prepare($dbc, "DELETE FROM password_reset_tokens WHERE token = ?");
if ($deleteStmt) {
    mysqli_stmt_bind_param($deleteStmt, 's', $token);
    mysqli_stmt_execute($deleteStmt);
    mysqli_stmt_close($deleteStmt);
}

echo json_encode(['success' => true, 'message' => 'Password reset successfully. Redirecting to login...']);
?>
