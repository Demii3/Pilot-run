<?php
    session_start();

    include './dbcon.php';
    /** @var mysqli $dbc */

    $msg = 'error';

    if (!isset($_SESSION['login']) || $_SESSION['type'] != 'Emp') {
        echo $msg;
        exit();
    }

    if (!isset($_SESSION['id'], $_POST['date'], $_POST['location'], $_POST['coordinates'], $_POST['clockin_time'], $_POST['clockin_status'])) {
        echo $msg;
        exit();
    }

    $empId = (int) $_SESSION['id'];
    $date = mysqli_real_escape_string($dbc, $_POST['date']);
    $location = mysqli_real_escape_string($dbc, $_POST['location']);
    $coordinates = mysqli_real_escape_string($dbc, $_POST['coordinates']);
    $clockinTime = mysqli_real_escape_string($dbc, $_POST['clockin_time']);
    $clockinStatus = mysqli_real_escape_string($dbc, $_POST['clockin_status']);

    $sql1 = "INSERT INTO employee_attendance (Emp_id, `Date`, `Location`, `Coordinates`, `Clock_in`, `Clockin_Status`) 
            VALUES ($empId, 
                    '$date', 
                    '$location', 
                    '$coordinates', 
                    '$clockinTime', 
                    '$clockinStatus')";

    $result1 = mysqli_query($dbc, $sql1);

    $sql2 = "UPDATE users 
             SET `Clockin_status` = 'Tapped-in' 
             WHERE `User_id` = $empId";

    $result2 = mysqli_query($dbc, $sql2);

    $sql3 = "SELECT MAX(Attendance_id) AS last_id FROM employee_attendance WHERE Emp_id = $empId";
    $result3 = mysqli_query($dbc, $sql3);

    if ($result1 && $result2 && $result3) {
        $_SESSION['Clock-in'] = $_POST['clockin_time'];
        $_SESSION['date'] = $_POST['date'];
        $_SESSION['selectedLocation'] = $_POST['location'];
        $_SESSION['selectedCoordinates'] = $_POST['coordinates'];
        $_SESSION['Clockin-status'] = $_POST['clockin_status'];
        $_SESSION['Attendance_id'] = mysqli_fetch_array($result3)['last_id'];
        $_SESSION['attendance_active'] = true;
        unset($_SESSION['Clockout-status']);

        $msg = 'success';
    }

    echo $msg;
?>