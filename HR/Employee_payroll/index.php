<?php
  session_start();
  if (!isset($_SESSION['login']) || $_SESSION['empType'] != "HR") {
    header("location: ../../");
    exit();
  }
  
  // Prevent caching to avoid showing logged-in content on back button
  header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
  header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private');
  header('Cache-Control: post-check=0, pre-check=0', FALSE);
  header('Pragma: no-cache');
  header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
  
  $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . '/Pilot-run';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/2.3.7/css/dataTables.dataTables.min.css">
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
  <script src="https://cdn.datatables.net/2.3.7/js/dataTables.min.js"></script>

  <!-- CSS -->
  <link rel="stylesheet" href="payslip_module.css">

  <link rel="icon" type="image/png" href="../../Images/logo.jpg"/>
  <script src="../../Modules/universal_logout_handler.js"></script>
</head>

<body>

<!-- Navigation Bar -->
<nav class="custom-navbar">

  <div class="nav-left">
    <a class="logo-circle" href="../index.php" aria-label="Go to Home">
      <img src="<?php echo $baseUrl; ?>/Images/logo.jpg" alt="Logo">
    </a>
    <span class="company-name">Chengshi <br>Construction Corp</span>
  </div>

  <div class="nav-right">
    <button class="avatar" onclick="toggleMenu()">
      <img src="<?php echo $baseUrl; ?>/Images/profilepic.jpg" alt="User">
    </button>

    <div id="profileMenu" class="dropdown-menu">

      <div class="profile-header">
        <img src="<?php echo $baseUrl; ?>/Images/profilepic.jpg">
        <span>User</span>
      </div>

      <a href="#" class="profile-item"> Settings & Privacy </a>
      <a href="#" class="profile-item"> Help & Support </a>
      <a href="<?php echo $baseUrl; ?>/Modules/logout_process.php" class="profile-item" onclick="return handleLogout(event);"> Logout </a>

    </div>

  </div>
</nav>


<div class="payroll-container">

  <!-- Sidebar -->
  <div class="sidebar">
    <h2>Payroll</h2>

    <button class="dropdown-btn">Payroll
      <i class="fa fa-caret-down"></i>
    </button>
    <div class="dropdown-container">
      <hr>
      <button onclick="showContent(this,'income')">Manage Incomes</button>
      <button onclick="showContent(this,'deductions')">Manage Deductions</button>
      <button onclick="showContent(this,'employee_income')">Manage Employee Income</button>
      <button onclick="showContent(this,'employee_deductions')">Manage Employee Deductions</button>
      <button onclick="showContent(this,'attendance')">Attendance</button> 
      <hr>
      <button class="dropdown-btn" onclick="showContent(this,'premiums')">Premiums
        <i class="fa fa-caret-down"></i>
      </button>
      <div class="dropdown-container">
        <hr>
        <button onclick="showContent(this,'tax_table')">Tax Table</button>
        <button onclick="showContent(this,'sss_table')">SSS table</button>
        <button onclick="showContent(this,'philhealth_table')">PhilHealth table</button>
        <button onclick="showContent(this,'pagibig_table')">Pag-IBIG table</button>
        <hr>
      </div>
      <button onclick="showContent(this,'employee_salary')">Employee Salary</button>
      <button onclick="showContent(this,'process_13')">Process 13th Month</button>
      <hr>
    </div>

    <button class="dropdown-btn">Reports
      <i class="fa fa-caret-down"></i>
    </button>
    <div class="dropdown-container">
      <hr>
      <button onclick="showContent(this,'payslip')">Payslip</button>
      <button onclick="showContent(this,'13_month_listing')">13th Month Listing</button>
      <hr>
      <button class="dropdown-btn">Remittances
        <i class="fa fa-caret-down"></i>
      </button>
      <div class="dropdown-container">
        <hr>
        <button onclick="showContent(this,'tax')">Tax</button>
        <button onclick="showContent(this,'sss')">SSS</button>
        <button onclick="showContent(this,'philhealth')">PhilHealth</button>
        <button onclick="showContent(this,'pagibig')">Pag-IBIG</button>
        <hr>
      </div>
    </div>
  </div>

  <!-- Content -->
  <div class="content" id="content-area">
    <div class="card">
      <h2>Payroll Module</h2>
      <p>Select a function from the left sidebar.</p>
 
    </div>
  </div>

</div>

<!-- Income Type Modal -->
<div class="modal fade" id="incomeTypeModal" tabindex="-1" aria-labelledby="incomeTypeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="incomeTypeModalLabel">Add Income Type</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="incomeTypeForm">
          <input type="hidden" id="incomeTypeId">
          <input type="hidden" id="incomeTypeCost" value="0">
          <div class="mb-3">
            <label for="incomeTypeName" class="form-label">Type of Income</label>
            <input type="text" id="incomeTypeName" class="form-control" required>
          </div>
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="incomeTypeTaxable" checked>
              <label class="form-check-label" for="incomeTypeTaxable">Taxable</label>
            </div>
          </div>
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="incomeType13th" checked>
              <label class="form-check-label" for="incomeType13th">Included in 13th Month</label>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="saveIncomeType()">Save</button>
      </div>
    </div>
  </div>
</div>

<!-- Deduction Type Modal -->
<div class="modal fade" id="deductionTypeModal" tabindex="-1" aria-labelledby="deductionTypeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deductionTypeModalLabel">Add Deduction Type</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="deductionTypeForm">
          <input type="hidden" id="deductionTypeId">
          <div class="mb-3">
            <label for="deductionTypeName" class="form-label">Type of Deduction</label>
            <input type="text" id="deductionTypeName" class="form-control" required>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="saveDeductionType()">Save</button>
      </div>
    </div>
  </div>
</div>

<!-- Employee Deduction Modal -->
<div class="modal fade" id="employeeDeductionModal" tabindex="-1" aria-labelledby="employeeDeductionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="employeeDeductionModalLabel">Add Deduction to Employee</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="employeeDeductionForm">
          <input type="hidden" id="employeeDeductionAssignmentId">
          <div class="row g-3">
            <div class="col-md-4">
              <label for="deductionEmployeeSelect" class="form-label">Employee</label>
              <select id="deductionEmployeeSelect" class="form-select" required></select>
            </div>
            <div class="col-md-4">
              <label for="deductionTypeSelect" class="form-label">Deduction Type</label>
              <select id="deductionTypeSelect" class="form-select" onchange="updateSelectedDeductionDetails()" required></select>
            </div>
            <div class="col-md-2">
              <label for="selectedDeductionCost" class="form-label">Cost</label>
              <input type="number" id="selectedDeductionCost" class="form-control" min="0" step="0.01" placeholder="0.00" required>
            </div>
            <div class="col-md-2">
              <label class="form-label">Apply Mode</label>
              <div class="form-check income-recurring-check">
                <input class="form-check-input" type="checkbox" id="selectedDeductionRecurring" onchange="toggleDeductionCutoffFieldsByRecurring()">
                <label class="form-check-label" for="selectedDeductionRecurring">Recurring</label>
              </div>
            </div>
            <div class="col-md-4">
              <label for="selectedDeductionApplyMonth" class="form-label">Apply Month (for non-recurring)</label>
              <input type="month" id="selectedDeductionApplyMonth" class="form-control">
            </div>
            <div class="col-md-3">
              <label for="selectedDeductionApplyCutoff" class="form-label">Cutoff</label>
              <select id="selectedDeductionApplyCutoff" class="form-select">
                <option value="">Select cutoff</option>
                <option value="1">1st cutoff</option>
                <option value="2">2nd cutoff</option>
              </select>
            </div>
          </div>
          <div class="form-text mt-2">
            Non-recurring deduction can be limited to a specific month and cutoff. Recurring deduction applies every cutoff automatically.
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="saveEmployeeDeductionAssignment()">Save Assignment</button>
      </div>
    </div>
  </div>
</div>

<!-- Employee Income Modal -->
<div class="modal fade" id="employeeIncomeModal" tabindex="-1" aria-labelledby="employeeIncomeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="employeeIncomeModalLabel">Add Income to Employee</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="employeeIncomeForm">
          <input type="hidden" id="employeeIncomeAssignmentId">
          <div class="row g-3">
            <div class="col-md-4">
              <label for="incomeEmployeeSelect" class="form-label">Employee</label>
              <select id="incomeEmployeeSelect" class="form-select" required></select>
            </div>
            <div class="col-md-4">
              <label for="incomeTypeSelect" class="form-label">Income Type</label>
              <select id="incomeTypeSelect" class="form-select" onchange="updateSelectedIncomeDetails()" required></select>
            </div>
            <div class="col-md-2">
              <label for="selectedIncomeCost" class="form-label">Cost</label>
              <input type="number" id="selectedIncomeCost" class="form-control" min="0" step="0.01" placeholder="0.00" required>
            </div>
            <div class="col-md-2">
              <label class="form-label">Flags</label>
              <div class="income-meta-flags">
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="checkbox" id="selectedIncomeTaxable" disabled>
                  <label class="form-check-label" for="selectedIncomeTaxable">Taxable</label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="checkbox" id="selectedIncome13th" disabled>
                  <label class="form-check-label" for="selectedIncome13th">13th</label>
                </div>
              </div>
            </div>

            <div class="col-md-3">
              <label class="form-label">Apply Mode</label>
              <div class="form-check income-recurring-check">
                <input class="form-check-input" type="checkbox" id="selectedIncomeRecurring" onchange="toggleIncomeCutoffFieldsByRecurring()">
                <label class="form-check-label" for="selectedIncomeRecurring">Recurring (every cutoff)</label>
              </div>
            </div>
            <div class="col-md-5">
              <label for="selectedIncomeApplyMonth" class="form-label">Apply Month (for non-recurring)</label>
              <input type="month" id="selectedIncomeApplyMonth" class="form-control">
            </div>
            <div class="col-md-4">
              <label for="selectedIncomeApplyCutoff" class="form-label">Cutoff</label>
              <select id="selectedIncomeApplyCutoff" class="form-select">
                <option value="">Select cutoff</option>
                <option value="1">1st cutoff</option>
                <option value="2">2nd cutoff</option>
              </select>
            </div>
          </div>
          <div class="form-text mt-2">
            Non-recurring income can be limited to a specific month and cutoff. Recurring income applies every cutoff automatically.
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="saveEmployeeIncomeAssignment()">Save Assignment</button>
      </div>
    </div>
  </div>
</div>

<!-- Premium Table Modals -->
<div class="modal fade" id="taxTableModal" tabindex="-1" aria-labelledby="taxTableModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="taxTableModalLabel">Add Tax Record</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="taxTableForm">
          <input type="hidden" id="taxFormId">
          <div class="row g-3 mb-3">
            <div class="col-md-2">
              <label for="taxYear" class="form-label">Year</label>
              <input type="number" id="taxYear" class="form-control" value="2026" required>
            </div>
            <div class="col-md-2">
              <label for="taxIncomeFrom" class="form-label">Income From</label>
              <input type="number" id="taxIncomeFrom" class="form-control" step="0.01" required>
            </div>
            <div class="col-md-2">
              <label for="taxIncomeTo" class="form-label">Income To</label>
              <input type="number" id="taxIncomeTo" class="form-control" step="0.01">
            </div>
            <div class="col-md-2">
              <label for="taxRate" class="form-label">Tax Rate (%)</label>
              <input type="number" id="taxRate" class="form-control" step="0.01" required>
            </div>
            <div class="col-md-2">
              <label for="taxBaseAmount" class="form-label">Base Amount</label>
              <input type="number" id="taxBaseAmount" class="form-control" step="0.01" value="0">
            </div>
            <div class="col-md-2">
              <label for="taxDescription" class="form-label">Description</label>
              <input type="text" id="taxDescription" class="form-control">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="taxManager.saveRecord()">Save</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="sssTableModal" tabindex="-1" aria-labelledby="sssTableModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="sssTableModalLabel">Add SSS Record</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="sssTableForm">
          <input type="hidden" id="sssFormId">
          <div class="row g-3 mb-3">
            <div class="col-md-2">
              <label for="sssYear" class="form-label">Year</label>
              <input type="number" id="sssYear" class="form-control" value="2026" required>
            </div>
            <div class="col-md-3">
              <label for="sssSalaryFrom" class="form-label">Salary From</label>
              <input type="number" id="sssSalaryFrom" class="form-control" step="0.01" required>
            </div>
            <div class="col-md-3">
              <label for="sssSalaryTo" class="form-label">Salary To</label>
              <input type="number" id="sssSalaryTo" class="form-control" step="0.01">
            </div>
            <div class="col-md-2">
              <label for="sssContribution" class="form-label">Monthly Contribution</label>
              <input type="number" id="sssContribution" class="form-control" step="0.01" required>
            </div>
            <div class="col-md-2">
              <label for="sssDescription" class="form-label">Description</label>
              <input type="text" id="sssDescription" class="form-control">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="sssManager.saveRecord()">Save</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="philhealthTableModal" tabindex="-1" aria-labelledby="philhealthTableModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="philhealthTableModalLabel">Add PhilHealth Record</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="philhealthTableForm">
          <input type="hidden" id="philhealthFormId">
          <div class="row g-3 mb-3">
            <div class="col-md-2">
              <label for="philhealthYear" class="form-label">Year</label>
              <input type="number" id="philhealthYear" class="form-control" value="2026" required>
            </div>
            <div class="col-md-2">
              <label for="philhealthSalaryFrom" class="form-label">Salary From</label>
              <input type="number" id="philhealthSalaryFrom" class="form-control" step="0.01" required>
            </div>
            <div class="col-md-2">
              <label for="philhealthSalaryTo" class="form-label">Salary To</label>
              <input type="number" id="philhealthSalaryTo" class="form-control" step="0.01">
            </div>
            <div class="col-md-1">
              <label for="philhealthRate" class="form-label">Rate</label>
              <input type="number" id="philhealthRate" class="form-control" step="0.0001" required>
            </div>
            <div class="col-md-1">
              <label for="philhealthMaximum" class="form-label">Maximum</label>
              <input type="number" id="philhealthMaximum" class="form-control" step="0.01" required>
            </div>
            <div class="col-md-1">
              <label for="philhealthFixed" class="form-label">Fixed</label>
              <input type="number" id="philhealthFixed" class="form-control" step="0.01">
            </div>
            <div class="col-md-3">
              <label for="philhealthDescription" class="form-label">Description</label>
              <input type="text" id="philhealthDescription" class="form-control">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="philhealthManager.saveRecord()">Save</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="pagibigTableModal" tabindex="-1" aria-labelledby="pagibigTableModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pagibigTableModalLabel">Add Pag-IBIG Record</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="pagibigTableForm">
          <input type="hidden" id="pagibigFormId">
          <div class="row g-3 mb-3">
            <div class="col-md-2">
              <label for="pagibigYear" class="form-label">Year</label>
              <input type="number" id="pagibigYear" class="form-control" value="2026" required>
            </div>
            <div class="col-md-2">
              <label for="pagibigSalaryFrom" class="form-label">Salary From</label>
              <input type="number" id="pagibigSalaryFrom" class="form-control" step="0.01" required>
            </div>
            <div class="col-md-2">
              <label for="pagibigSalaryTo" class="form-label">Salary To</label>
              <input type="number" id="pagibigSalaryTo" class="form-control" step="0.01">
            </div>
            <div class="col-md-2">
              <label for="pagibigRate" class="form-label">Rate</label>
              <input type="number" id="pagibigRate" class="form-control" step="0.0001" required>
            </div>
            <div class="col-md-2">
              <label for="pagibigMaximum" class="form-label">Maximum</label>
              <input type="number" id="pagibigMaximum" class="form-control" step="0.01" required>
            </div>
            <div class="col-md-2">
              <label for="pagibigDescription" class="form-label">Description</label>
              <input type="text" id="pagibigDescription" class="form-control">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="pagibigManager.saveRecord()">Save</button>
      </div>
    </div>
  </div>
</div>

<!-- Process Payroll Modal -->
<div class="modal fade" id="processPayrollModal" tabindex="-1" aria-labelledby="processPayrollModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="processPayrollModalLabel">Process Employee Salary for the Cut-off</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="processPayrollForm">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="modalProcessPayrollFrom" class="form-label">From</label>
              <input type="text" id="modalProcessPayrollFrom" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label for="modalProcessPayrollTo" class="form-label">To</label>
              <input type="text" id="modalProcessPayrollTo" class="form-control" required>
            </div>
          </div>
          <div class="small text-muted mt-2" id="modalProcessPayrollDateRange"></div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="confirmProcessPayroll()">Process Payroll</button>
      </div>
    </div>
  </div>
</div>

  <!-- Background -->
<div class="bg-container">
    <img src="<?php echo $baseUrl; ?>/Images/bgimg.jpg" class="bg-image">
    <div class="overlay"></div>
</div>




