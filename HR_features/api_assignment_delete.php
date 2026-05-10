<?php
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'POST required']);
    exit;
}
include __DIR__ . '/../Modules/dbcon.php';

$assignmentId = isset($_POST['assignment_id']) ? intval($_POST['assignment_id']) : 0;
if (!$assignmentId) {
    echo json_encode(['success' => false, 'message' => 'Invalid assignment ID']);
    exit;
}

$stmt = mysqli_prepare($dbc, "DELETE FROM employee_location WHERE tb_id = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . mysqli_error($dbc)]);
    exit;
}
mysqli_stmt_bind_param($stmt, 'i', $assignmentId);
$ok = mysqli_stmt_execute($stmt);
if ($ok) {
    echo json_encode(['success' => true, 'message' => 'Assignment deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Delete failed: ' . mysqli_stmt_error($stmt)]);
}
mysqli_stmt_close($stmt);
