<?php
// Reset Password Page
// Users provide a new password after clicking the email link

include __DIR__ . '/../Modules/dbcon.php';

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
$error = '';
$success = false;

if (!$createResult) {
        $error = 'Unable to initialize reset token storage';
}

$token = isset($_GET['token']) ? $_GET['token'] : '';

// Validate token
if (!$error && $token) {
    $stmt = mysqli_prepare($dbc, "SELECT user_id FROM password_reset_tokens WHERE token = ? AND expires_at > NOW() LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $token);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) === 0) {
        $error = 'Invalid or expired reset token';
    }
    mysqli_stmt_close($stmt);
} else {
    $error = 'No token provided';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Assets/login.css">
    <link rel="icon" type="image/png" href="../Images/logo.jpg"/>
    <style>
        .login-card input[type="password"] {
            width: 100%;
            margin-top: 5px;
            margin-bottom: 15px;
            padding: 8px 10px;
            border-radius: 6px;
            border: 1px solid rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.1);
            color: white;
            font-family: 'Poppins', sans-serif;
        }
        .login-card input[type="password"]::placeholder {
            color: rgba(255,255,255,0.6);
        }
        .login-card input[type="password"]:focus {
            outline: none;
            border-color: #3b82f6;
        }
        .alert {
            padding: 10px;
            border-radius: 6px;
            margin-top: 15px;
            font-size: 13px;
        }
        .alert-success {
            background: rgba(34, 197, 94, 0.2);
            color: #86efac;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }
        .alert-danger {
            background: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        .btn-link {
            background: #ffffff;
            color: black;
            border: none;
            border-radius: 6px;
            padding: 10px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            transition: 0.3s;
        }
        .btn-link:hover {
            background: #c3c3c4;
        }
        .forgot-link {
            text-align: center;
            margin-top: 15px;
        }
        .forgot-link a {
            color: white;
            text-decoration: none;
            font-size: 13px;
        }
        .forgot-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="bg-container">
        <img src="../Images/bgimg.jpg" class="bg-image" alt="Background">
        <div class="overlay"></div>
    </div>

    <div class="login-wrapper">
        <div class="login-card">
            <h1>Reset Password</h1>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <a href="forgot_password.php" class="btn-link" style="display: block;">Request New Reset Link</a>
            <?php else: ?>
                <p style="font-size: 13px; margin-bottom: 20px; color: rgba(255,255,255,0.8);">Enter your new password below</p>
                
                <form id="resetForm">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token ?? ''); ?>">
                    
                    <label>New Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Enter new password"
                        required
                        minlength="6"
                    >
                    
                    <label>Confirm Password</label>
                    <input 
                        type="password" 
                        id="passwordConfirm" 
                        name="passwordConfirm" 
                        placeholder="Confirm new password"
                        required
                        minlength="6"
                    >
                    
                    <button type="submit">Reset Password</button>
                </form>
                
                <div id="msg"></div>
                
                <div class="forgot-link">
                    <a href="../index.php">← Back to Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $('#resetForm').on('submit', function(e){
            e.preventDefault();
            const password = $('#password').val();
            const confirmPassword = $('#passwordConfirm').val();
            const token = $('input[name="token"]').val();
            
            if (password !== confirmPassword) {
                $('#msg').html('').removeClass().addClass('alert alert-danger').html('Passwords do not match');
                return;
            }
            
            $('#msg').html('Resetting...').removeClass();
            
            $.post('api_reset_password.php', { 
                token: token, 
                password: password 
            }, function(resp){
                try {
                    const j = typeof resp === 'object' ? resp : JSON.parse(resp);
                    if (j.success) {
                        $('#msg').removeClass().addClass('alert alert-success').html(j.message || 'Password reset successfully');
                        setTimeout(function(){
                            window.location.href = '../index.php';
                        }, 2000);
                    } else {
                        $('#msg').removeClass().addClass('alert alert-danger').html(j.message || 'Failed to reset password');
                    }
                } catch(e) {
                    $('#msg').removeClass().addClass('alert alert-danger').html('Unexpected response');
                }
            }).fail(function(){
                $('#msg').removeClass().addClass('alert alert-danger').html('Request failed');
            });
        });
    </script>
</body>
</html>
