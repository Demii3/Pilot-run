<?php
    include 'dbcon.php';
    /** @var mysqli $dbc */

    $file = __DIR__ . '/EmpInfo_module.php';

    $dataFile = file_get_contents($file);
    $error = '';

    $input = file_get_contents('php://input');
    $data = json_decode($input, true) ?? [];
    $userId = $data['USER_ID'] ?? 0;
    $querydata = [];

    $sql = "SELECT `name`, `email`, `department`, `username`
            FROM employees
            WHERE id = $userId";

    $result = mysqli_query($dbc, $sql);
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $querydata = $row;
    } else {
        $error = mysqli_error($dbc);
    }

    echo json_encode(['querydata' => $querydata, 'datafile' => $dataFile, 'error' => $error]);
?>
