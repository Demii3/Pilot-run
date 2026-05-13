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

function parseDateValue($value) {
    $text = trim((string)$value);
    if ($text === '') {
        return null;
    }

    $timestamp = strtotime($text);
    if ($timestamp === false) {
        return null;
    }

    return date('Y-m-d', $timestamp);
}

function ensureTables($dbc) {
    $runsSql = "CREATE TABLE IF NOT EXISTS `payroll_cutoff_runs` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `cutoff_key` VARCHAR(64) NOT NULL,
      `cutoff_from` DATE NOT NULL,
      `cutoff_to` DATE NOT NULL,
      `year_month` VARCHAR(7) NOT NULL,
      `cutoff_slot` TINYINT(1) NOT NULL,
      `processed_by` VARCHAR(255) DEFAULT NULL,
      `processed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `uniq_cutoff_key` (`cutoff_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    mysqli_query($dbc, $runsSql);
    if (mysqli_errno($dbc)) {
        respond(false, null, 'Failed to create payroll_cutoff_runs: ' . mysqli_error($dbc), 500);
    }

    $itemsSql = "CREATE TABLE IF NOT EXISTS `payroll_cutoff_items` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `run_id` INT UNSIGNED NOT NULL,
      `employee_id` INT UNSIGNED NOT NULL,
      `employee_name` VARCHAR(255) NOT NULL,
      `employee_email` VARCHAR(255) DEFAULT '',
      `monthly_salary` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `cutoff_salary` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `gross_pay_per_day` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `hours_worked` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `total_ot_pay` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `legal_holiday` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `special_holiday` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `taxable_additional_income` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `non_taxable_additional_income` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `sss` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `phlth` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `pagibig` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `tax` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `additional_deductions` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `total_deduction` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `carry_in` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `carry_out` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `net_pay` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `uniq_run_employee` (`run_id`, `employee_id`),
      CONSTRAINT `fk_payroll_cutoff_items_run` FOREIGN KEY (`run_id`) REFERENCES `payroll_cutoff_runs` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    mysqli_query($dbc, $itemsSql);
    if (mysqli_errno($dbc)) {
        respond(false, null, 'Failed to create payroll_cutoff_items: ' . mysqli_error($dbc), 500);
    }

    $runSchemaFixes = [
        ['column' => 'year_month', 'sql' => "ALTER TABLE `payroll_cutoff_runs` ADD COLUMN `year_month` VARCHAR(7) NOT NULL DEFAULT '' AFTER `cutoff_to`"],
        ['column' => 'cutoff_slot', 'sql' => "ALTER TABLE `payroll_cutoff_runs` ADD COLUMN `cutoff_slot` TINYINT(1) NOT NULL DEFAULT 0 AFTER `year_month`"],
        ['column' => 'processed_by', 'sql' => "ALTER TABLE `payroll_cutoff_runs` ADD COLUMN `processed_by` VARCHAR(255) DEFAULT NULL AFTER `cutoff_slot`"]
    ];

    foreach ($runSchemaFixes as $fix) {
        if (!columnExists($dbc, 'payroll_cutoff_runs', $fix['column'])) {
            mysqli_query($dbc, $fix['sql']);
            if (mysqli_errno($dbc)) {
                respond(false, null, 'Failed to upgrade payroll_cutoff_runs: ' . mysqli_error($dbc), 500);
            }
        }
    }

    $schemaFixes = [
        ['table' => 'assigned_emp_inc', 'column' => 'recurring', 'sql' => "ALTER TABLE `assigned_emp_inc` ADD COLUMN `recurring` TINYINT(1) NOT NULL DEFAULT 0 AFTER `month_13th`"],
        ['table' => 'assigned_emp_deduc', 'column' => 'recurring', 'sql' => "ALTER TABLE `assigned_emp_deduc` ADD COLUMN `recurring` TINYINT(1) NOT NULL DEFAULT 0 AFTER `cost`"],
        ['table' => 'emp_inc_type', 'column' => 'recurring', 'sql' => "ALTER TABLE `emp_inc_type` ADD COLUMN `recurring` TINYINT(1) NOT NULL DEFAULT 0 AFTER `included_in_13month`"],
        ['table' => 'emp_deduc_type', 'column' => 'recurring', 'sql' => "ALTER TABLE `emp_deduc_type` ADD COLUMN `recurring` TINYINT(1) NOT NULL DEFAULT 0 AFTER `included_in_13month`"]
    ];

    foreach ($schemaFixes as $fix) {
        if (!columnExists($dbc, $fix['table'], $fix['column'])) {
            mysqli_query($dbc, $fix['sql']);
            if (mysqli_errno($dbc)) {
                respond(false, null, 'Failed to upgrade ' . $fix['table'] . ': ' . mysqli_error($dbc), 500);
            }
        }
    }
}

function columnExists($dbc, $table, $column) {
    $table = mysqli_real_escape_string($dbc, $table);
    $column = mysqli_real_escape_string($dbc, $column);
    $result = mysqli_query($dbc, "SHOW COLUMNS FROM `" . $table . "` LIKE '" . $column . "'");
    return $result && mysqli_num_rows($result) > 0;
}

