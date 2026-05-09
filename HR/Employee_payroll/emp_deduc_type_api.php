<?php
header('Content-Type: application/json');

include '../../Modules/dbcon.php';


if (!$dbc) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

function getEmpDeducTypeTableSql() {
    // Return the SQL query to create the emp_deduc_type table if it doesn't exist
    return "CREATE TABLE IF NOT EXISTS emp_deduc_type (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type_of_deduction VARCHAR(255) NOT NULL,
        taxable TINYINT(1) NOT NULL DEFAULT 1,
        included_in_13month TINYINT(1) NOT NULL DEFAULT 1,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;";
}

function dropLegacyCostColumn($dbc) {
    $result = mysqli_query($dbc, "SHOW COLUMNS FROM emp_deduc_type LIKE 'cost'");
    if ($result && mysqli_num_rows($result) > 0) {
        mysqli_query($dbc, "ALTER TABLE emp_deduc_type DROP COLUMN cost");
        if (mysqli_errno($dbc)) {
            echo json_encode(['success' => false, 'message' => 'Database migration error: ' . mysqli_error($dbc)]);
            exit;
        }
    }
}

mysqli_query($dbc, getEmpDeducTypeTableSql());
if (mysqli_errno($dbc)) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($dbc)]);
    exit;
}

dropLegacyCostColumn($dbc);

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
        $stmt = mysqli_prepare($dbc, "SELECT id, type_of_deduction, taxable, included_in_13month FROM emp_deduc_type WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        respond(true, $row ?? null);
    }

    // Fetch all rows from the emp_deduc_type table
    $query = "SELECT * FROM emp_deduc_type";
    $result = mysqli_query($dbc, $query);

    if (!$result) {
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
    $typeOfDeduction = trim($input['type_of_deduction'] ?? '');
    $taxable = isset($input['taxable']) && ($input['taxable'] == 1 || $input['taxable'] === true || $input['taxable'] === '1') ? 1 : 0;
    $includedIn13 = isset($input['included_in_13month']) && ($input['included_in_13month'] == 1 || $input['included_in_13month'] === true || $input['included_in_13month'] === '1') ? 1 : 0;

    if ($typeOfDeduction === '') {
        respond(false, null, 'Missing required fields.');
    }

    if ($id) {
        $stmt = mysqli_prepare($dbc, "UPDATE emp_deduc_type SET type_of_deduction = ?, taxable = ?, included_in_13month = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'siii', $typeOfDeduction, $taxable, $includedIn13, $id);
        if (!mysqli_stmt_execute($stmt)) {
            respond(false, null, 'Update failed: ' . mysqli_error($dbc));
        }
        respond(true, ['id' => $id, 'type_of_deduction' => $typeOfDeduction, 'taxable' => $taxable, 'included_in_13month' => $includedIn13], 'Deduction type updated.');
    }

    $stmt = mysqli_prepare($dbc, "INSERT INTO emp_deduc_type (type_of_deduction, taxable, included_in_13month) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sii', $typeOfDeduction, $taxable, $includedIn13);
    if (!mysqli_stmt_execute($stmt)) {
        respond(false, null, 'Insert failed: ' . mysqli_error($dbc));
    }

    $insertedId = mysqli_insert_id($dbc);
    respond(true, ['id' => $insertedId, 'type_of_deduction' => $typeOfDeduction, 'taxable' => $taxable, 'included_in_13month' => $includedIn13], 'Deduction type created.');
}

if ($method === 'DELETE') {
    parse_str(file_get_contents('php://input'), $input);
    if (!isset($input['id']) || !is_numeric($input['id'])) {
        respond(false, null, 'Missing id.');
    }

    $id = intval($input['id']);
    $stmt = mysqli_prepare($dbc, "DELETE FROM emp_deduc_type WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (!mysqli_stmt_execute($stmt)) {
        respond(false, null, 'Delete failed: ' . mysqli_error($dbc));
    }
    respond(true, null, 'Deduction type deleted.');
}

respond(false, null, 'Unsupported request method.');
