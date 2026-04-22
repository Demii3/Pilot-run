<?php
    header('Content-Type: application/json');

    include './dbcon.php';

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
    $clock_in = $items['clockIn'] ?? '';
    $clockin_status = $items['clockInStatus'] ?? '';
    $clock_out = $items['clockOut'] ?? '';
    $clockout_status = $items['clockOutStatus'] ?? '';

    $sql = "UPDATE employee_attendance
            SET Clock_in = ?, Clockin_status = ?, Clock_out = ?, Clockout_status = ?
            WHERE Attendance_id = ?";
    $stmt = mysqli_prepare($dbc, $sql);

    if (!$stmt) {
        echo json_encode(['msg' => 'Failed to prepare update query: ' . mysqli_error($dbc)]);
        exit;
    }

    mysqli_stmt_bind_param($stmt, 'ssssi', $clock_in, $clockin_status, $clock_out, $clockout_status, $attendance_id);
    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        echo json_encode(['msg' => 'Record updated successfully']);
    } else {
        echo json_encode(['msg' => 'Recording of attendance failed: ' . mysqli_error($dbc)]);
    }

    mysqli_stmt_close($stmt);
?>