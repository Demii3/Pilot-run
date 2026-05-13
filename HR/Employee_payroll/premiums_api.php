<?php
ob_start();
header('Content-Type: application/json');

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

include '../../Modules/dbcon.php';

function respond($success, $data = null, $message = '', $statusCode = 200) {
    http_response_code($statusCode);
    if (ob_get_length()) {
        ob_end_clean();
    }
    echo json_encode(['success' => $success, 'data' => $data, 'message' => $message]);
    exit;
}

function ensurePremiumsTable($dbc) {
    $createSql = "CREATE TABLE IF NOT EXISTS `premiums` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `employee_id` INT UNSIGNED NOT NULL,
      `employee_name` VARCHAR(255) NOT NULL,
            `period_year` INT NULL DEFAULT NULL,
            `period_month` TINYINT NULL DEFAULT NULL,
      `sss` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
    `sss_employee` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
    `sss_employer` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `philhealth` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
    `philhealth_employee` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
    `philhealth_employer` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `pagibig` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
    `pagibig_employee` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
    `pagibig_employer` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `withholding_tax` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
    `total_premium` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `uniq_employee_id` (`employee_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    mysqli_query($dbc, $createSql);
    if (mysqli_errno($dbc)) {
        respond(false, null, 'Database setup failed: ' . mysqli_error($dbc), 500);
    }

    // Ensure split columns exist for older databases.
    $requiredColumns = [
        'sss_employee' => "ALTER TABLE `premiums` ADD COLUMN `sss_employee` DECIMAL(15,2) NOT NULL DEFAULT '0.00' AFTER `sss`",
        'sss_employer' => "ALTER TABLE `premiums` ADD COLUMN `sss_employer` DECIMAL(15,2) NOT NULL DEFAULT '0.00' AFTER `sss_employee`",
        'philhealth_employee' => "ALTER TABLE `premiums` ADD COLUMN `philhealth_employee` DECIMAL(15,2) NOT NULL DEFAULT '0.00' AFTER `philhealth`",
        'philhealth_employer' => "ALTER TABLE `premiums` ADD COLUMN `philhealth_employer` DECIMAL(15,2) NOT NULL DEFAULT '0.00' AFTER `philhealth_employee`",
        'pagibig_employee' => "ALTER TABLE `premiums` ADD COLUMN `pagibig_employee` DECIMAL(15,2) NOT NULL DEFAULT '0.00' AFTER `pagibig`",
        'pagibig_employer' => "ALTER TABLE `premiums` ADD COLUMN `pagibig_employer` DECIMAL(15,2) NOT NULL DEFAULT '0.00' AFTER `pagibig_employee`"
    ];

    // Optional period columns for filtering by month/year
    $requiredColumns['period_year'] = "ALTER TABLE `premiums` ADD COLUMN `period_year` INT NULL DEFAULT NULL AFTER `employee_name`";
    $requiredColumns['period_month'] = "ALTER TABLE `premiums` ADD COLUMN `period_month` TINYINT NULL DEFAULT NULL AFTER `period_year`";

    foreach ($requiredColumns as $columnName => $alterSql) {
        $safeColumn = mysqli_real_escape_string($dbc, $columnName);
        $checkResult = mysqli_query($dbc, "SHOW COLUMNS FROM `premiums` LIKE '{$safeColumn}'");
        $exists = $checkResult && mysqli_num_rows($checkResult) > 0;
        if (!$exists) {
            mysqli_query($dbc, $alterSql);
            if (mysqli_errno($dbc)) {
                respond(false, null, 'Failed to upgrade premiums table: ' . mysqli_error($dbc), 500);
            }
        }
    }
}

function computeSssSplit($totalContribution) {
    $total = (float)$totalContribution;
    if ($total <= 0) {
        return ['employee' => 0.0, 'employer' => 0.0, 'total' => 0.0];
    }

    // SSS split from Circular basis: employee 5%, employer 10% of total 15%.
    $employee = round($total / 3, 2);
    $employer = round($total - $employee, 2);
    return ['employee' => $employee, 'employer' => $employer, 'total' => round($total, 2)];
}

function computeEvenSplit($totalContribution) {
    $total = (float)$totalContribution;
    if ($total <= 0) {
        return ['employee' => 0.0, 'employer' => 0.0, 'total' => 0.0];
    }

    $employee = round($total / 2, 2);
    $employer = round($total - $employee, 2);
    return ['employee' => $employee, 'employer' => $employer, 'total' => round($total, 2)];
}

function getMonthFilterBounds($year, $month) {
    $month = max(1, min(12, (int)$month));
    $year = (int)$year;
    $start = sprintf('%04d-%02d-01', $year, $month);
    $end = date('Y-m-t', strtotime($start));

    return [$start, $end];
}

function handleGetRequest($dbc) {
    $year = isset($_GET['year']) ? intval($_GET['year']) : null;
    $month = isset($_GET['month']) ? intval($_GET['month']) : null;

    if (!$year || !$month) {
        $latestSql = "SELECT YEAR(r.cutoff_to) AS year_value, MONTH(r.cutoff_to) AS month_value
                      FROM payroll_cutoff_runs r
                      ORDER BY r.cutoff_to DESC
                      LIMIT 1";
        $latestResult = mysqli_query($dbc, $latestSql);
        if ($latestResult && ($latestRow = mysqli_fetch_assoc($latestResult))) {
            $year = (int)$latestRow['year_value'];
            $month = (int)$latestRow['month_value'];
        } else {
            $year = (int)date('Y');
            $month = (int)date('n');
        }
    }

    [$startDate, $endDate] = getMonthFilterBounds($year, $month);

    $sql = "SELECT
                i.employee_id,
                i.employee_name,
                i.employee_email AS email,
                SUM(i.sss) AS sss,
                SUM(i.phlth) AS philhealth,
                SUM(i.pagibig) AS pagibig,
                SUM(i.tax) AS withholding_tax,
                SUM(i.sss) / 3 AS sss_employee,
                SUM(i.sss) - (SUM(i.sss) / 3) AS sss_employer,
                SUM(i.phlth) / 2 AS philhealth_employee,
                SUM(i.phlth) / 2 AS philhealth_employer,
                SUM(i.pagibig) / 2 AS pagibig_employee,
                SUM(i.pagibig) / 2 AS pagibig_employer,
                SUM(i.sss + i.phlth + i.pagibig + i.tax) AS total_premium,
                MAX(i.created_at) AS updated_at
            FROM payroll_cutoff_items i
            INNER JOIN payroll_cutoff_runs r ON r.id = i.run_id
            WHERE r.cutoff_to >= ? AND r.cutoff_to <= ?
            GROUP BY i.employee_id, i.employee_name, i.employee_email
            ORDER BY i.employee_name ASC";

    $stmt = mysqli_prepare($dbc, $sql);
    if (!$stmt) {
        respond(false, null, 'Failed to prepare remittance query: ' . mysqli_error($dbc), 500);
    }

    mysqli_stmt_bind_param($stmt, 'ss', $startDate, $endDate);
    if (!mysqli_stmt_execute($stmt)) {
        respond(false, null, 'Failed to fetch remittances: ' . mysqli_error($dbc), 500);
    }

    $result = mysqli_stmt_get_result($stmt);
    $rows = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];

    foreach ($rows as &$row) {
        $row['employee_id'] = (int)$row['employee_id'];
        $row['sss'] = round((float)$row['sss'], 2);
        $row['sss_employee'] = round((float)$row['sss_employee'], 2);
        $row['sss_employer'] = round((float)$row['sss_employer'], 2);
        $row['philhealth'] = round((float)$row['philhealth'], 2);
        $row['philhealth_employee'] = round((float)$row['philhealth_employee'], 2);
        $row['philhealth_employer'] = round((float)$row['philhealth_employer'], 2);
        $row['pagibig'] = round((float)$row['pagibig'], 2);
        $row['pagibig_employee'] = round((float)$row['pagibig_employee'], 2);
        $row['pagibig_employer'] = round((float)$row['pagibig_employer'], 2);
        $row['withholding_tax'] = round((float)$row['withholding_tax'], 2);
        $row['total_premium'] = round((float)$row['total_premium'], 2);
    }
    unset($row);

    respond(true, $rows);
}

function handlePostRequest($dbc) {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    $records = [];
    if (isset($input['records']) && is_array($input['records'])) {
        $records = $input['records'];
    } else if (isset($input['employee_id'])) {
        $records = [$input];
    }

    if (count($records) === 0) {
        respond(false, null, 'Invalid input. Provide at least one record.', 400);
    }

        $sql = "INSERT INTO premiums (
                                                        employee_id,
                                                        employee_name,
                                                        period_year,
                                                        period_month,
                                                        sss,
                                                        sss_employee,
                                                        sss_employer,
                                                        philhealth,
                                                        philhealth_employee,
                                                        philhealth_employer,
                                                        pagibig,
                                                        pagibig_employee,
                                                        pagibig_employer,
                                                        withholding_tax,
                                                        total_premium
                                                )
                                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                            employee_name = VALUES(employee_name),
                            period_year = VALUES(period_year),
                            period_month = VALUES(period_month),
                            sss = VALUES(sss),
                                                        sss_employee = VALUES(sss_employee),
                                                        sss_employer = VALUES(sss_employer),
                            philhealth = VALUES(philhealth),
                                                        philhealth_employee = VALUES(philhealth_employee),
                                                        philhealth_employer = VALUES(philhealth_employer),
                            pagibig = VALUES(pagibig),
                                                        pagibig_employee = VALUES(pagibig_employee),
                                                        pagibig_employer = VALUES(pagibig_employer),
                                                        withholding_tax = VALUES(withholding_tax),
                                                        total_premium = VALUES(total_premium)";

    $stmt = mysqli_prepare($dbc, $sql);
    if (!$stmt) {
        respond(false, null, 'Failed to prepare statement: ' . mysqli_error($dbc), 500);
    }

    $saved = 0;
    foreach ($records as $record) {
        if (!isset($record['employee_id'], $record['employee_name'])) {
            continue;
        }

        $employeeId = intval($record['employee_id']);
        $employeeName = trim((string)$record['employee_name']);
        if ($employeeId <= 0 || $employeeName === '') {
            continue;
        }

        $sss = isset($record['sss']) ? floatval($record['sss']) : 0;
        $philhealth = isset($record['philhealth']) ? floatval($record['philhealth']) : 0;
        $pagibig = isset($record['pagibig']) ? floatval($record['pagibig']) : 0;
        $withholdingTax = isset($record['withholding_tax']) ? floatval($record['withholding_tax']) : 0;

        $sssSplit = computeSssSplit($sss);
        $philhealthSplit = computeEvenSplit($philhealth);
        $pagibigSplit = computeEvenSplit($pagibig);

        $sssEmployee = isset($record['sss_employee']) ? floatval($record['sss_employee']) : $sssSplit['employee'];
        $sssEmployer = isset($record['sss_employer']) ? floatval($record['sss_employer']) : $sssSplit['employer'];
        $philhealthEmployee = isset($record['philhealth_employee']) ? floatval($record['philhealth_employee']) : $philhealthSplit['employee'];
        $philhealthEmployer = isset($record['philhealth_employer']) ? floatval($record['philhealth_employer']) : $philhealthSplit['employer'];
        $pagibigEmployee = isset($record['pagibig_employee']) ? floatval($record['pagibig_employee']) : $pagibigSplit['employee'];
        $pagibigEmployer = isset($record['pagibig_employer']) ? floatval($record['pagibig_employer']) : $pagibigSplit['employer'];

        $totalPremium = isset($record['total_premium']) ? floatval($record['total_premium']) : ($sss + $philhealth + $pagibig + $withholdingTax);

        $periodYear = isset($record['period_year']) ? intval($record['period_year']) : null;
        $periodMonth = isset($record['period_month']) ? intval($record['period_month']) : null;

        mysqli_stmt_bind_param(
            $stmt,
            'isiiddddddddd',
            $employeeId,
            $employeeName,
            $periodYear,
            $periodMonth,
            $sss,
            $sssEmployee,
            $sssEmployer,
            $philhealth,
            $philhealthEmployee,
            $philhealthEmployer,
            $pagibig,
            $pagibigEmployee,
            $pagibigEmployer,
            $withholdingTax,
            $totalPremium
        );

        if (!mysqli_stmt_execute($stmt)) {
            respond(false, null, 'Failed saving premiums: ' . mysqli_error($dbc), 500);
        }

        $saved++;
    }

    respond(true, ['saved' => $saved], 'Premiums saved successfully.');
}

try {
    if (!$dbc) {
        respond(false, null, 'Database connection failed.', 500);
    }

    ensurePremiumsTable($dbc);

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        handleGetRequest($dbc);
    }

    if ($method === 'POST') {
        handlePostRequest($dbc);
    }

    respond(false, null, 'Method not allowed.', 405);
} catch (Throwable $e) {
    respond(false, null, 'Server error: ' . $e->getMessage(), 500);
}
