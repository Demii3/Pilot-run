<?php
require __DIR__ . '/../../Modules/dbcon.php';
$res = mysqli_query($dbc, "SELECT cutoff_from, cutoff_to FROM payroll_cutoff_runs ORDER BY processed_at DESC LIMIT 1");
if ($res) {
    $r = mysqli_fetch_assoc($res);
    if ($r) {
        echo $r['cutoff_from'] . '|' . $r['cutoff_to'];
        exit(0);
    }
}
echo '';
