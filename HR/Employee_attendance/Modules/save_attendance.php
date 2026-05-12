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

	function timeToMinutes(string $time): ?int {
		$time = trim($time);

		if ($time === '' || $time === '--:--' || $time === '--:-- --') {
			return null;
		}

		if (preg_match('/^(\d{1,2}):(\d{2})(?::\d{2})?\s*(AM|PM)$/i', $time, $matches)) {
			$hours = (int) $matches[1];
			$minutes = (int) $matches[2];
			$modifier = strtoupper($matches[3]);

			if ($modifier === 'PM' && $hours !== 12) {
				$hours += 12;
			} elseif ($modifier === 'AM' && $hours === 12) {
				$hours = 0;
			}

			return ($hours * 60) + $minutes;
		}

		if (preg_match('/^(\d{1,2}):(\d{2})(?::\d{2})?$/', $time, $matches)) {
			$hours = (int) $matches[1];
			$minutes = (int) $matches[2];

			return ($hours * 60) + $minutes;
		}

		return null;
	}

	function calculateAttendanceDuration(string $clockIn, string $clockOut, int $allowOvertime): float {
		$clockInMinutes = timeToMinutes($clockIn);
		$clockOutMinutes = timeToMinutes($clockOut);

		if ($clockInMinutes === null || $clockOutMinutes === null) {
			return 0;
		}

		if (!$allowOvertime) {
			$clockOutMinutes = min($clockOutMinutes, 17 * 60);
		}

		if ($clockOutMinutes === null || $clockInMinutes === null) {
			return 0;
		}

		if ($clockInMinutes >= 720 && $clockInMinutes <= 780) {
			$clockInMinutes = 0;
		}

		if ($clockOutMinutes >= 720 && $clockOutMinutes <= 780) {
			$clockOutMinutes = 0;
		}

		$duration = $clockOutMinutes - $clockInMinutes - 60;

		if ($duration < 0) {
			$duration += 24 * 60;
		}

		return (float) $duration;
	}

	$items = readAttendancePayload();

	$employeeId = trim((string) ($items['employeeId'] ?? ''));
	$date = trim((string) ($items['date'] ?? ''));
	$location = trim((string) ($items['location'] ?? ''));
	$locationCoordinates = trim((string) ($items['locationCoordinates'] ?? ''));
	$clockIn = trim((string) ($items['clockIn'] ?? ''));
	$clockInStatus = trim((string) ($items['clockInStatus'] ?? ''));
	$clockOut = trim((string) ($items['clockOut'] ?? ''));
	$clockOutStatus = trim((string) ($items['clockOutStatus'] ?? ''));
	$allowOvertime = isset($items['allowOvertime']) ? (int) $items['allowOvertime'] : 0;
	$workClassification = trim((string) ($items['workClassification'] ?? 'R'));

	if ($employeeId === '' || $date === '' || $location === '' || $locationCoordinates === '' || $clockIn === '' || $clockInStatus === '' || $clockOut === '' || $clockOutStatus === '') {
		echo json_encode(['success' => false, 'message' => 'Please fill in all required attendance fields before saving.', 'msg' => 'Please fill in all required attendance fields before saving.']);
		exit;
	}

	if (!ctype_digit($employeeId)) {
		echo json_encode(['success' => false, 'message' => 'Invalid employee id.', 'msg' => 'Invalid employee id.']);
		exit;
	}

	$duration = calculateAttendanceDuration($clockIn, $clockOut, $allowOvertime);

	$sql = 'INSERT INTO employee_attendance (Emp_id, Date, Location, Coordinates, Clock_in, Clock_out, Clockin_status, Clockout_status, Duration, AO, Work_Classification) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
	$stmt = mysqli_prepare($dbc, $sql);

	if (!$stmt) {
		echo json_encode(['success' => false, 'message' => 'Failed to prepare insert query: ' . mysqli_error($dbc), 'msg' => 'Failed to prepare insert query: ' . mysqli_error($dbc)]);
		exit;
	}

	mysqli_stmt_bind_param(
		$stmt,
		'isssssssdis',
		$employeeId,
		$date,
		$location,
		$locationCoordinates,
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
