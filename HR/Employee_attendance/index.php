<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Attendance List</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/2.3.7/css/dataTables.dataTables.css" />
  <script src="https://code.jquery.com/jquery-4.0.0.js" integrity="sha256-9fsHeVnKBvqh3FB2HYu7g2xseAZ5MlN6Kz/qnkASV8U=" crossorigin="anonymous"></script>
  <script src="https://cdn.datatables.net/2.3.7/js/dataTables.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <link href="./Assets/Demetri.css" rel="stylesheet">
  <script src="./Assets/Demetri.js"></script>
</head>

<body>

<!-- Navigation Bar -->
<nav class="custom-navbar">

  <div class="nav-left">
    <a class="logo-circle" href="../" aria-label="Go to Home">
      <img src="./Images/logo.jpg" alt="Logo">
    </a>
    <span class="company-name">Chengshi <br>Construction Corp</span>
  </div>

  <div class="nav-right">
    <button class="avatar" onclick="toggleMenu()">
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


<div class="employee-container">

  <!-- Sidebar -->
  <div class="sidebar">
    <h2>Attendance</h2>

    <button class="active" href="./">View Attendance</button>

  </div>

  <!-- Content -->
  <div class="content" id="content-area">
    <div class="card">
      <h2>Attendance List</h2>
      
      <div class="mb-3">
        <input type="text" id="searchInput" class="form-control" placeholder="Search by name, department, date, location...">
      </div>

      <div class="table-responsive">
        <table id="attendanceTable" class="table table-hover">
          <thead class="table-dark">
              <tr>
                  <th class="hide-me">Attendance ID</th>
                  <th class="hide-me">Employee ID</th>
                  <th>Name</th>
                  <th>Department</th>
                  <th>Date</th>
                  <th>Location</th>
                  <th>Clock In</th>
                  <th>Clock In Status</th>
                  <th>Clock Out</th>
                  <th>Clock Out Status</th>
                  <th>Duration</th>
                  <th>AO</th>
              </tr>
          </thead>
          <tbody>
              <!-- DATA FROM DATABASE IS DISPLAYED HERE -->
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<div class="modal fade" id="attendanceModal" tabindex="-1" aria-labelledby="attendanceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="attendanceModalLabel">Attendance Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Name</label>
            <input id="modalName" type="text" class="form-control" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label">Department</label>
            <input id="modalDepartment" type="text" class="form-control" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label">Date</label>
            <input id="modalDate" type="text" class="form-control" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label">Location</label>
            <input id="modalLocation" type="text" class="form-control" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label">Clock In</label>
            <input id="modalClockIn" type="time" class="form-control" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label">Clock In Status</label>
            <input id="modalClockInStatus" type="text" class="form-control" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label">Clock Out</label>
            <input id="modalClockOut" type="time" class="form-control" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label">Clock Out Status</label>
            <input id="modalClockOutStatus" type="text" class="form-control" readonly>
          </div>
          <div class="col-md-6">
            <button id="editButton" class="btn btn-warning" onclick = "editAttendance()">Edit</button>
            <button id="saveButton" class="btn btn-success d-none">Save</button>
            <button id="deleteButton" class="btn btn-danger d-none">Delete</button>
          </div>
          <div class="col-md-6">
            <button id="absentButton" class="btn btn-danger d-none">Absent</button>
            <button id="onLeaveButton" class="btn btn-info d-none">On Leave</button>
<!--             <button id="saveButton" class="btn btn-success d-none">Save</button> -->
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Background -->
<div class="bg-container">
    <img src="./Images/bgimg.jpg" class="bg-image">
    <div class="overlay"></div>
</div>

</body>
</html>
