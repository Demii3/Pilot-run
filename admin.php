<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "employee_db";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to calculate distance between two points using Haversine formula
function haversineDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371000; // meters
    $latDelta = deg2rad($lat2 - $lat1);
    $lonDelta = deg2rad($lon2 - $lon1);
    $a = sin($latDelta / 2) * sin($latDelta / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lonDelta / 2) * sin($lonDelta / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earthRadius * $c;
}

// Initialize variables
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$alert_message = '';
$alert_type = '';

// CREATE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_employee'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $position = $conn->real_escape_string($_POST['position']);
    $salary = $conn->real_escape_string($_POST['salary']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $latitude = isset($_POST['latitude']) && !empty($_POST['latitude']) ? (float)$_POST['latitude'] : NULL;
    $longitude = isset($_POST['longitude']) && !empty($_POST['longitude']) ? (float)$_POST['longitude'] : NULL;

    $sql = "INSERT INTO employees (name, email, position, salary, phone, latitude, longitude, last_location_update) VALUES ('$name', '$email', '$position', '$salary', '$phone', " . ($latitude !== NULL ? "'$latitude'" : "NULL") . ", " . ($longitude !== NULL ? "'$longitude'" : "NULL") . ", " . ($latitude !== NULL ? "NOW()" : "NULL") . ")";

    if ($conn->query($sql) === TRUE) {
        $alert_message = "Employee added successfully!";
        $alert_type = "success";
    } else {
        $alert_message = "Error: " . $conn->error;
        $alert_type = "danger";
    }
}

// UPDATE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_employee'])) {
    $id = (int)$_POST['id'];
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $position = $conn->real_escape_string($_POST['position']);
    $salary = $conn->real_escape_string($_POST['salary']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $latitude = isset($_POST['latitude']) && !empty($_POST['latitude']) ? (float)$_POST['latitude'] : NULL;
    $longitude = isset($_POST['longitude']) && !empty($_POST['longitude']) ? (float)$_POST['longitude'] : NULL;

    $sql = "UPDATE employees SET name='$name', email='$email', position='$position', salary='$salary', phone='$phone', latitude=" . ($latitude !== NULL ? "'$latitude'" : "NULL") . ", longitude=" . ($longitude !== NULL ? "'$longitude'" : "NULL") . ", last_location_update=" . ($latitude !== NULL ? "NOW()" : "NULL") . " WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        $alert_message = "Employee updated successfully!";
        $alert_type = "success";
        $action = '';
    } else {
        $alert_message = "Error: " . $conn->error;
        $alert_type = "danger";
    }
}

// DELETE
if ($action == 'delete' && $id > 0) {
    $sql = "DELETE FROM employees WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        $alert_message = "Employee deleted successfully!";
        $alert_type = "success";
        $action = '';
    } else {
        $alert_message = "Error: " . $conn->error;
        $alert_type = "danger";
    }
}

// READ - Get employee data for editing
$employee = null;
if ($action == 'edit' && $id > 0) {
    $sql = "SELECT * FROM employees WHERE id=$id";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $employee = $result->fetch_assoc();
    }
}

