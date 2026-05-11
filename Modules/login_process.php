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
                            // If remember-me requested, create a persistent login token
                            if(!empty($_POST['remember'])){
                                // Create selector and validator
                                $selector = bin2hex(random_bytes(8));
                                $validator = bin2hex(random_bytes(32));
                                $token_hash = hash('sha256', $validator);
                                $expires = date('Y-m-d H:i:s', time() + (30*24*60*60)); // 30 days

                                // Ensure remember_tokens table exists (fallback if migration wasn't run)
                                $create_table_sql = "CREATE TABLE IF NOT EXISTS remember_tokens (
                                    id INT AUTO_INCREMENT PRIMARY KEY,
                                    user_id INT NOT NULL,
                                    selector VARCHAR(64) NOT NULL,
                                    token_hash VARCHAR(128) NOT NULL,
                                    expires DATETIME NOT NULL,
                                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                                    INDEX(selector),
                                    INDEX(user_id)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
                                @mysqli_query($dbc, $create_table_sql);

                                // Remove expired tokens and insert new token
                                mysqli_query($dbc, "DELETE FROM remember_tokens WHERE expires < NOW() OR user_id = '".mysqli_real_escape_string($dbc, $row['id'])."'");
                                $ins = "INSERT INTO remember_tokens (user_id, selector, token_hash, expires) VALUES ('".
                                    mysqli_real_escape_string($dbc, $row['id'])."', '".
                                    mysqli_real_escape_string($dbc, $selector)."', '".
                                    mysqli_real_escape_string($dbc, $token_hash)."', '".
                                    mysqli_real_escape_string($dbc, $expires)."')";
                                mysqli_query($dbc, $ins);

                                // Set cookie
                                $cookie_value = $selector . ':' . $validator;
                                $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
                                setcookie('rememberme', $cookie_value, time() + (30*24*60*60), '/', '', $secure, true);
                            }

                            $msg = 'success';
                        }
        } else {
            $msg .= 'Username and Password do not match.';
        };
    }

    echo $msg;
?>