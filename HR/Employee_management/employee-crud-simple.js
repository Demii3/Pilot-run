const API_URL = './employees_api.php';
let employeesData = [];
let employeeTable;

function openAddForm() {
  document.getElementById('employeeForm').reset();
  document.getElementById('employeeId').value = '';
  document.getElementById('status').value = 'Active';
  document.getElementById('formTitle').textContent = 'Add New Employee';
  const modal = document.getElementById('employeeModal');
  modal.classList.add('show');
  document.body.classList.add('modal-open');
}

function closeModal() {
  const modal = document.getElementById('employeeModal');
  modal.classList.remove('show');
  document.body.classList.remove('modal-open');
}

async function fetchJson(url, options = {}) {
  const response = await fetch(url, options);
  const text = await response.text();
  try {
    return JSON.parse(text);
  } catch (error) {
    console.error('Invalid JSON response from', url, 'status', response.status, text);
    throw new Error('Server returned invalid JSON. See console for response.');
  }
}

function showNotification(message, type = 'info') {
  alert(message);
}

async function getEmployeeById(id) {
  const parsedId = parseInt(id, 10);
  let employee = employeesData.find(emp => parseInt(emp.id, 10) === parsedId);
  if (employee) {
    return employee;
  }

  try {
    const result = await fetchJson(`${API_URL}?id=${encodeURIComponent(parsedId)}`);
    if (!result.success) {
      return null;
    }
    employee = result.data;
    return employee;
  } catch (error) {
    return null;
  }
}

async function editEmployee(id) {
  const employee = await getEmployeeById(id);
  if (!employee) {
    showNotification('Employee not found', 'error');
    return;
  }

  document.getElementById('employeeId').value = employee.id;
  document.getElementById('name').value = employee.name;
  document.getElementById('email').value = employee.email;
  document.getElementById('username').value = employee.username || '';
  document.getElementById('password').value = employee.password || '';
  document.getElementById('type').value = employee.type || 'Emp';
  document.getElementById('position').value = employee.position;
  document.getElementById('department').value = employee.department;
  document.getElementById('salary').value = employee.salary;
  document.getElementById('joinDate').value = employee.join_date || employee.joinDate || '';
  document.getElementById('status').value = employee.status || 'Active';
  document.getElementById('formTitle').textContent = 'Edit Employee';
  const modal = document.getElementById('employeeModal');
  modal.classList.add('show');
  document.body.classList.add('modal-open');
}

function deleteEmployee(id) {
  if (!confirm('Are you sure you want to delete this employee?')) {
    return;
  }

  fetchJson(API_URL, {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: `id=${encodeURIComponent(id)}`
  })
    .then(result => {
      if (!result.success) {
        throw new Error(result.message || 'Unable to delete employee');
      }
      showNotification('Employee deleted successfully!', 'success');
      displayEmployees();
    })
    .catch(error => showNotification('An error occurred: ' + error.message, 'error'));
}

function saveEmployee() {
  const saveButton = document.querySelector('#employeeForm button[type="submit"]');
  if (saveButton) {
    saveButton.disabled = true;
    saveButton.textContent = 'Saving...';
  }

  const id = document.getElementById('employeeId').value;
  const name = document.getElementById('name').value.trim();
  const email = document.getElementById('email').value.trim();
  const username = document.getElementById('username').value.trim();
  const password = document.getElementById('password').value;
  const type = document.getElementById('type').value;
  const position = document.getElementById('position').value.trim();
  const department = document.getElementById('department').value.trim();
  const salary = document.getElementById('salary').value.trim();
  const joinDate = document.getElementById('joinDate').value;
  const status = document.getElementById('status').value;

  // Password is required only for new employees
  const isNewEmployee = !id;
  if (!name || !email || !username || !type || !position || !department || !salary || !joinDate || !status) {
    showNotification('Please fill in all fields', 'error');
    if (saveButton) {
      saveButton.disabled = false;
      saveButton.textContent = 'Save Employee';
    }
    return;
  }
  
  if (isNewEmployee && !password) {
    showNotification('Password is required for new employees', 'error');
    if (saveButton) {
      saveButton.disabled = false;
      saveButton.textContent = 'Save Employee';
    }
    return;
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    showNotification('Please enter a valid email', 'error');
    if (saveButton) {
      saveButton.disabled = false;
      saveButton.textContent = 'Save Employee';
    }
    return;
  }

  const employeeData = {
    name,
    email,
    username,
    type,
    position,
    department,
    salary: parseFloat(salary),
    joinDate,
    status
  };

  if (password) {
    employeeData.password = password;
  }

  if (id) {
    employeeData.id = parseInt(id, 10);
  }

  fetchJson(API_URL, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(employeeData)
  })
    .then(result => {
      if (!result.success) {
        throw new Error(result.message || 'Unable to save employee');
      }
      showNotification(id ? 'Employee updated successfully!' : 'Employee added successfully!', 'success');
      closeModal();
      displayEmployees();
    })
    .catch(error => showNotification('An error occurred: ' + error.message, 'error'))
    .finally(() => {
      if (saveButton) {
        saveButton.disabled = false;
        saveButton.textContent = 'Save Employee';
      }
    });
}

