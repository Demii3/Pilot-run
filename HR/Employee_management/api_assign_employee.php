<?php
// API to assign employee to geofence
// Validates and stores assignment

/** @var mysqli $dbc */
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['login']) || $_SESSION['type'] != "HR") {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include __DIR__ . '/../../Modules/dbcon.php';

$geofenceId = isset($_POST['geofence_id']) ? intval($_POST['geofence_id']) : 0;
$employeeId = isset($_POST['employee_id']) ? intval($_POST['employee_id']) : 0;

if (!$geofenceId || !$employeeId) {
    echo json_encode(['success' => false, 'message' => $geofenceId]);
    exit;
}

// Validate geofence exists
$stmt = mysqli_prepare($dbc, "SELECT id FROM geofences WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $geofenceId);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
if (mysqli_stmt_num_rows($stmt) === 0) {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Geofence not found']);
    exit;
}
mysqli_stmt_close($stmt);

// Validate employee exists
$stmt = mysqli_prepare($dbc, "SELECT id FROM employees WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $employeeId);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
if (mysqli_stmt_num_rows($stmt) === 0) {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Employee not found']);
    exit;
}
mysqli_stmt_close($stmt);

// Check if assignment already exists
$stmt = mysqli_prepare($dbc, "SELECT id FROM employee_location WHERE employee_id = ? AND location_id = ?");
mysqli_stmt_bind_param($stmt, 'ii', $employeeId, $geofenceId);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
if (mysqli_stmt_num_rows($stmt) > 0) {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Employee is already assigned to this site']);
    exit;
}
mysqli_stmt_close($stmt);

// Insert assignment
$stmt = mysqli_prepare($dbc, "INSERT INTO employee_location (employee_id, location_id) VALUES (?, ?)");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

mysqli_stmt_bind_param($stmt, 'ii', $employeeId, $geofenceId);
if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true, 'message' => 'Employee assigned to site successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to assign employee']);
}
mysqli_stmt_close($stmt);
?>