// READ - Get all employees
$sql = "SELECT * FROM employees ORDER BY id DESC";
$result = $conn->query($sql);
$employees = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .container-main {
            max-width: 1200px;
            margin: 0 auto;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 20px;
        }
        .btn-custom {
            border-radius: 5px;
            padding: 8px 16px;
            font-weight: 500;
        }
        .table-responsive {
            border-radius: 5px;
        }
        table {
            margin-bottom: 0;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .form-control, .form-select {
            border-radius: 5px;
            border: 1px solid #ddd;
            padding: 10px 15px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .alert {
            border-radius: 5px;
            border: none;
        }
        h1 {
            color: white;
            margin-bottom: 30px;
            font-weight: 700;
        }
        .form-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .form-title {
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .table-header-bg {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container-main">
        <h1 class="text-center">👨‍💼 Employee Management System</h1>

        <?php if (!empty($alert_message)): ?>
            <div class="alert alert-<?php echo $alert_type; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($alert_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Form Section -->
        <div class="form-section">
            <h3 class="form-title"><?php echo ($action == 'edit' && $employee) ? '✏️ Edit Employee' : '➕ Add New Employee'; ?></h3>
            <form method="POST">
                <?php if ($action == 'edit' && $employee): ?>
                    <input type="hidden" name="id" value="<?php echo $employee['id']; ?>">
                    <input type="hidden" name="update_employee" value="1">
                <?php else: ?>
                    <input type="hidden" name="add_employee" value="1">
                <?php endif; ?>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="name" placeholder="Enter full name" 
                               value="<?php echo $employee ? htmlspecialchars($employee['name']) : ''; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" placeholder="Enter email" 
                               value="<?php echo $employee ? htmlspecialchars($employee['email']) : ''; ?>" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Position</label>
                        <input type="text" class="form-control" name="position" placeholder="Enter position" 
                               value="<?php echo $employee ? htmlspecialchars($employee['position']) : ''; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Salary</label>
                        <input type="number" class="form-control" name="salary" placeholder="Enter salary" step="0.01"
                               value="<?php echo $employee ? htmlspecialchars($employee['salary']) : ''; ?>" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="tel" class="form-control" name="phone" placeholder="Enter phone number" 
                               value="<?php echo $employee ? htmlspecialchars($employee['phone']) : ''; ?>" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-5">
                        <label class="form-label">Latitude</label>
                        <input type="number" class="form-control" name="latitude" id="latitude" placeholder="Enter latitude" step="any"
                               value="<?php echo $employee && $employee['latitude'] ? htmlspecialchars($employee['latitude']) : ''; ?>">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Longitude</label>
                        <input type="number" class="form-control" name="longitude" id="longitude" placeholder="Enter longitude" step="any"
                               value="<?php echo $employee && $employee['longitude'] ? htmlspecialchars($employee['longitude']) : ''; ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-info btn-custom" onclick="getLocation()">📍 Get Location</button>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-custom">
                        <?php echo ($action == 'edit' && $employee) ? 'Update Employee' : 'Add Employee'; ?>
                    </button>
                    <?php if ($action == 'edit'): ?>
                        <a href="admin.php" class="btn btn-secondary btn-custom">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Table Section -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">📋 Employee List</h5>
            </div>
            <div class="card-body">
                <?php if (empty($employees)): ?>
                    <div class="alert alert-info mb-0">No employees found. Add your first employee above.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-header-bg">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Position</th>
                                    <th>Salary</th>
                                    <th>Phone</th>
                                    <th>Latitude</th>
                                    <th>Longitude</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employees as $emp): ?>
                                    <tr>
                                        <td><?php echo $emp['id']; ?></td>
                                        <td><?php echo htmlspecialchars($emp['name']); ?></td>
                                        <td><?php echo htmlspecialchars($emp['email']); ?></td>
                                        <td><?php echo htmlspecialchars($emp['position']); ?></td>
                                        <td>$<?php echo number_format($emp['salary'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($emp['phone']); ?></td>
                                        <td><?php echo $emp['latitude'] ? htmlspecialchars($emp['latitude']) : 'N/A'; ?></td>
                                        <td><?php echo $emp['longitude'] ? htmlspecialchars($emp['longitude']) : 'N/A'; ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="admin.php?action=edit&id=<?php echo $emp['id']; ?>" 
                                                   class="btn btn-warning btn-sm btn-custom">Edit</a>
                                                <a href="admin.php?action=delete&id=<?php echo $emp['id']; ?>" 
                                                   class="btn btn-danger btn-sm btn-custom"
                                                   onclick="return confirm('Are you sure you want to delete this employee?');">Delete</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 text-muted">
                        <small>Total Employees: <strong><?php echo count($employees); ?></strong></small>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Geofence Section -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">🚧 Geofence Settings</h5>
            </div>
            <div class="card-body">
                <p>Define a geofence area. Employees outside this area will be flagged.</p>
                <form method="POST" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Geofence Name</label>
                        <input type="text" class="form-control" name="geofence_name" placeholder="e.g., Office Area" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Center Latitude</label>
                        <input type="number" class="form-control" name="geofence_lat" step="any" placeholder="Latitude" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Center Longitude</label>
                        <input type="number" class="form-control" name="geofence_lng" step="any" placeholder="Longitude" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Radius (meters)</label>
                        <input type="number" class="form-control" name="geofence_radius" placeholder="Radius in meters" required>
                    </div>
                    <div class="col-12">
                        <button type="submit" name="set_geofence" class="btn btn-success btn-custom">Set Geofence</button>
                    </div>
                </form>
                <?php
                // Handle geofence setting
                if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['set_geofence'])) {
                    $name = $conn->real_escape_string($_POST['geofence_name']);
                    $lat = (float)$_POST['geofence_lat'];
                    $lng = (float)$_POST['geofence_lng'];
                    $radius = (int)$_POST['geofence_radius'];

                    $sql = "INSERT INTO geofences (name, latitude, longitude, radius_meters) VALUES ('$name', '$lat', '$lng', '$radius') ON DUPLICATE KEY UPDATE latitude='$lat', longitude='$lng', radius_meters='$radius'";
                    if ($conn->query($sql) === TRUE) {
                        echo '<div class="alert alert-success mt-3">Geofence set successfully!</div>';
                    } else {
                        echo '<div class="alert alert-danger mt-3">Error setting geofence: ' . $conn->error . '</div>';
                    }
                }

                // Get current geofence
                $geofence = null;
                $sql = "SELECT * FROM geofences ORDER BY id DESC LIMIT 1";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    $geofence = $result->fetch_assoc();
                }
                ?>
                <?php if ($geofence): ?>
                    <div class="mt-3">
                        <h6>Current Geofence: <?php echo htmlspecialchars($geofence['name']); ?></h6>
                        <p>Center: <?php echo $geofence['latitude']; ?>, <?php echo $geofence['longitude']; ?> | Radius: <?php echo $geofence['radius_meters']; ?> meters</p>
                        <h6>Employees Status:</h6>
                        <ul>
                            <?php foreach ($employees as $emp): ?>
                                <?php if ($emp['latitude'] && $emp['longitude']): ?>
                                    <?php
                                    $distance = haversineDistance($geofence['latitude'], $geofence['longitude'], $emp['latitude'], $emp['longitude']);
                                    $inFence = $distance <= $geofence['radius_meters'];
                                    ?>
                                    <li><?php echo htmlspecialchars($emp['name']); ?>: <?php echo $inFence ? '✅ Inside' : '❌ Outside'; ?> (<?php echo round($distance, 2); ?> meters away)</li>
                                <?php else: ?>
                                    <li><?php echo htmlspecialchars($emp['name']); ?>: 📍 No location data</li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Map Section -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">🗺️ Employee Locations Map</h5>
            </div>
            <div class="card-body">
                <div id="map" style="height: 400px;"></div>
            </div>
        </div>
    </div>

    <script>
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition, showError);
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        }

        function showPosition(position) {
            document.getElementById('latitude').value = position.coords.latitude;
            document.getElementById('longitude').value = position.coords.longitude;
        }

        function showError(error) {
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    alert("User denied the request for Geolocation.");
                    break;
                case error.POSITION_UNAVAILABLE:
                    alert("Location information is unavailable.");
                    break;
                case error.TIMEOUT:
                    alert("The request to get user location timed out.");
                    break;
                case error.UNKNOWN_ERROR:
                    alert("An unknown error occurred.");
                    break;
            }
        }

        // Initialize map
        var map = L.map('map').setView([0, 0], 2);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Add markers for employees with locations
        <?php foreach ($employees as $emp): ?>
            <?php if ($emp['latitude'] && $emp['longitude']): ?>
                L.marker([<?php echo $emp['latitude']; ?>, <?php echo $emp['longitude']; ?>])
                    .addTo(map)
                    .bindPopup('<b><?php echo addslashes($emp['name']); ?></b><br><?php echo addslashes($emp['position']); ?><br>Last update: <?php echo $emp['last_location_update'] ? htmlspecialchars($emp['last_location_update']) : 'N/A'; ?>');
            <?php endif; ?>
        <?php endforeach; ?>

        // Add geofence circle
        <?php if ($geofence): ?>
            L.circle([<?php echo $geofence['latitude']; ?>, <?php echo $geofence['longitude']; ?>], {
                color: 'red',
                fillColor: '#f03',
                fillOpacity: 0.1,
                radius: <?php echo $geofence['radius_meters']; ?>
            }).addTo(map).bindPopup('Geofence: <?php echo addslashes($geofence['name']); ?>');
        <?php endif; ?>

        // Fit map to markers if any
        var group = new L.featureGroup([]);
        <?php foreach ($employees as $emp): ?>
            <?php if ($emp['latitude'] && $emp['longitude']): ?>
                group.addLayer(L.marker([<?php echo $emp['latitude']; ?>, <?php echo $emp['longitude']; ?>]));
            <?php endif; ?>
        <?php endforeach; ?>
        <?php if ($geofence): ?>
            group.addLayer(L.circle([<?php echo $geofence['latitude']; ?>, <?php echo $geofence['longitude']; ?>], {radius: <?php echo $geofence['radius_meters']; ?>}));
        <?php endif; ?>
        if (group.getLayers().length > 0) {
            map.fitBounds(group.getBounds());
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</body>
</html>

<?php $conn->close(); ?>
