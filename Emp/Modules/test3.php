<?php
    include 'dbcon.php';
    /** @var mysqli $dbc */

    $file = __DIR__ . '/History_module.php';

    $dataFile = file_get_contents($file);
    $error = '';

    $input = file_get_contents('php://input');
    $data = json_decode($input, true) ?? [];
    $userId = $data['USER_ID'] ?? 0;
    $querydata = [];

    $sql = mysqli_prepare($dbc, "SELECT `Attendance_id`, `Date`, `Location`, Clock_in, Clock_out, Work_Classification FROM employee_attendance
            WHERE Emp_id = ?");

    mysqli_stmt_bind_param($sql, "i", $userId);
    $result = mysqli_stmt_execute($sql);
    $result = mysqli_stmt_get_result($sql);
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $querydata[] = $row;
        }
    } else {
        $error = mysqli_error($dbc);
    }

    echo json_encode(['querydata' => $querydata, 'datafile' => $dataFile, 'error' => $error]);
?>
