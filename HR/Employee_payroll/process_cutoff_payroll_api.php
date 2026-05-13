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

    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ]);
    exit;
}

function columnExists($dbc, $table, $column) {
    $table = mysqli_real_escape_string($dbc, $table);
    $column = mysqli_real_escape_string($dbc, $column);
    $result = mysqli_query($dbc, "SHOW COLUMNS FROM `" . $table . "` LIKE '" . $column . "'");
    return $result && mysqli_num_rows($result) > 0;
}

function ensureProcessedPayrollTables($dbc) {
    $createRuns = "CREATE TABLE IF NOT EXISTS `payroll_cutoff_runs` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `cutoff_key` VARCHAR(64) NOT NULL,
      `cutoff_from` VARCHAR(32) NOT NULL,
      `cutoff_to` VARCHAR(32) NOT NULL,
      `process_year_month` VARCHAR(7) NOT NULL,
      `cutoff_slot` TINYINT(1) NOT NULL,
      `processed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `processed_by` VARCHAR(255) DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `uniq_cutoff_key` (`cutoff_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    mysqli_query($dbc, $createRuns);
    if (mysqli_errno($dbc)) {
        respond(false, null, 'Database setup failed: ' . mysqli_error($dbc), 500);
    }

    $createItems = "CREATE TABLE IF NOT EXISTS `payroll_cutoff_items` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `run_id` INT UNSIGNED NOT NULL,
      `employee_id` INT UNSIGNED NOT NULL,
      `employee_name` VARCHAR(255) NOT NULL,
      `employee_email` VARCHAR(255) DEFAULT NULL,
      `monthly_salary` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `cutoff_salary` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `gross_pay_per_day` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `hours_worked` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `total_ot` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `legal_holiday` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `special_holiday` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `taxable_additional_income` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `non_taxable_additional_income` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `sss` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `philhealth` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `pagibig` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `tax` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `additional_deductions` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `total_deduction` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `net_pay` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `uniq_run_employee` (`run_id`, `employee_id`),
      CONSTRAINT `fk_payroll_cutoff_items_run` FOREIGN KEY (`run_id`) REFERENCES `payroll_cutoff_runs` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    mysqli_query($dbc, $createItems);
    if (mysqli_errno($dbc)) {
        respond(false, null, 'Database setup failed: ' . mysqli_error($dbc), 500);
    }

    if (!columnExists($dbc, 'employee_attendance', 'AO')) {
        respond(false, null, 'Attendance table is missing AO column required for payroll processing.', 500);
    }
}

function parseDateValue($value, $endOfDay = false) {
    if (!$value) {
        return null;
    }

    $value = trim((string)$value);
    $normalized = preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)
        ? $value
        : date('Y-m-d', strtotime($value));

    if (!$normalized) {
        return null;
    }

    return DateTime::createFromFormat('Y-m-d', $normalized) ?: null;
}

function cutoffContextFromDates($fromRaw, $toRaw) {
    $fromDate = parseDateValue($fromRaw);
    $toDate = parseDateValue($toRaw, true);

    if (!$fromDate || !$toDate) {
        return [null, null, null];
    }

    $yearMonth = $fromDate->format('Y-m');
    $cutoffSlot = ((int)$fromDate->format('j')) <= 15 ? 1 : 2;

    return [$fromDate, $toDate, [$yearMonth, $cutoffSlot]];
}

function makeCutoffKey($fromRaw, $toRaw) {
    return trim((string)$fromRaw) . '_' . trim((string)$toRaw);
}

function getAttendanceEmployeeId($item = []) {
    return StringHelper::trim((string)($item['Emp_id'] ?? $item['id'] ?? ''));
}

function getAttendanceItemDate($item = []) {
    $rawDate = $item['Date'] ?? $item['date'] ?? '';
    if (!$rawDate) {
        return null;
    }

    $normalized = trim((string)$rawDate);
    $parsedDate = preg_match('/^\d{4}-\d{2}-\d{2}/', $normalized)
        ? new DateTime(substr($normalized, 0, 10))
        : new DateTime($normalized);

    return $parsedDate instanceof DateTime ? $parsedDate : null;
}

function getAttendanceDurationMinutes($item = []) {
    $directDuration = isset($item['Duration']) ? floatval($item['Duration']) : (isset($item['duration']) ? floatval($item['duration']) : 0);
    if ($directDuration > 0) {
        return $directDuration;
    }

    $clockInValue = $item['Clock_in'] ?? $item['clockIn'] ?? null;
    $clockOutValue = $item['Clock_out'] ?? $item['clockOut'] ?? null;
    if (!$clockInValue || !$clockOutValue) {
        return 0;
    }

    $clockIn = new DateTime($clockInValue);
    $clockOut = new DateTime($clockOutValue);
    $durationMinutes = ($clockOut->getTimestamp() - $clockIn->getTimestamp()) / 60;
    if ($durationMinutes < 0) {
        $durationMinutes += 1440;
    }

    return $durationMinutes > 0 ? $durationMinutes : 0;
}

