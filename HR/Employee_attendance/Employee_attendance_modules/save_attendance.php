<?php
    include './dbcon.php';
    $msg = $_POST['Emp_id'] . " - " . $_POST['newClock_in'] . " - " . $_POST['newClock_out'] . " - " . $_POST['old_clockIn'] . " - " . $_POST['old_clockOut'];
    echo $msg;
?>