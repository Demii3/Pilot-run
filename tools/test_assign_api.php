<?php
// Quick DB schema detection + safe insert test for employee_location
require_once __DIR__ . '/../Modules/dbcon.php';
header('Content-Type: text/plain');

$tblCheck = mysqli_query($dbc, "SHOW TABLES LIKE 'employee_location'");
if (!$tblCheck || mysqli_num_rows($tblCheck) === 0) {
    echo json_encode(['error' => 'employee_location table not found']);
    exit(0);
}

$colsRes = mysqli_query($dbc, "SHOW COLUMNS FROM employee_location");
$cols = [];
while ($row = mysqli_fetch_assoc($colsRes)) {
    $cols[] = $row['Field'];
}

$employeeCol = in_array('employee_id', $cols) ? 'employee_id' : (in_array('User_Id', $cols) ? 'User_Id' : null);
$locationCol = in_array('location_id', $cols) ? 'location_id' : (in_array('loc_id', $cols) ? 'loc_id' : null);

echo "Detected columns:\n" . json_encode(['cols'=>$cols,'employeeCol'=>$employeeCol,'locationCol'=>$locationCol], JSON_PRETTY_PRINT) . "\n";

if (!$employeeCol || !$locationCol) {
    echo "Schema looks unexpected, aborting prepared insert test.\n";
    exit(0);
}

// Do a safe insert inside transaction and roll back
// Pick existing employee and geofence to avoid FK errors
$empRes = mysqli_query($dbc, "SELECT id FROM employees LIMIT 1");
$geoRes = mysqli_query($dbc, "SELECT id FROM geofences LIMIT 1");
$empRow = mysqli_fetch_assoc($empRes);
$geoRow = mysqli_fetch_assoc($geoRes);
if (!$empRow || !$geoRow) {
    echo "No existing employee or geofence to test insert.\n";
    exit(0);
}

$testEmployee = intval($empRow['id']);
$testLocation = intval($geoRow['id']);

mysqli_begin_transaction($dbc);
$insertSql = sprintf("INSERT INTO employee_location (%s, %s) VALUES (?, ?)", $employeeCol, $locationCol);
$stmt = mysqli_prepare($dbc, $insertSql);
if (!$stmt) {
    echo "Prepare failed: " . mysqli_error($dbc) . "\n";
    mysqli_rollback($dbc);
    exit(0);
}

mysqli_stmt_bind_param($stmt, 'ii', $testEmployee, $testLocation);
$exec = false;
try {
    $exec = mysqli_stmt_execute($stmt);
    $err = mysqli_stmt_error($stmt);
} catch (Exception $e) {
    $err = $e->getMessage();
}

echo "Prepared insert executed: " . ($exec ? 'true' : 'false') . "\n";
if (!empty($err)) echo "Statement error: $err\n";

mysqli_rollback($dbc);
mysqli_stmt_close($stmt);

echo "Transaction rolled back.\n";

exit(0);
