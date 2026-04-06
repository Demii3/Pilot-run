<?php
    $imagePrefix = '../';
    if (strpos($_SERVER['REQUEST_URI'], '/HR/Employee_manage/') !== false) {
        $imagePrefix = '../../';
    }
?>

<!-- Navigation Bar -->
<nav class="custom-navbar">

    <div class="nav-left">
        <div class="logo-circle">
            <img src="<?php echo $imagePrefix; ?>Images/logo.jpg" alt="Logo">
        </div>
        <span class="company-name">Chengshi <br>Construction Corp</span>
    </div>

    <div class="nav-right">
        <button class="avatar" onclick="toggleMenu()">
            <img src="<?php echo $imagePrefix; ?>Images/profilepic.jpg" alt="User">
        </button>

        <div id="profileMenu" class="dropdown-menu">

            <div class="profile-header">
                <img src="<?php echo $imagePrefix; ?>Images/profilepic.jpg">
                <span>User</span>
            </div>

            <a href="#" class="profile-item"> Settings & Privacy </a>
            <a href="#" class="profile-item"> Help & Support </a>
            <a href="<?php echo $imagePrefix; ?>?logout=logout" class="profile-item"> Logout </a>

        </div>

    </div>
</nav>