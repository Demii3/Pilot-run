<?php
    $login_start = true;
    $msg = "";


    if(trim($_POST['username'])==''){
        $login_start = false; 
        $msg .="Enter Username\n";
    }
    if(trim($_POST['password'])==''){
        $login_start = false; 
        $msg .="Enter Password\n";
    }

    if($login_start){
        include("dbcon.php");
        $query = "SELECT users.*, employee.Status FROM users JOIN employee ON users.User_id = employee.Emp_id WHERE Username='".mysqli_real_escape_string($dbc, $_POST['username'])."' and password='".mysqli_real_escape_string($dbc, $_POST['password'])."'";
        $result = mysqli_query($dbc, $query);
        if(mysqli_num_rows($result)>0){ // if with records found
            $row = mysqli_fetch_array($result);
            session_start();
            $_SESSION['login'] = '1';
            $_SESSION['type'] = $row['Type'];
            $_SESSION['username'] = $row['Username'];
            $_SESSION['emp_id'] = $row['User_id'];
            $_SESSION['Clock-status'] = $row['Work_status'];
            $_SESSION['Work-status'] = $row['Status'];
            $msg = 'success';
        } else {
            $msg .= 'Username and Password do not match.';
        };

        if (isset($_SESSION['Work-status'])) {
            if ($_SESSION['Work-status'] == 'Inactive') {
                return;
            } else {
                if ($_SESSION['Clock-status'] == 'Tapped-out') {
                    $_SESSION['AttendanceID'] = '';
                    $_SESSION['Date'] = '';
                    $_SESSION['Location'] = '';
                    $_SESSION['Clock-in'] = '';
                    $_SESSION['Clock-inStatus'] = '';
                } else {
                    $query2 = "SELECT * FROM employee_attendance WHERE employee_attendance.Attendance_ID = (SELECT MAX(employee_attendance.Attendance_ID) AS `Most_recent` FROM `employee_attendance` WHERE employee_attendance.Emp_id = '" . $_SESSION['emp_id'] . "');";
                    $result2 = mysqli_query($dbc, $query2);
                    if (mysqli_num_rows($result2) > 0) {
                        $row2 = mysqli_fetch_array($result2);
                        $_SESSION['AttendanceID'] = $row2['Attendance_ID'];
                        $_SESSION['Date'] = $row2['Date'];
                        $_SESSION['Location'] = $row2['Location'];
                        $_SESSION['Clock-in'] = $row2['Clock_in'];
                        $_SESSION['Clock-inStatus'] = $row2['Clock_inStatus'];
                    }
                }
            }
        } else {
            $_SESSION['Work-status'] = '';
        }
        echo $msg;
    }
?>