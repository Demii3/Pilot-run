<?php 
    include 'dbcon.php';
    /** @var mysqli $dbc */

    $input = file_get_contents('php://input');
    $data = json_decode($input, true) ?? [];
    $userId = $data['USER_ID'] ?? 0;
    $querydata = [];
    $error = '';

    $stmt = mysqli_prepare($dbc, "SELECT geofences.name, geofences.coordinates 
                                  FROM `geofences` 
                                  JOIN employee_location 
                                  ON employee_location.loc_id = geofences.id 
                                  WHERE employee_location.User_Id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $querydata[] = $row;
            }
        } else {
            $error = "Error fetching location data: " . mysqli_error($dbc);
        }
    } else {
        $error = "Database error: failed to prepare statement.";
    }
    mysqli_stmt_close($stmt);
    echo json_encode(['querydata' => $querydata, 'error' => $error]);
?>