<!-- JAVA RICE -->
<script>
  // Set global attendance active status for logout handler
  window.isAttendanceActive = false; // HR users don't have attendance active status
  
  function toggleMenu() {
    document.getElementById("profileMenu").classList.toggle("active");
  }

  document.addEventListener("click", function(e) {
    const menu = document.getElementById("profileMenu");
    const avatar = document.querySelector(".avatar");

    if (!avatar.contains(e.target) && !menu.contains(e.target)) {
      menu.classList.remove("active");
    }
  });

  document.querySelectorAll('.dropdown-container').forEach(container => {
    container.style.display = 'none';
  });

  document.querySelectorAll('.dropdown-btn').forEach(button => {
    button.addEventListener('click', function() {
      this.classList.toggle('active');
      const dropdownContent = this.nextElementSibling;
      if (dropdownContent.style.display === 'block') {
        dropdownContent.style.display = 'none';
      } else {
        dropdownContent.style.display = 'block';
      }
    });
  });

  function showContent(button, section) {

  document.querySelectorAll(".sidebar button").forEach(btn => {
    btn.classList.remove("active");
  });

  button.classList.add("active");

  const content = document.getElementById("content-area");

  if (section === "deductions") {
    content.innerHTML = `
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h2>Employee Deduction Types</h2>
          <button class="btn btn-secondary" onclick="openDeductionTypeForm()">+ Add Deduction To Employee Type</button>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <input 
              type="text" 
              id="searchDeductionTypes" 
              class="form-control" 
              placeholder="Search by deduction type..."
              onkeyup="filterDeductionTypes()"
            >
          </div>

          <div class="table-responsive">
            <table class="table table-hover" id="deductionTypeTable">
              <thead class="table-dark">
                <tr>
                  <th>Type of Deduction</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
          <div id="deductionTypePagination" class="d-flex justify-content-between align-items-center mt-3"></div>
        </div>
      </div>`;
    loadDeductionTypes();
  }

  else if (section === "income") {
      content.innerHTML = `
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h2>Employee Income Types</h2>
            <button class="btn btn-secondary" onclick="openIncomeTypeForm()">+ Add Income Type</button>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <input 
                type="text" 
                id="searchIncomeTypes" 
                class="form-control" 
                placeholder="Search by income type..."
                onkeyup="filterIncomeTypes()"
              >
            </div>

            <div class="table-responsive">
              <table class="table table-hover" id="incomeTypeTable">
                <thead class="table-dark">
                  <tr>
                    <th>Type of Income</th>
                    <th>Taxable / Non-Taxable</th>
                    <th>Included in 13th Month</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
            <div id="incomeTypePagination" class="d-flex justify-content-between align-items-center mt-3"></div>
          </div>
        </div>`;
      loadIncomeTypes();
  }

  else if (section === "employee_deductions") {
    content.innerHTML = `
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div>
            <h2>Manage Employee Deductions</h2>
            restorePayslipRangeState();
            loadPayslip();
            <span class="text-muted">Search employees stored in simpletest_db</span>
          </div>
          <button class="btn btn-success" onclick="openEmployeeDeductionForm()">Add Deduction to Employee</button>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <input
              type="text"
              id="searchDeductions" 
              class="form-control" 
              placeholder="Search by employee name..."
              onkeyup="filterEmployeesForDeductions()"
            >
          </div>

          <div class="table-responsive">
            <table class="table table-hover" id="employeeDeductionSearchTable">
              <thead class="table-dark">
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Salary</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
          <div id="employeeDeductionSearchPagination" class="d-flex justify-content-between align-items-center mt-3"></div>

          <div class="mt-4">
            <h5>Assigned Deduction Items</h5>
            <div class="table-responsive">
              <table class="table table-hover" id="employeeDeductionAssignmentTable">
                <thead class="table-dark">
                  <tr>
                    <th>Employee</th>
                    <th>Deduction Type</th>
                    <th>Cost</th>
                    <th>Recurring</th>
                    <th>Cutoff Scope</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
            <div id="employeeDeductionAssignmentPagination" class="d-flex justify-content-between align-items-center mt-3"></div>
          </div>
        </div>
      </div>`;
    loadEmployeesForDeductions();
    loadDeductionTypesForAssignments();
    fetchEmployeeDeductionAssignments();
  }

  else if (section === "employee_income") {
    content.innerHTML = `
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div>
            <h2>Manage Employee Income</h2>
            <span class="text-muted">Search employees stored in simpletest_db</span>
          </div>
          <button class="btn btn-success" onclick="openEmployeeIncomeForm()">Add Income to Employee</button>
        </div>
        <div class="card-body">
          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <input
                type="text"
                id="searchEmployees"
                class="form-control"
                placeholder="Search by name or email..."
                onkeyup="filterEmployees()"
              >
            </div>
            <div class="col-md-3">
              <label for="employeeIncomeDateFrom" class="form-label mb-1">From</label>
              <input
                type="text"
                id="employeeIncomeDateFrom"
                class="form-control"
              >
            </div>
            <div class="col-md-3">
              <label for="employeeIncomeDateTo" class="form-label mb-1">To</label>
              <input
                type="text"
                id="employeeIncomeDateTo"
                class="form-control"
              >
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-hover" id="employeeSearchTable">
              <thead class="table-dark">
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Salary</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
          <div id="employeeSearchPagination" class="d-flex justify-content-between align-items-center mt-3"></div>

          <div class="mt-4">
            <h5>Assigned Income Items</h5>
            <div class="table-responsive">
              <table class="table table-hover" id="employeeIncomeAssignmentTable">
                <thead class="table-dark">
                  <tr>
                    <th>Employee</th>
                    <th>Income Type</th>
                    <th>Cost</th>
                    <th>Taxable</th>
                    <th>13th Month</th>
                    <th>Recurring</th>
                    <th>Cutoff Scope</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
            <div id="employeeIncomeAssignmentPagination" class="d-flex justify-content-between align-items-center mt-3"></div>
          </div>
        </div>
      </div>`;
    initBiMonthlyRanges();
    loadEmployees();
    loadIncomeTypesForAssignments();
    fetchEmployeeIncomeAssignments();
  }

  else if (section === "attendance") {
    content.innerHTML = `
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2>Employee Attendance</h2>
        </div>
        <div class="card-body">
          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <input
                type="text"
                id="searchAttendance"
                class="form-control"
                placeholder="Search by ID, name, date, status, or duration..."
                onkeyup="filterAttendance()"
              >
            </div>
            <div class="col-md-3">
              <label for="attendanceDateFrom" class="form-label mb-1">From</label>
              <input
                type="text"
                id="attendanceDateFrom"
                class="form-control"
                onchange="filterAttendance()"
              >
            </div>
            <div class="col-md-3">
              <label for="attendanceDateTo" class="form-label mb-1">To</label>
              <input
                type="text"
                id="attendanceDateTo"
                class="form-control"
                onchange="filterAttendance()"
              >
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-hover" id="attendanceTable">
              <thead class="table-dark">
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Date</th>
                  <th>Clock In</th>
                  <th>Clock Out</th>
                  <th>Clock In Status</th>
                  <th>Clock Out Status</th>
                  <th>Duration(Minutes)</th>
                  <th>AO</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
          <div id="attendancePagination" class="d-flex justify-content-between align-items-center mt-3"></div>

          <div class="mt-4">
            <h5>Attendance Summary</h5>
            <div class="table-responsive">
              <table class="table table-hover" id="attendanceSummaryTable">
                <thead class="table-dark">
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Total_Hours</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>
      </div>`;
    initBiMonthlyRanges();
    loadAttendance();
  }

  else if (section === "premiums") {
    content.innerHTML = `
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div>
            <h2>Premium Summary</h2>
            <span class="text-muted">Employee ID, name, and mandatory deductions</span>
          </div>
        </div>
        <div class="card-body">
          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <input
                type="text"
                id="searchPremiumEmployees"
                class="form-control"
                placeholder="Search by employee ID, name, or deduction..."
                onkeyup="filterEmployeesForPremiums()"
              >
            </div>
            <div class="col-md-3">
              <label for="premiumDateFrom" class="form-label mb-1">From</label>
              <input
                type="text"
                id="premiumDateFrom"
                class="form-control"
                onchange="filterEmployeesForPremiums()"
              >
            </div>
            <div class="col-md-3">
              <label for="premiumDateTo" class="form-label mb-1">To</label>
              <input
                type="text"
                id="premiumDateTo"
                class="form-control"
                onchange="filterEmployeesForPremiums()"
              >
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-hover" id="employeePremiumSearchTable">
              <thead class="table-dark">
                <tr>
                  <th>Employee ID</th>
                  <th>Employee Name</th>
                  <th>SSS Deduction</th>
                  <th>PhilHealth Deduction</th>
                  <th>Pag-IBIG Deduction</th>
                  <th>Withholding Tax</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
          <div id="employeePremiumSearchPagination" class="d-flex justify-content-between align-items-center mt-3"></div>
        </div>
      </div>`;
    initBiMonthlyRanges();
    loadEmployeesForPremiums();
  }

  else if (section === "tax") {
    content.innerHTML = `
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h2>Remittances - Withholding Tax</h2>
          <div>
            <input type="text" id="remittanceTaxSearch" class="form-control form-control-sm" placeholder="Search by name or id..." onkeyup="filterRemittanceTaxes()" style="width:220px; display:inline-block;">
            <input type="month" id="remittanceTaxMonth" onchange="loadRemittanceTaxes()" style="margin-left:8px;">
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover" id="remittanceTaxTable">
              <thead class="table-dark">
                <tr>
                  <th>Employee ID</th>
                  <th>Employee Name</th>
                  <th>Withholding Tax</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>`;

    const taxMonthInput = document.getElementById('remittanceTaxMonth');
    if (taxMonthInput && !taxMonthInput.value) {
      const today = new Date();
      taxMonthInput.value = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0');
    }
    loadRemittanceTaxes();
  }

  else if (section === "sss") {
    content.innerHTML = `
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h2>Remittances - SSS</h2>
          <div>
            <input type="text" id="remittanceSssSearch" class="form-control form-control-sm" placeholder="Search by name or id..." onkeyup="filterRemittanceSss()" style="width:200px; display:inline-block;">
            <input type="month" id="remittanceSssMonth" onchange="loadRemittanceSss()" style="margin-left:8px;">
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover" id="remittanceSssTable">
              <thead class="table-dark">
                <tr>
                  <th>Employee ID</th>
                  <th>Employee Name</th>
                  <th>Employee Contribution</th>
                  <th>Employer Contribution</th>
                  <th>Total Contribution</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>`;

    const sssMonthInput = document.getElementById('remittanceSssMonth');
    if (sssMonthInput && !sssMonthInput.value) {
      const today = new Date();
      sssMonthInput.value = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0');
    }
    loadRemittanceSss();
  }

  else if (section === "philhealth") {
    content.innerHTML = `
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h2>Remittances - PhilHealth</h2>
          <div>
            <input type="text" id="remittancePhilSearch" class="form-control form-control-sm" placeholder="Search by name or id..." onkeyup="filterRemittancePhilhealth()" style="width:200px; display:inline-block;">
            <input type="month" id="remittancePhilMonth" onchange="loadRemittancePhilhealth()" style="margin-left:8px;">
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover" id="remittancePhilTable">
              <thead class="table-dark">
                <tr>
                  <th>Employee ID</th>
                  <th>Employee Name</th>
                  <th>Employee Contribution</th>
                  <th>Employer Contribution</th>
                  <th>Total Contribution</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>`;

    const philMonthInput = document.getElementById('remittancePhilMonth');
    if (philMonthInput && !philMonthInput.value) {
      const today = new Date();
      philMonthInput.value = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0');
    }
    loadRemittancePhilhealth();
  }

  else if (section === "pagibig") {
    content.innerHTML = `
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h2>Remittances - Pag-IBIG</h2>
          <div>
            <input type="text" id="remittancePagibigSearch" class="form-control form-control-sm" placeholder="Search by name or id..." onkeyup="filterRemittancePagibig()" style="width:200px; display:inline-block;">
            <input type="month" id="remittancePagibigMonth" onchange="loadRemittancePagibig()" style="margin-left:8px;">
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover" id="remittancePagibigTable">
              <thead class="table-dark">
                <tr>
                  <th>Employee ID</th>
                  <th>Employee Name</th>
                  <th>Employee Contribution</th>
                  <th>Employer Contribution</th>
                  <th>Total Contribution</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>`;

    const pagibigMonthInput = document.getElementById('remittancePagibigMonth');
    if (pagibigMonthInput && !pagibigMonthInput.value) {
      const today = new Date();
      pagibigMonthInput.value = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0');
    }
    loadRemittancePagibig();
  }

  else if (section === "tax_table") {
    content.innerHTML = `
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h2>Withholding Tax Table</h2>
          <div class="d-flex gap-2 flex-wrap justify-content-end">
            <button class="btn btn-outline-primary btn-sm" onclick="taxManager.copyPreviousYearData()">Import Previous Year</button>
            <button class="btn btn-success btn-sm" onclick="taxManager.openNewForm()">+ Add Record</button>
          </div>
        </div>
        <div class="card-body">
          <div class="row g-3 mb-3">
            <div class="col-md-3">
              <label for="taxYearSelect" class="form-label">Year</label>
              <select id="taxYearSelect" class="form-select" onchange="toggleTableData('taxTable', 'taxYearSelect')">
                <option value="2020">2020</option>
                <option value="2021">2021</option>
                <option value="2022">2022</option>
                <option value="2023">2023</option>
                <option value="2024">2024</option>
                <option value="2025">2025</option>
                <option value="2026" selected>2026</option>
                <option value="2027">2027</option>
                <option value="2028">2028</option>
                <option value="2029">2029</option>
                <option value="2030">2030</option>
              </select>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-hover" id="taxTable">
              <thead class="table-dark">
                <tr>
                  <th>Description</th>
                  <th>Tax Rate</th>
                  <th>Base Amount</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>`;
    taxManager.loadTableData(2026);
  }

  else if (section === "sss_table") {
    content.innerHTML = `
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h2>SSS Contribution Table</h2>
          <div class="d-flex gap-2 flex-wrap justify-content-end">
            <button class="btn btn-outline-primary btn-sm" onclick="sssManager.copyPreviousYearData()">Import Previous Year</button>
            <button class="btn btn-success btn-sm" onclick="sssManager.openNewForm()">+ Add Record</button>
          </div>
        </div>
        <div class="card-body">
          <div class="row g-3 mb-3">
            <div class="col-md-3">
              <label for="sssYearSelect" class="form-label">Year</label>
              <select id="sssYearSelect" class="form-select" onchange="toggleTableData('sssTable', 'sssYearSelect')">
                <option value="2020">2020</option>
                <option value="2021">2021</option>
                <option value="2022">2022</option>
                <option value="2023">2023</option>
                <option value="2024">2024</option>
                <option value="2025">2025</option>
                <option value="2026" selected>2026</option>
                <option value="2027">2027</option>
                <option value="2028">2028</option>
                <option value="2029">2029</option>
                <option value="2030">2030</option>
              </select>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-hover" id="sssTable">
              <thead class="table-dark">
                <tr>
                  <th>Description</th>
                  <th>Monthly Contribution</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>`;
    sssManager.loadTableData(2026);
  }

  else if (section === "philhealth_table") {
    content.innerHTML = `
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h2>PhilHealth Contribution Table</h2>
          <div class="d-flex gap-2 flex-wrap justify-content-end">
            <button class="btn btn-outline-primary btn-sm" onclick="philhealthManager.copyPreviousYearData()">Import Previous Year</button>
            <button class="btn btn-success btn-sm" onclick="philhealthManager.openNewForm()">+ Add Record</button>
          </div>
        </div>
        <div class="card-body">
          <div class="row g-3 mb-3">
            <div class="col-md-3">
              <label for="philhealthYearSelect" class="form-label">Year</label>
              <select id="philhealthYearSelect" class="form-select" onchange="toggleTableData('philhealthTable', 'philhealthYearSelect')">
                <option value="2020">2020</option>
                <option value="2021">2021</option>
                <option value="2022">2022</option>
                <option value="2023">2023</option>
                <option value="2024">2024</option>
                <option value="2025">2025</option>
                <option value="2026" selected>2026</option>
                <option value="2027">2027</option>
                <option value="2028">2028</option>
                <option value="2029">2029</option>
                <option value="2030">2030</option>
              </select>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-hover" id="philhealthTable">
              <thead class="table-dark">
                <tr>
                  <th>Description</th>
                  <th>Rate / Fixed Amount</th>
                  <th>Maximum</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>`;
    philhealthManager.loadTableData(2026);
  }

  else if (section === "pagibig_table") {
    content.innerHTML = `
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h2>Pag-IBIG Contribution Table</h2>
          <div class="d-flex gap-2 flex-wrap justify-content-end">
            <button class="btn btn-outline-primary btn-sm" onclick="pagibigManager.copyPreviousYearData()">Import Previous Year</button>
            <button class="btn btn-success btn-sm" onclick="pagibigManager.openNewForm()">+ Add Record</button>
          </div>
        </div>
        <div class="card-body">
          <div class="row g-3 mb-3">
            <div class="col-md-3">
              <label for="pagibigYearSelect" class="form-label">Year</label>
              <select id="pagibigYearSelect" class="form-select" onchange="toggleTableData('pagibigTable', 'pagibigYearSelect')">
                <option value="2020">2020</option>
                <option value="2021">2021</option>
                <option value="2022">2022</option>
                <option value="2023">2023</option>
                <option value="2024">2024</option>
                <option value="2025">2025</option>
                <option value="2026" selected>2026</option>
                <option value="2027">2027</option>
                <option value="2028">2028</option>
                <option value="2029">2029</option>
                <option value="2030">2030</option>
              </select>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-hover" id="pagibigTable">
              <thead class="table-dark">
                <tr>
                  <th>Description</th>
                  <th>Rate</th>
                  <th>Maximum</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>`;
    pagibigManager.loadTableData(2026);
  }

  else if (section === "employee_salary") {
    content.innerHTML = `
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2>Employee Salary</h2>
            <button class="btn btn-success btn-sm" id="processEmployeeSalaryBtn" onclick="openProcessPayrollModal()">Process Employee Salary for the cut-off</button>
        </div>
        <div class="card-body">
          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <input
                type="text"
                id="searchEmployeeSalary"
                class="form-control"
                placeholder="Search by ID, name, gross pay, allowance, deduction, holiday, or net pay..."
                onkeyup="filterEmployeeSalary()"
              >
            </div>
            <div class="col-md-3">
              <label for="employeeSalaryDateFrom" class="form-label mb-1">From</label>
              <input
                type="text"
                id="employeeSalaryDateFrom"
                class="form-control"
                onchange="loadEmployeeSalary()"
              >
            </div>
            <div class="col-md-3">
              <label for="employeeSalaryDateTo" class="form-label mb-1">To</label>
              <input
                type="text"
                id="employeeSalaryDateTo"
                class="form-control"
                onchange="loadEmployeeSalary()"
              >
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-hover" id="employeeSalaryTable">
              <thead class="table-dark">
                <tr>
                  <th>Employee ID</th>
                  <th>Employee</th>
                  <th>Email</th>
                  <th>Cutoff Salary</th>
                  <th>Gross Pay Per Day</th>
                  <th>Hours of Work</th>
                  <th>Total OT</th>
                  <th>Legal Holiday</th>
                  <th>Special Holiday</th>
                  <th>Taxable Additional Income</th>
                  <th>Non-Taxable Additional Income</th>
                  <th>SSS</th>
                  <th>PHLTH</th>
                  <th>PAGIBIG</th>
                  <th>TAX</th>
                  <th>Additional Deductions</th>
                  <th>Total Ded.</th>
                  <th>Net Pay</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
          <div id="employeeSalaryPagination" class="d-flex justify-content-between align-items-center mt-3"></div>
        </div>
      </div>
      <div id="employeeSalaryEditModal" class="position-fixed top-0 start-0 w-100 h-100 d-none" style="background: rgba(0, 0, 0, 0.45); z-index: 1055;">
        <div class="d-flex align-items-center justify-content-center w-100 h-100 p-3">
          <div class="card shadow" style="max-width: 920px; width: 100%; max-height: 90vh; display: flex; flex-direction: column;">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h5 class="mb-0">Edit Employee Salary</h5>
              <button type="button" class="btn-close" aria-label="Close" onclick="closeEmployeeSalaryEditModal()"></button>
            </div>
            <div class="card-body" style="flex: 1; overflow-y: auto;">
              <input type="hidden" id="employeeSalaryEditEmployeeId">
              <input type="hidden" id="employeeSalaryEditCarryIn" value="0">
              <div class="row g-3 mb-3">
                <div class="col-md-4">
                  <label class="form-label">Employee ID</label>
                  <input type="text" id="employeeSalaryEditEmployeeIdDisplay" class="form-control" readonly>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Employee</label>
                  <input type="text" id="employeeSalaryEditEmployeeName" class="form-control" readonly>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Email</label>
                  <input type="text" id="employeeSalaryEditEmployeeEmail" class="form-control" readonly>
                </div>
              </div>
              <div class="row g-3">
                <div class="col-md-4"><label class="form-label">Cutoff Salary</label><input type="number" step="0.01" id="employeeSalaryEditCutoffSalary" class="form-control" oninput="recalculateEmployeeSalaryEditPreview()"></div>
                <div class="col-md-4"><label class="form-label">Gross Pay Per Day</label><input type="number" step="0.01" id="employeeSalaryEditGrossPayPerDay" class="form-control" oninput="recalculateEmployeeSalaryEditPreview()"></div>
                <div class="col-md-4"><label class="form-label">Hours of Work</label><input type="number" step="0.01" id="employeeSalaryEditHoursWorked" class="form-control" oninput="recalculateEmployeeSalaryEditPreview()"></div>
                <div class="col-md-4"><label class="form-label">Total OT</label><input type="number" step="0.01" id="employeeSalaryEditTotalOtPay" class="form-control" oninput="recalculateEmployeeSalaryEditPreview()"></div>
                <div class="col-md-4"><label class="form-label">Legal Holiday</label><input type="number" step="0.01" id="employeeSalaryEditLegalHoliday" class="form-control" oninput="recalculateEmployeeSalaryEditPreview()"></div>
                <div class="col-md-4"><label class="form-label">Special Holiday</label><input type="number" step="0.01" id="employeeSalaryEditSpecialHoliday" class="form-control" oninput="recalculateEmployeeSalaryEditPreview()"></div>
                <div class="col-md-4"><label class="form-label">Taxable Additional Income</label><input type="number" step="0.01" id="employeeSalaryEditTaxableAdditionalIncome" class="form-control" oninput="recalculateEmployeeSalaryEditPreview()"></div>
                <div class="col-md-4"><label class="form-label">Non-Taxable Additional Income</label><input type="number" step="0.01" id="employeeSalaryEditNonTaxableAdditionalIncome" class="form-control" oninput="recalculateEmployeeSalaryEditPreview()"></div>
                <div class="col-md-4"><label class="form-label">SSS</label><input type="number" step="0.01" id="employeeSalaryEditSss" class="form-control" oninput="recalculateEmployeeSalaryEditPreview()"></div>
                <div class="col-md-4"><label class="form-label">PHLTH</label><input type="number" step="0.01" id="employeeSalaryEditPhlth" class="form-control" oninput="recalculateEmployeeSalaryEditPreview()"></div>
                <div class="col-md-4"><label class="form-label">PAGIBIG</label><input type="number" step="0.01" id="employeeSalaryEditPagibig" class="form-control" oninput="recalculateEmployeeSalaryEditPreview()"></div>
                <div class="col-md-4"><label class="form-label">TAX</label><input type="number" step="0.01" id="employeeSalaryEditTax" class="form-control" oninput="recalculateEmployeeSalaryEditPreview()"></div>
                <div class="col-md-4"><label class="form-label">Additional Deductions</label><input type="number" step="0.01" id="employeeSalaryEditAdditionalDeductions" class="form-control" oninput="recalculateEmployeeSalaryEditPreview()"></div>
                <div class="col-md-4"><label class="form-label">Total Ded.</label><input type="text" id="employeeSalaryEditTotalDeduction" class="form-control" readonly></div>
                <div class="col-md-4"><label class="form-label">Net Pay</label><input type="text" id="employeeSalaryEditNetPay" class="form-control" readonly></div>
              </div>
            </div>
            <div class="card-footer d-flex justify-content-end gap-2">
              <button type="button" class="btn btn-outline-secondary" onclick="closeEmployeeSalaryEditModal()">Cancel</button>
              <button type="button" class="btn btn-primary" onclick="saveEmployeeSalaryEditRow()">Save Changes</button>
            </div>
          </div>
        </div>
      </div>`;
    initBiMonthlyRanges();
    loadEmployeeSalary();
  }

  else if (section === "process_13") {
    content.innerHTML = `
      <div class="card">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="mb-0">Process 13th Month Pay</h2>
            <div class="d-flex align-items-end gap-2 flex-wrap">
              <div>
                <label for="month13ListingYear" class="form-label mb-1">Year</label>
                <input
                  type="number"
                  id="month13ListingYear"
                  class="form-control"
                  value="${new Date().getFullYear()}"
                  min="1900"
                  max="9999"
                >
              </div>
              <button class="btn btn-primary" onclick="loadProcess13ComputedData()">Load</button>
              <button class="btn btn-warning" id="process13ListingBtn" onclick="process13MonthListingYear()">Process Year</button>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <input
              type="text"
              id="searchProcess13"
              class="form-control"
              placeholder="Search by ID, name, basic salary earned, or 13th month pay..."
              onkeyup="filterProcess13()"
            >
          </div>
          <div class="table-responsive">
            <table class="table table-hover" id="process13Table">
              <thead class="table-dark">
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Salary</th>
                  <th>Basic Salary Earned (Jan-Dec)</th>
                  <th>13th Month Pay</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
          <div id="process13Pagination" class="d-flex justify-content-between align-items-center mt-3"></div>
        </div>
      </div>`;
    loadEmployeesForProcess13();
  }

  else if (section === "13_month_listing") {
    content.innerHTML = `
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
          <h2 class="mb-0">13th Month Listing</h2>
          <div class="d-flex align-items-end gap-2 flex-wrap">
            <div>
              <label for="month13ListingYearFilter" class="form-label mb-1">Year</label>
              <input
                type="number"
                id="month13ListingYearFilter"
                class="form-control"
                value="${new Date().getFullYear()}"
                min="1900"
                max="9999"
              >
            </div>
            <button class="btn btn-primary" onclick="load13MonthListing()">Load</button>
            <button class="btn btn-outline-success" onclick="export13MonthListingCsv()">Export CSV</button>
          </div>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <input
              type="text"
              id="search13MonthListing"
              class="form-control"
              placeholder="Search by ID, name, year, salary, basic earned, or 13th month pay..."
              onkeyup="filter13MonthListing()"
            >
          </div>
          <div class="table-responsive">
            <table class="table table-hover" id="month13ListingTable">
              <thead class="table-dark">
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Year</th>
                  <th>Salary</th>
                  <th>Basic Salary Earned (Jan-Dec)</th>
                  <th>13th Month Pay</th>
                  <th>Computed At</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
          <div id="month13ListingPagination" class="d-flex justify-content-between align-items-center mt-3"></div>
        </div>
      </div>`;
    load13MonthListing();
  }

  else if (section === "payslip") {
    content.innerHTML = `
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h2>Payslip</h2>
          <button class="btn btn-success" onclick="openBulkEmailModal()">Email Employee</button>
        </div>
        <div class="card-body">
          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <input
                type="text"
                id="searchPayslip"
                class="form-control"
                placeholder="Search by employee, salary, deduction, or net pay..."
                onkeyup="filterPayslip()"
              >
            </div>
            <div class="col-md-3">
              <label for="payslipDateFrom" class="form-label mb-1">From</label>
              <input
                type="text"
                id="payslipDateFrom"
                class="form-control"
                onchange="loadPayslip()"
              >
            </div>
            <div class="col-md-3">
              <label for="payslipDateTo" class="form-label mb-1">To</label>
              <input
                type="text"
                id="payslipDateTo"
                class="form-control"
                onchange="loadPayslip()"
              >
            </div>
          </div>

          <div id="payslipEmptyState" class="alert alert-info mb-3 d-none">
            No processed payroll found for this cutoff. Go to Employee Salary, select the cutoff, then click Process Employee Salary for the cut-off.
          </div>

          <div id="payslipTableWrap" class="table-responsive">
            <table class="table table-hover" id="payslipTable">
              <thead class="table-dark">
                <tr>
                  <th>Employee ID</th>
                  <th>Employee</th>
                  <th>Email</th>
                  <th>Cutoff Salary</th>
                  <th>Gross Pay Per Day</th>
                  <th>Hours of Work</th>
                  <th>Total OT</th>
                  <th>Legal Holiday</th>
                  <th>Special Holiday</th>
                  <th>Taxable Additional Income</th>
                  <th>Non-Taxable Additional Income</th>
                  <th>SSS</th>
                  <th>PHLTH</th>
                  <th>PAGIBIG</th>
                  <th>TAX</th>
                  <th>Additional Deductions</th>
                  <th>Total Ded.</th>
                  <th>Net Pay</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
          <div id="payslipPagination" class="d-flex justify-content-between align-items-center mt-3"></div>

          <div id="payslipEmailModal" class="position-fixed top-0 start-0 w-100 h-100 d-none" style="background: rgba(0, 0, 0, 0.45); z-index: 1055;">
            <div class="d-flex align-items-center justify-content-center w-100 h-100 p-3">
              <div class="card shadow" style="max-width: 540px; width: 100%;">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <h5 class="mb-0">Payslip Email</h5>
                  <button type="button" class="btn-close" aria-label="Close" onclick="closePayslipEmailModal()"></button>
                </div>
                <div class="card-body">
                  <p class="mb-2"><strong>Employee ID:</strong> <span id="payslipModalEmployeeId">-</span></p>
                  <p class="mb-2"><strong>Employee:</strong> <span id="payslipModalEmployeeName">-</span></p>
                  <p class="mb-3"><strong>Email:</strong> <span id="payslipModalEmployeeEmail">-</span></p>
                  <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" onclick="closePayslipEmailModal()">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="sendPayslipBreakdownInEmail()">Send payslip breakdown in email</button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div id="bulkEmailModal" class="position-fixed top-0 start-0 w-100 h-100 d-none" style="background: rgba(0, 0, 0, 0.45); z-index: 1055;">
            <div class="d-flex align-items-center justify-content-center w-100 h-100 p-3">
              <div class="card shadow" style="max-width: 900px; width: 100%; max-height: 90vh; display: flex; flex-direction: column;">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <h5 class="mb-0">Email Payslip to Employees</h5>
                  <button type="button" class="btn-close" aria-label="Close" onclick="closeBulkEmailModal()"></button>
                </div>
                <div class="card-body" style="flex: 1; overflow-y: auto;">
                  <div class="mb-3 d-flex gap-2">
                    <input
                      type="text"
                      id="bulkEmailSearchInput"
                      class="form-control"
                      placeholder="Search employees by name or email..."
                      onkeyup="filterBulkEmailEmployees()"
                    >
                    <button type="button" class="btn btn-info" onclick="selectAllBulkEmailEmployees()">Select All</button>
                  </div>
                  <div id="bulkEmailEmployeeList" style="overflow-y: auto;">
                    <!-- Employee table will be populated here -->
                  </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                  <button type="button" class="btn btn-outline-secondary" onclick="closeBulkEmailModal()">Cancel</button>
                  <button type="button" class="btn btn-success" onclick="sendBulkPayslipEmails()">Send Payslips</button>
                </div>
              </div>
            </div>
          </div>
              </div>
            </div>
          </div>
        </div>
      </div>`;
    initBiMonthlyRanges();
    loadPayslip();
  }

  else if (section === "reports") {
    content.innerHTML = `
      <div class="card">
        <h2>Reports</h2>
        <ul>
          <li>Payslip</li>
          <li>13th Month Listing</li>
          <li>Contributions (Tax, SSS, PhilHealth, Pag-IBIG)</li>
        </ul>
      </div>`;
  }
}