function buildAttendanceWorkMap($attendanceData, $dateFrom, $dateTo) {
    $attendanceByEmployee = [];

    foreach ($attendanceData as $item) {
        $employeeId = getAttendanceEmployeeId($item);
        if ($employeeId === '') {
            continue;
        }

        if ($dateFrom || $dateTo) {
            $itemDate = getAttendanceItemDate($item);
            if (!$itemDate) {
                continue;
            }
            if ($dateFrom && $itemDate < $dateFrom) {
                continue;
            }
            if ($dateTo && $itemDate > $dateTo) {
                continue;
            }
        }

        $durationMinutes = getAttendanceDurationMinutes($item);
        $isAO = isset($item['AO']) && intval($item['AO']) === 1;
        $regularMinutes = max(0, min($durationMinutes, 480));
        $overtimeMinutes = ($durationMinutes > 480 && $isAO) ? ($durationMinutes - 480) : 0;

        if (!isset($attendanceByEmployee[$employeeId])) {
            $attendanceByEmployee[$employeeId] = ['regularMinutes' => 0, 'overtimeMinutes' => 0];
        }

        $attendanceByEmployee[$employeeId]['regularMinutes'] += $regularMinutes;
        $attendanceByEmployee[$employeeId]['overtimeMinutes'] += $overtimeMinutes;
    }

    return $attendanceByEmployee;
}

function buildIncomeByTypeMap($assignedIncomeData = []) {
    $incomeByEmployee = [];

    foreach ($assignedIncomeData as $item) {
        $employeeNameKey = strtolower(trim((string)($item['name'] ?? '')));
        $incomeType = strtolower(trim((string)($item['type_of_income'] ?? '')));
        $cost = floatval($item['cost'] ?? 0);

        if ($employeeNameKey === '' || $incomeType === '') {
            continue;
        }

        if (!isset($incomeByEmployee[$employeeNameKey])) {
            $incomeByEmployee[$employeeNameKey] = [];
        }

        if (!isset($incomeByEmployee[$employeeNameKey][$incomeType])) {
            $incomeByEmployee[$employeeNameKey][$incomeType] = 0;
        }

        $incomeByEmployee[$employeeNameKey][$incomeType] += $cost;
    }

    return $incomeByEmployee;
}

function buildAdditionalIncomeSummaryMap($assignedIncomeData = []) {
    $summaryByEmployee = [];

    foreach ($assignedIncomeData as $item) {
        $employeeNameKey = strtolower(trim((string)($item['name'] ?? '')));
        $incomeType = strtolower(trim((string)($item['type_of_income'] ?? '')));
        $cost = floatval($item['cost'] ?? 0);

        if ($employeeNameKey === '' || $incomeType === '') {
            continue;
        }

        if (preg_match('/legal\s*holiday/i', $incomeType) || preg_match('/special\s*holiday/i', $incomeType)) {
            continue;
        }

        if (!isset($summaryByEmployee[$employeeNameKey])) {
            $summaryByEmployee[$employeeNameKey] = ['taxable' => 0, 'nonTaxable' => 0];
        }

        if (intval($item['taxable'] ?? 0) === 1) {
            $summaryByEmployee[$employeeNameKey]['taxable'] += $cost;
        } else {
            $summaryByEmployee[$employeeNameKey]['nonTaxable'] += $cost;
        }
    }

    return $summaryByEmployee;
}

function buildNonTaxableIncomeMap($assignedIncomeData = []) {
    $nonTaxableCostByEmployee = [];

    foreach ($assignedIncomeData as $item) {
        $employeeNameKey = strtolower(trim((string)($item['name'] ?? '')));
        $isNonTaxable = intval($item['taxable'] ?? 0) === 0;
        $cost = floatval($item['cost'] ?? 0);

        if ($employeeNameKey === '' || !$isNonTaxable) {
            continue;
        }

        if (!isset($nonTaxableCostByEmployee[$employeeNameKey])) {
            $nonTaxableCostByEmployee[$employeeNameKey] = 0;
        }

        $nonTaxableCostByEmployee[$employeeNameKey] += $cost;
    }

    return $nonTaxableCostByEmployee;
}

