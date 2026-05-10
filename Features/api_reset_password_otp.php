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

try {
    $sessionId = isset($_POST['session_id']) ? trim($_POST['session_id']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (!$sessionId || !$password) {
        echo json_encode(['success' => false, 'message' => 'Session ID and password required']);
        exit;
    }

    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
        exit;
    }

    // Create password reset sessions table if needed
    $createTableSql = "CREATE TABLE IF NOT EXISTS `password_reset_sessions` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `session_id` VARCHAR(255) NOT NULL UNIQUE,
        `user_id` INT UNSIGNED NOT NULL,
        `user_email` VARCHAR(255) NOT NULL,
        `expires_at` DATETIME NOT NULL,
        `is_completed` TINYINT(1) DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_session_id` (`session_id`),
        KEY `idx_user_id` (`user_id`),
        KEY `idx_expires` (`expires_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

    if (!mysqli_query($dbc, $createTableSql)) {
        throw new Exception('Failed to create sessions table: ' . mysqli_error($dbc));
    }

    // Validate session
    $sessionStmt = mysqli_prepare($dbc, "SELECT user_id, user_email FROM password_reset_sessions WHERE session_id = ? AND expires_at > NOW() AND is_completed = 0 LIMIT 1");

    if (!$sessionStmt) {
        throw new Exception('Failed to prepare session query: ' . mysqli_error($dbc));
    }

    mysqli_stmt_bind_param($sessionStmt, 's', $sessionId);
    if (!mysqli_stmt_execute($sessionStmt)) {
        throw new Exception('Failed to execute session query: ' . mysqli_error($dbc));
    }
    mysqli_stmt_bind_result($sessionStmt, $userId, $userEmail);

    if (!mysqli_stmt_fetch($sessionStmt)) {
        mysqli_stmt_close($sessionStmt);
        echo json_encode(['success' => false, 'message' => 'Invalid or expired session. Please start the reset process again.']);
        exit;
    }

    mysqli_stmt_close($sessionStmt);

    // Hash the new password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Update password in users table
    $updateUsersStmt = mysqli_prepare($dbc, "UPDATE users SET Password = ? WHERE User_id = ?");

    if (!$updateUsersStmt) {
        throw new Exception('Failed to prepare users password update: ' . mysqli_error($dbc));
    }

    mysqli_stmt_bind_param($updateUsersStmt, 'si', $hashedPassword, $userId);
    if (!mysqli_stmt_execute($updateUsersStmt)) {
        throw new Exception('Failed to update users password: ' . mysqli_error($dbc));
    }
    mysqli_stmt_close($updateUsersStmt);

    // Also update password in employees table to keep in sync
    $updateEmployeesStmt = mysqli_prepare($dbc, "UPDATE employees SET password = ? WHERE id = ?");

    if (!$updateEmployeesStmt) {
        throw new Exception('Failed to prepare employees password update: ' . mysqli_error($dbc));
    }

    mysqli_stmt_bind_param($updateEmployeesStmt, 'si', $hashedPassword, $userId);
    if (!mysqli_stmt_execute($updateEmployeesStmt)) {
        throw new Exception('Failed to update employees password: ' . mysqli_error($dbc));
    }
    mysqli_stmt_close($updateEmployeesStmt);

    // Mark session as completed
    $completeStmt = mysqli_prepare($dbc, "UPDATE password_reset_sessions SET is_completed = 1 WHERE session_id = ?");

    if ($completeStmt) {
        mysqli_stmt_bind_param($completeStmt, 's', $sessionId);
        mysqli_stmt_execute($completeStmt);
        mysqli_stmt_close($completeStmt);
    }

    // Clean up old OTP tokens for this user
    $cleanupStmt = mysqli_prepare($dbc, "DELETE FROM otp_tokens WHERE user_email = ? AND created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)");

    if ($cleanupStmt) {
        mysqli_stmt_bind_param($cleanupStmt, 's', $userEmail);
        mysqli_stmt_execute($cleanupStmt);
        mysqli_stmt_close($cleanupStmt);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Password reset successfully'
    ]);

} catch (Exception $e) {
    error_log("Password Reset Exception: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>
