<?php
    session_start();

    include './dbcon.php';
    /** @var mysqli $dbc */

    $msg = 'error';

    if (!isset($_SESSION['login']) || $_SESSION['empType'] != 'Emp') {
        echo mysqli_error($dbc);
        exit();
    }

    if (!isset($_POST['userId'], $_POST['date'], $_POST['location'], $_POST['coordinates'], $_POST['timeIn'], $_POST['timeInStatus'])) {
        echo mysqli_error($dbc);
        exit();
    }

    $empId = (int) $_POST['userId'];
    $date = mysqli_real_escape_string($dbc, $_POST['date']);
    $location = mysqli_real_escape_string($dbc, $_POST['location']);
    $coordinates = mysqli_real_escape_string($dbc, $_POST['coordinates']);
    $clockinTime = mysqli_real_escape_string($dbc, $_POST['timeIn']);
    $clockinStatus = mysqli_real_escape_string($dbc, $_POST['timeInStatus']);

    $sql1 = "INSERT INTO employee_attendance (Emp_id, `Date`, `Location`, `Coordinates`, `Clock_in`, `Clockin_Status`) 
            VALUES ($empId, 
                    '$date', 
                    '$location', 
                    '$coordinates', 
                    '$clockinTime', 
                    '$clockinStatus')";

    $result1 = mysqli_query($dbc, $sql1);

    if (!$result1) {
        echo mysqli_error($dbc);
        exit();
    }

    $sql2 = "UPDATE users 
             SET `Work_status` = 'Tapped-in' 
             WHERE `User_id` = $empId";

    $result2 = mysqli_query($dbc, $sql2);

    $sql3 = "SELECT MAX(Attendance_id) AS last_id FROM employee_attendance WHERE Emp_id = $empId";
    $result3 = mysqli_query($dbc, $sql3);

    if ($result1 && $result2 && $result3) {
        $_SESSION['Clock-in'] = $_POST['timeIn'];
        $_SESSION['date'] = $_POST['date'];
        $_SESSION['selectedLocation'] = $_POST['location'];
        $_SESSION['selectedCoordinates'] = $_POST['coordinates'];
        $_SESSION['Clockin-status'] = $_POST['timeInStatus'];
        $_SESSION['Attendance_id'] = mysqli_fetch_array($result3)['last_id'];
        $_SESSION['attendance_active'] = true;
        unset($_SESSION['Clockout-status']);

        $msg = 'success';
    }

    echo $msg;
?>