<?php
    session_start();
    $msg = '';

    include './dbcon.php';
    $sql1 = "UPDATE employee_attendance 
             SET `Clock_out` = '" . $_POST['clockout_time'] . "',
                 `Clockout_Status` = '" . $_POST['clockout_status'] . "',
                 `Duration` = '" . $_POST['duration'] . "'
            WHERE `Attendance_id` = '" . $_SESSION['Attendance_id'] . "'";
    $result1 = mysqli_query($dbc, $sql1);

    $sql2 = "UPDATE users 
             SET `Clockin_status` = 'Tapped-out' 
             WHERE `User_id` = " . $_SESSION['id'];
    $result2 = mysqli_query($dbc, $sql2);

    if($result1 && $result2) {
        $msg .= $_POST['duration'] . " minutes worked. Attendance recorded successfully.";
    } else {
        $msg .= "Error recording attendance: " . mysqli_error($dbc);
    }
    $_SESSION['Clockout-status'] = $_POST['clockout_status'];
    echo $msg;
?>