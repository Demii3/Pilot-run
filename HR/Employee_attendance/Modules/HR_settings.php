<?php
    include './dbcon.php';
    /** @var mysqli $dbc */

    $error = '';
    if ($_POST['purpose'] == 'get_settings') {
        $settings = array();
        $sql = "SELECT Manual_mode, Hidden_columns, Clock_in, Clock_out FROM hr_attendance_settings WHERE id = 1";
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

    if (isset($_POST['purpose']) && $_POST['purpose'] == 'save_settings') {
        $override = (int)($_POST['override'] ?? 0);
        $hiddenColumns = (string)($_POST['hiddenColumns'] ?? '');
        $setTimeIn = (string)($_POST['TimeIn'] ?? '');
        $setTimeOut = (string)($_POST['TimeOut'] ?? '');

        $sql = "UPDATE hr_attendance_settings SET Manual_mode = ?, Hidden_columns = ?, Clock_in = ?, Clock_out = ? WHERE id = 1";
        $stmt = mysqli_prepare($dbc, $sql);

        if (!$stmt) {
            echo json_encode(['error' => 'Database prepare failed: ' . mysqli_error($dbc)]);
            exit();
        }

        mysqli_stmt_bind_param($stmt, 'isss', $override, $hiddenColumns, $setTimeIn, $setTimeOut);

        if (!mysqli_stmt_execute($stmt)) {
            echo json_encode(['error' => 'Database update failed: ' . mysqli_stmt_error($stmt)]);
            exit();
        }

        echo json_encode(['msg' => 'Settings saved successfully.']);
        exit();
    }
?>