function buildPersonalCaMap($assignedDeductionData = []) {
    $personalCaByEmployee = [];

    foreach ($assignedDeductionData as $item) {
        $employeeNameKey = strtolower(trim((string)($item['name'] ?? '')));
        $cost = floatval($item['cost'] ?? 0);

        if ($employeeNameKey === '') {
            continue;
        }

        if (!isset($personalCaByEmployee[$employeeNameKey])) {
            $personalCaByEmployee[$employeeNameKey] = 0;
        }

        $personalCaByEmployee[$employeeNameKey] += $cost;
    }

    return $personalCaByEmployee;
}

function buildRecurringMapByIncomeType($incomeTypesData = []) {
    $recurringMap = [];
    foreach ($incomeTypesData as $item) {
        $typeNameKey = strtolower(trim((string)($item['type_of_income'] ?? '')));
        if ($typeNameKey === '') {
            continue;
        }
        $recurringMap[$typeNameKey] = intval($item['recurring'] ?? 0) === 1 ? 1 : 0;
    }
    return $recurringMap;
}

function filterAssignedIncomeByCutoff($assignedIncomeData = [], $incomeTypesData = [], $dateFrom = null, $dateTo = null) {
    $recurringMap = buildRecurringMapByIncomeType($incomeTypesData);
    $yearMonth = $dateFrom ? $dateFrom->format('Y-m') : null;
    $cutoffSlot = $dateFrom ? ((int)$dateFrom->format('j') <= 15 ? 1 : 2) : null;

    return array_values(array_filter($assignedIncomeData, function($item) use ($recurringMap, $yearMonth, $cutoffSlot) {
        $typeNameKey = strtolower(trim((string)($item['type_of_income'] ?? '')));
        $isRecurring = intval($item['recurring'] ?? 0) === 1 || ($typeNameKey !== '' && ($recurringMap[$typeNameKey] ?? 0) === 1);
        if ($isRecurring) {
            return true;
        }

        $applyYearMonth = trim((string)($item['apply_year_month'] ?? ''));
        $applyCutoffSlot = intval($item['apply_cutoff_slot'] ?? 0);
        if ($applyYearMonth === '' || !$applyCutoffSlot) {
            return true;
        }

        return $applyYearMonth === $yearMonth && $applyCutoffSlot === $cutoffSlot;
    }));
}

function buildRecurringDeductionTypeMap($deductionTypesData = []) {
    $recurringMap = [];
    foreach ($deductionTypesData as $item) {
        $typeNameKey = strtolower(trim((string)($item['type_of_deduction'] ?? '')));
        if ($typeNameKey === '') {
            continue;
        }
        $recurringMap[$typeNameKey] = intval($item['recurring'] ?? 0) === 1 ? 1 : 0;
    }
    return $recurringMap;
}

function filterAssignedDeductionsByCutoff($assignedDeductionData = [], $deductionTypesData = [], $dateFrom = null, $dateTo = null) {
    $recurringMap = buildRecurringDeductionTypeMap($deductionTypesData);
    $yearMonth = $dateFrom ? $dateFrom->format('Y-m') : null;
    $cutoffSlot = $dateFrom ? ((int)$dateFrom->format('j') <= 15 ? 1 : 2) : null;

    return array_values(array_filter($assignedDeductionData, function($item) use ($recurringMap, $yearMonth, $cutoffSlot) {
        $typeNameKey = strtolower(trim((string)($item['type_of_deduction'] ?? '')));
        $isRecurring = intval($item['recurring'] ?? 0) === 1 || ($typeNameKey !== '' && ($recurringMap[$typeNameKey] ?? 0) === 1);
        if ($isRecurring) {
            return true;
        }

        $applyYearMonth = trim((string)($item['apply_year_month'] ?? ''));
        $applyCutoffSlot = intval($item['apply_cutoff_slot'] ?? 0);
        if ($applyYearMonth === '' || !$applyCutoffSlot) {
            return true;
        }

        return $applyYearMonth === $yearMonth && $applyCutoffSlot === $cutoffSlot;
    }));
}

function getComputedIncomeAmountByKeyword($employeeIncomeByType = [], $keywordPattern = null, $multiplier = 1) {
    $total = 0;
    foreach ($employeeIncomeByType as $typeName => $amount) {
        if ($keywordPattern && preg_match($keywordPattern, $typeName)) {
            $total += floatval($amount);
        }
    }

    $computed = $total * floatval($multiplier ?: 1);
    return round($computed, 2);
}

function computeSssContribution($salaryValue) {
  $salary = Number.isFinite(Number($salaryValue)) ? Number($salaryValue) : null;
  if ($salary === null || $salary < 0) return null;
  if ($salary === 0) return 0;
  if ($salary < 5250) return 250;
  const bracketStep = Math.floor(($salary - 5250) / 500) + 1;
  const contribution = 250 + bracketStep * 25;
  return Math.min(contribution, 1750);
}
