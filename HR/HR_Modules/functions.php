<?php
    function updateEmployee($employee_id,
                            $total_hours,
                            $rate_per_hour,
                            $special_holiday, 
                            $legal_holiday, 
                            $overtime_rate, 
                            $late_count, 
                            $absent_count, 
                            $cash_advance, 
                            $sss, 
                            $philhealth, 
                            $pagibig, 
                            $tax) {
        global $conn;
        // Calculate deductions
        $late_deduction = $late_count * $rate_per_hour;
        $absent_deduction = $absent_count * $rate_per_hour * 8; // Assuming 8 hours per day

        $stmt = $conn->prepare("UPDATE employees SET total_hours = ?, rate_per_hour = ?, special_holiday = ?, legal_holiday = ?, overtime_rate = ?, late = ?, absent = ?, cash_advance = ?, sss = ?, philhealth = ?, pagibig = ?, tax = ? WHERE employee_id = ?");
        $stmt->bind_param("ddddddddddddi", $total_hours, $rate_per_hour, $special_holiday, $legal_holiday, $overtime_rate, $late_deduction, $absent_deduction, $cash_advance, $sss, $philhealth, $pagibig, $tax, $employee_id);
        if (!$stmt->execute()) {
            error_log("Error: " . $stmt->error); // Log errors instead of echoing
        }
        $stmt->close();
    }

    // Function to delete an employee record
    function deleteEmployee($employee_id) {
        global $conn;
        $stmt = $conn->prepare("DELETE FROM employees WHERE employee_id = ?");
        $stmt->bind_param("i", $employee_id);
        if (!$stmt->execute()) {
            error_log("Error: " . $stmt->error); // Log errors instead of echoing
        }
        $stmt->close();
    }

    // Function to create a new employee record
    function createEmployee($employee_id, $total_hours, $rate_per_hour, $special_holiday, $legal_holiday, $overtime_rate, $late_count, $absent_count, $cash_advance, $sss, $philhealth, $pagibig, $tax) {
        global $conn;

        // Calculate deductions
        $late_deduction = $late_count * $rate_per_hour;
        $absent_deduction = $absent_count * $rate_per_hour * 8; // Assuming 8 hours per day

        $stmt = $conn->prepare("INSERT INTO employees (employee_id, total_hours, rate_per_hour, special_holiday, legal_holiday, overtime_rate, late, absent, cash_advance, sss, philhealth, pagibig, tax) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("idddddddddddd", $employee_id, $total_hours, $rate_per_hour, $special_holiday, $legal_holiday, $overtime_rate, $late_deduction, $absent_deduction, $cash_advance, $sss, $philhealth, $pagibig, $tax);
        if (!$stmt->execute()) {
            error_log("Error: " . $stmt->error); // Log errors instead of echoing
        }
        $stmt->close();
    }
?>