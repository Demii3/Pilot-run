<?php
    include 'dbcon.php';
    /** @var mysqli $dbc */

    $file = __DIR__ . '/Attendance_module.php';

    $dataFile = file_get_contents($file);

    $input = file_get_contents('php://input');
    $data = json_decode($input, true) ?? [];
    $userId = $data['USER_ID'] ?? 0;
    $querydata = 'Unknown';

    $sql = "SELECT Work_Status, employee_attendance.Location, employee_attendance.Coordinates FROM users
            JOIN employee_attendance ON users.User_id = employee_attendance.Emp_id
            WHERE users.User_id = $userId
            AND employee_attendance.Attendance_id = (SELECT MAX(Attendance_id) FROM employee_attendance WHERE Emp_id = $userId)";
    $result = mysqli_query($dbc, $sql);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $querydata = $row;
    } 


    echo json_encode(['workStatus' => $querydata, 'datafile' => $dataFile]);
?>
