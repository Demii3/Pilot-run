<?php
// API endpoint to fetch employees as JSON

/** @var mysqli $dbc */
header('Content-Type: application/json');

include __DIR__ . '/../../Modules/dbcon.php';

$query = "SELECT id, name FROM employees WHERE status = 'Active' ORDER BY name";
$result = mysqli_query($dbc, $query);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$employees = [];
while ($row = mysqli_fetch_assoc($result)) {
    $employees[] = $row;
}

echo json_encode($employees);
?>
