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

function subtractTime(timeStr1, timeStr2) {
    const [h1, m1, s1] = timeStr1[0].split(':').map(Number);
    const [h2, m2, s2] = timeStr2[0].split(':').map(Number);

    const minutes1 = timeStr1[1] == 'PM' ? (h1 * 60 + m1) + 720 : h1 * 60 + m1; // Adjust for PM if needed
    const minutes2 = timeStr2[1] == 'PM' ? (h2 * 60 + m2) + 720 : h2 * 60 + m2; // Adjust for PM if needed;

    return minutes1 - minutes2;
};

function formatDateToYYYYMMDD(dateStr) {
    const date = new Date(dateStr);

    if (isNaN(date.getTime())) {
        return '';
    }

    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;   
};

function checkClockinStatus(timeStr) {
    let [h1, m1, s1] = timeStr[0].split(':').map(Number);
    if(h1 === 12 && timeStr[1] === 'AM') { 
        h1 = 0; // Convert 12 AM to 0 hours for easier comparison{
    };
    const timeinMinutes = timeStr[1] == 'PM' ? (h1 * 60 + m1) + 720 : h1 * 60 + m1; // Adjust for PM if needed
    return timeinMinutes <= 480 ? 'On Time' : 'Late'; // 480 minutes = 8 hours
};

function checkClockoutStatus(timeStr) {
    let [h1, m1, s1] = timeStr[0].split(':').map(Number);
    if(h1 === 12 && timeStr[1] === 'AM') { 
        h1 = 0; // Convert 12 AM to 0 hours for easier comparison{
    };
    const timeinMinutes = timeStr[1] == 'PM' ? (h1 * 60 + m1) + 720 : h1 * 60 + m1; // Adjust for PM if needed
    return timeinMinutes <= 18 * 60 ? 'Under-time' : 'Over-time'; // 18 * 60 minutes = 18 hours
};

