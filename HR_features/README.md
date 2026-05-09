HR Features - Assign Employee to Geofence Site

Files added (non-destructive):

- `assign_employee.php` — standalone UI to choose a geofence and employee.
- `assign_employee.js` — simple JS to call the API.
- `api_assign_employee.php` — POST endpoint that adds a record to `employee_location`.

How to use:

1. Place this folder under the project root (already in `HR_features`).
2. In your browser open: `http://localhost/geofence_test/HR_features/assign_employee.php`.
3. Select a site and employee, then click Assign.

Notes:
- This feature uses the existing `Modules/dbcon.php` connection.
- No existing project files were modified.
- The endpoint checks for existing assignment and validates both employee and geofence.
