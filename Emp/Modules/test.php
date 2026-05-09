<?php
    include 'dbcon.php';
    /** @var mysqli $dbc */

    $file = __DIR__ . '/Attendance_module.php';
    echo file_get_contents($file);

    $input = file_get_contents('php://input');
    $data = json_decode($input, true) ?? [];

    $sql = "SELECT ";


?>
