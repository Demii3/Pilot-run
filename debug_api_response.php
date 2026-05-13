<?php
include 'Modules/dbcon.php';

// Get the raw API response
$query = "SELECT 
            ea.Attendance_id,
            ea.Emp_id,
            e.name AS employee_name,
            ea.Date,
            ea.Clock_in,
            ea.Clock_out,
            ea.Clockin_status,
            ea.Clockout_status,
            ea.Duration,
            ea.AO
          FROM employee_attendance ea
          INNER JOIN employees e ON ea.Emp_id = e.id
          ORDER BY ea.Date DESC, ea.Attendance_id DESC";

$result = mysqli_query($dbc, $query);
$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Filter for April 16-30 and employee 4
$filtered = array_filter($rows, function($item) {
    $date = $item['Date'] ?? null;
    $empId = $item['Emp_id'] ?? null;
    return $empId == 4 && $date >= '2026-04-16' && $date <= '2026-04-30';
});

echo "=== Attendance API Response for Alexis Eron (ID: 4) for April 16-30 ===\n";
echo "Total records: " . count($filtered) . "\n\n";

foreach ($filtered as $record) {
    echo "Record: " . json_encode($record, JSON_PRETTY_PRINT) . "\n";
    echo "Duration value: " . var_export($record['Duration'], true) . "\n";
    echo "Duration as number: " . intval($record['Duration']) . "\n";
    echo "---\n";
}

echo "\n=== Calculate total hours ===\n";
$totalMinutes = 0;
foreach ($filtered as $record) {
    $duration = intval($record['Duration'] ?? 0);
    $totalMinutes += $duration;
    echo "Date: " . $record['Date'] . " | Duration: " . $duration . " minutes\n";
}
echo "Total: " . $totalMinutes . " minutes = " . ($totalMinutes / 60) . " hours\n";
?>
