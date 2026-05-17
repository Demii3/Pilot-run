<?php
  session_start();
  if (!isset($_SESSION['login']) || $_SESSION['empType'] != "HR") {
    header("location: ../../");
    exit();
  }

  $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . '/Pilot-run';

  
  // Prevent caching to avoid showing logged-in content on back button
  header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
  header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private');
  header('Cache-Control: post-check=0, pre-check=0', FALSE);
  header('Pragma: no-cache');
  header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <title>Attendance List</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/2.3.7/css/dataTables.dataTables.css" />
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.2/css/buttons.dataTables.css" />
  <script src="https://code.jquery.com/jquery-4.0.0.js" integrity="sha256-9fsHeVnKBvqh3FB2HYu7g2xseAZ5MlN6Kz/qnkASV8U=" crossorigin="anonymous"></script>
  <script src="https://cdn.datatables.net/2.3.7/js/dataTables.js"></script>
  <script src="https://cdn.datatables.net/buttons/3.2.2/js/dataTables.buttons.js"></script>
  <script src="https://cdn.datatables.net/buttons/3.2.2/js/buttons.colVis.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/3.2.2/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/3.2.2/js/buttons.print.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
	<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

  <link href="./Assets/Demetri.css" rel="stylesheet">
  <script src="./Assets/Demetri.js"></script>
  <script src="./Assets/Time_functions.js"></script>
  <script src="./Assets/Database_communication.js"></script>
  <script src="./Assets/Onclick_functions.js"></script>
  <script src="../../Modules/universal_logout_handler.js"></script>
</head>

<body>

<!-- Navigation Bar -->
<nav class="custom-navbar">
  <input type="hidden" id="baseUrl" value="<?php echo $baseUrl; ?>">

  <div class="nav-left">
    <a class="logo-circle" href="../" aria-label="Go to Home">
      <img src="<?php echo $baseUrl; ?>/Images/logo.jpg" alt="Logo">
    </a>
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
      <a class="profile-item" onclick="logout()"> Logout </a>

    </div>

  </div>
</nav>

