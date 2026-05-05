// Premium Tables CRUD Management Module

class PremiumTableManager {
  constructor(tableType, tableId, apiUrl) {
    this.tableType = tableType;
    this.tableId = tableId;
    this.apiUrl = apiUrl;
    this.currentYear = 2026;
    this.tableData = [];
  }

  async loadTableData(year = 2026) {
    this.currentYear = year;
    try {
      const response = await fetch(`${this.apiUrl}?year=${year}`);
      const result = await response.json();
      
      if (!result.success) {
        throw new Error(result.message || 'Failed to load data');
      }
      
      this.tableData = result.data || [];
      this.renderTable();
      return true;
    } catch (error) {
      console.error(`Error loading ${this.tableType} data:`, error);
      alert(`Error loading ${this.tableType} data: ${error.message}`);
      return false;
    }
  }

  renderTable() {
    const tableBody = document.querySelector(`#${this.tableId} tbody`);
    if (!tableBody) return;
    
    if (this.tableData.length === 0) {
      tableBody.innerHTML = `<tr><td colspan="100%" style="text-align: center; padding: 20px; color: #999;">No data available for year ${this.currentYear}</td></tr>`;
      return;
    }
    
    tableBody.innerHTML = '';
    this.tableData.forEach(row => {
      const tr = document.createElement('tr');
      tr.innerHTML = this.formatTableRow(row);
      tableBody.appendChild(tr);
    });
  }

  formatTableRow(row) {
    // Override in subclasses
    return '';
  }

  async deleteRecord(id) {
    if (!confirm('Are you sure you want to delete this record?')) {
      return false;
    }
    
    try {
      const response = await fetch(`${this.apiUrl}?id=${id}`, { method: 'DELETE' });
      const result = await response.json();
      
      if (!result.success) {
        throw new Error(result.message || 'Failed to delete record');
      }
      
      await this.loadTableData(this.currentYear);
      return true;
    } catch (error) {
      alert(`Error deleting record: ${error.message}`);
      return false;
    }
  }

  async copyPreviousYearData() {
    const targetYear = this.currentYear;
    const sourceYear = targetYear - 1;

    if (targetYear <= 2026) {
      alert('Select 2027 or later to import the previous year computations.');
      return false;
    }

    if (!confirm(`Import ${sourceYear} computations into ${targetYear}? This will replace any existing records for ${targetYear}.`)) {
      return false;
    }

    try {
      const sourceResponse = await fetch(`${this.apiUrl}?year=${sourceYear}`);
      const sourceResult = await sourceResponse.json();

      if (!sourceResult.success) {
        throw new Error(sourceResult.message || `Failed to load ${sourceYear} data`);
      }

      const sourceRows = sourceResult.data || [];
      if (sourceRows.length === 0) {
        throw new Error(`No ${sourceYear} data found to import`);
      }

      const targetResponse = await fetch(`${this.apiUrl}?year=${targetYear}`);
      const targetResult = await targetResponse.json();
      if (!targetResult.success) {
        throw new Error(targetResult.message || `Failed to check ${targetYear} data`);
      }

      for (const existingRow of targetResult.data || []) {
        const deleteResponse = await fetch(`${this.apiUrl}?id=${existingRow.id}`, { method: 'DELETE' });
        const deleteResult = await deleteResponse.json();
        if (!deleteResult.success) {
          throw new Error(deleteResult.message || `Failed to clear existing ${targetYear} record ${existingRow.id}`);
        }
      }

      for (const sourceRow of sourceRows) {
        const payload = this.buildCopyPayload(sourceRow, targetYear);
        const createResponse = await fetch(this.apiUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const createResult = await createResponse.json();
        if (!createResult.success) {
          throw new Error(createResult.message || `Failed to import ${sourceYear} record`);
        }
      }

      await this.loadTableData(targetYear);
      alert(`Imported ${sourceYear} computations into ${targetYear}.`);
      return true;
    } catch (error) {
      alert(`Error importing previous year data: ${error.message}`);
      return false;
    }
  }

  openEditForm(id) {
    const record = this.tableData.find(r => r.id === id);
    if (record) {
      this.populateFormForEdit(record);
      this.showForm();
    }
  }

  openNewForm() {
    this.clearForm();
    this.showForm();
  }

  populateFormForEdit(record) {
    // Override in subclasses
  }

  clearForm() {
    // Override in subclasses
  }

  buildCopyPayload(record, targetYear) {
    // Override in subclasses
    return null;
  }

  showForm() {
    // Override in subclasses
  }

  async saveRecord() {
    // Override in subclasses
  }
}

class TaxTableManager extends PremiumTableManager {
  constructor() {
    super('Tax Table', 'taxTable', 'tax_table_api.php');
  }

