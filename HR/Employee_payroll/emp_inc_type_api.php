<?php
header('Content-Type: application/json');

include '../../Modules/dbcon.php';
include 'Emp_Inc_Type_query.php';

if (!$dbc) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
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

if ($method === 'GET') {
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id = intval($_GET['id']);
        $stmt = mysqli_prepare($dbc, "SELECT id, type_of_income, cost, taxable, included_in_13month FROM Emp_Inc_Type WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        respond(true, $row ?? null);
    }

    $result = mysqli_query($dbc, "SELECT id, type_of_income, cost, taxable, included_in_13month FROM Emp_Inc_Type ORDER BY id ASC");
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    respond(true, $rows);
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    $id = isset($input['id']) && is_numeric($input['id']) ? intval($input['id']) : null;
    $typeOfIncome = trim($input['type_of_income'] ?? '');
    $cost = isset($input['cost']) ? floatval($input['cost']) : null;
    $taxable = isset($input['taxable']) && ($input['taxable'] == 1 || $input['taxable'] === true || $input['taxable'] === '1') ? 1 : 0;
    $includedIn13 = isset($input['included_in_13month']) && ($input['included_in_13month'] == 1 || $input['included_in_13month'] === true || $input['included_in_13month'] === '1') ? 1 : 0;

    if ($typeOfIncome === '' || $cost === null) {
        respond(false, null, 'Missing required fields.');
    }

    if ($id) {
        $stmt = mysqli_prepare($dbc, "UPDATE Emp_Inc_Type SET type_of_income = ?, cost = ?, taxable = ?, included_in_13month = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'sdiii', $typeOfIncome, $cost, $taxable, $includedIn13, $id);
        if (!mysqli_stmt_execute($stmt)) {
            respond(false, null, 'Update failed: ' . mysqli_error($dbc));
        }
        respond(true, ['id' => $id, 'type_of_income' => $typeOfIncome, 'cost' => $cost, 'taxable' => $taxable, 'included_in_13month' => $includedIn13], 'Income type updated.');
    }

    $stmt = mysqli_prepare($dbc, "INSERT INTO Emp_Inc_Type (type_of_income, cost, taxable, included_in_13month) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sdii', $typeOfIncome, $cost, $taxable, $includedIn13);
    if (!mysqli_stmt_execute($stmt)) {
        respond(false, null, 'Insert failed: ' . mysqli_error($dbc));
    }

    $insertedId = mysqli_insert_id($dbc);
    respond(true, ['id' => $insertedId, 'type_of_income' => $typeOfIncome, 'cost' => $cost, 'taxable' => $taxable, 'included_in_13month' => $includedIn13], 'Income type created.');
}

if ($method === 'DELETE') {
    parse_str(file_get_contents('php://input'), $input);
    if (!isset($input['id']) || !is_numeric($input['id'])) {
        respond(false, null, 'Missing id.');
    }

    $id = intval($input['id']);
    $stmt = mysqli_prepare($dbc, "DELETE FROM Emp_Inc_Type WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (!mysqli_stmt_execute($stmt)) {
        respond(false, null, 'Delete failed: ' . mysqli_error($dbc));
    }
    respond(true, null, 'Income type deleted.');
}

respond(false, null, 'Unsupported request method.');
