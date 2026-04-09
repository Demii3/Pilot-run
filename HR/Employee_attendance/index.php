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

</head>
<body>
    <div class="container py-5">
        <div class="attendance-header p-4 mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <h1 class="h3 mb-2">Attendance List</h1>
                    <p class="text-muted mb-0">Track daily employee attendance and review status at a glance.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="../" class="btn btn-outline-secondary">Back to HR Dashboard</a>
                    <a href="index.php" class="btn btn-primary">Refresh</a>
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

                <div class="table-responsive">
                    <table class="table table-hover attendance-table">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="d-none">Employee ID</th>
                                <th scope="col">Name</th>
                                <th scope="col">Department</th>
                                <th scope="col">Date</th>
                                <th scope="col">Location</th>
                                <th scope="col">Clock In</th>
                                <th scope="col">Clock Out</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody id="attendanceTable">
                            <?php
                                include '../../Modules/dbcon.php';
                                $query = "SELECT * FROM employee";
                                $result = mysqli_query($dbc, $query);
                                while ($row = mysqli_fetch_array($result)) {
                                        echo "<tr role='button' tabindex='0'>";
                                        echo "<td class='d-none'>" . $row['Emp_id'] . "</td>";
                                        echo "<td>" . $row['Firstname'] . " " . $row['Lastname'] . "</td>";
                                        echo "<td>" . $row['Department'] . "</td>";
                                        echo "<td>--</td>";
                                        echo "<td>--</td>";
                                        echo "<td>--</td>";
                                        echo "<td>--</td>";
                                        echo "<td>--</td>";
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
                                <input id="modalLocation" type="text" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="modalClockIn" class="form-label">Clock In</label>
                                <input id="modalClockIn" type="text" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="modalClockOut" class="form-label">Clock Out</label>
                                <input id="modalClockOut" type="text" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="modalStatus" class="form-label">Status</label>
                                <input id="modalStatus" type="text" class="form-control" readonly>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const dateFilter = document.getElementById('dateFilter');
        const attendanceTable = document.getElementById('attendanceTable');

        function filterAttendance() {
            const searchValue = searchInput.value.toLowerCase();
            const statusValue = statusFilter.value;
            const dateValue = dateFilter.value;

            Array.from(attendanceTable.rows).forEach(row => {
                const cells = row.cells;
                // Get searchable text from Name (cells[1]) and Department (cells[2])
                const nameText = cells[1] ? cells[1].textContent.toLowerCase() : '';
                const deptText = cells[2] ? cells[2].textContent.toLowerCase() : '';

                const matchesSearch = searchValue === '' || nameText.includes(searchValue) || deptText.includes(searchValue);

                row.style.display = (matchesSearch) ? '' : 'none';
            });
        }

        searchInput.addEventListener('input', filterAttendance);
        statusFilter.addEventListener('change', filterAttendance);
        dateFilter.addEventListener('change', filterAttendance);

        function openRowModal(row) {
            const cells = Array.from(row.cells).map(cell => cell.textContent.trim());
            // Populate modal input fields with the selected row values
            document.getElementById('modalEmpId').value = cells[0] || '';
            document.getElementById('modalName').value = cells[1] || '';
            document.getElementById('modalDepartment').value = cells[2] || '';
            document.getElementById('modalDate').value = cells[3] || '';
            document.getElementById('modalLocation').value = cells[4] || '';
            document.getElementById('modalClockIn').value = cells[5] || '';
            document.getElementById('modalClockOut').value = cells[6] || '';
            document.getElementById('modalStatus').value = cells[7] || '';
            const attendanceModal = new bootstrap.Modal(document.getElementById('attendanceModal'));
            attendanceModal.show();
        }

        document.querySelectorAll('.attendance-table tbody tr[role="button"]').forEach(row => {
            row.addEventListener('click', () => openRowModal(row));
            // Keyboard activation for Enter and Space
            row.addEventListener('keydown', event => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    openRowModal(row);
                }
            });
        });
    </script>
</body>
</html>
