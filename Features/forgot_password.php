<?php
// Forgot Password Request Page
// Users enter their email to request a password reset token
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Assets/login.css">
    <link rel="icon" type="image/png" href="../Images/logo.jpg"/>
    <style>
        .login-card input[type="email"] {
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
        .login-card input[type="email"]::placeholder {
            color: rgba(255,255,255,0.6);
        }
        .login-card input[type="email"]:focus {
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
            <h1>Forgot Password</h1>
            <p style="font-size: 13px; margin-bottom: 20px; color: rgba(255,255,255,0.8);">Enter your email to receive a password reset link (valid for 1 hour)</p>
            
            <form id="forgotForm">
                <label>Email or Username</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="Enter your email"
                    required
                >
                
                <button type="submit">Send Reset Link</button>
            </form>
            
            <div id="msg"></div>
            
            <div class="forgot-link">
                <a href="../index.php">← Back to Login</a>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $('#forgotForm').on('submit', function(e){
            e.preventDefault();
            const email = $('#email').val();
            $('#msg').html('').removeClass();
            
            $.post('api_forgot_password.php', { email: email }, function(resp){
                try {
                    const j = typeof resp === 'object' ? resp : JSON.parse(resp);
                    if (j.success) {
                        $('#msg').addClass('alert alert-success').html(j.message || 'Check your email for reset link');
                        $('#forgotForm')[0].reset();
                    } else {
                        $('#msg').addClass('alert alert-danger').html(j.message || 'Failed to send reset link');
                    }
                } catch(e) {
                    $('#msg').addClass('alert alert-danger').html('Unexpected response');
                }
            }).fail(function(){
                $('#msg').addClass('alert alert-danger').html('Request failed');
            });
        });
    </script>
</body>
</html>
