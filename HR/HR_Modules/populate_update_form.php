<script>
    function editEmployee(employee) {
        document.getElementById('update_employee_id').value = employee.employee_id;
        document.getElementById('update_total_hours').value = employee.total_hours;
        document.getElementById('update_rate_per_hour').value = employee.rate_per_hour;
        document.getElementById('update_special_holiday').value = employee.special_holiday;
        document.getElementById('update_legal_holiday').value = employee.legal_holiday;
        document.getElementById('update_overtime_rate').value = employee.overtime_rate;
        document.getElementById('update_late').value = '';
        document.getElementById('update_absent').value = '';
        document.getElementById('update_cash_advance').value = employee.cash_advance;
        document.getElementById('update_sss').value = employee.sss;
        document.getElementById('update_philhealth').value = employee.philhealth;
        document.getElementById('update_pagibig').value = employee.pagibig;
        document.getElementById('update_tax').value = employee.tax;

        document.getElementById('updateForm').style.display = 'block';
    }

    // JavaScript to handle canceling the update form
    function cancelUpdate() {
        document.getElementById('updateForm').style.display = 'none';
    }
</script>