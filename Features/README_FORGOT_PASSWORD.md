# Forgot Password Feature

## Files Added

- `forgot_password.php` — User-facing form to request password reset
- `api_forgot_password.php` — Backend API that generates token and sends email
- `reset_password.php` — Token-validated form to enter new password
- `api_reset_password.php` — Backend API to process password reset
- `migrations/001_password_reset_tokens.sql` — Creates the password_reset_tokens table

## Setup

1. **Run the migration** to create the password_reset_tokens table:
   ```sql
   CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
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
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
   ```

2. **Add a link to the forgot password page** on your login page:
   ```html
   <a href="../Features/forgot_password.php">Forgot password?</a>
   ```

3. **(Optional) Configure email sending:**
   - Update the mail configuration in `api_forgot_password.php` to use your SMTP server
   - Or use a library like PHPMailer for more reliable email delivery

## How It Works

1. User visits `/Features/forgot_password.php` and enters email/username
2. API validates and generates a reset token (valid for 1 hour)
3. Token is stored in `password_reset_tokens` table
4. Email is sent with reset link (includes token)
5. User clicks link, which takes them to `/Features/reset_password.php?token=xxx`
6. User enters new password
7. API validates token, updates password in both `users` and `employees` tables, and deletes the token

## URL to Access

- Forgot Password: `http://localhost/geofence_test/Features/forgot_password.php`
- Reset Password: `http://localhost/geofence_test/Features/reset_password.php?token=<TOKEN>` (sent via email)

## Security Notes

- Tokens expire after 1 hour
- Tokens are hashed and stored securely
- Passwords are hashed using PHP's password_hash()
- Old tokens are deleted after use
- Input validation is performed on all endpoints
