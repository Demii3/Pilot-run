
const proceedButton = document.getElementById('proceedToAttendance');
const attendanceBtn = document.getElementById('attendanceBtn');
const logoutButton = document.getElementById('logout');
const empLocationLat = document.getElementById('empLocationLat');
const empLocationLng = document.getElementById('empLocationLng');
let map; // Declare map variable in a scope accessible to all functions
let markers; // Declare marker variable to manage the marker on the map

function loadAttendanceContent() {
        const content = document.getElementById('content-area');
        const payload = { TEST: 'This is a string', USER_ID: '' };

        fetch('./Modules/test.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(response => {
            if (!response.ok) throw new Error('Failed to load content');
            return response.text();
        })
        .then(html => { 
            content.innerHTML = html; 
            fillspans(); // Call the function to fill spans after loading the content
            return getUserLocation(); // Get user location after loading the content
        })
        .then(() => {
            applymap(); // Call the function to initialize the map after loading the content
            setAttendanceModuleProperties(); // Set properties for attendance module buttons
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = '<p style="color: red;">Failed to load content. Please try again later.</p>';
        });
    }


if (proceedButton) {
    proceedButton.addEventListener('click', loadAttendanceContent);
};

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

function applymap() {
    console.log('Initializing map with coordinates:', empLocationLat.value, empLocationLng.value);
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

    if (workStatus == 'Tapped-out') {
        return;
    }
};

function setAttendanceModuleProperties() {
    const returnToHomeButton = document.getElementById('returnToHome');
    const refreshLocationButton = document.getElementById('refreshLocation');

    if(refreshLocationButton) {
        refreshLocationButton.addEventListener('click', function() {
            getUserLocation().then(() => {
                markers.clearLayers(); // Clear existing markers
                applymap(); // Re-apply the map to update the marker position
            });
        });
    }

    if (returnToHomeButton) {
        returnToHomeButton.addEventListener('click', function() {
            window.location = './index.php';
        });
    }
}

