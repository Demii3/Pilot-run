<?php
// Test remittances API
require __DIR__ . '/../Modules/dbcon.php';

// Simulate GET request for April 2026
$_GET['year'] = 2026;
$_GET['month'] = 4;

// Get month filter bounds
function getMonthFilterBounds($year, $month) {
    $month = max(1, min(12, (int)$month));
    $year = (int)$year;
    $start = sprintf('%04d-%02d-01', $year, $month);
    $end = date('Y-m-t', strtotime($start));
    return [$start, $end];
}

[$startDate, $endDate] = getMonthFilterBounds(2026, 4);

$sql = "SELECT
            i.employee_id,
            i.employee_name,
            i.employee_email AS email,
            SUM(i.sss) AS sss,
            SUM(i.phlth) AS philhealth,
            SUM(i.pagibig) AS pagibig,
            SUM(i.tax) AS withholding_tax,
            SUM(i.sss) / 3 AS sss_employee,
            SUM(i.sss) - (SUM(i.sss) / 3) AS sss_employer,
            SUM(i.phlth) / 2 AS philhealth_employee,
            SUM(i.phlth) / 2 AS philhealth_employer,
            SUM(i.pagibig) / 2 AS pagibig_employee,
            SUM(i.pagibig) / 2 AS pagibig_employer,
            SUM(i.sss + i.phlth + i.pagibig + i.tax) AS total_premium,
            MAX(i.created_at) AS updated_at
        FROM payroll_cutoff_items i
        INNER JOIN payroll_cutoff_runs r ON r.id = i.run_id
        WHERE r.cutoff_to >= ? AND r.cutoff_to <= ?
        GROUP BY i.employee_id, i.employee_name, i.employee_email
        ORDER BY i.employee_name ASC";

$stmt = mysqli_prepare($dbc, $sql);
mysqli_stmt_bind_param($stmt, 'ss', $startDate, $endDate);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$rows = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];

echo json_encode([
    'success' => true,
    'startDate' => $startDate,
    'endDate' => $endDate,
    'count' => count($rows),
    'data' => $rows
], JSON_PRETTY_PRINT);

?>