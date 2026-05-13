<?php
include 'Modules/dbcon.php';

// Check the database schema for employee_attendance
$query = "DESCRIBE employee_attendance";
$result = mysqli_query($dbc, $query);
$schema = mysqli_fetch_all($result, MYSQLI_ASSOC);

echo "=== employee_attendance Table Schema ===\n";
echo json_encode($schema, JSON_PRETTY_PRINT);

// Check specific Duration values
echo "\n\n=== Duration field values for Employee 4 ===\n";
$query2 = "SELECT Duration, CAST(Duration AS UNSIGNED) as duration_int FROM employee_attendance WHERE Emp_id = 4 LIMIT 5";
$result2 = mysqli_query($dbc, $query2);
$durations = mysqli_fetch_all($result2, MYSQLI_ASSOC);
echo json_encode($durations, JSON_PRETTY_PRINT);

// Check if there's any Duration that's NULL or empty
echo "\n\n=== Potential NULL/Empty Duration issues ===\n";
$query3 = "SELECT COUNT(*) as null_count FROM employee_attendance WHERE Duration IS NULL OR Duration = '' OR Duration = 0";
$result3 = mysqli_query($dbc, $query3);
$nullCount = mysqli_fetch_assoc($result3);
echo "Records with NULL/empty/zero Duration: " . $nullCount['null_count'];

// Check actual Duration values in database for employee 4
echo "\n\n=== All Duration values for Employee 4 ===\n";
$query4 = "SELECT Attendance_id, Date, Duration FROM employee_attendance WHERE Emp_id = 4 ORDER BY Date DESC LIMIT 20";
$result4 = mysqli_query($dbc, $query4);
$allDurations = mysqli_fetch_all($result4, MYSQLI_ASSOC);
echo json_encode($allDurations, JSON_PRETTY_PRINT);
?>
