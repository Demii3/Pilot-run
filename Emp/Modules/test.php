<?php
    include 'dbcon.php';
    /** @var mysqli $dbc */

    $file = __DIR__ . '/Attendance_module.php';

    $dataFile = file_get_contents($file);

    $input = file_get_contents('php://input');
    $data = json_decode($input, true) ?? [];
    $userId = $data['USER_ID'] ?? 0;
    $querydata = 'Unknown';
    $error = '';

    $sql = "SELECT Work_Status, employee_attendance.Attendance_id, employee_attendance.Location, employee_attendance.Coordinates FROM users
            JOIN employee_attendance ON users.User_id = employee_attendance.Emp_id
            WHERE users.User_id = $userId
            AND employee_attendance.Attendance_id = (SELECT MAX(Attendance_id) FROM employee_attendance WHERE Emp_id = $userId)";
    $result = mysqli_query($dbc, $sql);
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $querydata = $row;
    } else {
        $sql2 = "SELECT MAX(Attendance_id) + 1 as max_id FROM employee_attendance WHERE Emp_id = $userId";
        $result2 = mysqli_query($dbc, $sql2);
        $row2 = mysqli_fetch_assoc($result2);
        $querydata = array(
            'Work_Status' => 'Tapped-out',
            'Attendance_id' => $row2['max_id'],
            'Location' => null,
            'Coordinates' => null
        );
    }


    echo json_encode(['querydata' => $querydata, 'datafile' => $dataFile, 'error' => $error]);
?>
