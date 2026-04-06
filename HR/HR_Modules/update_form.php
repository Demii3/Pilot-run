<!-- Update Form (hidden by default, shown when "Edit" is clicked) -->
<div id="updateForm" class="form-container">
    <h2>Update Employee</h2>
    <form action="index.php" method="POST">
        <input type="hidden" id="update_employee_id" name="employee_id">
        <input type="number" id="update_total_hours" name="total_hours" placeholder="Total Hours" required>
        <input type="number" step="0.01" id="update_rate_per_hour" name="rate_per_hour" placeholder="Rate per Hour" required>
        <input type="number" step="0.01" id="update_special_holiday" name="special_holiday" placeholder="Special Holiday" required>
        <input type="number" step="0.01" id="update_legal_holiday" name="legal_holiday" placeholder="Legal Holiday" required>
        <input type="number" step="0.01" id="update_overtime_rate" name="overtime_rate" placeholder="Overtime Rate" required>
        <input type="number" step="1" id="update_late" name="late" placeholder="Number of Times Late" required>
        <input type="number" step="1" id="update_absent" name="absent" placeholder="Number of Times Absent" required>
        <input type="number" step="0.01" id="update_cash_advance" name="cash_advance" placeholder="Cash Advance" required>
        <input type="number" step="0.01" id="update_sss" name="sss" placeholder="SSS" required>
        <input type="number" step="0.01" id="update_philhealth" name="philhealth" placeholder="PhilHealth" required>
        <input type="number" step="0.01" id="update_pagibig" name="pagibig" placeholder="Pag-IBIG" required>
        <input type="number" step="0.01" id="update_tax" name="tax" placeholder="Tax" required>
        <button type="submit" name="update" style="background-color: #ffc107; color: white;">Update Employee</button>
        <button type="button" onclick="cancelUpdate()" style="background-color: #dc3545; color: white;">Cancel</button>
    </form>
</div>