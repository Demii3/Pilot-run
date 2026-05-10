<?php
// Reset Password Page (Step 3)
// Users enter their new password after OTP verification

$error = '';
$sessionId = isset($_GET['session_id']) ? $_GET['session_id'] : '';

if (!$sessionId) {
    $error = 'Invalid session. Please start the password reset process again.';
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
        .password-strength {
            margin-top: 5px;
            font-size: 12px;
            height: 4px;
            border-radius: 2px;
            background: rgba(255,255,255,0.2);
            overflow: hidden;
        }
        .strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
        }
        .strength-weak { background: #f44336; width: 33%; }
        .strength-medium { background: #ff9800; width: 66%; }
        .strength-strong { background: #4CAF50; width: 100%; }
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
            <p style="font-size: 13px; margin-bottom: 20px; color: rgba(255,255,255,0.8);">
                Create a new password for your account
            </p>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php else: ?>
                <form id="resetForm">
                    <label>New Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Enter new password"
                        required
                        minlength="6"
                    >
                    <div class="password-strength">
                        <div class="strength-bar" id="strengthBar"></div>
                    </div>
                    
                    <label style="margin-top: 15px;">Confirm Password</label>
                    <input 
                        type="password" 
                        id="confirmPassword" 
                        name="confirmPassword" 
                        placeholder="Confirm new password"
                        required
                        minlength="6"
                    >
                    
                    <input type="hidden" id="sessionId" name="sessionId" value="<?php echo htmlspecialchars($sessionId); ?>">
                    
                    <button type="submit">Reset Password</button>
                </form>
                
                <div id="msg"></div>
            <?php endif; ?>
            
            <div class="forgot">
                <a href="../index.php">← Back to Login</a>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Password strength indicator
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');
        
        passwordInput.addEventListener('input', function() {
            const strength = checkPasswordStrength(this.value);
            strengthBar.className = 'strength-bar strength-' + strength;
        });
        
        function checkPasswordStrength(password) {
            if (password.length < 6) return 'weak';
            if (password.length < 10) return 'medium';
            if (/[A-Z]/.test(password) && /[0-9]/.test(password) && /[!@#$%^&*]/.test(password)) {
                return 'strong';
            }
            return 'medium';
        }
        
        // Form submission
        $('#resetForm').on('submit', function(e){
            e.preventDefault();
            
            const password = $('#password').val();
            const confirmPassword = $('#confirmPassword').val();
            const sessionId = $('#sessionId').val();
            
            $('#msg').html('').removeClass();
            
            if (password.length < 6) {
                $('#msg').addClass('alert alert-danger').html('Password must be at least 6 characters');
                return;
            }
            
            if (password !== confirmPassword) {
                $('#msg').addClass('alert alert-danger').html('Passwords do not match');
                return;
            }
            
            $.post('api_reset_password_otp.php', { 
                session_id: sessionId, 
                password: password 
            }, function(resp){
                try {
                    const j = typeof resp === 'object' ? resp : JSON.parse(resp);
                    if (j.success) {
                        $('#msg').addClass('alert alert-success').html('Password reset successfully! Redirecting to login...');
                        setTimeout(() => {
                            window.location.href = '../index.php';
                        }, 2000);
                    } else {
                        $('#msg').addClass('alert alert-danger').html(j.message || 'Failed to reset password');
                    }
                } catch(e) {
                    $('#msg').addClass('alert alert-danger').html('Unexpected response: ' + e.message);
                }
            }).fail(function(xhr, status, error){
                $('#msg').addClass('alert alert-danger').html('Request failed: ' + error);
            });
        });
    </script>
</body>
</html>
