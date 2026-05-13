<?php
// Usage: php check_payroll_persistence.php 2026-04-01 2026-04-15
require __DIR__ . '/../Modules/dbcon.php';

$from = $argv[1] ?? '';
$to = $argv[2] ?? '';
if (!$from || !$to) {
    echo json_encode(['success' => false, 'message' => 'Usage: php check_payroll_persistence.php <from> <to>']);
    exit(1);
}

header('Content-Type: application/json');

$result = ['success' => true, 'from' => $from, 'to' => $to, 'run' => null, 'items_count' => 0, 'sample_items' => []];

$runSql = "SELECT id, cutoff_key, cutoff_from, cutoff_to, `year_month`, cutoff_slot, processed_by, processed_at FROM payroll_cutoff_runs WHERE cutoff_from = ? AND cutoff_to = ? LIMIT 1";
if ($stmt = mysqli_prepare($dbc, $runSql)) {
    mysqli_stmt_bind_param($stmt, 'ss', $from, $to);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $run = $res ? mysqli_fetch_assoc($res) : null;
    if ($run) {
        $result['run'] = $run;
        $runId = (int)$run['id'];

        $countSql = "SELECT COUNT(*) AS c FROM payroll_cutoff_items WHERE run_id = ?";
        if ($cstmt = mysqli_prepare($dbc, $countSql)) {
            mysqli_stmt_bind_param($cstmt, 'i', $runId);
            mysqli_stmt_execute($cstmt);
            $cres = mysqli_stmt_get_result($cstmt);
            $crow = $cres ? mysqli_fetch_assoc($cres) : null;
            $result['items_count'] = $crow ? (int)$crow['c'] : 0;
        }

        $sampleSql = "SELECT employee_id, employee_name, monthly_salary, cutoff_salary, hours_worked, sss, phlth, pagibig, tax, net_pay FROM payroll_cutoff_items WHERE run_id = ? ORDER BY employee_id ASC LIMIT 10";
        if ($sstmt = mysqli_prepare($dbc, $sampleSql)) {
            mysqli_stmt_bind_param($sstmt, 'i', $runId);
            mysqli_stmt_execute($sstmt);
            $sres = mysqli_stmt_get_result($sstmt);
            $rows = $sres ? mysqli_fetch_all($sres, MYSQLI_ASSOC) : [];
            $result['sample_items'] = $rows;
        }
    }
}

echo json_encode($result, JSON_PRETTY_PRINT);

?>
