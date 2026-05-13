<?php
ob_start();
header('Content-Type: application/json');

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

include '../../Modules/dbcon.php';

function respond($success, $data = null, $message = '', $statusCode = 200)
{
    http_response_code($statusCode);
    if (ob_get_length()) {
        ob_end_clean();
    }

    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ]);
    exit;
}

function tableExists($dbc, $name)
{
    $escaped = mysqli_real_escape_string($dbc, $name);
    $result = mysqli_query($dbc, "SHOW TABLES LIKE '{$escaped}'");
    return $result && mysqli_num_rows($result) > 0;
}

function columnHasAutoIncrement($dbc, $table, $column)
{
    $table = mysqli_real_escape_string($dbc, $table);
    $column = mysqli_real_escape_string($dbc, $column);
    $result = mysqli_query($dbc, "SHOW COLUMNS FROM `" . $table . "` LIKE '" . $column . "'");
    if (!$result || mysqli_num_rows($result) === 0) {
        return false;
    }

    $row = mysqli_fetch_assoc($result);
    return isset($row['Extra']) && stripos($row['Extra'], 'auto_increment') !== false;
}

function ensureProcessed13MonthTable($dbc)
{
    $createSql = "CREATE TABLE IF NOT EXISTS `employee_13th_month` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `employee_id` INT UNSIGNED NOT NULL,
      `employee_name` VARCHAR(255) NOT NULL,
      `process_year` SMALLINT NOT NULL,
      `monthly_salary` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `total_basic_salary_earned` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `month_13_pay` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `computed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `uniq_employee_year` (`employee_id`, `process_year`),
      KEY `idx_process_year` (`process_year`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    mysqli_query($dbc, $createSql);
    if (mysqli_errno($dbc)) {
        respond(false, null, 'Database setup failed: ' . mysqli_error($dbc), 500);
    }

    if (!columnHasAutoIncrement($dbc, 'employee_13th_month', 'id')) {
        mysqli_query($dbc, "ALTER TABLE `employee_13th_month` MODIFY `id` INT UNSIGNED NOT NULL AUTO_INCREMENT");
        if (mysqli_errno($dbc)) {
            respond(false, null, 'Failed to fix employee_13th_month id column: ' . mysqli_error($dbc), 500);
        }
    }
}

function fetchRegularMinutesByEmployee($dbc, $year)
{
    if (!tableExists($dbc, 'employee_attendance')) {
        return [];
    }

    $fromDate = sprintf('%04d-01-01', $year);
    $toDate = sprintf('%04d-12-31', $year);

    $query = "SELECT
                Emp_id,
                SUM(LEAST(COALESCE(Duration, 0), 480)) AS regular_minutes
              FROM employee_attendance
              WHERE Date BETWEEN ? AND ?
              GROUP BY Emp_id";

    $stmt = mysqli_prepare($dbc, $query);
    if (!$stmt) {
        respond(false, null, 'Failed to prepare attendance query: ' . mysqli_error($dbc), 500);
    }

    mysqli_stmt_bind_param($stmt, 'ss', $fromDate, $toDate);
    if (!mysqli_stmt_execute($stmt)) {
        respond(false, null, 'Failed to fetch attendance: ' . mysqli_error($dbc), 500);
    }

    $result = mysqli_stmt_get_result($stmt);
    $minutesByEmployee = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $employeeId = (string) ($row['Emp_id'] ?? '');
        if ($employeeId === '') {
            continue;
        }

        $minutesByEmployee[$employeeId] = (float) ($row['regular_minutes'] ?? 0);
    }

    mysqli_stmt_close($stmt);
    return $minutesByEmployee;
}

function upsert13MonthRows($dbc, $rows)
{
    if (empty($rows)) {
        return; // Nothing to insert
    }
    foreach ($rows as $row) {
        $employeeId = (int) $row['id'];
        $employeeName = mysqli_real_escape_string($dbc, (string) $row['name']);
        $year = (int) $row['process_year'];
        $monthlySalary = (float) $row['salary'];
        $totalBasicSalaryEarned = (float) $row['total_basic_salary_earned'];
        $month13Pay = (float) $row['month_13_pay'];

        $sql = sprintf(
            "INSERT INTO employee_13th_month (
                employee_id,
                employee_name,
                process_year,
                monthly_salary,
                total_basic_salary_earned,
                month_13_pay
            ) VALUES (%d, '%s', %d, %.2f, %.2f, %.2f)
            ON DUPLICATE KEY UPDATE
                employee_name = VALUES(employee_name),
                monthly_salary = VALUES(monthly_salary),
                total_basic_salary_earned = VALUES(total_basic_salary_earned),
                month_13_pay = VALUES(month_13_pay),
                computed_at = CURRENT_TIMESTAMP",
            $employeeId,
            $employeeName,
            $year,
            $monthlySalary,
            $totalBasicSalaryEarned,
            $month13Pay
        );

        if (!mysqli_query($dbc, $sql)) {
            respond(false, null, 'Failed to save 13th month results: ' . mysqli_error($dbc), 500);
        }
    }
}

function fetchStored13MonthRows($dbc, $year)
{
    $query = "SELECT
                employee_id AS id,
                employee_name AS name,
                monthly_salary AS salary,
                process_year,
                total_basic_salary_earned,
                month_13_pay,
                computed_at
              FROM employee_13th_month
              WHERE process_year = ?
              ORDER BY employee_id ASC";

    $stmt = mysqli_prepare($dbc, $query);
    if (!$stmt) {
        respond(false, null, 'Failed to prepare listing query: ' . mysqli_error($dbc), 500);
    }

    mysqli_stmt_bind_param($stmt, 'i', $year);
    if (!mysqli_stmt_execute($stmt)) {
        respond(false, null, 'Failed to fetch listing data: ' . mysqli_error($dbc), 500);
    }

    $result = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = [
            'id' => (int) $row['id'],
            'name' => (string) ($row['name'] ?? ''),
            'salary' => round((float) ($row['salary'] ?? 0), 2),
            'process_year' => (int) ($row['process_year'] ?? $year),
            'total_basic_salary_earned' => round((float) ($row['total_basic_salary_earned'] ?? 0), 2),
            'month_13_pay' => round((float) ($row['month_13_pay'] ?? 0), 2),
            'computed_at' => (string) ($row['computed_at'] ?? '')
        ];
    }

    mysqli_stmt_close($stmt);
    return $rows;
}

function handleGetRequest($dbc)
{
    if (!tableExists($dbc, 'employees')) {
        respond(false, null, 'Employees table not found.', 500);
    }

    $year = isset($_GET['year']) && is_numeric($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));
    if ($year < 1900 || $year > 9999) {
        respond(false, null, 'Invalid year.', 400);
    }

    $storedOnly = isset($_GET['stored']) && ($_GET['stored'] === '1' || strtolower((string) $_GET['stored']) === 'true');
    if ($storedOnly) {
        $storedRows = fetchStored13MonthRows($dbc, $year);
        respond(true, $storedRows);
    }

    $employeeResult = mysqli_query($dbc, "SELECT id, name, salary FROM employees ORDER BY id ASC");
    if (!$employeeResult) {
        respond(false, null, 'Failed to fetch employees: ' . mysqli_error($dbc), 500);
    }

    $minutesByEmployee = fetchRegularMinutesByEmployee($dbc, $year);

    $rows = [];

    while ($employee = mysqli_fetch_assoc($employeeResult)) {
        $employeeId = (string) ($employee['id'] ?? '');
        if ($employeeId === '') {
            continue;
        }

        $salary = (float) ($employee['salary'] ?? 0);
        $grossPayPerDay = $salary / 26;
        $regularMinutesWorked = (float) ($minutesByEmployee[$employeeId] ?? 0);

        // Include all employees, even those with zero attendance
        $regularDaysWorked = $regularMinutesWorked / 480;
        $totalBasicSalaryEarned = $regularDaysWorked * $grossPayPerDay;
        $month13Pay = $totalBasicSalaryEarned / 12;

        $rows[] = [
            'id' => (int) $employee['id'],
            'name' => (string) ($employee['name'] ?? ''),
            'salary' => round($salary, 2),
            'process_year' => $year,
            'total_basic_salary_earned' => round($totalBasicSalaryEarned, 2),
            'month_13_pay' => round($month13Pay, 2)
        ];
    }

    upsert13MonthRows($dbc, $rows);
    respond(true, $rows);
}

try {
    if (!$dbc) {
        respond(false, null, 'Database connection failed.', 500);
    }

    ensureProcessed13MonthTable($dbc);

    $method = $_SERVER['REQUEST_METHOD'];
    if ($method === 'GET') {
        handleGetRequest($dbc);
    }

    respond(false, null, 'Method not allowed.', 405);
} catch (Throwable $e) {
    respond(false, null, 'Server error: ' . $e->getMessage(), 500);
}
