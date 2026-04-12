<?php
    include './dbcon.php';
    $msg = '';

    if (isset($_POST['Attendance_ID']) && isset($_POST['newDate']) && isset($_POST['newClock_in']) && isset($_POST['newClock_out'])) {
        $attendanceId = intval($_POST['Attendance_ID']);
        $newDate = mysqli_real_escape_string($dbc, $_POST['newDate']);
        $newClockIn = mysqli_real_escape_string($dbc, $_POST['newClock_in']);
        $newClockOut = mysqli_real_escape_string($dbc, $_POST['newClock_out']);
        $duration = intval($_POST['duration']);
        $ao = isset($_POST['AO']) ? intval($_POST['AO']) : 0;

        if ($attendanceId <= 0) {
            echo 'error';
            exit;
        }

        $sql = "UPDATE employee_attendance 
                SET `Date` = '" . $newDate . "',
                    Clock_in = '" . $newClockIn . "',
                    Clock_out = '" . $newClockOut . "',
                    Clockin_status = '" . $_POST['newClock_in_status'] . "',
                    Clockout_status = '" . $_POST['newClock_out_status'] . "',
                    Duration = " . $duration . ",
                    AO = " . $ao . " 
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