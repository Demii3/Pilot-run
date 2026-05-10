<?php
// Directly execute assign endpoint with mocked HR session to verify JSON response.

$_POST['geofence_id'] = '4';
$_POST['employee_id'] = '6';

session_id('assignapitest');
session_start();
$_SESSION['login'] = true;
$_SESSION['type'] = 'HR';

ob_start();
include __DIR__ . '/../HR/Employee_management/api_assign_employee.php';
$output = ob_get_clean();

$decoded = json_decode($output, true);
echo "Raw output:\n" . $output . "\n\n";
if (json_last_error() === JSON_ERROR_NONE) {
    echo "Valid JSON: true\n";
    echo "Parsed success: " . (isset($decoded['success']) ? ($decoded['success'] ? 'true' : 'false') : 'n/a') . "\n";
    echo "Parsed message: " . ($decoded['message'] ?? 'n/a') . "\n";
} else {
    echo "Valid JSON: false\n";
    echo "JSON error: " . json_last_error_msg() . "\n";
}
