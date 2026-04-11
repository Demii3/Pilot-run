<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h2>Manage Employees</h2>
    <div>
      <button class="btn btn-info me-2" onclick="document.getElementById('importFile').click()">Import from Excel</button>
      <input type="file" id="importFile" accept=".csv,.xlsx,.xls" style="display: none;" onchange="importFromExcel(this)">
      <button class="btn btn-success me-2" onclick="exportToExcel()">Export to Excel</button>
      <button class="btn btn-primary" onclick="openAddForm()">+ Add Employee</button>
    </div>
  </div>

  <div class="card-body">
    <div class="mb-3">
      <input 
        type="text" 
        id="searchInput" 
        class="form-control" 
        placeholder="Search by name, email, position, department, or status..."
        onkeyup="searchEmployees()"
      >
    </div>

    <div class="table-responsive">
      <table class="table table-hover" id="employeeTable">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Username</th>
            <th>Password</th>
            <th>Position</th>
            <th>Department</th>
            <th>Salary</th>
            <th>Type</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="pagination-container">
      <button class="btn btn-secondary btn-sm" onclick="goToFirstPage()">First</button>
      <button class="btn btn-secondary btn-sm" onclick="previousPage()">Previous</button>
      <span class="page-info">Page <span id="currentPage">1</span> of <span id="totalPages">1</span></span>
      <button class="btn btn-secondary btn-sm" onclick="nextPage()">Next</button>
      <button class="btn btn-secondary btn-sm" onclick="goToLastPage()">Last</button>
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
        <input type="password" id="password" class="form-control" required>
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