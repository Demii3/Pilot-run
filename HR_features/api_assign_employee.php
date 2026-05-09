<?php
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'message'=>'POST required']);
    exit;
}
include __DIR__ . '/../Modules/dbcon.php';

$userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$locId = isset($_POST['loc_id']) ? (int)$_POST['loc_id'] : 0;

if (!$userId || !$locId) {
    echo json_encode(['success'=>false,'message'=>'Invalid input']);
    exit;
}

// check employee exists
$eRes = mysqli_prepare($dbc, "SELECT id FROM employees WHERE id = ?");
mysqli_stmt_bind_param($eRes, 'i', $userId);
mysqli_stmt_execute($eRes);
mysqli_stmt_store_result($eRes);
if (mysqli_stmt_num_rows($eRes) === 0) {
    echo json_encode(['success'=>false,'message'=>'Employee not found']);
    exit;
}

// check geofence exists
$gRes = mysqli_prepare($dbc, "SELECT id FROM geofences WHERE id = ?");
mysqli_stmt_bind_param($gRes, 'i', $locId);
mysqli_stmt_execute($gRes);
mysqli_stmt_store_result($gRes);
if (mysqli_stmt_num_rows($gRes) === 0) {
    echo json_encode(['success'=>false,'message'=>'Site not found']);
    exit;
}

// avoid duplicate assignment
$check = mysqli_prepare($dbc, "SELECT tb_id FROM employee_location WHERE User_Id = ? AND loc_id = ?");
mysqli_stmt_bind_param($check, 'ii', $userId, $locId);
mysqli_stmt_execute($check);
mysqli_stmt_store_result($check);
if (mysqli_stmt_num_rows($check) > 0) {
    echo json_encode(['success'=>false,'message'=>'Employee already assigned to this site']);
    exit;
}

$ins = mysqli_prepare($dbc, "INSERT INTO employee_location (User_Id, loc_id) VALUES (?, ?)");
mysqli_stmt_bind_param($ins, 'ii', $userId, $locId);
$ok = mysqli_stmt_execute($ins);
if ($ok) {
    echo json_encode(['success'=>true,'message'=>'Assigned successfully']);
} else {
    echo json_encode(['success'=>false,'message'=>'DB error: '.mysqli_error($dbc)]);
}
