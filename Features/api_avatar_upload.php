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

if (!isset($_FILES['avatar'])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

$file = $_FILES['avatar'];
$userId = $_SESSION['id'];

// Validate file
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Upload error']);
    exit;
}

// Check file size (max 5MB)
if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File too large (max 5MB)']);
    exit;
}

// Check MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid image format']);
    exit;
}

// Create avatars directory if it doesn't exist
$avatarDir = __DIR__ . '/avatars';
if (!is_dir($avatarDir)) {
    mkdir($avatarDir, 0755, true);
}

// Generate unique filename
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
$filepath = $avatarDir . '/' . $filename;
$relativeUrl = 'Features/avatars/' . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save file']);
    exit;
}

// Delete old avatar if exists
$oldStmt = mysqli_prepare($dbc, "SELECT avatar_path FROM employees WHERE id = ?");
mysqli_stmt_bind_param($oldStmt, 'i', $userId);
mysqli_stmt_execute($oldStmt);
mysqli_stmt_bind_result($oldStmt, $oldPath);
if (mysqli_stmt_fetch($oldStmt) && $oldPath) {
    $oldFile = __DIR__ . '/../' . $oldPath;
    if (file_exists($oldFile)) {
        @unlink($oldFile);
    }
}
mysqli_stmt_close($oldStmt);

// Update database
$updateStmt = mysqli_prepare($dbc, "UPDATE employees SET avatar_path = ? WHERE id = ?");
if (!$updateStmt) {
    @unlink($filepath);
    echo json_encode(['success' => false, 'message' => 'DB error']);
    exit;
}

mysqli_stmt_bind_param($updateStmt, 'si', $relativeUrl, $userId);
if (!mysqli_stmt_execute($updateStmt)) {
    @unlink($filepath);
    echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
    exit;
}
mysqli_stmt_close($updateStmt);

echo json_encode([
    'success' => true,
    'message' => 'Avatar uploaded successfully',
    'avatar_url' => '../' . $relativeUrl
]);
?>
