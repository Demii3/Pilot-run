<?php
header('Content-Type: application/json');

include '../../Modules/dbcon.php';

if (!$dbc) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handleGetRequest($dbc);
        break;
    case 'POST':
        handlePostRequest($dbc);
        break;
    case 'DELETE':
        handleDeleteRequest($dbc);
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
        break;
}

function handleGetRequest($dbc) {
    $query = "SELECT * FROM assigned_emp_inc";
    $result = mysqli_query($dbc, $query);

    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Failed to fetch data.']);
        exit;
    }

    $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'data' => $rows]);
}

function handlePostRequest($dbc) {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['name'], $input['type_of_income'], $input['cost'], $input['taxable'], $input['month_13th'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid input.']);
        exit;
    }

    $id = $input['id'] ?? null;
    $name = $input['name'];
    $type_of_income = $input['type_of_income'];
    $cost = $input['cost'];
    $taxable = $input['taxable'];
    $month_13th = $input['month_13th'];

    if ($id) {
        $stmt = mysqli_prepare($dbc, "UPDATE assigned_emp_inc SET name = ?, type_of_income = ?, cost = ?, taxable = ?, month_13th = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'ssdiii', $name, $type_of_income, $cost, $taxable, $month_13th, $id);
    } else {
        $stmt = mysqli_prepare($dbc, "INSERT INTO assigned_emp_inc (name, type_of_income, cost, taxable, month_13th) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssdii', $name, $type_of_income, $cost, $taxable, $month_13th);
    }

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => $id ? 'Assignment updated successfully.' : 'Assignment saved successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database operation failed.']);
    }
}

function handleDeleteRequest($dbc) {
    parse_str(file_get_contents('php://input'), $input);

    if (!isset($input['id'])) {
        echo json_encode(['success' => false, 'message' => 'ID is required for deletion.']);
        exit;
    }

    $id = intval($input['id']);
    $stmt = mysqli_prepare($dbc, "DELETE FROM assigned_emp_inc WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Assignment deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete assignment.']);
    }
}


