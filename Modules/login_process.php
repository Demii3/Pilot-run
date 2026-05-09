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
        /** @var mysqli $dbc */
        $query = "SELECT id, employees.username, employees.type, employees.status, users.Work_status FROM employees
                  JOIN users ON employees.id = users.User_id
                  WHERE users.Username='".mysqli_real_escape_string($dbc, $_POST['username'])."' 
                  AND users.Password='".mysqli_real_escape_string($dbc, $_POST['password'])."'";

        $result = mysqli_query($dbc, $query);

        if(mysqli_num_rows($result)>0){ // if with records found
            $row = mysqli_fetch_array($result);
            if($row['status'] == 'Inactive'){
                echo $msg .= 'Inactive';
                exit();
            }
            session_start();
            $_SESSION['login'] = true;
            $_SESSION['id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['type'] = $row['type'];
            $_SESSION['status'] = $row['status'];
            $_SESSION['work_status'] = $row['Work_status'];
            $_SESSION['locations'] = [];
            $_SESSION['coordinates'] = [];
            $another__query = "SELECT `name`, `coordinates` 
                               FROM geofences 
                               WHERE geofences.id 
                               IN (SELECT loc_id 
                                   FROM employee_location 
                                   WHERE User_id = " . $row['id'] . ")";


            $result2 = mysqli_query($dbc, $another__query);
            while($row2 = mysqli_fetch_array($result2)){
                $_SESSION['locations'][] = $row2['name'];
                $_SESSION['coordinates'][] = $row2['coordinates'];
            }
            $msg = 'success';
        } else {
            $msg .= 'Username and Password do not match.';
        };
    }

    echo $msg;
?>