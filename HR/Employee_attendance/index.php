<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['type'] != "HR") {
    header("Location: ../");
    exit();
}

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
    <title>Attendance List</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../../Images/logo.jpg"/>
    <link rel="stylesheet" href="../HR_Assets/Employee_attendance.css">
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin ="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

</head>

<body>
    <?php include './Employee_attendance_modules/background.php'; ?>
    <div class="container py-5">
        <div class="attendance-header p-4 mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <h1 class="h3 mb-2" id = 'attendance-list'>Attendance List</h1>
                    <p class="text-muted mb-0">Track daily employee attendance and review status at a glance.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="../" class="btn btn-outline-secondary">Back to HR Dashboard</a>
                    <a href="./" class="btn btn-primary">Refresh</a>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="searchInput" class="form-label">Search employee</label>
                        <input id="searchInput" type="text" class="form-control" placeholder="Search by ID, name, or date">
                    </div>
                    <div class="col-md-3">
                        <label for="statusFilter" class="form-label">Status filter</label>
                        <select id="statusFilter" class="form-select">
                            <option value="all">All statuses</option>
                            <option value="Present">Present</option>
                            <option value="Absent">Absent</option>
                            <option value="Late">Late</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="dateFilter" class="form-label">Date filter</label>
                        <input id="dateFilter" type="date" class="form-control" value="2026-04-07">
                    </div>
                </div>

                <div class="d-flex justify-content-end mb-3">
                    <button id="bulkDeleteBtn" type="button" class="btn btn-danger" disabled>Delete Selected</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover attendance-table">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="d-none">Atttendance ID</th>
                                <th scope="col" class="d-none">Employee ID</th>
                                <th scope="col">Name</th>
                                <th scope="col" class="department-col">Department</th>
                                <th scope="col">Date</th>
                                <th scope="col">Location</th>
                                <th scope="col">Clock In </th>
                                <th scope="col" class="clkinStatus-col">Clockin Status</th>
                                <th scope="col">Clock Out</th>
                                <th scope="col" class="clkinStatus-col">Clockout Status</th>
                                <th scope="col">Duration</th>
                                <th scope="col" class="text-center">
                                    <input id="selectAllRows" type="checkbox" class="form-check-input" aria-label="Select all rows">
                                </th>
                            </tr>
                        </thead>
                        <tbody id="attendanceTable">
                            <?php
                                include '../../Modules/dbcon.php';
                                $query = "SELECT employee_attendance.*, employees.name, employees.department FROM `employee_attendance` JOIN `employees` ON employee_attendance.Emp_id = employees.id";
                                $result = mysqli_query($dbc, $query);
                                while ($row = mysqli_fetch_array($result)) {
                                        echo "<tr role='button' tabindex='0'>";
                                        echo "<td class='d-none'>" . $row['Attendance_id'] . "</td>";
                                        echo "<td class='d-none'>" . $row['Emp_id'] . "</td>";
                                        echo "<td>" . $row['name'] . "</td>";
                                        echo "<td class='department-col'>" . $row['department'] . "</td>";
                                        echo "<td>" . $row['Date'] . "</td>";
                                        echo "<td>" . $row['Location'] . "</td>";
                                        echo "<td>" . $row['Clock_in'] . "</td>";
                                        echo "<td class='clkinStatus-col'>" . $row['Clockin_status'] . "</td>";
                                        echo "<td>" . $row['Clock_out'] . "</td>";
                                        echo "<td class='clkinStatus-col'>" . $row['Clockout_status'] . "</td>";
                                        echo "<td>" . $row['Duration'] . "</td>";
                                        echo "<td class='text-center'><input type='checkbox' class='form-check-input row-select' aria-label='Select row for deletion'></td>";
                                        echo "</tr>";
                                }
                            ?>
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
                    <h5 class="modal-title" id="attendanceModalLabel">Attendance details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="row g-3">
                            <div class="col-md-6 d-none">
                                <label for="modalEmpId" class="form-label">Employee ID</label>
                                <input id="modalEmpId" type="text" class="form-control" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="modalName" class="form-label">Name</label>
                                <input id="modalName" type="text" class="form-control" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="modalDepartment" class="form-label">Department</label>
                                <input id="modalDepartment" type="text" class="form-control" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="modalDate" class="form-label">Date</label>
                                <input id="modalDate" type="text" class="form-control" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="modalLocation" class="form-label">Location</label>
                                <input id="modalLocation" type="text" class="form-control" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="modalClockIn" class="form-label">Clock In</label>
                                <div class="input-group">
                                    <input id="modalClockIn" type="time" class="form-control" readonly>
                                    <button type="button" id="NAbtn" class="btn btn-outline-secondary btn-sm" disabled>N/A</button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="modalClockOut" class="form-label">Clock Out</label>
                                <input id="modalClockOut" type="time" class="form-control" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="modalStatus" class="form-label">Clock-in Status</label>
                                <select id="modalStatus" class="form-select" disabled>
                                    <option value="Late">Late</option>
                                    <option value="On-time">On-time</option>
                                    <option value="On-leave">On-leave</option>
                                    <option value="Absent">Absent</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="modalClockoutStatus" class="form-label">Clock-out Status</label>
                                <select id="modalClockoutStatus" class="form-select" disabled>
                                    <option value="Over-time">Overtime</option>
                                    <option value="Under-time">Undertime</option>
                                    <option value="Absent">Absent</option>
                                    <option value="Present">Present</option>
                                </select>
                                <div id="overtimeDecisionWrapper" class="mt-2 d-none">
                                    <label for="modalOvertimeDecision" class="form-label">Overtime Decision</label>
                                    <select id="modalOvertimeDecision" class="form-select" disabled>
                                        <option value="Allowed">Allowed</option>
                                        <option value="Rejected">Rejected</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" id="editBtn" class="btn btn-warning">Edit</button>
                    <button type="button" id="saveBtn" class="btn btn-success d-none" data-bs-dismiss="modal">Save</button>
                    <button type="button" id="deleteBtn" class="btn btn-danger d-none">Delete</button>
                    <button type="button" id="closeBtn" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./Employee_attendance_assets/employee_attendance.js"></script>
</body>
</html>