// Set bi-monthly date ranges (1-15, 16-end) for From/To inputs when empty
function setBiMonthlyRangeForInputs(fromId, toId, referenceDate = new Date()) {
  const fromInput = document.getElementById(fromId);
  const toInput = document.getElementById(toId);
  if (!fromInput || !toInput) return;

  // Only set defaults when inputs are empty
  if (fromInput.value || toInput.value) return;

  const ref = new Date(referenceDate);
  const year = ref.getFullYear();
  const month = ref.getMonth();
  let fromDate, toDate;

  if (ref.getDate() <= 15) {
    fromDate = new Date(year, month, 1);
    toDate = new Date(year, month, 15);
  } else {
    fromDate = new Date(year, month, 16);
    // last day of month: day 0 of next month
    toDate = new Date(year, month + 1, 0);
  }

  // Use ISO format (YYYY-MM-DD) for consistent parsing across all systems
  const toISODateString = (date) => {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
  };

  fromInput.value = toISODateString(fromDate);
  toInput.value = toISODateString(toDate);
}

function parseDateInputValue(value, endOfDay = false) {
  const raw = String(value || '').trim();
  if (!raw) return null;

  let parsedDate = null;

  if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) {
    const [year, month, day] = raw.split('-').map(Number);
    parsedDate = new Date(year, month - 1, day);
  } else if (/^\d{2}\/\d{2}\/\d{4}$/.test(raw)) {
    const [partA, partB, year] = raw.split('/').map(Number);
    let day = partA;
    let month = partB;

    if (partA <= 12 && partB > 12) {
      month = partA;
      day = partB;
    }

    parsedDate = new Date(year, month - 1, day);
  } else {
    const fallback = new Date(raw);
    if (!Number.isNaN(fallback.getTime())) {
      parsedDate = fallback;
    }
  }

  if (!parsedDate || Number.isNaN(parsedDate.getTime())) {
    return null;
  }

  if (endOfDay) {
    parsedDate.setHours(23, 59, 59, 999);
  } else {
    parsedDate.setHours(0, 0, 0, 0);
  }

  return parsedDate;
}

function formatIsoDateToWords(isoDateValue) {
  if (!isoDateValue) {
    return '';
  }

  const raw = String(isoDateValue).trim();
  let date = null;

  if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) {
    const parts = raw.split('-');
    const year = Number(parts[0]);
    const monthIndex = Number(parts[1]) - 1;
    const day = Number(parts[2]);
    date = new Date(year, monthIndex, day);
  } else if (/^\d{2}\/\d{2}\/\d{4}$/.test(raw)) {
    const parts = raw.split('/');
    // UI is in day/month/year form.
    const day = Number(parts[0]);
    const monthIndex = Number(parts[1]) - 1;
    const year = Number(parts[2]);
    date = new Date(year, monthIndex, day);
  } else {
    const parsed = new Date(raw);
    date = Number.isNaN(parsed.getTime()) ? null : parsed;
  }

  if (!date || Number.isNaN(date.getTime())) {
    return '';
  }

  if (Number.isNaN(date.getTime())) {
    return '';
  }

  return date.toLocaleDateString('en-US', {
    month: 'long',
    day: 'numeric',
    year: 'numeric'
  });
}

function updateDateRangeText(fromId, toId, outputId) {
  const fromInput = document.getElementById(fromId);
  const toInput = document.getElementById(toId);
  if (!fromInput || !toInput) return;

  let output = document.getElementById(outputId);
  if (!output) {
    output = document.createElement('div');
    output.id = outputId;
    output.className = 'small text-muted mt-1 fw-semibold';

    const row = fromInput.closest('.row');
    if (row && row.parentNode) {
      row.parentNode.insertBefore(output, row.nextSibling);
    } else {
      toInput.parentNode.appendChild(output);
    }
  }

  const fromText = formatIsoDateToWords(fromInput.value);
  const toText = formatIsoDateToWords(toInput.value);

  if (fromText && toText) {
    output.textContent = `From ${fromText} to ${toText}`;
    return;
  }

  if (fromText) {
    output.textContent = `From ${fromText}`;
    return;
  }

  if (toText) {
    output.textContent = `To ${toText}`;
    return;
  }

  output.textContent = '';
}

function attachDateRangeFormatter(fromId, toId, outputId) {
  const fromInput = document.getElementById(fromId);
  const toInput = document.getElementById(toId);
  if (!fromInput || !toInput) return;

  const sync = () => updateDateRangeText(fromId, toId, outputId);
  fromInput.removeEventListener('change', sync);
  toInput.removeEventListener('change', sync);
  fromInput.addEventListener('change', sync);
  toInput.addEventListener('change', sync);
  sync();
}

function initDatePickerForInput(inputId, onChanged) {
  if (typeof $ === 'undefined' || !$.fn || !$.fn.datepicker) {
    return;
  }

  const selector = `#${inputId}`;
  const input = document.getElementById(inputId);
  if (!input) {
    return;
  }

  if ($(selector).hasClass('hasDatepicker')) {
    $(selector).datepicker('destroy');
  }

  $(selector).datepicker({
    dateFormat: 'yy-mm-dd',  // ISO format for consistent parsing
    changeMonth: true,
    changeYear: true,
    onSelect: function() {
      if (typeof onChanged === 'function') {
        onChanged();
      }
    }
  });
}

function initDatePickersForRanges() {
  const rangeConfigs = [
    ['employeeIncomeDateFrom', 'employeeIncomeDateTo', 'employeeIncomeDateRangeText', null],
    ['attendanceDateFrom', 'attendanceDateTo', 'attendanceDateRangeText', null],
    ['premiumDateFrom', 'premiumDateTo', 'premiumDateRangeText', null],
    ['employeeSalaryDateFrom', 'employeeSalaryDateTo', 'employeeSalaryDateRangeText', loadEmployeeSalary],
    ['payslipDateFrom', 'payslipDateTo', 'payslipDateRangeText', loadPayslip]
  ];

  rangeConfigs.forEach(([fromId, toId, outputId, reloadFn]) => {
    const sync = () => updateDateRangeText(fromId, toId, outputId);
    const syncAndReload = () => {
      sync();
      if (typeof reloadFn === 'function') {
        reloadFn();
      }
    };
    initDatePickerForInput(fromId, syncAndReload);
    initDatePickerForInput(toId, syncAndReload);
  });
}

function initBiMonthlyRanges() {
  const pairs = [
    ['employeeIncomeDateFrom', 'employeeIncomeDateTo'],
    ['attendanceDateFrom', 'attendanceDateTo'],
    ['premiumDateFrom', 'premiumDateTo'],
    ['employeeSalaryDateFrom', 'employeeSalaryDateTo'],
    ['payslipDateFrom', 'payslipDateTo']
  ];
  pairs.forEach(p => setBiMonthlyRangeForInputs(p[0], p[1]));

  attachDateRangeFormatter('employeeIncomeDateFrom', 'employeeIncomeDateTo', 'employeeIncomeDateRangeText');
  attachDateRangeFormatter('attendanceDateFrom', 'attendanceDateTo', 'attendanceDateRangeText');
  attachDateRangeFormatter('premiumDateFrom', 'premiumDateTo', 'premiumDateRangeText');
  attachDateRangeFormatter('employeeSalaryDateFrom', 'employeeSalaryDateTo', 'employeeSalaryDateRangeText');
  attachDateRangeFormatter('payslipDateFrom', 'payslipDateTo', 'payslipDateRangeText');
  initDatePickersForRanges();
}

function getPayslipRangeStorageKey() {
  return 'payroll_payslip_range';
}

function savePayslipRangeState() {
  const fromInput = document.getElementById('payslipDateFrom');
  const toInput = document.getElementById('payslipDateTo');
  if (!fromInput || !toInput || typeof localStorage === 'undefined') {
    return;
  }

  const value = {
    from: fromInput.value || '',
    to: toInput.value || ''
  };

  try {
    localStorage.setItem(getPayslipRangeStorageKey(), JSON.stringify(value));
  } catch (error) {
    console.warn('Unable to persist payslip range state:', error);
  }
}

function restorePayslipRangeState() {
  const fromInput = document.getElementById('payslipDateFrom');
  const toInput = document.getElementById('payslipDateTo');
  if (!fromInput || !toInput || typeof localStorage === 'undefined') {
    return false;
  }

  try {
    const raw = localStorage.getItem(getPayslipRangeStorageKey());
    if (!raw) {
      return false;
    }

    const parsed = JSON.parse(raw);
    const fromValue = typeof parsed.from === 'string' ? parsed.from : '';
    const toValue = typeof parsed.to === 'string' ? parsed.to : '';
    if (!fromValue || !toValue) {
      return false;
    }

    fromInput.value = fromValue;
    toInput.value = toValue;
    updateDateRangeText('payslipDateFrom', 'payslipDateTo', 'payslipDateRangeText');
    return true;
  } catch (error) {
    console.warn('Unable to restore payslip range state:', error);
    return false;
  }
}

document.addEventListener('DOMContentLoaded', initBiMonthlyRanges);

let employeesForIncome = [];
let incomeTypesForAssignments = [];
let employeeIncomeAssignments = [];
let employeesForDeductions = [];
let deductionTypesForAssignments = [];
let employeeDeductionAssignments = [];
let employeesForPremiums = [];
let employeesForSalary = [];
let attendanceRows = [];
let filteredAttendanceRows = [];
let month13ListingRows = [];
let currentEmployeeSalaryRows = [];
let attendanceCurrentPage = 1;
const attendancePageSize = 10;
const tablePageSize = 10;
const tablePaginationState = {};
const payrollDataTableInstances = {};
let bulkEmailEmployees = [];
let allPayslipEmployees = [];
let selectedBulkEmailEmployees = new Set();
const processedCutoffPayrollCache = {};

function saveProcessedCutoffPayroll(cutoffKey, rows = []) {
  if (!cutoffKey) return;

  processedCutoffPayrollCache[cutoffKey] = {
    cutoffKey,
    processedAt: new Date().toISOString(),
    rows
  };
}

function getProcessedCutoffPayroll(cutoffKey) {
  if (!cutoffKey) return null;
  return processedCutoffPayrollCache[cutoffKey] || null;
}

function getPayrollDataTable(tableId) {
  return payrollDataTableInstances[tableId] || null;
}

function searchPayrollDataTable(tableId, filter) {
  const tableInstance = getPayrollDataTable(tableId);
  if (!tableInstance) {
    return;
  }

  tableInstance.search(filter || '').draw();
}

function destroyPayrollDataTable(tableId) {
  const existingTable = payrollDataTableInstances[tableId];
  if (existingTable) {
    existingTable.destroy();
    payrollDataTableInstances[tableId] = null;
  }
}

function initializePayrollDataTable(tableId, paginationId) {
  const tableElement = document.getElementById(tableId);
  if (!tableElement || typeof DataTable === 'undefined') {
    return null;
  }

  const tableBody = tableElement.querySelector('tbody');
  if (!tableBody) {
    return null;
  }

  const rows = Array.from(tableBody.querySelectorAll('tr'));
  const hasMessageRow = rows.length === 1 && rows[0].querySelector('td[colspan]');

  destroyPayrollDataTable(tableId);

  if (rows.length === 0 || hasMessageRow) {
    const paginationContainer = paginationId ? document.getElementById(paginationId) : null;
    if (paginationContainer) {
      paginationContainer.style.display = 'none';
    }
    return null;
  }

  const instance = new DataTable(tableElement, {
    paging: true,
    pageLength: tablePageSize,
    lengthChange: false,
    searching: true,
    info: true,
    ordering: true,
    dom: 'rt<"d-flex justify-content-between align-items-center mt-3"lip>',
    destroy: true,
    autoWidth: false
  });

  payrollDataTableInstances[tableId] = instance;

  const paginationContainer = paginationId ? document.getElementById(paginationId) : null;
  if (paginationContainer) {
    paginationContainer.style.display = 'none';
  }

  return instance;
}

function getCompactPageSequence(totalPages, currentPage) {
  if (totalPages <= 7) {
    return Array.from({ length: totalPages }, (_, i) => i + 1);
  }

  const pages = [1];
  const start = Math.max(2, currentPage - 1);
  const end = Math.min(totalPages - 1, currentPage + 1);

  if (start > 2) {
    pages.push('ellipsis-left');
  }

  for (let page = start; page <= end; page++) {
    pages.push(page);
  }

  if (end < totalPages - 1) {
    pages.push('ellipsis-right');
  }

  pages.push(totalPages);
  return pages;
}

function paginateTable(tableId, paginationId, resetPage = false) {
  const tableInstance = getPayrollDataTable(tableId);
  if (tableInstance) {
    tableInstance.draw(false);
    const paginationContainer = document.getElementById(paginationId);
    if (paginationContainer) {
      paginationContainer.style.display = 'none';
    }
    return;
  }

  initializePayrollDataTable(tableId, paginationId);
}

if (typeof DataTable !== 'undefined') {
  DataTable.ext.search.push((settings, data, dataIndex) => {
    const tableId = settings?.nTable?.id;

    if (tableId === 'attendanceTable') {
      const searchTerm = (document.getElementById('searchAttendance')?.value || '').toLowerCase();
      const dateFromInput = document.getElementById('attendanceDateFrom');
      const dateToInput = document.getElementById('attendanceDateTo');
      const dateFrom = parseDateInputValue(dateFromInput?.value, false);
      const dateTo = parseDateInputValue(dateToInput?.value, true);

      const rowText = data.join(' ').toLowerCase();
      const textMatches = !searchTerm || rowText.includes(searchTerm);
      if (!textMatches) {
        return false;
      }

      if (!dateFrom && !dateTo) {
        return true;
      }

      const rowDate = data[2] ? new Date(`${data[2]}T00:00:00`) : null;
      if (!rowDate || Number.isNaN(rowDate.getTime())) {
        return false;
      }

      if (dateFrom && rowDate < dateFrom) {
        return false;
      }

      if (dateTo && rowDate > dateTo) {
        return false;
      }

      return true;
    }

    if (tableId === 'employeePremiumSearchTable') {
      const searchTerm = (document.getElementById('searchPremiumEmployees')?.value || '').toLowerCase();
      const dateFromInput = document.getElementById('premiumDateFrom');
      const dateToInput = document.getElementById('premiumDateTo');
      const dateFrom = parseDateInputValue(dateFromInput?.value, false);
      const dateTo = parseDateInputValue(dateToInput?.value, true);

      const rowText = data.join(' ').toLowerCase();
      const textMatches = !searchTerm || rowText.includes(searchTerm);
      if (!textMatches) {
        return false;
      }

      if (!dateFrom && !dateTo) {
        return true;
      }

      const rowNode = settings?.aoData?.[dataIndex]?.nTr;
      const joinDateText = rowNode?.dataset?.joinDate || '';
      const joinDate = joinDateText ? new Date(`${joinDateText}T00:00:00`) : null;
      if (!joinDate || Number.isNaN(joinDate.getTime())) {
        return false;
      }

      if (dateFrom && joinDate < dateFrom) {
        return false;
      }

      if (dateTo && joinDate > dateTo) {
        return false;
      }

      return true;
    }

    return true;
  });
}

function goToTablePage(tableId, paginationId, page) {
  tablePaginationState[tableId] = page;
  paginateTable(tableId, paginationId, false);
}

function computeSssContribution(salaryValue) {
  const salary = Number(String(salaryValue ?? '').replace(/,/g, ''));
  if (!Number.isFinite(salary) || salary < 0) {
    return null;
  }

  // Return 0 for zero salary (cutoff with 0 hours worked)
  if (salary === 0) {
    return 0;
  }

  // SSS (effective Jan 2025): 15% total contribution,
  // split as 5% employee and 10% employer, based on Monthly Salary Credit (MSC).
  // Salary input is expected to be MONTHLY salary for this function.
  let msc = 5000;
  if (salary >= 34750) {
    msc = 35000;
  } else if (salary >= 5250) {
    const step = Math.floor((salary - 5250) / 500) + 1;
    msc = 5000 + step * 500;
  }

  return Number((msc * 0.15).toFixed(2));
}

function computePagibigContribution(salaryValue) {
  const salary = Number(String(salaryValue ?? '').replace(/,/g, ''));
  if (!Number.isFinite(salary) || salary < 0) {
    return null;
  }

  // Return 0 for zero salary (cutoff with 0 hours worked)
  if (salary === 0) {
    return 0;
  }

  // Pag-IBIG: 2% total contribution (1% employee + 1% employer), capped at 200 total.
  return Number(Math.min(salary * 0.02, 200).toFixed(2));
}

function computePhilhealthContribution(salaryValue) {
  const salary = Number(String(salaryValue ?? '').replace(/,/g, ''));
  if (!Number.isFinite(salary) || salary < 0) {
    return null;
  }

  // Return 0 for zero salary (cutoff with 0 hours worked)
  if (salary === 0) {
    return 0;
  }

  // PhilHealth: 5% premium rate with monthly salary floor/ceiling (10,000 to 100,000).
  const premiumBasis = Math.min(Math.max(salary, 10000), 100000);
  return Number((premiumBasis * 0.05).toFixed(2));
}

function computeWithholdingTax(salaryValue, sssValue = 0, philhealthValue = 0, pagibigValue = 0, nonTaxableValue = 0) {
  const salary = Number(String(salaryValue ?? '').replace(/,/g, ''));
  const sss = Number(sssValue) || 0;
  const philhealth = Number(philhealthValue) || 0;
  const pagibig = Number(pagibigValue) || 0;
  const nonTaxable = Number(nonTaxableValue) || 0;

  if (!Number.isFinite(salary) || salary < 0) {
    return null;
  }

  // Return 0 for zero salary (cutoff with 0 hours worked)
  if (salary === 0) {
    return 0;
  }

  // Taxable Income = Gross - Non-taxable - Mandatory Contributions.
  const taxableIncome = Math.max(0, salary - nonTaxable - sss - philhealth - pagibig);

  // Monthly BIR TRAIN tax table equivalent.
  if (taxableIncome <= 20833) return 0;
  if (taxableIncome <= 33333) return (taxableIncome - 20833) * 0.15;
  if (taxableIncome <= 66667) return 1875 + (taxableIncome - 33333) * 0.20;
  if (taxableIncome <= 166667) return 8541.8 + (taxableIncome - 66667) * 0.25;
  if (taxableIncome <= 666667) return 33541.8 + (taxableIncome - 166667) * 0.30;
  return 183541.8 + (taxableIncome - 666667) * 0.35;
}

// Compute split between employee and employer shares for each scheme.
function computeSssSplit(totalContribution) {
  const total = Number(totalContribution) || 0;
  if (!Number.isFinite(total)) return { employee: null, employer: null, total: null };
  if (total === 0) return { employee: 0, employer: 0, total: 0 };
  // SSS exact split from Circular 2024-006 basis: 5% employee and 10% employer (of 15% total).
  const employee = Number((total / 3).toFixed(2));
  const employer = Number((total - employee).toFixed(2));
  return { employee, employer, total: Number(total.toFixed(2)) };
}

function computePhilhealthSplit(totalContribution) {
  const total = Number(totalContribution) || 0;
  if (!Number.isFinite(total)) return { employee: null, employer: null, total: null };
  if (total === 0) return { employee: 0, employer: 0, total: 0 };
  // PhilHealth is typically split 50/50 between employee and employer
  const employee = Number((total / 2).toFixed(2));
  const employer = Number((total - employee).toFixed(2));
  return { employee, employer, total: Number(total.toFixed(2)) };
}

function computePagibigSplit(totalContribution) {
  const total = Number(totalContribution) || 0;
  if (!Number.isFinite(total)) return { employee: null, employer: null, total: null };
  if (total === 0) return { employee: 0, employer: 0, total: 0 };
  // Pag-IBIG current standard total is 2% (1% employee + 1% employer) — split evenly
  const employee = Number((total / 2).toFixed(2));
  const employer = Number((total - employee).toFixed(2));
  return { employee, employer, total: Number(total.toFixed(2)) };
}

// --- Carry-over helpers: store and retrieve per-employee, per-cutoff carry amounts ---
function makeCutoffKey(fromStr, toStr) {
  return `${String(fromStr || '')}_${String(toStr || '')}`;
}

function getCarryForCutoff(employeeId, cutoffKey) {
  if (!employeeId || !cutoffKey) return 0;
  try {
    const raw = localStorage.getItem(`payroll_carry_${employeeId}_${cutoffKey}`);
    const v = Number(raw || 0);
    return Number.isFinite(v) ? v : 0;
  } catch (e) {
    return 0;
  }
}

function setCarryForCutoff(employeeId, cutoffKey, amount) {
  if (!employeeId || !cutoffKey) return;
  try {
    const v = Number(amount) || 0;
    localStorage.setItem(`payroll_carry_${employeeId}_${cutoffKey}`, String(v));
  } catch (e) {
    // ignore storage errors
  }
}

function computeNextCutoffKey(fromStr, toStr) {
  // fromStr and toStr expected as yyyy-mm-dd or empty. We'll parse to Dates safely.
  try {
    const from = fromStr ? new Date(fromStr + 'T00:00:00') : null;
    const to = toStr ? new Date(toStr + 'T00:00:00') : null;
    if (!to || isNaN(to.getTime())) return '';

    // If current cutoff is 1..15 -> next is 16..last day of same month
    // Else (16..end) -> next is 1..15 of next month
    const dayFrom = from ? from.getDate() : 1;
    const dayTo = to.getDate();

    let nextFrom, nextTo;
    if (dayFrom === 1 && dayTo === 15) {
      // same month, second cutoff
      nextFrom = new Date(to);
      nextFrom.setDate(16);
      const lastDay = new Date(to.getFullYear(), to.getMonth() + 1, 0).getDate();
      nextTo = new Date(to);
      nextTo.setDate(lastDay);
    } else {
      // next is first..15 of next month
      const year = to.getFullYear();
      const month = to.getMonth();
      nextFrom = new Date(year, month + 1, 1);
      nextTo = new Date(year, month + 1, 15);
    }

    const fromIso = nextFrom.toISOString().slice(0,10);
    const toIso = nextTo.toISOString().slice(0,10);
    return makeCutoffKey(fromIso, toIso);
  } catch (e) {
    return '';
  }
}


function buildNonTaxableIncomeMap(assignedIncomeData = []) {
  const nonTaxableCostByEmployee = {};

  (assignedIncomeData || []).forEach(item => {
    const employeeNameKey = String(item.name || '').trim().toLowerCase();
    const isNonTaxable = Number(item.taxable) === 0;
    const cost = Number(item.cost);

    if (!employeeNameKey || !isNonTaxable || !Number.isFinite(cost)) {
      return;
    }

    nonTaxableCostByEmployee[employeeNameKey] = (nonTaxableCostByEmployee[employeeNameKey] || 0) + cost;
  });

  return nonTaxableCostByEmployee;
}

