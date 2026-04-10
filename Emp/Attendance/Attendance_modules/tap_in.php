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

    <script>
        const tapButton = document.getElementById('tapButton');
        const locationSelect = document.getElementById('locationSelect');
        const statusDisplay = document.getElementById('statusDisplay');

        let isTappedIn = <?php echo $_SESSION['Work_status'] === 'Tapped-in' ? 'true' : 'false'; ?>;
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

        // Point-in-polygon function using ray casting (Used to process geofence coordinates and check if user is inside)
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

        function to24HourTime(dateObj) {
            return dateObj.toLocaleTimeString('en-GB', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
        };

        function clockinStatus(timeStr) {
            const parts = String(timeStr).split(':');
            return parts[0] <= '08' ? 'On Time' : 'Late';
        };

        function drawSelectedGeofence(coordinatesJson) {
            if (geofenceLayer) {
                map.removeLayer(geofenceLayer);
                geofenceLayer = null;
            }

            if (!coordinatesJson) {
                return;
            }

            try {
                const points = JSON.parse(coordinatesJson);
                if (!Array.isArray(points) || points.length < 3) {
                    return;
                }

                geofenceLayer = L.polygon(points, {
                    color: '#2d6cdf',
                    weight: 2,
                    fillColor: '#2d6cdf',
                    fillOpacity: 0.18
                }).addTo(map);

                const bounds = geofenceLayer.getBounds();
                if (bounds.isValid()) {
                    map.fitBounds(bounds, { padding: [20, 20], maxZoom: 17 });
                }
            } catch (error) {
                console.error('Invalid geofence coordinates:', error);
            }
        }


        // Check if location is selected
        locationSelect.addEventListener('change', function() {
            selectedLocation = this.value;
            if (this.value !== '') {
                selectedCoordinates = this.options[this.selectedIndex].getAttribute('data-coordinates');
                drawSelectedGeofence(selectedCoordinates);
            } else {
                selectedCoordinates = null;
                drawSelectedGeofence(null);
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

                        now = tapInTime;
                        timeString = now.toLocaleTimeString();
                        dateString = now.toLocaleDateString();
                        statusClockin = clockinStatus(timeString);

                        statusDisplay.innerHTML = `
                            <div class="status-present">
                                <strong>Tapped In</strong><br>
                                <p class="d-none">
                                User ID: <?php echo $_SESSION['emp_id']; ?> 
                                </p>
                                Date: ${dateString}<br>
                                Location: ${selectedLocation}<br>
                                Time: ${timeString}<br>
                                Status: ${statusClockin}
                            </div>
                        `;
                        statusDisplay.style.display = 'block';

                        $.post('./Attendance_modules/save_attendance.php', {
                            Emp_id: <?php echo $_SESSION['emp_id']; ?>,
                            Date: dateString,
                            Location: selectedLocation,
                            Clock_in: timeString,
                            Status: statusClockin,
                            Work_status: 'Tapped-in'
                        }, function(response) {
                            alert(response);
                        }).fail(function(jqXHR, textStatus, errorThrown) {
                            console.error('save_attendance request failed', {
                                status: jqXHR.status,
                                responseText: jqXHR.responseText,
                                textStatus,
                                errorThrown
                            });
                            alert('Failed to save attendance. Check console for details.');
                        });
                        
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
                        Tap In: ${to24HourTime(tapInTime)}<br>
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