  formatTableRow(row) {
    return `
      <td>${row.description || ''}</td>
      <td>${parseFloat(row.tax_rate).toFixed(2)}%</td>
      <td>${row.base_tax ? parseFloat(row.base_tax).toLocaleString('en-US', {minimumFractionDigits: 2}) : 'N/A'}</td>
      <td>
        <button class="btn btn-sm btn-warning" onclick="taxManager.openEditForm(${row.id})">Edit</button>
        <button class="btn btn-sm btn-danger" onclick="taxManager.deleteRecord(${row.id})">Delete</button>
      </td>
    `;
  }

  populateFormForEdit(record) {
    document.getElementById('taxFormId').value = record.id;
    document.getElementById('taxYear').value = record.year;
    document.getElementById('taxIncomeFrom').value = record.income_from;
    document.getElementById('taxIncomeTo').value = record.income_to || '';
    document.getElementById('taxRate').value = record.tax_rate;
    document.getElementById('taxBaseAmount').value = record.base_tax || 0;
    document.getElementById('taxDescription').value = record.description || '';
  }

  clearForm() {
    document.getElementById('taxFormId').value = '';
    document.getElementById('taxYear').value = 2026;
    document.getElementById('taxIncomeFrom').value = '';
    document.getElementById('taxIncomeTo').value = '';
    document.getElementById('taxRate').value = '';
    document.getElementById('taxBaseAmount').value = 0;
    document.getElementById('taxDescription').value = '';
  }

  buildCopyPayload(record, targetYear) {
    return {
      year: targetYear,
      income_from: record.income_from,
      income_to: record.income_to,
      tax_rate: record.tax_rate,
      base_tax: record.base_tax,
      description: record.description
    };
  }

  showForm() {
    const form = document.getElementById('taxTableForm');
    if (form) {
      form.style.display = 'block';
    }
  }

  hideForm() {
    const form = document.getElementById('taxTableForm');
    if (form) {
      form.style.display = 'none';
    }
  }

