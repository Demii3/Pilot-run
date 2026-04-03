<?php
ob_start(); // Start output buffering

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "employee_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to update an employee record
function updateEmployee($employee_id, $total_hours, $rate_per_hour, $special_holiday, $legal_holiday, $overtime_rate, $late_count, $absent_count, $cash_advance, $sss, $philhealth, $pagibig, $tax) {
    global $conn;
    // Calculate deductions
    $late_deduction = $late_count * $rate_per_hour;
    $absent_deduction = $absent_count * $rate_per_hour * 8; // Assuming 8 hours per day

    $stmt = $conn->prepare("UPDATE employees SET total_hours = ?, rate_per_hour = ?, special_holiday = ?, legal_holiday = ?, overtime_rate = ?, late = ?, absent = ?, cash_advance = ?, sss = ?, philhealth = ?, pagibig = ?, tax = ? WHERE employee_id = ?");
    $stmt->bind_param("ddddddddddddi", $total_hours, $rate_per_hour, $special_holiday, $legal_holiday, $overtime_rate, $late_deduction, $absent_deduction, $cash_advance, $sss, $philhealth, $pagibig, $tax, $employee_id);
    if (!$stmt->execute()) {
        error_log("Error: " . $stmt->error); // Log errors instead of echoing
    }
    $stmt->close();
}

// Function to delete an employee record
function deleteEmployee($employee_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM employees WHERE employee_id = ?");
    $stmt->bind_param("i", $employee_id);
    if (!$stmt->execute()) {
        error_log("Error: " . $stmt->error); // Log errors instead of echoing
    }
    $stmt->close();
}

