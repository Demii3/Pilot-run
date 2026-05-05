<?php
    header('Content-Type: application/json');

    include 'dbcon.php';
    /** @var mysqli $dbc */

    $searchTerm = isset($_POST['search']) ? mysqli_real_escape_string($dbc, $_POST['search']) : '';
    $id = isset($_POST['id']) ? mysqli_real_escape_string($dbc, $_POST['id']) : '';

    $sql = "SELECT `name` 
            FROM geofences
            JOIN employee_location ON geofences.id = employee_location.loc_id ";

    if (strlen($searchTerm) == 0) {
        $sql .= "WHERE employee_location.User_Id = '$id'";

    } else {
        $sql .= "WHERE geofences.name LIKE '%$searchTerm%'
                AND employee_location.User_Id = '$id'";
    };

    $result = mysqli_query($dbc, $sql);

    $location = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $location[] = $row;
    };

    echo json_encode($location);
?>