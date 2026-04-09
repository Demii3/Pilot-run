class EmployeeCRUD {
  constructor() {
    this.storageKey = 'employees_data';
    this.initializeStorage();
  }

  initializeStorage() {
    if (!localStorage.getItem(this.storageKey)) {
      const sampleData = [
        {
          id: 1,
          name: 'John Doe',
          email: 'john.doe@company.com',
          position: 'Manager',
          department: 'IT',
          salary: 75000,
          joinDate: '2022-01-15',
          status: 'Active'
        },
        {
          id: 2,
          name: 'Jane Smith',
          email: 'jane.smith@company.com',
          position: 'Developer',
          department: 'IT',
          salary: 65000,
          joinDate: '2022-06-20',
          status: 'Active'
        }
      ];
      localStorage.setItem(this.storageKey, JSON.stringify(sampleData));
    }
  }

  getAllEmployees() {
    const data = localStorage.getItem(this.storageKey);
    return data ? JSON.parse(data) : [];
  }

  getEmployeeById(id) {
    const employees = this.getAllEmployees();
    return employees.find(emp => emp.id === parseInt(id));
  }

  createEmployee(employeeData) {
    const employees = this.getAllEmployees();
    const newId = employees.length > 0 ? Math.max(...employees.map(e => e.id)) + 1 : 1;
    
    const newEmployee = {
      id: newId,
      ...employeeData
    };
    
    employees.push(newEmployee);
    localStorage.setItem(this.storageKey, JSON.stringify(employees));
    return newEmployee;
  }

  updateEmployee(id, employeeData) {
    const employees = this.getAllEmployees();
    const index = employees.findIndex(emp => emp.id === parseInt(id));
    
    if (index !== -1) {
      employees[index] = {
        ...employees[index],
        ...employeeData,
        id: parseInt(id)
      };
      localStorage.setItem(this.storageKey, JSON.stringify(employees));
      return employees[index];
    }
    return null;
  }

  deleteEmployee(id) {
    const employees = this.getAllEmployees();
    const filteredEmployees = employees.filter(emp => emp.id !== parseInt(id));
    localStorage.setItem(this.storageKey, JSON.stringify(filteredEmployees));
    return true;
  }

  clearAllData() {
    localStorage.removeItem(this.storageKey);
    this.initializeStorage();
  }
}

const employeeCRUD = new EmployeeCRUD();

// Pagination variables
let currentPage = 1;
const itemsPerPage = 5;
let totalPages = 1;
let isSearchActive = false;
let searchResults = [];

// Notif function
function showNotification(message, type = 'info') {
  // Remove notifications
  const existingNotifications = document.querySelectorAll('.notification');
  existingNotifications.forEach(notification => notification.remove());

  // Notification element
  const notification = document.createElement('div');
  notification.className = `notification notification-${type}`;
  notification.innerHTML = `
    <div class="notification-content">
      <span>${message}</span>
      <button class="notification-close" onclick="this.parentElement.parentElement.remove()">&times;</button>
    </div>
  `;

  // Notif Styles
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

  // Add to page
  document.body.appendChild(notification);

  // Auto remove after 5 seconds
  setTimeout(() => {
    if (notification.parentElement) {
      notification.style.animation = 'slideOut 0.3s ease-in';
      setTimeout(() => notification.remove(), 300);
    }
  }, 5000);
}

function displayEmployees() {
  const employees = employeeCRUD.getAllEmployees();
  const tableBody = document.querySelector('#employeeTable tbody');
  
  if (!tableBody) return;
  
  isSearchActive = false;
  currentPage = 1;
  
  if (employees.length === 0) {
    tableBody.innerHTML = '<tr><td colspan="8" class="text-center">No employees found</td></tr>';
    updatePaginationInfo(0);
    return;
  }
  
  totalPages = Math.ceil(employees.length / itemsPerPage);
  displayPage(employees);
}

