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
        $query = "SELECT * FROM users WHERE Username='".mysqli_real_escape_string($dbc, $_POST['username'])."' and password='".mysqli_real_escape_string($dbc, $_POST['password'])."'";
        $result = mysqli_query($dbc, $query);
        if(mysqli_num_rows($result)>0){ // if with records found
            $row = mysqli_fetch_array($result);
            session_start();
            $_SESSION['login'] = '1';
            $_SESSION['type'] = $row['Type'];
            $_SESSION['bg_source'] = $row['Type'];
            $_SESSION['username'] = $row['Username'];
            $_SESSION['emp_id'] = $row['User_id'];
            $_SESSION['Work_status'] = $row['Work_status'];
            $_SESSION['Clock_in'] = null;
            $msg = 'success';
        } else {
            $msg .= 'Username and Password do not match.';
        }
    }
    echo $msg;
?>