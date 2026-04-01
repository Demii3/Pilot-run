<?php
    session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

  <!-- CSS -->
  <link rel="stylesheet" href="../Assets/home_hr.css">

  <link rel="icon" type="image/png" href="../Images/logo.jpg"/>
</head>

<body>

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
      <a href="../?logout=true" class="profile-item"> Logout </a>

    </div>

  </div>
</nav>

<!-- Welcome Card -->
<div class="welcome-card">
  <div class="welcomecard-left">
    <img src="../Images/profilepic.jpg" class="profile-img">
    <div>
      <p class="welcome-text">Welcome!</p>
      <h4 class="user-name"><?php echo $_SESSION['username']; ?></h4>
    </div>
  </div>

  <div class="welcomecard-right">
    <p id="month"></p>
    <p id="day"></p>
    <p id="year"></p>
    <h2 id="time"></h2>
  </div>
</div>

<!-- functions card -->
<div class="functions-card">
    <div class="card-icons">

      <a href="#" class="icon-item">
        <img src="../Images/attendance.png" alt="Attendance">
        <p>Attendance</p>
      </a>

      <a href="#" class="icon-item">
        <img src="../Images/payslip.png" alt="Payslip">
        <p>Payslip</p>
      </a>

      <a href="#" class="icon-item">
        <img src="../Images/payroll.png" alt="Attendance">
        <p>Payroll</p>
      </a>

      <a href="#" class="icon-item">
        <img src="../Images/employees.png" alt="Payslip">
        <p>Employees</p>
      </a>
    </div>
</div>



  <!-- Background -->
<div class="bg-container">
    <img src="../Images/bgimg.jpg" class="bg-image">
    <div class="overlay"></div>
</div>


<!-- JAVA RICE -->
<?php
  include("ionknowdisdude.php");
?>

</body>
</html>