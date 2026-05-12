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
            <p style="font-size: 13px; margin-bottom: 20px; color: rgba(255,255,255,0.8);">Enter your email or username to receive a One-Time Password (OTP)</p>
            
            <form id="forgotForm">
                <label>Email or Username</label>
                <input 
                    type="text" 
                    id="email" 
                    name="email" 
                    placeholder="Enter your email or username"
                    required
                >
                
                <button type="submit">Send OTP</button>
            </form>
            
            <div id="msg"></div>
            
            <div class="forgot">
                <a href="../index.php">← Back to Login</a>
            </div>
            
            <!-- Development mode button -->
            <div id="devProceedBtn" style="display: none; margin-top: 15px; text-align: center;">
                <button type="button" id="proceedBtn" style="width: 100%; padding: 10px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">
                    Proceed to OTP Entry →
                </button>
                <small style="color: #aaa; display: block; margin-top: 5px;">Email: <span id="showEmail"></span></small>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let devEmail = '';
        let actualEmail = '';
        
        $('#forgotForm').on('submit', function(e){
            e.preventDefault();
            const email = $('#email').val();
            const $submitBtn = $(this).find('button[type="submit"]');
            devEmail = email;
            $('#msg').html('').removeClass();
            
            // Disable button and show loading state
            $submitBtn.prop('disabled', true).text('Sending OTP...');
            
            $.post('api_forgot_password.php', { email: email }, function(resp){
                try {
                    const j = typeof resp === 'object' ? resp : JSON.parse(resp);
                    console.log('API Response:', j);
                    
                    if (j.success) {
                        // Store the actual email for later use
                        actualEmail = j.actual_email || email;
                        console.log('Actual Email set to:', actualEmail);
                        
                        let message = j.message || 'Check your email for OTP';
                        
                        // Development mode - show OTP
                        if (j.dev_mode && j.dev_otp) {
                            message = '<strong style="color: #ffc107;">⚠️ ' + j.dev_notice + '</strong><br><br>';
                            message += '<div style="background: rgba(76,175,80,0.2); padding: 15px; border-radius: 4px; margin: 15px 0; border: 2px solid #4CAF50;">';
                            message += '<strong style="color: #4CAF50; font-size: 18px;">Your OTP: ' + j.dev_otp + '</strong><br>';
                            message += '<small style="color: #aaa;">Use this code to proceed to the next step</small>';
                            message += '</div>';
                            
                            $('#msg').addClass('alert alert-success').html(message);
                            $('#showEmail').text(actualEmail);
                            $('#devProceedBtn').show();
                        } else {
                            // Not dev mode, redirect after showing message
                            $('#msg').addClass('alert alert-success').html(message);
                            setTimeout(() => {
                                window.location.href = 'forgot_password_verify_otp.php?email=' + encodeURIComponent(actualEmail);
                            }, 1500);
                        }
                    } else {
                        $('#msg').addClass('alert alert-danger').html(j.message || 'Failed to send OTP');
                        $submitBtn.prop('disabled', false).text('Send OTP');
                    }
                } catch(e) {
                    console.error('Error:', e);
                    $('#msg').addClass('alert alert-danger').html('Unexpected response: ' + e.message);
                    $submitBtn.prop('disabled', false).text('Send OTP');
                }
            }).fail(function(xhr, status, error){
                console.error('Request failed:', error);
                $('#msg').addClass('alert alert-danger').html('Request failed: ' + error);
                $submitBtn.prop('disabled', false).text('Send OTP');
            });
        });
        
        // Proceed button for development mode
        $('#proceedBtn').on('click', function(){
            if (!actualEmail) {
                alert('Email not retrieved. Please try again.');
                return;
            }
            console.log('Proceeding with email:', actualEmail);
            window.location.href = 'forgot_password_verify_otp.php?email=' + encodeURIComponent(actualEmail);
        });
    </script>
</body>
</html>
