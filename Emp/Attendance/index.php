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
                    include '../../dbcon.php';
                    $sql = "SELECT `name` FROM geofences WHERE id IN (SELECT loc_id FROM employee_location WHERE User_id = " . $_SESSION['emp_id'] . ")";
                    $result = $conn->query($sql);
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['location'] . "'>" . $row['location'] . "</option>";
                    }
                ?>
            </select>
        </div>

        <button id="tapButton" class="tap-button" disabled>
            Tap In
        </button>

        <div id="statusDisplay" class="status-display" style="display: none;">
            <!-- Status will be shown here -->
        </div>

        <div class="mt-4 text-muted">
            <small>Welcome, <?php echo $_SESSION['username'] ?? 'Employee'; ?>!</small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const tapButton = document.getElementById('tapButton');
        const locationSelect = document.getElementById('locationSelect');
        const statusDisplay = document.getElementById('statusDisplay');

        let isTappedIn = false;
        let tapInTime = null;
        let selectedLocation = '';

        // Check if location is selected
        locationSelect.addEventListener('change', function() {
            selectedLocation = this.value;
            tapButton.disabled = !selectedLocation;
        });

        // Handle tap button click
        tapButton.addEventListener('click', function() {
            if (!selectedLocation) {
                alert('Please select a location first.');
                return;
            }

            const now = new Date();
            const timeString = now.toLocaleTimeString();
            const dateString = now.toLocaleDateString();

            if (!isTappedIn) {
                // Tap In
                tapInTime = now;
                isTappedIn = true;
                tapButton.textContent = 'Tap Out';
                tapButton.classList.add('tapped-out');

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
                // Tap Out
                const tapOutTime = now;
                const duration = Math.round((tapOutTime - tapInTime) / 1000 / 60); // minutes

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
                locationSelect.value = '';
                tapButton.disabled = true;
            }
        });
    </script>
</body>
</html>
