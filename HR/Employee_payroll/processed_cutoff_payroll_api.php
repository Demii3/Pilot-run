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

    $ts = strtotime($text);
    if ($ts === false) {
        return null;
    }

    return date('Y-m-d', $ts);
}

function ensureProcessedCutoffTable($dbc) {
    $sql = "CREATE TABLE IF NOT EXISTS `processed_cutoff_payroll` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `cutoff_key` VARCHAR(255) NOT NULL,
      `cutoff_from` DATE NOT NULL,
      `cutoff_to` DATE NOT NULL,
      `employee_id` INT UNSIGNED NOT NULL,
      `employee_name` VARCHAR(255) NOT NULL,
      `email` VARCHAR(255) DEFAULT '',
      `monthly_salary` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `cutoff_salary` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `gross_pay_per_day` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `hours_worked` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
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
      `processed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `uniq_cutoff_employee` (`cutoff_from`, `cutoff_to`, `employee_id`),
      KEY `idx_employee_cutoff` (`employee_id`, `cutoff_from`, `cutoff_to`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    mysqli_query($dbc, $sql);
    if (mysqli_errno($dbc)) {
        respond(false, null, 'Database setup failed: ' . mysqli_error($dbc), 500);
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

function fetchEmployees($dbc) {
    $result = mysqli_query($dbc, "SELECT id, name, email, salary FROM employees ORDER BY id ASC");
    if (!$result) {
        respond(false, null, 'Failed to fetch employees: ' . mysqli_error($dbc), 500);
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function fetchAttendanceRows($dbc, $dateFrom, $dateTo) {
    $sql = "SELECT Emp_id, Date, Clock_in, Clock_out, Duration, AO
            FROM employee_attendance
            WHERE Date >= ? AND Date <= ?
            ORDER BY Date ASC";

    $stmt = mysqli_prepare($dbc, $sql);
    mysqli_stmt_bind_param($stmt, 'ss', $dateFrom, $dateTo);

    if (!mysqli_stmt_execute($stmt)) {
        respond(false, null, 'Failed to fetch attendance: ' . mysqli_error($dbc), 500);
    }

    $result = mysqli_stmt_get_result($stmt);
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

function fetchAssignedIncome($dbc) {
    $result = mysqli_query($dbc, "SELECT name, type_of_income, cost, taxable, recurring, apply_year_month, apply_cutoff_slot FROM assigned_emp_inc");
    if (!$result) {
        respond(false, null, 'Failed to fetch assigned income: ' . mysqli_error($dbc), 500);
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function fetchIncomeTypes($dbc) {
    $result = mysqli_query($dbc, "SELECT type_of_income, recurring FROM emp_inc_type");
    if (!$result) {
        return [];
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function fetchAssignedDeductions($dbc) {
    $result = mysqli_query($dbc, "SELECT name, type_of_deduction, cost, recurring, apply_year_month, apply_cutoff_slot FROM assigned_emp_deduc");
    if (!$result) {
        respond(false, null, 'Failed to fetch assigned deductions: ' . mysqli_error($dbc), 500);
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function fetchDeductionTypes($dbc) {
    $result = mysqli_query($dbc, "SELECT type_of_deduction, recurring FROM emp_deduc_type");
    if (!$result) {
        return [];
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function buildAttendanceWorkMap($attendanceRows) {
    $map = [];

    foreach ($attendanceRows as $item) {
        $employeeId = trim((string)($item['Emp_id'] ?? ''));
        if ($employeeId === '') {
            continue;
        }

        $directDuration = isset($item['Duration']) ? (float)$item['Duration'] : 0;
        $durationMinutes = $directDuration > 0 ? $directDuration : 0;

        if ($durationMinutes <= 0 && !empty($item['Clock_in']) && !empty($item['Clock_out'])) {
            $clockInTs = strtotime((string)$item['Clock_in']);
            $clockOutTs = strtotime((string)$item['Clock_out']);
            if ($clockInTs !== false && $clockOutTs !== false) {
                $durationMinutes = ($clockOutTs - $clockInTs) / 60;
                if ($durationMinutes < 0) {
                    $durationMinutes += 1440;
                }
            }
        }

        if (!is_finite($durationMinutes) || $durationMinutes <= 0) {
            $durationMinutes = 0;
        }

        $isAo = ((int)($item['AO'] ?? 0)) === 1;
        $regularMinutes = max(0, min($durationMinutes, 480));
        $overtimeMinutes = $durationMinutes > 480 && $isAo ? ($durationMinutes - 480) : 0;

        if (!isset($map[$employeeId])) {
            $map[$employeeId] = ['regularMinutes' => 0, 'overtimeMinutes' => 0];
        }

        $map[$employeeId]['regularMinutes'] += $regularMinutes;
        $map[$employeeId]['overtimeMinutes'] += $overtimeMinutes;
    }

    return $map;
}

function getCutoffContext($dateFrom, $dateTo) {
    $reference = $dateFrom ?: $dateTo;
    if (!$reference) {
        $reference = date('Y-m-d');
    }

    $ts = strtotime($reference);
    $yearMonth = date('Y-m', $ts);
    $cutoffSlot = ((int)date('j', $ts) <= 15) ? 1 : 2;

    return ['yearMonth' => $yearMonth, 'cutoffSlot' => $cutoffSlot];
}

function filterAssignedIncomeByCutoff($assignedIncome, $incomeTypes, $dateFrom, $dateTo) {
    $recurringMap = [];
    foreach ($incomeTypes as $typeItem) {
        $key = strtolower(trim((string)($typeItem['type_of_income'] ?? '')));
        if ($key === '') continue;
        $recurringMap[$key] = ((int)($typeItem['recurring'] ?? 0) === 1) ? 1 : 0;
    }

    $context = getCutoffContext($dateFrom, $dateTo);
    $result = [];

    foreach ($assignedIncome as $item) {
        $typeKey = strtolower(trim((string)($item['type_of_income'] ?? '')));
        $isRecurring = ((int)($item['recurring'] ?? 0) === 1) || (($recurringMap[$typeKey] ?? 0) === 1);

        if ($isRecurring) {
            $result[] = $item;
            continue;
        }

        $applyYearMonth = trim((string)($item['apply_year_month'] ?? ''));
        $applyCutoffSlot = (int)($item['apply_cutoff_slot'] ?? 0);

        if ($applyYearMonth === '' || $applyCutoffSlot === 0) {
            $result[] = $item;
            continue;
        }

        if ($applyYearMonth === $context['yearMonth'] && $applyCutoffSlot === $context['cutoffSlot']) {
            $result[] = $item;
        }
    }

    return $result;
}

function filterAssignedDeductionsByCutoff($assignedDeductions, $deductionTypes, $dateFrom, $dateTo) {
    $recurringMap = [];
    foreach ($deductionTypes as $typeItem) {
        $key = strtolower(trim((string)($typeItem['type_of_deduction'] ?? '')));
        if ($key === '') continue;
        $recurringMap[$key] = ((int)($typeItem['recurring'] ?? 0) === 1) ? 1 : 0;
    }

    $context = getCutoffContext($dateFrom, $dateTo);
    $result = [];

    foreach ($assignedDeductions as $item) {
        $typeKey = strtolower(trim((string)($item['type_of_deduction'] ?? '')));
        $isRecurring = ((int)($item['recurring'] ?? 0) === 1) || (($recurringMap[$typeKey] ?? 0) === 1);

        if ($isRecurring) {
            $result[] = $item;
            continue;
        }

        $applyYearMonth = trim((string)($item['apply_year_month'] ?? ''));
        $applyCutoffSlot = (int)($item['apply_cutoff_slot'] ?? 0);

        if ($applyYearMonth === '' || $applyCutoffSlot === 0) {
            $result[] = $item;
            continue;
        }

        if ($applyYearMonth === $context['yearMonth'] && $applyCutoffSlot === $context['cutoffSlot']) {
            $result[] = $item;
        }
    }

    return $result;
}

function buildIncomeByTypeMap($assignedIncome) {
    $map = [];
    foreach ($assignedIncome as $item) {
        $employeeKey = strtolower(trim((string)($item['name'] ?? '')));
        $incomeType = strtolower(trim((string)($item['type_of_income'] ?? '')));
        $cost = (float)($item['cost'] ?? 0);

        if ($employeeKey === '' || $incomeType === '') continue;

        if (!isset($map[$employeeKey])) {
            $map[$employeeKey] = [];
        }

        $map[$employeeKey][$incomeType] = ($map[$employeeKey][$incomeType] ?? 0) + $cost;
    }

    return $map;
}

function buildAdditionalIncomeSummaryMap($assignedIncome) {
    $map = [];

    foreach ($assignedIncome as $item) {
        $employeeKey = strtolower(trim((string)($item['name'] ?? '')));
        $incomeType = strtolower(trim((string)($item['type_of_income'] ?? '')));
        $cost = (float)($item['cost'] ?? 0);
        $taxable = (int)($item['taxable'] ?? 0) === 1;

        if ($employeeKey === '' || $incomeType === '') continue;

        if (preg_match('/legal\s*holiday/i', $incomeType) || preg_match('/special\s*holiday/i', $incomeType)) {
            continue;
        }

        if (!isset($map[$employeeKey])) {
            $map[$employeeKey] = ['taxable' => 0, 'nonTaxable' => 0];
        }

        if ($taxable) {
            $map[$employeeKey]['taxable'] += $cost;
        } else {
            $map[$employeeKey]['nonTaxable'] += $cost;
        }
    }

    return $map;
}

function buildNonTaxableIncomeMap($assignedIncome) {
    $map = [];

    foreach ($assignedIncome as $item) {
        $employeeKey = strtolower(trim((string)($item['name'] ?? '')));
        $isNonTaxable = (int)($item['taxable'] ?? 0) === 0;
        $cost = (float)($item['cost'] ?? 0);

        if ($employeeKey === '' || !$isNonTaxable) continue;

        $map[$employeeKey] = ($map[$employeeKey] ?? 0) + $cost;
    }

    return $map;
}

function buildPersonalCaMap($assignedDeductions) {
    $map = [];

    foreach ($assignedDeductions as $item) {
        $employeeKey = strtolower(trim((string)($item['name'] ?? '')));
        $cost = (float)($item['cost'] ?? 0);

        if ($employeeKey === '') continue;

        $map[$employeeKey] = ($map[$employeeKey] ?? 0) + $cost;
    }

    return $map;
}

function getComputedIncomeAmountByKeyword($incomeByType, $pattern, $multiplier) {
    $total = 0;
    foreach ($incomeByType as $typeName => $amount) {
        if (preg_match($pattern, $typeName)) {
            $total += (float)$amount;
        }
    }

    return round($total * (float)$multiplier, 2);
}

function computeSssContribution($salary) {
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

function computePagibigContribution($salary) {
    $salary = (float)$salary;
    if ($salary <= 0) return 0;

    // Pag-IBIG total monthly contribution: 2% (1% employee + 1% employer), capped at 200.
    return round(min($salary * 0.02, 200), 2);
}

function computePhilhealthContribution($salary) {
    $salary = (float)$salary;
    if ($salary <= 0) return 0;

    // PhilHealth monthly premium: 5% with salary floor 10,000 and ceiling 100,000.
    $premiumBasis = min(max($salary, 10000), 100000);
    return round($premiumBasis * 0.05, 2);
}

function computeWithholdingTax($salary, $sss, $philhealth, $pagibig, $nonTaxable) {
    $salary = (float)$salary;
    if ($salary <= 0) return 0;

    $taxableIncome = max(0, $salary - (float)$nonTaxable - (float)$sss - (float)$philhealth - (float)$pagibig);

    if ($taxableIncome <= 20833) return 0;
    if ($taxableIncome <= 33333) return ($taxableIncome - 20833) * 0.15;
    if ($taxableIncome <= 66667) return 1875 + ($taxableIncome - 33333) * 0.20;
    if ($taxableIncome <= 166667) return 8541.8 + ($taxableIncome - 66667) * 0.25;
    if ($taxableIncome <= 666667) return 33541.8 + ($taxableIncome - 166667) * 0.30;
    return 183541.8 + ($taxableIncome - 666667) * 0.35;
}

function computePremiumDeductions($monthlySalary, $hoursWorked, $nonTaxableIncome) {
    $cutoffBaseSalary = (float)$monthlySalary;

    $dailyRate = $cutoffBaseSalary / 26;
    $hourlyRate = $dailyRate / 8;
    $cutoffSalary = $hourlyRate * (float)$hoursWorked;

    if ($cutoffSalary <= 0) {
        return [
            'sss' => 0,
            'phlth' => 0,
            'pagibig' => 0,
            'tax' => 0
        ];
    }

    // Employee salary records are bi-monthly. Remittance formulas are monthly,
    // so compute monthly mandatory premiums then split equally per cutoff.
    $monthlyEquivalentSalary = $cutoffBaseSalary * 2;
    $sssMonthly = computeSssContribution($monthlyEquivalentSalary);
    $phlthMonthly = computePhilhealthContribution($monthlyEquivalentSalary);
    $pagibigMonthly = computePagibigContribution($monthlyEquivalentSalary);

    $sss = round($sssMonthly / 2, 2);
    $phlth = round($phlthMonthly / 2, 2);
    $pagibig = round($pagibigMonthly / 2, 2);

    // Keep withholding tax computed on cutoff earnings.
    $tax = computeWithholdingTax($cutoffSalary, $sss, $phlth, $pagibig, $nonTaxableIncome);

    return [
        'sss' => $sss,
        'phlth' => $phlth,
        'pagibig' => $pagibig,
        'tax' => $tax
    ];
}

function getPreviousCarryOut($dbc, $employeeId, $dateFrom) {
    $sql = "SELECT carry_out
            FROM processed_cutoff_payroll
            WHERE employee_id = ? AND cutoff_to < ?
            ORDER BY cutoff_to DESC
            LIMIT 1";
    $stmt = mysqli_prepare($dbc, $sql);
    mysqli_stmt_bind_param($stmt, 'is', $employeeId, $dateFrom);

    if (!mysqli_stmt_execute($stmt)) {
        return 0;
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        return 0;
    }

    $row = mysqli_fetch_assoc($result);
    return $row ? (float)$row['carry_out'] : 0;
}

function processCutoffPayroll($dbc, $dateFrom, $dateTo) {
    $employees = fetchEmployees($dbc);
    $attendanceRows = fetchAttendanceRows($dbc, $dateFrom, $dateTo);
    $assignedIncome = fetchAssignedIncome($dbc);
    $incomeTypes = fetchIncomeTypes($dbc);
    $assignedDeductions = fetchAssignedDeductions($dbc);
    $deductionTypes = fetchDeductionTypes($dbc);

    $applicableIncome = filterAssignedIncomeByCutoff($assignedIncome, $incomeTypes, $dateFrom, $dateTo);
    $applicableDeductions = filterAssignedDeductionsByCutoff($assignedDeductions, $deductionTypes, $dateFrom, $dateTo);

    $attendanceByEmployee = buildAttendanceWorkMap($attendanceRows);
    $incomeByType = buildIncomeByTypeMap($applicableIncome);
    $additionalIncomeSummary = buildAdditionalIncomeSummaryMap($applicableIncome);
    $nonTaxableIncomeMap = buildNonTaxableIncomeMap($applicableIncome);
    $personalCaMap = buildPersonalCaMap($applicableDeductions);

    $cutoffKey = $dateFrom . '_' . $dateTo;
    $rows = [];

    mysqli_begin_transaction($dbc);

    try {
        $insertSql = "INSERT INTO processed_cutoff_payroll
            (cutoff_key, cutoff_from, cutoff_to, employee_id, employee_name, email, monthly_salary, cutoff_salary, gross_pay_per_day, hours_worked,
             total_ot_pay, legal_holiday, special_holiday, taxable_additional_income, non_taxable_additional_income,
             sss, phlth, pagibig, tax, additional_deductions, total_deduction, carry_in, carry_out, net_pay)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
              cutoff_key = VALUES(cutoff_key),
              employee_name = VALUES(employee_name),
              email = VALUES(email),
              monthly_salary = VALUES(monthly_salary),
              cutoff_salary = VALUES(cutoff_salary),
              gross_pay_per_day = VALUES(gross_pay_per_day),
              hours_worked = VALUES(hours_worked),
              total_ot_pay = VALUES(total_ot_pay),
              legal_holiday = VALUES(legal_holiday),
              special_holiday = VALUES(special_holiday),
              taxable_additional_income = VALUES(taxable_additional_income),
              non_taxable_additional_income = VALUES(non_taxable_additional_income),
              sss = VALUES(sss),
              phlth = VALUES(phlth),
              pagibig = VALUES(pagibig),
              tax = VALUES(tax),
              additional_deductions = VALUES(additional_deductions),
              total_deduction = VALUES(total_deduction),
              carry_in = VALUES(carry_in),
              carry_out = VALUES(carry_out),
              net_pay = VALUES(net_pay),
              processed_at = CURRENT_TIMESTAMP";

        $stmt = mysqli_prepare($dbc, $insertSql);
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare save statement: ' . mysqli_error($dbc));
        }

        foreach ($employees as $emp) {
            $employeeId = (int)($emp['id'] ?? 0);
            $employeeName = trim((string)($emp['name'] ?? ''));
            $employeeNameKey = strtolower($employeeName);
            $email = trim((string)($emp['email'] ?? ''));
            $monthlySalary = (float)($emp['salary'] ?? 0);

            $grossPayPerDay = $monthlySalary / 26;
            $hourlyRate = $grossPayPerDay / 8;

            $work = $attendanceByEmployee[(string)$employeeId] ?? ['regularMinutes' => 0, 'overtimeMinutes' => 0];
            $hoursWorked = ($work['regularMinutes'] ?? 0) / 60;
            $cutoffSalary = $hourlyRate * $hoursWorked;
            $totalOtPay = (($work['overtimeMinutes'] ?? 0) / 60) * $hourlyRate;

            $employeeIncomeByType = $incomeByType[$employeeNameKey] ?? [];
            $incomeSummary = $additionalIncomeSummary[$employeeNameKey] ?? ['taxable' => 0, 'nonTaxable' => 0];

            $legalHoliday = getComputedIncomeAmountByKeyword($employeeIncomeByType, '/legal\\s*holiday/i', 2);
            $specialHoliday = getComputedIncomeAmountByKeyword($employeeIncomeByType, '/special\\s*holiday/i', 1.3);
            $taxableAdditionalIncome = (float)($incomeSummary['taxable'] ?? 0);
            $nonTaxableAdditionalIncome = (float)($incomeSummary['nonTaxable'] ?? 0);
            $nonTaxableIncome = (float)($nonTaxableIncomeMap[$employeeNameKey] ?? 0) + $nonTaxableAdditionalIncome;

            $hasWorkedHours = $hoursWorked > 0;
            $premium = $hasWorkedHours ? computePremiumDeductions($monthlySalary, $hoursWorked, $nonTaxableIncome) : ['sss' => 0, 'phlth' => 0, 'pagibig' => 0, 'tax' => 0];
            $sss = (float)$premium['sss'];
            $phlth = (float)$premium['phlth'];
            $pagibig = (float)$premium['pagibig'];
            $tax = (float)$premium['tax'];

            $additionalDeductions = $hasWorkedHours ? (float)($personalCaMap[$employeeNameKey] ?? 0) : 0;
            $totalDeduction = $sss + $phlth + $pagibig + $tax + $additionalDeductions;
            $grossNet = $cutoffSalary + $totalOtPay + $legalHoliday + $specialHoliday + $taxableAdditionalIncome + $nonTaxableAdditionalIncome - $totalDeduction;

            $carryIn = getPreviousCarryOut($dbc, $employeeId, $dateFrom);
            $displayedNet = $grossNet - $carryIn;
            $carryOut = 0;
            if ($displayedNet < 0) {
                $carryOut = -$displayedNet;
                $displayedNet = 0;
            }

            mysqli_stmt_bind_param(
                $stmt,
                'sssissddddddddddddddddd',
                $cutoffKey,
                $dateFrom,
                $dateTo,
                $employeeId,
                $employeeName,
                $email,
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
                $displayedNet
            );

            if (!mysqli_stmt_execute($stmt)) {
                throw new RuntimeException('Failed to save processed payroll: ' . mysqli_error($dbc));
            }

            $rows[] = [
                'id' => $employeeId,
                'name' => $employeeName,
                'email' => $email,
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
                'netPay' => round($displayedNet, 2),
                'riceSubsidy' => round($nonTaxableAdditionalIncome, 2),
                'electricity' => round($taxableAdditionalIncome, 2),
                'personalCa' => round($additionalDeductions, 2)
            ];
        }

        mysqli_commit($dbc);
        return $rows;
    } catch (Throwable $e) {
        mysqli_rollback($dbc);
        throw $e;
    }
}

function getProcessedRowsByCutoff($dbc, $dateFrom, $dateTo) {
    $sql = "SELECT
              employee_id,
              employee_name,
              email,
              monthly_salary,
              cutoff_salary,
              gross_pay_per_day,
              hours_worked,
              total_ot_pay,
              legal_holiday,
              special_holiday,
              taxable_additional_income,
              non_taxable_additional_income,
              sss,
              phlth,
              pagibig,
              tax,
              additional_deductions,
              total_deduction,
              carry_in,
              carry_out,
              net_pay,
              processed_at
            FROM processed_cutoff_payroll
            WHERE cutoff_from = ? AND cutoff_to = ?
            ORDER BY employee_id ASC";

    $stmt = mysqli_prepare($dbc, $sql);
    mysqli_stmt_bind_param($stmt, 'ss', $dateFrom, $dateTo);

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
            'email' => (string)$row['email'],
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
            'processedAt' => (string)$row['processed_at']
        ];
    }

    return $mapped;
}

try {
    if (!$dbc) {
        respond(false, null, 'Database connection failed.', 500);
    }

    ensureProcessedCutoffTable($dbc);

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $dateFrom = parseDateValue($_GET['from'] ?? '');
        $dateTo = parseDateValue($_GET['to'] ?? '');

        if (!$dateFrom || !$dateTo) {
            respond(false, null, 'Invalid cutoff range. Provide from and to dates.', 400);
        }

        $rows = getProcessedRowsByCutoff($dbc, $dateFrom, $dateTo);
        respond(true, [
            'from' => $dateFrom,
            'to' => $dateTo,
            'rows' => $rows
        ]);
    }

    if ($method === 'POST') {
        $payload = json_decode(file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            $payload = $_POST;
        }

        $dateFrom = parseDateValue($payload['from'] ?? '');
        $dateTo = parseDateValue($payload['to'] ?? '');

        if (!$dateFrom || !$dateTo) {
            respond(false, null, 'Invalid cutoff range. Provide from and to dates.', 400);
        }

        $rows = processCutoffPayroll($dbc, $dateFrom, $dateTo);
        respond(true, [
            'from' => $dateFrom,
            'to' => $dateTo,
            'rows' => $rows
        ], 'Cutoff payroll processed successfully.');
    }

    respond(false, null, 'Method not allowed.', 405);
} catch (Throwable $e) {
    respond(false, null, 'Server error: ' . $e->getMessage(), 500);
}
