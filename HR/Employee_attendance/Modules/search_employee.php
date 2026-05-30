<?php
    header('Content-Type: application/json');

    include 'dbcon.php';
    /** @var mysqli $dbc */

    $rawInput = file_get_contents('php://input');
    $jsonInput = json_decode($rawInput, true);

    if (is_array($jsonInput) && array_key_exists('searchTerm', $jsonInput)) {
        $searchTerm = mysqli_real_escape_string($dbc, $jsonInput['searchTerm']);
    } else {
        $searchTerm = isset($_POST['searchTerm']) ? mysqli_real_escape_string($dbc, $_POST['searchTerm']) : '';
    }

    $sql = '';

    if (strlen($searchTerm) == 0) {
        $sql = "SELECT id, `name`, department, `status` 
            FROM employees
            LIMIT 30";
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