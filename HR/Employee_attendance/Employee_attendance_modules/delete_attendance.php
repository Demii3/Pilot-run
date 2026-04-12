<?php
    include './dbcon.php';
    $msg = '';

    if (isset($_POST['Attendance_IDs']) && is_array($_POST['Attendance_IDs'])) {
        $ids = array_map('intval', $_POST['Attendance_IDs']);
        $ids = array_values(array_filter($ids, function($id) {
            return $id > 0;
        }));

        if (count($ids) === 0) {
            echo 'error';
            exit;
        }

        $sql = "DELETE FROM employee_attendance WHERE Attendance_ID IN (" . implode(',', $ids) . ")";
    } elseif (isset($_POST['Attendance_ID'])) {
        $attendanceId = intval($_POST['Attendance_ID']);
        $sql = "DELETE FROM employee_attendance WHERE Attendance_ID = " . $attendanceId;
    } else {
        echo 'error';
        exit;
    }

    $result = mysqli_query($dbc, $sql);
    if($result){
        $msg .='success';
    } else {
        $msg .='error';
    }
    echo $msg;
?>