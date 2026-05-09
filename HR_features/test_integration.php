<?php
// Test script to validate assign API integration
header('Content-Type: text/html; charset=utf-8');
include __DIR__ . '/../Modules/dbcon.php';
$tests = [];

// Test 1: Check employee_location table exists
$res = mysqli_query($dbc, "SHOW TABLES LIKE 'employee_location'");
$tests['table_exists'] = mysqli_num_rows($res) > 0 ? 'PASS' : 'FAIL';

// Test 2: Check geofences exist
$res = mysqli_query($dbc, "SELECT COUNT(*) as cnt FROM geofences");
$row = mysqli_fetch_assoc($res);
$tests['geofences_exist'] = ($row['cnt'] > 0) ? 'PASS (count: ' . $row['cnt'] . ')' : 'FAIL';

// Test 3: Check employees exist
$res = mysqli_query($dbc, "SELECT COUNT(*) as cnt FROM employees");
$row = mysqli_fetch_assoc($res);
$tests['employees_exist'] = ($row['cnt'] > 0) ? 'PASS (count: ' . $row['cnt'] . ')' : 'FAIL';

// Test 4: Check existing assignments
$res = mysqli_query($dbc, "SELECT COUNT(*) as cnt FROM employee_location");
$row = mysqli_fetch_assoc($res);
$tests['existing_assignments'] = 'count: ' . $row['cnt'];
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Integration Test</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .pass { color: green; font-weight: bold; }
    .fail { color: red; font-weight: bold; }
  </style>
</head>
<body class="p-4">
  <div class="container">
    <h2>Assign Employee Feature - Integration Test</h2>
    <table class="table">
      <tr><th>Test</th><th>Result</th></tr>
      <?php foreach ($tests as $k => $v): ?>
        <tr>
          <td><?php echo htmlspecialchars($k); ?></td>
          <td class="<?php echo (strpos($v, 'PASS') === 0) ? 'pass' : 'fail'; ?>">
            <?php echo htmlspecialchars($v); ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
    <div class="mt-3">
      <a href="assign_employee.php" class="btn btn-primary">Go to Assign UI</a>
    </div>
  </div>
</body>
</html>
