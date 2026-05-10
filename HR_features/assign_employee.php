<?php
// Simple HR UI to assign employees to a geofence site
// Does not modify existing app files; standalone page.
include __DIR__ . '/../Modules/dbcon.php';

$geofences = [];
$res = mysqli_query($dbc, "SELECT id, name, coordinates FROM geofences ORDER BY name");
if ($res) $geofences = mysqli_fetch_all($res, MYSQLI_ASSOC);

$employees = [];
$res2 = mysqli_query($dbc, "SELECT id, name, username FROM employees ORDER BY name");
if ($res2) $employees = mysqli_fetch_all($res2, MYSQLI_ASSOC);
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Assign Employee to Site</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4 bg-light">
    <div class="container">
        <h2 class="mb-4">Assign Employee to Site</h2>
        <div class="row gy-3">
            <div class="col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Assign Employee</h5>
                        <div class="mb-3">
                            <label class="form-label">Select Geofence Site</label>
                            <select id="siteSelect" class="form-select">
                                <option value="">-- choose site --</option>
                                <?php foreach($geofences as $g): ?>
                                    <option value="<?=htmlspecialchars($g['id'])?>"><?=htmlspecialchars($g['name'])?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Select Employee</label>
                            <select id="employeeSelect" class="form-select">
                                <option value="">-- choose employee --</option>
                                <?php foreach($employees as $e): ?>
                                    <option value="<?=htmlspecialchars($e['id'])?>"><?=htmlspecialchars($e['name'].($e['username'] ? ' ('.$e['username'].')' : ''))?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="d-flex gap-2">
                            <button id="assignBtn" class="btn btn-primary flex-grow-1">Assign</button>
                            <button id="cancelEditBtn" class="btn btn-secondary flex-grow-1 d-none">Cancel</button>
                        </div>
                        <div id="msg" class="mt-3"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Assigned Employees</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Employee</th>
                                        <th>Site</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="assignmentTableBody"></tbody>
                            </table>
                        </div>
                        <div id="tableEmpty" class="text-muted small mt-3">Loading assignments...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assign_employee.js"></script>
</body>
</html>
