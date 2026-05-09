<?php
header('Content-Type: application/json');

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'POST required']);
    exit;
}

if (!isset($_SESSION['login']) || !isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include __DIR__ . '/../Modules/dbcon.php';

$userId = $_SESSION['id'];
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

if (!$name || !$email) {
    echo json_encode(['success' => false, 'message' => 'Name and email required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Check if email is already used by another user
$checkStmt = mysqli_prepare($dbc, "SELECT id FROM employees WHERE email = ? AND id != ?");
mysqli_stmt_bind_param($checkStmt, 'si', $email, $userId);
mysqli_stmt_execute($checkStmt);
mysqli_stmt_store_result($checkStmt);
if (mysqli_stmt_num_rows($checkStmt) > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already in use']);
    exit;
}
mysqli_stmt_close($checkStmt);

// Update employees table
$updateStmt = mysqli_prepare($dbc, "UPDATE employees SET name = ?, email = ? WHERE id = ?");
if (!$updateStmt) {
    echo json_encode(['success' => false, 'message' => 'DB error']);
    exit;
}

mysqli_stmt_bind_param($updateStmt, 'ssi', $name, $email, $userId);
if (!mysqli_stmt_execute($updateStmt)) {
    echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
    exit;
}
mysqli_stmt_close($updateStmt);

// Update session info
$_SESSION['username'] = $name;

echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
?>
