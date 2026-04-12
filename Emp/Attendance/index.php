<?php
    session_start();
    if (!isset($_SESSION['login']) || $_SESSION['type'] != "Emp") {
        header("Location: ../");
        exit();

    }
    echo '<pre>';
    print_r($_SESSION);
    echo '</pre>';
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
                    $i = count($_SESSION['locations']); 
                    for ($i = 0; $i < count($_SESSION['locations']); $i++) {
                        echo '<option value="' . $_SESSION['locations'][$i] . '" data-coordinates=\'' . $_SESSION['coordinates'][$i] . '\'>' . $_SESSION['locations'][$i] . '</option>';
                    }
                ?>
            </select>
        </div>

        <div id="map" style="height: 300px; width: 100%; margin-top: 20px; display: block;"></div>

        <button id="tapButton" class="tap-button" disabled>
            Disabled
        </button>

        <div id="statusDisplay" class="status-display" style="display: block;">
        </div>

        <div class="mt-4 text-muted">
            <small>Welcome, <?php echo $_SESSION['username'] ?? 'Employee'; ?>!</small>
        </div>
    </div>

    <script>
        const sessionState = <?php echo json_encode([
            'attendanceActive' => !empty($_SESSION['attendance_active']),
            'selectedLocation' => $_SESSION['selectedLocation'] ?? '',
            'selectedCoordinates' => $_SESSION['selectedCoordinates'] ?? '',
            'clockInTime' => $_SESSION['Clock-in'] ?? '',
            'attendanceId' => isset($_SESSION['Attendance_id']) ? (int) $_SESSION['Attendance_id'] : 0,
            'clockOutStatus' => $_SESSION['Clockout-status'] ?? ''
        ]); ?>;

        // map initialization
        let map = L.map('map').setView([0, 0], 2);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const userlat = position.coords.latitude;
                const userlong = position.coords.longitude;
                map.setView([userlat, userlong], 15);
                const userMarker = L.marker([userlat, userlong]).addTo(map).bindPopup('Your current location').openPopup();
                map.invalidateSize();
            }, function(error) {
                console.log('Geolocation error:', error.message);
            });
        };

        const selectLocation = document.getElementById('locationSelect');
        const tapButton = document.getElementById('tapButton');

        function applyIdleState(selectedOption) {
            const option = selectedOption || selectLocation.options[selectLocation.selectedIndex];
            const locationName = option ? option.value : "0";
            const coordinates = option ? option.getAttribute('data-coordinates') || '' : '';

            if (locationName === "0") {
                tapButton.disabled = true;
                tapButton.textContent = 'Disabled';
                tapButton.classList.add('disabled-state');
                tapButton.classList.remove('tapped-out');
                tapButton.onclick = null;
                return;
            }

            tapButton.disabled = false;
            tapButton.textContent = 'Tap in';
            tapButton.classList.remove('disabled-state');
            tapButton.classList.remove('tapped-out');
            tapButton.onclick = () => ClockinClick(locationName, coordinates);
        }

        function applyActiveState(locationName, coordinates) {
            tapButton.disabled = false;
            tapButton.textContent = 'Tap out';
            tapButton.classList.remove('disabled-state');
            tapButton.classList.add('tapped-out');
            tapButton.onclick = () => ClockoutClick(locationName, coordinates);
            selectLocation.disabled = true;
        }

        function hydrateFromSession() {
            if (sessionState.selectedLocation) {
                selectLocation.value = sessionState.selectedLocation;
            }

            const selectedOption = selectLocation.options[selectLocation.selectedIndex];
            const selectedCoordinates = selectedOption ? (selectedOption.getAttribute('data-coordinates') || '') : '';
            const locationName = selectLocation.value;

            if (sessionState.attendanceActive && locationName !== "0") {
                applyActiveState(locationName, sessionState.selectedCoordinates || selectedCoordinates);
            } else {
                selectLocation.disabled = false;
                applyIdleState(selectedOption);
            }
        }

        selectLocation.addEventListener('change', function() {
            applyIdleState(this.options[this.selectedIndex]);
        });

        hydrateFromSession();

        function ClockinClick(locationName, coordinates) {
            const Time = new Date();
            const dateStr = formatDateToYYYYMMDD(Time.toDateString());
            const TimeString = Time.toLocaleTimeString();
            const ClockinStatus = checkClockinStatus(TimeString.split(' '));
            applyActiveState(locationName, coordinates);
            $.post('./Attendance_modules/save_clockin.php', {
                emp_id: <?php echo $_SESSION['id']; ?>,
                date: dateStr,
                location: locationName,
                coordinates: coordinates,
                clockin_time: TimeString,
                clockin_status: ClockinStatus,
            }, function(response) {
                alert(response);
            });
        }

        function ClockoutClick(locationName, coordinates) {
            const Time = new Date();
            const TimeString = Time.toLocaleTimeString();
            const ClockoutStatus = checkClockoutStatus(TimeString.split(' '));
            const Attendance_id = sessionState.attendanceId || <?php echo isset($_SESSION['Attendance_id']) ? $_SESSION['Attendance_id'] : 0; ?>;
            const clockInTime = sessionState.clockInTime || "<?php echo isset($_SESSION['Clock-in']) ? $_SESSION['Clock-in'] : '00:00:00 AM'; ?>";
            const duration = subtractTime(TimeString.split(' '), clockInTime.split(' '));
            selectLocation.disabled = false;
            applyIdleState(selectLocation.options[selectLocation.selectedIndex]);
            $.post('./Attendance_modules/save_clockout.php', {
                Attendance_id: Attendance_id,
                clockout_time: TimeString,
                clockout_status: ClockoutStatus,
                duration: duration,
            }, function(response) {
                alert(response);
            });
        }
    </script>
</body>
</html>