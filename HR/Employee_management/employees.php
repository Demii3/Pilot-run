<style>
  #employeeTable {
    font-size: 0.92rem;
    width: 100%;
    min-width: 100%;
    table-layout: fixed;
  }

  #employeeTable th,
  #employeeTable td {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  #employeeTable th {
    background-color: #f8f9fa;
  }

  .table-responsive {
    overflow-x: auto;
  }

  #employeeTable tbody td:last-child {
    display: table-cell;
    flex-direction: initial;
    gap: 0;
    justify-content: initial;
    align-items: initial;
  }

  .employee-summary {
    display: flex;
    flex-direction: column;
    gap: 2px;
    white-space: normal;
    line-height: 1.2;
  }

  .employee-summary strong {
    font-size: 0.98rem;
    color: #1f2d3d;
  }

  .employee-summary span,
  .employee-summary small {
    color: #5a6472;
  }

  .employee-actions {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  .employee-detail-body {
    padding: 20px;
    overflow-y: auto;
    max-height: calc(85vh - 120px);
  }

  .employee-detail-body .form-control,
  .employee-detail-body .form-control:disabled,
  .employee-detail-body .form-control[readonly] {
    background-color: #f8f9fa;
    opacity: 1;
  }
</style>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h2>Manage Employees</h2>
    <div>
      <button class="btn btn-info me-2" onclick="document.getElementById('importFile').click()">Import from Excel</button>
      <input type="file" id="importFile" accept=".csv,.xlsx,.xls" style="display: none;" onchange="importFromExcel(this)">
      <button class="btn btn-success me-2" onclick="exportToExcel()">Export to Excel</button>
      <button class="btn btn-primary add-employee-btn" onclick="openAddForm()">+ Add Employee</button>
    </div>
  </div>

  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover" id="employeeTable"></table>
    </div>
  </div>
</div>

<div id="employeeModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2 id="formTitle">Add New Employee</h2>
      <span class="close" onclick="closeModal()">&times;</span>
    </div>

    <form id="employeeForm">
      <input type="hidden" id="employeeId">

      <div class="form-group">
        <label for="name">Full Name:</label>
        <input type="text" id="name" class="form-control" required>
      </div>

      <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" class="form-control" required>
      </div>

      <div class="form-group">
        <label for="username">Username:</label>
        <input type="text" id="username" class="form-control" required>
      </div>

      <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" id="password" class="form-control">
      </div>

      <div class="form-group">
        <label for="type">Type:</label>
        <select id="type" class="form-control" required>
          <option value="HR">HR</option>
          <option value="Emp">Emp</option>
        </select>
      </div>

      <div class="form-group">
        <label for="position">Position:</label>
        <input type="text" id="position" class="form-control" required>
      </div>

      <div class="form-group">
        <label for="department">Department:</label>
        <input type="text" id="department" class="form-control" required>
      </div>

      <div class="form-group">
        <label for="salary">Salary:</label>
        <input type="number" id="salary" class="form-control" step="0.01" required>
      </div>

      <div class="form-group">
        <label for="joinDate">Join Date:</label>
        <input type="date" id="joinDate" class="form-control" required>
      </div>

      <div class="form-group">
        <label for="status">Status:</label>
        <select id="status" class="form-control" required>
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
        </select>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Employee</button>
      </div>
    </form>
  </div>
</div>

<div id="employeeDetailModal" class="modal employee-detail-modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2 id="detailTitle">Employee Details</h2>
      <span class="close" onclick="closeEmployeeDetails()">&times;</span>
    </div>

    <div class="employee-detail-body">
      <div class="form-group">
        <label for="detailId">ID:</label>
        <input type="text" id="detailId" class="form-control" readonly>
      </div>

      <div class="form-group">
        <label for="detailName">Full Name:</label>
        <input type="text" id="detailName" class="form-control" readonly>
      </div>

      <div class="form-group">
        <label for="detailEmail">Email:</label>
        <input type="email" id="detailEmail" class="form-control" readonly>
      </div>

      <div class="form-group">
        <label for="detailUsername">Username:</label>
        <input type="text" id="detailUsername" class="form-control" readonly>
      </div>

      <div class="form-group">
        <label for="detailType">Type:</label>
        <select id="detailType" class="form-control" disabled>
          <option value="HR">HR</option>
          <option value="Emp">Emp</option>
        </select>
      </div>

      <div class="form-group">
        <label for="detailPosition">Position:</label>
        <input type="text" id="detailPosition" class="form-control" readonly>
      </div>

      <div class="form-group">
        <label for="detailDepartment">Department:</label>
        <input type="text" id="detailDepartment" class="form-control" readonly>
      </div>

      <div class="form-group">
        <label for="detailSalary">Salary:</label>
        <input type="text" id="detailSalary" class="form-control" readonly>
      </div>

      <div class="form-group">
        <label for="detailJoinDate">Join Date:</label>
        <input type="date" id="detailJoinDate" class="form-control" readonly>
      </div>

      <div class="form-group">
        <label for="detailStatus">Status:</label>
        <select id="detailStatus" class="form-control" disabled>
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
        </select>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeEmployeeDetails()">Close</button>
      </div>
    </div>
  </div>
</div>