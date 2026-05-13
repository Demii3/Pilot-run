<?php
include 'Modules/dbcon.php';

echo "=== ALL Attendance Records for April 16-30, 2026 ===\n";
$query = "SELECT ea.*, e.name 
          FROM employee_attendance ea 
          LEFT JOIN employees e ON ea.Emp_id = e.id
          WHERE Date >= '2026-04-16' AND Date <= '2026-04-30' 
          ORDER BY Emp_id, Date";
$result = mysqli_query($dbc, $query);
$allRows = mysqli_fetch_all($result, MYSQLI_ASSOC);

echo json_encode($allRows, JSON_PRETTY_PRINT);

echo "\n\n=== Employee 4 (Alexis Eron) Detailed ===\n";
$query2 = "SELECT * FROM employee_attendance WHERE Emp_id = 4 ORDER BY Date DESC LIMIT 10";
$result2 = mysqli_query($dbc, $query2);
$employee4 = mysqli_fetch_all($result2, MYSQLI_ASSOC);
echo json_encode($employee4, JSON_PRETTY_PRINT);

echo "\n\n=== Employee Summary for April 16-30 ===\n";
$query3 = "SELECT ea.Emp_id, e.name, COUNT(*) as record_count, 
                  SUM(CAST(ea.Duration AS UNSIGNED)) as total_duration_minutes
           FROM employee_attendance ea 
           LEFT JOIN employees e ON ea.Emp_id = e.id
           WHERE Date >= '2026-04-16' AND Date <= '2026-04-30' 
           GROUP BY ea.Emp_id, e.name
           ORDER BY ea.Emp_id";
$result3 = mysqli_query($dbc, $query3);
$summary = mysqli_fetch_all($result3, MYSQLI_ASSOC);
echo json_encode($summary, JSON_PRETTY_PRINT);
?>
