<?php
// OTP Verification Page
// Users verify the OTP sent to their email
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Assets/login.css">
    <link rel="icon" type="image/png" href="../Images/logo.jpg"/>
    <style>
        .otp-input-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin: 20px 0;
        }
        .otp-input {
            width: 45px;
            height: 45px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 8px;
            background: rgba(255,255,255,0.1);
            color: white;
        }
        .otp-input:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 10px rgba(76,175,80,0.5);
        }
        .resend-container {
            text-align: center;
            margin-top: 15px;
        }
        .resend-btn {
            background: none;
            border: none;
            color: #4CAF50;
            cursor: pointer;
            text-decoration: underline;
            font-size: 14px;
        }
        .resend-btn:disabled {
            color: rgba(76,175,80,0.5);
            cursor: not-allowed;
        }
        .timer {
            font-size: 12px;
            color: black;
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
            <h1>Verify OTP</h1>
            <p style="font-size: 13px; margin-bottom: 20px; color: rgba(255,255,255,0.8);">
                Enter the One-Time Password sent to your email (valid for 10 minutes)
            </p>
            
            <?php 
            $email = htmlspecialchars($_GET['email'] ?? '');
            if ($email):
            ?>
            <div style="background: rgba(76,175,80,0.1); padding: 10px; border-radius: 4px; margin-bottom: 15px; border-left: 3px solid #4CAF50;">
                <small style="color: #aaa;">Verifying OTP for: <strong><?php echo $email; ?></strong></small>
            </div>
            <?php endif; ?>
            
            <form id="otpForm">
                <label>One-Time Password (OTP)</label>
                <div class="otp-input-group">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" required>
                </div>
                
                <input type="hidden" id="email" name="email" value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>">
                
                <button type="submit">Verify OTP</button>
            </form>
            
            <div id="msg"></div>
            
            <div class="resend-container">
                <button type="button" class="resend-btn" id="resendBtn" disabled>
                    Resend OTP <span class="timer">(<span id="timer">120</span>s)</span>
                </button>
            </div>
            
            <div class="forgot">
                <a href="forgot_password.php">← Back</a>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // OTP input handling
        const otpInputs = document.querySelectorAll('.otp-input');
        
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', function() {
                if (this.value && /[0-9]/.test(this.value)) {
                    if (index < otpInputs.length - 1) {
                        otpInputs[index + 1].focus();
                    }
                } else {
                    this.value = '';
                }
            });
            
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });
        });
        
        // Resend OTP timer
        let resendTimer = 120;
        const resendBtn = document.getElementById('resendBtn');
        const timerSpan = document.getElementById('timer');
        
        const countdownInterval = setInterval(() => {
            resendTimer--;
            timerSpan.textContent = resendTimer;
            
            if (resendTimer <= 0) {
                clearInterval(countdownInterval);
                resendBtn.disabled = false;
            }
        }, 1000);
        
        // Form submission
        $('#otpForm').on('submit', function(e){
            e.preventDefault();
            
            const otp = Array.from(otpInputs).map(input => input.value).join('');
            const email = $('#email').val();
            
            console.log('Submitting OTP:', { email: email, otp: otp });
            
            if (otp.length !== 6) {
                $('#msg').addClass('alert alert-danger').html('Please enter a valid 6-digit OTP');
                return;
            }
            
            if (!email) {
                $('#msg').addClass('alert alert-danger').html('Email is missing. Please go back and try again.');
                return;
            }
            
            $('#msg').html('').removeClass();
            
            $.post('api_verify_otp.php', { email: email, otp: otp }, function(resp){
                try {
                    const j = typeof resp === 'object' ? resp : JSON.parse(resp);
                    console.log('OTP Verification Response:', j);
                    
                    if (j.success) {
                        // Redirect to password reset page
                        window.location.href = 'forgot_password_reset.php?session_id=' + encodeURIComponent(j.session_id);
                    } else {
                        let message = j.message || 'Failed to verify OTP';
                        if (j.debug_info && Array.isArray(j.debug_info)) {
                            message += '<br><small style="color: #ffc107; margin-top: 10px; display: block;">Debug: ' + j.debug_info.join('; ') + '</small>';
                        } else if (j.debug) {
                            message += '<br><small style="color: #aaa;">Debug: ' + j.debug + '</small>';
                        }
                        $('#msg').addClass('alert alert-danger').html(message);
                    }
                } catch(e) {
                    console.error('Response parsing error:', e);
                    $('#msg').addClass('alert alert-danger').html('Unexpected response: ' + e.message);
                }
            }).fail(function(xhr, status, error){
                console.error('Request failed:', error);
                $('#msg').addClass('alert alert-danger').html('Request failed: ' + error);
            });
        });
        
        // Resend OTP
        $('#resendBtn').on('click', function(){
            const email = $('#email').val();
            
            $.post('api_forgot_password.php', { email: email }, function(resp){
                try {
                    const j = typeof resp === 'object' ? resp : JSON.parse(resp);
                    if (j.success) {
                        $('#msg').addClass('alert alert-success').html('OTP resent to your email');
                        resendTimer = 120;
                        resendBtn.disabled = true;
                        timerSpan.textContent = resendTimer;
                        
                        const interval = setInterval(() => {
                            resendTimer--;
                            timerSpan.textContent = resendTimer;
                            if (resendTimer <= 0) {
                                clearInterval(interval);
                                resendBtn.disabled = false;
                            }
                        }, 1000);
                    } else {
                        $('#msg').addClass('alert alert-danger').html(j.message || 'Failed to resend OTP');
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
