<?php
ob_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

include '../../Modules/dbcon.php';

function respond($success, $data = null, $message = '', $statusCode = 200)
{
    http_response_code($statusCode);
    if (ob_get_length()) {
        ob_end_clean();
    }

    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ]);
    exit;
}

function columnExists($dbc, $table, $column)
{
    $table = mysqli_real_escape_string($dbc, $table);
    $column = mysqli_real_escape_string($dbc, $column);
    $result = mysqli_query($dbc, "SHOW COLUMNS FROM `" . $table . "` LIKE '" . $column . "'");
    return $result && mysqli_num_rows($result) > 0;
}

function ensureEmpIncTypeTable($dbc)
{
    $createSql = "CREATE TABLE IF NOT EXISTS `emp_inc_type` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `type_of_income` VARCHAR(255) NOT NULL,
      `cost` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
      `taxable` TINYINT(1) NOT NULL DEFAULT 1,
      `included_in_13month` TINYINT(1) NOT NULL DEFAULT 1,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    mysqli_query($dbc, $createSql);
    if (mysqli_errno($dbc)) {
        respond(false, null, 'Database setup failed: ' . mysqli_error($dbc), 500);
    }

    if (!columnExists($dbc, 'emp_inc_type', 'included_in_13month')) {
        mysqli_query($dbc, "ALTER TABLE `emp_inc_type` ADD COLUMN `included_in_13month` TINYINT(1) NOT NULL DEFAULT 1");
        if (mysqli_errno($dbc)) {
            respond(false, null, 'Failed to add included_in_13month column: ' . mysqli_error($dbc), 500);
        }
    }
}

function asBoolInt($value, $default = 0)
{
    if (!isset($value)) {
        return $default;
    }

    return ($value == 1 || $value === true || $value === '1') ? 1 : 0;
}

function handleGetRequest($dbc)
{
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id = intval($_GET['id']);
        $stmt = mysqli_prepare($dbc, "SELECT id, type_of_income, cost, taxable, included_in_13month FROM emp_inc_type WHERE id = ?");
        if (!$stmt) {
            respond(false, null, 'Failed to prepare query: ' . mysqli_error($dbc), 500);
        }

        mysqli_stmt_bind_param($stmt, 'i', $id);
        if (!mysqli_stmt_execute($stmt)) {
            respond(false, null, 'Failed to fetch income type: ' . mysqli_error($dbc), 500);
        }

        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        respond(true, $row ?? null);
    }

    $query = "SELECT id, type_of_income, cost, taxable, included_in_13month FROM emp_inc_type ORDER BY id DESC";
    $result = mysqli_query($dbc, $query);
    if (!$result) {
        respond(false, null, 'Failed to fetch income types: ' . mysqli_error($dbc), 500);
    }

    $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
    respond(true, $rows);
}

function handlePostRequest($dbc)
{
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    $id = isset($input['id']) && is_numeric($input['id']) ? intval($input['id']) : null;
    $typeOfIncome = trim($input['type_of_income'] ?? '');
    $cost = isset($input['cost']) && $input['cost'] !== '' ? floatval($input['cost']) : 0.0;
    $taxable = asBoolInt($input['taxable'] ?? null, 1);
    $includedIn13 = asBoolInt($input['included_in_13month'] ?? null, 1);

    if ($typeOfIncome === '') {
        respond(false, null, 'Type of income is required.', 400);
    }

    if ($id) {
        $stmt = mysqli_prepare($dbc, "UPDATE emp_inc_type SET type_of_income = ?, cost = ?, taxable = ?, included_in_13month = ? WHERE id = ?");
        if (!$stmt) {
            respond(false, null, 'Failed to prepare update: ' . mysqli_error($dbc), 500);
        }

        mysqli_stmt_bind_param($stmt, 'sdiii', $typeOfIncome, $cost, $taxable, $includedIn13, $id);
        if (!mysqli_stmt_execute($stmt)) {
            respond(false, null, 'Update failed: ' . mysqli_error($dbc), 500);
        }

        mysqli_stmt_close($stmt);
        respond(true, [
            'id' => $id,
            'type_of_income' => $typeOfIncome,
            'cost' => $cost,
            'taxable' => $taxable,
            'included_in_13month' => $includedIn13
        ], 'Income type updated.');
    }

    $stmt = mysqli_prepare($dbc, "INSERT INTO emp_inc_type (type_of_income, cost, taxable, included_in_13month) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        respond(false, null, 'Failed to prepare insert: ' . mysqli_error($dbc), 500);
    }

    mysqli_stmt_bind_param($stmt, 'sdii', $typeOfIncome, $cost, $taxable, $includedIn13);
    if (!mysqli_stmt_execute($stmt)) {
        respond(false, null, 'Insert failed: ' . mysqli_error($dbc), 500);
    }

    $insertedId = mysqli_insert_id($dbc);
    mysqli_stmt_close($stmt);
    respond(true, [
        'id' => $insertedId,
        'type_of_income' => $typeOfIncome,
        'cost' => $cost,
        'taxable' => $taxable,
        'included_in_13month' => $includedIn13
    ], 'Income type created.');
}

function handleDeleteRequest($dbc)
{
    parse_str(file_get_contents('php://input'), $input);
    if (!isset($input['id']) || !is_numeric($input['id'])) {
        respond(false, null, 'Missing id.', 400);
    }

    $id = intval($input['id']);
    $stmt = mysqli_prepare($dbc, "DELETE FROM emp_inc_type WHERE id = ?");
    if (!$stmt) {
        respond(false, null, 'Failed to prepare delete: ' . mysqli_error($dbc), 500);
    }

    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (!mysqli_stmt_execute($stmt)) {
        respond(false, null, 'Delete failed: ' . mysqli_error($dbc), 500);
    }

    mysqli_stmt_close($stmt);
    respond(true, null, 'Income type deleted.');
}

try {
    if (!$dbc) {
        respond(false, null, 'Database connection failed.', 500);
    }

    ensureEmpIncTypeTable($dbc);

    $method = $_SERVER['REQUEST_METHOD'];
    if ($method === 'OPTIONS') {
        respond(true, null, 'OK');
    }

    if ($method === 'GET') {
        handleGetRequest($dbc);
    }

    if ($method === 'POST') {
        handlePostRequest($dbc);
    }

    if ($method === 'DELETE') {
        handleDeleteRequest($dbc);
    }

    respond(false, null, 'Unsupported request method.', 405);
} catch (Throwable $e) {
    respond(false, null, 'Server error: ' . $e->getMessage(), 500);
}
