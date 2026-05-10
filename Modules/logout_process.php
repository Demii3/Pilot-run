<?php
    session_start();

    // Prevent browser caching to stop users from logging back in via back button
    header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private');
    header('Cache-Control: post-check=0, pre-check=0', FALSE);
    header('Pragma: no-cache');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

    include './dbcon.php';

    function parseAttendanceTimeToMinutes($timeValue) {
        $timeValue = trim((string) $timeValue);

        if ($timeValue === '') {
            return null;
        }

        $formats = ['g:i:s A', 'h:i:s A', 'g:i A', 'h:i A', 'H:i:s', 'H:i'];

        foreach ($formats as $format) {
            $dateTime = DateTime::createFromFormat($format, $timeValue);
            if ($dateTime instanceof DateTime) {
                return ((int) $dateTime->format('H')) * 60 + (int) $dateTime->format('i');
            }
        }

        return null;
    }

    /** @var mysqli $dbc */
    function findOpenAttendanceId($dbc, $empId) {
        $sqlFind = "SELECT Attendance_id
                    FROM employee_attendance
                    WHERE Emp_id = $empId
                      AND (Clock_out IS NULL OR Clock_out = '')
                    ORDER BY Attendance_id DESC
                    LIMIT 1";
        $resultFind = mysqli_query($dbc, $sqlFind);

        if ($resultFind && mysqli_num_rows($resultFind) > 0) {
            return (int) mysqli_fetch_array($resultFind)['Attendance_id'];
        }

        return 0;
    }
    /** @var mysqli $dbc */
    if (isset($_SESSION['login'], $_SESSION['type']) && $_SESSION['type'] === 'Emp' && !empty($_SESSION['attendance_active'])) {
        $empId = (int) ($_SESSION['id'] ?? 0);
        $attendanceId = isset($_SESSION['Attendance_id']) ? (int) $_SESSION['Attendance_id'] : 0;

        if ($attendanceId <= 0 && $empId > 0) {
            $attendanceId = findOpenAttendanceId($dbc, $empId);
        }

        if ($attendanceId > 0) {
            $clockoutTime = date('g:i:s A');
            $clockoutStatus = 'Tapped-out';
            $duration = 0;

            $sqlClockIn = "SELECT `Clock_in` FROM employee_attendance WHERE `Attendance_id` = $attendanceId LIMIT 1";
            $resultClockIn = mysqli_query($dbc, $sqlClockIn);

            if ($resultClockIn && mysqli_num_rows($resultClockIn) > 0) {
                $clockInRow = mysqli_fetch_array($resultClockIn);
                $clockInTime = $clockInRow['Clock_in'] ?? '';
                $clockInMinutes = parseAttendanceTimeToMinutes($clockInTime);
                $clockOutMinutes = parseAttendanceTimeToMinutes($clockoutTime);

                if ($clockInMinutes !== null && $clockOutMinutes !== null) {
                    $duration = $clockOutMinutes - $clockInMinutes;
                    if ($duration < 0) {
                        $duration += 24 * 60;
                    }
                }
            }

            $clockoutTimeEscaped = mysqli_real_escape_string($dbc, $clockoutTime);
            $clockoutStatusEscaped = mysqli_real_escape_string($dbc, $clockoutStatus);

            $sqlUpdate = "UPDATE employee_attendance
                          SET `Clock_out` = '$clockoutTimeEscaped',
                              `Clockout_Status` = '$clockoutStatusEscaped',
                              `Duration` = $duration
                          WHERE `Attendance_id` = $attendanceId";
            mysqli_query($dbc, $sqlUpdate);

            if ($empId > 0) {
                $sqlUser = "UPDATE users
                            SET `Clockin_status` = 'Tapped-out'
                            WHERE `User_id` = $empId";
                mysqli_query($dbc, $sqlUser);
            }
        }
    }

    session_destroy();
    $_SESSION = [];
    
    // Invalidate session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    // Check if this is an AJAX request
    $isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
    
    if ($isAjax) {
        // Return JSON for AJAX requests
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
    } else {
        // Force browser to not cache the login page after logout
        // Redirect to main index page with cache busting parameter
        header('Location: ../?nocache=' . time());
    }
    exit();
?>