<?php
    session_start();

    include './dbcon.php';

    $msg = 'error';

    if (!isset($_SESSION['login']) || $_SESSION['type'] != 'Emp') {
        echo $msg;
        exit();
    }

    if (!isset($_SESSION['id'], $_POST['clockout_time'], $_POST['clockout_status'])) {
        echo $msg;
        exit();
    }

    /** @var mysqli $dbc */

    $empId = (int) $_SESSION['id'];
    $clockoutTime = mysqli_real_escape_string($dbc, $_POST['clockout_time']);
    $clockoutStatus = mysqli_real_escape_string($dbc, $_POST['clockout_status']);
    $attendanceId = isset($_SESSION['Attendance_id']) ? (int) $_SESSION['Attendance_id'] : 0;

    if ($attendanceId <= 0) {
        $sqlFind = "SELECT Attendance_id
                    FROM employee_attendance
                    WHERE Emp_id = $empId
                    AND (Clock_out IS NULL OR Clock_out = '')
                    ORDER BY Attendance_id DESC
                    LIMIT 1";
        $resultFind = mysqli_query($dbc, $sqlFind);

        if ($resultFind && mysqli_num_rows($resultFind) > 0) {
            $attendanceId = (int) mysqli_fetch_array($resultFind)['Attendance_id'];
        }
    }

    if ($attendanceId <= 0) {
        echo $msg;
        exit();
    }

    $sqlClockIn = "SELECT `Clock_in` FROM employee_attendance WHERE `Attendance_id` = $attendanceId LIMIT 1";
    $resultClockIn = mysqli_query($dbc, $sqlClockIn);

    if (!$resultClockIn || mysqli_num_rows($resultClockIn) === 0) {
        echo $msg;
        exit();
    }

    $clockInRow = mysqli_fetch_array($resultClockIn);
    $clockInTime = $clockInRow['Clock_in'] ?? '';

    function parseAttendanceTimeToMinutes($timeValue) {
        $timeValue = trim((string) $timeValue);

        if ($timeValue === '') {
            return null;
        }

        $formats = ['g:i:s A', 'h:i:s A', 'g:i A', 'h:i A', 'H:i:s', 'H:i'];
        foreach ($formats as $format) {
            $dateTime = DateTime::createFromFormat($format, $timeValue);
            if ($dateTime instanceof DateTime) {
                return ((int) $dateTime->format('H')) * 60 + (int) $dateTime->format('i');
            }
        }

        return null;
    }

    $clockInMinutes = parseAttendanceTimeToMinutes($clockInTime);
    $clockOutMinutes = parseAttendanceTimeToMinutes($_POST['clockout_time']);
    $duration = 0;

    if ($clockInMinutes !== null && $clockOutMinutes !== null) {
        $duration = $clockOutMinutes - $clockInMinutes;

        if ($duration < 0) {
            $duration += 24 * 60;
        }
    }

    $sql1 = "UPDATE employee_attendance
             SET `Clock_out` = '$clockoutTime',
                 `Clockout_Status` = '$clockoutStatus',
                 `Duration` = $duration
             WHERE `Attendance_id` = $attendanceId";
    $result1 = mysqli_query($dbc, $sql1);

    $sql2 = "UPDATE users
             SET `Clockin_status` = 'Tapped-out'
             WHERE `User_id` = $empId";
    $result2 = mysqli_query($dbc, $sql2);

    if ($result1 && $result2) {
        $_SESSION['Clockout-status'] = $_POST['clockout_status'];
        $_SESSION['attendance_active'] = false;
        unset($_SESSION['Clock-in']);
        unset($_SESSION['selectedLocation']);
        unset($_SESSION['selectedCoordinates']);
        unset($_SESSION['Attendance_id']);

        $msg = 'success';
    }

    echo $msg;
?>