<?php
// Reset Password Page
// Users provide a new password after clicking the email link

include __DIR__ . '/../Modules/dbcon.php';

$token = isset($_GET['token']) ? $_GET['token'] : '';
$error = '';
$success = false;

// Validate token
if ($token) {
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .reset-card {
            max-width: 400px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            border-radius: 10px;
        }
        .reset-card .card-header {
            background-color: #667eea;
            color: white;
            border-radius: 10px 10px 0 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card reset-card">
            <div class="card-header">
                <h4 class="mb-0">Reset Password</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <a href="forgot_password.php" class="btn btn-secondary w-100">Request New Reset Link</a>
                <?php else: ?>
                    <form id="resetForm">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="password" 
                                name="password" 
                                placeholder="Enter new password"
                                required
                                minlength="6"
                            >
                        </div>
                        <div class="mb-3">
                            <label for="passwordConfirm" class="form-label">Confirm Password</label>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="passwordConfirm" 
                                name="passwordConfirm" 
                                placeholder="Confirm new password"
                                required
                                minlength="6"
                            >
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                    </form>
                    <div id="msg" class="mt-3"></div>
                    <hr>
                    <p class="text-center small">
                        <a href="../index.php">Back to Login</a>
                    </p>
                <?php endif; ?>
            </div>
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
                $('#msg').removeClass().addClass('alert alert-danger').text('Passwords do not match');
                return;
            }
            
            $('#msg').removeClass().text('Resetting...');
            
            $.post('api_reset_password.php', { 
                token: token, 
                password: password 
            }, function(resp){
                try {
                    const j = typeof resp === 'object' ? resp : JSON.parse(resp);
                    if (j.success) {
                        $('#msg').removeClass().addClass('alert alert-success').text(j.message || 'Password reset successfully');
                        setTimeout(function(){
                            window.location.href = '../index.php';
                        }, 2000);
                    } else {
                        $('#msg').removeClass().addClass('alert alert-danger').text(j.message || 'Failed to reset password');
                    }
                } catch(e) {
                    $('#msg').removeClass().addClass('alert alert-danger').text('Unexpected response');
                }
            }).fail(function(){
                $('#msg').removeClass().addClass('alert alert-danger').text('Request failed');
            });
        });
    </script>
</body>
</html>
