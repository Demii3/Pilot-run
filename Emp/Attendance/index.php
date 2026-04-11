<?php
    session_start();
    if (!isset($_SESSION['login']) || $_SESSION['type'] != "Emp") {
        header("Location: ../");
        exit();

    }

    echo ($_SESSION['locations'][0]);

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
        selectLocation.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const coordinates = selectedOption.getAttribute('data-coordinates');
            if (selectedOption.value === "0"){
                document.getElementById('tapButton').disabled = true;
                document.getElementById('tapButton').textContent = 'Disabled';

            } else {
                document.getElementById('tapButton').disabled = false;
                document.getElementById('tapButton').textContent = 'Tap in';
                document.getElementById('tapButton').onclick = () => ClockinClick(selectedOption.value, coordinates);
            }
        });

        function ClockinClick(locationName, coordinates) {
            const Time = new Date();
            const dateStr = formatDateToYYYYMMDD(Time.toDateString());
            const TimeString = Time.toLocaleTimeString();
            const ClockinStatus = checkClockinStatus(TimeString.split(' '));
            document.getElementById('tapButton').textContent = 'Tap out';
            document.getElementById('tapButton').classList.add('tapped-out');
            document.getElementById('tapButton').onclick = () => ClockoutClick(locationName, coordinates);
            document.getElementById('locationSelect').disabled = true;
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
            document.getElementById('tapButton').textContent = 'Tap in';
            document.getElementById('tapButton').classList.remove('tapped-out');
            document.getElementById('tapButton').onclick = () => ClockinClick(locationName, coordinates);
            document.getElementById('locationSelect').disabled = false;
            $.post('./Attendance_modules/save_clockout.php', {
                Attendance_id: <?php echo $_SESSION['Attendance_id']; ?>,
                clockout_time: TimeString,
                clockout_status: ClockoutStatus,
                duration: subtractTime(TimeString.split(' '), "<?php echo $_SESSION['Clock-in']; ?>".split(' ')),
            }, function(response) {
                alert(response);
            });
        }
    </script>
</body>
</html>