document.addEventListener('DOMContentLoaded', function () {
    const mapContainer = document.getElementById('map');
    const locationStatus = document.getElementById('locationStatus');
    const locationSelect = document.getElementById('locationSelect');
    const tapButton = document.getElementById('tapButton');
    const statusDisplay = document.getElementById('statusDisplay');

    if (!mapContainer || !locationSelect || !tapButton || typeof L === 'undefined') {
        return;
    }

    let attendanceActive = tapButton.dataset.attendanceActive === '1';

    const fallbackCenter = [14.5995, 120.9842];
    const map = L.map('map', {
        zoomControl: true,
        attributionControl: true
    }).setView(fallbackCenter, 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    let currentMarker = null;
    let accuracyCircle = null;
    let geofenceLayer = null;

    function setLocationStatus(message) {
        if (locationStatus) {
            locationStatus.textContent = message;
        }
    }

    function setTapButtonState(enabled, label) {
        tapButton.disabled = !enabled;
        tapButton.textContent = label;

        if (enabled) {
            tapButton.classList.remove('disabled-state');
            if (label === 'Tap out') {
                tapButton.classList.add('tapped-out');
            } else {
                tapButton.classList.remove('tapped-out');
            }
        } else {
            tapButton.classList.add('disabled-state');
            tapButton.classList.remove('tapped-out');
        }
    }

    function syncTapButtonState() {
        if (attendanceActive) {
            locationSelect.disabled = true;
            setTapButtonState(true, 'Tap out');
            return;
        }

        locationSelect.disabled = false;
        setTapButtonState(false, 'Disabled');
    }

    function setTappedInState() {
        attendanceActive = true;
        tapButton.dataset.attendanceActive = '1';
        syncTapButtonState();
    }

    function setTappedOutState() {
        attendanceActive = false;
        tapButton.dataset.attendanceActive = '0';
        syncTapButtonState();
    }

    function clearLiveLayers() {
        if (currentMarker) {
            map.removeLayer(currentMarker);
            currentMarker = null;
        }

        if (accuracyCircle) {
            map.removeLayer(accuracyCircle);
            accuracyCircle = null;
        }

        if (geofenceLayer) {
            map.removeLayer(geofenceLayer);
            geofenceLayer = null;
        }
    }

    function parseCoordinates(rawCoordinates) {
        if (!rawCoordinates) {
            return [];
        }

        if (Array.isArray(rawCoordinates)) {
            return rawCoordinates;
        }

        try {
            const parsed = JSON.parse(rawCoordinates);
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            return [];
        }
    }

    function getPolygonCentroid(points) {
        if (!Array.isArray(points) || points.length === 0) {
            return null;
        }

        let latitudeTotal = 0;
        let longitudeTotal = 0;
        let validPointCount = 0;

        points.forEach(function (point) {
            if (!Array.isArray(point) || point.length < 2) {
                return;
            }

            const latitude = Number(point[0]);
            const longitude = Number(point[1]);

            if (Number.isNaN(latitude) || Number.isNaN(longitude)) {
                return;
            }

            latitudeTotal += latitude;
            longitudeTotal += longitude;
            validPointCount += 1;
        });

        if (!validPointCount) {
            return null;
        }

        return [latitudeTotal / validPointCount, longitudeTotal / validPointCount];
    }

    function renderUserPosition(position, popupText) {
        const latitude = position.coords.latitude;
        const longitude = position.coords.longitude;
        const accuracy = position.coords.accuracy || 0;
        const currentLatLng = [latitude, longitude];

        currentMarker = L.marker(currentLatLng).addTo(map).bindPopup(popupText);
        accuracyCircle = L.circle(currentLatLng, {
            radius: accuracy,
            color: '#2563eb',
            fillColor: '#60a5fa',
            fillOpacity: 0.18,
            weight: 2
        }).addTo(map);

        return currentLatLng;
    }

    function requestCurrentPosition(onSuccess, onError, options) {
        navigator.geolocation.getCurrentPosition(onSuccess, onError, {
            enableHighAccuracy: true,
            maximumAge: 0,
            timeout: 10000,
            ...(options || {})
        });
    }

    function pointToSegmentDistanceMeters(point, start, end) {
        const latitudeFactor = 111132.92;
        const longitudeFactor = 111412.84 * Math.cos(point[0] * Math.PI / 180);

        const px = 0;
        const py = 0;
        const sx = (Number(start[1]) - point[1]) * longitudeFactor;
        const sy = (Number(start[0]) - point[0]) * latitudeFactor;
        const ex = (Number(end[1]) - point[1]) * longitudeFactor;
        const ey = (Number(end[0]) - point[0]) * latitudeFactor;

        const dx = ex - sx;
        const dy = ey - sy;

        if (dx === 0 && dy === 0) {
            return Math.sqrt((sx - px) * (sx - px) + (sy - py) * (sy - py));
        }

        const t = Math.max(0, Math.min(1, ((px - sx) * dx + (py - sy) * dy) / (dx * dx + dy * dy)));
        const closestX = sx + (t * dx);
        const closestY = sy + (t * dy);

        return Math.sqrt((closestX - px) * (closestX - px) + (closestY - py) * (closestY - py));
    }

    function isNearGeofence(point, polygon, thresholdMeters) {
        if (!Array.isArray(point) || !Array.isArray(polygon) || polygon.length < 3) {
            return false;
        }

        if (isPointInPolygon(point, polygon)) {
            return true;
        }

        let minDistance = Infinity;

        for (let i = 0; i < polygon.length; i++) {
            const nextIndex = (i + 1) % polygon.length;
            const distance = pointToSegmentDistanceMeters(point, polygon[i], polygon[nextIndex]);
            if (distance < minDistance) {
                minDistance = distance;
            }
        }

        return minDistance <= thresholdMeters;
    }

    function showCurrentLocation(statusMessage) {
        clearLiveLayers();
        syncTapButtonState();

        if (!navigator.geolocation) {
            setLocationStatus('Geolocation is not supported by this browser.');
            map.setView(fallbackCenter, 15);
            return;
        }

        setLocationStatus(statusMessage || 'Fetching your current location...');

        requestCurrentPosition(function (position) {
            const currentLatLng = renderUserPosition(position, 'You are here');
            map.setView(currentLatLng, 18, { animate: true });
            setLocationStatus('Showing your current location.');
        }, function (error) {
            let message = 'Unable to retrieve your current location.';

            if (error && error.code === 1) {
                message = 'Location access was denied in the browser.';
            } else if (error && error.code === 2) {
                message = 'Your location is currently unavailable.';
            } else if (error && error.code === 3) {
                message = 'Location request timed out.';
            }

            setLocationStatus(message);
            map.setView(fallbackCenter, 15);
        });
    }

    function showSelectedGeofence(option) {
        const selectedLocation = option ? option.value : '';
        const rawCoordinates = option ? option.getAttribute('data-coordinates') || '' : '';

        clearLiveLayers();

        if (!attendanceActive) {
            setTapButtonState(false, 'Disabled');
        } else {
            setTapButtonState(true, 'Tap out');
            locationSelect.disabled = true;
        }

        if (!rawCoordinates) {
            showCurrentLocation('Showing current location for the selected entry.');
            return;
        }

        const coordinates = parseCoordinates(rawCoordinates);

        if (!Array.isArray(coordinates) || coordinates.length < 3) {
            setLocationStatus('Selected location has no valid geofence coordinates.');
            map.setView(fallbackCenter, 15);
            return;
        }

        geofenceLayer = L.polygon(coordinates, {
            color: '#2563eb',
            weight: 2,
            fillColor: '#60a5fa',
            fillOpacity: 0.18
        }).addTo(map);

        const bounds = geofenceLayer.getBounds();
        if (bounds.isValid()) {
            map.fitBounds(bounds, { padding: [18, 18], maxZoom: 18 });
        } else {
            const centroid = getPolygonCentroid(coordinates);
            if (centroid) {
                map.setView(centroid, 17, { animate: true });
            }
        }

        if (!navigator.geolocation) {
            setLocationStatus('Showing geofence for ' + selectedLocation + '. Geolocation is not supported by this browser.');
            return;
        }

        setLocationStatus('Checking whether you are near ' + selectedLocation + '...');

        requestCurrentPosition(function (position) {
            const currentLatLng = [position.coords.latitude, position.coords.longitude];
            const isNear = isNearGeofence(currentLatLng, coordinates, 100);

            if (attendanceActive) {
                renderUserPosition(position, 'You are currently tapped in');

                if (bounds.isValid()) {
                    const combinedBounds = L.latLngBounds(bounds);
                    combinedBounds.extend(currentLatLng);
                    map.fitBounds(combinedBounds, { padding: [18, 18], maxZoom: 18 });
                } else {
                    map.setView(currentLatLng, 18, { animate: true });
                }

                setTapButtonState(true, 'Tap out');
                setLocationStatus('You are tapped in at ' + selectedLocation + '.');
                return;
            }

            if (!isNear) {
                setLocationStatus('Showing geofence for ' + selectedLocation + '. You are not near it right now.');
                setTapButtonState(false, 'Disabled');
                return;
            }

            renderUserPosition(position, 'You are near ' + selectedLocation);

            if (bounds.isValid()) {
                const combinedBounds = L.latLngBounds(bounds);
                combinedBounds.extend(currentLatLng);
                map.fitBounds(combinedBounds, { padding: [18, 18], maxZoom: 18 });
            } else {
                map.setView(currentLatLng, 18, { animate: true });
            }

            setTapButtonState(true, 'Tap in');
            setLocationStatus('You are near ' + selectedLocation + '.');
        }, function (error) {
            let message = 'Showing geofence for ' + selectedLocation + '.';
            if (!attendanceActive) {
                setTapButtonState(false, 'Disabled');
            } else {
                setTapButtonState(true, 'Tap out');
            }

            if (error && error.code === 1) {
                message = message + ' Location access was denied in the browser.';
            } else if (error && error.code === 2) {
                message = message + ' Your current location is unavailable.';
            } else if (error && error.code === 3) {
                message = message + ' Location request timed out.';
            }

            setLocationStatus(message);
        });
    }

    function saveClockIn() {
        if (tapButton.disabled) {
            return;
        }

        const selectedOption = locationSelect.options[locationSelect.selectedIndex];
        if (!selectedOption || selectedOption.value === '0') {
            return;
        }

        const selectedLocation = selectedOption.value;
        const selectedCoordinates = selectedOption.getAttribute('data-coordinates') || '';
        const currentTime = new Date();
        const dateString = formatDateToYYYYMMDD(currentTime.toDateString());
        const clockinTime = currentTime.toLocaleTimeString();
        const clockinStatus = checkClockinStatus(clockinTime.split(' '));
        const userId = tapButton.dataset.userId || '';

        setTapButtonState(false, 'Saving...');

        $.post('./Attendance_modules/save_clockin.php', {
            emp_id: userId,
            date: dateString,
            location: selectedLocation,
            coordinates: selectedCoordinates,
            clockin_time: clockinTime,
            clockin_status: clockinStatus
        }, function (response) {
            const trimmedResponse = String(response || '').trim();

            if (trimmedResponse === 'success') {
                tapButton.dataset.clockInTime = clockinTime;
                setTappedInState();
                if (statusDisplay) {
                    statusDisplay.innerHTML = '<div class="status-present"><strong>Clock-in saved successfully.</strong></div>';
                }
                setLocationStatus('Clock-in saved for ' + selectedLocation + '.');
                showSelectedGeofence(selectedOption);
                return;
            }

            setTapButtonState(true, 'Tap in');
            if (statusDisplay) {
                statusDisplay.innerHTML = '<div class="status-absent"><strong>Unable to save clock-in.</strong></div>';
            }
            setLocationStatus('Unable to save clock-in for ' + selectedLocation + '.');
            alert(trimmedResponse || 'Unable to save clock-in.');
        }).fail(function () {
            setTapButtonState(true, 'Tap in');
            setLocationStatus('Unable to save clock-in for ' + selectedLocation + '.');
            alert('Unable to save clock-in.');
        });
    }

    function saveClockOut() {
        if (tapButton.disabled) {
            return;
        }

        const selectedOption = locationSelect.options[locationSelect.selectedIndex];
        if (!selectedOption || selectedOption.value === '0') {
            return;
        }

        const selectedLocation = selectedOption.value;
        const currentTime = new Date();
        const clockoutTime = currentTime.toLocaleTimeString();
        const clockoutStatus = checkClockoutStatus(clockoutTime.split(' '));
        const clockInTime = tapButton.dataset.clockInTime || '';
        const duration = clockInTime ? subtractTime(clockoutTime.split(' '), clockInTime.split(' ')) : 0;

        setTapButtonState(false, 'Saving...');

        $.post('./Attendance_modules/save_clockout.php', {
            clockout_time: clockoutTime,
            clockout_status: clockoutStatus,
            duration: duration
        }, function (response) {
            const trimmedResponse = String(response || '').trim();

            if (trimmedResponse === 'success') {
                setTappedOutState();
                if (statusDisplay) {
                    statusDisplay.innerHTML = '<div class="status-present"><strong>Clock-out saved successfully.</strong></div>';
                }
                setLocationStatus('Clock-out saved for ' + selectedLocation + '.');
                showSelectedGeofence(selectedOption);
                return;
            }

            setTappedInState();
            if (statusDisplay) {
                statusDisplay.innerHTML = '<div class="status-absent"><strong>Unable to save clock-out.</strong></div>';
            }
            setLocationStatus('Unable to save clock-out for ' + selectedLocation + '.');
            alert(trimmedResponse || 'Unable to save clock-out.');
        }).fail(function () {
            setTappedInState();
            setLocationStatus('Unable to save clock-out for ' + selectedLocation + '.');
            alert('Unable to save clock-out.');
        });
    }

    function handleTapButtonClick() {
        if (attendanceActive) {
            saveClockOut();
            return;
        }

        saveClockIn();
    }

    function handleLocationSelection() {
        const selectedOption = locationSelect.options[locationSelect.selectedIndex];

        if (!selectedOption || selectedOption.value === '0') {
            showCurrentLocation('Select a location or use your current position.');
            return;
        }

        showSelectedGeofence(selectedOption);
    }

    locationSelect.addEventListener('change', handleLocationSelection);
    tapButton.addEventListener('click', handleTapButtonClick);

    if (attendanceActive) {
        setTappedInState();
    }

    handleLocationSelection();

    window.addEventListener('resize', function () {
        map.invalidateSize();
    });
});