function exportToExcel() {
  const employees = employeesData;
  if (employees.length === 0) {
    showNotification('No employee data to export', 'error');
    return;
  }

  const headers = ['ID', 'Name', 'Email', 'Username', 'Position', 'Department', 'Salary', 'Type', 'Status'];
  const csvContent = [
    headers.join(','),
    ...employees.map(emp => [
      emp.id,
      `"${emp.name}"`,
      emp.email,
      `"${emp.username || ''}"`,
      `"${emp.position}"`,
      `"${emp.department}"`,
      emp.salary,
      emp.type || '',
      emp.status || 'Inactive'
    ].join(','))
  ].join('\n');

  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);

  link.setAttribute('href', url);
  link.setAttribute('download', `employees_${new Date().toISOString().split('T')[0]}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
  showNotification('Employee data exported successfully!', 'success');
}

async function displayEmployees() {
  try {
    const response = await fetch(API_URL);
    const result = await response.json();
    if (!result.success) {
      throw new Error(result.message || 'Failed to load employees');
    }
    employeesData = result.data || [];
  } catch (error) {
    console.error(error);
    return;
  }

  // Destroy existing DataTable if it exists
  if (employeeTable) {
    employeeTable.destroy();
  }

  // Initialize DataTable
  employeeTable = $('#employeeTable').DataTable({
    data: employeesData,
    scrollX: true,
    scrollCollapse: true,
    autoWidth: true,
    responsive: false,
    columns: [
      { data: 'id', title: 'ID', width: '60px' },
      { data: 'name', title: 'Name', width: '180px' },
      { data: 'email', title: 'Email', width: '240px' },
      { data: 'username', title: 'Username', width: '150px' },
      { data: 'position', title: 'Position', width: '170px' },
      { data: 'department', title: 'Department', width: '160px' },
      { 
        data: 'salary',
        title: 'Salary',
        width: '120px',
        render: function(data) {
          return '₱' + parseFloat(data).toLocaleString();
        }
      },
      { data: 'type', title: 'Type', width: '90px' },
      { data: 'status', title: 'Status', width: '90px' },
      {
        data: null,
        title: 'Actions',
        width: '140px',
        orderable: false,
        searchable: false,
        render: function(data, type, row) {
          return `
            <button class="btn btn-sm btn-warning" onclick="editEmployee(${row.id})">Edit</button>
            <button class="btn btn-sm btn-danger" onclick="deleteEmployee(${row.id})">Delete</button>
          `;
        }
      }
    ],
    pageLength: 10,
    lengthMenu: [5, 10, 25, 50],
    language: {
      search: "Search employees:",
      lengthMenu: "Show _MENU_ employees per page",
      info: "Showing _START_ to _END_ of _TOTAL_ employees",
      infoEmpty: "No employees found",
      infoFiltered: "(filtered from _MAX_ total employees)",
      paginate: {
        first: "First",
        last: "Last",
        next: "Next",
        previous: "Previous"
      }
    }
  });
}

window.onclick = function(event) {
  const modal = document.getElementById('employeeModal');
  if (event.target === modal) {
    closeModal();
  }
};

document.addEventListener('DOMContentLoaded', function() {
  displayEmployees();
  const employeeForm = document.getElementById('employeeForm');
  if (employeeForm) {
    employeeForm.addEventListener('submit', function(event) {
      event.preventDefault();
      saveEmployee();
    });
  }
});