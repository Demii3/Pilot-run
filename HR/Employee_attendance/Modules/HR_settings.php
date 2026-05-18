<?php
    include './dbcon.php';
    /** @var mysqli $dbc */
    if ($_POST['purpose'] == 'get_settings') {
        $settings = array();
        $sql = "SELECT Manual_mode, Hidden_columns FROM hr_attendance_settings WHERE id = 1";
        $result = mysqli_query($dbc, $sql);

        if (!$result) {
            echo json_encode(['error' => 'Database query failed: ' . mysqli_error($dbc)]);
            exit();
        }

        $row = mysqli_fetch_assoc($result);
        
        if (!$row) {
            echo json_encode(['error' => 'No settings found']);
            exit();
        }
        
        echo json_encode(['settings' => $row]);
        exit();
    }

    if ($_POST['purpose'] == 'save_settings') {
        $override = $_POST['override'];
        $hiddenColumns = $_POST['hiddenColumns'];
        $sql = "UPDATE hr_attendance_settings SET Manual_mode = $override, Hidden_columns = $hiddenColumns WHERE id = 1";
        mysqli_query($dbc, $sql);
        echo json_encode(['msg' => 'Settings saved successfully.']);
        exit();
    }
?>