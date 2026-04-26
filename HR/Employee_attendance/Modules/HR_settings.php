<?php
    include './dbcon.php';
    $sql = "SELECT * FROM `hr_attendance_settings` WHERE `id` = 1";
    $result = mysqli_query($dbc, $sql);
    $settings = mysqli_fetch_assoc($result);
    echo json_encode(['settings' => $settings]);
?>