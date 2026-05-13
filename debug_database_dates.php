<?php
include 'Modules/dbcon.php';

// Check actual attendance records for Employee 4 in April 1-15
$query = "SELECT Attendance_id, Emp_id, Date, Clock_in, Clock_out, Duration FROM employee_attendance WHERE Emp_id = 4 AND Date BETWEEN '2026-04-01' AND '2026-04-15' ORDER BY Date LIMIT 10";

$result = mysqli_query($dbc, $query);
$records = mysqli_fetch_all($result, MYSQLI_ASSOC);

echo "=== Direct Database Query Results ===\n";
echo "Query: " . $query . "\n\n";
echo "Records found: " . count($records) . "\n\n";

if ($records) {
    foreach ($records as $r) {
        echo "ID={$r['Attendance_id']}, Emp={$r['Emp_id']}, Date={$r['Date']} (type: " . gettype($r['Date']) . "), Clock_in={$r['Clock_in']}, Clock_out={$r['Clock_out']}, Duration={$r['Duration']}\n";
    }
}

// Also check the Date field type in the table
echo "\n\n=== Table Schema for Date Field ===\n";
$schema = "DESCRIBE employee_attendance";
$schemaResult = mysqli_query($dbc, $schema);
$schemaRows = mysqli_fetch_all($schemaResult, MYSQLI_ASSOC);

foreach ($schemaRows as $s) {
    if ($s['Field'] === 'Date') {
        echo "Field: {$s['Field']}, Type: {$s['Type']}, Null: {$s['Null']}, Default: {$s['Default']}\n";
    }
}

// Now test what the API returns
echo "\n\n=== API Response Format Test ===\n";
// Simulate what the API does
$apiQuery = "SELECT ea.Attendance_id, ea.Emp_id, ea.Date, ea.Clock_in, ea.Clock_out, ea.Duration, ea.AO FROM employee_attendance ea INNER JOIN employees e ON ea.Emp_id = e.id WHERE ea.Emp_id = 4 AND ea.Date BETWEEN '2026-04-01' AND '2026-04-15' ORDER BY ea.Date DESC, ea.Attendance_id DESC LIMIT 10";

$apiResult = mysqli_query($dbc, $apiQuery);
$apiRows = mysqli_fetch_all($apiResult, MYSQLI_ASSOC);
echo json_encode([
    'success' => true,
    'data' => $apiRows
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
