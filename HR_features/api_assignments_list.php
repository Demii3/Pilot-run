<?php
header('Content-Type: application/json');
include __DIR__ . '/../Modules/dbcon.php';

$result = ['success' => false, 'message' => 'Unable to load assignments', 'data' => []];

$query = "SELECT el.tb_id, el.User_Id AS employee_id, el.loc_id AS site_id, e.name AS employee_name, COALESCE(e.username, '') AS employee_username, g.name AS site_name FROM employee_location el JOIN employees e ON el.User_Id = e.id JOIN geofences g ON el.loc_id = g.id ORDER BY el.tb_id DESC";
$res = mysqli_query($dbc, $query);
if ($res) {
    $rows = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $rows[] = $row;
    }
    $result = ['success' => true, 'data' => $rows];
} else {
    $result['message'] = 'DB error: ' . mysqli_error($dbc);
}

echo json_encode($result);
