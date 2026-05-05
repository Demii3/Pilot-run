<?php
    header('Content-Type: application/json');

    include 'dbcon.php';
    /** @var mysqli $dbc */

    $searchTerm = isset($_POST['search']) ? mysqli_real_escape_string($dbc, $_POST['search']) : '';
    $sql = '';

    if (strlen($searchTerm) == 0) {
        $sql = "SELECT id, `name`, department, `status` 
            FROM employees
            WHERE `name` LIKE '%$searchTerm%'
            LIMIT 15";
    } else {
        $sql = "SELECT id, `name`, department, `status` 
                FROM employees
                WHERE `name` LIKE '%$searchTerm%'";
    };

    $result = mysqli_query($dbc, $sql);

    $employees = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $employees[] = $row;
    }

    echo json_encode($employees);
?>