<?php
ob_start();
header('Content-Type: application/json');

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

include '../../Modules/dbcon.php';

function respond($success, $data = null, $message = '', $statusCode = 200) {
    http_response_code($statusCode);
    if (ob_get_length()) {
        ob_end_clean();
    }
    echo json_encode(['success' => $success, 'data' => $data, 'message' => $message]);
    exit;
}

function ensureAssignedEmpDeducTable($dbc) {
    $createSql = "CREATE TABLE IF NOT EXISTS `assigned_emp_deduc` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `name` VARCHAR(255) NOT NULL,
      `type_of_deduction` VARCHAR(255) NOT NULL,
      `cost` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    mysqli_query($dbc, $createSql);
    if (mysqli_errno($dbc)) {
        respond(false, null, 'Database setup failed: ' . mysqli_error($dbc), 500);
    }
}

function handleGetRequest($dbc) {
    $query = "SELECT id, name, type_of_deduction, cost, created_at FROM assigned_emp_deduc ORDER BY id DESC";
    $result = mysqli_query($dbc, $query);

    if (!$result) {
        respond(false, null, 'Failed to fetch data: ' . mysqli_error($dbc), 500);
    }

    $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
    respond(true, $rows);
}

function handlePostRequest($dbc) {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    if (!isset($input['name'], $input['type_of_deduction'], $input['cost'])) {
        respond(false, null, 'Invalid input.', 400);
    }

    $id = isset($input['id']) && is_numeric($input['id']) ? intval($input['id']) : null;
    $name = trim($input['name']);
    $type_of_deduction = trim($input['type_of_deduction']);
    $cost = floatval($input['cost']);

    if ($name === '' || $type_of_deduction === '') {
        respond(false, null, 'Name and deduction type are required.', 400);
    }

    if ($id) {
        $stmt = mysqli_prepare($dbc, "UPDATE assigned_emp_deduc SET name = ?, type_of_deduction = ?, cost = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'ssdi', $name, $type_of_deduction, $cost, $id);
    } else {
        $stmt = mysqli_prepare($dbc, "INSERT INTO assigned_emp_deduc (name, type_of_deduction, cost) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssd', $name, $type_of_deduction, $cost);
    }

    if (!mysqli_stmt_execute($stmt)) {
        respond(false, null, 'Database operation failed: ' . mysqli_error($dbc), 500);
    }

    respond(true, null, $id ? 'Assignment updated successfully.' : 'Assignment saved successfully.');
}

function handleDeleteRequest($dbc) {
    parse_str(file_get_contents('php://input'), $input);

    if (!isset($input['id']) || !is_numeric($input['id'])) {
        respond(false, null, 'ID is required for deletion.', 400);
    }

    $id = intval($input['id']);
    $stmt = mysqli_prepare($dbc, "DELETE FROM assigned_emp_deduc WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);

    if (!mysqli_stmt_execute($stmt)) {
        respond(false, null, 'Failed to delete assignment: ' . mysqli_error($dbc), 500);
    }

    respond(true, null, 'Assignment deleted successfully.');
}

try {
    if (!$dbc) {
        respond(false, null, 'Database connection failed.', 500);
    }

    ensureAssignedEmpDeducTable($dbc);

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        handleGetRequest($dbc);
    }

    if ($method === 'POST') {
        handlePostRequest($dbc);
    }

    if ($method === 'DELETE') {
        handleDeleteRequest($dbc);
    }

    respond(false, null, 'Method not allowed.', 405);
} catch (Throwable $e) {
    respond(false, null, 'Server error: ' . $e->getMessage(), 500);
}
