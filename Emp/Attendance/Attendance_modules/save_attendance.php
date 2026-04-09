<?php
    $msg = '';
    include './dbcon.php';
    $sql = "INSERT INTO employee_attendance (Emp_id, `Date`, `Location`, `Clock_in`, `Status`) VALUES (" . $_POST['Emp_id'] . ", '" . date('Y-m-d', strtotime($_POST['Date'])) . "', '" . $_POST['Location'] . "', '" . $_POST['Clock_in'] . "', '" . $_POST['Status'] . "')";
    $result = mysqli_query($dbc, $sql);
    if($result) {
        $msg .= $_POST['Date'] . " attendance recorded successfully.";
    } else {
        $msg .= "Error recording attendance: " . mysqli_error($dbc);
    }
    echo $msg;
?>