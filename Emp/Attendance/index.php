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
    <link rel="stylesheet" href="../Assets/login.css">
    <link rel="stylesheet" href="./Attendance_assets/Attendance.css">

</head>
<body>
    <a href="../" class="back-link">← Back to Dashboard</a>

    <div class="attendance-card">
        <h2 class="mb-4">Attendance Tap</h2>

        <div class="location-select">
            <label for="locationSelect" class="form-label">Select Location</label>
            <select id="locationSelect" class="form-select">
                <?php 
                    include "./Attendance_modules/dbcon.php";
                    $sql = "SELECT `name`, `coordinates` FROM geofences WHERE id IN (SELECT loc_id FROM employee_location WHERE User_id = 3)";
                    $result = mysqli_query($dbc, $sql);
                    echo "<option value=''>Select your location</option>";
                    while ($row = mysqli_fetch_array($result)) {
                        echo "<option value='" . $row['name'] . "' data-coordinates='" . $row['coordinates'] . "'>" . $row['coordinates'] . "</option>";
                    }
                ?>
            </select>
        </div>

        <button id="tapButton" class="tap-button">
            Tap In
        </button>

        <div id="statusDisplay" class="status-display" style="display: none;">
            <!-- Status will be shown here -->
        </div>

        <div id="map" style="height: 300px; width: 100%; margin-top: 20px; display: block;"></div>

        <div class="mt-4 text-muted">
            <small>Welcome, <?php echo $_SESSION['username'] ?? 'Employee'; ?>!</small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const tapButton = document.getElementById('tapButton');
        const locationSelect = document.getElementById('locationSelect');
        const statusDisplay = document.getElementById('statusDisplay');

        let isTappedIn = false;
        let tapInTime = null;
        let selectedLocation = '';
        let selectedCoordinates = null;
        let map = L.map('map').setView([0, 0], 2);
        let userMarker = null;
        let geofenceLayer = null;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Get and display user's current location on map load
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                map.setView([lat, lng], 15);
                userMarker = L.marker([lat, lng]).addTo(map).bindPopup('Your current location').openPopup();
                map.invalidateSize();
            }, function(error) {
                console.log('Geolocation error:', error.message);
            });
        }

        // Point-in-polygon function using ray casting
        function isPointInPolygon(point, polygon) {
            let x = point[0], y = point[1];
            let inside = false;
            for (let i = 0, j = polygon.length - 1; i < polygon.length; j = i++) {
                let xi = polygon[i][0], yi = polygon[i][1];
                let xj = polygon[j][0], yj = polygon[j][1];
                if ((yi > y) !== (yj > y) && (x < (xj - xi) * (y - yi) / (yj - yi) + xi)) {
                    inside = !inside;
                }
            }
            return inside;
        }

        // Check if location is selected
        locationSelect.addEventListener('change', function() {
            selectedLocation = this.value;
            if (this.value !== '') {
                selectedCoordinates = this.options[this.selectedIndex].getAttribute('data-coordinates');
                alert(selectedLocation + " " + selectedCoordinates);
            } else {
                selectedCoordinates = null;
            }
        });

        // Handle tap button click
        tapButton.addEventListener('click', function() {
            if (selectedLocation == '') {
                alert('Please select a location first.');
                return;
            }

            if (!isTappedIn) {
                // Get user location and check if inside geofence
                if (!navigator.geolocation) {
                    alert('Geolocation is not supported by this browser.');
                    return;
                }

                navigator.geolocation.getCurrentPosition(function(position) {
                    const userLat = position.coords.latitude;
                    const userLng = position.coords.longitude;
                    const data = JSON.parse(selectedCoordinates);

                    if (isPointInPolygon([userLat, userLng], data)) {
                        // Proceed with tap in
                        tapInTime = new Date();
                        isTappedIn = true;
                        tapButton.textContent = 'Tap Out';
                        tapButton.classList.add('tapped-out');

                        const now = tapInTime;
                        const timeString = now.toLocaleTimeString();
                        const dateString = now.toLocaleDateString();

                        statusDisplay.innerHTML = `
                            <div class="status-present">
                                <strong>Tapped In</strong><br>
                                Location: ${selectedLocation}<br>
                                Time: ${timeString}<br>
                                Date: ${dateString}
                            </div>
                        `;
                        statusDisplay.style.display = 'block';

                        // Disable location change while tapped in
                        locationSelect.disabled = true;
                    } else {
                        alert('You are not within the selected location boundaries. Please move to the correct location.');
                    }
                }, function(error) {
                    alert('Unable to retrieve your location: ' + error.message);
                });
            } else {
                // Tap Out
                const now = new Date();
                const timeString = now.toLocaleTimeString();
                const duration = Math.round((now - tapInTime) / 1000 / 60); // minutes

                isTappedIn = false;
                tapButton.textContent = 'Tap In';
                tapButton.classList.remove('tapped-out');

                statusDisplay.innerHTML = `
                    <div class="status-absent">
                        <strong>Tapped Out</strong><br>
                        Location: ${selectedLocation}<br>
                        Tap In: ${tapInTime.toLocaleTimeString()}<br>
                        Tap Out: ${timeString}<br>
                        Duration: ${duration} minutes
                    </div>
                `;

                // Re-enable location selection
                locationSelect.disabled = false;
                selectedLocation = '';
                selectedCoordinates = null;
                locationSelect.value = '';
            }
        });
    </script>
</body>
</html>
