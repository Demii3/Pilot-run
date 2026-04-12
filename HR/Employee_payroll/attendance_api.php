<?php
ob_start();
header('Content-Type: application/json');

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

include '../../Modules/dbcon.php';

function respond($success, $data = null, $message = '', $statusCode = 200) {
    http_response_code($statusCode);
    if (ob_get_length()) {
        ob_end_clean();
    }
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ]);
    exit;
}

function handleGetRequest($dbc) {
    $query = "SELECT
                ea.Attendance_id,
                ea.Emp_id,
                e.name AS employee_name,
                ea.Date,
                ea.Clock_in,
                ea.Clock_out,
                ea.Clockin_status,
                ea.Clockout_status,
                ea.Duration,
                ea.AO
              FROM employee_attendance ea
              INNER JOIN employees e ON ea.Emp_id = e.id
              ORDER BY ea.Date DESC, ea.Attendance_id DESC";

    $result = mysqli_query($dbc, $query);
    if (!$result) {
        respond(false, null, 'Failed to fetch attendance: ' . mysqli_error($dbc), 500);
    }

    $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
    respond(true, $rows);
}

try {
    if (!$dbc) {
        respond(false, null, 'Database connection failed.', 500);
    }

    $method = $_SERVER['REQUEST_METHOD'];
    if ($method === 'GET') {
        handleGetRequest($dbc);
    }

    respond(false, null, 'Method not allowed.', 405);
} catch (Throwable $e) {
    respond(false, null, 'Server error: ' . $e->getMessage(), 500);
}
