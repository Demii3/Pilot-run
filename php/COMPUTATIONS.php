<?php
// Connect to the database (example connection, adjust credentials as needed)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "employee_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch employee data (example query, adjust table/column names as needed)
$employeeId = 'E12345'; // Example static ID, replace with dynamic input if needed
$sql = "SELECT * FROM employees WHERE employee_id = '$employeeId'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $totalHours = $row['total_hours'];
    $ratePerHour = $row['rate_per_hour'];
    $specialHoliday = $row['special_holiday'];
    $legalHoliday = $row['legal_holiday'];
    $overtimeRate = $row['overtime_rate'];
    $late = $row['late'];
    $absent = $row['absent'];
    $cashAdvance = $row['cash_advance'];
    $sss = $row['sss'];
    $philHealth = $row['philhealth'];
    $pagIbig = $row['pagibig'];
    $tax = $row['tax'];
} else {
    die("No employee data found.");
}

// Calculate totals
$totalOtherIncome = $specialHoliday + $legalHoliday + $overtimeRate;
$totalOtherDeductions = $late + $absent + $cashAdvance;
$totalMonthlySalary = $totalHours * $ratePerHour;
$totalPremiums = $sss + $philHealth + $pagIbig + $tax;
$netPay = $totalOtherIncome + $totalMonthlySalary - ($totalOtherDeductions + $totalPremiums);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Net Pay Viewer</title>
    <link rel="stylesheet" href="computation_css.css">
</head>
<body>
    <div class="container">
        <h2>Employee Net Pay Viewer</h2>

        <div class="employee-details">
            <span>Employee ID:</span>
            <span class="value" id="employeeId"><?php echo $employeeId; ?></span>
        </div>

        <div class="category">
            <h3>Monthly Salary:</h3>
            <div class="item">
                <span>Total Hours:</span>
                <span class="value" id="totalHours"><?php echo $totalHours; ?></span>
            </div>
            <div class="item">
                <span>Rate Per Hour:</span>
                <span class="value" id="ratePerHour"><?php echo number_format($ratePerHour, 2); ?></span>
            </div>
            <div class="item">
                <strong>Total Monthly Salary:</strong>
                <strong class="value" id="totalMonthlySalary"><?php echo number_format($totalMonthlySalary, 2); ?></strong>
            </div>
        </div>

        <div class="category">
            <h3>Other Income:</h3>
            <div class="item">
                <span>Special Holiday:</span>
                <span class="value" id="specialHoliday"><?php echo number_format($specialHoliday, 2); ?></span>
            </div>
            <div class="item">
                <span>Legal Holiday:</span>
                <span class="value" id="legalHoliday"><?php echo number_format($legalHoliday, 2); ?></span>
            </div>
            <div class="item">
                <span>Overtime Rate:</span>
                <span class="value" id="overtimeRate"><?php echo number_format($overtimeRate, 2); ?></span>
            </div>
            <div class="item">
                <strong>Total Other Income:</strong>
                <strong class="value" id="totalOtherIncome"><?php echo number_format($totalOtherIncome, 2); ?></strong>
            </div>
        </div>

        <div class="category deduction">
            <h3>Other Deductions:</h3>
            <div class="item">
                <span>Late:</span>
                <span class="value" id="late"><?php echo number_format($late, 2); ?></span>
            </div>
            <div class="item">
                <span>Absent:</span>
                <span class="value" id="absent"><?php echo number_format($absent, 2); ?></span>
            </div>
            <div class="item">
                <span>Cash Advance:</span>
                <span class="value" id="cashAdvance"><?php echo number_format($cashAdvance, 2); ?></span>
            </div>
            <div class="item">
                <strong>Total Other Deductions:</strong>
                <strong class="value" id="totalOtherDeductions"><?php echo number_format($totalOtherDeductions, 2); ?></strong>
            </div>
        </div>

        <div class="category deduction">
            <h3>Premiums:</h3>
            <div class="item">
                <span>SSS:</span>
                <span class="value" id="sss"><?php echo number_format($sss, 2); ?></span>
            </div>
            <div class="item">
                <span>PhilHealth:</span>
                <span class="value" id="philHealth"><?php echo number_format($philHealth, 2); ?></span>
            </div>
            <div class="item">
                <span>PagIbig:</span>
                <span class="value" id="pagIbig"><?php echo number_format($pagIbig, 2); ?></span>
            </div>
            <div class="item">
                <span>Tax:</span>
                <span class="value" id="tax"><?php echo number_format($tax, 2); ?></span>
            </div>
            <div class="item">
                <strong>Total Premiums:</strong>
                <strong class="value" id="totalPremiums"><?php echo number_format($totalPremiums, 2); ?></strong>
            </div>
        </div>

        <div class="result" id="result">
            <p><strong>Net Pay:</strong> <span id="netPay"><?php echo number_format($netPay, 2); ?></span></p>
        </div>
    </div>
</body>
</html>