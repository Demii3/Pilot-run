<?php
    include './dbcon.php';
    $msg = '';
    $sql = "DELETE FROM employee_attendance WHERE Attendance_ID = " . $_POST['Attendance_ID'];
    $result = mysqli_query($dbc, $sql);
    if($result){
        $msg .='success';
    } else {
        $msg .='error';
    }
    echo $msg;
?>