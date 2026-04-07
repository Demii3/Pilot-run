<?php
    session_start();
    if (!isset($_SESSION['login']) || $_SESSION['type'] != "HR") {
        header("location: ../");
        exit();
    }

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

    // Handle CRUD operations
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['create'])) {
            $firstname = $_POST['firstname'];
            $lastname = $_POST['lastname'];
            $department = $_POST['department'];

            $stmt = $conn->prepare("INSERT INTO employee (Firstname, Lastname, Department) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $firstname, $lastname, $department);
            $stmt->execute();
            $stmt->close();
            header("Location: employee_management.php");
            exit;
        }

        if (isset($_POST['update'])) {
            $emp_id = $_POST['emp_id'];
            $firstname = $_POST['firstname'];
            $lastname = $_POST['lastname'];
            $department = $_POST['department'];

            $stmt = $conn->prepare("UPDATE employee SET Firstname = ?, Lastname = ?, Department = ? WHERE Emp_id = ?");
            $stmt->bind_param("sssi", $firstname, $lastname, $department, $emp_id);
            $stmt->execute();
            $stmt->close();
            header("Location: employee_management.php");
            exit;
        }

        if (isset($_POST['delete'])) {
            $emp_id = $_POST['emp_id'];

            $stmt = $conn->prepare("DELETE FROM employee WHERE Emp_id = ?");
            $stmt->bind_param("i", $emp_id);
            $stmt->execute();
            $stmt->close();
            header("Location: employee_management.php");
            exit;
        }
    }

    // Get employee data for editing
    $edit_employee = null;
    if (isset($_GET['edit'])) {
        $edit_id = $_GET['edit'];
        $stmt = $conn->prepare("SELECT * FROM employee WHERE Emp_id = ?");
        $stmt->bind_param("i", $edit_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $edit_employee = $result->fetch_assoc();
        $stmt->close();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management - HR Dashboard</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <!-- Your CSS -->
    <link rel="stylesheet" href="../../Assets/home_hr.css">

    <!-- Company Logo -->
    <link rel="icon" type="image/png" href="../../Images/logo.jpg"/>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <?php include '../../Modules/background.php'; ?>
    <?php include '../../Modules/navbar.php'; ?>

    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0">Employee Management</h3>
                    </div>
                    <div class="card-body">
                        <!-- Add New Employee Button -->
                        <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                            <i class="fas fa-plus"></i> Add New Employee
                        </button>

                        <!-- Add Work Location Button -->
                        <button type="button" class="btn btn-info mb-3 ms-2" onclick="window.location='#';">
                            <a href="./geofence" class="icon-item">
                                Add work location
                            </a>
                        </button>

                        <!-- Employees Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Employee ID</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Department</th>
                                        <th>Place of Work</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $result = $conn->query("SELECT * FROM employee ORDER BY Emp_id");
                                        if ($result && $result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>" . $row['Emp_id'] . "</td>";
                                                echo "<td>" . htmlspecialchars($row['Firstname']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['Lastname']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['Department']) . "</td>";
                                                echo "<td></td>";
                                                echo "<td>";
                                                echo "<a href='?edit=" . $row['Emp_id'] . "' class='btn btn-warning btn-sm me-2'>Edit</a>";
                                                echo "<form method='POST' style='display:inline;' onsubmit='return confirm(\"Are you sure you want to delete this employee?\")'>";
                                                echo "<input type='hidden' name='emp_id' value='" . $row['Emp_id'] . "'>";
                                                echo "<button type='submit' name='delete' class='btn btn-danger btn-sm'>Delete</button>";
                                                echo "</form>";
                                                echo "</td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='5' class='text-center'>No employees found.</td></tr>";
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Employee Modal -->
    <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEmployeeModalLabel">Add New Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="firstname" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstname" name="firstname" required>
                        </div>
                        <div class="mb-3">
                            <label for="lastname" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastname" name="lastname" required>
                        </div>
                        <div class="mb-3">
                            <label for="department" class="form-label">Department</label>
                            <select class="form-control" id="department" name="department" required>
                                <option value="">Select Department</option>
                                <option value="HR">HR</option>
                                <option value="Project Manager">Project Manager</option>
                                <option value="System Admin">System Admin</option>
                                <option value="IT">IT</option>
                                <option value="Finance">Finance</option>
                                <option value="Marketing">Marketing</option>
                                <option value="Operations">Operations</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="create" class="btn btn-primary">Add Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Employee Modal -->
    <?php if ($edit_employee): ?>
    <div class="modal fade show" id="editEmployeeModal" tabindex="-1" style="display: block;" aria-labelledby="editEmployeeModalLabel" aria-hidden="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editEmployeeModalLabel">Edit Employee</h5>
                    <a href="employee_management.php" class="btn-close" aria-label="Close"></a>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="emp_id" value="<?php echo $edit_employee['Emp_id']; ?>">
                        <div class="mb-3">
                            <label for="edit_firstname" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="edit_firstname" name="firstname" value="<?php echo htmlspecialchars($edit_employee['Firstname']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_lastname" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="edit_lastname" name="lastname" value="<?php echo htmlspecialchars($edit_employee['Lastname']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_department" class="form-label">Department</label>
                            <select class="form-control" id="edit_department" name="department" required>
                                <option value="">Select Department</option>
                                <option value="HR" <?php echo ($edit_employee['Department'] == 'HR') ? 'selected' : ''; ?>>HR</option>
                                <option value="Project Manager" <?php echo ($edit_employee['Department'] == 'Project Manager') ? 'selected' : ''; ?>>Project Manager</option>
                                <option value="System Admin" <?php echo ($edit_employee['Department'] == 'System Admin') ? 'selected' : ''; ?>>System Admin</option>
                                <option value="IT" <?php echo ($edit_employee['Department'] == 'IT') ? 'selected' : ''; ?>>IT</option>
                                <option value="Finance" <?php echo ($edit_employee['Department'] == 'Finance') ? 'selected' : ''; ?>>Finance</option>
                                <option value="Marketing" <?php echo ($edit_employee['Department'] == 'Marketing') ? 'selected' : ''; ?>>Marketing</option>
                                <option value="Operations" <?php echo ($edit_employee['Department'] == 'Operations') ? 'selected' : ''; ?>>Operations</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="employee_management.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" name="update" class="btn btn-primary">Update Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    <?php endif; ?>

    <!-- Session Check Script -->
    <script>
        $(document).ready(function(){
            $.get('../Modules/check_session.php', function(data){
                if(data == '0'){
                    window.location = '../';
                }
            });
        });
    </script>

</body>
</html>