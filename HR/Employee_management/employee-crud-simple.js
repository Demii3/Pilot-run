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

function normalizeText(value) {
  return value === null || value === undefined ? '' : String(value).trim();
}

function getRowValue(row, possibleNames) {
  if (!row || typeof row !== 'object') {
    return '';
  }

  const entries = Object.entries(row);
  for (const name of possibleNames) {
    const match = entries.find(([key]) => key.trim().toLowerCase() === name.trim().toLowerCase());
    if (match) {
      return match[1];
    }
  }

  return '';
}

function deriveUsername(name, email, fallbackIndex) {
  const fromEmail = normalizeText(email).split('@')[0];
  if (fromEmail) {
    return fromEmail;
  }

  const fromName = normalizeText(name)
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '.')
    .replace(/^\.+|\.+$/g, '');

  if (fromName) {
    return fromName;
  }

  return `employee${fallbackIndex + 1}`;
}

function normalizeImportedDate(value) {
  if (value instanceof Date && !Number.isNaN(value.getTime())) {
    return value.toISOString().slice(0, 10);
  }

  if (typeof value === 'number' && window.XLSX && XLSX.SSF && typeof XLSX.SSF.parse_date_code === 'function') {
    const parsed = XLSX.SSF.parse_date_code(value);
    if (parsed) {
      const year = String(parsed.y).padStart(4, '0');
      const month = String(parsed.m).padStart(2, '0');
      const day = String(parsed.d).padStart(2, '0');
      return `${year}-${month}-${day}`;
    }
  }

  return normalizeText(value);
}

function normalizeImportedEmployees(rows) {
  if (!Array.isArray(rows)) {
    return [];
  }

  const employees = [];

  rows.forEach((row, index) => {
    const name = normalizeText(getRowValue(row, ['Name', 'Full Name', 'name']));
    const email = normalizeText(getRowValue(row, ['Email', 'email']));
    const position = normalizeText(getRowValue(row, ['Position', 'position']));
    const department = normalizeText(getRowValue(row, ['Department', 'department']));
    const salary = normalizeText(getRowValue(row, ['Salary', 'salary']));
    const joinDate = normalizeImportedDate(getRowValue(row, ['Join Date', 'join_date', 'JoinDate', 'joinDate']));
    const status = normalizeText(getRowValue(row, ['Status', 'status'])) || 'Active';
    const username = normalizeText(getRowValue(row, ['Username', 'username'])) || deriveUsername(name, email, index);
    const type = normalizeText(getRowValue(row, ['Type', 'type'])) || 'Emp';
    const password = normalizeText(getRowValue(row, ['Password', 'password'])) || username;
    const idValue = normalizeText(getRowValue(row, ['ID', 'Id', 'id', 'Emp_id', 'emp_id']));

    if (!name || !email || !position || !department || !salary || !joinDate) {
      return;
    }

    const employee = {
      name,
      email,
      username,
      password,
      type,
      position,
      department,
      salary: parseFloat(salary),
      joinDate,
      status
    };

    employees.push(employee);
  });

  return employees;
}

async function parseImportedFile(file) {
  const fileName = (file && file.name ? file.name : '').toLowerCase();
  if (!window.XLSX) {
    throw new Error('Spreadsheet parser is not loaded. Refresh the page and try again.');
  }

  if (fileName.endsWith('.csv')) {
    const text = await file.text();
    const workbook = XLSX.read(text, { type: 'string' });
    const sheetName = workbook.SheetNames[0];
    if (!sheetName) {
      return [];
    }

    return XLSX.utils.sheet_to_json(workbook.Sheets[sheetName], { defval: '' });
  }

  const buffer = await file.arrayBuffer();
  const workbook = XLSX.read(buffer, { type: 'array' });
  const sheetName = workbook.SheetNames[0];
  if (!sheetName) {
    return [];
  }

  return XLSX.utils.sheet_to_json(workbook.Sheets[sheetName], { defval: '' });
}

async function importFromExcel(input) {
  const file = input && input.files ? input.files[0] : null;
  if (!file) {
    return;
  }

  try {
    const rawRows = await parseImportedFile(file);
    const employees = normalizeImportedEmployees(rawRows);

    if (employees.length === 0) {
      showNotification('No valid employee rows were found in the file. Make sure the file includes Name, Email, Position, Department, Salary, and Join Date.', 'error');
      input.value = '';
      return;
    }

    const result = await fetchJson(API_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        action: 'upsert_many',
        employees
      })
    });

    if (!result.success) {
      throw new Error(result.message || 'Unable to import employees');
    }

    showNotification(`Imported ${employees.length} employee(s) successfully. Existing rows were updated and new rows were added.`, 'success');
    input.value = '';
    displayEmployees();
  } catch (error) {
    console.error(error);
    showNotification('Import failed: ' + error.message, 'error');
    input.value = '';
  }
}

