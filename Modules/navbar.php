<?php
    // Get the base path for absolute URLs
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . '/Pilot-run';
?>

<!-- Navigation Bar -->
<nav class="custom-navbar">

    <div class="nav-left">
        <div class="logo-circle">
            <img src="<?php echo $baseUrl; ?>/Images/logo.jpg" alt="Logo">
        </div>
        <span class="company-name">Chengshi <br>Construction Corp</span>
    </div>

    <div class="nav-right">
        <button class="avatar" onclick="toggleMenu()">
            <img src="<?php echo $baseUrl; ?>/Images/profilepic.jpg" alt="User">
        </button>

        <div id="profileMenu" class="dropdown-menu">

            <div class="profile-header">
                <img src="<?php echo $baseUrl; ?>/Images/profilepic.jpg">
                <span>User</span>
            </div>

            <a href="#" class="profile-item"> Settings & Privacy </a>
            <a href="#" class="profile-item"> Help & Support </a>
            <a href="<?php echo $baseUrl; ?>/Modules/logout_process.php" class="profile-item" onclick="return handleLogout(event);"> Logout </a>

        </div>

    </div>
</nav>

<!-- Include Universal Logout Handler Script -->
<script src="<?php echo $baseUrl; ?>/Modules/universal_logout_handler.js"></script>