// Function to create a new employee record
function createEmployee($employee_id, $total_hours, $rate_per_hour, $special_holiday, $legal_holiday, $overtime_rate, $late_count, $absent_count, $cash_advance, $sss, $philhealth, $pagibig, $tax) {
    global $conn;

    // Calculate deductions
    $late_deduction = $late_count * $rate_per_hour;
    $absent_deduction = $absent_count * $rate_per_hour * 8; // Assuming 8 hours per day

    $stmt = $conn->prepare("INSERT INTO employees (employee_id, total_hours, rate_per_hour, special_holiday, legal_holiday, overtime_rate, late, absent, cash_advance, sss, philhealth, pagibig, tax) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("idddddddddddd", $employee_id, $total_hours, $rate_per_hour, $special_holiday, $legal_holiday, $overtime_rate, $late_deduction, $absent_deduction, $cash_advance, $sss, $philhealth, $pagibig, $tax);
    if (!$stmt->execute()) {
        error_log("Error: " . $stmt->error); // Log errors instead of echoing
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management</title>
    <link rel="stylesheet" href="Computation_crude.css">
</head>
<body>
    <h1>Employee Management</h1>

    <div class="container">
        <!-- Form to create a new employee -->
        <div class="form-container">
            <h2>Create New Employee</h2>
            <form action="employee_management.php" method="POST">
                <input type="text" name="employee_id" placeholder="Employee ID" required>
                <input type="number" name="total_hours" placeholder="Total Hours" required>
                <input type="number" step="0.01" name="rate_per_hour" placeholder="Rate per Hour" required>
                <input type="number" step="0.01" name="special_holiday" placeholder="Special Holiday" required>
                <input type="number" step="0.01" name="legal_holiday" placeholder="Legal Holiday" required>
                <input type="number" step="0.01" name="overtime_rate" placeholder="Overtime Rate" required>
                <input type="number" step="1" name="late" placeholder="Number of Times Late" required>
                <input type="number" step="1" name="absent" placeholder="Number of Times Absent" required>
                <input type="number" step="0.01" name="cash_advance" placeholder="Cash Advance" required>
                <input type="number" step="0.01" name="sss" placeholder="SSS" required>
                <input type="number" step="0.01" name="philhealth" placeholder="PhilHealth" required>
                <input type="number" step="0.01" name="pagibig" placeholder="Pag-IBIG" required>
                <input type="number" step="0.01" name="tax" placeholder="Tax" required>
                <button type="submit" name="create" style="background-color: green; color: white;">Create Employee</button>
            </form>
        </div>

        <!-- Update Form (hidden by default, shown when "Edit" is clicked) -->
        <div id="updateForm" class="form-container" style="display: none;">
            <h2>Update Employee</h2>
            <form action="employee_management.php" method="POST">
                <input type="hidden" id="update_employee_id" name="employee_id">
                <input type="number" id="update_total_hours" name="total_hours" placeholder="Total Hours" required>
                <input type="number" step="0.01" id="update_rate_per_hour" name="rate_per_hour" placeholder="Rate per Hour" required>
                <input type="number" step="0.01" id="update_special_holiday" name="special_holiday" placeholder="Special Holiday" required>
                <input type="number" step="0.01" id="update_legal_holiday" name="legal_holiday" placeholder="Legal Holiday" required>
                <input type="number" step="0.01" id="update_overtime_rate" name="overtime_rate" placeholder="Overtime Rate" required>
                <input type="number" step="1" id="update_late" name="late" placeholder="Number of Times Late" required>
                <input type="number" step="1" id="update_absent" name="absent" placeholder="Number of Times Absent" required>
                <input type="number" step="0.01" id="update_cash_advance" name="cash_advance" placeholder="Cash Advance" required>
                <input type="number" step="0.01" id="update_sss" name="sss" placeholder="SSS" required>
                <input type="number" step="0.01" id="update_philhealth" name="philhealth" placeholder="PhilHealth" required>
                <input type="number" step="0.01" id="update_pagibig" name="pagibig" placeholder="Pag-IBIG" required>
                <input type="number" step="0.01" id="update_tax" name="tax" placeholder="Tax" required>
                <button type="submit" name="update" style="background-color: #ffc107; color: white;">Update Employee</button>
                <button type="button" onclick="cancelUpdate()" style="background-color: #dc3545; color: white;">Cancel</button>
            </form>
        </div>

        <!-- Table to display employees -->
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Total Hours</th>
                        <th>Rate per Hour</th>
                        <th>Special Holiday</th>
                        <th>Legal Holiday</th>
                        <th>Overtime Rate</th>
                        <th>Late</th>
                        <th>Absent</th>
                        <th>Cash Advance</th>
                        <th>SSS</th>
                        <th>PhilHealth</th>
                        <th>Pag-IBIG</th>
                        <th>Tax</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Handle form submissions
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        if (isset($_POST['create'])) {
                            createEmployee(
                                $_POST['employee_id'],
                                $_POST['total_hours'],
                                $_POST['rate_per_hour'],
                                $_POST['special_holiday'],
                                $_POST['legal_holiday'],
                                $_POST['overtime_rate'],
                                $_POST['late'],
                                $_POST['absent'],
                                $_POST['cash_advance'],
                                $_POST['sss'],
                                $_POST['philhealth'],
                                $_POST['pagibig'],
                                $_POST['tax']
                            );
                            header("Location: employee_management.php");
                            exit;
                        }

                        if (isset($_POST['update'])) {
                            updateEmployee(
                                $_POST['employee_id'],
                                $_POST['total_hours'],
                                $_POST['rate_per_hour'],
                                $_POST['special_holiday'],
                                $_POST['legal_holiday'],
                                $_POST['overtime_rate'],
                                $_POST['late'],
                                $_POST['absent'],
                                $_POST['cash_advance'],
                                $_POST['sss'],
                                $_POST['philhealth'],
                                $_POST['pagibig'],
                                $_POST['tax']
                            );
                            header("Location: employee_management.php");
                            exit;
                        }

                        if (isset($_POST['delete'])) {
                            deleteEmployee($_POST['employee_id']);
                            header("Location: employee_management.php");
                            exit;
                        }
                    }

                    // Add "Edit" and "Delete" button functionality in the employee list
                    $result = $conn->query("SELECT * FROM employees");
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['employee_id'] . "</td>";
                            echo "<td>" . $row['total_hours'] . "</td>";
                            echo "<td>" . $row['rate_per_hour'] . "</td>";
                            echo "<td>" . $row['special_holiday'] . "</td>";
                            echo "<td>" . $row['legal_holiday'] . "</td>";
                            echo "<td>" . $row['overtime_rate'] . "</td>";
                            echo "<td>" . $row['late'] . "</td>"; // Display late deduction
                            echo "<td>" . $row['absent'] . "</td>"; // Display absent deduction
                            echo "<td>" . $row['cash_advance'] . "</td>";
                            echo "<td>" . $row['sss'] . "</td>";
                            echo "<td>" . $row['philhealth'] . "</td>";
                            echo "<td>" . $row['pagibig'] . "</td>";
                            echo "<td>" . $row['tax'] . "</td>";
                            echo "<td>";
                            echo "<button class='btn btn-update' onclick='editEmployee(" . json_encode($row) . ")'>Edit</button>";
                            echo "<form action='employee_management.php' method='POST' style='display:inline;'>";
                            echo "<input type='hidden' name='employee_id' value='" . $row['employee_id'] . "'>";
                            echo "<button type='submit' name='delete' class='btn btn-delete'>Delete</button>";
                            echo "</form>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='14'>No employee records found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- JavaScript to handle "Edit" button click and populate the update form -->
    <script>
    function editEmployee(employee) {
        document.getElementById('update_employee_id').value = employee.employee_id;
        document.getElementById('update_total_hours').value = employee.total_hours;
        document.getElementById('update_rate_per_hour').value = employee.rate_per_hour;
        document.getElementById('update_special_holiday').value = employee.special_holiday;
        document.getElementById('update_legal_holiday').value = employee.legal_holiday;
        document.getElementById('update_overtime_rate').value = employee.overtime_rate;
        document.getElementById('update_late').value = '';
        document.getElementById('update_absent').value = '';
        document.getElementById('update_cash_advance').value = employee.cash_advance;
        document.getElementById('update_sss').value = employee.sss;
        document.getElementById('update_philhealth').value = employee.philhealth;
        document.getElementById('update_pagibig').value = employee.pagibig;
        document.getElementById('update_tax').value = employee.tax;

        document.getElementById('updateForm').style.display = 'block';
    }

    // JavaScript to handle canceling the update form
    function cancelUpdate() {
        document.getElementById('updateForm').style.display = 'none';
    }
    </script>
</body>
</html>