function loadEmployees($dbc) {
    $result = mysqli_query($dbc, "SELECT id, name, email, salary FROM employees ORDER BY id ASC");
    if (!$result) {
        respond(false, null, 'Failed to load employees: ' . mysqli_error($dbc), 500);
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function loadAttendance($dbc, $fromDate, $toDate) {
    $sql = "SELECT Emp_id, Date, Clock_in, Clock_out, Duration, AO
            FROM employee_attendance
            WHERE Date >= ? AND Date <= ?
            ORDER BY Date ASC";
    $stmt = mysqli_prepare($dbc, $sql);
    mysqli_stmt_bind_param($stmt, 'ss', $fromDate, $toDate);
    if (!mysqli_stmt_execute($stmt)) {
        respond(false, null, 'Failed to load attendance: ' . mysqli_error($dbc), 500);
    }

    $result = mysqli_stmt_get_result($stmt);
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

function loadAssignedIncome($dbc) {
    $result = mysqli_query($dbc, "SELECT name, type_of_income, cost, taxable, recurring, apply_year_month, apply_cutoff_slot FROM assigned_emp_inc");
    if (!$result) {
        respond(false, null, 'Failed to load assigned income: ' . mysqli_error($dbc), 500);
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function loadIncomeTypes($dbc) {
    $result = mysqli_query($dbc, "SELECT type_of_income, recurring FROM emp_inc_type");
    if (!$result) {
        return [];
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function loadAssignedDeductions($dbc) {
    $result = mysqli_query($dbc, "SELECT name, type_of_deduction, cost, recurring, apply_year_month, apply_cutoff_slot FROM assigned_emp_deduc");
    if (!$result) {
        respond(false, null, 'Failed to load assigned deductions: ' . mysqli_error($dbc), 500);
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function loadDeductionTypes($dbc) {
    $result = mysqli_query($dbc, "SELECT type_of_deduction, recurring FROM emp_deduc_type");
    if (!$result) {
        return [];
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function attendanceMap($attendanceRows, $fromDate = null, $toDate = null) {
    $map = [];

    foreach ($attendanceRows as $item) {
        $employeeId = trim((string)($item['Emp_id'] ?? ''));
        if ($employeeId === '') {
            continue;
        }

        $dateValue = trim((string)($item['Date'] ?? ''));
        if ($fromDate && $dateValue < $fromDate) {
            continue;
        }
        if ($toDate && $dateValue > $toDate) {
            continue;
        }

        $duration = (float)($item['Duration'] ?? 0);
        if ($duration <= 0 && !empty($item['Clock_in']) && !empty($item['Clock_out'])) {
            $clockIn = strtotime((string)$item['Clock_in']);
            $clockOut = strtotime((string)$item['Clock_out']);
            if ($clockIn !== false && $clockOut !== false) {
                $duration = ($clockOut - $clockIn) / 60;
                if ($duration < 0) {
                    $duration += 1440;
                }
            }
        }

        $duration = max(0, $duration);
        $isAO = (int)($item['AO'] ?? 0) === 1;
        $regularMinutes = max(0, min($duration, 480));
        $overtimeMinutes = $duration > 480 && $isAO ? ($duration - 480) : 0;

        if (!isset($map[$employeeId])) {
            $map[$employeeId] = ['regularMinutes' => 0, 'overtimeMinutes' => 0];
        }

        $map[$employeeId]['regularMinutes'] += $regularMinutes;
        $map[$employeeId]['overtimeMinutes'] += $overtimeMinutes;
    }

    return $map;
}

function cutoffContext($fromDate) {
    $timestamp = strtotime($fromDate ?: date('Y-m-d'));
    return [
        'yearMonth' => date('Y-m', $timestamp),
        'cutoffSlot' => ((int)date('j', $timestamp) <= 15) ? 1 : 2
    ];
}

function incomeByTypeMap($items) {
    $map = [];
    foreach ($items as $item) {
        $nameKey = strtolower(trim((string)($item['name'] ?? '')));
        $typeKey = strtolower(trim((string)($item['type_of_income'] ?? '')));
        if ($nameKey === '' || $typeKey === '') {
            continue;
        }
        $map[$nameKey][$typeKey] = ($map[$nameKey][$typeKey] ?? 0) + (float)($item['cost'] ?? 0);
    }
    return $map;
}

function additionalIncomeMap($items) {
    $map = [];
    foreach ($items as $item) {
        $nameKey = strtolower(trim((string)($item['name'] ?? '')));
        $typeKey = strtolower(trim((string)($item['type_of_income'] ?? '')));
        if ($nameKey === '' || $typeKey === '') {
            continue;
        }
        if (preg_match('/legal\s*holiday/i', $typeKey) || preg_match('/special\s*holiday/i', $typeKey)) {
            continue;
        }
        if (!isset($map[$nameKey])) {
            $map[$nameKey] = ['taxable' => 0, 'nonTaxable' => 0];
        }
        if ((int)($item['taxable'] ?? 0) === 1) {
            $map[$nameKey]['taxable'] += (float)($item['cost'] ?? 0);
        } else {
            $map[$nameKey]['nonTaxable'] += (float)($item['cost'] ?? 0);
        }
    }
    return $map;
}

function nonTaxableMap($items) {
    $map = [];
    foreach ($items as $item) {
        $nameKey = strtolower(trim((string)($item['name'] ?? '')));
        if ($nameKey === '' || (int)($item['taxable'] ?? 0) !== 0) {
            continue;
        }
        $map[$nameKey] = ($map[$nameKey] ?? 0) + (float)($item['cost'] ?? 0);
    }
    return $map;
}

function personalCaMap($items) {
    $map = [];
    foreach ($items as $item) {
        $nameKey = strtolower(trim((string)($item['name'] ?? '')));
        if ($nameKey === '') {
            continue;
        }
        $map[$nameKey] = ($map[$nameKey] ?? 0) + (float)($item['cost'] ?? 0);
    }
    return $map;
}

function recurringIncomeMap($types) {
    $map = [];
    foreach ($types as $item) {
        $key = strtolower(trim((string)($item['type_of_income'] ?? '')));
        if ($key !== '') {
            $map[$key] = (int)($item['recurring'] ?? 0) === 1;
        }
    }
    return $map;
}

function recurringDeductionMap($types) {
    $map = [];
    foreach ($types as $item) {
        $key = strtolower(trim((string)($item['type_of_deduction'] ?? '')));
        if ($key !== '') {
            $map[$key] = (int)($item['recurring'] ?? 0) === 1;
        }
    }
    return $map;
}

function filterIncomeByCutoff($items, $types, $fromDate) {
    $context = cutoffContext($fromDate);
    $recurring = recurringIncomeMap($types);
    return array_values(array_filter($items, function($item) use ($context, $recurring) {
        $typeKey = strtolower(trim((string)($item['type_of_income'] ?? '')));
        $isRecurring = (int)($item['recurring'] ?? 0) === 1 || ($typeKey !== '' && !empty($recurring[$typeKey]));
        if ($isRecurring) {
            return true;
        }
        $applyYearMonth = trim((string)($item['apply_year_month'] ?? ''));
        $applyCutoffSlot = (int)($item['apply_cutoff_slot'] ?? 0);
        if ($applyYearMonth === '' || $applyCutoffSlot === 0) {
            return true;
        }
        return $applyYearMonth === $context['yearMonth'] && $applyCutoffSlot === $context['cutoffSlot'];
    }));
}

function filterDeductionByCutoff($items, $types, $fromDate) {
    $context = cutoffContext($fromDate);
    $recurring = recurringDeductionMap($types);
    return array_values(array_filter($items, function($item) use ($context, $recurring) {
        $typeKey = strtolower(trim((string)($item['type_of_deduction'] ?? '')));
        $isRecurring = (int)($item['recurring'] ?? 0) === 1 || ($typeKey !== '' && !empty($recurring[$typeKey]));
        if ($isRecurring) {
            return true;
        }
        $applyYearMonth = trim((string)($item['apply_year_month'] ?? ''));
        $applyCutoffSlot = (int)($item['apply_cutoff_slot'] ?? 0);
        if ($applyYearMonth === '' || $applyCutoffSlot === 0) {
            return true;
        }
        return $applyYearMonth === $context['yearMonth'] && $applyCutoffSlot === $context['cutoffSlot'];
    }));
}

function computeSss($salary) {
    $salary = (float)$salary;
    if ($salary <= 0) return 0;

    // SSS (effective Jan 2025): 15% total of MSC, with 5,000 floor and 35,000 ceiling.
    if ($salary < 5250) {
        $msc = 5000;
    } else if ($salary >= 34750) {
        $msc = 35000;
    } else {
        $step = floor(($salary - 5250) / 500) + 1;
        $msc = 5000 + ($step * 500);
    }

    return round($msc * 0.15, 2);
}

function computePagibig($salary) {
    $salary = (float)$salary;
    if ($salary <= 0) return 0;

    // Pag-IBIG total monthly contribution: 2% (1% employee + 1% employer), capped at 200.
    return round(min($salary * 0.02, 200), 2);
}

function computePhilhealth($salary) {
    $salary = (float)$salary;
    if ($salary <= 0) return 0;

    // PhilHealth monthly premium: 5% with salary floor 10,000 and ceiling 100,000.
    $premiumBasis = min(max($salary, 10000), 100000);
    return round($premiumBasis * 0.05, 2);
}

function computeTax($salary, $sss, $ph, $pagibig, $nonTaxable) {
    $salary = (float)$salary;
    if ($salary <= 0) return 0;
    $taxable = max(0, $salary - (float)$nonTaxable - (float)$sss - (float)$ph - (float)$pagibig);
    if ($taxable <= 20833) return 0;
    if ($taxable <= 33333) return ($taxable - 20833) * 0.15;
    if ($taxable <= 66667) return 1875 + ($taxable - 33333) * 0.20;
    if ($taxable <= 166667) return 8541.8 + ($taxable - 66667) * 0.25;
    if ($taxable <= 666667) return 33541.8 + ($taxable - 166667) * 0.30;
    return 183541.8 + ($taxable - 666667) * 0.35;
}

function previousCarryOut($dbc, $employeeId, $fromDate) {
    $runSql = "SELECT id FROM payroll_cutoff_runs WHERE cutoff_to < ? ORDER BY cutoff_to DESC LIMIT 1";
    $runStmt = mysqli_prepare($dbc, $runSql);
    mysqli_stmt_bind_param($runStmt, 's', $fromDate);
    if (!mysqli_stmt_execute($runStmt)) {
        return 0;
    }

    $runResult = mysqli_stmt_get_result($runStmt);
    $runRow = $runResult ? mysqli_fetch_assoc($runResult) : null;
    $runId = $runRow ? (int)$runRow['id'] : 0;
    if ($runId <= 0) {
        return 0;
    }

    $sql = "SELECT carry_out FROM payroll_cutoff_items WHERE run_id = ? AND employee_id = ? LIMIT 1";
    $stmt = mysqli_prepare($dbc, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $runId, $employeeId);
    if (!mysqli_stmt_execute($stmt)) {
        return 0;
    }

    $result = mysqli_stmt_get_result($stmt);
    $row = $result ? mysqli_fetch_assoc($result) : null;
    return $row ? (float)$row['carry_out'] : 0;
}

function getComputedIncomeAmountByKeyword($employeeIncomeByType = [], $keywordPattern = null, $multiplier = 1) {
    if (!is_array($employeeIncomeByType) || $keywordPattern === null) {
        return 0;
    }

    $total = 0;
    foreach ($employeeIncomeByType as $typeName => $amount) {
        if (preg_match($keywordPattern, (string)$typeName)) {
            $total += (float)$amount;
        }
    }

    return round($total * (float)$multiplier, 2);
}

function processPayroll($dbc, $fromDate, $toDate) {
    $employees = loadEmployees($dbc);
    $attendance = loadAttendance($dbc, $fromDate, $toDate);
    $assignedIncome = loadAssignedIncome($dbc);
    $incomeTypes = loadIncomeTypes($dbc);
    $assignedDeductions = loadAssignedDeductions($dbc);
    $deductionTypes = loadDeductionTypes($dbc);

    $assignedIncome = filterIncomeByCutoff($assignedIncome, $incomeTypes, $fromDate);
    $assignedDeductions = filterDeductionByCutoff($assignedDeductions, $deductionTypes, $fromDate);

    $attendanceMap = attendanceMap($attendance, $fromDate, $toDate);
    $incomeMap = incomeByTypeMap($assignedIncome);
    $additionalMap = additionalIncomeMap($assignedIncome);
    $nonTaxableIncome = nonTaxableMap($assignedIncome);
    $personalCa = personalCaMap($assignedDeductions);

    $yearMonth = date('Y-m', strtotime($fromDate));
    $cutoffSlot = ((int)date('j', strtotime($fromDate)) <= 15) ? 1 : 2;
    $cutoffKey = $fromDate . '_' . $toDate;
    $processedRows = [];

    mysqli_begin_transaction($dbc);
    try {
        $lookupSql = "SELECT id FROM payroll_cutoff_runs WHERE cutoff_key = ? LIMIT 1";
        $lookupStmt = mysqli_prepare($dbc, $lookupSql);
        if (!$lookupStmt) {
            throw new RuntimeException('Failed to prepare payroll run lookup: ' . mysqli_error($dbc));
        }

        mysqli_stmt_bind_param($lookupStmt, 's', $cutoffKey);
        if (!mysqli_stmt_execute($lookupStmt)) {
            throw new RuntimeException('Failed to look up payroll run: ' . mysqli_error($dbc));
        }

        $lookupResult = mysqli_stmt_get_result($lookupStmt);
        $runRow = $lookupResult ? mysqli_fetch_assoc($lookupResult) : null;
        $runId = $runRow ? (int)$runRow['id'] : 0;

        if ($runId > 0) {
            $updateSql = "UPDATE payroll_cutoff_runs
                          SET cutoff_from = ?, cutoff_to = ?, processed_at = CURRENT_TIMESTAMP
                          WHERE id = ?";
            $updateStmt = mysqli_prepare($dbc, $updateSql);
            if (!$updateStmt) {
                throw new RuntimeException('Failed to prepare payroll run update: ' . mysqli_error($dbc));
            }

            mysqli_stmt_bind_param($updateStmt, 'ssi', $fromDate, $toDate, $runId);
            if (!mysqli_stmt_execute($updateStmt)) {
                throw new RuntimeException('Failed to update payroll run: ' . mysqli_error($dbc));
            }
        } else {
            $insertSql = "INSERT INTO payroll_cutoff_runs (cutoff_key, cutoff_from, cutoff_to)
                          VALUES (?, ?, ?)
                          ON DUPLICATE KEY UPDATE cutoff_from = VALUES(cutoff_from), cutoff_to = VALUES(cutoff_to), processed_at = CURRENT_TIMESTAMP";
            $insertStmt = mysqli_prepare($dbc, $insertSql);
            if (!$insertStmt) {
                throw new RuntimeException('Failed to prepare payroll run insert: ' . mysqli_error($dbc));
            }

            mysqli_stmt_bind_param($insertStmt, 'sss', $cutoffKey, $fromDate, $toDate);
            if (!mysqli_stmt_execute($insertStmt)) {
                throw new RuntimeException('Failed to save payroll run: ' . mysqli_error($dbc));
            }

            $runId = mysqli_insert_id($dbc);
        }

        if ($runId <= 0) {
            throw new RuntimeException('Unable to determine payroll run ID.');
        }

        $itemSql = "INSERT INTO payroll_cutoff_items
            (run_id, employee_id, employee_name, employee_email, monthly_salary, cutoff_salary, gross_pay_per_day, hours_worked, total_ot_pay,
             legal_holiday, special_holiday, taxable_additional_income, non_taxable_additional_income, sss, phlth, pagibig, tax, additional_deductions, total_deduction, carry_in, carry_out, net_pay)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
             employee_name = VALUES(employee_name), employee_email = VALUES(employee_email), monthly_salary = VALUES(monthly_salary), cutoff_salary = VALUES(cutoff_salary),
             gross_pay_per_day = VALUES(gross_pay_per_day), hours_worked = VALUES(hours_worked), total_ot_pay = VALUES(total_ot_pay), legal_holiday = VALUES(legal_holiday),
             special_holiday = VALUES(special_holiday), taxable_additional_income = VALUES(taxable_additional_income), non_taxable_additional_income = VALUES(non_taxable_additional_income),
             sss = VALUES(sss), phlth = VALUES(phlth), pagibig = VALUES(pagibig), tax = VALUES(tax), additional_deductions = VALUES(additional_deductions),
             total_deduction = VALUES(total_deduction), carry_in = VALUES(carry_in), carry_out = VALUES(carry_out), net_pay = VALUES(net_pay), created_at = CURRENT_TIMESTAMP";
        $itemStmt = mysqli_prepare($dbc, $itemSql);

        foreach ($employees as $employee) {
            $employeeId = (int)($employee['id'] ?? 0);
            $employeeName = (string)($employee['name'] ?? '');
            $employeeEmail = (string)($employee['email'] ?? '');
            $monthlySalary = (float)($employee['salary'] ?? 0);
            $grossPayPerDay = $monthlySalary / 26;
            $hourlyRate = $grossPayPerDay / 8;

            $work = $attendanceMap[(string)$employeeId] ?? ['regularMinutes' => 0, 'overtimeMinutes' => 0];
            $hoursWorked = ((float)($work['regularMinutes'] ?? 0)) / 60;
            $cutoffSalary = $hourlyRate * $hoursWorked;
            $totalOtPay = (((float)($work['overtimeMinutes'] ?? 0)) / 60) * $hourlyRate;

            $employeeKey = strtolower(trim($employeeName));
            $incomeByEmployee = $incomeMap[$employeeKey] ?? [];
            $additionalIncomeSummary = $additionalMap[$employeeKey] ?? ['taxable' => 0, 'nonTaxable' => 0];
            $legalHoliday = getComputedIncomeAmountByKeyword($incomeByEmployee, '/legal\\s*holiday/i', 2);
            $specialHoliday = getComputedIncomeAmountByKeyword($incomeByEmployee, '/special\\s*holiday/i', 1.3);
            $taxableAdditionalIncome = (float)($additionalIncomeSummary['taxable'] ?? 0);
            $nonTaxableAdditionalIncome = (float)($additionalIncomeSummary['nonTaxable'] ?? 0);
            $nonTaxableTotal = ((float)($nonTaxableIncome[$employeeKey] ?? 0)) + $nonTaxableAdditionalIncome;

            if ($hoursWorked > 0) {
                // Employee salary is bi-monthly. Convert to monthly for mandatory premium formulas,
                // then allocate half to each cutoff payroll.
                $monthlyEquivalentSalary = $monthlySalary * 2;
                $sss = round(computeSss($monthlyEquivalentSalary) / 2, 2);
                $phlth = round(computePhilhealth($monthlyEquivalentSalary) / 2, 2);
                $pagibig = round(computePagibig($monthlyEquivalentSalary) / 2, 2);
                $tax = computeTax($cutoffSalary, $sss, $phlth, $pagibig, $nonTaxableTotal);
            } else {
                $sss = 0;
                $phlth = 0;
                $pagibig = 0;
                $tax = 0;
            }
            $additionalDeductions = $hoursWorked > 0 ? (float)($personalCa[$employeeKey] ?? 0) : 0;
            $totalDeduction = $sss + $phlth + $pagibig + $tax + $additionalDeductions;
            $grossNet = $cutoffSalary + $totalOtPay + $legalHoliday + $specialHoliday + $taxableAdditionalIncome + $nonTaxableAdditionalIncome - $totalDeduction;
            $carryIn = previousCarryOut($dbc, $employeeId, $fromDate);
            $netPay = $grossNet - $carryIn;
            $carryOut = 0;
            if ($netPay < 0) {
                $carryOut = abs($netPay);
                $netPay = 0;
            }

            mysqli_stmt_bind_param(
                $itemStmt,
                'iissdddddddddddddddddd',
                $runId,
                $employeeId,
                $employeeName,
                $employeeEmail,
                $monthlySalary,
                $cutoffSalary,
                $grossPayPerDay,
                $hoursWorked,
                $totalOtPay,
                $legalHoliday,
                $specialHoliday,
                $taxableAdditionalIncome,
                $nonTaxableAdditionalIncome,
                $sss,
                $phlth,
                $pagibig,
                $tax,
                $additionalDeductions,
                $totalDeduction,
                $carryIn,
                $carryOut,
                $netPay
            );

            if (!mysqli_stmt_execute($itemStmt)) {
                throw new RuntimeException('Failed to save payroll item: ' . mysqli_error($dbc));
            }

            $processedRows[] = [
                'id' => $employeeId,
                'name' => $employeeName,
                'email' => $employeeEmail,
                'monthlySalary' => round($monthlySalary, 2),
                'cutoffSalary' => round($cutoffSalary, 2),
                'grossPayPerDay' => round($grossPayPerDay, 2),
                'hoursWorked' => round($hoursWorked, 2),
                'totalOtPay' => round($totalOtPay, 2),
                'legalHoliday' => round($legalHoliday, 2),
                'specialHoliday' => round($specialHoliday, 2),
                'taxableAdditionalIncome' => round($taxableAdditionalIncome, 2),
                'nonTaxableAdditionalIncome' => round($nonTaxableAdditionalIncome, 2),
                'sss' => round($sss, 2),
                'phlth' => round($phlth, 2),
                'pagibig' => round($pagibig, 2),
                'tax' => round($tax, 2),
                'additionalDeductions' => round($additionalDeductions, 2),
                'totalDeduction' => round($totalDeduction, 2),
                'carryIn' => round($carryIn, 2),
                'carryOut' => round($carryOut, 2),
                'netPay' => round($netPay, 2),
                'riceSubsidy' => round($nonTaxableAdditionalIncome, 2),
                'electricity' => round($taxableAdditionalIncome, 2),
                'personalCa' => round($additionalDeductions, 2),
                'processedAt' => date('Y-m-d H:i:s')
            ];
        }

        mysqli_commit($dbc);
        return ['cutoff_key' => $cutoffKey, 'from' => $fromDate, 'to' => $toDate, 'rows' => $processedRows];
    } catch (Throwable $e) {
        mysqli_rollback($dbc);
        throw $e;
    }
}

function getProcessedPayroll($dbc, $fromDate, $toDate) {
    $sql = "SELECT employee_id, employee_name, employee_email, monthly_salary, cutoff_salary, gross_pay_per_day, hours_worked,
                   total_ot_pay, legal_holiday, special_holiday, taxable_additional_income, non_taxable_additional_income,
                   sss, phlth, pagibig, tax, additional_deductions, total_deduction, carry_in, carry_out, net_pay, created_at
            FROM payroll_cutoff_items i
            INNER JOIN payroll_cutoff_runs r ON r.id = i.run_id
            WHERE r.cutoff_from = ? AND r.cutoff_to = ?
            ORDER BY employee_id ASC";
    $stmt = mysqli_prepare($dbc, $sql);
    mysqli_stmt_bind_param($stmt, 'ss', $fromDate, $toDate);
    if (!mysqli_stmt_execute($stmt)) {
        respond(false, null, 'Failed to load processed payroll: ' . mysqli_error($dbc), 500);
    }

    $result = mysqli_stmt_get_result($stmt);
    $rows = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
    $mapped = [];
    foreach ($rows as $row) {
        $mapped[] = [
            'id' => (int)$row['employee_id'],
            'name' => (string)$row['employee_name'],
            'email' => (string)$row['employee_email'],
            'monthlySalary' => (float)$row['monthly_salary'],
            'cutoffSalary' => (float)$row['cutoff_salary'],
            'grossPayPerDay' => (float)$row['gross_pay_per_day'],
            'hoursWorked' => (float)$row['hours_worked'],
            'totalOtPay' => (float)$row['total_ot_pay'],
            'legalHoliday' => (float)$row['legal_holiday'],
            'specialHoliday' => (float)$row['special_holiday'],
            'taxableAdditionalIncome' => (float)$row['taxable_additional_income'],
            'nonTaxableAdditionalIncome' => (float)$row['non_taxable_additional_income'],
            'sss' => (float)$row['sss'],
            'phlth' => (float)$row['phlth'],
            'pagibig' => (float)$row['pagibig'],
            'tax' => (float)$row['tax'],
            'additionalDeductions' => (float)$row['additional_deductions'],
            'totalDeduction' => (float)$row['total_deduction'],
            'carryIn' => (float)$row['carry_in'],
            'carryOut' => (float)$row['carry_out'],
            'netPay' => (float)$row['net_pay'],
            'riceSubsidy' => (float)$row['non_taxable_additional_income'],
            'electricity' => (float)$row['taxable_additional_income'],
            'personalCa' => (float)$row['additional_deductions'],
            'processedAt' => (string)$row['created_at']
        ];
    }

    return $mapped;
}

try {
    if (!$dbc) {
        respond(false, null, 'Database connection failed.', 500);
    }

    ensureTables($dbc);

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $fromDate = parseDateValue($_GET['from'] ?? '');
        $toDate = parseDateValue($_GET['to'] ?? '');
        if (!$fromDate || !$toDate) {
            respond(false, null, 'Invalid cutoff range.', 400);
        }

        $rows = getProcessedPayroll($dbc, $fromDate, $toDate);
        respond(true, ['from' => $fromDate, 'to' => $toDate, 'rows' => $rows]);
    }

    if ($method === 'POST') {
        $payload = json_decode(file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            $payload = $_POST;
        }

        if (($payload['action'] ?? '') === 'update') {
            $fromDate = parseDateValue($payload['from'] ?? '');
            $toDate = parseDateValue($payload['to'] ?? '');
            $employeeId = (int)($payload['employeeId'] ?? 0);

            if (!$fromDate || !$toDate || $employeeId <= 0) {
                respond(false, null, 'Invalid update request.', 400);
            }

            $cutoffSalary = (float)($payload['cutoffSalary'] ?? 0);
            $grossPayPerDay = (float)($payload['grossPayPerDay'] ?? 0);
            $hoursWorked = (float)($payload['hoursWorked'] ?? 0);
            $totalOtPay = (float)($payload['totalOtPay'] ?? 0);
            $legalHoliday = (float)($payload['legalHoliday'] ?? 0);
            $specialHoliday = (float)($payload['specialHoliday'] ?? 0);
            $taxableAdditionalIncome = (float)($payload['taxableAdditionalIncome'] ?? 0);
            $nonTaxableAdditionalIncome = (float)($payload['nonTaxableAdditionalIncome'] ?? 0);
            $sss = (float)($payload['sss'] ?? 0);
            $phlth = (float)($payload['phlth'] ?? 0);
            $pagibig = (float)($payload['pagibig'] ?? 0);
            $tax = (float)($payload['tax'] ?? 0);
            $additionalDeductions = (float)($payload['additionalDeductions'] ?? 0);

            mysqli_begin_transaction($dbc);
            try {
                $runSql = "SELECT id FROM payroll_cutoff_runs WHERE cutoff_from = ? AND cutoff_to = ? LIMIT 1";
                $runStmt = mysqli_prepare($dbc, $runSql);
                if (!$runStmt) {
                    throw new RuntimeException('Failed to prepare payroll run lookup: ' . mysqli_error($dbc));
                }

                mysqli_stmt_bind_param($runStmt, 'ss', $fromDate, $toDate);
                if (!mysqli_stmt_execute($runStmt)) {
                    throw new RuntimeException('Failed to locate payroll run: ' . mysqli_error($dbc));
                }

                $runResult = mysqli_stmt_get_result($runStmt);
                $runRow = $runResult ? mysqli_fetch_assoc($runResult) : null;
                $runId = $runRow ? (int)$runRow['id'] : 0;
                if ($runId <= 0) {
                    throw new RuntimeException('Processed payroll run not found for this cutoff.');
                }

                $existingSql = "SELECT carry_in FROM payroll_cutoff_items WHERE run_id = ? AND employee_id = ? LIMIT 1";
                $existingStmt = mysqli_prepare($dbc, $existingSql);
                if (!$existingStmt) {
                    throw new RuntimeException('Failed to prepare payroll row lookup: ' . mysqli_error($dbc));
                }

                mysqli_stmt_bind_param($existingStmt, 'ii', $runId, $employeeId);
                if (!mysqli_stmt_execute($existingStmt)) {
                    throw new RuntimeException('Failed to load payroll row: ' . mysqli_error($dbc));
                }

                $existingResult = mysqli_stmt_get_result($existingStmt);
                $existingRow = $existingResult ? mysqli_fetch_assoc($existingResult) : null;
                if (!$existingRow) {
                    throw new RuntimeException('Processed payroll row not found.');
                }

                $carryIn = (float)($existingRow['carry_in'] ?? 0);
                $totalDeduction = $sss + $phlth + $pagibig + $tax + $additionalDeductions;
                $grossNet = $cutoffSalary + $totalOtPay + $legalHoliday + $specialHoliday + $taxableAdditionalIncome + $nonTaxableAdditionalIncome - $totalDeduction;
                $netPay = $grossNet - $carryIn;
                $carryOut = 0;
                if ($netPay < 0) {
                    $carryOut = abs($netPay);
                    $netPay = 0;
                }

                $updateSql = "UPDATE payroll_cutoff_items
                              SET cutoff_salary = ?, gross_pay_per_day = ?, hours_worked = ?, total_ot_pay = ?, legal_holiday = ?, special_holiday = ?,
                                  taxable_additional_income = ?, non_taxable_additional_income = ?, sss = ?, phlth = ?, pagibig = ?, tax = ?, additional_deductions = ?,
                                  total_deduction = ?, carry_out = ?, net_pay = ?, created_at = CURRENT_TIMESTAMP
                              WHERE run_id = ? AND employee_id = ?";
                $updateStmt = mysqli_prepare($dbc, $updateSql);
                if (!$updateStmt) {
                    throw new RuntimeException('Failed to prepare payroll row update: ' . mysqli_error($dbc));
                }

                mysqli_stmt_bind_param(
                    $updateStmt,
                    'ddddddddddddddddii',
                    $cutoffSalary,
                    $grossPayPerDay,
                    $hoursWorked,
                    $totalOtPay,
                    $legalHoliday,
                    $specialHoliday,
                    $taxableAdditionalIncome,
                    $nonTaxableAdditionalIncome,
                    $sss,
                    $phlth,
                    $pagibig,
                    $tax,
                    $additionalDeductions,
                    $totalDeduction,
                    $carryOut,
                    $netPay,
                    $runId,
                    $employeeId
                );

                if (!mysqli_stmt_execute($updateStmt)) {
                    throw new RuntimeException('Failed to update payroll row: ' . mysqli_error($dbc));
                }

                mysqli_commit($dbc);
                respond(true, ['updated' => mysqli_stmt_affected_rows($updateStmt)], 'Processed payroll row updated successfully.');
            } catch (Throwable $e) {
                mysqli_rollback($dbc);
                respond(false, null, $e->getMessage(), 500);
            }
        }

        if (($payload['action'] ?? '') === 'delete') {
            $fromDate = parseDateValue($payload['from'] ?? '');
            $toDate = parseDateValue($payload['to'] ?? '');
            $employeeId = (int)($payload['employeeId'] ?? 0);

            if (!$fromDate || !$toDate || $employeeId <= 0) {
                respond(false, null, 'Invalid delete request.', 400);
            }

            mysqli_begin_transaction($dbc);
            try {
                $runSql = "SELECT id FROM payroll_cutoff_runs WHERE cutoff_from = ? AND cutoff_to = ? LIMIT 1";
                $runStmt = mysqli_prepare($dbc, $runSql);
                if (!$runStmt) {
                    throw new RuntimeException('Failed to prepare payroll run lookup: ' . mysqli_error($dbc));
                }

                mysqli_stmt_bind_param($runStmt, 'ss', $fromDate, $toDate);
                if (!mysqli_stmt_execute($runStmt)) {
                    throw new RuntimeException('Failed to locate payroll run: ' . mysqli_error($dbc));
                }

                $runResult = mysqli_stmt_get_result($runStmt);
                $runRow = $runResult ? mysqli_fetch_assoc($runResult) : null;
                $runId = $runRow ? (int)$runRow['id'] : 0;
                if ($runId <= 0) {
                    throw new RuntimeException('Processed payroll run not found for this cutoff.');
                }

                $deleteSql = "DELETE FROM payroll_cutoff_items WHERE run_id = ? AND employee_id = ?";
                $deleteStmt = mysqli_prepare($dbc, $deleteSql);
                if (!$deleteStmt) {
                    throw new RuntimeException('Failed to prepare payroll row delete: ' . mysqli_error($dbc));
                }

                mysqli_stmt_bind_param($deleteStmt, 'ii', $runId, $employeeId);
                if (!mysqli_stmt_execute($deleteStmt)) {
                    throw new RuntimeException('Failed to delete payroll row: ' . mysqli_error($dbc));
                }

                $deletedCount = mysqli_stmt_affected_rows($deleteStmt);
                if ($deletedCount <= 0) {
                    throw new RuntimeException('No processed payroll row was deleted.');
                }

                $remainingResult = mysqli_query($dbc, "SELECT COUNT(*) AS c FROM payroll_cutoff_items WHERE run_id = " . (int)$runId);
                $remainingRow = $remainingResult ? mysqli_fetch_assoc($remainingResult) : null;
                $remainingCount = (int)($remainingRow['c'] ?? 0);

                if ($remainingCount <= 0) {
                    $deleteRunSql = "DELETE FROM payroll_cutoff_runs WHERE id = ?";
                    $deleteRunStmt = mysqli_prepare($dbc, $deleteRunSql);
                    if (!$deleteRunStmt) {
                        throw new RuntimeException('Failed to prepare payroll run cleanup: ' . mysqli_error($dbc));
                    }

                    mysqli_stmt_bind_param($deleteRunStmt, 'i', $runId);
                    if (!mysqli_stmt_execute($deleteRunStmt)) {
                        throw new RuntimeException('Failed to delete empty payroll run: ' . mysqli_error($dbc));
                    }
                }

                mysqli_commit($dbc);
                respond(true, ['deleted' => $deletedCount], 'Processed payroll row deleted successfully.');
            } catch (Throwable $e) {
                mysqli_rollback($dbc);
                respond(false, null, $e->getMessage(), 500);
            }
        }

        $fromDate = parseDateValue($payload['from'] ?? '');
        $toDate = parseDateValue($payload['to'] ?? '');
        if (!$fromDate || !$toDate) {
            respond(false, null, 'Invalid cutoff range.', 400);
        }

        $result = processPayroll($dbc, $fromDate, $toDate);
        respond(true, $result, 'Cutoff payroll processed successfully.');
    }

    respond(false, null, 'Method not allowed.', 405);
} catch (Throwable $e) {
    respond(false, null, 'Server error: ' . $e->getMessage(), 500);
}
