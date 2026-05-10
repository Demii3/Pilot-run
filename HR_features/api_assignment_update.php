<?php
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'POST required']);
    exit;
}
include __DIR__ . '/../Modules/dbcon.php';

$assignmentId = isset($_POST['assignment_id']) ? intval($_POST['assignment_id']) : 0;
$employeeId = isset($_POST['employee_id']) ? intval($_POST['employee_id']) : 0;
$siteId = isset($_POST['site_id']) ? intval($_POST['site_id']) : 0;

if (!$assignmentId || !$employeeId || !$siteId) {
    echo json_encode(['success' => false, 'message' => 'Invalid assignment, employee or site']);
    exit;
}

// Validate assignment exists
$stmt = mysqli_prepare($dbc, "SELECT tb_id FROM employee_location WHERE tb_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $assignmentId);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
if (mysqli_stmt_num_rows($stmt) === 0) {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Assignment not found']);
    exit;
}
mysqli_stmt_close($stmt);

// Validate employee and site exist
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

$stmt = mysqli_prepare($dbc, "SELECT id FROM geofences WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $siteId);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
if (mysqli_stmt_num_rows($stmt) === 0) {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Site not found']);
    exit;
}
mysqli_stmt_close($stmt);

// Prevent duplicate assignment on another row
$stmt = mysqli_prepare($dbc, "SELECT tb_id FROM employee_location WHERE User_Id = ? AND loc_id = ? AND tb_id != ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'iii', $employeeId, $siteId, $assignmentId);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
if (mysqli_stmt_num_rows($stmt) > 0) {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'This employee is already assigned to the selected site']);
    exit;
}
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($dbc, "UPDATE employee_location SET User_Id = ?, loc_id = ? WHERE tb_id = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . mysqli_error($dbc)]);
    exit;
}
mysqli_stmt_bind_param($stmt, 'iii', $employeeId, $siteId, $assignmentId);
$ok = mysqli_stmt_execute($stmt);
if ($ok) {
    echo json_encode(['success' => true, 'message' => 'Assignment updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed: ' . mysqli_stmt_error($stmt)]);
}
mysqli_stmt_close($stmt);
