<?php
    include './dbcon.php';
    $msg = '';

    if (isset($_POST['Attendance_ID']) && isset($_POST['newClock_in']) && isset($_POST['newClock_out'])) {
        $attendanceId = intval($_POST['Attendance_ID']);
        $newClockIn = mysqli_real_escape_string($dbc, $_POST['newClock_in']);
        $newClockOut = mysqli_real_escape_string($dbc, $_POST['newClock_out']);
        $duration = intval($_POST['duration']);

        if ($attendanceId <= 0) {
            echo 'error';
            exit;
        }

        $sql = "UPDATE employee_attendance 
                SET Clock_in = '" . $newClockIn . "',
                    Clock_out = '" . $newClockOut . "',
                    Clockin_status = '" . $_POST['newClock_in_status'] . "',
                    Clockout_status = '" . $_POST['newClock_out_status'] . "',
                    Duration = " . $duration . " 
                WHERE Attendance_ID = " . $attendanceId;
        $result = mysqli_query($dbc, $sql);

        if ($result) {
            $msg = 'success';
        } else {
            $msg = 'error';
        }
    } else {
        $msg = 'error';
    }

    echo $msg;
?>