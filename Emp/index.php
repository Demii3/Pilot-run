<?php
    session_start();
    if (!isset($_SESSION['login']) || $_SESSION['type'] != "Emp") {
        header("location: ../");
        exit();
    };

    header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: post-check=0, pre-check=0', FALSE);
    header('Pragma: no-cache');
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Employee Dashboard</title>

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
	<link rel="stylesheet" href="./Assets/Employee_UI.css">

	<script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin ="anonymous"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="vendor/jquery/jquery.form.js"></script>
	<link rel="icon" type="image/png" href="./Images/logo.jpg"/>
	<script src="./Assets/Onclick_functions.js" defer></script>
	<script src="./Assets/Event_functions.js" defer></script>

</head>

<body>

<div class="bg-container">
	<img src="./Images/bgimg.jpg" class="bg-image" alt="Background">
	<div class="overlay"></div>
</div>

<nav class="custom-navbar">
	<div class="nav-left">
		<button class="sidebar-toggle" id="sidebarToggle" type="button" aria-label="Toggle sidebar">
			<i class="fas fa-bars"></i>
		</button>
		<a class="logo-circle" href="../HR/index.php" aria-label="Go to Home">
			<img src="./Images/logo.jpg" alt="Logo">
		</a>
		<span class="company-name">Chengshi <br>Construction Corp</span>
	</div>

	<div class="nav-right">
		<button class="avatar" type="button" aria-label="User profile">
			<img src="./Images/profilepic.jpg" alt="User">
		</button>

		<div id="profileMenu" class="dropdown-menu">
			<div class="profile-header">
				<img src="./Images/profilepic.jpg" alt="User">
				<span>User</span>
			</div>

			<a href="#" class="profile-item"> Settings & Privacy </a>
			<a href="#" class="profile-item"> Help & Support </a>
			<a href="#" class="profile-item"> Logout </a>
		</div>
	</div>
</nav>

<div class="payroll-container">
	<div class="sidebar">
		<h2>Home</h2>
        <hr>
		<button class="dropdown-btn" type="button">
			Payroll
		</button>
		<button class="dropdown-btn" type="button">
			Reports
		</button>
	</div>

    <div class="content" id="content-area">
        <div class="content-header">
            <h1>Employee Dashboard</h1>
            <div class="card">
                <h2>Home Module</h2>
                <p>Welcome, <span id="userName">User</span>!</p>
            </div>
        </div>

        <div class="content-body">
            <div class="welcome-card-container">
                <!-- Welcome Card -->
                <div class="welcome-card">
                    <div class="welcomecard-left">
                        <h2>Hello <span id="userName">User</span>!</h2>
                        <p>You are currently <span id="userStatus">logged in</span></p>
                        <button id="proceedToAttendance" class="btn btn-primary" type="button">
                            Proceed to Attendance
                        </button>
                    </div>
                </div>

                <div class="welcome-card">
                    <div class="welcomecard-right">
                        <p id="month"></p>
                        <p id="day"></p>
                        <p id="year"></p>
                        <h2 id="time"></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
	// Profile menu toggle
	function toggleMenu() {
		document.getElementById("profileMenu").classList.toggle("active");
	}

	// Close menu when clicking outside
	document.addEventListener("click", function(e) {
		const menu = document.getElementById("profileMenu");
		const avatar = document.querySelector(".avatar");

		if (!avatar.contains(e.target) && !menu.contains(e.target)) {
			menu.classList.remove("active");
		}
	});

	// Sidebar toggle for mobile
	const sidebarToggle = document.getElementById("sidebarToggle");
	const sidebar = document.querySelector(".sidebar");
	const contentArea = document.getElementById("content-area");

	if (sidebarToggle) {
		sidebarToggle.addEventListener("click", function() {
			sidebar.classList.toggle("active");
		});
	}

	// Close sidebar when clicking outside on mobile
	document.addEventListener("click", function(e) {
		if (window.innerWidth <= 768) {
			if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
				sidebar.classList.remove("active");
			}
		}
	});

	// Close sidebar on content click (mobile)
	if (contentArea) {
		contentArea.addEventListener("click", function() {
			if (window.innerWidth <= 768) {
				sidebar.classList.remove("active");
			}
		});
	}

	// Update date and time
	function updateDateTime() {
		const now = new Date();

		const month = now.toLocaleString('default', { month: 'long' });
		const day = now.getDate();
		const year = now.getFullYear();

		let hours = now.getHours();
		let minutes = now.getMinutes();
		let ampm = hours >= 12 ? 'PM' : 'AM';

		hours = hours % 12;
		hours = hours ? hours : 12; // 0 becomes 12
		minutes = minutes < 10 ? '0' + minutes : minutes;

		const time = hours + ":" + minutes + " " + ampm;

        if (document.getElementById("month")) {
            document.getElementById("month").textContent = month;
        };

        if (document.getElementById("day")) {
            document.getElementById("day").textContent = day;
        };

        if (document.getElementById("year")) {
            document.getElementById("year").textContent = year;
        };

        if (document.getElementById("time")) {
            document.getElementById("time").textContent = time;
        };
	};

	updateDateTime();
    setInterval(updateDateTime, 1000); // Update every second
</script>

</body>
</html>
