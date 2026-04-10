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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

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
                    $sql = "SELECT `name`, `coordinates` FROM geofences WHERE id IN (SELECT loc_id FROM employee_location WHERE User_id = " . $_SESSION['emp_id'] . ")";
                    $result = mysqli_query($dbc, $sql);
                    echo "<option value=''>Select your location</option>";
                    while ($row = mysqli_fetch_array($result)) {
                        echo "<option value='" . $row['name'] . "' data-coordinates='" . $row['coordinates'] . "'>" . $row['name'] . "</option>";
                    }
                ?>
            </select>
        </div>

        <button id="tapButton" class="tap-button" onclick="Click()">
            Tap In
        </button>

        <div id="statusDisplay" class="status-display" style="display: block;">
        </div>

        <div id="map" style="height: 300px; width: 100%; margin-top: 20px; display: block;"></div>

        <div class="mt-4 text-muted">
            <small>Welcome, <?php echo $_SESSION['username'] ?? 'Employee'; ?>!</small>
        </div>
    </div>

    <script>
        // object ID's
        const tapButton = document.getElementById('tapButton');
        let locationSelect = document.getElementById('locationSelect');
        const statusDisplay = document.getElementById('statusDisplay');

        // time variables
        let dateString = new Date().toLocaleDateString();
        let timeString = '';
        let statusClockin = '';

        // user's work status
        let workStatus = "<?php echo $_SESSION['Work_status']; ?>";

        // map variables
        let map = L.map('map').setView([0, 0], 2);
        let userlat = '';
        let userlong = '';
        let userMarker = null;

        // map setup
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                userlat = position.coords.latitude;
                userlong = position.coords.longitude;
                map.setView([userlat, userlong], 15);
                userMarker = L.marker([userlat, userlong]).addTo(map).bindPopup('Your current location').openPopup();
                map.invalidateSize();
            }, function(error) {
                console.log('Geolocation error:', error.message);
            });
        };

        // select location variables
        let selectedLocation = '';
        let selectedCoordinates = null;

        // select location change event
        locationSelect.addEventListener('change', function() {
            selectedLocation = this.value;
            if (this.value !== '') {
                selectedCoordinates = this.options[this.selectedIndex].getAttribute('data-coordinates');
            } else {
                selectedCoordinates = null;
            }
        });

        // function for checking user location if within geofence on tap
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
        };

        // function to convert time to 24-hour format (can be used for comparing and operations)
        function to24HourTime(dateObj) {
            return dateObj.toLocaleTimeString('en-GB', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
        };

        // function to determine if tap in is on time or late
        function clockinStatus(timeStr) {
            const parts = String(timeStr).split(':');
            return parts[0] <= '08' ? 'On Time' : 'Late';
        };

        // main function for tap button click event
        function Click() {
            if (workStatus == 'Tapped-out') {
                if (selectedLocation === '') {
                    alert('Please select a location before tapping in.');
                    return;
                };

                if (isPointInPolygon([userlat, userlong], JSON.parse(selectedCoordinates))) {
                    let tapInTime = new Date();
                    timeString = to24HourTime(tapInTime);
                    dateString = tapInTime.toLocaleDateString();
                    statusClockin = clockinStatus(to24HourTime(tapInTime));
                    $.post('./Attendance_modules/save_clockin.php', {
                            Emp_id: <?php echo $_SESSION['emp_id']; ?>,
                            Date: dateString,
                            Location: selectedLocation,
                            Clock_in: timeString,
                            Status: statusClockin,
                            Work_status: 'Tapped-in'
                        }, function(response) {
                            alert(response);
                            document.location = './';
                    });
                } else {
                    alert('You are not within the geofenced area. Please move closer to the location and try again.');
                };
            } else {
                let tapInTime = new Date();
                timeString = to24HourTime(tapInTime);
                $.post('./Attendance_modules/save_clockout.php', {
                    Emp_id: <?php echo $_SESSION['emp_id']; ?>,
                    Clock_out: timeString,
                    Work_status: 'Tapped-out'
                }, function(response) {
                    alert(response);
                    document.location = './';
                });
            };

            
        };

        switch (workStatus) {
            case 'Tapped-in':
                tapButton.textContent = 'Tap Out';
                tapButton.classList.add('tapped-out');
                locationSelect.disable = true;
                statusDisplay.innerHTML = `
                            <div class="status-present">
                                <strong>Tapped In</strong><br>
                                <p class="d-none">
                                User ID: <?php echo $_SESSION['emp_id']; ?> 
                                </p>
                                Date: ${dateString}<br>
                                Time: <?php echo $_SESSION['Clock_in']; ?><br>
                            </div>
                        `;
                break;
            case 'Tapped-out':
                tapButton.textContent = 'Tap In';
                tapButton.classList.remove('tapped-out');
                break;
            default:
                tapButton.textContent = 'Tap In';
        };

    </script>