function formatCurrency(value) {
  const numericValue = Number(value);
  if (!Number.isFinite(numericValue)) {
    return '—';
  }

  return '₱' + numericValue.toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
}

function setDetailField(fieldId, value, emptyValue = '—') {
  const field = document.getElementById(fieldId);
  if (field) {
    const normalizedValue = value === null || value === undefined || value === '' ? emptyValue : value;
    if ('value' in field) {
      field.value = normalizedValue;
    } else {
      field.textContent = normalizedValue;
    }
  }
}

function openEmployeeDetails(employee) {
  if (!employee) {
    return;
  }

  setDetailField('detailId', employee.id);
  setDetailField('detailName', employee.name);
  setDetailField('detailEmail', employee.email);
  setDetailField('detailUsername', employee.username);
  setDetailField('detailType', employee.type, '');
  setDetailField('detailPosition', employee.position);
  setDetailField('detailDepartment', employee.department);
  setDetailField('detailSalary', formatCurrency(employee.salary));
  setDetailField('detailJoinDate', employee.join_date || employee.joinDate, '');
  setDetailField('detailStatus', employee.status, '');

  const title = document.getElementById('detailTitle');
  if (title) {
    title.textContent = `Employee Details - ${employee.name || 'Employee'}`;
  }

  const modal = document.getElementById('employeeDetailModal');
  if (modal) {
    modal.classList.add('show');
    document.body.classList.add('modal-open');
  }
}

function closeEmployeeDetails() {
  const modal = document.getElementById('employeeDetailModal');
  if (modal) {
    modal.classList.remove('show');
  }
  document.body.classList.remove('modal-open');
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

async function exportToExcel() {
  const employees = employeesData;
  if (employees.length === 0) {
    showNotification('No employee data to export', 'error');
    return;
  }

  const headers = ['ID', 'Name', 'Email', 'Username', 'Position', 'Department', 'Salary', 'Join Date', 'Type', 'Status'];
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
      emp.join_date || emp.joinDate || '',
      emp.type || '',
      emp.status || 'Inactive'
    ].join(','))
  ].join('\n');

  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });

  if (window.showSaveFilePicker) {
    try {
      const fileHandle = await window.showSaveFilePicker({
        suggestedName: `employees_${new Date().toISOString().split('T')[0]}.csv`,
        types: [
          {
            description: 'CSV files',
            accept: { 'text/csv': ['.csv'] }
          }
        ]
      });

      const writable = await fileHandle.createWritable();
      await writable.write(blob);
      await writable.close();
      showNotification('Employee data exported successfully!', 'success');
      return;
    } catch (error) {
      if (error && error.name === 'AbortError') {
        return;
      }

      console.error('Save dialog export failed, falling back to browser download.', error);
    }
  }

  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);

  link.setAttribute('href', url);
  link.setAttribute('download', `employees_${new Date().toISOString().split('T')[0]}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
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
      {
        data: null,
        title: 'Employee',
        width: '320px',
        render: function(data, type, row) {
          return `
            <div class="employee-summary">
              <strong>${row.name || ''}</strong>
              <span>${row.email || ''}</span>
              <small>${row.position ? `Position: ${row.position}` : ''}</small>
            </div>
          `;
        }
      },
      { data: 'department', title: 'Department', width: '160px' },
      { 
        data: 'salary',
        title: 'Salary',
        width: '120px',
        render: function(data) {
          return formatCurrency(data);
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
            <div class="employee-actions">
              <button class="btn btn-sm btn-warning" onclick="editEmployee(${row.id})">Edit</button>
              <button class="btn btn-sm btn-danger" onclick="deleteEmployee(${row.id})">Delete</button>
            </div>
          `;
        }
      },
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
    },
    createdRow: function(row) {
      row.style.cursor = 'pointer';
      row.title = 'Double-click to view employee details';
    }
  });

  $('#employeeTable tbody')
    .off('dblclick.employeeDetails')
    .on('dblclick.employeeDetails', 'tr', function(event) {
      if ($(event.target).closest('button').length) {
        return;
      }

      const rowData = employeeTable.row(this).data();
      if (rowData) {
        openEmployeeDetails(rowData);
      }
    });
}

window.onclick = function(event) {
  const modal = document.getElementById('employeeModal');
  if (!modal) return;
  if (event.target === modal) {
    closeModal();
    return;
  }
  // If Bootstrap inserted a backdrop and it's clicked, close our modal too
  if (event.target && event.target.classList && event.target.classList.contains('modal-backdrop')) {
    closeModal();
    // remove any bootstrap backdrops left behind
    const backdrops = document.getElementsByClassName('modal-backdrop');
    Array.from(backdrops).forEach(b => b.parentNode && b.parentNode.removeChild(b));
  }

  const detailModal = document.getElementById('employeeDetailModal');
  if (detailModal && event.target === detailModal) {
    closeEmployeeDetails();
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