<?php
    include './dbcon.php';
    $msg = '';
    $sql = "DELETE FROM employee_attendance WHERE Emp_id = " . $_POST['emp_Id'] . " AND Clock_in = '" . $_POST['clock_in'] . "' AND Clock_out = '" . $_POST['clock_out'] . "'";
    $result = mysqli_query($dbc, $sql);
    if($result){
        $msg .='success';
    } else {
        $msg .='error';
    }
    echo $msg;
?>