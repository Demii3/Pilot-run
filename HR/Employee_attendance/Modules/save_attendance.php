<?php
	header('Content-Type: application/json');

	$dbc = null;
	include __DIR__ . '/dbcon.php';
	/** @var mysqli $dbc */

	function readAttendancePayload(): array {
		$rawInput = file_get_contents('php://input');

		if (is_string($rawInput) && trim($rawInput) !== '') {
			$decoded = json_decode($rawInput, true);
			if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
				return $decoded;
			}
		}

		return is_array($_POST) ? $_POST : [];
	}

	$items = readAttendancePayload();

	$employeeId = trim((string) ($items['employeeId'] ?? ''));
	$date = trim((string) ($items['date'] ?? ''));
	$location = trim((string) ($items['location'] ?? ''));
	$clockIn = trim((string) ($items['clockIn'] ?? ''));
	$clockInStatus = trim((string) ($items['clockInStatus'] ?? ''));
	$clockOut = trim((string) ($items['clockOut'] ?? ''));
	$clockOutStatus = trim((string) ($items['clockOutStatus'] ?? ''));
	$allowOvertime = isset($items['allowOvertime']) ? (int) $items['allowOvertime'] : 0;
	$workClassification = trim((string) ($items['workClassification'] ?? 'R'));
	$duration = isset($items['duration']) ? (float) $items['duration'] : 0;

	if ($employeeId === '' || $date === '' || $location === '' || $clockIn === '' || $clockInStatus === '' || $clockOut === '' || $clockOutStatus === '') {
		echo json_encode(['success' => false, 'message' => [$employeeId, $date, $location, $clockIn, $clockInStatus, $clockOut, $clockOutStatus], 'msg' => 'Please fill in all required attendance fields before saving.']);
		exit;
	}

	if (!ctype_digit($employeeId)) {
		echo json_encode(['success' => false, 'message' => 'Invalid employee id.', 'msg' => 'Invalid employee id.']);
		exit;
	}


	$sql = 'INSERT INTO employee_attendance (Emp_id, `Date`, `Location`, Clock_in, Clock_out, Clockin_status, Clockout_status, Duration, AO, Work_Classification) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
	$stmt = mysqli_prepare($dbc, $sql);

	if (!$stmt) {
		echo json_encode(['success' => false, 'message' => 'Failed to prepare insert query: ' . mysqli_error($dbc), 'msg' => 'Failed to prepare insert query: ' . mysqli_error($dbc)]);
		exit;
	}
	mysqli_stmt_bind_param(
		$stmt,
		'issssssdis',
		$employeeId,
		$date,
		$location,
		$clockIn,
		$clockOut,
		$clockInStatus,
		$clockOutStatus,
		$duration,
		$allowOvertime,
		$workClassification
	);

	$result = mysqli_stmt_execute($stmt);

	if ($result) {
		echo json_encode(['success' => true, 'message' => 'Attendance saved successfully.', 'msg' => 'Attendance saved successfully.']);
	} else {
		echo json_encode(['success' => false, 'message' => 'Recording of attendance failed: ' . mysqli_stmt_error($stmt), 'msg' => 'Recording of attendance failed: ' . mysqli_stmt_error($stmt)]);
	}

	mysqli_stmt_close($stmt);
?>
