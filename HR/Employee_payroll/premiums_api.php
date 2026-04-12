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
      `sss` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `philhealth` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `pagibig` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
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
}

function handleGetRequest($dbc) {
    $query = "SELECT id, employee_id, employee_name, sss, philhealth, pagibig, withholding_tax, total_premium, updated_at FROM premiums ORDER BY employee_name ASC";
    $result = mysqli_query($dbc, $query);

    if (!$result) {
        respond(false, null, 'Failed to fetch premiums: ' . mysqli_error($dbc), 500);
    }

    $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
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

        $sql = "INSERT INTO premiums (employee_id, employee_name, sss, philhealth, pagibig, withholding_tax, total_premium)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
              employee_name = VALUES(employee_name),
              sss = VALUES(sss),
              philhealth = VALUES(philhealth),
              pagibig = VALUES(pagibig),
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
        $totalPremium = isset($record['total_premium']) ? floatval($record['total_premium']) : ($sss + $philhealth + $pagibig + $withholdingTax);

        mysqli_stmt_bind_param($stmt, 'isddddd', $employeeId, $employeeName, $sss, $philhealth, $pagibig, $withholdingTax, $totalPremium);

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
