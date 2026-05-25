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

$siteOptions = [];
foreach ($geofences as $geofence) {
    $siteOptions[] = [
        'id' => (string)$geofence['id'],
        'label' => $geofence['name'],
    ];
}

$employeeOptions = [];
foreach ($employees as $employee) {
    $employeeOptions[] = [
        'id' => (string)$employee['id'],
        'label' => $employee['name'] . (!empty($employee['username']) ? ' (' . $employee['username'] . ')' : ''),
    ];
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Assign Employee to Site</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .combo-wrap {
            position: relative;
        }
        .search-dropdown {
            max-height: 220px;
            overflow-y: auto;
            z-index: 1050;
        }
        .search-dropdown .list-group-item {
            cursor: pointer;
        }
        .search-dropdown .list-group-item:hover,
        .search-dropdown .list-group-item.active {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: #fff;
        }
    </style>
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
                            <label for="siteSearch" class="form-label">Select Geofence Site</label>
                            <div class="combo-wrap">
                                <input id="siteSearch" type="text" class="form-control" placeholder="Type to search sites" autocomplete="off">
                                <input id="siteId" type="hidden">
                                <div id="siteSuggestions" class="list-group position-absolute top-100 start-0 end-0 mt-1 bg-white border rounded shadow-sm search-dropdown d-none"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="employeeSearch" class="form-label">Select Employee</label>
                            <div class="combo-wrap">
                                <input id="employeeSearch" type="text" class="form-control" placeholder="Type to search employees" autocomplete="off">
                                <input id="employeeId" type="hidden">
                                <div id="employeeSuggestions" class="list-group position-absolute top-100 start-0 end-0 mt-1 bg-white border rounded shadow-sm search-dropdown d-none"></div>
                            </div>
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
    <script>
        window.assignmentSites = <?= json_encode($siteOptions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        window.assignmentEmployees = <?= json_encode($employeeOptions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    </script>
    <script src="assign_employee.js"></script>
</body>
</html>