function displayPage(dataToDisplay) {
  const tableBody = document.querySelector('#employeeTable tbody');
  tableBody.innerHTML = '';
  
  if (dataToDisplay.length === 0) {
    tableBody.innerHTML = '<tr><td colspan="8" class="text-center">No employees found</td></tr>';
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
      <td>${emp.position}</td>
      <td>${emp.department}</td>
      <td>₱${parseFloat(emp.salary).toLocaleString()}</td>
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

function openAddForm() {
  document.getElementById('employeeForm').reset();
  document.getElementById('employeeId').value = '';
  document.getElementById('status').value = 'Active';
  document.getElementById('formTitle').textContent = 'Add New Employee';
  document.getElementById('employeeModal').style.display = 'block';
}

function closeModal() {
  document.getElementById('employeeModal').style.display = 'none';
}

function editEmployee(id) {
  const employee = employeeCRUD.getEmployeeById(id);
  if (employee) {
    document.getElementById('employeeId').value = employee.id;
    document.getElementById('name').value = employee.name;
    document.getElementById('email').value = employee.email;
    document.getElementById('position').value = employee.position;
    document.getElementById('department').value = employee.department;
    document.getElementById('salary').value = employee.salary;
    document.getElementById('joinDate').value = employee.joinDate;
    document.getElementById('status').value = employee.status || 'Active';
    document.getElementById('formTitle').textContent = 'Edit Employee';
    document.getElementById('employeeModal').style.display = 'block';
  }
}

function deleteEmployee(id) {
  if (confirm('Are you sure you want to delete this employee?')) {
    employeeCRUD.deleteEmployee(id);
    displayEmployees();
    showNotification('Employee deleted successfully!', 'success');
  }
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
  const position = document.getElementById('position').value.trim();
  const department = document.getElementById('department').value.trim();
  const salary = document.getElementById('salary').value.trim();
  const joinDate = document.getElementById('joinDate').value;
  const status = document.getElementById('status').value;

  if (!name || !email || !position || !department || !salary || !joinDate || !status) {
    showNotification('Please fill in all fields', 'error');
    saveButton.disabled = false;
    saveButton.textContent = 'Save Employee';
    return;
  }
  
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    showNotification('Please enter a valid email', 'error');
    saveButton.disabled = false;
    saveButton.textContent = 'Save Employee';
    return;
  }
  
  const employeeData = {
    name,
    email,
    position,
    department,
    salary: parseFloat(salary),
    joinDate,
    status
  };
  
  try {
    if (id) {
      // Update
      employeeCRUD.updateEmployee(id, employeeData);
      showNotification('Employee updated successfully!', 'success');
    } else {
      // Create new
      employeeCRUD.createEmployee(employeeData);
      showNotification('Employee added successfully!', 'success');
    }
    closeModal();
    displayEmployees();
  } catch (error) {
    showNotification('An error occurred: ' + error.message, 'error');
  } finally {
    if (saveButton) {
      saveButton.disabled = false;
      saveButton.textContent = 'Save Employee';
    }
  }
}

function searchEmployees() {
  const searchTerm = document.getElementById('searchInput').value.toLowerCase();
  const employees = employeeCRUD.getAllEmployees();
  
  if (!searchTerm) {
    displayEmployees();
    return;
  }
  
  searchResults = employees.filter(emp =>
    emp.name.toLowerCase().includes(searchTerm) ||
    emp.email.toLowerCase().includes(searchTerm) ||
    emp.position.toLowerCase().includes(searchTerm) ||
    emp.department.toLowerCase().includes(searchTerm) ||
    (emp.status || 'Inactive').toLowerCase().includes(searchTerm)
  );
  
  isSearchActive = true;
  currentPage = 1;
  totalPages = Math.ceil(searchResults.length / itemsPerPage);
  displayPage(searchResults);
}

function exportToExcel() {
  const employees = employeeCRUD.getAllEmployees();
  
  if (employees.length === 0) {
    showNotification('No employee data to export', 'error');
    return;
  }

  // Create CSV file
  const headers = ['ID', 'Name', 'Email', 'Position', 'Department', 'Salary', 'Join Date', 'Status'];
  const csvContent = [
    headers.join(','),
    ...employees.map(emp => [
      emp.id,
      `"${emp.name}"`,
      emp.email,
      `"${emp.position}"`,
      `"${emp.department}"`,
      emp.salary,
      emp.joinDate,
      emp.status || 'Inactive'
    ].join(','))
  ].join('\n');

  // Create and download the file
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  
  if (link.download !== undefined) {
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `employees_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
    showNotification('Employee data exported successfully!', 'success');
  } else {
    showNotification('Export not supported in this browser', 'error');
  }
}

function importFromExcel(input) {
  const file = input.files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = function(e) {
    const csv = e.target.result;
    const lines = csv.split('\n').filter(line => line.trim() !== '');
    
    if (lines.length < 2) {
      showNotification('Invalid file format. Please check your CSV file.', 'error');
      return;
    }

    const headers = lines[0].split(',').map(h => h.replace(/"/g, '').trim());
    const requiredHeaders = ['ID', 'Name', 'Email', 'Position', 'Department', 'Salary', 'Join Date'];
    const hasStatusHeader = headers.some(header => header.toLowerCase() === 'status');

    const headersMatch = requiredHeaders.every(expected => 
      headers.some(header => header.toLowerCase() === expected.toLowerCase())
    );

    if (!headersMatch) {
      showNotification('Invalid CSV format. Expected headers: ID, Name, Email, Position, Department, Salary, Join Date', 'error');
      return;
    }

    const employees = [];
    let successCount = 0;
    let errorCount = 0;

    for (let i = 1; i < lines.length; i++) {
      try {
        const values = parseCSVLine(lines[i]);
        if (values.length >= 7) {
          const employee = {
            name: values[1].replace(/"/g, '').trim(),
            email: values[2].replace(/"/g, '').trim(),
            position: values[3].replace(/"/g, '').trim(),
            department: values[4].replace(/"/g, '').trim(),
            salary: parseFloat(values[5].replace(/"/g, '').trim()),
            joinDate: values[6].replace(/"/g, '').trim(),
            status: values[7] ? values[7].replace(/"/g, '').trim() : 'Inactive'
          };

          // Validate required fields
          if (employee.name && employee.email && employee.position && 
              employee.department && !isNaN(employee.salary) && employee.joinDate) {
            
            // Validate email format
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (emailRegex.test(employee.email)) {
              employeeCRUD.createEmployee(employee);
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

    // Clear the file input
    input.value = '';

    // Refresh
    displayEmployees();

    // Show results
    if (successCount > 0) {
      showNotification(`Import completed! ${successCount} employees added successfully. ${errorCount > 0 ? errorCount + ' rows had errors.' : ''}`, 'success');
    } else {
      showNotification('No valid employees were imported. Please check your file format.', 'error');
    }
  };

  reader.readAsText(file);
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

// Pagination Navigation Functions
function nextPage() {
  if (currentPage < totalPages) {
    currentPage++;
    const dataToDisplay = isSearchActive ? searchResults : employeeCRUD.getAllEmployees();
    displayPage(dataToDisplay);
  }
}

function previousPage() {
  if (currentPage > 1) {
    currentPage--;
    const dataToDisplay = isSearchActive ? searchResults : employeeCRUD.getAllEmployees();
    displayPage(dataToDisplay);
  }
}

function goToFirstPage() {
  currentPage = 1;
  const dataToDisplay = isSearchActive ? searchResults : employeeCRUD.getAllEmployees();
  displayPage(dataToDisplay);
}

function goToLastPage() {
  currentPage = totalPages;
  const dataToDisplay = isSearchActive ? searchResults : employeeCRUD.getAllEmployees();
  displayPage(dataToDisplay);
}

window.onclick = function(event) {
  const modal = document.getElementById('employeeModal');
  if (event.target === modal) {
    modal.style.display = 'none';
  }
};

document.addEventListener('DOMContentLoaded', function() {
  displayEmployees();
});
