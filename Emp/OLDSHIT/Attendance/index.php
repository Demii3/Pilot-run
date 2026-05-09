<?php
    session_start();
    if (!isset($_SESSION['login']) || $_SESSION['type'] != "Emp") {
        header("Location: ../");
        exit();

    }
    header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: post-check=0, pre-check=0', FALSE);
    header('Pragma: no-cache');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Tap</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="icon" type="image/png" href="../../Images/logo.jpg"/>
    <link rel="stylesheet" href="./Attendance_assets/Attendance.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="./Attendance_assets/Attendance.js"></script>
</head>
<body>
    <a href="../" class="back-link">← Back to Dashboard</a>

    <div class="attendance-card">
        <h2 class="mb-4">Attendance Tap</h2>

        <div class="location-select">
            <label for="locationSelect" class="form-label">Select Location</label>
            <select id="locationSelect" class="form-select">
                <?php
                    echo '<option value="0" data-coordinates="">-- Select Location --</option>';
                    $selectedLocation = $_SESSION['selectedLocation'] ?? '';
                    for ($i = 0; $i < count($_SESSION['locations']); $i++) {
                        $optionLocation = $_SESSION['locations'][$i];
                        $isSelected = $optionLocation === $selectedLocation ? ' selected' : '';
                        echo '<option value="' . $optionLocation . '" data-coordinates=\'' . $_SESSION['coordinates'][$i] . '\'' . $isSelected . '>' . $optionLocation . '</option>';
                    }
                ?>
            </select>
        </div>

        <div id="map" class="live-location-map"></div>
        <small id="locationStatus" class="location-status">Fetching your current location...</small>

        <button id="tapButton"
                class="tap-button <?php echo !empty($_SESSION['attendance_active']) ? 'tapped-out' : 'disabled-state'; ?>"
                data-user-id="<?php echo (int) $_SESSION['id']; ?>"
                data-clock-in-time="<?php echo htmlspecialchars($_SESSION['Clock-in'] ?? '', ENT_QUOTES); ?>"
                data-attendance-active="<?php echo !empty($_SESSION['attendance_active']) ? '1' : '0'; ?>"
                disabled type="button">
            <?php echo !empty($_SESSION['attendance_active']) ? 'Tap out' : 'Disabled'; ?>
        </button>

        <div id="statusDisplay" class="status-display" style="display: block;">
        </div>

        <div class="mt-4 text-muted">
            <small>Welcome, <?php echo $_SESSION['username'] ?? 'Employee'; ?>!</small>
        </div>
    </div>


</body>
</html>