<?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['create'])) {
            createEmployee(
                $_POST['employee_id'],
                $_POST['total_hours'],
                $_POST['rate_per_hour'],
                $_POST['special_holiday'],
                $_POST['legal_holiday'],
                $_POST['overtime_rate'],
                $_POST['late'],
                $_POST['absent'],
                $_POST['cash_advance'],
                $_POST['sss'],
                $_POST['philhealth'],
                $_POST['pagibig'],
                $_POST['tax']);
            header("Location: ./");
            exit;
        }

        if (isset($_POST['update'])) {
            updateEmployee(
                $_POST['employee_id'],
                $_POST['total_hours'],
                $_POST['rate_per_hour'],
                $_POST['special_holiday'],
                $_POST['legal_holiday'],
                $_POST['overtime_rate'],
                $_POST['late'],
                $_POST['absent'],
                $_POST['cash_advance'],
                $_POST['sss'],
                $_POST['philhealth'],
                $_POST['pagibig'],
                $_POST['tax']
            );
            header("Location: ./");
            exit;
        }

        if (isset($_POST['delete'])) {
            deleteEmployee($_POST['employee_id']);
            header("Location: ./");
            exit;
        }
    }
?>