function buildTotalIncomeMap(assignedIncomeData = []) {
  const totalIncomeByEmployee = {};

  (assignedIncomeData || []).forEach(item => {
    const employeeNameKey = String(item.name || '').trim().toLowerCase();
    const cost = Number(item.cost);

    if (!employeeNameKey || !Number.isFinite(cost)) {
      return;
    }

    totalIncomeByEmployee[employeeNameKey] = (totalIncomeByEmployee[employeeNameKey] || 0) + cost;
  });

  return totalIncomeByEmployee;
}

function computePremiumDeductions(salaryValue, hoursWorkedInCutoff = null, nonTaxableIncome = 0) {
  // If hoursWorkedInCutoff is provided, compute cutoffSalary from monthly salary -> daily -> hourly
  // and use cutoffSalary as the input basis for the premium calculations. If hoursWorkedInCutoff
  // is null or not a finite number, fall back to the existing monthly-salary behaviour.
  let basisSalary = Number(salaryValue) || 0;
  if (hoursWorkedInCutoff !== null && Number.isFinite(Number(hoursWorkedInCutoff))) {
    const dailyRate = basisSalary / 26;
    const hourlyRate = dailyRate / 8;
    const cutoffSalary = hourlyRate * Number(hoursWorkedInCutoff);
    basisSalary = cutoffSalary;
  }

  // If cutoff salary is 0 (no hours worked in this cutoff), all deductions are 0
  if (basisSalary <= 0) {
    return {
      sssContribution: 0,
      pagibigContribution: 0,
      philhealthContribution: 0,
      withholdingTax: 0,
      totalDeductions: 0
    };
  }

  const sssContribution = computeSssContribution(basisSalary);
  const pagibigContribution = computePagibigContribution(basisSalary);
  const philhealthContribution = computePhilhealthContribution(basisSalary);
  const withholdingTax = computeWithholdingTax(
    basisSalary,
    sssContribution,
    philhealthContribution,
    pagibigContribution,
    nonTaxableIncome
  );
  const totalDeductions =
    (Number(sssContribution) || 0) +
    (Number(pagibigContribution) || 0) +
    (Number(philhealthContribution) || 0) +
    (Number(withholdingTax) || 0);

  return {
    sssContribution,
    pagibigContribution,
    philhealthContribution,
    withholdingTax,
    totalDeductions
  };
}

function formatCurrency(value) {
  return `₱${Number(value).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
}

function parseMoneyInput(rawValue) {
  const normalized = String(rawValue ?? '').replace(/[^0-9.-]/g, '');
  const parsed = Number(normalized);
  return Number.isFinite(parsed) ? parsed : 0;
}

async function loadDeductionTypes() {
  const tableBody = document.querySelector('#deductionTypeTable tbody');
  if (!tableBody) return;

  tableBody.innerHTML = '<tr><td colspan="2" class="text-center">Loading...</td></tr>';

  try {
    const response = await fetch('emp_deduc_type_api.php');
    const result = await response.json();
    if (!result.success) {
      throw new Error(result.message || 'Failed to load deduction types.');
    }

    const rows = result.data || [];
    if (rows.length === 0) {
      tableBody.innerHTML = '<tr><td colspan="2" class="text-center">No deduction types found.</td></tr>';
      paginateTable('deductionTypeTable', 'deductionTypePagination', true);
      return;
    }

    tableBody.innerHTML = '';
    rows.forEach(item => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${item.type_of_deduction}</td>
        <td>
          <button type="button" class="btn btn-sm btn-warning edit-deduction-type">Edit</button>
          <button type="button" class="btn btn-sm btn-danger delete-deduction-type">Delete</button>
        </td>`;
      tableBody.appendChild(row);

      const editButton = row.querySelector('.edit-deduction-type');
      const deleteButton = row.querySelector('.delete-deduction-type');
      if (editButton) {
        editButton.addEventListener('click', () => editDeductionType(item.id));
      }
      if (deleteButton) {
        deleteButton.addEventListener('click', () => deleteDeductionType(item.id));
      }
    });
    paginateTable('deductionTypeTable', 'deductionTypePagination', true);
  } catch (error) {
    tableBody.innerHTML = `<tr><td colspan="2" class="text-center text-danger">${error.message}</td></tr>`;
    paginateTable('deductionTypeTable', 'deductionTypePagination', true);
  }
}

function openDeductionTypeForm(reset = true) {
  // Reset form fields
  if (reset) {
    document.getElementById('deductionTypeId').value = '';
    document.getElementById('deductionTypeName').value = '';
  }
  
  // Show modal
  const modalElement = document.getElementById('deductionTypeModal');
  const modal = new bootstrap.Modal(modalElement);
  modal.show();
}

function hideDeductionTypeForm() {
  const modalElement = document.getElementById('deductionTypeModal');
  if (modalElement) {
    const modal = bootstrap.Modal.getInstance(modalElement);
    if (modal) {
      modal.hide();
    }
  }
}

async function saveDeductionType() {
  const id = document.getElementById('deductionTypeId').value;
  const typeOfDeduction = document.getElementById('deductionTypeName').value.trim();
  const taxable = 1;
  const includedIn13 = 1;

  if (!typeOfDeduction) {
    alert('Please fill in the deduction type.');
    return;
  }

  const payload = {
    type_of_deduction: typeOfDeduction,
    taxable,
    included_in_13month: includedIn13
  };

  if (id) {
    payload.id = Number(id);
  }

  try {
    const response = await fetch('emp_deduc_type_api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const result = await response.json();

    if (!result.success) {
      throw new Error(result.message || 'Save failed.');
    }

    hideDeductionTypeForm();
    loadDeductionTypes();
  } catch (error) {
    alert(error.message);
  }
}

async function editDeductionType(id) {
  try {
    const response = await fetch(`emp_deduc_type_api.php?id=${id}`);
    const result = await response.json();
    if (!result.success || !result.data) {
      throw new Error(result.message || 'Unable to load deduction type.');
    }

    const item = result.data;
    document.getElementById('deductionTypeId').value = item.id;
    document.getElementById('deductionTypeName').value = item.type_of_deduction;
    openDeductionTypeForm(false);
  } catch (error) {
    alert(error.message);
  }
}

async function deleteDeductionType(id) {
  if (!confirm('Delete this deduction type?')) {
    return;
  }

  try {
    const response = await fetch('emp_deduc_type_api.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `id=${encodeURIComponent(id)}`
    });
    const result = await response.json();
    if (!result.success) {
      throw new Error(result.message || 'Delete failed.');
    }
    loadDeductionTypes();
  } catch (error) {
    alert(error.message);
  }
}

function filterDeductionTypes() {
  const searchInput = document.getElementById('searchDeductionTypes');
  if (!searchInput) return;
  searchPayrollDataTable('deductionTypeTable', searchInput.value);
}

