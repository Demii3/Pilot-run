<?php
// API to assign employee to geofence
/** @var mysqli $dbc */
ini_set('display_errors', '0');
error_reporting(E_ALL);
header('Content-Type: application/json');

set_error_handler(function ($severity, $message) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $message]);
    exit;
});

set_exception_handler(function ($exception) {
    echo json_encode(['success' => false, 'message' => 'Server exception: ' . $exception->getMessage()]);
    exit;
});

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$host = $_SERVER['HTTP_HOST'] ?? '';
$remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
$isLocalRequest = strpos($host, 'localhost') !== false || $remoteAddr === '127.0.0.1' || $remoteAddr === '::1';
$isHrSession = isset($_SESSION['login'], $_SESSION['type']) && $_SESSION['type'] === 'HR';

// Allow direct local testing from geofence page without forcing login.
if (!$isHrSession && !$isLocalRequest) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include __DIR__ . '/../../Modules/dbcon.php';

// Support both JSON and form-encoded requests.
$input = file_get_contents('php://input');
$data = json_decode($input, true);
if (!is_array($data)) {
    $data = $_POST;
}

$geofenceId = isset($data['geofence_id']) ? intval($data['geofence_id']) : 0;
$employeeId = isset($data['employee_id']) ? intval($data['employee_id']) : 0;

if (!$geofenceId || !$employeeId) {
    echo json_encode(['success' => false, 'message' => 'Invalid geofence or employee ID']);
    exit;
}

$tblCheck = mysqli_query($dbc, "SHOW TABLES LIKE 'employee_location'");
if (!$tblCheck || mysqli_num_rows($tblCheck) === 0) {
    echo json_encode(['success' => false, 'message' => 'Assignment table not found']);
    exit;
}

$colsRes = mysqli_query($dbc, "SHOW COLUMNS FROM employee_location");
$cols = [];
while ($row = mysqli_fetch_assoc($colsRes)) {
    $cols[] = $row['Field'];
}

$employeeCol = in_array('employee_id', $cols) ? 'employee_id' : (in_array('User_Id', $cols) ? 'User_Id' : null);
$locationCol = in_array('location_id', $cols) ? 'location_id' : (in_array('loc_id', $cols) ? 'loc_id' : null);

if (!$employeeCol || !$locationCol) {
    echo json_encode(['success' => false, 'message' => 'Unexpected employee_location schema']);
    exit;
}

$stmt = mysqli_prepare($dbc, "SELECT id FROM geofences WHERE id = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . mysqli_error($dbc)]);
    exit;
}
mysqli_stmt_bind_param($stmt, 'i', $geofenceId);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
if (mysqli_stmt_num_rows($stmt) === 0) {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Geofence not found']);
    exit;
}
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($dbc, "SELECT id FROM employees WHERE id = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . mysqli_error($dbc)]);
    exit;
}
mysqli_stmt_bind_param($stmt, 'i', $employeeId);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
if (mysqli_stmt_num_rows($stmt) === 0) {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Employee not found']);
    exit;
}
mysqli_stmt_close($stmt);

$checkSql = sprintf("SELECT 1 FROM employee_location WHERE %s = ? AND %s = ? LIMIT 1", $employeeCol, $locationCol);
$stmt = mysqli_prepare($dbc, $checkSql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . mysqli_error($dbc)]);
    exit;
}
mysqli_stmt_bind_param($stmt, 'ii', $employeeId, $geofenceId);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
if (mysqli_stmt_num_rows($stmt) > 0) {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Employee is already assigned to this site']);
    exit;
}
mysqli_stmt_close($stmt);

$insertSql = sprintf("INSERT INTO employee_location (%s, %s) VALUES (?, ?)", $employeeCol, $locationCol);
$stmt = mysqli_prepare($dbc, $insertSql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . mysqli_error($dbc)]);
    exit;
}
mysqli_stmt_bind_param($stmt, 'ii', $employeeId, $geofenceId);
if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true, 'message' => 'Employee assigned to site successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to assign employee: ' . mysqli_stmt_error($stmt)]);
}
mysqli_stmt_close($stmt);
