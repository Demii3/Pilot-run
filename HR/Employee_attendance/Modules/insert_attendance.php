<?php
header('Content-Type: application/json');

include 'dbcon.php';
/** @var mysqli $dbc */

$response = array('success' => false, 'msg' => 'An error occurred');

try {
    // Validate and sanitize input
    $empId = isset($_POST['empId']) ? mysqli_real_escape_string($dbc, $_POST['empId']) : '';
    $name = isset($_POST['name']) ? mysqli_real_escape_string($dbc, $_POST['name']) : '';
    $department = isset($_POST['department']) ? mysqli_real_escape_string($dbc, $_POST['department']) : '';
    $date = isset($_POST['date']) ? mysqli_real_escape_string($dbc, $_POST['date']) : '';
    $location = isset($_POST['location']) ? mysqli_real_escape_string($dbc, $_POST['location']) : '';
    $clockIn = isset($_POST['clockIn']) ? mysqli_real_escape_string($dbc, $_POST['clockIn']) : '';
    $clockInStatus = isset($_POST['clockInStatus']) ? mysqli_real_escape_string($dbc, $_POST['clockInStatus']) : '';
    $clockOut = isset($_POST['clockOut']) ? mysqli_real_escape_string($dbc, $_POST['clockOut']) : '';
    $clockOutStatus = isset($_POST['clockOutStatus']) ? mysqli_real_escape_string($dbc, $_POST['clockOutStatus']) : '';
    $allowOvertime = isset($_POST['allowOvertime']) ? (int)$_POST['allowOvertime'] : 0;
    $workClassification = isset($_POST['workClassification']) ? mysqli_real_escape_string($dbc, $_POST['workClassification']) : 'R';

    // Validate required fields
    if (empty($empId) || empty($date) || empty($clockIn) || empty($clockOut)) {
        $response['msg'] = 'Missing required fields';
        echo json_encode($response);
        exit;
    }

    // Calculate duration if both clock times are provided
    $duration = '';
    if (!empty($clockIn) && !empty($clockOut)) {
        $clockInTime = strtotime($clockIn);
        $clockOutTime = strtotime($clockOut);
        
        if ($clockOutTime >= $clockInTime) {
            $diffSeconds = $clockOutTime - $clockInTime;
            $hours = floor($diffSeconds / 3600);
            $minutes = floor(($diffSeconds % 3600) / 60);
            $duration = sprintf('%02d:%02d', $hours, $minutes);
        } else {
            $response['msg'] = 'Clock out time must be after clock in time';
            echo json_encode($response);
            exit;
        }
    }

    // Insert into database
    $insertSql = "INSERT INTO employee_attendance 
                  (Attendance_id, Emp_id, Clockin_status, Clockout_status, `Location`, `Date`, Clock_in, Clock_out, Duration, AO, Work_classification) 
                  VALUES 
                  ((SELECT MAX(Attendance_id) + 1 FROM employee_attendance), '$empId', '$clockInStatus', '$clockOutStatus', '$location', '$date', '$clockIn', '$clockOut', '$duration', $allowOvertime, '$workClassification')";

    if (mysqli_query($dbc, $insertSql)) {
        $response['success'] = true;
        $response['msg'] = 'Attendance record inserted successfully';
        $response['data'] = array(
            'empId' => $empId,
            'name' => $name,
            'department' => $department,
            'date' => $date,
            'location' => $location,
            'clockIn' => $clockIn,
            'clockInStatus' => $clockInStatus,
            'clockOut' => $clockOut,
            'clockOutStatus' => $clockOutStatus,
            'allowOvertime' => $allowOvertime,
            'workClassification' => $workClassification,
            'duration' => $duration
        );
    } else {
        $response['msg'] = 'Database error: ' . mysqli_error($dbc);
    }

} catch (Exception $e) {
    $response['msg'] = 'Exception: ' . $e->getMessage();
}

echo json_encode($response);
?>