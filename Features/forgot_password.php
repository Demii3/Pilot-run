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
</head>
<body>
    <div class="bg-container">
        <img src="../Images/bgimg.jpg" class="bg-image" alt="Background">
        <div class="overlay"></div>
    </div>

    <div class="login-wrapper">
        <div class="login-card">
            <h1>Forgot Password</h1>
            <p style="font-size: 13px; margin-bottom: 20px; color: rgba(255,255,255,0.8);">Enter your email or username to receive a password reset link (valid for 1 hour)</p>
            
            <form id="forgotForm">
                <label>Email or Username</label>
                <input 
                    type="text" 
                    id="email" 
                    name="email" 
                    placeholder="Enter your email or username"
                    required
                >
                
                <button type="submit">Send Reset Link</button>
            </form>
            
            <div id="msg"></div>
            
            <div class="forgot">
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
