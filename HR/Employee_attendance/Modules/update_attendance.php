<?php
    header('Content-Type: application/json');

    include './dbcon.php';
    /** @var mysqli $dbc */

    if (!isset($_POST['data']) || !is_array($_POST['data'])) {
        echo json_encode(['msg' => 'Invalid request payload.']);
        exit;
    }

    $items = $_POST['data'];
    if (!isset($items['id']) || $items['id'] === '') {
        echo json_encode(['msg' => 'Missing attendance id.']);
        exit;
    }

    $attendance_id = (int) $items['id'];
    $date = $items['date'] ?? '';
    $clock_in = $items['clockIn'] ?? '';
    $clockin_status = $items['clockInStatus'] ?? '';
    $clock_out = $items['clockOut'] ?? '';
    $clockout_status = $items['clockOutStatus'] ?? '';
    $duration = $items['duration'] ?? 0;
    $allow_overtime = $items['allowOvertime'] ?? 0;
    $workClasssification = $items['workClassification'] ?? '';

    $sql = "UPDATE employee_attendance
            SET Date = ?, Clock_in = ?, Clockin_status = ?, Clock_out = ?, Clockout_status = ?, Duration = ?, AO = ?, Work_classification = ?
            WHERE Attendance_id = ?";
    $stmt = mysqli_prepare($dbc, $sql);

    if (!$stmt) {
        echo json_encode(['msg' => 'Failed to prepare update query: ' . mysqli_error($dbc)]);
        exit;
    }

    mysqli_stmt_bind_param($stmt, 'sssssiisi', $date, $clock_in, $clockin_status, $clock_out, $clockout_status, $duration, $allow_overtime, $workClasssification, $attendance_id);
    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        echo json_encode(['msg' => 'Record updated successfully']);
    } else {
        echo json_encode(['msg' => 'Recording of attendance failed: ' . mysqli_error($dbc)]);
    }

    mysqli_stmt_close($stmt);
?>