<script>
  // Set global attendance active status for logout handler
  window.isAttendanceActive = false; // HR users don't have attendance active status
  
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
</script>


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
      
      <div class="row g-2 mb-3 align-items-center">
        <div class="col-auto">
          <button type="button" class="settings-modal-trigger" data-bs-toggle="modal" data-bs-target="#optionsModal" onclick="event.stopPropagation();" aria-label="Open options modal">
            <i class="bi bi-gear-fill"></i>
          </button>
        </div>
        <div class="col-auto">
          <button type="button" class="settings-modal-trigger" data-bs-toggle="modal" data-bs-target="#createAttendanceModal" onclick="event.stopPropagation();" aria-label="Open create attendance modal">
            <i class="bi bi-plus-circle-fill"></i>
          </button>
        </div>
        <div class="col">
          <input type="text" id="searchInput" class="form-control" placeholder="Search by name, department, location...">
        </div>
        <div class="col-auto">
          <input type="date" id="searchDate" class="form-control" aria-label="Search by date">
        </div>
      </div>

      <div class="table-responsive">
        <table id="attendanceTable" class="table table-hover">
          <thead class="table-dark">
              <tr>
                  <th>Attendance ID</th>
                  <th>Employee ID</th>
                  <th>Name</th>
                  <th>Department</th>
                  <th>Date</th>
                  <th>Location</th>
                  <th>Clock In</th>
                  <th>CI Status</th>
                  <th>Clock Out</th>
                  <th>CO Status</th>
                  <th>Duration</th>
                  <th>AO</th>
                  <th>Work Day</th>
                  <th>User Location</th>
                  <th>In Geofence</th>
                  <th>Coordinates</th>
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
      <div class="modal-body" id="modalBody1">
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
            <input id="modalDate" type="date" class="form-control" readonly>
          </div>
          <div class="col-md-6">
            <div class="col-md-12 d-flex justify-content-between gap-2">
              <label class="form-label">Location</label>
              <button type="button" class="btn btn-sm btn-outline-primary" data-bs-target="#userLocModal" onclick="viewLocationOnMap()">
                View Location
              </button>
            </div>
            <input id="modalLocation" type="text" class="form-control" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label">Clock In</label>
            <input id="modalClockIn" type="time" class="form-control" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label">Clock In Status</label>
            <select id="modalClockInStatus" class="form-select" disabled>
              <option value="On-time">On-time</option>
              <option value="Late">Late</option>
              <option value="On-leave" disabled>On-leave</option>
              <option value="Absent" disabled>Absent</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Clock Out</label>
            <input id="modalClockOut" type="time" class="form-control" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label">Clock Out Status</label>
            <select id="modalClockOutStatus" class="form-select" disabled>
              <option value="Under-time">Under-time</option>
              <option value="Present">Present</option>
              <option value="Over-time" disabled>Over-time</option>
              <option value="On-leave" disabled>On-leave</option>
              <option value="Absent" disabled>Absent</option>
            </select>
            <div class="form-check mt-2" id="allowOvertimeContainer">
              <input class="form-check-input" type="checkbox" id="allowOvertime" disabled>
              <label class="form-check-label" for="allowOvertime">Allow Over-time</label>
            </div>
          </div>
          <div class="col-md-6">
              <label class="form-label">Work Classification</label>
              <select id="modalWorkClassification" class="form-select">
                <option value="R">Regular Day</option>
                <option value="SH">Special Holiday</option>
                <option value="LH">Legal Holiday</option>
                <option value="Custom">Custom</option>
              </select>
          </div>
          <div class="col-md-6 d-none">
            <label class="form-label">User Location</label>
            <input id="modalUserLoc" type="text" class="form-control" readonly>
          </div>
          <div class="col-md-6 d-none">
            <label class="form-label">Coordinates</label>
            <input id="modalCoordinates" type="text" class="form-control" readonly>
          </div>
          <!-- <div class="col-md-6">
              <label class="form-label">Duration</label>
              <input id="modalDuration" type="text" class="form-control" readonly>
              <div class="form-check mt-2" id="allowOvertimeContainer">
                <input class="form-check-input" type="checkbox" id="allowDurationConfig">
                <label class="form-check-label" for="allowOvertime">Manually Set Duration</label>
              </div>
          </div> -->
          <div class="col-md-6 d-flex justify-content-end gap-2" id="statusButtons">
            <button id="absentButton" class="btn btn-danger d-none" onclick="setAttendanceStatus('Absent')">Absent</button>
            <button id="onLeaveButton" class="btn btn-info d-none" onclick="setAttendanceStatus('On-Leave')">On Leave</button>
          </div>
          <div class="col-md-12 d-none" id="additionalSettings">
            <span><hr></span>
            <h5>Additional Settings</h5>
            <div class="d-flex align-items-center gap-2 mt-3">
              <div class="toggle-switch">
                <input type="checkbox" id="Manual-modify" class="toggle-input">
                <label for="Manual-modify" class="toggle-label"></label>
              </div>
              <label class="form-check-label mb-0" for="Manual-modify">Custom Attendance Edit</label>
              <span class="help-badge" tabindex="0" aria-label="Help about Override All Preset Functions">
                ?
                <span class="help-badge-tooltip" style="text-align: center;">
                  This is automatically enabled if "Override All Preset Functions" is enabled in the options menu. <hr>
                  This allows you to modify the current attendance record manually without any preset functions. <hr>
                </span>
              </span>
            </div>

            <div class="row g-3 mt-2">

              <div class="col-md-6 d-flex align-items-center gap-2">
                <div class="toggle-switch">
                  <input type="checkbox" id="Include-lunchbreak" class="toggle-input">
                  <label for="Include-lunchbreak" class="toggle-label"></label>
                </div>
                <label class="form-check-label mb-0" for="Include-lunchbreak">Include Lunch Break</label>
                <span class="help-badge" tabindex="0" aria-label="Help about Include Lunch Break">
                  ?
                  <span class="help-badge-tooltip" style="text-align: center;">
                    This is will include the lunch break duration in the total duration calculation.
                  </span>
                </span>
              </div>
            </div>

          </div>
        </div>
      </div>

      <div class="modal-footer d-flex justify-content-between">
        <div class="col-md-6 d-flex justify-content-start gap-2">
          <button id="editButton" class="btn btn-warning" onclick = "editAttendance()">Edit</button>
          <button id="deleteButton" class="btn btn-danger d-none">Delete</button>
          <button id="moreButton" class="btn btn-warning d-none" onclick="moreAttendance()">More</button>
        </div>
        <div class="col-md-5 d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="editAttendance()">Close</button>
          <button id="saveButton" class="btn btn-primary d-none">Save changes</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Create Attendance Modal (editable inputs) -->
