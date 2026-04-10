<?php
    session_start();
    $msg = '';
    $duration = (strtotime($_POST['Clock_out']) - strtotime($_SESSION['Clock_in'])) / 60;
    include './dbcon.php';
    $sql = "UPDATE employee_attendance SET `Clock_out` = '" . $_POST['Clock_out'] . "', `Duration` = '" . $duration . "' WHERE `Emp_id` = " . $_POST['Emp_id'] . " AND `Clock_in` = '" . $_SESSION['Clock_in'] . "'";
    $result = mysqli_query($dbc, $sql);
    $sql2 = "UPDATE users SET `Work_status` = '" . $_POST['Work_status'] . "' WHERE `User_id` = " . $_POST['Emp_id'];
    $result2 = mysqli_query($dbc, $sql2);
    if($result && $result2) {
        $msg .= $duration . " minutes worked. Attendance recorded successfully.";
    } else {
        $msg .= "Error recording attendance: " . mysqli_error($dbc);
    }
    $_SESSION['Work_status'] = $_POST['Work_status'];
    echo $msg;
?>