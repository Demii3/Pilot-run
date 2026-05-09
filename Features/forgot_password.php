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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .forgot-card {
            max-width: 400px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            border-radius: 10px;
        }
        .forgot-card .card-header {
            background-color: #667eea;
            color: white;
            border-radius: 10px 10px 0 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card forgot-card">
            <div class="card-header">
                <h4 class="mb-0">Forgot Password</h4>
            </div>
            <div class="card-body">
                <p class="text-muted small">Enter your email or username to receive a password reset link.</p>
                <form id="forgotForm">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email or Username</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="email" 
                            name="email" 
                            placeholder="user@example.com or username"
                            required
                        >
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
                </form>
                <div id="msg" class="mt-3"></div>
                <hr>
                <p class="text-center small">
                    <a href="../index.php">Back to Login</a>
                </p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $('#forgotForm').on('submit', function(e){
            e.preventDefault();
            const email = $('#email').val();
            $('#msg').removeClass().text('Sending...');
            
            $.post('api_forgot_password.php', { email: email }, function(resp){
                try {
                    const j = typeof resp === 'object' ? resp : JSON.parse(resp);
                    if (j.success) {
                        $('#msg').addClass('alert alert-success').text(j.message || 'Check your email for reset link');
                        $('#forgotForm')[0].reset();
                    } else {
                        $('#msg').addClass('alert alert-danger').text(j.message || 'Failed to send reset link');
                    }
                } catch(e) {
                    $('#msg').addClass('alert alert-danger').text('Unexpected response');
                }
            }).fail(function(){
                $('#msg').addClass('alert alert-danger').text('Request failed');
            });
        });
    </script>
</body>
</html>
