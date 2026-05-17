<?php
session_start();

if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    $empType = $_SESSION['empType'] ?? '';

    if ($empType === 'HR') {
        header('Location: ./HR');
        exit;
    }

    if ($empType === 'Emp') {
        header('Location: ./Emp');
        exit;
    }

    session_unset();
    session_destroy();
}

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
?>
<!DOCTYPE html>
<html lang="en"> 
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>

        <!-- Bootstrap -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Google Font -->
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

        <!-- Your CSS -->
        <link rel="stylesheet" href="Assets/login.css">

        <!-- Company Logo -->
        <link rel="icon" type="image/png" href="Images/logo.jpg"/>

        <!-- Scripts -->
        <script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="Assets/Login.js" defer></script>
        <script src="Assets/Event_functions.js" defer></script>

    </head>

    <body>

        <div class="bg-container">
            <img src="./Images/bgimg.jpg" class="bg-image">
            <div class="overlay"></div>
        </div>

        <div class="login-wrapper">
            <div class="login-card">
                <h1>LOGIN ACCOUNT</h1>
                <form id="formlogin">
                    <label>Username</label>
                    <input type="text" name="username" id="userName">
                    <label>Password</label>
                    <input type="password" name="password" id="passWord">
                    <div class="remember">
                        <input type="checkbox" name="rememberMe" id="rememberMe">
                        <span>Remember me.</span>
                    </div>
                    <button type="button" id="loginBtn" onclick="login()">LOGIN</button>
                    <div class="forgot">
                        <a href="Features/forgot_password.php">Forgot your password?</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Notification Modal -->
        <div id="notificationModal" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Notification</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p id="notificationMessage">Your notification message here.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

    </body>
</html>