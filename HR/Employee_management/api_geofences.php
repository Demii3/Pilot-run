<?php
// API to fetch geofences for the assign employees section
// Uses the same mysqli connection as the rest of the app

/** @var mysqli $dbc */
header('Content-Type: application/json');

include __DIR__ . '/../../Modules/dbcon.php';

$query = "SELECT id, name, coordinates FROM geofences ORDER BY name";
$result = mysqli_query($dbc, $query);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$geofences = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Parse coordinates if they're stored as JSON string
    if (is_string($row['coordinates'])) {
        $row['coordinates'] = json_decode($row['coordinates'], true);
    }
    $geofences[] = $row;
}

echo json_encode($geofences);
?>
