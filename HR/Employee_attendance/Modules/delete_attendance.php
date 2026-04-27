<?php
    header('Content-Type: application/json');

    include './dbcon.php';

    if (!isset($_POST['data']) || !is_array($_POST['data']) || !isset($_POST['data']['id'])) {
        echo json_encode(['msg' => 'Invalid request payload.']);
        exit;
    }

    $data = $_POST['data'];
    $sql = "DELETE FROM employee_attendance WHERE Attendance_id = " . (int) $data['id'];
    $result = mysqli_query($dbc, $sql);
    
    if ($result) {
        echo json_encode(['msg' => 'Attendance record deleted successfully.']);
    } else {
        echo json_encode(['msg' => 'Failed to delete attendance record: ' . mysqli_error($dbc)]);
    }
?>