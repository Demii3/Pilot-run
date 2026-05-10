
const proceedButton = document.getElementById('proceedToAttendance');
const attendanceBtn = document.getElementById('attendanceBtn');
const logoutButton = document.getElementById('logout');
const empLocationLat = document.getElementById('empLocationLat');
const empLocationLng = document.getElementById('empLocationLng');
let map; // Declare map variable in a scope accessible to all functions
let markers; // Declare marker variable to manage the marker on the map
let geofenceLayer;

function loadAttendanceContent() {
    const content = document.getElementById('content-area');
    const payload = { TEST: 'This is a string', USER_ID: document.getElementById('userId').value };

    fetch('./Modules/test.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(response => {
        if (!response.ok) throw new Error('Failed to load content');
        return response.json();
    })
    .then(data => { 
        content.innerHTML = data.datafile; // Assuming the response contains a property 'datafile' with the HTML content
        console.log('Received data:', data);
        fillspans(); // Call the function to fill spans after loading the content
        return getUserLocation().then(() => data);
    })
    .then((data) => {
        applymap(data.querydata); // Call the function to initialize the map after loading the content
        setAttendanceModuleProperties(data.querydata); // Set properties for attendance module buttons
    })
    .catch(error => {
        console.error('Error:', error);
        content.innerHTML = '<p style="color: red;">Failed to load content. Please try again later.</p>';
    });
}

if (attendanceBtn) {
    attendanceBtn.addEventListener('click', loadAttendanceContent);
};

if (proceedButton) {
    proceedButton.addEventListener('click', loadAttendanceContent);
};


function getUserLocation() {
    return new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            console.log('Geolocation is not supported by this browser.');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                empLocationLat.value = position.coords.latitude;
                empLocationLng.value = position.coords.longitude;

                console.log('Latitude:', empLocationLat.value);
                console.log('Longitude:', empLocationLng.value);

                const empLocation = document.getElementById('empLocation');
                if (empLocation) {
                    empLocation.value = `${empLocationLat.value},${empLocationLng.value}`;
                }
                resolve();
            },
            (error) => {
                console.error('Location error:', error.message);
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    });
}

function applymap(querydata) {
    const MapContainer = document.getElementById('Map-container');
    if (!MapContainer || typeof L === 'undefined') return;

    if (map) {
        // Map already exists, just update its view
        map.setView([parseFloat(empLocationLat.value), parseFloat(empLocationLng.value)], 13);
        L.marker([parseFloat(empLocationLat.value), parseFloat(empLocationLng.value)]).addTo(markers);
        return;
    }

    map = L.map(MapContainer).setView([parseFloat(empLocationLat.value), parseFloat(empLocationLng.value)], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    markers = L.layerGroup().addTo(map);

    L.marker([parseFloat(empLocationLat.value), parseFloat(empLocationLng.value)]).addTo(markers);

    const workStatus = document.getElementById('workStatus');

    if (querydata == 0) {
        return;
    }

    if (querydata.Work_Status == 'Tapped-out') {
        drawGeofence(JSON.parse(querydata.Coordinates));
    }
};

function drawGeofence(coords) {
    console.log('Drawing geofence with coordinates:', coords);

    if (!map || !Array.isArray(coords) || coords.length < 3) return;

    if (geofenceLayer) {
        geofenceLayer.remove();
    }

    geofenceLayer = L.polygon(coords, {
        color: 'blue',
        fillColor: '#3388ff',
        fillOpacity: 0.2
    }).addTo(map);

    map.fitBounds(geofenceLayer.getBounds());
}

function setAttendanceModuleProperties(querydata) {
    console.log('Setting attendance module properties with querydata:', querydata);
    const returnToHomeButton = document.getElementById('returnToHome');
    const refreshLocationButton = document.getElementById('refreshLocation');
    const tapInButton = document.getElementById('tapIn');
    const locationSelect = document.getElementById('locationSelect');
    const attendanceIdInput = document.getElementById('attendanceId');
    attendanceIdInput.value = querydata.Attendance_id || '';

    function parseJsonList(rawValue) {
        if (!rawValue) {
            return [];
        }

        if (Array.isArray(rawValue)) {
            return rawValue;
        }

        try {
            const parsedValue = JSON.parse(rawValue);
            return Array.isArray(parsedValue) ? parsedValue : [];
        } catch (error) {
            return [];
        }
    }

    if (locationSelect) {
        const locationsData = parseJsonList(document.getElementById('locations')?.value);
        const coordinatesData = parseJsonList(document.getElementById('coordinates')?.value);

        locationsData.forEach((location, index) => {
            const option = document.createElement('option');
            option.value = location;
            option.textContent = location;

            if (coordinatesData[index]) {
                option.setAttribute('data-coordinates', JSON.stringify(coordinatesData[index]));
            }

            locationSelect.appendChild(option);
        });

        if (querydata.Work_Status == 'Tapped-in') {
            locationSelect.value = document.getElementById('locationSelect').options[1].value; // Set to the first location if already tapped in
            locationSelect.disabled = true;
        }
    }

    locationSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const coordinates = selectedOption.getAttribute('data-coordinates');
        if (coordinates) {
            drawGeofence(JSON.parse(JSON.parse(coordinates)));
        }
    });

    if(tapInButton) {
        tapInButton.addEventListener('click', TapIn);

            if (querydata.Work_Status == 'Tapped-in') {
                tapInButton.innerText = 'Tapped Out';
                tapInButton.classList.remove('btn-success');
                tapInButton.removeEventListener('click', TapIn);
                tapInButton.classList.add('btn-danger');
                tapInButton.addEventListener('click', TapOut);
            }
    }

    if(refreshLocationButton) {
        refreshLocationButton.addEventListener('click', function() {
            getUserLocation().then(() => {
                markers.clearLayers(); // Clear existing markers
                applymap(querydata = 0); // Re-apply the map to update the marker position
            });
        });
    }

    if (returnToHomeButton) {
        returnToHomeButton.addEventListener('click', function() {
            window.location = './index.php';
        });
    }
}

function TapIn() {
    const selectedOption = locationSelect.options[locationSelect.selectedIndex];
    const locationName = selectedOption.value;
    const coordinates = selectedOption.getAttribute('data-coordinates');
    if (locationName && coordinates) {
            saveTimein();
    };
}

function TapOut() {
    const selectedOption = locationSelect.options[locationSelect.selectedIndex];
    const locationName = selectedOption.value;
    const coordinates = selectedOption.getAttribute('data-coordinates');
    if (locationName && coordinates) {
            saveTimeOut();
    };
}
