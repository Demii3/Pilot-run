<?php
    session_start();

    $msg = '';

    include './dbcon.php';
    $sql1 = "INSERT INTO employee_attendance (Emp_id, `Date`, `Location`, `Coordinates`, `Clock_in`, `Clockin_Status`) 
            VALUES (" . $_POST['emp_id'] . ", 
                    '" . $_POST['date'] . "', 
                    '" . $_POST['location'] . "', 
                    '" . $_POST['coordinates'] . "', 
                    '" . $_POST['clockin_time'] . "', 
                    '" . $_POST['clockin_status'] . "')";

    $result1 = mysqli_query($dbc, $sql1);
    
    $sql2 = "UPDATE users 
             SET `Clockin_status` = 'Tapped-in' 
             WHERE `User_id` = " . $_POST['emp_id'];

    $result2 = mysqli_query($dbc, $sql2);

    $sql3 = "SELECT MAX(Attendance_id) AS last_id FROM employee_attendance WHERE Emp_id = " . $_POST['emp_id'];

    $result3 = mysqli_query($dbc, $sql3);

    if($result1 && $result2 && $result3) {
        $msg .=" attendance recorded successfully.";
        $_SESSION['Clock-in'] = $_POST['clockin_time'];
        $_SESSION['date'] = $_POST['date'];
        $_SESSION['selectedLocation'] = $_POST['location'];
        $_SESSION['selectedCoordinates'] = $_POST['coordinates'];
        $_SESSION['Clockin-status'] = $_POST['clockin_status'];
        $_SESSION['Attendance_id'] = mysqli_fetch_array($result3)['last_id'];
        $_SESSION['attendance_active'] = true;
        unset($_SESSION['Clockout-status']);
    } else {
        $msg .= "Error recording attendance: " . mysqli_error($dbc);
    };
    echo $msg;
?>