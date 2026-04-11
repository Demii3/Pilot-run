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
  <link rel="stylesheet" href="./employee_module.css">

  <link rel="icon" type="image/png" href="../../Images/logo.jpg"/>

  <script src="./employee-crud.js"></script>
</head>

<body>

<!-- Navigation Bar -->
<nav class="custom-navbar">

  <div class="nav-left">
    <a class="logo-circle" href="../index.php" aria-label="Go to Home">
      <img src="../../Images/logo.jpg" alt="Logo">
    </a>
    <span class="company-name">Chengshi <br>Construction Corp</span>
  </div>

  <div class="nav-right">
    <button class="avatar" onclick="toggleMenu()">
      <img src="../../Images/profilepic.jpg" alt="User">
    </button>

    <div id="profileMenu" class="dropdown-menu">

      <div class="profile-header">
        <img src="../../Images/profilepic.jpg" alt="User">
        <span>User</span>
      </div>

      <a href="#" class="profile-item"> Settings & Privacy </a>
      <a href="#" class="profile-item"> Help & Support </a>
      <a href="#" class="profile-item"> Logout </a>

    </div>

  </div>
</nav>


<div class="employee-container">

  <!-- Sidebar -->
  <div class="sidebar">
    <h2>Employee Management</h2>

    <button onclick="showContent(this,'employees')">Manage Employees</button>
    <button onclick="showContent(this,'sites')">Manage Sites</button>

  </div>

  <!-- Content -->
  <div class="content" id="content-area">
    <div class="card">
      <h2>Employee Module</h2>
      <p>Select a function from the left sidebar.</p>
 
    </div>
  </div>

</div>



  <!-- Background -->
<div class="bg-container">
    <img src="../../Images/bgimg.jpg" class="bg-image">
    <div class="overlay"></div>
</div>


<!-- JAVA RICE -->
<script>
  const sidebarButtons = document.querySelectorAll('.sidebar button');

  function toggleMenu() {
    document.getElementById("profileMenu").classList.toggle("active");
  }

  document.addEventListener("click", function(e) {
    const menu = document.getElementById("profileMenu");
    const avatar = document.querySelector(".avatar");

    if (!avatar.contains(e.target) && !menu.contains(e.target)) {
      menu.classList.remove("active");
    }
  });


/* Content */


  function showContent(button, section) {

  sidebarButtons.forEach(btn => {
    btn.classList.remove("active");
  });

  button.classList.add("active");

  const content = document.getElementById("content-area");

  if (section === "employees") {
    fetch('employees.php')
      .then(response => {
        if (!response.ok) throw new Error("Network response not OK");
        return response.text();
      })
      .then(html => {
        content.innerHTML = html;
        const modal = document.getElementById('employeeModal');
        if (modal && modal.parentElement !== document.body) {
          document.body.appendChild(modal);
        }
        // Call displayEmployees after content is loaded
        if (typeof displayEmployees === 'function') {
          displayEmployees();
        }
        // Add form submit event listener
        const employeeForm = document.getElementById('employeeForm');
        if (employeeForm) {
          employeeForm.addEventListener('submit', function(event) {
            event.preventDefault();
            saveEmployee();
          });
        }
      })
      .catch(err => {
        content.innerHTML = "<p>Error loading content</p>";
        console.error(err);
      });

  } else if (section === "sites") {
    window.location.href = 'geofence/sites.php';
    return;
  }

}

  document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    const section = params.get('section');

    if (!section) {
      return;
    }

    const targetButton = Array.from(sidebarButtons).find(function(btn) {
      return btn.getAttribute('onclick') && btn.getAttribute('onclick').includes("'" + section + "'");
    });

    if (targetButton) {
      showContent(targetButton, section);
    }
  });

</script>


</body>
</html>