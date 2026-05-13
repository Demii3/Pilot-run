<?php
include 'Modules/dbcon.php';

// Check Alexis Eron's attendance for April 16-30
$query = "SELECT * FROM employee_attendance 
          WHERE Emp_id = 4 AND Date >= '2026-04-16' AND Date <= '2026-04-30' 
          ORDER BY Date";
$result = mysqli_query($dbc, $query);
$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);

echo "=== Alexis Eron (ID: 4) Attendance April 16-30, 2026 ===\n";
echo json_encode($rows, JSON_PRETTY_PRINT);

// Also check what API is returning
echo "\n\n=== Via Attendance API ===\n";
echo "Calling attendance_api.php...\n";

$ch = curl_init('http://localhost/Pilot-run/HR/Employee_payroll/attendance_api.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
$data = json_decode($response, true);

// Filter for employee 4
$alexisRecords = array_filter($data['data'] ?? [], function($item) {
    $empId = $item['Emp_id'] ?? $item['id'] ?? null;
    if ($empId == 4) {
        $date = $item['Date'] ?? $item['date'] ?? null;
        // Check if date is within range
        return $date >= '2026-04-16' && $date <= '2026-04-30';
    }
    return false;
});

echo "Alexis Eron records from API: " . count($alexisRecords) . "\n";
echo json_encode(array_values($alexisRecords), JSON_PRETTY_PRINT);
?>
