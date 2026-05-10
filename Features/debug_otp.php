<?php
// Debug page to check OTP tokens in database
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/../Modules/dbcon.php';

echo "<h1>OTP Tokens Debug</h1>";

// Create table
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

mysqli_query($dbc, $createTableSql);

echo "<h2>All OTP Tokens:</h2>";
$result = mysqli_query($dbc, "SELECT id, user_id, user_email, otp_code, is_verified, expires_at, created_at FROM otp_tokens ORDER BY created_at DESC LIMIT 20");

if ($result) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>User ID</th><th>Email</th><th>OTP</th><th>Verified</th><th>Expires At</th><th>Created At</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        $verified = $row['is_verified'] ? 'YES' : 'NO';
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['user_id']}</td>";
        echo "<td>{$row['user_email']}</td>";
        echo "<td><strong>{$row['otp_code']}</strong></td>";
        echo "<td>{$verified}</td>";
        echo "<td>{$row['expires_at']}</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "Error: " . mysqli_error($dbc);
}

echo "<h2>Current Server Time:</h2>";
echo date('Y-m-d H:i:s');

echo "<hr>";
echo "<p><a href='forgot_password.php'>← Back to Forgot Password</a></p>";
?>
