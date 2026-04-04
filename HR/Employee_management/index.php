<?php
    ob_start(); // Start output buffering

    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "simpletest_db";

    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Function to update an employee record
    include '../HR_Modules/functions.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management</title>
    <link rel="stylesheet" href="../HR_Assets/Computation_crude.css">
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
            <form action="index.php" method="POST">
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
                            header("Location: ./");
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
                            header("Location: ./");
                            exit;
                        }

                        if (isset($_POST['delete'])) {
                            deleteEmployee($_POST['employee_id']);
                            header("Location: ./");
                            exit;
                        }
                    }

                    // Add "Edit" and "Delete" button functionality in the employee list
                    include '../HR_Modules/display_all_employees.php';
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- JavaScript to handle "Edit" button click and populate the update form -->
    <?php include '../HR_Modules/populate_update_form.php'; ?>


</body>
</html>

