<?php
header('Content-Type: application/json');

include 'dbcon.php';

// Ensure the employees table exists.
$createTableSql = "CREATE TABLE IF NOT EXISTS `employees` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `position` VARCHAR(255) NOT NULL,
  `department` VARCHAR(255) NOT NULL,
  `salary` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
  `join_date` DATE NOT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'Active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

mysqli_query($dbc, $createTableSql);
if (mysqli_errno($dbc)) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($dbc)]);
    exit;
}

function tableExists($dbc, $name) {
    $name = mysqli_real_escape_string($dbc, $name);
    $result = mysqli_query($dbc, "SHOW TABLES LIKE '" . $name . "'");
    return $result && mysqli_num_rows($result) > 0;
}

function legacyEmployeeTableExists($dbc) {
    return tableExists($dbc, 'employee');
}

function migrateLegacyEmployeeTable($dbc) {
    if (!legacyEmployeeTableExists($dbc)) {
        return;
    }

    $countResult = mysqli_query($dbc, "SELECT COUNT(*) AS cnt FROM employees");
    $countRow = mysqli_fetch_assoc($countResult);
    if ($countRow && intval($countRow['cnt']) > 0) {
        return;
    }

    $result = mysqli_query($dbc, "SELECT Emp_id, Name, Position, Department, Salary, Status FROM employee");
    if (!$result) {
        return;
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $stmt = mysqli_prepare($dbc, "INSERT INTO employees (id, name, email, position, department, salary, join_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            continue;
        }

        $id = intval($row['Emp_id']);
        $name = $row['Name'];
        $email = '';
        $position = $row['Position'];
        $department = $row['Department'];
        $salary = floatval($row['Salary']);
        $joinDate = date('Y-m-d');
        $status = $row['Status'] ?: 'Active';

        mysqli_stmt_bind_param($stmt, 'isssdsss', $id, $name, $email, $position, $department, $salary, $joinDate, $status);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

migrateLegacyEmployeeTable($dbc);

function respond($success, $data = null, $message = '') {
    echo json_encode(['success' => $success, 'data' => $data, 'message' => $message]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id = intval($_GET['id']);
        $stmt = mysqli_prepare($dbc, "SELECT id, name, email, position, department, salary, DATE_FORMAT(join_date, '%Y-%m-%d') AS join_date, status FROM employees WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $employee = mysqli_fetch_assoc($result);
        respond(true, $employee ?? null);
    }

    $result = mysqli_query($dbc, "SELECT id, name, email, position, department, salary, DATE_FORMAT(join_date, '%Y-%m-%d') AS join_date, status FROM employees ORDER BY id ASC");
    $employees = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $employees[] = $row;
    }
    respond(true, $employees);
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    $name = trim($input['name'] ?? '');
    $email = trim($input['email'] ?? '');
    $position = trim($input['position'] ?? '');
    $department = trim($input['department'] ?? '');
    $salary = $input['salary'] ?? null;
    $joinDate = trim($input['joinDate'] ?? '');
    $status = trim($input['status'] ?? 'Active');

    if (!$name || !$email || !$position || !$department || $salary === null || $joinDate === '') {
        respond(false, null, 'Missing required employee fields.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond(false, null, 'Invalid email address.');
    }

    $salary = floatval($salary);
    $status = $status ?: 'Active';

    if (isset($input['id']) && is_numeric($input['id'])) {
        $id = intval($input['id']);
        $stmt = mysqli_prepare($dbc, "UPDATE employees SET name = ?, email = ?, position = ?, department = ?, salary = ?, join_date = ?, status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'ssssdssi', $name, $email, $position, $department, $salary, $joinDate, $status, $id);
        if (!mysqli_stmt_execute($stmt)) {
            respond(false, null, 'Database update failed: ' . mysqli_error($dbc));
        }
        respond(true, ['id' => $id, 'name' => $name, 'email' => $email, 'position' => $position, 'department' => $department, 'salary' => $salary, 'join_date' => $joinDate, 'status' => $status], 'Employee updated.');
    }

    $stmt = mysqli_prepare($dbc, "INSERT INTO employees (name, email, position, department, salary, join_date, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'ssssdss', $name, $email, $position, $department, $salary, $joinDate, $status);

    if (!mysqli_stmt_execute($stmt)) {
        respond(false, null, 'Database insert failed: ' . mysqli_error($dbc));
    }

    $insertedId = mysqli_insert_id($dbc);
    respond(true, ['id' => $insertedId, 'name' => $name, 'email' => $email, 'position' => $position, 'department' => $department, 'salary' => $salary, 'join_date' => $joinDate, 'status' => $status], 'Employee created.');
}

if ($method === 'DELETE') {
    parse_str(file_get_contents('php://input'), $input);
    if (!isset($input['id']) || !is_numeric($input['id'])) {
        respond(false, null, 'Missing employee id.');
    }

    $id = intval($input['id']);
    $stmt = mysqli_prepare($dbc, "DELETE FROM employees WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (!mysqli_stmt_execute($stmt)) {
        respond(false, null, 'Delete failed: ' . mysqli_error($dbc));
    }
    respond(true, null, 'Employee deleted.');
}

respond(false, null, 'Unsupported request method.');