<div class="modal fade" id="createAttendanceModal" tabindex="-1" aria-labelledby="createAttendanceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="createAttendanceModalLabel">Create Attendance</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">

          <div class="col-md-12">
            <div class="employee-search-wrapper">
              <input type="text" id="searchEmployeeInput" class="form-control" placeholder="Search employee by" aria-autocomplete="list" aria-controls="employeeSuggestionDropdown" aria-expanded="false">

              <!-- Container of employees from database (overlay, doesn't affect layout) -->
              <div id="employeeSuggestionDropdown" class="employee-suggestion-dropdown d-none" role="listbox" aria-label="Employee suggestions">#</div>
            </div>
            <span><hr></span>
          </div>

          <div class="col-md-6 d-none">
            <label class="form-label">Id</label>
            <input id="newModalId" type="text" class="form-control">
          </div>

          <div class="col-md-6">
            <label class="form-label">Name</label>
            <input id="newModalName" type="text" class="form-control" readonly>
          </div>

          <div class="col-md-6">
            <label class="form-label">Department</label>
            <input id="newModalDepartment" type="text" class="form-control" readonly>
          </div>

          <div class="col-md-6">
            <label class="form-label">Date</label>
            <input id="newModalDate" type="date" class="form-control">
          </div>

          <div class="col-md-6">
            <div class="employee-search-wrapper">
              <label class="form-label">Location</label>
              <input type="text" id="newModalLocation" class="form-control" placeholder="Search location" aria-autocomplete="list" aria-controls="locationSuggestionDropdown" aria-expanded="false">

              <!-- Container of employees location from database (overlay, doesn't affect layout) -->
              <div id="locationSuggestionDropdown" class="employee-suggestion-dropdown d-none" role="listbox" aria-label="Location suggestions">#</div>
            </div>
          </div>

          <div class="col-md-6 d-none">
            <label class="form-label">Location Coordinates</label>
            <input id="newModalLocationCoordinates" type="text" class="form-control">
          </div>

          <div class="col-md-6">
            <label class="form-label">Clock In</label>
            <input id="newModalClockIn" type="time" class="form-control">
          </div>

          <div class="col-md-6">
            <label class="form-label">Clock In Status</label>
            <select id="newModalClockInStatus" class="form-select">
              <option value="On-time">On-time</option>
              <option value="Late">Late</option>
              <option value="On-leave">On-leave</option>
              <option value="Absent">Absent</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Clock Out</label>
            <input id="newModalClockOut" type="time" class="form-control">
          </div>

          <div class="col-md-6">
            <label class="form-label">Clock Out Status</label>
            <select id="newModalClockOutStatus" class="form-select">
              <option value="Under-time">Under-time</option>
              <option value="Present">Present</option>
              <option value="Over-time">Over-time</option>
              <option value="On-leave">On-leave</option>
              <option value="Absent">Absent</option>
            </select>
            <div class="form-check mt-2" id="newAllowOvertimeContainer">
              <input class="form-check-input" type="checkbox" id="newAllowOvertime">
              <label class="form-check-label" for="newAllowOvertime">Allow Over-time</label>
            </div>
          </div>
          
          <div class="col-md-6">
              <label class="form-label">Work Classification</label>
              <select id="newModalWorkClassification" class="form-select">
                <option value="R">Regular Day</option>
                <option value="SH">Special Holiday</option>
                <option value="LH">Legal Holiday</option>
                <option value="Custom">Custom</option>
              </select>
          </div>

          <div class="modal-footer col-md-12 row g-3 justify-content-between">
            <div class= "col-md-6 d-flex justify-content-start gap-2">
              <button type="button" id="clearCreateAttendanceButton" class="btn btn-danger">Clear</button>
            </div>
            <div class= "col-md-5 d-flex justify-content-end gap-2">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button id="newSaveButton" class="btn btn-primary" onclick="saveAttendance()">Save</button>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="optionsModal" tabindex="-1" aria-labelledby="optionsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="optionsModalLabel">Options</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body" id="optionsModalBody">
        <div class="row g-3">

          <div class="col-md-12">
            <div class="d-flex align-items-center gap-2">
              <div class="toggle-switch">
                <input type="checkbox" id="overideAll" class="toggle-input">
                <label for="overideAll" class="toggle-label"></label>
              </div>
              <label class="form-check-label mb-0" for="overideAll">Override All Preset Functions</label>
              <span class="help-badge" tabindex="0" aria-label="Help about Override All Preset Functions">
                ?
                <span class="help-badge-tooltip" style="text-align: center;">This will disable all the preset functions employed by the developers
                  <hr> i.e selecting On-time in clock-in status will not automatically set the time value to 8:00 AM
                </span>
              </span>
            </div>
          </div>

          <div class="col-md-12">
            <span><hr></span>
            <span><h5>Display</h5></span>
          </div>

          <div class="col-md-3 d-flex align-items-center gap-3 mt-3">
            <div class="toggle-switch">
              <input type="checkbox" id="hideDepartment" class="toggle-input">
              <label for="hideDepartment" class="toggle-label"></label>
            </div>
            <label class="form-check-label" for="hideDepartment">Department</label>
          </div>

          <div class="col-md-3 d-flex align-items-center gap-3 mt-3">
            <div class="toggle-switch">
              <input type="checkbox" id="hideLocations" class="toggle-input">
              <label for="hideLocations" class="toggle-label"></label>
            </div>
            <label class="form-check-label" for="hideLocations">Locations</label>
          </div>

          <div class="col-md-3 d-flex align-items-center gap-3 mt-3">
            <div class="toggle-switch">
              <input type="checkbox" id="hideDuration" class="toggle-input">
              <label for="hideDuration" class="toggle-label"></label>
            </div>
            <label class="form-check-label" for="hideDuration">Duration</label>
          </div>

        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick='saveOptions()'>Save changes</button>
      </div>

    </div>
  </div>
</div>

<div id="userLocModal" class="modal fade" tabindex="-1" data-bs-backdrop="true" data-bs-keyboard="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Area and Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div id="userLocModalBody" class="modal-body">
              <span class="d-none">This attendance has no location data available</span>
              <!-- Map Location is stored here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
