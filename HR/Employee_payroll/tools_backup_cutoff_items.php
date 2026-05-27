<?php
require __DIR__ . '/../../Modules/dbcon.php';
$from = $argv[1] ?? '';
$to = $argv[2] ?? '';
if (!$from || !$to) { echo "Usage: php tools_backup_cutoff_items.php YYYY-MM-DD YYYY-MM-DD\n"; exit(1); }
$stmt = mysqli_prepare($dbc, "SELECT * FROM payroll_cutoff_items i INNER JOIN payroll_cutoff_runs r ON r.id = i.run_id WHERE r.cutoff_from = ? AND r.cutoff_to = ? ORDER BY i.employee_id ASC");
mysqli_stmt_bind_param($stmt, 'ss', $from, $to);
if (!mysqli_stmt_execute($stmt)) { echo "Query failed: " . mysqli_error($dbc) . "\n"; exit(2); }
$res = mysqli_stmt_get_result($stmt);
$rows = $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
$file = __DIR__ . "/backup_{$from}_{$to}.json";
file_put_contents($file, json_encode(['from'=>$from,'to'=>$to,'rows'=>$rows], JSON_PRETTY_PRINT));
echo "Wrote backup to: $file\n";
