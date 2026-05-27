<?php
require __DIR__ . '/../../Modules/dbcon.php';
$from = $argv[1] ?? '';
$to = $argv[2] ?? '';
if (!$from || !$to) { echo "Usage: php tools_delete_cutoff_items.php YYYY-MM-DD YYYY-MM-DD\n"; exit(1); }
$runStmt = mysqli_prepare($dbc, "SELECT id FROM payroll_cutoff_runs WHERE cutoff_from = ? AND cutoff_to = ? LIMIT 1");
mysqli_stmt_bind_param($runStmt, 'ss', $from, $to);
if (!mysqli_stmt_execute($runStmt)) { echo "Lookup failed: " . mysqli_error($dbc) . "\n"; exit(2); }
$res = mysqli_stmt_get_result($runStmt);
$row = $res ? mysqli_fetch_assoc($res) : null;
if (!$row) { echo "No run found for this cutoff.\n"; exit(0); }
$runId = (int)$row['id'];
$delStmt = mysqli_prepare($dbc, "DELETE FROM payroll_cutoff_items WHERE run_id = ?");
mysqli_stmt_bind_param($delStmt, 'i', $runId);
if (!mysqli_stmt_execute($delStmt)) { echo "Delete failed: " . mysqli_error($dbc) . "\n"; exit(3); }
$deleted = mysqli_stmt_affected_rows($delStmt);
echo "Deleted rows: $deleted\n";
