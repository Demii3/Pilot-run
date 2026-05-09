<?php
// Simple HR UI to assign employees to a geofence site
// Does not modify existing app files; standalone page.
include __DIR__ . '/../Modules/dbcon.php';

$geofences = [];
$res = mysqli_query($dbc, "SELECT id, name, coordinates FROM geofences ORDER BY name");
if ($res) $geofences = mysqli_fetch_all($res, MYSQLI_ASSOC);

$employees = [];
$res2 = mysqli_query($dbc, "SELECT id, name FROM employees ORDER BY name");
if ($res2) $employees = mysqli_fetch_all($res2, MYSQLI_ASSOC);
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Assign Employee to Site</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <div class="container">
        <h2>Assign Employee to Geofence Site</h2>
        <div class="row mt-3">
            <div class="col-md-5">
                <label class="form-label">Select Geofence Site</label>
                <select id="siteSelect" class="form-select">
                    <option value="">-- choose site --</option>
                    <?php foreach($geofences as $g): ?>
                        <option value="<?=htmlspecialchars($g['id'])?>"><?=htmlspecialchars($g['name'])?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label">Select Employee</label>
                <select id="employeeSelect" class="form-select">
                    <option value="">-- choose employee --</option>
                    <?php foreach($employees as $e): ?>
                        <option value="<?=htmlspecialchars($e['id'])?>"><?=htmlspecialchars($e['name'].' ('.$e['username'].')')?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button id="assignBtn" class="btn btn-primary w-100">Assign</button>
            </div>
        </div>

        <div id="msg" class="mt-3"></div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assign_employee.js"></script>
</body>
</html>
