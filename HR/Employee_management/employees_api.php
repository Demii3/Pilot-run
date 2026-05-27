<?php
ob_start();
header('Content-Type: application/json');
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

include 'dbcon.php';
/** @var mysqli $dbc */

try {
// Ensure the employees table exists.
$createTableSql = "CREATE TABLE IF NOT EXISTS `employees` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `username` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `type` VARCHAR(50) NOT NULL DEFAULT 'Emp',
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

function columnExists($dbc, $table, $column) {
    $table = mysqli_real_escape_string($dbc, $table);
    $column = mysqli_real_escape_string($dbc, $column);
    $result = mysqli_query($dbc, "SHOW COLUMNS FROM `" . $table . "` LIKE '" . $column . "'");
    return $result && mysqli_num_rows($result) > 0;
}

function ensureEmployeeTableColumns($dbc) {
    if (!columnExists($dbc, 'employees', 'email')) {
        mysqli_query($dbc, "ALTER TABLE `employees` ADD COLUMN `email` VARCHAR(255) NOT NULL DEFAULT '' AFTER `name`");
    }
    if (!columnExists($dbc, 'employees', 'username')) {
        mysqli_query($dbc, "ALTER TABLE `employees` ADD COLUMN `username` VARCHAR(255) NOT NULL DEFAULT '' AFTER `email`");
    }
    if (!columnExists($dbc, 'employees', 'password')) {
        mysqli_query($dbc, "ALTER TABLE `employees` ADD COLUMN `password` VARCHAR(255) NOT NULL DEFAULT '' AFTER `username`");
    }
    if (!columnExists($dbc, 'employees', 'type')) {
        mysqli_query($dbc, "ALTER TABLE `employees` ADD COLUMN `type` VARCHAR(50) NOT NULL DEFAULT 'Emp' AFTER `password`");
    }
    if (!columnExists($dbc, 'employees', 'status')) {
        mysqli_query($dbc, "ALTER TABLE `employees` ADD COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'Active' AFTER `join_date`");
    }
}

ensureEmployeeTableColumns($dbc);

function tableExists($dbc, $name) {
    $name = mysqli_real_escape_string($dbc, $name);
    $result = mysqli_query($dbc, "SHOW TABLES LIKE '" . $name . "'");
    return $result && mysqli_num_rows($result) > 0;
}

function ensureUsersTable($dbc) {
    if (!tableExists($dbc, 'users')) {
        $createUsersSql = "CREATE TABLE IF NOT EXISTS `users` (
            `User_id` bigint(255) NOT NULL,
            `Username` varchar(255) NOT NULL,
            `Password` varchar(255) NOT NULL,
            `Type` varchar(255) NOT NULL,
            `Work_status` varchar(255) NOT NULL DEFAULT 'Tapped-out',
            PRIMARY KEY (`User_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        mysqli_query($dbc, $createUsersSql);
    }

    if (!columnExists($dbc, 'users', 'User_id')) {
        mysqli_query($dbc, "ALTER TABLE `users` ADD COLUMN `User_id` bigint(255) NOT NULL");
    }
    if (!columnExists($dbc, 'users', 'Username')) {
        mysqli_query($dbc, "ALTER TABLE `users` ADD COLUMN `Username` varchar(255) NOT NULL");
    }
    if (!columnExists($dbc, 'users', 'Password')) {
        mysqli_query($dbc, "ALTER TABLE `users` ADD COLUMN `Password` varchar(255) NOT NULL");
    }
    if (!columnExists($dbc, 'users', 'Type')) {
        mysqli_query($dbc, "ALTER TABLE `users` ADD COLUMN `Type` varchar(255) NOT NULL");
    }
    if (!columnExists($dbc, 'users', 'Work_status')) {
        mysqli_query($dbc, "ALTER TABLE `users` ADD COLUMN `Work_status` varchar(255) NOT NULL DEFAULT 'Tapped-out'");
    }
}

ensureUsersTable($dbc);

function ensureUserRow($dbc, $id, $username, $password, $type, $workStatus = 'Tapped-out') {
    $id = intval($id);
    $existingPassword = '';
    $workStatus = $workStatus ?: 'Tapped-out';

    $stmt = mysqli_prepare($dbc, "SELECT `User_id`, `Password`, `Work_status` FROM `users` WHERE `User_id` = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $existingPassword = $row['Password'];
            $workStatus = $row['Work_status'] ?: $workStatus;
            if ($password === null) {
                $password = $existingPassword;
            }
            mysqli_stmt_close($stmt);

            if ($password !== null && password_get_info($password)['algo'] === null) {
                $password = password_hash($password, PASSWORD_DEFAULT);
            }

            if ($password === $existingPassword && $existingPassword !== '' && password_get_info($existingPassword)['algo'] === null) {
                $password = password_hash($existingPassword, PASSWORD_DEFAULT);
            }

            $updateStmt = mysqli_prepare($dbc, "UPDATE `users` SET `Username` = ?, `Password` = ?, `Type` = ? WHERE `User_id` = ?");
            if ($updateStmt) {
                mysqli_stmt_bind_param($updateStmt, 'sssi', $username, $password, $type, $id);
                mysqli_stmt_execute($updateStmt);
                mysqli_stmt_close($updateStmt);
            }
            return;
        }
        mysqli_stmt_close($stmt);
    }

    if ($password === null) {
        $password = '';
    }

    if ($password !== '' && password_get_info($password)['algo'] === null) {
        $password = password_hash($password, PASSWORD_DEFAULT);
    }

    $insertStmt = mysqli_prepare($dbc, "INSERT INTO `users` (`User_id`, `Username`, `Password`, `Type`, `Work_status`) VALUES (?, ?, ?, ?, ?)");
    if ($insertStmt) {
        mysqli_stmt_bind_param($insertStmt, 'issss', $id, $username, $password, $type, $workStatus);
        mysqli_stmt_execute($insertStmt);
        mysqli_stmt_close($insertStmt);
    }
}

function fetchExistingAuthMap($dbc) {
    $byId = [];
    $byUsername = [];

    $result = mysqli_query($dbc, "SELECT `User_id`, `Username`, `Password`, `Work_status` FROM `users`");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $userId = intval($row['User_id']);
            $username = trim($row['Username'] ?? '');
            $byId[$userId] = $row;
            if ($username !== '') {
                $byUsername[strtolower($username)] = $row;
            }
        }
        mysqli_free_result($result);
    }

    return [$byId, $byUsername];
}

function normalizeImportValue($value) {
    return is_string($value) ? trim($value) : $value;
}

function normalizePasswordForStorage($password) {
    if ($password === null) {
        return null;
    }

    $password = (string)$password;
    if ($password === '') {
        return '';
    }

    if (password_get_info($password)['algo'] === null) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    return $password;
}

function findExistingEmployeeRow($dbc, $employee) {
    $username = trim($employee['username'] ?? '');
    $email = trim($employee['email'] ?? '');

    if ($email !== '') {
        $stmt = mysqli_prepare($dbc, "SELECT id, name, email, username, password, type, position, department, salary, DATE_FORMAT(join_date, '%Y-%m-%d') AS join_date, status FROM employees WHERE email = ? LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = $result ? mysqli_fetch_assoc($result) : null;
            mysqli_stmt_close($stmt);
            if ($row) {
                return $row;
            }
        }
    }

    if ($username !== '') {
        $stmt = mysqli_prepare($dbc, "SELECT id, name, email, username, password, type, position, department, salary, DATE_FORMAT(join_date, '%Y-%m-%d') AS join_date, status FROM employees WHERE username = ? LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = $result ? mysqli_fetch_assoc($result) : null;
            mysqli_stmt_close($stmt);
            if ($row) {
                return $row;
            }
        }
    }

    return null;
}

function importEmployeeRow($dbc, $employee, $existingPassword = null, $existingWorkStatus = null, $existingRow = null) {
    $name = trim($employee['name'] ?? '');
    $email = trim($employee['email'] ?? '');
    $username = trim($employee['username'] ?? '');
    $password = array_key_exists('password', $employee) ? $employee['password'] : '';
    $type = trim($employee['type'] ?? 'Emp');
    $position = trim($employee['position'] ?? '');
    $department = trim($employee['department'] ?? '');
    $salary = isset($employee['salary']) ? floatval($employee['salary']) : null;
    $joinDate = trim($employee['joinDate'] ?? $employee['join_date'] ?? '');
    $status = trim($employee['status'] ?? 'Active');

    if ($existingPassword !== null && $existingPassword !== '') {
        $password = $existingPassword;
    } elseif ($password === '') {
        $password = $username;
    }

    if (!$name || !$email || !$username || $password === '' || !$position || !$department || $salary === null || $joinDate === '') {
        throw new Exception('Imported file is missing required employee fields.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Imported file contains an invalid email address.');
    }

    $password = normalizePasswordForStorage($password);

    if ($existingRow && isset($existingRow['id']) && is_numeric($existingRow['id'])) {
        $existingId = intval($existingRow['id']);
        $existingPassword = $existingPassword !== null && $existingPassword !== '' ? $existingPassword : ($existingRow['password'] ?? null);
        $existingWorkStatus = $existingWorkStatus !== null && $existingWorkStatus !== '' ? $existingWorkStatus : null;

        if ($existingPassword !== null && $existingPassword !== '') {
            $password = $existingPassword;
        } elseif ($password === '') {
            $password = $existingRow['password'] ?? $username;
        }

        $password = normalizePasswordForStorage($password);

        $stmt = mysqli_prepare($dbc, "UPDATE employees SET name = ?, email = ?, username = ?, password = ?, type = ?, position = ?, department = ?, salary = ?, join_date = ?, status = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception('Failed to prepare employee update: ' . mysqli_error($dbc));
        }
        mysqli_stmt_bind_param($stmt, 'sssssssdssi', $name, $email, $username, $password, $type, $position, $department, $salary, $joinDate, $status, $existingId);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Failed to update employee: ' . mysqli_error($dbc));
        }
        mysqli_stmt_close($stmt);

        ensureUserRow($dbc, $existingId, $username, $password, $type, $existingWorkStatus ?: ($existingRow['Work_status'] ?? 'Tapped-out'));
        return ['id' => $existingId, 'action' => 'updated'];
    }

    $stmt = mysqli_prepare($dbc, "INSERT INTO employees (name, email, username, password, type, position, department, salary, join_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sssssssdss', $name, $email, $username, $password, $type, $position, $department, $salary, $joinDate, $status);

    if (!$stmt || !mysqli_stmt_execute($stmt)) {
        throw new Exception('Failed to import employee: ' . mysqli_error($dbc));
    }

    $employeeId = mysqli_insert_id($dbc);
    ensureUserRow($dbc, $employeeId, $username, $password, $type, $existingWorkStatus);
    return ['id' => $employeeId, 'action' => 'inserted'];
}

// Legacy migration is disabled here because `employees` is the source of truth.
// Existing data in `employee` should not repopulate `employees`.
// migrateLegacyEmployeeTable($dbc);

function respond($success, $data = null, $message = '') {
    if (ob_get_length()) {
        ob_end_clean();
    }
    echo json_encode(['success' => $success, 'data' => $data, 'message' => $message]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    if (($input['action'] ?? '') === 'replace_all') {
        respond(false, null, 'Replace-all import is disabled. Use upsert_many so existing employees are updated and new employees are added.');
    }

    if (in_array(($input['action'] ?? ''), ['upsert_many', 'import_merge'], true)) {
        $importedEmployees = $input['employees'] ?? [];
        if (!is_array($importedEmployees) || count($importedEmployees) === 0) {
            respond(false, null, 'No employees were provided for import.');
        }

        list($existingAuthById, $existingAuthByUsername) = fetchExistingAuthMap($dbc);

        mysqli_begin_transaction($dbc);
        try {
            $importedCount = 0;
            $updatedCount = 0;
            $insertedCount = 0;
            foreach ($importedEmployees as $employee) {
                if (!is_array($employee)) {
                    continue;
                }
                $existingRow = findExistingEmployeeRow($dbc, $employee);
                $authId = null;
                $authUsername = strtolower(trim($employee['username'] ?? ''));
                $existingPassword = null;
                $existingWorkStatus = null;

                if ($existingRow && isset($existingRow['id']) && is_numeric($existingRow['id'])) {
                    $authId = intval($existingRow['id']);
                } elseif (isset($employee['id']) && is_numeric($employee['id'])) {
                    $authId = intval($employee['id']);
                }

                if ($authId !== null && isset($existingAuthById[$authId])) {
                    $existingPassword = $existingAuthById[$authId]['Password'] ?? null;
                    $existingWorkStatus = $existingAuthById[$authId]['Work_status'] ?? null;
                } elseif ($authUsername !== '' && isset($existingAuthByUsername[$authUsername])) {
                    $existingPassword = $existingAuthByUsername[$authUsername]['Password'] ?? null;
                    $existingWorkStatus = $existingAuthByUsername[$authUsername]['Work_status'] ?? null;
                }

                $result = importEmployeeRow($dbc, $employee, $existingPassword, $existingWorkStatus, $existingRow);
                if (($result['action'] ?? '') === 'updated') {
                    $updatedCount++;
                } else {
                    $insertedCount++;
                }
                $importedCount++;
            }

            mysqli_commit($dbc);
            respond(true, ['imported' => $importedCount, 'updated' => $updatedCount, 'inserted' => $insertedCount], 'Employees imported successfully.');
        } catch (Throwable $e) {
            mysqli_rollback($dbc);
            respond(false, null, $e->getMessage());
        }
    }
}

if ($method === 'GET') {
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id = intval($_GET['id']);
        $stmt = mysqli_prepare($dbc, "SELECT id, name, email, username, password, type, position, department, salary, DATE_FORMAT(join_date, '%Y-%m-%d') AS join_date, status FROM employees WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $employee = mysqli_fetch_assoc($result);
        respond(true, $employee ?? null);
    }

    $result = mysqli_query($dbc, "SELECT id, name, email, username, type, position, department, salary, DATE_FORMAT(join_date, '%Y-%m-%d') AS join_date, status FROM employees ORDER BY id ASC");
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
    $username = trim($input['username'] ?? '');
    $password = isset($input['password']) ? $input['password'] : null;
    $type = trim($input['type'] ?? '');
    $position = trim($input['position'] ?? '');
    $department = trim($input['department'] ?? '');
    $salary = $input['salary'] ?? null;
    $joinDate = trim($input['joinDate'] ?? '');
    $status = trim($input['status'] ?? 'Active');

    if (!$name || !$email || !$username || (!$password && !isset($input['id'])) || !$type || !$position || !$department || $salary === null || $joinDate === '') {
        respond(false, null, 'Missing required employee fields.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond(false, null, 'Invalid email address.');
    }

    $salary = floatval($salary);
    $status = $status ?: 'Active';
    $type = $type ?: 'Emp';

    if (isset($input['id']) && is_numeric($input['id'])) {
        $id = intval($input['id']);
        $existingEmployeePassword = null;
        $existingStmt = mysqli_prepare($dbc, "SELECT `password` FROM `employees` WHERE `id` = ?");
        if ($existingStmt) {
            mysqli_stmt_bind_param($existingStmt, 'i', $id);
            mysqli_stmt_execute($existingStmt);
            $existingResult = mysqli_stmt_get_result($existingStmt);
            if ($existingResult && ($existingRow = mysqli_fetch_assoc($existingResult))) {
                $existingEmployeePassword = $existingRow['password'] ?? null;
            }
            mysqli_stmt_close($existingStmt);
        }

        $passwordForStorage = $password;
        if ($passwordForStorage === null || $passwordForStorage === '') {
            $passwordForStorage = $existingEmployeePassword;
        }
        $passwordForStorage = normalizePasswordForStorage($passwordForStorage);

        if ($password === null) {
            $stmt = mysqli_prepare($dbc, "UPDATE employees SET name = ?, email = ?, username = ?, password = ?, type = ?, position = ?, department = ?, salary = ?, join_date = ?, status = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'sssssssdssi', $name, $email, $username, $passwordForStorage, $type, $position, $department, $salary, $joinDate, $status, $id);
        } else {
            $stmt = mysqli_prepare($dbc, "UPDATE employees SET name = ?, email = ?, username = ?, password = ?, type = ?, position = ?, department = ?, salary = ?, join_date = ?, status = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'sssssssdssi', $name, $email, $username, $passwordForStorage, $type, $position, $department, $salary, $joinDate, $status, $id);
        }
        if (!mysqli_stmt_execute($stmt)) {
            respond(false, null, 'Database update failed: ' . mysqli_error($dbc));
        }
        ensureUserRow($dbc, $id, $username, $passwordForStorage, $type);
        respond(true, ['id' => $id, 'name' => $name, 'email' => $email, 'username' => $username, 'type' => $type, 'position' => $position, 'department' => $department, 'salary' => $salary, 'join_date' => $joinDate, 'status' => $status], 'Employee updated.');
    }

    $passwordForStorage = normalizePasswordForStorage($password);
    $stmt = mysqli_prepare($dbc, "INSERT INTO employees (name, email, username, password, type, position, department, salary, join_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sssssssdss', $name, $email, $username, $passwordForStorage, $type, $position, $department, $salary, $joinDate, $status);

    if (!mysqli_stmt_execute($stmt)) {
        respond(false, null, 'Database insert failed: ' . mysqli_error($dbc));
    }

    $insertedId = mysqli_insert_id($dbc);
    ensureUserRow($dbc, $insertedId, $username, $passwordForStorage, $type);
    respond(true, ['id' => $insertedId, 'name' => $name, 'email' => $email, 'username' => $username, 'type' => $type, 'position' => $position, 'department' => $department, 'salary' => $salary, 'join_date' => $joinDate, 'status' => $status], 'Employee created.');
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

    $userDeleteStmt = mysqli_prepare($dbc, "DELETE FROM `users` WHERE `User_id` = ?");
    if ($userDeleteStmt) {
        mysqli_stmt_bind_param($userDeleteStmt, 'i', $id);
        mysqli_stmt_execute($userDeleteStmt);
        mysqli_stmt_close($userDeleteStmt);
    }

    respond(true, null, 'Employee deleted.');
}

respond(false, null, 'Unsupported request method.');
} catch (Throwable $e) {
    if (ob_get_length()) {
        ob_end_clean();
    }
    echo json_encode(['success' => false, 'data' => null, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
}
