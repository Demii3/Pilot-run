const API_URL = './employees_api.php';
let employeesData = [];
let currentPage = 1;
const itemsPerPage = 5;
let totalPages = 1;
let isSearchActive = false;
let searchResults = [];

function showNotification(message, type = 'info') {
  const existingNotifications = document.querySelectorAll('.notification');
  existingNotifications.forEach(notification => notification.remove());

  const notification = document.createElement('div');
  notification.className = `notification notification-${type}`;
  notification.innerHTML = `
    <div class="notification-content">
      <span>${message}</span>
      <button class="notification-close" onclick="this.parentElement.parentElement.remove()">&times;</button>
    </div>
  `;

  if (!document.getElementById('notification-styles')) {
    const styles = document.createElement('style');
    styles.id = 'notification-styles';
    styles.textContent = `
      .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
        min-width: 300px;
        max-width: 500px;
        padding: 0;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        animation: slideIn 0.3s ease-out;
      }
      .notification-success {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
      }
      .notification-error {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
      }
      .notification-info {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
      }
      .notification-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
      }
      .notification-close {
        background: none;
        border: none;
        color: white;
        font-size: 20px;
        cursor: pointer;
        padding: 0;
        margin-left: 10px;
        opacity: 0.8;
      }
      .notification-close:hover {
        opacity: 1;
      }
      @keyframes slideIn {
        from {
          transform: translateX(100%);
          opacity: 0;
        }
        to {
          transform: translateX(0);
          opacity: 1;
        }
      }
      @keyframes slideOut {
        from {
          transform: translateX(0);
          opacity: 1;
        }
        to {
          transform: translateX(100%);
          opacity: 0;
        }
      }
    `;
    document.head.appendChild(styles);
  }

  document.body.appendChild(notification);
  setTimeout(() => {
    if (notification.parentElement) {
      notification.style.animation = 'slideOut 0.3s ease-in';
      setTimeout(() => notification.remove(), 300);
    }
  }, 5000);
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

async function fetchEmployees() {
  const result = await fetchJson(API_URL);
  if (!result.success) {
    throw new Error(result.message || 'Failed to load employees');
  }
  employeesData = result.data || [];
  return employeesData;
}

function displayPage(dataToDisplay) {
  const tableBody = document.querySelector('#employeeTable tbody');
  tableBody.innerHTML = '';

  if (dataToDisplay.length === 0) {
    tableBody.innerHTML = '<tr><td colspan="10" class="text-center">No employees found</td></tr>';
    updatePaginationInfo(0);
    return;
  }

  const startIndex = (currentPage - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  const pageData = dataToDisplay.slice(startIndex, endIndex);

  pageData.forEach(emp => {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td>${emp.id}</td>
      <td>${emp.name}</td>
      <td>${emp.email}</td>
      <td>${emp.username || ''}</td>
      <td>${emp.password || ''}</td>
      <td>${emp.position}</td>
      <td>${emp.department}</td>
      <td>₱${parseFloat(emp.salary).toLocaleString()}</td>
      <td>${emp.type || ''}</td>
      <td>${emp.status || 'Inactive'}</td>
      <td>
        <button class="btn btn-sm btn-warning" onclick="editEmployee(${emp.id})">Edit</button>
        <button class="btn btn-sm btn-danger" onclick="deleteEmployee(${emp.id})">Delete</button>
      </td>
    `;
    tableBody.appendChild(row);
  });

  updatePaginationInfo(dataToDisplay.length);
}

function updatePaginationInfo(totalRecords) {
  const currentPageElement = document.getElementById('currentPage');
  const totalPagesElement = document.getElementById('totalPages');

  if (currentPageElement) currentPageElement.textContent = currentPage;
  if (totalPagesElement) totalPagesElement.textContent = totalPages || 1;
}

async function displayEmployees() {
  const tableBody = document.querySelector('#employeeTable tbody');
  if (!tableBody) return;

  try {
    await fetchEmployees();
  } catch (error) {
    tableBody.innerHTML = '<tr><td colspan="10" class="text-center">Unable to load employees</td></tr>';
    showNotification(error.message, 'error');
    return;
  }

  isSearchActive = false;
  currentPage = 1;
  totalPages = Math.ceil(employeesData.length / itemsPerPage);
  displayPage(employeesData);
}

function openAddForm() {
  document.getElementById('employeeForm').reset();
  document.getElementById('employeeId').value = '';
  document.getElementById('status').value = 'Active';
  document.getElementById('formTitle').textContent = 'Add New Employee';
  document.getElementById('employeeModal').style.display = 'block';
  document.body.classList.add('modal-open');
}

function closeModal() {
  document.getElementById('employeeModal').style.display = 'none';
  document.body.classList.remove('modal-open');
}

async function getEmployeeById(id) {
  const parsedId = parseInt(id, 10);
  let employee = employeesData.find(emp => parseInt(emp.id, 10) === parsedId);
  if (employee) {
    return employee;
  }

  const result = await fetchJson(`${API_URL}?id=${encodeURIComponent(parsedId)}`);
  if (!result.success) {
    return null;
  }

  employee = result.data;
  return employee;
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
  document.getElementById('employeeModal').style.display = 'block';
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

  if (!name || !email || !username || (!id && !password) || !type || !position || !department || !salary || !joinDate || !status) {
    showNotification('Please fill in all fields', 'error');
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

function searchEmployees() {
  const searchTerm = document.getElementById('searchInput').value.toLowerCase();

  if (!searchTerm) {
    isSearchActive = false;
    currentPage = 1;
    totalPages = Math.ceil(employeesData.length / itemsPerPage);
    displayPage(employeesData);
    return;
  }

  searchResults = employeesData.filter(emp =>
    emp.name.toLowerCase().includes(searchTerm) ||
    emp.email.toLowerCase().includes(searchTerm) ||
    (emp.username || '').toLowerCase().includes(searchTerm) ||
    emp.position.toLowerCase().includes(searchTerm) ||
    emp.department.toLowerCase().includes(searchTerm) ||
    (emp.type || '').toLowerCase().includes(searchTerm) ||
    (emp.status || 'Inactive').toLowerCase().includes(searchTerm)
  );

  isSearchActive = true;
  currentPage = 1;
  totalPages = Math.ceil(searchResults.length / itemsPerPage);
  displayPage(searchResults);
}

function exportToExcel() {
  const employees = employeesData;
  if (employees.length === 0) {
    showNotification('No employee data to export', 'error');
    return;
  }

  const headers = ['ID', 'Name', 'Email', 'Username', 'Password', 'Type', 'Position', 'Department', 'Salary', 'Join Date', 'Status'];
  const csvContent = [
    headers.join(','),
    ...employees.map(emp => [
      emp.id,
      `"${emp.name}"`,
      emp.email,
      `"${emp.username || ''}"`,
      `"${emp.password || ''}"`,
      emp.type || '',
      `"${emp.position}"`,
      `"${emp.department}"`,
      emp.salary,
      emp.join_date || emp.joinDate,
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

function parseCSVLine(line) {
  const result = [];
  let current = '';
  let inQuotes = false;

  for (let i = 0; i < line.length; i++) {
    const char = line[i];
    if (char === '"') {
      inQuotes = !inQuotes;
    } else if (char === ',' && !inQuotes) {
      result.push(current);
      current = '';
    } else {
      current += char;
    }
  }

  result.push(current);
  return result;
}

async function importFromExcel(input) {
  const file = input.files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = async function(e) {
    const csv = e.target.result;
    const lines = csv.split('\n').filter(line => line.trim() !== '');

    if (lines.length < 2) {
      showNotification('Invalid file format. Please check your CSV file.', 'error');
      return;
    }

    const headers = lines[0].split(',').map(h => h.replace(/"/g, '').trim());
    const requiredHeaders = ['ID', 'Name', 'Email', 'Username', 'Password', 'Type', 'Position', 'Department', 'Salary', 'Join Date'];

    const headersMatch = requiredHeaders.every(expected =>
      headers.some(header => header.toLowerCase() === expected.toLowerCase())
    );

    if (!headersMatch) {
      showNotification('Invalid CSV format. Expected headers: ID, Name, Email, Position, Department, Salary, Join Date', 'error');
      return;
    }

    let successCount = 0;
    let errorCount = 0;

    for (let i = 1; i < lines.length; i++) {
      try {
        const values = parseCSVLine(lines[i]);
        if (values.length >= 11) {
          const employee = {
            name: values[1].replace(/"/g, '').trim(),
            email: values[2].replace(/"/g, '').trim(),
            username: values[3].replace(/"/g, '').trim(),
            password: values[4].replace(/"/g, '').trim(),
            type: values[5].replace(/"/g, '').trim() || 'Emp',
            position: values[6].replace(/"/g, '').trim(),
            department: values[7].replace(/"/g, '').trim(),
            salary: parseFloat(values[8].replace(/"/g, '').trim()),
            joinDate: values[9].replace(/"/g, '').trim(),
            status: values[10] ? values[10].replace(/"/g, '').trim() : 'Inactive'
          };

          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (employee.name && employee.email && employee.position && employee.department && !isNaN(employee.salary) && employee.joinDate && emailRegex.test(employee.email)) {
            const result = await fetchJson(API_URL, {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify(employee)
            });
            if (result.success) {
              successCount++;
            } else {
              errorCount++;
            }
          } else {
            errorCount++;
          }
        } else {
          errorCount++;
        }
      } catch (error) {
        errorCount++;
      }
    }

    input.value = '';
    await displayEmployees();

    if (successCount > 0) {
      showNotification(`Import completed! ${successCount} employees added successfully. ${errorCount > 0 ? errorCount + ' rows had errors.' : ''}`, 'success');
    } else {
      showNotification('No valid employees were imported. Please check your file format.', 'error');
    }
  };

  reader.readAsText(file);
}

function nextPage() {
  if (currentPage < totalPages) {
    currentPage++;
    const dataToDisplay = isSearchActive ? searchResults : employeesData;
    displayPage(dataToDisplay);
  }
}

function previousPage() {
  if (currentPage > 1) {
    currentPage--;
    const dataToDisplay = isSearchActive ? searchResults : employeesData;
    displayPage(dataToDisplay);
  }
}

function goToFirstPage() {
  currentPage = 1;
  const dataToDisplay = isSearchActive ? searchResults : employeesData;
  displayPage(dataToDisplay);
}

function goToLastPage() {
  currentPage = totalPages;
  const dataToDisplay = isSearchActive ? searchResults : employeesData;
  displayPage(dataToDisplay);
}

window.onclick = function(event) {
  const modal = document.getElementById('employeeModal');
  if (event.target === modal) {
    closeModal();
  }
};

document.addEventListener('DOMContentLoaded', function() {
  displayEmployees();
});
