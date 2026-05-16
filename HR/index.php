<?php
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true || (($_SESSION['empType'] ?? $_SESSION['type'] ?? '') !== 'HR')) {
    header('Location: ../');
    exit;
}

$BASE_URL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . '/Pilot-run';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Expires" content="0">
        <title>HR Dashboard</title>

        <!-- Bootstrap -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Google Font -->
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

        <!-- Your CSS -->
        <link rel="stylesheet" href="../Assets/home_hr.css">

        <!-- Company Logo -->
        <link rel="icon" type="image/png" href="../Images/logo.jpg"/>

        <!-- Scripts -->
        <script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="<?php echo $BASE_URL; ?>/HR/HR_assets/Logout.js" defer></script>
    </head>

    <body>
        <div class="bg-container">
            <img src="../Images/bgimg.jpg" class="bg-image">
            <div class="overlay"></div>
        </div>
        <!-- Navigation Bar -->
        <nav class="custom-navbar">

            <div class="nav-left">
                <div class="logo-circle">
                    <img src="../Images/logo.jpg" alt="Logo">
                </div>
                <span class="company-name">Chengshi <br>Construction Corp</span>
            </div>

            <div class="nav-right">
                <button class="avatar" onclick="toggleMenu()">
                    <img src="../Images/profilepic.jpg" alt="User">
                </button>

                <div id="profileMenu" class="dropdown-menu">

                    <div class="profile-header">
                        <img src="../Images/profilepic.jpg">
                        <span>User</span>
                    </div>

                    <a href="#" class="profile-item"> Settings & Privacy </a>
                    <a href="#" class="profile-item"> Help & Support </a>
                    <a class="profile-item" onclick="logout()"> Logout </a>

                </div>

            </div>
        </nav>

        <div class="dashboard-layout">
            <aside class="side-dashboard">

                <div class="sidebar-title">DASHBOARD</div>

                <nav class="sidebar-nav">
                    <a href="./Employee_attendance" class="sidebar-link">
                        <span>Attendance</span>
                    </a>
                    <a href="./Employee_management" class="sidebar-link">
                        <span>Manage Employees</span>
                    </a>
                    <a href="./Employee_payroll" class="sidebar-link">
                        <span>Payroll</span>
                    </a>
                </nav>
            </aside>

            <main class="dashboard-main">

                <?php include '../Modules/welcome_card.php'; ?>

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


        <!-- JAVA RICE -->
        <?php include '../Modules/navbar_and_welcome_card_script.php'; ?>

    </body>
</html>