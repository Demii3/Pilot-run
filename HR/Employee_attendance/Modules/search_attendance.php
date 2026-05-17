<?php
header('Content-Type: application/json');

include 'dbcon.php';
/** @var mysqli $dbc */

$searchTerm = isset($_POST['search']) ? mysqli_real_escape_string($dbc, $_POST['search']) : '';
$searchDate = isset($_POST['searchDate']) ? mysqli_real_escape_string($dbc, $_POST['searchDate']) : '';

$sql = "SELECT employee_attendance.*, employees.name, employees.department 
        FROM employee_attendance 
        LEFT JOIN employees 
        ON employee_attendance.Emp_id = employees.id
        WHERE (
            employees.name LIKE '%$searchTerm%'
            OR employees.department LIKE '%$searchTerm%'
            OR employee_attendance.Location LIKE '%$searchTerm%'
        )";

if ($searchDate !== '') {
    $sql .= " AND employee_attendance.Date = '$searchDate'";
}

$sql .= " ORDER BY employee_attendance.Attendance_id DESC
          LIMIT 100";

$result = mysqli_query($dbc, $sql);
$data = array();

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $status = strtolower($row['Clockin_status']);
        $statusClass = '';
        if ($status == 'on-time' || $status == 'ontime') {
            $statusClass = 'status-ontime';
        } elseif ($status == 'late') {
            $statusClass = 'status-late';
        } elseif ($status == 'absent') {
            $statusClass = 'status-absent';
        } elseif ($status == 'on-leave' || $status == 'onleave') {
            $statusClass = 'status-onleave';
        }
        $clockinStatusHtml = "<span class='" . $statusClass . "'>" . $row['Clockin_status'] . "</span>";

        $outStatus = strtolower($row['Clockout_status']);
        $outStatusClass = '';
        if ($outStatus == 'present') {
            $outStatusClass = 'status-present';
        } elseif ($outStatus == 'under-time') {
            $outStatusClass = 'status-undertime';
        } elseif ($outStatus == 'absent') {
            $outStatusClass = 'status-absent';
        } elseif ($outStatus == 'on-leave' || $outStatus == 'onleave') {
            $outStatusClass = 'status-onleave';
        } elseif (strpos($outStatus, 'over-time') !== false || strpos($outStatus, 'overtime') !== false) {
            if ($row['AO'] == 1) {
                $outStatusClass = 'status-overtime-allowed';
            } elseif ($row['AO'] !== 1) {
                $outStatusClass = 'status-overtime-rejected';
            }
        }
        $clockoutStatusHtml = "<span class='" . $outStatusClass . "'>" . $row['Clockout_status'] . "</span>";
        $WorkClassification = '';

        switch (strtolower($row['Work_Classification'])) {
            case 'r':
                $WorkClassification = 'Regular Day';
                break;
            case 'sh':
                $WorkClassification = 'Special Holiday';
                break;
            case 'lh':
                $WorkClassification = 'Legal Holiday';
                break;
            default:
                $WorkClassification = $row['Work_Classification'];
        }

        $data[] = array(
            'Attendance_id' => $row['Attendance_id'],
            'Emp_id' => $row['Emp_id'],
            'name' => $row['name'],
            'department' => $row['department'],
            'Date' => $row['Date'],
            'Location' => $row['Location'],
            'Clock_in' => $row['Clock_in'],
            'Clockin_status' => $row['Clockin_status'],
            'Clockin_status_html' => $clockinStatusHtml,
            'Clock_out' => $row['Clock_out'],
            'Clockout_status' => $row['Clockout_status'],
            'Clockout_status_html' => $clockoutStatusHtml,
            'Duration' => $row['Duration'],
            'AO' => $row['AO'],
            'Work_day_status' => $WorkClassification,
            'userLoc' => $row['User_loc'],
            'inGeofence'=> $row['In_geofence'],
            'coordinates'=> $row['Coordinates']
        );
    }
}

echo json_encode(['data' => $data]);
?>
