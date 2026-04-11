<?php
    session_start();

    $msg = '';

    include './dbcon.php';
    $sql = "INSERT INTO employee_attendance (Emp_id, `Date`, `Location`, `Clock_in`, `Clockin_Status`) VALUES (" . $_POST['emp_id'] . ", '" . date('Y-m-d', strtotime($_POST['Date'])) . "', '" . $_POST['Location'] . "', '" . $_POST['Clock_in'] . "', '" . $_POST['Status'] . "')";
    $result = mysqli_query($dbc, $sql);
    $sql2 = "UPDATE users SET `Work_status` = '" . $_POST['Clockin_status'] . "' WHERE `User_id` = " . $_POST['emp_id'];
    $result2 = mysqli_query($dbc, $sql2);
    if($result && $result2) {
        $msg .=" attendance recorded successfully.";
    } else {
        $msg .= "Error recording attendance: " . mysqli_error($dbc);
    };

    $_SESSION['Clock-in'] = $_POST['Clock_in'];
    $_SESSION['Clock-status'] = $_POST['Clockin_status'];
    $_SESSION['Location'] = [$_POST['Location']];
    $_SESSION['Clock-inStatus'] = [$_POST['Status']];
    echo $msg;
?>