async function loadIncomeTypes() {
  const tableBody = document.querySelector('#incomeTypeTable tbody');
  if (!tableBody) return;

tableBody.innerHTML = '<tr><td colspan="4" class="text-center">Loading...</td></tr>';

  try {
    const response = await fetch('emp_inc_type_api.php');
    const result = await response.json();

    if (!result.success) {
      throw new Error(result.message || 'Failed to load income types.');
    }

    const rows = result.data || [];
    if (rows.length === 0) {
      tableBody.innerHTML = '<tr><td colspan="4" class="text-center">No income types found.</td></tr>';
      paginateTable('incomeTypeTable', 'incomeTypePagination', true);
      return;
    }

    tableBody.innerHTML = '';
    rows.forEach(item => {
      const isTaxable = Number(item.taxable) === 1;
      const isIncludedIn13th = Number(item.included_in_13month) === 1;
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${item.type_of_income}</td>
        <td>${isTaxable ? 'Taxable' : 'Non-Taxable'}</td>
        <td>${isIncludedIn13th ? 'Yes' : 'No'}</td>
        <td>
          <button class="btn btn-primary edit-income-type" data-id="${item.id}">Edit</button>
          <button class="btn btn-danger delete-income-type" data-id="${item.id}">Delete</button>
        </td>`;
      tableBody.appendChild(row);

      const editButton = row.querySelector('.edit-income-type');
      const deleteButton = row.querySelector('.delete-income-type');
      if (editButton) {
        editButton.addEventListener('click', () => editIncomeType(item.id));
      }
      if (deleteButton) {
        deleteButton.addEventListener('click', () => deleteIncomeType(item.id));
      }
    });
    paginateTable('incomeTypeTable', 'incomeTypePagination', true);
  } catch (error) {
    tableBody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">${error.message}</td></tr>`;
    paginateTable('incomeTypeTable', 'incomeTypePagination', true);
  }
}

function openIncomeTypeForm(reset = true) {
  // Reset form fields
  if (reset) {
    document.getElementById('incomeTypeId').value = '';
    document.getElementById('incomeTypeName').value = '';
    document.getElementById('incomeTypeCost').value = '';
    document.getElementById('incomeTypeTaxable').checked = true;
    document.getElementById('incomeType13th').checked = true;
  }
  
  // Show modal
  const modalElement = document.getElementById('incomeTypeModal');
  const modal = new bootstrap.Modal(modalElement);
  modal.show();
}

function hideIncomeTypeForm() {
  const modalElement = document.getElementById('incomeTypeModal');
  if (modalElement) {
    const modal = bootstrap.Modal.getInstance(modalElement);
    if (modal) {
      modal.hide();
    }
  }
}

async function saveIncomeType() {
  const id = document.getElementById('incomeTypeId').value;
  const typeOfIncome = document.getElementById('incomeTypeName').value.trim();
  const cost = document.getElementById('incomeTypeCost').value || '0';
  const taxable = document.getElementById('incomeTypeTaxable').checked ? 1 : 0;
  const includedIn13 = document.getElementById('incomeType13th').checked ? 1 : 0;

  if (!typeOfIncome) {
    alert('Please fill in the income type.');
    return;
  }

  const payload = {
    type_of_income: typeOfIncome,
    cost: parseFloat(cost),
    taxable,
    included_in_13month: includedIn13
  };

  if (id) {
    payload.id = Number(id);
  }

  try {
    const response = await fetch('emp_inc_type_api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const result = await response.json();

    if (!result.success) {
      throw new Error(result.message || 'Save failed.');
    }

    hideIncomeTypeForm();
    loadIncomeTypes();

    // Keep Manage Employee Income data in sync with updated income type values.
    await loadIncomeTypesForAssignments();

    // If the Manage Employee Income tab is currently visible, fully refresh it.
    const manageEmployeeIncomeTable = document.getElementById('employeeIncomeAssignmentTable');
    if (manageEmployeeIncomeTable) {
      const employeeIncomeButton = Array.from(document.querySelectorAll('.sidebar button')).find(btn => btn.getAttribute('onclick') === "showContent(this,'employee_income')");
      if (employeeIncomeButton) {
        showContent(employeeIncomeButton, 'employee_income');
      } else {
        loadEmployees();
        loadIncomeTypesForAssignments();
        fetchEmployeeIncomeAssignments();
      }
    }
  } catch (error) {
    alert(error.message);
  }
}

async function editIncomeType(id) {
  try {
    const response = await fetch(`emp_inc_type_api.php?id=${id}`);
    const result = await response.json();
    if (!result.success || !result.data) {
      throw new Error(result.message || 'Unable to load income type.');
    }

    const item = result.data;
    document.getElementById('incomeTypeId').value = item.id;
    document.getElementById('incomeTypeName').value = item.type_of_income;
    document.getElementById('incomeTypeCost').value = parseFloat(item.cost || 0).toFixed(2);
    document.getElementById('incomeTypeTaxable').checked = item.taxable == 1;
    document.getElementById('incomeType13th').checked = item.included_in_13month == 1;
    openIncomeTypeForm(false);
  } catch (error) {
    alert(error.message);
  }
}

async function deleteIncomeType(id) {
  if (!confirm('Delete this income type?')) {
    return;
  }

  try {
    const response = await fetch('emp_inc_type_api.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `id=${encodeURIComponent(id)}`
    });
    const result = await response.json();
    if (!result.success) {
      throw new Error(result.message || 'Delete failed.');
    }
    loadIncomeTypes();
  } catch (error) {
    alert(error.message);
  }
}

function populateEmployeeSelect() {
  const select = document.getElementById('incomeEmployeeSelect');
  if (!select) return;
  select.innerHTML = '<option value="">Select employee</option>';
  employeesForIncome.forEach(emp => {
    const option = document.createElement('option');
    option.value = emp.id;
    option.textContent = `${emp.name} (${emp.position})`;
    select.appendChild(option);
  });
}

async function loadIncomeTypesForAssignments() {
  incomeTypesForAssignments = [];
  const select = document.getElementById('incomeTypeSelect');
  if (select) {
    select.innerHTML = '<option value="">Select income type</option>';
  }

  try {
    const response = await fetch('emp_inc_type_api.php');
    const result = await response.json();
    if (!result.success) {
      throw new Error(result.message || 'Unable to load income types.');
    }

    incomeTypesForAssignments = result.data || [];
    if (!select) return;

    incomeTypesForAssignments.forEach(item => {
      const option = document.createElement('option');
      option.value = item.id;
      option.textContent = item.type_of_income || '';
      option.dataset.cost = item.cost;
      option.dataset.taxable = item.taxable;
      option.dataset.includedIn13 = item.included_in_13month;
      option.dataset.recurring = item.recurring;
      select.appendChild(option);
    });
  } catch (error) {
    console.error(error.message);
  }
}

function populateDeductionEmployeeSelect() {
  const select = document.getElementById('deductionEmployeeSelect');
  if (!select) return;
  select.innerHTML = '<option value="">Select employee</option>';
  employeesForDeductions.forEach(emp => {
    const option = document.createElement('option');
    option.value = emp.id;
    option.textContent = `${emp.name} (${emp.position})`;
    select.appendChild(option);
  });
}

async function loadDeductionTypesForAssignments() {
  deductionTypesForAssignments = [];
  const select = document.getElementById('deductionTypeSelect');
  if (select) {
    select.innerHTML = '<option value="">Select deduction type</option>';
  }

  try {
    const response = await fetch('emp_deduc_type_api.php');
    const result = await response.json();
    if (!result.success) {
      throw new Error(result.message || 'Unable to load deduction types.');
    }

    deductionTypesForAssignments = result.data || [];
    if (!select) return;

    deductionTypesForAssignments.forEach(item => {
      const option = document.createElement('option');
      option.value = item.id;
      option.textContent = item.type_of_deduction;
      option.dataset.taxable = item.taxable;
      option.dataset.includedIn13 = item.included_in_13month;
      option.dataset.recurring = item.recurring;
      select.appendChild(option);
    });
  } catch (error) {
    console.error(error.message);
  }
}

function openEmployeeDeductionForm(reset = true) {
  if (reset) {
    document.getElementById('employeeDeductionAssignmentId').value = '';
    document.getElementById('deductionEmployeeSelect').value = '';
    document.getElementById('deductionTypeSelect').value = '';
    document.getElementById('selectedDeductionCost').value = '';
    document.getElementById('selectedDeductionRecurring').checked = false;
    document.getElementById('selectedDeductionApplyMonth').value = '';
    document.getElementById('selectedDeductionApplyCutoff').value = '';
  }

  toggleDeductionCutoffFieldsByRecurring();

  const modalElement = document.getElementById('employeeDeductionModal');
  const modal = new bootstrap.Modal(modalElement);
  modal.show();
}

function hideEmployeeDeductionForm() {
  const modalElement = document.getElementById('employeeDeductionModal');
  if (modalElement) {
    const modal = bootstrap.Modal.getInstance(modalElement);
    if (modal) {
      modal.hide();
    }
  }
}

function openPremiumTypeForm() {
  alert('Premium type form is not implemented yet.');
}

function updateSelectedDeductionDetails() {
  const select = document.getElementById('deductionTypeSelect');
  const costInput = document.getElementById('selectedDeductionCost');
  const recurringCheckbox = document.getElementById('selectedDeductionRecurring');
  if (!select || !costInput || !recurringCheckbox) return;

  const selectedOption = select.options[select.selectedIndex];
  if (!selectedOption || !selectedOption.value) {
    costInput.value = '';
    recurringCheckbox.checked = false;
    toggleDeductionCutoffFieldsByRecurring();
    return;
  }

  recurringCheckbox.checked = selectedOption.dataset.recurring === '1';
  toggleDeductionCutoffFieldsByRecurring();
}

function toggleDeductionCutoffFieldsByRecurring() {
  const recurringCheckbox = document.getElementById('selectedDeductionRecurring');
  const applyMonthInput = document.getElementById('selectedDeductionApplyMonth');
  const applyCutoffSelect = document.getElementById('selectedDeductionApplyCutoff');
  if (!recurringCheckbox || !applyMonthInput || !applyCutoffSelect) return;

  const isRecurring = recurringCheckbox.checked;
  applyMonthInput.disabled = isRecurring;
  applyCutoffSelect.disabled = isRecurring;

  if (isRecurring) {
    applyMonthInput.value = '';
    applyCutoffSelect.value = '';
  }
}

function saveEmployeeDeductionAssignment() {
  const assignmentIdInput = document.getElementById('employeeDeductionAssignmentId');
  const employeeSelect = document.getElementById('deductionEmployeeSelect');
  const deductionTypeSelect = document.getElementById('deductionTypeSelect');
  const costInput = document.getElementById('selectedDeductionCost');
  const recurringCheckbox = document.getElementById('selectedDeductionRecurring');
  const applyMonthInput = document.getElementById('selectedDeductionApplyMonth');
  const applyCutoffSelect = document.getElementById('selectedDeductionApplyCutoff');

  if (!employeeSelect || !deductionTypeSelect) return;
  if (!employeeSelect.value || !deductionTypeSelect.value) {
    alert('Please select both an employee and a deduction type.');
    return;
  }

  const employee = employeesForDeductions.find(emp => String(emp.id) === employeeSelect.value);
  const deductionType = deductionTypesForAssignments.find(item => String(item.id) === deductionTypeSelect.value);
  if (!employee || !deductionType) {
    alert('Selected employee or deduction type is invalid.');
    return;
  }

  if (!costInput || String(costInput.value).trim() === '') {
    alert('Please enter a deduction cost.');
    return;
  }

  const parsedCost = parseMoneyInput(costInput.value);
  if (!Number.isFinite(parsedCost) || parsedCost < 0) {
    alert('Please enter a valid deduction cost.');
    return;
  }

  const isRecurring = recurringCheckbox?.checked === true;
  const applyYearMonth = (applyMonthInput?.value || '').trim();
  const applyCutoffSlot = applyCutoffSelect?.value ? Number(applyCutoffSelect.value) : null;

  if (!isRecurring) {
    if (!applyYearMonth || !applyCutoffSlot) {
      alert('Please select apply month and cutoff for non-recurring deduction.');
      return;
    }
  }

  const payload = {
    name: employee.name,
    type_of_deduction: deductionType.type_of_deduction,
    cost: parsedCost,
    recurring: isRecurring ? 1 : 0,
    apply_year_month: isRecurring ? null : applyYearMonth,
    apply_cutoff_slot: isRecurring ? null : applyCutoffSlot
  };

  if (assignmentIdInput && assignmentIdInput.value) {
    payload.id = Number(assignmentIdInput.value);
  }

  fetch('assigned_emp_deduc_api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  })
    .then(response => response.json())
    .then(result => {
      if (!result.success) {
        throw new Error(result.message || 'Failed to save assignment.');
      }
      alert('Deduction assignment saved successfully!');
      hideEmployeeDeductionForm();
      fetchEmployeeDeductionAssignments();
    })
    .catch(error => {
      console.error('Error saving assignment:', error);
      alert(`Error: ${error.message}`);
    });
}

function renderEmployeeDeductionAssignments() {
  const tableBody = document.querySelector('#employeeDeductionAssignmentTable tbody');
  if (!tableBody) return;
  if (employeeDeductionAssignments.length === 0) {
    tableBody.innerHTML = '<tr><td colspan="6" class="text-center">No assigned deduction items yet.</td></tr>';
    paginateTable('employeeDeductionAssignmentTable', 'employeeDeductionAssignmentPagination', true);
    return;
  }

  tableBody.innerHTML = '';
  employeeDeductionAssignments.forEach(item => {
    const isRecurring = Number(item.recurring) === 1;
    const cutoffScopeText = isRecurring
      ? 'Every cutoff'
      : (item.applyYearMonth && Number(item.applyCutoffSlot) > 0
        ? `${item.applyYearMonth} (${Number(item.applyCutoffSlot) === 1 ? '1st' : '2nd'} cutoff)`
        : 'Any cutoff');
    const row = document.createElement('tr');
    row.innerHTML = `
      <td>${item.employeeName}</td>
      <td>${item.deductionType}</td>
      <td>₱${parseFloat(item.cost).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
      <td>${isRecurring ? 'Yes' : 'No'}</td>
      <td>${cutoffScopeText}</td>
      <td>
        <button type="button" class="btn btn-sm btn-warning edit-employee-deduction">Edit</button>
        <button type="button" class="btn btn-sm btn-danger delete-employee-deduction">Delete</button>
      </td>
    `;
    tableBody.appendChild(row);

    const editButton = row.querySelector('.edit-employee-deduction');
    const deleteButton = row.querySelector('.delete-employee-deduction');
    if (editButton) {
      editButton.addEventListener('click', () => editEmployeeDeductionAssignment(item.id));
    }
    if (deleteButton) {
      deleteButton.addEventListener('click', () => deleteEmployeeDeductionAssignment(item.id));
    }
  });
  paginateTable('employeeDeductionAssignmentTable', 'employeeDeductionAssignmentPagination', true);
}

function editEmployeeDeductionAssignment(id) {
  const assignment = employeeDeductionAssignments.find(item => String(item.id) === String(id));
  if (!assignment) {
    alert('Assignment not found.');
    return;
  }

  openEmployeeDeductionForm(false);
  document.getElementById('employeeDeductionAssignmentId').value = assignment.id;

  const employee = employeesForDeductions.find(emp => emp.name === assignment.employeeName);
  const deductionType = deductionTypesForAssignments.find(type => type.type_of_deduction === assignment.deductionType);

  document.getElementById('deductionEmployeeSelect').value = employee ? String(employee.id) : '';
  document.getElementById('deductionTypeSelect').value = deductionType ? String(deductionType.id) : '';
  document.getElementById('selectedDeductionCost').value = Number(parseFloat(assignment.cost || 0).toFixed(2));
  document.getElementById('selectedDeductionRecurring').checked = assignment.recurring == 1;
  document.getElementById('selectedDeductionApplyMonth').value = assignment.applyYearMonth || '';
  document.getElementById('selectedDeductionApplyCutoff').value = assignment.applyCutoffSlot ? String(assignment.applyCutoffSlot) : '';
  toggleDeductionCutoffFieldsByRecurring();
}

async function deleteEmployeeDeductionAssignment(id) {
  if (!confirm('Delete this assigned deduction item?')) {
    return;
  }

  try {
    const response = await fetch('assigned_emp_deduc_api.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `id=${encodeURIComponent(id)}`
    });
    const result = await response.json();
    if (!result.success) {
      throw new Error(result.message || 'Delete failed.');
    }
    fetchEmployeeDeductionAssignments();
  } catch (error) {
    alert(error.message);
  }
}

async function loadEmployeesForDeductions() {
  const tableBody = document.querySelector('#employeeDeductionSearchTable tbody');
  if (!tableBody) return;

  tableBody.innerHTML = '<tr><td colspan="4" class="text-center">Loading employees...</td></tr>';

  try {
    const response = await fetch('../Employee_management/employees_api.php');
    const result = await response.json();
    if (!result.success) {
      throw new Error(result.message || 'Unable to load employees.');
    }

    const rows = result.data || [];
    employeesForDeductions = rows;
    populateDeductionEmployeeSelect();

    if (rows.length === 0) {
      tableBody.innerHTML = '<tr><td colspan="4" class="text-center">No employees found.</td></tr>';
      paginateTable('employeeDeductionSearchTable', 'employeeDeductionSearchPagination', true);
      return;
    }

    tableBody.innerHTML = '';
    rows.forEach(emp => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${emp.id}</td>
        <td>${emp.name}</td>
        <td>${emp.email}</td>
        <td>₱${parseFloat(emp.salary).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
      `;
      tableBody.appendChild(row);
    });
    paginateTable('employeeDeductionSearchTable', 'employeeDeductionSearchPagination', true);
  } catch (error) {
    tableBody.innerHTML = `<tr><td colspan="4" class="text-center text-danger">${error.message}</td></tr>`;
    paginateTable('employeeDeductionSearchTable', 'employeeDeductionSearchPagination', true);
  }
}

async function loadEmployeesForPremiums() {
  const tableBody = document.querySelector('#employeePremiumSearchTable tbody');
  if (!tableBody) return;

  tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Loading employees...</td></tr>';

  try {
    const [employeeResponse, assignedIncomeResponse] = await Promise.all([
      fetch('../Employee_management/employees_api.php'),
      fetch('assigned_emp_inc_api.php')
    ]);

    const result = await employeeResponse.json();
    const assignedIncomeResult = await assignedIncomeResponse.json();

    if (!result.success) {
      throw new Error(result.message || 'Unable to load employees.');
    }
    if (!assignedIncomeResult.success) {
      throw new Error(assignedIncomeResult.message || 'Unable to load assigned employee income data.');
    }

    const nonTaxableCostByEmployee = buildNonTaxableIncomeMap(assignedIncomeResult.data || []);

    const rows = result.data || [];
    employeesForPremiums = rows;

    if (rows.length === 0) {
      tableBody.innerHTML = '<tr><td colspan="6" class="text-center">No employees found.</td></tr>';
      paginateTable('employeePremiumSearchTable', 'employeePremiumSearchPagination', true);
      return;
    }

    const premiumRecords = [];
    tableBody.innerHTML = '';
    rows.forEach(emp => {
      const salaryBiMonthly = Number(emp.salary || 0);
      // Employee salary in this module is bi-monthly; convert to monthly for premium tables.
      const monthlySalary = salaryBiMonthly * 2;
      const employeeNameKey = String(emp.name || '').trim().toLowerCase();
      const nonTaxableIncome = nonTaxableCostByEmployee[employeeNameKey] || 0;
      const {
        sssContribution,
        pagibigContribution,
        philhealthContribution,
        withholdingTax,
        totalDeductions
      } = computePremiumDeductions(monthlySalary, null, nonTaxableIncome);

      // Determine selected period for these premium records (default to current month)
      const periodInput = document.getElementById('premiumDateFrom') || document.getElementById('remittanceSssMonth');
      let periodYear = null;
      let periodMonth = null;
      if (periodInput && periodInput.value) {
        const d = new Date(periodInput.value);
        if (!Number.isNaN(d.getTime())) {
          periodYear = d.getFullYear();
          periodMonth = d.getMonth() + 1;
        } else if (periodInput.value.indexOf('-') !== -1) {
          const parts = periodInput.value.split('-');
          if (parts.length >= 2) {
            periodYear = parseInt(parts[0], 10);
            periodMonth = parseInt(parts[1], 10);
          }
        }
      }

      premiumRecords.push({
        employee_id: Number(emp.id),
        employee_name: emp.name,
        salary: monthlySalary,
        sss: sssContribution === null ? 0 : sssContribution,
        sss_employee: computeSssSplit(sssContribution === null ? 0 : sssContribution).employee,
        sss_employer: computeSssSplit(sssContribution === null ? 0 : sssContribution).employer,
        philhealth: philhealthContribution === null ? 0 : philhealthContribution,
        philhealth_employee: computePhilhealthSplit(philhealthContribution === null ? 0 : philhealthContribution).employee,
        philhealth_employer: computePhilhealthSplit(philhealthContribution === null ? 0 : philhealthContribution).employer,
        pagibig: pagibigContribution === null ? 0 : pagibigContribution,
        pagibig_employee: computePagibigSplit(pagibigContribution === null ? 0 : pagibigContribution).employee,
        pagibig_employer: computePagibigSplit(pagibigContribution === null ? 0 : pagibigContribution).employer,
        withholding_tax: withholdingTax === null ? 0 : withholdingTax,
        total_premium: totalDeductions,
        period_year: periodYear,
        period_month: periodMonth
      });

      const row = document.createElement('tr');
      row.dataset.joinDate = emp.join_date || '';
      row.innerHTML = `
        <td>${emp.id}</td>
        <td>${emp.name}</td>
        <td>${sssContribution === null ? '' : formatCurrency(sssContribution)}</td>
        <td>${philhealthContribution === null ? '' : formatCurrency(philhealthContribution)}</td>
        <td>${pagibigContribution === null ? '' : formatCurrency(pagibigContribution)}</td>
        <td>${formatCurrency(withholdingTax)}</td>
      `;
      tableBody.appendChild(row);
    });

    // Persist computed premiums into a separate table for reporting and auditing.
    try {
      await fetch('premiums_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ records: premiumRecords })
      });
    } catch (persistError) {
      console.error('Unable to persist premiums snapshot:', persistError);
    }
    paginateTable('employeePremiumSearchTable', 'employeePremiumSearchPagination', true);
  } catch (error) {
    tableBody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">${error.message}</td></tr>`;
    paginateTable('employeePremiumSearchTable', 'employeePremiumSearchPagination', true);
  }
}

function filterEmployeesForPremiums() {
  const searchInput = document.getElementById('searchPremiumEmployees');
  const dateFromInput = document.getElementById('premiumDateFrom');
  const dateToInput = document.getElementById('premiumDateTo');
  const filter = (searchInput?.value || '').toLowerCase();
  const tableBody = document.querySelector('#employeePremiumSearchTable tbody');
  if (!tableBody) return;
  const rows = tableBody.getElementsByTagName('tr');
  const dateFrom = parseDateInputValue(dateFromInput?.value, false);
  const dateTo = parseDateInputValue(dateToInput?.value, true);

  for (let i = 0; i < rows.length; i++) {
    const cells = rows[i].getElementsByTagName('td');
    if (cells.length >= 6) {
      const name = cells[1].textContent.toLowerCase();
      const sss = cells[2].textContent.toLowerCase();
      const philhealth = cells[3].textContent.toLowerCase();
      const pagibig = cells[4].textContent.toLowerCase();
      const withholdingTax = cells[5].textContent.toLowerCase();
      const textMatches = name.includes(filter) || sss.includes(filter) || philhealth.includes(filter) || pagibig.includes(filter) || withholdingTax.includes(filter);

      const joinDateText = rows[i].dataset.joinDate || '';
      const joinDate = joinDateText ? new Date(`${joinDateText}T00:00:00`) : null;
      const dateMatches = (!dateFrom && !dateTo) || (joinDate && !Number.isNaN(joinDate.getTime()) && (!dateFrom || joinDate >= dateFrom) && (!dateTo || joinDate <= dateTo));

      rows[i].dataset.filterVisible = textMatches && dateMatches ? '1' : '0';
    }
  }

  paginateTable('employeePremiumSearchTable', 'employeePremiumSearchPagination', true);
}

// Load remittances withholding tax for all employees (uses premiums snapshot)
async function loadRemittanceTaxes() {
  const tableBody = document.querySelector('#remittanceTaxTable tbody');
  if (!tableBody) return;

  tableBody.innerHTML = '<tr><td colspan="3" class="text-center">Loading...</td></tr>';

  try {
    let url = 'premiums_api.php';
    const monthInput = document.getElementById('remittanceTaxMonth');
    if (monthInput && monthInput.value) {
      const parts = monthInput.value.split('-');
      if (parts.length === 2) {
        url += `?year=${parts[0]}&month=${parseInt(parts[1], 10)}`;
      }
    }
    const response = await fetch(url);
    const result = await response.json();

    if (!result.success) {
      throw new Error(result.message || 'Unable to load remittances data.');
    }

    const rows = result.data || [];
    if (rows.length === 0) {
      tableBody.innerHTML = '<tr><td colspan="3" class="text-center">No data available.</td></tr>';
      return;
    }

    tableBody.innerHTML = '';
    rows.forEach(r => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${r.employee_id}</td>
        <td>${r.employee_name}</td>
        <td>${formatCurrency(r.withholding_tax || 0)}</td>
      `;
      tableBody.appendChild(tr);
    });
  } catch (error) {
    tableBody.innerHTML = `<tr><td colspan="3" class="text-center text-danger">${error.message}</td></tr>`;
  }
}

function filterRemittanceTaxes() {
  const input = document.getElementById('remittanceTaxSearch');
  if (!input) return;
  const filter = input.value.trim().toLowerCase();
  const tbody = document.querySelector('#remittanceTaxTable tbody');
  if (!tbody) return;

  Array.from(tbody.querySelectorAll('tr')).forEach(row => {
    const cells = row.querySelectorAll('td');
    if (cells.length < 2) return;
    const id = String(cells[0].textContent || '').toLowerCase();
    const name = String(cells[1].textContent || '').toLowerCase();
    const matches = id.includes(filter) || name.includes(filter);
    row.style.display = matches ? '' : 'none';
  });
}

// Load SSS remittances (from premiums snapshot)
async function loadRemittanceSss() {
  const tableBody = document.querySelector('#remittanceSssTable tbody');
  if (!tableBody) return;
  tableBody.innerHTML = '<tr><td colspan="3" class="text-center">Loading...</td></tr>';
  try {
    let url = 'premiums_api.php';
    const monthInput = document.getElementById('remittanceSssMonth');
    if (monthInput && monthInput.value) {
      const parts = monthInput.value.split('-');
      if (parts.length === 2) {
        url += `?year=${parts[0]}&month=${parseInt(parts[1], 10)}`;
      }
    }
    const response = await fetch(url);
    const result = await response.json();
    if (!result.success) throw new Error(result.message || 'Unable to load SSS data.');
    const rows = result.data || [];
    if (rows.length === 0) { tableBody.innerHTML = '<tr><td colspan="3" class="text-center">No data available.</td></tr>'; return; }
    tableBody.innerHTML = '';
    rows.forEach(r => {
      const tr = document.createElement('tr');
      const total = Number(r.sss || 0) || 0;
      const split = {
        employee: Number(r.sss_employee),
        employer: Number(r.sss_employer),
        total
      };
      if (!Number.isFinite(split.employee) || !Number.isFinite(split.employer)) {
        Object.assign(split, computeSssSplit(total));
      }
      tr.innerHTML = `
        <td>${r.employee_id}</td>
        <td>${r.employee_name}</td>
        <td>${split.employee === null ? '' : formatCurrency(split.employee)}</td>
        <td>${split.employer === null ? '' : formatCurrency(split.employer)}</td>
        <td>${split.total === null ? '' : formatCurrency(split.total)}</td>
      `;
      tableBody.appendChild(tr);
    });
  } catch (error) {
    tableBody.innerHTML = `<tr><td colspan="3" class="text-center text-danger">${error.message}</td></tr>`;
  }
}

function filterRemittanceSss() {
  const input = document.getElementById('remittanceSssSearch');
  if (!input) return;
  const filter = input.value.trim().toLowerCase();
  const tbody = document.querySelector('#remittanceSssTable tbody');
  if (!tbody) return;
  Array.from(tbody.querySelectorAll('tr')).forEach(row => {
    const cells = row.querySelectorAll('td');
    if (cells.length < 2) return;
    const id = String(cells[0].textContent || '').toLowerCase();
    const name = String(cells[1].textContent || '').toLowerCase();
    row.style.display = (id.includes(filter) || name.includes(filter)) ? '' : 'none';
  });
}

// Load PhilHealth remittances
async function loadRemittancePhilhealth() {
  const tableBody = document.querySelector('#remittancePhilTable tbody');
  if (!tableBody) return;
  tableBody.innerHTML = '<tr><td colspan="3" class="text-center">Loading...</td></tr>';
  try {
    let url = 'premiums_api.php';
    const monthInput = document.getElementById('remittancePhilMonth');
    if (monthInput && monthInput.value) {
      const parts = monthInput.value.split('-');
      if (parts.length === 2) {
        url += `?year=${parts[0]}&month=${parseInt(parts[1], 10)}`;
      }
    }
    const response = await fetch(url);
    const result = await response.json();
    if (!result.success) throw new Error(result.message || 'Unable to load PhilHealth data.');
    const rows = result.data || [];
    if (rows.length === 0) { tableBody.innerHTML = '<tr><td colspan="3" class="text-center">No data available.</td></tr>'; return; }
    tableBody.innerHTML = '';
    rows.forEach(r => {
      const tr = document.createElement('tr');
      const total = Number(r.philhealth || 0) || 0;
      const split = {
        employee: Number(r.philhealth_employee),
        employer: Number(r.philhealth_employer),
        total
      };
      if (!Number.isFinite(split.employee) || !Number.isFinite(split.employer)) {
        Object.assign(split, computePhilhealthSplit(total));
      }
      tr.innerHTML = `
        <td>${r.employee_id}</td>
        <td>${r.employee_name}</td>
        <td>${split.employee === null ? '' : formatCurrency(split.employee)}</td>
        <td>${split.employer === null ? '' : formatCurrency(split.employer)}</td>
        <td>${split.total === null ? '' : formatCurrency(split.total)}</td>
      `;
      tableBody.appendChild(tr);
    });
  } catch (error) {
    tableBody.innerHTML = `<tr><td colspan="3" class="text-center text-danger">${error.message}</td></tr>`;
  }
}

function filterRemittancePhilhealth() {
  const input = document.getElementById('remittancePhilSearch');
  if (!input) return;
  const filter = input.value.trim().toLowerCase();
  const tbody = document.querySelector('#remittancePhilTable tbody');
  if (!tbody) return;
  Array.from(tbody.querySelectorAll('tr')).forEach(row => {
    const cells = row.querySelectorAll('td');
    if (cells.length < 2) return;
    const id = String(cells[0].textContent || '').toLowerCase();
    const name = String(cells[1].textContent || '').toLowerCase();
    row.style.display = (id.includes(filter) || name.includes(filter)) ? '' : 'none';
  });
}

// Load Pag-IBIG remittances
async function loadRemittancePagibig() {
  const tableBody = document.querySelector('#remittancePagibigTable tbody');
  if (!tableBody) return;
  tableBody.innerHTML = '<tr><td colspan="3" class="text-center">Loading...</td></tr>';
  try {
    let url = 'premiums_api.php';
    const monthInput = document.getElementById('remittancePagibigMonth');
    if (monthInput && monthInput.value) {
      const parts = monthInput.value.split('-');
      if (parts.length === 2) {
        url += `?year=${parts[0]}&month=${parseInt(parts[1], 10)}`;
      }
    }
    const response = await fetch(url);
    const result = await response.json();
    if (!result.success) throw new Error(result.message || 'Unable to load Pag-IBIG data.');
    const rows = result.data || [];
    if (rows.length === 0) { tableBody.innerHTML = '<tr><td colspan="3" class="text-center">No data available.</td></tr>'; return; }
    tableBody.innerHTML = '';
    rows.forEach(r => {
      const tr = document.createElement('tr');
      const total = Number(r.pagibig || 0) || 0;
      const split = {
        employee: Number(r.pagibig_employee),
        employer: Number(r.pagibig_employer),
        total
      };
      if (!Number.isFinite(split.employee) || !Number.isFinite(split.employer)) {
        Object.assign(split, computePagibigSplit(total));
      }
      tr.innerHTML = `
        <td>${r.employee_id}</td>
        <td>${r.employee_name}</td>
        <td>${split.employee === null ? '' : formatCurrency(split.employee)}</td>
        <td>${split.employer === null ? '' : formatCurrency(split.employer)}</td>
        <td>${split.total === null ? '' : formatCurrency(split.total)}</td>
      `;
      tableBody.appendChild(tr);
    });
  } catch (error) {
    tableBody.innerHTML = `<tr><td colspan="3" class="text-center text-danger">${error.message}</td></tr>`;
  }
}

function filterRemittancePagibig() {
  const input = document.getElementById('remittancePagibigSearch');
  if (!input) return;
  const filter = input.value.trim().toLowerCase();
  const tbody = document.querySelector('#remittancePagibigTable tbody');
  if (!tbody) return;
  Array.from(tbody.querySelectorAll('tr')).forEach(row => {
    const cells = row.querySelectorAll('td');
    if (cells.length < 2) return;
    const id = String(cells[0].textContent || '').toLowerCase();
    const name = String(cells[1].textContent || '').toLowerCase();
    row.style.display = (id.includes(filter) || name.includes(filter)) ? '' : 'none';
  });
}

function filterEmployeesForDeductions() {
  const searchInput = document.getElementById('searchDeductions');
  if (!searchInput) return;
  searchPayrollDataTable('employeeDeductionSearchTable', searchInput.value);
}

async function fetchEmployeeDeductionAssignments() {
  try {
    const [assignmentResponse, deductionTypeResponse] = await Promise.all([
      fetch('assigned_emp_deduc_api.php'),
      fetch('emp_deduc_type_api.php')
    ]);

    if (!assignmentResponse.ok) {
      throw new Error(`HTTP error! status: ${assignmentResponse.status}`);
    }

    const assignmentData = await assignmentResponse.json();
    const deductionTypeData = deductionTypeResponse.ok ? await deductionTypeResponse.json() : { success: false, data: [] };

    if (assignmentData.success) {
      const deductionTypeMap = {};
      if (deductionTypeData.success) {
        (deductionTypeData.data || []).forEach(typeItem => {
          const typeNameKey = String(typeItem.type_of_deduction || '').trim().toLowerCase();
          if (!typeNameKey) {
            return;
          }
          deductionTypeMap[typeNameKey] = typeItem;
        });
      }

      employeeDeductionAssignments = assignmentData.data.map(item => {
        const typeNameKey = String(item.type_of_deduction || '').trim().toLowerCase();
        const latestType = deductionTypeMap[typeNameKey];

        return {
        id: item.id,
        employeeName: item.name,
        deductionType: item.type_of_deduction,
        cost: item.cost,
        recurring: Number(item.recurring) === 1 ? 1 : 0,
        applyYearMonth: item.apply_year_month || null,
        applyCutoffSlot: item.apply_cutoff_slot || null
      };
      });
      renderEmployeeDeductionAssignments();
    } else {
      console.error('Failed to fetch assignments:', assignmentData.message);
      alert(`Error: ${assignmentData.message}`);
    }
  } catch (error) {
    console.error('Error fetching assignments:', error);
    alert(`Error: ${error.message}`);
  }
}

function openEmployeeIncomeForm(reset = true) {
  if (reset) {
    document.getElementById('employeeIncomeAssignmentId').value = '';
    document.getElementById('incomeEmployeeSelect').value = '';
    document.getElementById('incomeTypeSelect').value = '';
    document.getElementById('selectedIncomeCost').value = '';
    document.getElementById('selectedIncomeTaxable').checked = false;
    document.getElementById('selectedIncome13th').checked = false;
    document.getElementById('selectedIncomeRecurring').checked = false;
    document.getElementById('selectedIncomeApplyMonth').value = '';
    document.getElementById('selectedIncomeApplyCutoff').value = '';
  }

  toggleIncomeCutoffFieldsByRecurring();

  const modalElement = document.getElementById('employeeIncomeModal');
  const modal = new bootstrap.Modal(modalElement);
  modal.show();
}

function hideEmployeeIncomeForm() {
  const modalElement = document.getElementById('employeeIncomeModal');
  if (modalElement) {
    const modal = bootstrap.Modal.getInstance(modalElement);
    if (modal) {
      modal.hide();
    }
  }
}

function updateSelectedIncomeDetails() {
  const select = document.getElementById('incomeTypeSelect');
  const costInput = document.getElementById('selectedIncomeCost');
  const taxableCheckbox = document.getElementById('selectedIncomeTaxable');
  const included13Checkbox = document.getElementById('selectedIncome13th');
  const recurringCheckbox = document.getElementById('selectedIncomeRecurring');
  if (!select || !costInput || !taxableCheckbox || !included13Checkbox || !recurringCheckbox) return;

  const selectedOption = select.options[select.selectedIndex];
  if (!selectedOption || !selectedOption.value) {
    costInput.value = '';
    taxableCheckbox.checked = false;
    included13Checkbox.checked = false;
    recurringCheckbox.checked = false;
    toggleIncomeCutoffFieldsByRecurring();
    return;
  }

  costInput.value = Number(parseFloat(selectedOption.dataset.cost || 0).toFixed(2));
  taxableCheckbox.checked = selectedOption.dataset.taxable === '1';
  included13Checkbox.checked = selectedOption.dataset.includedIn13 === '1';
  recurringCheckbox.checked = selectedOption.dataset.recurring === '1';
  toggleIncomeCutoffFieldsByRecurring();
}

function toggleIncomeCutoffFieldsByRecurring() {
  const recurringCheckbox = document.getElementById('selectedIncomeRecurring');
  const applyMonthInput = document.getElementById('selectedIncomeApplyMonth');
  const applyCutoffSelect = document.getElementById('selectedIncomeApplyCutoff');
  if (!recurringCheckbox || !applyMonthInput || !applyCutoffSelect) return;

  const isRecurring = recurringCheckbox.checked;
  applyMonthInput.disabled = isRecurring;
  applyCutoffSelect.disabled = isRecurring;

  if (isRecurring) {
    applyMonthInput.value = '';
    applyCutoffSelect.value = '';
  }
}

function saveEmployeeIncomeAssignment() {
  const assignmentIdInput = document.getElementById('employeeIncomeAssignmentId');
  const employeeSelect = document.getElementById('incomeEmployeeSelect');
  const incomeTypeSelect = document.getElementById('incomeTypeSelect');
  const costInput = document.getElementById('selectedIncomeCost');
  const taxableCheckbox = document.getElementById('selectedIncomeTaxable');
  const included13Checkbox = document.getElementById('selectedIncome13th');
  const recurringCheckbox = document.getElementById('selectedIncomeRecurring');
  const applyMonthInput = document.getElementById('selectedIncomeApplyMonth');
  const applyCutoffSelect = document.getElementById('selectedIncomeApplyCutoff');

  if (!employeeSelect || !incomeTypeSelect) return;
  if (!employeeSelect.value || !incomeTypeSelect.value) {
    alert('Please select both an employee and an income type.');
    return;
  }

  const employee = employeesForIncome.find(emp => String(emp.id) === employeeSelect.value);
  const incomeType = incomeTypesForAssignments.find(item => String(item.id) === incomeTypeSelect.value);
  if (!employee || !incomeType) {
    alert('Selected employee or income type is invalid.');
    return;
  }

  if (!costInput || String(costInput.value).trim() === '') {
    alert('Please enter an income cost.');
    return;
  }

  const parsedCost = parseMoneyInput(costInput.value);
  if (!Number.isFinite(parsedCost) || parsedCost < 0) {
    alert('Please enter a valid income cost.');
    return;
  }

  const isRecurring = recurringCheckbox?.checked === true;
  const applyYearMonth = (applyMonthInput?.value || '').trim();
  const applyCutoffSlot = applyCutoffSelect?.value ? Number(applyCutoffSelect.value) : null;

  if (!isRecurring) {
    if (!applyYearMonth || !applyCutoffSlot) {
      alert('Please select apply month and cutoff for non-recurring income.');
      return;
    }
  }

  // Push values into the assigned_emp_inc table using the assigned_emp_inc_api
  const payload = {
    name: employee.name,
    type_of_income: incomeType.type_of_income,
    cost: parsedCost,
    taxable: taxableCheckbox.checked ? 1 : 0,
    month_13th: included13Checkbox.checked ? 1 : 0,
    recurring: isRecurring ? 1 : 0,
    apply_year_month: isRecurring ? null : applyYearMonth,
    apply_cutoff_slot: isRecurring ? null : applyCutoffSlot
  };

  if (assignmentIdInput && assignmentIdInput.value) {
    payload.id = Number(assignmentIdInput.value);
  }

  fetch('assigned_emp_inc_api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  })
    .then(response => response.json())
    .then(result => {
      if (!result.success) {
        throw new Error(result.message || 'Failed to save assignment.');
      }
      alert('Assignment saved successfully!');
      hideEmployeeIncomeForm();
      fetchEmployeeIncomeAssignments();
    })
    .catch(error => {
      console.error('Error saving assignment:', error);
      alert(`Error: ${error.message}`);
    });
}

function renderEmployeeIncomeAssignments() {
  const tableBody = document.querySelector('#employeeIncomeAssignmentTable tbody');
  if (!tableBody) return;
  if (employeeIncomeAssignments.length === 0) {
    tableBody.innerHTML = '<tr><td colspan="8" class="text-center">No assigned income items yet.</td></tr>';
    paginateTable('employeeIncomeAssignmentTable', 'employeeIncomeAssignmentPagination', true);
    return;
  }

  tableBody.innerHTML = '';
  employeeIncomeAssignments.forEach(item => {
    const isTaxable = Number(item.taxable) === 1;
    const isIncludedIn13th = Number(item.included13) === 1;
    const isRecurring = Number(item.recurring) === 1;
    const cutoffScopeText = isRecurring
      ? 'Every cutoff'
      : (item.applyYearMonth && Number(item.applyCutoffSlot) > 0
        ? `${item.applyYearMonth} (${Number(item.applyCutoffSlot) === 1 ? '1st' : '2nd'} cutoff)`
        : 'Any cutoff');
    const row = document.createElement('tr');
    row.innerHTML = `
      <td>${item.employeeName}</td>
      <td>${item.incomeType}</td>
      <td>₱${parseFloat(item.cost).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
      <td>${isTaxable ? 'Yes' : 'No'}</td>
      <td>${isIncludedIn13th ? 'Yes' : 'No'}</td>
      <td>${isRecurring ? 'Yes' : 'No'}</td>
      <td>${cutoffScopeText}</td>
      <td>
        <button type="button" class="btn btn-sm btn-warning edit-employee-income">Edit</button>
        <button type="button" class="btn btn-sm btn-danger delete-employee-income">Delete</button>
      </td>
    `;
    tableBody.appendChild(row);

    const editButton = row.querySelector('.edit-employee-income');
    const deleteButton = row.querySelector('.delete-employee-income');
    if (editButton) {
      editButton.addEventListener('click', () => editEmployeeIncomeAssignment(item.id));
    }
    if (deleteButton) {
      deleteButton.addEventListener('click', () => deleteEmployeeIncomeAssignment(item.id));
    }
  });

  paginateTable('employeeIncomeAssignmentTable', 'employeeIncomeAssignmentPagination', true);
}

function editEmployeeIncomeAssignment(id) {
  const assignment = employeeIncomeAssignments.find(item => String(item.id) === String(id));
  if (!assignment) {
    alert('Assignment not found.');
    return;
  }

  openEmployeeIncomeForm(false);
  document.getElementById('employeeIncomeAssignmentId').value = assignment.id;

  const employee = employeesForIncome.find(emp => emp.name === assignment.employeeName);
  const incomeType = incomeTypesForAssignments.find(type => type.type_of_income === assignment.incomeType);

  document.getElementById('incomeEmployeeSelect').value = employee ? String(employee.id) : '';
  document.getElementById('incomeTypeSelect').value = incomeType ? String(incomeType.id) : '';
  document.getElementById('selectedIncomeCost').value = Number(parseFloat(assignment.cost || 0).toFixed(2));
  document.getElementById('selectedIncomeTaxable').checked = assignment.taxable == 1;
  document.getElementById('selectedIncome13th').checked = assignment.included13 == 1;
  document.getElementById('selectedIncomeRecurring').checked = assignment.recurring == 1;
  document.getElementById('selectedIncomeApplyMonth').value = assignment.applyYearMonth || '';
  document.getElementById('selectedIncomeApplyCutoff').value = assignment.applyCutoffSlot ? String(assignment.applyCutoffSlot) : '';
  toggleIncomeCutoffFieldsByRecurring();
}

async function deleteEmployeeIncomeAssignment(id) {
  if (!confirm('Delete this assigned income item?')) {
    return;
  }

  try {
    const response = await fetch('assigned_emp_inc_api.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `id=${encodeURIComponent(id)}`
    });
    const result = await response.json();
    if (!result.success) {
      throw new Error(result.message || 'Delete failed.');
    }
    fetchEmployeeIncomeAssignments();
  } catch (error) {
    alert(error.message);
  }
}

function filterIncomeTypes() {
  const searchInput = document.getElementById('searchIncomeTypes');
  if (!searchInput) return;
  searchPayrollDataTable('incomeTypeTable', searchInput.value);
}

function filterProcess13() {
  const searchInput = document.getElementById('searchProcess13');
  if (!searchInput) return;
  searchPayrollDataTable('process13Table', searchInput.value);
}

function filter13MonthListing() {
  const searchInput = document.getElementById('search13MonthListing');
  if (!searchInput) return;
  searchPayrollDataTable('month13ListingTable', searchInput.value);
}

function filterAttendance() {
  const searchInput = document.getElementById('searchAttendance');
  const dateFromInput = document.getElementById('attendanceDateFrom');
  const dateToInput = document.getElementById('attendanceDateTo');
  if (!searchInput) return;

  const filter = searchInput.value.toLowerCase();
  const dateFrom = parseDateInputValue(dateFromInput?.value, false);
  const dateTo = parseDateInputValue(dateToInput?.value, true);

  filteredAttendanceRows = attendanceRows.filter(item => {
    const id = String(item.id ?? '').toLowerCase();
    const name = String(item.name ?? '').toLowerCase();
    const date = String(item.date ?? '').toLowerCase();
    const clockIn = String(item.clockIn ?? '').toLowerCase();
    const clockOut = String(item.clockOut ?? '').toLowerCase();
    const clockInStatus = String(item.clockInStatus ?? '').toLowerCase();
    const clockOutStatus = String(item.clockOutStatus ?? '').toLowerCase();
    const duration = String(item.duration ?? '').toLowerCase();
    const ao = String(item.ao ?? '').toLowerCase();

    const textMatches = (
      id.includes(filter) ||
      name.includes(filter) ||
      date.includes(filter) ||
      clockIn.includes(filter) ||
      clockOut.includes(filter) ||
      clockInStatus.includes(filter) ||
      clockOutStatus.includes(filter) ||
      duration.includes(filter) ||
      ao.includes(filter)
    );

    if (!textMatches) {
      return false;
    }

    if (!dateFrom && !dateTo) {
      return true;
    }

    const itemDate = item.date ? new Date(`${item.date}T00:00:00`) : null;
    if (!itemDate || Number.isNaN(itemDate.getTime())) {
      return false;
    }

    if (dateFrom && itemDate < dateFrom) {
      return false;
    }

    if (dateTo && itemDate > dateTo) {
      return false;
    }

    return true;
  });

  attendanceCurrentPage = 1;
  renderAttendanceTable();
}

function renderAttendanceTable() {
  const tableBody = document.querySelector('#attendanceTable tbody');
  if (!tableBody) return;

  const totalRows = filteredAttendanceRows.length;
  if (totalRows === 0) {
    tableBody.innerHTML = '<tr><td colspan="9" class="text-center">No attendance records found.</td></tr>';
    renderAttendancePagination(0);
    return;
  }

  const totalPages = Math.ceil(totalRows / attendancePageSize);
  if (attendanceCurrentPage > totalPages) {
    attendanceCurrentPage = totalPages;
  }

  const startIndex = (attendanceCurrentPage - 1) * attendancePageSize;
  const endIndex = startIndex + attendancePageSize;
  const pageRows = filteredAttendanceRows.slice(startIndex, endIndex);

  tableBody.innerHTML = '';
  pageRows.forEach(item => {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td>${item.id ?? ''}</td>
      <td>${item.name ?? ''}</td>
      <td>${item.date ?? ''}</td>
      <td>${item.clockIn ?? ''}</td>
      <td>${item.clockOut ?? ''}</td>
      <td>${item.clockInStatus ?? ''}</td>
      <td>${item.clockOutStatus ?? ''}</td>
      <td>${item.duration ?? ''}</td>
      <td>${item.ao ?? ''}</td>
    `;
    tableBody.appendChild(row);
  });

  renderAttendancePagination(totalPages);
  renderAttendanceSummaryTable();
}

function renderAttendanceSummaryTable() {
  const tableBody = document.querySelector('#attendanceSummaryTable tbody');
  if (!tableBody) return;

  const summaryByEmployeeAndPeriod = new Map();

  filteredAttendanceRows.forEach(item => {
    const employeeId = item.id ?? getAttendanceEmployeeId(item);
    const employeeName = item.name ?? item.employee_name ?? '';
    const itemDate = item.date ? new Date(`${item.date}T00:00:00`) : getAttendanceItemDate(item);
    if (!itemDate || Number.isNaN(itemDate.getTime())) {
      return;
    }

    const periodLabel = getAttendanceHalfMonthLabel(itemDate);
    const durationMinutes = getAttendanceDurationMinutes(item);
    const isAO = Number(item.ao) === 1;
    const effectiveMinutes = durationMinutes > 480 ? (isAO ? durationMinutes : 480) : durationMinutes;
    const summaryKey = `${employeeId}::${employeeName}::${periodLabel}`;
    const existing = summaryByEmployeeAndPeriod.get(summaryKey) || {
      id: employeeId,
      name: employeeName,
      date: periodLabel,
      totalHours: 0
    };
    existing.totalHours += effectiveMinutes / 60;
    summaryByEmployeeAndPeriod.set(summaryKey, existing);
  });

  const summaryRows = Array.from(summaryByEmployeeAndPeriod.values());
  if (summaryRows.length === 0) {
    tableBody.innerHTML = '<tr><td colspan="4" class="text-center">No attendance summary available.</td></tr>';
    return;
  }

  tableBody.innerHTML = '';
  summaryRows.forEach(item => {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td>${item.id}</td>
      <td>${item.name}</td>
      <td>${item.totalHours.toFixed(2)}</td>
      <td>${item.date}</td>
    `;
    tableBody.appendChild(row);
  });
}

function getAttendanceEmployeeId(item = {}) {
  return String(item.Emp_id ?? item.id ?? '').trim();
}

function getAttendanceItemDate(item = {}) {
  const rawDate = item.Date ?? item.date ?? '';
  if (!rawDate) {
    return null;
  }

  const normalized = String(rawDate).trim();
  const parsedDate = /^\d{4}-\d{2}-\d{2}/.test(normalized)
    ? new Date(`${normalized.slice(0, 10)}T00:00:00`)
    : new Date(normalized);
  return Number.isNaN(parsedDate.getTime()) ? null : parsedDate;
}

function getAttendanceDurationMinutes(item = {}) {
  const directDuration = Number(item.Duration ?? item.duration);
  if (Number.isFinite(directDuration) && directDuration > 0) {
    return directDuration;
  }

  const clockInValue = item.Clock_in ?? item.clockIn;
  const clockOutValue = item.Clock_out ?? item.clockOut;
  if (!clockInValue || !clockOutValue) {
    return 0;
  }

  const clockIn = new Date(clockInValue);
  const clockOut = new Date(clockOutValue);
  if (Number.isNaN(clockIn.getTime()) || Number.isNaN(clockOut.getTime())) {
    return 0;
  }

  let durationMinutes = (clockOut.getTime() - clockIn.getTime()) / 60000;
  if (durationMinutes < 0) {
    durationMinutes += 1440;
  }

  return Number.isFinite(durationMinutes) && durationMinutes > 0 ? durationMinutes : 0;
}

function getAttendanceHalfMonthLabel(date) {
  const month = date.getMonth() + 1;
  const year = String(date.getFullYear()).slice(-2);
  const day = date.getDate();

  if (day <= 15) {
    return `${month}-1-${year} - ${month}-15-${year}`;
  }

  const lastDayOfMonth = new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
  return `${month}-16-${year} - ${month}-${lastDayOfMonth}-${year}`;
}

function renderAttendancePagination(totalPages) {
  const paginationContainer = document.getElementById('attendancePagination');
  if (!paginationContainer) return;

  if (totalPages <= 1) {
    const shownRows = filteredAttendanceRows.length;
    paginationContainer.innerHTML = `<small class="text-muted">Showing ${shownRows} of ${attendanceRows.length} records</small>`;
    return;
  }

  const pageSequence = getCompactPageSequence(totalPages, attendanceCurrentPage);
  let pageButtons = '';
  pageSequence.forEach(item => {
    if (typeof item === 'number') {
      pageButtons += `
        <button
          type="button"
          class="btn btn-sm ${item === attendanceCurrentPage ? 'btn-primary' : 'btn-outline-primary'}"
          onclick="goToAttendancePage(${item})"
        >${item}</button>
      `;
      return;
    }

    pageButtons += '<span class="px-1 align-self-center text-muted">...</span>';
  });

  const startIndex = (attendanceCurrentPage - 1) * attendancePageSize + 1;
  const endIndex = Math.min(attendanceCurrentPage * attendancePageSize, filteredAttendanceRows.length);

  paginationContainer.innerHTML = `
    <small class="text-muted">Showing ${startIndex}-${endIndex} of ${filteredAttendanceRows.length} records</small>
    <div class="d-flex gap-1 flex-wrap justify-content-end">
      <button
        type="button"
        class="btn btn-sm btn-outline-secondary"
        onclick="goToAttendancePage(${attendanceCurrentPage - 1})"
        ${attendanceCurrentPage === 1 ? 'disabled' : ''}
      >Previous</button>
      ${pageButtons}
      <button
        type="button"
        class="btn btn-sm btn-outline-secondary"
        onclick="goToAttendancePage(${attendanceCurrentPage + 1})"
        ${attendanceCurrentPage === totalPages ? 'disabled' : ''}
      >Next</button>
    </div>
  `;
}

function goToAttendancePage(page) {
  const totalPages = Math.max(1, Math.ceil(filteredAttendanceRows.length / attendancePageSize));
  const targetPage = Math.min(Math.max(page, 1), totalPages);
  if (targetPage === attendanceCurrentPage) return;
  attendanceCurrentPage = targetPage;
  renderAttendanceTable();
}

async function loadAttendance() {
  const tableBody = document.querySelector('#attendanceTable tbody');
  if (!tableBody) return;

  tableBody.innerHTML = '<tr><td colspan="9" class="text-center">Loading attendance...</td></tr>';

  try {
    const response = await fetch('attendance_api.php');
    const result = await response.json();

    if (!result.success) {
      throw new Error(result.message || 'Unable to load attendance.');
    }

    const rows = result.data || [];
    if (rows.length === 0) {
      attendanceRows = [];
      filteredAttendanceRows = [];
      attendanceCurrentPage = 1;
      tableBody.innerHTML = '<tr><td colspan="9" class="text-center">No attendance records found.</td></tr>';
      renderAttendancePagination(0);
      return;
    }

    attendanceRows = rows.map(item => ({
      id: item.Emp_id,
      name: item.employee_name,
      date: item.Date,
      clockIn: item.Clock_in,
      clockOut: item.Clock_out,
      clockInStatus: item.Clockin_status,
      clockOutStatus: item.Clockout_status,
      duration: item.Duration,
      ao: item.AO
    }));
    filteredAttendanceRows = [...attendanceRows];
    attendanceCurrentPage = 1;
    renderAttendanceTable();
  } catch (error) {
    tableBody.innerHTML = `<tr><td colspan="9" class="text-center text-danger">${error.message}</td></tr>`;
    const paginationContainer = document.getElementById('attendancePagination');
    if (paginationContainer) {
      paginationContainer.innerHTML = '';
    }
  }
}

async function loadProcess13ComputedData() {
  const tableBody = document.querySelector('#process13Table tbody');
  const yearInput = document.getElementById('month13ListingYear');
  const selectedYear = Number(yearInput?.value || new Date().getFullYear());

  if (!Number.isInteger(selectedYear) || selectedYear < 1900 || selectedYear > 9999) {
    alert('Please enter a valid year.');
    return;
  }

  if (!tableBody) return;

  tableBody.innerHTML = '<tr><td colspan="5" class="text-center">Loading computed 13th month data...</td></tr>';

  try {
    const response = await fetch(`process_13th_api.php?year=${encodeURIComponent(selectedYear)}`);
    const result = await response.json();

    if (!result.success) {
      throw new Error(result.message || 'Unable to load 13th month data.');
    }

    const rows = result.data || [];

    if (rows.length === 0) {
      tableBody.innerHTML = '<tr><td colspan="5" class="text-center">No employees found for the selected year.</td></tr>';
      paginateTable('process13Table', 'process13Pagination', true);
      return;
    }

    tableBody.innerHTML = '';
    rows.forEach(emp => {
      const monthlySalary = Number(emp.salary || 0);
      const totalBasicSalaryEarned = Number(emp.total_basic_salary_earned || 0);
      const month13Pay = Number(emp.month_13_pay || 0);

      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${emp.id}</td>
        <td>${emp.name}</td>
        <td>${formatCurrency(monthlySalary)}</td>
        <td>${formatCurrency(totalBasicSalaryEarned)}</td>
        <td>${formatCurrency(month13Pay)}</td>
      `;
      tableBody.appendChild(row);
    });
    paginateTable('process13Table', 'process13Pagination', true);
  } catch (error) {
    tableBody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">${error.message}</td></tr>`;
    paginateTable('process13Table', 'process13Pagination', true);
  }
}

async function loadEmployeesForProcess13() {
  const tableBody = document.querySelector('#process13Table tbody');
  if (!tableBody) return;

  tableBody.innerHTML = '<tr><td colspan="5" class="text-center">Loading employees...</td></tr>';

  try {
    const currentYear = new Date().getFullYear();
    const response = await fetch(`process_13th_api.php?year=${encodeURIComponent(currentYear)}`);
    const result = await response.json();

    if (!result.success) {
      throw new Error(result.message || 'Unable to load 13th month data.');
    }

    const rows = result.data || [];

    if (rows.length === 0) {
      tableBody.innerHTML = '<tr><td colspan="5" class="text-center">No employees found.</td></tr>';
      paginateTable('process13Table', 'process13Pagination', true);
      return;
    }

    tableBody.innerHTML = '';
    rows.forEach(emp => {
      const monthlySalary = Number(emp.salary || 0);
      const totalBasicSalaryEarned = Number(emp.total_basic_salary_earned || 0);
      const month13Pay = Number(emp.month_13_pay || 0);

      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${emp.id}</td>
        <td>${emp.name}</td>
        <td>${formatCurrency(monthlySalary)}</td>
        <td>${formatCurrency(totalBasicSalaryEarned)}</td>
        <td>${formatCurrency(month13Pay)}</td>
      `;
      tableBody.appendChild(row);
    });
    paginateTable('process13Table', 'process13Pagination', true);
  } catch (error) {
    tableBody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">${error.message}</td></tr>`;
    paginateTable('process13Table', 'process13Pagination', true);
  }
}

async function load13MonthListing() {
  const tableBody = document.querySelector('#month13ListingTable tbody');
  if (!tableBody) return;

  const yearInput = document.getElementById('month13ListingYearFilter');
  const selectedYear = Number(yearInput?.value || new Date().getFullYear());
  if (!Number.isInteger(selectedYear) || selectedYear < 1900 || selectedYear > 9999) {
    alert('Please enter a valid year.');
    return;
  }

  tableBody.innerHTML = '<tr><td colspan="7" class="text-center">Loading 13th month listing...</td></tr>';

  try {
    const response = await fetch(`process_13th_api.php?year=${encodeURIComponent(selectedYear)}&stored=1`);
    const result = await response.json();

    if (!result.success) {
      throw new Error(result.message || 'Unable to load 13th month listing.');
    }

    const rows = result.data || [];
    month13ListingRows = rows;

    if (rows.length === 0) {
      tableBody.innerHTML = '<tr><td colspan="7" class="text-center">No 13th month records found for the selected year.</td></tr>';
      paginateTable('month13ListingTable', 'month13ListingPagination', true);
      return;
    }

    tableBody.innerHTML = '';
    rows.forEach(item => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${item.id}</td>
        <td>${item.name}</td>
        <td>${item.process_year}</td>
        <td>${formatCurrency(Number(item.salary || 0))}</td>
        <td>${formatCurrency(Number(item.total_basic_salary_earned || 0))}</td>
        <td>${formatCurrency(Number(item.month_13_pay || 0))}</td>
        <td>${item.computed_at || ''}</td>
      `;
      tableBody.appendChild(row);
    });

    paginateTable('month13ListingTable', 'month13ListingPagination', true);
  } catch (error) {
    tableBody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">${error.message}</td></tr>`;
    paginateTable('month13ListingTable', 'month13ListingPagination', true);
  }
}

async function process13MonthListingYear() {
  const yearInput = document.getElementById('month13ListingYear');
  const processButton = document.getElementById('process13ListingBtn');
  const selectedYear = Number(yearInput?.value || new Date().getFullYear());

  if (!Number.isInteger(selectedYear) || selectedYear < 1900 || selectedYear > 9999) {
    alert('Please enter a valid year.');
    return;
  }

  if (processButton) {
    processButton.disabled = true;
    processButton.textContent = 'Processing...';
  }

  try {
    const response = await fetch(`process_13th_api.php?year=${encodeURIComponent(selectedYear)}`);
    const result = await response.json();

    if (!result.success) {
      throw new Error(result.message || 'Unable to process 13th month data.');
    }

    // Update the Listing tab's year filter to match the processed year
    const listingYearInput = document.getElementById('month13ListingYearFilter');
    if (listingYearInput) {
      listingYearInput.value = selectedYear;
    }

    // After processing, navigate to the Listing tab; it will load its own data on render.
    // Find and click the 13th Month Listing button to show the results
    const listingButton = document.querySelector('button[onclick*="13_month_listing"]');
    if (listingButton) {
      listingButton.click();
    }
    
    alert(`13th month data for ${selectedYear} has been processed and displayed in the Listing tab.`);
  } catch (error) {
    alert(error.message);
  } finally {
    if (processButton) {
      processButton.disabled = false;
      processButton.textContent = 'Process Year';
    }
  }
}

function export13MonthListingCsv() {
  if (!Array.isArray(month13ListingRows) || month13ListingRows.length === 0) {
    alert('No 13th month listing data to export.');
    return;
  }

  const yearInput = document.getElementById('month13ListingYearFilter');
  const selectedYear = Number(yearInput?.value || new Date().getFullYear());
  const headers = [
    'Employee ID',
    'Employee Name',
    'Year',
    'Monthly Salary',
    'Total Basic Salary Earned (Jan-Dec)',
    '13th Month Pay',
    'Computed At'
  ];

  const escapeCsv = (value) => {
    const text = String(value ?? '');
    const escaped = text.replace(/"/g, '""');
    return `"${escaped}"`;
  };

  const lines = [headers.map(escapeCsv).join(',')];
  month13ListingRows.forEach(item => {
    lines.push([
      item.id,
      item.name,
      item.process_year,
      Number(item.salary || 0).toFixed(2),
      Number(item.total_basic_salary_earned || 0).toFixed(2),
      Number(item.month_13_pay || 0).toFixed(2),
      item.computed_at || ''
    ].map(escapeCsv).join(','));
  });

  const csvContent = lines.join('\n');
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = `13th_month_listing_${selectedYear}.csv`;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
}

async function loadEmployees() {
  const tableBody = document.querySelector('#employeeSearchTable tbody');
  if (!tableBody) return;

  tableBody.innerHTML = '<tr><td colspan="4" class="text-center">Loading employees...</td></tr>';

  try {
    const response = await fetch('../Employee_management/employees_api.php');
    const result = await response.json();
    if (!result.success) {
      throw new Error(result.message || 'Unable to load employees.');
    }

    const rows = result.data || [];
    employeesForIncome = rows;
    populateEmployeeSelect();
    if (rows.length === 0) {
      tableBody.innerHTML = '<tr><td colspan="4" class="text-center">No employees found.</td></tr>';
      paginateTable('employeeSearchTable', 'employeeSearchPagination', true);
      return;
    }

    tableBody.innerHTML = '';
    rows.forEach(emp => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${emp.id}</td>
        <td>${emp.name}</td>
        <td>${emp.email}</td>
        <td>₱${parseFloat(emp.salary).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
      `;
      tableBody.appendChild(row);
    });
    paginateTable('employeeSearchTable', 'employeeSearchPagination', true);
  } catch (error) {
    tableBody.innerHTML = `<tr><td colspan="4" class="text-center text-danger">${error.message}</td></tr>`;
    paginateTable('employeeSearchTable', 'employeeSearchPagination', true);
  }
}

async function buildCutoffPayrollRows(dateFrom = null, dateTo = null, fromRaw = '', toRaw = '', applyCarry = false) {
  const [employeeResponse, attendanceResponse, assignedIncomeResponse, assignedDeductionResponse, incomeTypesResponse, deductionTypesResponse] = await Promise.all([
    fetch('../Employee_management/employees_api.php'),
    fetch('attendance_api.php'),
    fetch('assigned_emp_inc_api.php'),
    fetch('assigned_emp_deduc_api.php'),
    fetch('emp_inc_type_api.php'),
    fetch('emp_deduc_type_api.php')
  ]);

  const employeeResult = await employeeResponse.json();
  const attendanceResult = attendanceResponse.ok ? await attendanceResponse.json() : { success: false, data: [] };
  const assignedIncomeResult = assignedIncomeResponse.ok ? await assignedIncomeResponse.json() : { success: false, data: [] };
  const incomeTypesResult = incomeTypesResponse.ok ? await incomeTypesResponse.json() : { success: false, data: [] };
  const assignedDeductionResult = assignedDeductionResponse.ok ? await assignedDeductionResponse.json() : { success: false, data: [] };
  const deductionTypesResult = deductionTypesResponse.ok ? await deductionTypesResponse.json() : { success: false, data: [] };

  if (!employeeResult.success) {
    throw new Error(employeeResult.message || 'Unable to load employees.');
  }

  const rows = employeeResult.data || [];
  const attendanceRows = attendanceResult.success ? (attendanceResult.data || []) : [];
  const assignedIncomeData = assignedIncomeResult.success ? (assignedIncomeResult.data || []) : [];
  const incomeTypesData = incomeTypesResult.success ? (incomeTypesResult.data || []) : [];
  const assignedDeductions = assignedDeductionResult.success ? (assignedDeductionResult.data || []) : [];
  const deductionTypes = deductionTypesResult.success ? (deductionTypesResult.data || []) : [];
  
  console.log('=== buildCutoffPayrollRows DEBUG ===');
  console.log('dateFrom:', dateFrom);
  console.log('dateTo:', dateTo);
  console.log('attendanceResult.success:', attendanceResult.success);
  console.log('attendanceResult.message:', attendanceResult.message);
  console.log('attendanceRows count:', attendanceRows.length);
  if (attendanceRows.length > 0) {
    console.log('First attendance record:', attendanceRows[0]);
    console.log('Record keys:', Object.keys(attendanceRows[0]));
    console.log('Emp_id value:', attendanceRows[0].Emp_id);
    console.log('Emp_id type:', typeof attendanceRows[0].Emp_id);
    console.log('Date value:', attendanceRows[0].Date);
    console.log('Duration value:', attendanceRows[0].Duration);
    console.log('Duration type:', typeof attendanceRows[0].Duration);
  } else {
    console.log('WARNING: No attendance rows returned!');
  }
  
  const applicableAssignedIncomeData = filterAssignedIncomeByCutoff(assignedIncomeData, incomeTypesData, dateFrom, dateTo);
  const applicableAssignedDeductionData = filterAssignedDeductionsByCutoff(assignedDeductions, deductionTypes, dateFrom, dateTo);
  const attendanceByEmployee = buildAttendanceWorkMap(attendanceRows, dateFrom, dateTo);
  
  console.log('attendanceByEmployee Map size:', attendanceByEmployee.size);
  Array.from(attendanceByEmployee.entries()).forEach(([empId, data]) => {
    console.log(`  Employee ${empId} (type: ${typeof empId}): ${data.regularMinutes}min regular, ${data.overtimeMinutes}min overtime`);
  });
  const incomeByType = buildIncomeByTypeMap(applicableAssignedIncomeData);
  const additionalIncomeByEmployee = buildAdditionalIncomeSummaryMap(applicableAssignedIncomeData);
  const nonTaxableIncomeByEmployee = buildNonTaxableIncomeMap(applicableAssignedIncomeData);
  const personalCaByEmployee = buildPersonalCaMap(applicableAssignedDeductionData);

  const processedRows = rows.map(emp => {
    const employeeId = String(emp.id);
    const employeeNameKey = String(emp.name || '').trim().toLowerCase();
    const monthlySalary = Number(emp.salary || 0);
    const grossPayPerDay = monthlySalary / 26;
    const hourlyRate = grossPayPerDay / 8;
    const work = attendanceByEmployee.get(employeeId) || { regularMinutes: 0, overtimeMinutes: 0 };
    const hoursWorked = (work.regularMinutes || 0) / 60;
    
    // Detailed logging for first few employees
    if (emp.id <= 5) {
      console.log(`Processing employee ${emp.id} (${emp.name}):`);
      console.log(`  employeeId key: "${employeeId}" (type: ${typeof employeeId})`);
      console.log(`  work data found:`, work);
      console.log(`  hoursWorked: ${hoursWorked}`);
    }
    
    const cutoffSalary = hourlyRate * hoursWorked;
    const totalOtPay = (work.overtimeMinutes / 60) * hourlyRate;
    const employeeIncomeByType = incomeByType[employeeNameKey] || {};
    const additionalIncomeSummary = additionalIncomeByEmployee[employeeNameKey] || { taxable: 0, nonTaxable: 0 };
    const legalHoliday = getComputedIncomeAmountByKeyword(employeeIncomeByType, /legal\s*holiday/i, 2);
    const specialHoliday = getComputedIncomeAmountByKeyword(employeeIncomeByType, /special\s*holiday/i, 1.3);
    const taxableAdditionalIncome = Number(additionalIncomeSummary.taxable) || 0;
    const nonTaxableAdditionalIncome = Number(additionalIncomeSummary.nonTaxable) || 0;
    const nonTaxableIncome = (nonTaxableIncomeByEmployee[employeeNameKey] || 0) + nonTaxableAdditionalIncome;

    const hasWorkedHours = hoursWorked > 0;
    const premium = hasWorkedHours
      ? computePremiumDeductions(monthlySalary, hoursWorked, nonTaxableIncome)
      : {
          sssContribution: 0,
          philhealthContribution: 0,
          pagibigContribution: 0,
          withholdingTax: 0
        };

    const sss = Number(premium.sssContribution) || 0;
    const phlth = Number(premium.philhealthContribution) || 0;
    const pagibig = Number(premium.pagibigContribution) || 0;
    const tax = Number(premium.withholdingTax) || 0;
    const additionalDeductions = hasWorkedHours ? (Number(personalCaByEmployee[employeeNameKey]) || 0) : 0;
    const totalDeduction = sss + phlth + pagibig + tax + additionalDeductions;
    const netPay = cutoffSalary + totalOtPay + legalHoliday + specialHoliday + taxableAdditionalIncome + nonTaxableAdditionalIncome - totalDeduction;

    const currentCutoffKey = makeCutoffKey(fromRaw, toRaw);
    const prevCarry = applyCarry ? (getCarryForCutoff(employeeId, currentCutoffKey) || 0) : 0;
    let displayedNet = netPay - prevCarry;
    let carryOut = 0;

    if (displayedNet < 0) {
      carryOut = -displayedNet;
      displayedNet = 0;
    }

    if (applyCarry) {
      try { localStorage.removeItem(`payroll_carry_${employeeId}_${currentCutoffKey}`); } catch (e) {}
      const nextCutoffKey = computeNextCutoffKey(fromRaw, toRaw);
      if (nextCutoffKey) {
        setCarryForCutoff(employeeId, nextCutoffKey, carryOut);
      }
    }

    return {
      id: emp.id || '',
      name: emp.name || '',
      email: emp.email || '',
      monthlySalary,
      cutoffSalary,
      grossPayPerDay,
      hoursWorked,
      totalOtPay,
      legalHoliday,
      specialHoliday,
      taxableAdditionalIncome,
      nonTaxableAdditionalIncome,
      sss,
      phlth,
      pagibig,
      tax,
      additionalDeductions,
      totalDeduction,
      netPay: displayedNet,
      riceSubsidy: nonTaxableAdditionalIncome,
      electricity: taxableAdditionalIncome,
      personalCa: additionalDeductions
    };
  });

  return { employees: rows, rows: processedRows };
}

function renderEmployeeSalaryRows(rows = []) {
  const tableBody = document.querySelector('#employeeSalaryTable tbody');
  if (!tableBody) return;

  currentEmployeeSalaryRows = Array.isArray(rows) ? rows.map(item => ({ ...item })) : [];

  if (!rows.length) {
    tableBody.innerHTML = '<tr><td colspan="19" class="text-center">No employees found.</td></tr>';
    paginateTable('employeeSalaryTable', 'employeeSalaryPagination', true);
    return;
  }

  tableBody.innerHTML = '';
  rows.forEach(item => {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td>${item.id}</td>
      <td>${item.name}</td>
      <td>${item.email}</td>
      <td>${formatCurrency(item.cutoffSalary)}</td>
      <td>${formatCurrency(item.grossPayPerDay)}</td>
      <td>${Number(item.hoursWorked || 0).toFixed(2)}</td>
      <td>${formatCurrency(item.totalOtPay)}</td>
      <td>${formatCurrency(item.legalHoliday)}</td>
      <td>${formatCurrency(item.specialHoliday)}</td>
      <td>${formatCurrency(item.taxableAdditionalIncome)}</td>
      <td>${formatCurrency(item.nonTaxableAdditionalIncome)}</td>
      <td>${formatCurrency(item.sss)}</td>
      <td>${formatCurrency(item.phlth)}</td>
      <td>${formatCurrency(item.pagibig)}</td>
      <td>${formatCurrency(item.tax)}</td>
      <td>${formatCurrency(item.additionalDeductions)}</td>
      <td>${formatCurrency(item.totalDeduction)}</td>
      <td>${formatCurrency(item.netPay)}</td>
      <td>
        <div class="d-flex gap-1 flex-wrap">
          <button type="button" class="btn btn-warning btn-sm" onclick="editEmployeeSalaryRow('${item.id}')">Edit</button>
          <button type="button" class="btn btn-danger btn-sm" onclick="deleteEmployeeSalaryRow('${item.id}')">Delete</button>
        </div>
      </td>
    `;
    tableBody.appendChild(row);
  });

  paginateTable('employeeSalaryTable', 'employeeSalaryPagination', true);
}

function renderPayslipRows(rows = []) {
  const tableBody = document.querySelector('#payslipTable tbody');
  const tableWrap = document.getElementById('payslipTableWrap');
  const emptyState = document.getElementById('payslipEmptyState');
  if (!tableBody) return;

  if (!rows.length) {
    tableBody.innerHTML = '';
    if (tableWrap) tableWrap.classList.add('d-none');
    if (emptyState) emptyState.classList.remove('d-none');
    const pagination = document.getElementById('payslipPagination');
    if (pagination) pagination.innerHTML = '';
    allPayslipEmployees = [];
    return;
  }

  if (tableWrap) tableWrap.classList.remove('d-none');
  if (emptyState) emptyState.classList.add('d-none');

  tableBody.innerHTML = '';
  allPayslipEmployees = rows.map(item => ({
    id: item.id,
    name: item.name,
    email: item.email,
    cutoffSalary: item.cutoffSalary,
    grossPayPerMonth: item.cutoffSalary,
    grossPayPerDay: item.grossPayPerDay,
    hoursWorked: item.hoursWorked,
    totalOt: item.totalOtPay,
    legalHoliday: item.legalHoliday,
    specialHoliday: item.specialHoliday,
    taxableAdditionalIncome: item.taxableAdditionalIncome,
    nonTaxableAdditionalIncome: item.nonTaxableAdditionalIncome,
    riceSubsidy: item.riceSubsidy,
    electricity: item.electricity,
    sss: item.sss,
    phlth: item.phlth,
    pagibig: item.pagibig,
    tax: item.tax,
    personalCa: item.additionalDeductions,
    additionalDeductions: item.additionalDeductions,
    totalDeduction: item.totalDeduction,
    netPay: item.netPay
  }));

  rows.forEach(item => {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td>${item.id}</td>
      <td>${item.name}</td>
      <td>${item.email}</td>
      <td>${formatCurrency(item.cutoffSalary)}</td>
      <td>${formatCurrency(item.grossPayPerDay)}</td>
      <td>${Number(item.hoursWorked || 0).toFixed(2)}</td>
      <td>${formatCurrency(item.totalOtPay)}</td>
      <td>${formatCurrency(item.legalHoliday)}</td>
      <td>${formatCurrency(item.specialHoliday)}</td>
      <td>${formatCurrency(item.taxableAdditionalIncome)}</td>
      <td>${formatCurrency(item.nonTaxableAdditionalIncome)}</td>
      <td>${formatCurrency(item.sss)}</td>
      <td>${formatCurrency(item.phlth)}</td>
      <td>${formatCurrency(item.pagibig)}</td>
      <td>${formatCurrency(item.tax)}</td>
      <td>${formatCurrency(item.additionalDeductions)}</td>
      <td>${formatCurrency(item.totalDeduction)}</td>
      <td>${formatCurrency(item.netPay)}</td>
    `;
    tableBody.appendChild(row);
  });

  paginateTable('payslipTable', 'payslipPagination', true);
}

function openProcessPayrollModal() {
  const fromInput = document.getElementById('employeeSalaryDateFrom');
  const toInput = document.getElementById('employeeSalaryDateTo');
  
  // Pre-fill modal with current values
  const modalFromInput = document.getElementById('modalProcessPayrollFrom');
  const modalToInput = document.getElementById('modalProcessPayrollTo');
  
  if (modalFromInput) {
    modalFromInput.value = fromInput?.value || '';
  }
  if (modalToInput) {
    modalToInput.value = toInput?.value || '';
  }
  
  // Update date range display in modal
  updateModalPayrollDateRange();
  
  // Open the modal
  const modalElement = document.getElementById('processPayrollModal');
  const modal = new bootstrap.Modal(modalElement);
  modal.show();
}

function updateModalPayrollDateRange() {
  const fromInput = document.getElementById('modalProcessPayrollFrom');
  const toInput = document.getElementById('modalProcessPayrollTo');
  const rangeDisplay = document.getElementById('modalProcessPayrollDateRange');
  
  if (!rangeDisplay) return;
  
  const fromText = formatIsoDateToWords(fromInput?.value);
  const toText = formatIsoDateToWords(toInput?.value);
  
  if (fromText && toText) {
    rangeDisplay.textContent = `From ${fromText} to ${toText}`;
  } else if (fromText) {
    rangeDisplay.textContent = `From ${fromText}`;
  } else if (toText) {
    rangeDisplay.textContent = `To ${toText}`;
  } else {
    rangeDisplay.textContent = '';
  }
}

// Add event listeners to modal inputs for date range formatting
document.addEventListener('DOMContentLoaded', () => {
  const modalFromInput = document.getElementById('modalProcessPayrollFrom');
  const modalToInput = document.getElementById('modalProcessPayrollTo');
  
  if (modalFromInput) {
    modalFromInput.addEventListener('change', updateModalPayrollDateRange);
  }
  if (modalToInput) {
    modalToInput.addEventListener('change', updateModalPayrollDateRange);
  }
  
  // Initialize date pickers for modal inputs
  if (typeof $ !== 'undefined' && $.fn && $.fn.datepicker) {
    if (modalFromInput && !$(modalFromInput).hasClass('hasDatepicker')) {
      $(modalFromInput).datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true,
        onSelect: function() {
          updateModalPayrollDateRange();
        }
      });
    }
    
    if (modalToInput && !$(modalToInput).hasClass('hasDatepicker')) {
      $(modalToInput).datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true,
        onSelect: function() {
          updateModalPayrollDateRange();
        }
      });
    }
  }
});

async function confirmProcessPayroll() {
  const modalFromInput = document.getElementById('modalProcessPayrollFrom');
  const modalToInput = document.getElementById('modalProcessPayrollTo');
  const fromRaw = modalFromInput?.value || '';
  const toRaw = modalToInput?.value || '';
  const dateFrom = parseDateInputValue(fromRaw, false);
  const dateTo = parseDateInputValue(toRaw, true);
  const cutoffKey = makeCutoffKey(fromRaw, toRaw);

  if (!cutoffKey) {
    alert('Please select a valid cutoff date range before processing salary.');
    return;
  }

  // Close modal first
  const modalElement = document.getElementById('processPayrollModal');
  const modal = bootstrap.Modal.getInstance(modalElement);
  if (modal) {
    modal.hide();
  }

  const processButton = document.getElementById('processEmployeeSalaryBtn');
  if (processButton) {
    processButton.disabled = true;
    processButton.textContent = 'Processing...';
  }

  try {
    const response = await fetch('cutoff_payroll_api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ from: fromRaw, to: toRaw })
    });
    const result = await response.json();
    if (!result.success) {
      throw new Error(result.message || 'Failed to process employee salary.');
    }

    const rows = result.data?.rows || [];
    renderEmployeeSalaryRows(rows);

    const payslipFrom = document.getElementById('payslipDateFrom');
    const payslipTo = document.getElementById('payslipDateTo');
    if ((payslipFrom?.value || '') === fromRaw && (payslipTo?.value || '') === toRaw) {
      renderPayslipRows(rows);
    }

    alert('Employee salary has been processed for the selected cutoff. Payslip is updated for this cutoff.');
  } catch (error) {
    alert(error.message || 'Failed to process employee salary.');
  } finally {
    if (processButton) {
      processButton.disabled = false;
      processButton.textContent = 'Process Employee Salary for the cut-off';
    }
  }
}

async function processEmployeeSalaryForCutoff() {
  const processButton = document.getElementById('processEmployeeSalaryBtn');
  const fromInput = document.getElementById('employeeSalaryDateFrom');
  const toInput = document.getElementById('employeeSalaryDateTo');
  const fromRaw = fromInput?.value || '';
  const toRaw = toInput?.value || '';
  const dateFrom = parseDateInputValue(fromRaw, false);
  const dateTo = parseDateInputValue(toRaw, true);
  const cutoffKey = makeCutoffKey(fromRaw, toRaw);

  if (!cutoffKey) {
    alert('Please select a valid cutoff date range before processing salary.');
    return;
  }

  if (processButton) {
    processButton.disabled = true;
    processButton.textContent = 'Processing...';
  }

  try {
    const response = await fetch('cutoff_payroll_api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ from: fromRaw, to: toRaw })
    });
    const result = await response.json();
    if (!result.success) {
      throw new Error(result.message || 'Failed to process employee salary.');
    }

    const rows = result.data?.rows || [];
    renderEmployeeSalaryRows(rows);

    const payslipFrom = document.getElementById('payslipDateFrom');
    const payslipTo = document.getElementById('payslipDateTo');
    if ((payslipFrom?.value || '') === fromRaw && (payslipTo?.value || '') === toRaw) {
      renderPayslipRows(rows);
    }

    alert('Employee salary has been processed for the selected cutoff. Payslip is updated for this cutoff.');
  } catch (error) {
    alert(error.message || 'Failed to process employee salary.');
  } finally {
    if (processButton) {
      processButton.disabled = false;
      processButton.textContent = 'Process Employee Salary for the cut-off';
    }
  }
}

function filterEmployees() {
  const searchInput = document.getElementById('searchEmployees');
  if (!searchInput) return;
  searchPayrollDataTable('employeeSearchTable', searchInput.value);
}

function filterEmployeeSalary() {
  const searchInput = document.getElementById('searchEmployeeSalary');
  if (!searchInput) return;
  searchPayrollDataTable('employeeSalaryTable', searchInput.value);
}

async function loadEmployeeSalary() {
  const tableBody = document.querySelector('#employeeSalaryTable tbody');
  if (!tableBody) return;

  const dateFromInput = document.getElementById('employeeSalaryDateFrom');
  const dateToInput = document.getElementById('employeeSalaryDateTo');
  const dateFrom = parseDateInputValue(dateFromInput?.value, false);
  const dateTo = parseDateInputValue(dateToInput?.value, true);

  tableBody.innerHTML = '<tr><td colspan="19" class="text-center">Loading employees...</td></tr>';

  try {
    const response = await fetch(`cutoff_payroll_api.php?from=${encodeURIComponent(dateFromInput?.value || '')}&to=${encodeURIComponent(dateToInput?.value || '')}`);
    const result = await response.json();

    if (result.success && Array.isArray(result.data?.rows) && result.data.rows.length > 0) {
      renderEmployeeSalaryRows(result.data.rows);
      return;
    }
  } catch (error) {
    console.warn('Unable to load stored employee salary rows, falling back to live computation:', error);
  }

  try {
    const { rows } = await buildCutoffPayrollRows(
      dateFrom,
      dateTo,
      dateFromInput?.value || '',
      dateToInput?.value || '',
      false
    );
    renderEmployeeSalaryRows(rows);
  } catch (error) {
    tableBody.innerHTML = `<tr><td colspan="19" class="text-center text-danger">${error.message}</td></tr>`;
    paginateTable('employeeSalaryTable', 'employeeSalaryPagination', true);
  }
}

function filterPayslip() {
  const searchInput = document.getElementById('searchPayslip');
  if (!searchInput) return;
  searchPayrollDataTable('payslipTable', searchInput.value);
}

let selectedPayslipEmployee = null;

function openPayslipEmailModal(employee) {
  selectedPayslipEmployee = employee || null;

  const modal = document.getElementById('payslipEmailModal');
  const idField = document.getElementById('payslipModalEmployeeId');
  const nameField = document.getElementById('payslipModalEmployeeName');
  const emailField = document.getElementById('payslipModalEmployeeEmail');

  if (!modal || !idField || !nameField || !emailField || !selectedPayslipEmployee) {
    return;
  }

  idField.textContent = selectedPayslipEmployee.id || '-';
  nameField.textContent = selectedPayslipEmployee.name || '-';
  emailField.textContent = selectedPayslipEmployee.email || '-';
  modal.classList.remove('d-none');
}

function closePayslipEmailModal() {
  const modal = document.getElementById('payslipEmailModal');
  if (!modal) {
    return;
  }

  modal.classList.add('d-none');
  selectedPayslipEmployee = null;
}

function sendPayslipBreakdownInEmail() {
  if (!selectedPayslipEmployee) {
    alert('Please select an employee first.');
    return;
  }

  if (!selectedPayslipEmployee.email) {
    alert('Selected employee has no email address.');
    return;
  }

  fetch('send_payslip_email.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(selectedPayslipEmployee)
  })
    .then(response => response.json())
    .then(result => {
      if (!result.success) {
        throw new Error(result.message || 'Failed to send payslip email.');
      }

      alert(result.message || `Payslip breakdown sent to ${selectedPayslipEmployee.email}.`);
      closePayslipEmailModal();
    })
    .catch(error => {
      alert(`Error: ${error.message}`);
    });
}

function openBulkEmailModal() {
  const modal = document.getElementById('bulkEmailModal');
  if (!modal) return;

  selectedBulkEmailEmployees.clear();
  bulkEmailEmployees = [...allPayslipEmployees];
  populateBulkEmailList();
  modal.classList.remove('d-none');
}

function closeBulkEmailModal() {
  const modal = document.getElementById('bulkEmailModal');
  if (!modal) return;

  modal.classList.add('d-none');
  selectedBulkEmailEmployees.clear();
}

function editEmployeeSalaryRow(employeeId) {
  const row = currentEmployeeSalaryRows.find(item => String(item.id) === String(employeeId));
  const modal = document.getElementById('employeeSalaryEditModal');
  if (!row || !modal) {
    alert('Unable to load this employee salary row for editing.');
    return;
  }

  if (!row.processedAt) {
    alert('This cutoff has not been saved yet. Process the cutoff first, then edit the stored payroll row.');
    return;
  }

  const setValue = (id, value) => {
    const input = document.getElementById(id);
    if (input) {
      input.value = Number.isFinite(Number(value)) ? Number(value).toFixed(2) : '0.00';
    }
  };

  const textField = (id, value) => {
    const input = document.getElementById(id);
    if (input) {
      input.value = value || '';
    }
  };

  textField('employeeSalaryEditEmployeeIdDisplay', row.id);
  textField('employeeSalaryEditEmployeeName', row.name);
  textField('employeeSalaryEditEmployeeEmail', row.email);
  document.getElementById('employeeSalaryEditEmployeeId').value = row.id || '';
  document.getElementById('employeeSalaryEditCarryIn').value = Number(row.carryIn || 0).toFixed(2);

  setValue('employeeSalaryEditCutoffSalary', row.cutoffSalary);
  setValue('employeeSalaryEditGrossPayPerDay', row.grossPayPerDay);
  setValue('employeeSalaryEditHoursWorked', row.hoursWorked);
  setValue('employeeSalaryEditTotalOtPay', row.totalOtPay);
  setValue('employeeSalaryEditLegalHoliday', row.legalHoliday);
  setValue('employeeSalaryEditSpecialHoliday', row.specialHoliday);
  setValue('employeeSalaryEditTaxableAdditionalIncome', row.taxableAdditionalIncome);
  setValue('employeeSalaryEditNonTaxableAdditionalIncome', row.nonTaxableAdditionalIncome);
  setValue('employeeSalaryEditSss', row.sss);
  setValue('employeeSalaryEditPhlth', row.phlth);
  setValue('employeeSalaryEditPagibig', row.pagibig);
  setValue('employeeSalaryEditTax', row.tax);
  setValue('employeeSalaryEditAdditionalDeductions', row.additionalDeductions);

  recalculateEmployeeSalaryEditPreview();
  modal.classList.remove('d-none');
}

function closeEmployeeSalaryEditModal() {
  const modal = document.getElementById('employeeSalaryEditModal');
  if (!modal) return;
  modal.classList.add('d-none');
}

function readEmployeeSalaryEditNumber(id) {
  const input = document.getElementById(id);
  const value = Number(input?.value || 0);
  return Number.isFinite(value) ? value : 0;
}

function recalculateEmployeeSalaryEditPreview() {
  const cutoffSalary = readEmployeeSalaryEditNumber('employeeSalaryEditCutoffSalary');
  const totalOtPay = readEmployeeSalaryEditNumber('employeeSalaryEditTotalOtPay');
  const legalHoliday = readEmployeeSalaryEditNumber('employeeSalaryEditLegalHoliday');
  const specialHoliday = readEmployeeSalaryEditNumber('employeeSalaryEditSpecialHoliday');
  const taxableAdditionalIncome = readEmployeeSalaryEditNumber('employeeSalaryEditTaxableAdditionalIncome');
  const nonTaxableAdditionalIncome = readEmployeeSalaryEditNumber('employeeSalaryEditNonTaxableAdditionalIncome');
  const sss = readEmployeeSalaryEditNumber('employeeSalaryEditSss');
  const phlth = readEmployeeSalaryEditNumber('employeeSalaryEditPhlth');
  const pagibig = readEmployeeSalaryEditNumber('employeeSalaryEditPagibig');
  const tax = readEmployeeSalaryEditNumber('employeeSalaryEditTax');
  const additionalDeductions = readEmployeeSalaryEditNumber('employeeSalaryEditAdditionalDeductions');
  const carryIn = readEmployeeSalaryEditNumber('employeeSalaryEditCarryIn');

  const totalDeduction = sss + phlth + pagibig + tax + additionalDeductions;
  const grossNet = cutoffSalary + totalOtPay + legalHoliday + specialHoliday + taxableAdditionalIncome + nonTaxableAdditionalIncome - totalDeduction;
  const netPay = Math.max(0, grossNet - carryIn);

  const totalField = document.getElementById('employeeSalaryEditTotalDeduction');
  const netField = document.getElementById('employeeSalaryEditNetPay');
  if (totalField) totalField.value = formatCurrency(totalDeduction);
  if (netField) netField.value = formatCurrency(netPay);
}

async function saveEmployeeSalaryEditRow() {
  const employeeId = Number(document.getElementById('employeeSalaryEditEmployeeId')?.value || 0);
  const dateFromInput = document.getElementById('employeeSalaryDateFrom');
  const dateToInput = document.getElementById('employeeSalaryDateTo');
  const fromValue = dateFromInput?.value || '';
  const toValue = dateToInput?.value || '';

  if (!employeeId || !fromValue || !toValue) {
    alert('Missing employee or cutoff details.');
    return;
  }

  const payload = {
    action: 'update',
    from: fromValue,
    to: toValue,
    employeeId,
    cutoffSalary: readEmployeeSalaryEditNumber('employeeSalaryEditCutoffSalary'),
    grossPayPerDay: readEmployeeSalaryEditNumber('employeeSalaryEditGrossPayPerDay'),
    hoursWorked: readEmployeeSalaryEditNumber('employeeSalaryEditHoursWorked'),
    totalOtPay: readEmployeeSalaryEditNumber('employeeSalaryEditTotalOtPay'),
    legalHoliday: readEmployeeSalaryEditNumber('employeeSalaryEditLegalHoliday'),
    specialHoliday: readEmployeeSalaryEditNumber('employeeSalaryEditSpecialHoliday'),
    taxableAdditionalIncome: readEmployeeSalaryEditNumber('employeeSalaryEditTaxableAdditionalIncome'),
    nonTaxableAdditionalIncome: readEmployeeSalaryEditNumber('employeeSalaryEditNonTaxableAdditionalIncome'),
    sss: readEmployeeSalaryEditNumber('employeeSalaryEditSss'),
    phlth: readEmployeeSalaryEditNumber('employeeSalaryEditPhlth'),
    pagibig: readEmployeeSalaryEditNumber('employeeSalaryEditPagibig'),
    tax: readEmployeeSalaryEditNumber('employeeSalaryEditTax'),
    additionalDeductions: readEmployeeSalaryEditNumber('employeeSalaryEditAdditionalDeductions')
  };

  try {
    const response = await fetch('cutoff_payroll_api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    const result = await response.json();
    if (!result.success) {
      throw new Error(result.message || 'Failed to update employee salary row.');
    }

    closeEmployeeSalaryEditModal();
    await loadEmployeeSalary();
    await loadPayslip();
    alert(result.message || 'Employee salary row updated successfully.');
  } catch (error) {
    alert(error.message || 'Failed to update employee salary row.');
  }
}

async function deleteEmployeeSalaryRow(employeeId) {
  const dateFromInput = document.getElementById('employeeSalaryDateFrom');
  const dateToInput = document.getElementById('employeeSalaryDateTo');
  const fromValue = dateFromInput?.value || '';
  const toValue = dateToInput?.value || '';

  if (!fromValue || !toValue) {
    alert('Please select a valid cutoff range before deleting a processed salary row.');
    return;
  }

  if (!confirm(`Delete the processed salary row for employee ID ${employeeId}?`)) {
    return;
  }

  try {
    const response = await fetch('cutoff_payroll_api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'delete',
        from: fromValue,
        to: toValue,
        employeeId: Number(employeeId)
      })
    });

    const result = await response.json();
    if (!result.success) {
      throw new Error(result.message || 'Failed to delete employee salary row.');
    }

    await loadEmployeeSalary();
    await loadPayslip();
    alert(result.message || 'Employee salary row deleted successfully.');
  } catch (error) {
    alert(error.message || 'Failed to delete employee salary row.');
  }
}

function populateBulkEmailList() {
  const listContainer = document.getElementById('bulkEmailEmployeeList');
  if (!listContainer) return;

  const table = document.createElement('table');
  table.className = 'table table-hover';
  table.innerHTML = `
    <thead class="table-light">
      <tr>
        <th style="width: 50%;">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(event)">
            <label class="form-check-label" for="selectAllCheckbox">Employee</label>
          </div>
        </th>
        <th style="width: 50%;">
          <div class="form-check">
            <label class="form-check-label">Email</label>
          </div>
        </th>
      </tr>
    </thead>
    <tbody id="bulkEmailTableBody"></tbody>
  `;
  listContainer.innerHTML = '';
  listContainer.appendChild(table);

  const tableBody = table.querySelector('#bulkEmailTableBody');
  bulkEmailEmployees.forEach((emp, index) => {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td>
        <div class="form-check">
          <input 
            class="form-check-input bulk-email-checkbox" 
            type="checkbox" 
            id="bulkEmailCheckbox${index}" 
            data-employee-id="${emp.id}"
          >
          <label class="form-check-label" for="bulkEmailCheckbox${index}">
            ${emp.name}
          </label>
        </div>
      </td>
      <td>${emp.email}</td>
    `;
    tableBody.appendChild(row);

    const checkbox = row.querySelector('input');
    checkbox.addEventListener('change', (e) => {
      if (e.target.checked) {
        selectedBulkEmailEmployees.add(emp.id);
      } else {
        selectedBulkEmailEmployees.delete(emp.id);
        document.getElementById('selectAllCheckbox').checked = false;
      }
    });
  });
}

function toggleSelectAll(event) {
  const checkboxes = document.querySelectorAll('.bulk-email-checkbox');
  checkboxes.forEach(checkbox => {
    checkbox.checked = event.target.checked;
    if (event.target.checked) {
      selectedBulkEmailEmployees.add(checkbox.dataset.employeeId);
    } else {
      selectedBulkEmailEmployees.delete(checkbox.dataset.employeeId);
    }
  });
}

function selectAllBulkEmailEmployees() {
  const selectAllCheckbox = document.getElementById('selectAllCheckbox');
  if (selectAllCheckbox) {
    selectAllCheckbox.checked = true;
    toggleSelectAll({ target: selectAllCheckbox });
  }
}

function filterBulkEmailEmployees() {
  const searchInput = document.getElementById('bulkEmailSearchInput');
  if (!searchInput) return;

  const searchTerm = searchInput.value.toLowerCase();
  bulkEmailEmployees = allPayslipEmployees.filter(emp =>
    emp.name.toLowerCase().includes(searchTerm) ||
    emp.email.toLowerCase().includes(searchTerm)
  );
  populateBulkEmailList();
}

function sendBulkPayslipEmails() {
  if (selectedBulkEmailEmployees.size === 0) {
    alert('Please select at least one employee.');
    return;
  }

  const selectedEmployees = allPayslipEmployees.filter(emp =>
    selectedBulkEmailEmployees.has(emp.id)
  );

  const sendButton = document.querySelector('#bulkEmailModal .card-footer .btn-success');
  if (sendButton) {
    sendButton.disabled = true;
    sendButton.textContent = 'Sending...';
  }

  let successCount = 0;
  let failureCount = 0;

  Promise.all(
    selectedEmployees.map(emp =>
      fetch('send_payslip_email.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(emp)
      })
        .then(response => response.json())
        .then(result => {
          if (result.success) {
            successCount++;
          } else {
            failureCount++;
            console.error(`Failed to send to ${emp.email}:`, result.message);
          }
        })
        .catch(error => {
          failureCount++;
          console.error(`Error sending to ${emp.email}:`, error.message);
        })
    )
  ).then(() => {
    if (sendButton) {
      sendButton.disabled = false;
      sendButton.textContent = 'Send Payslips';
    }

    let message = `Sent ${successCount} payslip${successCount !== 1 ? 's' : ''}`;
    if (failureCount > 0) {
      message += ` (${failureCount} failed)`;
    }
    alert(message);
    closeBulkEmailModal();
  });
}

function buildAttendanceWorkMap(attendanceData = [], dateFrom = null, dateTo = null) {
  const attendanceByEmployee = new Map();
  
  console.log('buildAttendanceWorkMap called with:');
  console.log('  attendanceData.length:', attendanceData.length);
  console.log('  dateFrom:', dateFrom);
  console.log('  dateTo:', dateTo);

  try {
    (attendanceData || []).forEach((item, index) => {
      try {
        const employeeId = getAttendanceEmployeeId(item);
        if (!employeeId) {
          console.log(`Record ${index}: No employeeId`);
          return;
        }

        if (dateFrom || dateTo) {
          const itemDate = getAttendanceItemDate(item);
          if (!itemDate || Number.isNaN(itemDate.getTime())) {
            console.log(`Record ${index}: Invalid date - itemDate=${itemDate}`);
            return;
          }

          if (dateFrom && itemDate < dateFrom) {
            console.log(`Record ${index} (${employeeId}): Date ${itemDate} is before dateFrom ${dateFrom}`);
            return;
          }

          if (dateTo && itemDate > dateTo) {
            console.log(`Record ${index} (${employeeId}): Date ${itemDate} is after dateTo ${dateTo}`);
            return;
          }
        }

        const durationMinutes = getAttendanceDurationMinutes(item);
        const isAO = Number(item.AO) === 1;
        const regularMinutes = Math.max(0, Math.min(durationMinutes, 480));
        const overtimeMinutes = durationMinutes > 480 && isAO ? (durationMinutes - 480) : 0;

        const existing = attendanceByEmployee.get(employeeId) || { regularMinutes: 0, overtimeMinutes: 0 };
        existing.regularMinutes += regularMinutes;
        existing.overtimeMinutes += overtimeMinutes;
        attendanceByEmployee.set(employeeId, existing);
        
        if (index < 3 || employeeId === '4') {
          console.log(`Record ${index} added: Emp${employeeId}, duration=${durationMinutes}min, regular=${regularMinutes}min`);
        }
      } catch (recordError) {
        console.error(`Error processing record ${index}:`, recordError);
      }
    });
  } catch (mapError) {
    console.error('Error building attendance map:', mapError);
  }

  console.log('buildAttendanceWorkMap complete - map size:', attendanceByEmployee.size);
  return attendanceByEmployee;
}

function buildIncomeByTypeMap(assignedIncomeData = []) {
  const incomeByEmployee = {};

  (assignedIncomeData || []).forEach(item => {
    const employeeNameKey = String(item.name || '').trim().toLowerCase();
    const incomeType = String(item.type_of_income || '').trim().toLowerCase();
    const cost = Number(item.cost);

    if (!employeeNameKey || !incomeType || !Number.isFinite(cost)) {
      return;
    }

    if (!incomeByEmployee[employeeNameKey]) {
      incomeByEmployee[employeeNameKey] = {};
    }

    incomeByEmployee[employeeNameKey][incomeType] = (incomeByEmployee[employeeNameKey][incomeType] || 0) + cost;
  });

  return incomeByEmployee;
}

function buildAdditionalIncomeSummaryMap(assignedIncomeData = []) {
  const summaryByEmployee = {};

  (assignedIncomeData || []).forEach(item => {
    const employeeNameKey = String(item.name || '').trim().toLowerCase();
    const incomeType = String(item.type_of_income || '').trim().toLowerCase();
    const cost = Number(item.cost);

    if (!employeeNameKey || !incomeType || !Number.isFinite(cost)) {
      return;
    }

    // Holiday pay is already represented in its own columns.
    if (/legal\s*holiday/i.test(incomeType) || /special\s*holiday/i.test(incomeType)) {
      return;
    }

    if (!summaryByEmployee[employeeNameKey]) {
      summaryByEmployee[employeeNameKey] = { taxable: 0, nonTaxable: 0 };
    }

    if (Number(item.taxable) === 1) {
      summaryByEmployee[employeeNameKey].taxable += cost;
    } else {
      summaryByEmployee[employeeNameKey].nonTaxable += cost;
    }
  });

  return summaryByEmployee;
}

function getCutoffContext(dateFrom = null, dateTo = null) {
  const referenceDate = dateFrom || dateTo || new Date();
  const year = referenceDate.getFullYear();
  const month = String(referenceDate.getMonth() + 1).padStart(2, '0');
  const yearMonth = `${year}-${month}`;
  const cutoffSlot = referenceDate.getDate() <= 15 ? 1 : 2;
  return { yearMonth, cutoffSlot };
}

function buildRecurringMapByIncomeType(incomeTypesData = []) {
  const recurringMap = {};

  (incomeTypesData || []).forEach(item => {
    const typeNameKey = String(item.type_of_income || '').trim().toLowerCase();
    if (!typeNameKey) {
      return;
    }

    recurringMap[typeNameKey] = Number(item.recurring) === 1 ? 1 : 0;
  });

  return recurringMap;
}

function filterAssignedIncomeByCutoff(assignedIncomeData = [], incomeTypesData = [], dateFrom = null, dateTo = null) {
  const recurringMap = buildRecurringMapByIncomeType(incomeTypesData);
  const cutoffContext = getCutoffContext(dateFrom, dateTo);

  return (assignedIncomeData || []).filter(item => {
    const typeNameKey = String(item.type_of_income || '').trim().toLowerCase();
    const isRecurring = Number(item.recurring) === 1 || recurringMap[typeNameKey] === 1;
    if (isRecurring) {
      return true;
    }

    const applyYearMonth = String(item.apply_year_month || '').trim();
    const applyCutoffSlot = Number(item.apply_cutoff_slot || 0);

    // Backward compatibility for old rows that have no cutoff scope yet.
    if (!applyYearMonth || !applyCutoffSlot) {
      return true;
    }

    return applyYearMonth === cutoffContext.yearMonth && applyCutoffSlot === cutoffContext.cutoffSlot;
  });
}

function getComputedIncomeAmountByKeyword(employeeIncomeByType = {}, keywordPattern, multiplier = 1) {
  let total = 0;
  Object.keys(employeeIncomeByType || {}).forEach(typeName => {
    if (keywordPattern.test(typeName)) {
      total += Number(employeeIncomeByType[typeName]) || 0;
    }
  });
  const computed = total * Number(multiplier || 1);
  return Number.isFinite(computed) ? Number(parseFloat(computed).toFixed(2)) : 0;
}

function buildPersonalCaMap(assignedDeductionData = []) {
  const personalCaByEmployee = {};

  (assignedDeductionData || []).forEach(item => {
    const employeeNameKey = String(item.name || '').trim().toLowerCase();
    const cost = Number(item.cost);

    if (!employeeNameKey || !Number.isFinite(cost)) {
      return;
    }

    personalCaByEmployee[employeeNameKey] = (personalCaByEmployee[employeeNameKey] || 0) + cost;
  });

  return personalCaByEmployee;
}

function buildRecurringDeductionTypeMap(deductionTypesData = []) {
  const recurringMap = {};

  (deductionTypesData || []).forEach(item => {
    const typeNameKey = String(item.type_of_deduction || '').trim().toLowerCase();
    if (!typeNameKey) {
      return;
    }

    recurringMap[typeNameKey] = Number(item.recurring) === 1 ? 1 : 0;
  });

  return recurringMap;
}

function filterAssignedDeductionsByCutoff(assignedDeductionData = [], deductionTypesData = [], dateFrom = null, dateTo = null) {
  const recurringMap = buildRecurringDeductionTypeMap(deductionTypesData);
  const cutoffContext = getCutoffContext(dateFrom, dateTo);

  return (assignedDeductionData || []).filter(item => {
    const typeNameKey = String(item.type_of_deduction || '').trim().toLowerCase();
    const isRecurring = Number(item.recurring) === 1 || recurringMap[typeNameKey] === 1;
    if (isRecurring) {
      return true;
    }

    const applyYearMonth = String(item.apply_year_month || '').trim();
    const applyCutoffSlot = Number(item.apply_cutoff_slot || 0);

    // Backward compatibility for old rows that have no cutoff scope yet.
    if (!applyYearMonth || !applyCutoffSlot) {
      return true;
    }

    return applyYearMonth === cutoffContext.yearMonth && applyCutoffSlot === cutoffContext.cutoffSlot;
  });
}

async function loadPayslip() {
  const tableBody = document.querySelector('#payslipTable tbody');
  if (!tableBody) return;

  const dateFromInput = document.getElementById('payslipDateFrom');
  const dateToInput = document.getElementById('payslipDateTo');
  const dateFrom = parseDateInputValue(dateFromInput?.value, false);
  const dateTo = parseDateInputValue(dateToInput?.value, true);

  savePayslipRangeState();

  tableBody.innerHTML = '<tr><td colspan="17" class="text-center">Loading payslip...</td></tr>';

  try {
    const response = await fetch(`cutoff_payroll_api.php?from=${encodeURIComponent(dateFromInput?.value || '')}&to=${encodeURIComponent(dateToInput?.value || '')}`);
    const result = await response.json();

    if (!result.success) {
      renderPayslipRows([]);
      return;
    }

    const rows = result.data?.rows || [];
    renderPayslipRows(rows);
  } catch (error) {
    tableBody.innerHTML = `<tr><td colspan="19" class="text-center text-danger">${error.message}</td></tr>`;
    paginateTable('payslipTable', 'payslipPagination', true);
  }
}

async function fetchEmployeeIncomeAssignments() {
  try {
    const [assignmentResponse, incomeTypeResponse] = await Promise.all([
      fetch('assigned_emp_inc_api.php'),
      fetch('emp_inc_type_api.php')
    ]);

    if (!assignmentResponse.ok) {
      throw new Error(`HTTP error! status: ${assignmentResponse.status}`);
    }

    const assignmentData = await assignmentResponse.json();
    const incomeTypeData = await incomeTypeResponse.json();

    if (assignmentData.success) {
      const incomeTypeMap = {};
      if (incomeTypeData.success) {
        (incomeTypeData.data || []).forEach(typeItem => {
          const typeNameKey = String(typeItem.type_of_income || '').trim().toLowerCase();
          if (!typeNameKey) {
            return;
          }
          incomeTypeMap[typeNameKey] = typeItem;
        });
      }

      employeeIncomeAssignments = assignmentData.data.map(item => {
        const typeNameKey = String(item.type_of_income || '').trim().toLowerCase();
        const latestType = incomeTypeMap[typeNameKey];

        return {
          id: item.id,
          employeeName: item.name,
          incomeType: item.type_of_income,
          cost: item.cost,
          taxable: latestType ? latestType.taxable : item.taxable,
          included13: latestType ? latestType.included_in_13month : item['month_13th'],
          recurring: Number(item.recurring) === 1 ? 1 : 0,
          applyYearMonth: item.apply_year_month || null,
          applyCutoffSlot: item.apply_cutoff_slot || null
        };
      });
      renderEmployeeIncomeAssignments();
    } else {
      console.error('Failed to fetch assignments:', assignmentData.message);
      alert(`Error: ${assignmentData.message}`);
    }
  } catch (error) {
    console.error('Error fetching assignments:', error);
    alert(`Error: ${error.message}`);
  }
}

// Call fetchEmployeeIncomeAssignments when the page loads or data needs to be refreshed
fetchEmployeeIncomeAssignments();
</script>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Premium Tables CRUD Management -->
<script src="premium_tables_manager.js"></script>

</body>
</html>