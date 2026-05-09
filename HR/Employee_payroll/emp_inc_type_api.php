<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

include '../../Modules/dbcon.php';

if (!$dbc) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

function getEmpIncTypeTableSql() {
    // Return the SQL query to create the emp_inc_type table if it doesn't exist
    return "CREATE TABLE IF NOT EXISTS emp_inc_type (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type_of_income VARCHAR(255) NOT NULL,
        cost DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        taxable TINYINT(1) NOT NULL,
        included_in_13month TINYINT(1) NOT NULL,
        recurring TINYINT(1) NOT NULL DEFAULT 0
    ) ENGINE=InnoDB;";
}

mysqli_query($dbc, getEmpIncTypeTableSql());
if (mysqli_errno($dbc)) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($dbc)]);
    exit;
}

function respond($success, $data = null, $message = '') {
    echo json_encode(['success' => $success, 'data' => $data, 'message' => $message]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// Add debugging logs to identify issues
error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
error_log("Input data: " . file_get_contents('php://input'));

// Add CORS headers to handle cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($method === 'GET') {
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id = intval($_GET['id']);
        $stmt = mysqli_prepare($dbc, "SELECT id, type_of_income, cost, taxable, included_in_13month, recurring FROM emp_inc_type WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        respond(true, $row ?? null);
    }

    // Fetch all rows from the emp_inc_type table
    $query = "SELECT * FROM emp_inc_type";
    $result = mysqli_query($dbc, $query);

    if (!$result) {
        error_log('Database query failed: ' . mysqli_error($dbc));
        echo json_encode(['success' => false, 'message' => 'Database query failed: ' . mysqli_error($dbc)]);
        exit;
    }

    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $rows]);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    $id = isset($input['id']) && is_numeric($input['id']) ? intval($input['id']) : null;
    $typeOfIncome = trim($input['type_of_income'] ?? '');
    $cost = isset($input['cost']) && $input['cost'] !== '' ? floatval($input['cost']) : 0.0;
    $taxable = isset($input['taxable']) && ($input['taxable'] == 1 || $input['taxable'] === true || $input['taxable'] === '1') ? 1 : 0;
    $includedIn13 = isset($input['included_in_13month']) && ($input['included_in_13month'] == 1 || $input['included_in_13month'] === true || $input['included_in_13month'] === '1') ? 1 : 0;
    $recurring = isset($input['recurring']) && ($input['recurring'] == 1 || $input['recurring'] === true || $input['recurring'] === '1') ? 1 : 0;

    if ($typeOfIncome === '') {
        respond(false, null, 'Missing required fields.');
    }

    if ($id) {
        $stmt = mysqli_prepare($dbc, "UPDATE emp_inc_type SET type_of_income = ?, cost = ?, taxable = ?, included_in_13month = ?, recurring = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'sdiiii', $typeOfIncome, $cost, $taxable, $includedIn13, $recurring, $id);
        if (!mysqli_stmt_execute($stmt)) {
            error_log('Update failed: ' . mysqli_error($dbc));
            respond(false, null, 'Update failed: ' . mysqli_error($dbc));
        }
        respond(true, ['id' => $id, 'type_of_income' => $typeOfIncome, 'cost' => $cost, 'taxable' => $taxable, 'included_in_13month' => $includedIn13, 'recurring' => $recurring], 'Income type updated.');
    }

    $stmt = mysqli_prepare($dbc, "INSERT INTO emp_inc_type (type_of_income, cost, taxable, included_in_13month, recurring) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sdiii', $typeOfIncome, $cost, $taxable, $includedIn13, $recurring);
    if (!mysqli_stmt_execute($stmt)) {
        error_log('Insert failed: ' . mysqli_error($dbc));
        respond(false, null, 'Insert failed: ' . mysqli_error($dbc));
    }

    $insertedId = mysqli_insert_id($dbc);
    respond(true, ['id' => $insertedId, 'type_of_income' => $typeOfIncome, 'cost' => $cost, 'taxable' => $taxable, 'included_in_13month' => $includedIn13, 'recurring' => $recurring], 'Income type created.');
}

if ($method === 'DELETE') {
    parse_str(file_get_contents('php://input'), $input);
    if (!isset($input['id']) || !is_numeric($input['id'])) {
        respond(false, null, 'Missing id.');
    }

    $id = intval($input['id']);
    $stmt = mysqli_prepare($dbc, "DELETE FROM emp_inc_type WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (!mysqli_stmt_execute($stmt)) {
        error_log('Delete failed: ' . mysqli_error($dbc));
        respond(false, null, 'Delete failed: ' . mysqli_error($dbc));
    }
    respond(true, null, 'Income type deleted.');
}

respond(false, null, 'Unsupported request method.');
