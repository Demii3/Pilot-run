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
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';

    if (!$email || !$otp) {
        echo json_encode(['success' => false, 'message' => 'Email and OTP required']);
        exit;
    }

    // Log debug info
    error_log("OTP Verification - Email: $email, OTP: $otp");

    // Validate OTP format
    if (!preg_match('/^[0-9]{6}$/', $otp)) {
        echo json_encode(['success' => false, 'message' => 'Invalid OTP format']);
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
        throw new Exception('Failed to initialize OTP storage: ' . mysqli_error($dbc));
    }

    // Verify OTP - with detailed debugging
    $stmt = mysqli_prepare($dbc, "SELECT id, user_id FROM otp_tokens WHERE LOWER(user_email) = LOWER(?) AND otp_code = ? AND expires_at > NOW() AND is_verified = 0 LIMIT 1");

    if (!$stmt) {
        throw new Exception('Prepare error: ' . mysqli_error($dbc));
    }

    mysqli_stmt_bind_param($stmt, 'ss', $email, $otp);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Execute error: ' . mysqli_error($dbc));
    }

    mysqli_stmt_bind_result($stmt, $otpId, $userId);

    if (!mysqli_stmt_fetch($stmt)) {
        mysqli_stmt_close($stmt);
        
        // Detailed debugging - check why OTP was not found
        $debugInfo = [];
        
        // Check if any OTP exists for this email
        $debugStmt = mysqli_prepare($dbc, "SELECT COUNT(*) as cnt FROM otp_tokens WHERE LOWER(user_email) = LOWER(?)");
        if ($debugStmt) {
            mysqli_stmt_bind_param($debugStmt, 's', $email);
            mysqli_stmt_execute($debugStmt);
            mysqli_stmt_bind_result($debugStmt, $cnt);
            mysqli_stmt_fetch($debugStmt);
            mysqli_stmt_close($debugStmt);
            $debugInfo[] = "Email records: $cnt";
        }
        
        // Check if OTP code exists (regardless of email/expiry)
        $debugStmt = mysqli_prepare($dbc, "SELECT COUNT(*) as cnt FROM otp_tokens WHERE otp_code = ?");
        if ($debugStmt) {
            mysqli_stmt_bind_param($debugStmt, 's', $otp);
            mysqli_stmt_execute($debugStmt);
            mysqli_stmt_bind_result($debugStmt, $cnt);
            mysqli_stmt_fetch($debugStmt);
            mysqli_stmt_close($debugStmt);
            $debugInfo[] = "OTP code records: $cnt";
        }
        
        // Check if any OTP for this email is expired
        $debugStmt = mysqli_prepare($dbc, "SELECT COUNT(*) as cnt FROM otp_tokens WHERE LOWER(user_email) = LOWER(?) AND expires_at <= NOW()");
        if ($debugStmt) {
            mysqli_stmt_bind_param($debugStmt, 's', $email);
            mysqli_stmt_execute($debugStmt);
            mysqli_stmt_bind_result($debugStmt, $cnt);
            mysqli_stmt_fetch($debugStmt);
            mysqli_stmt_close($debugStmt);
            $debugInfo[] = "Expired OTPs for email: $cnt";
        }
        
        // Check if any OTP for this email is already verified
        $debugStmt = mysqli_prepare($dbc, "SELECT COUNT(*) as cnt FROM otp_tokens WHERE LOWER(user_email) = LOWER(?) AND is_verified = 1");
        if ($debugStmt) {
            mysqli_stmt_bind_param($debugStmt, 's', $email);
            mysqli_stmt_execute($debugStmt);
            mysqli_stmt_bind_result($debugStmt, $cnt);
            mysqli_stmt_fetch($debugStmt);
            mysqli_stmt_close($debugStmt);
            $debugInfo[] = "Verified OTPs for email: $cnt";
        }
        
        error_log("OTP Verification failed for email: $email, otp: $otp. Debug: " . implode("; ", $debugInfo));
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid or expired OTP',
            'debug_info' => $debugInfo
        ]);
        exit;
    }

    mysqli_stmt_close($stmt);

    // Mark OTP as verified
    $verifyStmt = mysqli_prepare($dbc, "UPDATE otp_tokens SET is_verified = 1, verified_at = NOW() WHERE id = ?");

    if (!$verifyStmt) {
        throw new Exception('Failed to prepare verify statement: ' . mysqli_error($dbc));
    }

    mysqli_stmt_bind_param($verifyStmt, 'i', $otpId);
    if (!mysqli_stmt_execute($verifyStmt)) {
        throw new Exception('Failed to mark OTP as verified: ' . mysqli_error($dbc));
    }
    mysqli_stmt_close($verifyStmt);

    // Generate a temporary session ID
    $sessionId = bin2hex(random_bytes(16));

    // Create password reset sessions table if needed
    $createSessionTableSql = "CREATE TABLE IF NOT EXISTS `password_reset_sessions` (
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

    if (!mysqli_query($dbc, $createSessionTableSql)) {
        throw new Exception('Failed to create session table: ' . mysqli_error($dbc));
    }

    // Store session - use MySQL NOW() function for consistency
    $sessionStmt = mysqli_prepare($dbc, "INSERT INTO password_reset_sessions (session_id, user_id, user_email, expires_at) VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE))");

    if (!$sessionStmt) {
        throw new Exception('Failed to prepare session insert: ' . mysqli_error($dbc));
    }

    mysqli_stmt_bind_param($sessionStmt, 'sis', $sessionId, $userId, $email);
    if (!mysqli_stmt_execute($sessionStmt)) {
        throw new Exception('Failed to insert session: ' . mysqli_error($dbc));
    }
    mysqli_stmt_close($sessionStmt);

    echo json_encode([
        'success' => true,
        'message' => 'OTP verified successfully',
        'session_id' => $sessionId
    ]);

} catch (Exception $e) {
    error_log("OTP Verification Exception: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>