  async saveRecord() {
    const id = document.getElementById('taxFormId').value;
    const year = parseInt(document.getElementById('taxYear').value);
    const incomeFrom = parseFloat(document.getElementById('taxIncomeFrom').value);
    const incomeTo = document.getElementById('taxIncomeTo').value ? parseFloat(document.getElementById('taxIncomeTo').value) : null;
    const taxRate = parseFloat(document.getElementById('taxRate').value);
    const baseTax = parseFloat(document.getElementById('taxBaseAmount').value);
    const description = document.getElementById('taxDescription').value;

    if (!year || !incomeFrom || !taxRate) {
      alert('Please fill in all required fields');
      return false;
    }

    const payload = {
      year,
      income_from: incomeFrom,
      income_to: incomeTo,
      tax_rate: taxRate,
      base_tax: baseTax,
      description
    };

    if (id) {
      payload.id = parseInt(id);
    }

    try {
      const response = await fetch(this.apiUrl, {
        method: id ? 'PUT' : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      const result = await response.json();
      if (!result.success) {
        throw new Error(result.message || 'Save failed');
      }

      this.hideForm();
      await this.loadTableData(year);
      alert(id ? 'Record updated' : 'Record created');
      return true;
    } catch (error) {
      alert(`Error saving record: ${error.message}`);
      return false;
    }
  }
}

class SssTableManager extends PremiumTableManager {
  constructor() {
    super('SSS Table', 'sssTable', 'sss_table_api.php');
  }

  formatTableRow(row) {
    return `
      <td>${row.description || ''}</td>
      <td>₱${parseFloat(row.monthly_contribution).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
      <td>
        <button class="btn btn-sm btn-warning" onclick="sssManager.openEditForm(${row.id})">Edit</button>
        <button class="btn btn-sm btn-danger" onclick="sssManager.deleteRecord(${row.id})">Delete</button>
      </td>
    `;
  }

  populateFormForEdit(record) {
    document.getElementById('sssFormId').value = record.id;
    document.getElementById('sssYear').value = record.year;
    document.getElementById('sssSalaryFrom').value = record.salary_from;
    document.getElementById('sssSalaryTo').value = record.salary_to || '';
    document.getElementById('sssContribution').value = record.monthly_contribution;
    document.getElementById('sssDescription').value = record.description || '';
  }

  clearForm() {
    document.getElementById('sssFormId').value = '';
    document.getElementById('sssYear').value = 2026;
    document.getElementById('sssSalaryFrom').value = '';
    document.getElementById('sssSalaryTo').value = '';
    document.getElementById('sssContribution').value = '';
    document.getElementById('sssDescription').value = '';
  }

  buildCopyPayload(record, targetYear) {
    return {
      year: targetYear,
      salary_from: record.salary_from,
      salary_to: record.salary_to,
      monthly_contribution: record.monthly_contribution,
      description: record.description
    };
  }

  showForm() {
    const form = document.getElementById('sssTableForm');
    if (form) {
      form.style.display = 'block';
    }
  }

  hideForm() {
    const form = document.getElementById('sssTableForm');
    if (form) {
      form.style.display = 'none';
    }
  }

  async saveRecord() {
    const id = document.getElementById('sssFormId').value;
    const year = parseInt(document.getElementById('sssYear').value);
    const salaryFrom = parseFloat(document.getElementById('sssSalaryFrom').value);
    const salaryTo = document.getElementById('sssSalaryTo').value ? parseFloat(document.getElementById('sssSalaryTo').value) : null;
    const monthlyContribution = parseFloat(document.getElementById('sssContribution').value);
    const description = document.getElementById('sssDescription').value;

    if (!year || !salaryFrom || !monthlyContribution) {
      alert('Please fill in all required fields');
      return false;
    }

    const payload = {
      year,
      salary_from: salaryFrom,
      salary_to: salaryTo,
      monthly_contribution: monthlyContribution,
      description
    };

    if (id) {
      payload.id = parseInt(id);
    }

    try {
      const response = await fetch(this.apiUrl, {
        method: id ? 'PUT' : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      const result = await response.json();
      if (!result.success) {
        throw new Error(result.message || 'Save failed');
      }

      this.hideForm();
      await this.loadTableData(year);
      alert(id ? 'Record updated' : 'Record created');
      return true;
    } catch (error) {
      alert(`Error saving record: ${error.message}`);
      return false;
    }
  }
}

class PhilhealthTableManager extends PremiumTableManager {
  constructor() {
    super('PhilHealth Table', 'philhealthTable', 'philhealth_table_api.php');
  }

  formatTableRow(row) {
    const rateDisplay = row.fixed_amount ? `Fixed ₱${parseFloat(row.fixed_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}` : `${(parseFloat(row.contribution_rate) * 100).toFixed(2)}%`;
    return `
      <td>${row.description || ''}</td>
      <td>${rateDisplay}</td>
      <td>₱${parseFloat(row.maximum_contribution).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
      <td>
        <button class="btn btn-sm btn-warning" onclick="philhealthManager.openEditForm(${row.id})">Edit</button>
        <button class="btn btn-sm btn-danger" onclick="philhealthManager.deleteRecord(${row.id})">Delete</button>
      </td>
    `;
  }

  populateFormForEdit(record) {
    document.getElementById('philhealthFormId').value = record.id;
    document.getElementById('philhealthYear').value = record.year;
    document.getElementById('philhealthSalaryFrom').value = record.salary_from;
    document.getElementById('philhealthSalaryTo').value = record.salary_to || '';
    document.getElementById('philhealthRate').value = record.contribution_rate;
    document.getElementById('philhealthMaximum').value = record.maximum_contribution;
    document.getElementById('philhealthFixed').value = record.fixed_amount || '';
    document.getElementById('philhealthDescription').value = record.description || '';
  }

  clearForm() {
    document.getElementById('philhealthFormId').value = '';
    document.getElementById('philhealthYear').value = 2026;
    document.getElementById('philhealthSalaryFrom').value = '';
    document.getElementById('philhealthSalaryTo').value = '';
    document.getElementById('philhealthRate').value = '';
    document.getElementById('philhealthMaximum').value = '';
    document.getElementById('philhealthFixed').value = '';
    document.getElementById('philhealthDescription').value = '';
  }

  buildCopyPayload(record, targetYear) {
    return {
      year: targetYear,
      salary_from: record.salary_from,
      salary_to: record.salary_to,
      contribution_rate: record.contribution_rate,
      maximum_contribution: record.maximum_contribution,
      fixed_amount: record.fixed_amount,
      description: record.description
    };
  }

  showForm() {
    const form = document.getElementById('philhealthTableForm');
    if (form) {
      form.style.display = 'block';
    }
  }

  hideForm() {
    const form = document.getElementById('philhealthTableForm');
    if (form) {
      form.style.display = 'none';
    }
  }

  async saveRecord() {
    const id = document.getElementById('philhealthFormId').value;
    const year = parseInt(document.getElementById('philhealthYear').value);
    const salaryFrom = parseFloat(document.getElementById('philhealthSalaryFrom').value);
    const salaryTo = document.getElementById('philhealthSalaryTo').value ? parseFloat(document.getElementById('philhealthSalaryTo').value) : null;
    const contributionRate = parseFloat(document.getElementById('philhealthRate').value);
    const maximumContribution = parseFloat(document.getElementById('philhealthMaximum').value);
    const fixedAmount = document.getElementById('philhealthFixed').value ? parseFloat(document.getElementById('philhealthFixed').value) : null;
    const description = document.getElementById('philhealthDescription').value;

    if (!year || !salaryFrom || !contributionRate || !maximumContribution) {
      alert('Please fill in all required fields');
      return false;
    }

    const payload = {
      year,
      salary_from: salaryFrom,
      salary_to: salaryTo,
      contribution_rate: contributionRate,
      maximum_contribution: maximumContribution,
      fixed_amount: fixedAmount,
      description
    };

    if (id) {
      payload.id = parseInt(id);
    }

    try {
      const response = await fetch(this.apiUrl, {
        method: id ? 'PUT' : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      const result = await response.json();
      if (!result.success) {
        throw new Error(result.message || 'Save failed');
      }

      this.hideForm();
      await this.loadTableData(year);
      alert(id ? 'Record updated' : 'Record created');
      return true;
    } catch (error) {
      alert(`Error saving record: ${error.message}`);
      return false;
    }
  }
}

class PagibigTableManager extends PremiumTableManager {
  constructor() {
    super('Pag-IBIG Table', 'pagibigTable', 'pagibig_table_api.php');
  }

  formatTableRow(row) {
    return `
      <td>${row.description || ''}</td>
      <td>${(parseFloat(row.contribution_rate) * 100).toFixed(2)}%</td>
      <td>₱${parseFloat(row.maximum_contribution).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
      <td>
        <button class="btn btn-sm btn-warning" onclick="pagibigManager.openEditForm(${row.id})">Edit</button>
        <button class="btn btn-sm btn-danger" onclick="pagibigManager.deleteRecord(${row.id})">Delete</button>
      </td>
    `;
  }

  populateFormForEdit(record) {
    document.getElementById('pagibigFormId').value = record.id;
    document.getElementById('pagibigYear').value = record.year;
    document.getElementById('pagibigSalaryFrom').value = record.salary_from;
    document.getElementById('pagibigSalaryTo').value = record.salary_to || '';
    document.getElementById('pagibigRate').value = record.contribution_rate;
    document.getElementById('pagibigMaximum').value = record.maximum_contribution;
    document.getElementById('pagibigDescription').value = record.description || '';
  }

  clearForm() {
    document.getElementById('pagibigFormId').value = '';
    document.getElementById('pagibigYear').value = 2026;
    document.getElementById('pagibigSalaryFrom').value = '';
    document.getElementById('pagibigSalaryTo').value = '';
    document.getElementById('pagibigRate').value = '';
    document.getElementById('pagibigMaximum').value = '';
    document.getElementById('pagibigDescription').value = '';
  }

  buildCopyPayload(record, targetYear) {
    return {
      year: targetYear,
      salary_from: record.salary_from,
      salary_to: record.salary_to,
      contribution_rate: record.contribution_rate,
      maximum_contribution: record.maximum_contribution,
      description: record.description
    };
  }

  showForm() {
    const form = document.getElementById('pagibigTableForm');
    if (form) {
      form.style.display = 'block';
    }
  }

  hideForm() {
    const form = document.getElementById('pagibigTableForm');
    if (form) {
      form.style.display = 'none';
    }
  }

  async saveRecord() {
    const id = document.getElementById('pagibigFormId').value;
    const year = parseInt(document.getElementById('pagibigYear').value);
    const salaryFrom = parseFloat(document.getElementById('pagibigSalaryFrom').value);
    const salaryTo = document.getElementById('pagibigSalaryTo').value ? parseFloat(document.getElementById('pagibigSalaryTo').value) : null;
    const contributionRate = parseFloat(document.getElementById('pagibigRate').value);
    const maximumContribution = parseFloat(document.getElementById('pagibigMaximum').value);
    const description = document.getElementById('pagibigDescription').value;

    if (!year || !salaryFrom || !contributionRate || !maximumContribution) {
      alert('Please fill in all required fields');
      return false;
    }

    const payload = {
      year,
      salary_from: salaryFrom,
      salary_to: salaryTo,
      contribution_rate: contributionRate,
      maximum_contribution: maximumContribution,
      description
    };

    if (id) {
      payload.id = parseInt(id);
    }

    try {
      const response = await fetch(this.apiUrl, {
        method: id ? 'PUT' : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      const result = await response.json();
      if (!result.success) {
        throw new Error(result.message || 'Save failed');
      }

      this.hideForm();
      await this.loadTableData(year);
      alert(id ? 'Record updated' : 'Record created');
      return true;
    } catch (error) {
      alert(`Error saving record: ${error.message}`);
      return false;
    }
  }
}

// Initialize managers
const taxManager = new TaxTableManager();
const sssManager = new SssTableManager();
const philhealthManager = new PhilhealthTableManager();
const pagibigManager = new PagibigTableManager();

// Modify toggleTableData to use manager
function toggleTableData(tableId, yearSelectId) {
  const yearSelect = document.getElementById(yearSelectId);
  if (!yearSelect) return;

  const selectedYear = parseInt(yearSelect.value);
  
  if (tableId === 'taxTable') {
    taxManager.loadTableData(selectedYear);
  } else if (tableId === 'sssTable') {
    sssManager.loadTableData(selectedYear);
  } else if (tableId === 'philhealthTable') {
    philhealthManager.loadTableData(selectedYear);
  } else if (tableId === 'pagibigTable') {
    pagibigManager.loadTableData(selectedYear);
  }
}
