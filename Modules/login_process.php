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
        // Get user and password hash from database
        $query = "SELECT employees.id, employees.username, employees.type, employees.status, users.Work_status, users.Password FROM employees
              JOIN users ON employees.id = users.User_id
              WHERE users.Username='".mysqli_real_escape_string($dbc, $_POST['username'])."' 
              LIMIT 1";

        $result = mysqli_query($dbc, $query);

        if(mysqli_num_rows($result) > 0){
            $row = mysqli_fetch_array($result);
            
                        // Verify password using password_verify (supports both hashed and plain text for compatibility)
                        $passwordMatch = false;
                        $storedPassword = $row['Password'];
                        $inputPassword = $_POST['password'];
            
                        // Try bcrypt hash verification first
                        if (password_verify($inputPassword, $storedPassword)) {
                            $passwordMatch = true;
                        } 
                        // Fallback for old plain text passwords (backward compatibility)
                        else if ($storedPassword === $inputPassword) {
                            $passwordMatch = true;
                            // Rehash the password for security
                            $hashedPassword = password_hash($inputPassword, PASSWORD_DEFAULT);
                            mysqli_query($dbc, "UPDATE users SET Password = '".mysqli_real_escape_string($dbc, $hashedPassword)."' WHERE User_id = '".$row['id']."'");
                            mysqli_query($dbc, "UPDATE employees SET password = '".mysqli_real_escape_string($dbc, $hashedPassword)."' WHERE id = '".$row['id']."'");
                        }
            
                        if (!$passwordMatch) {
                            $msg .= 'Username and Password do not match.';
                        } else if ($row['status'] == 'Inactive') {
                            echo $msg .= 'Inactive';
                            exit();
                        } else {
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
                        }
        } else {
            $msg .= 'Username and Password do not match.';
        };
    }

    echo $msg;
?>