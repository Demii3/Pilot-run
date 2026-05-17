<?php

    include("./dbcon.php");
    /** @var mysqli $dbc */

    $data = file_get_contents("php://input");
    $inputs = json_decode($data, true) ?? [];

    $messagge = "No data received.";
    $success = false;
    $empType = "";
    $result = [];

    $rawUsername = $inputs['username'] ?? 'none';
    $rawPassword = $inputs['password'] ?? 'none';
    $username = mysqli_real_escape_string($dbc, $rawUsername);
    $password = mysqli_real_escape_string($dbc, $rawPassword);

    $stmt1 = mysqli_prepare($dbc, 'SELECT * FROM users WHERE username = ?');
    if ($stmt1) {
        mysqli_stmt_bind_param($stmt1, 's', $username);
        mysqli_stmt_execute($stmt1);
        $result = mysqli_stmt_get_result($stmt1);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $passwordHash = $row['Password'];
            if (password_verify($password, $passwordHash)) {
                $success = true;
                $empType = $row['Type'];
                $messagge = "Login successful.";
                session_start();
                $_SESSION['login'] = true;
                $_SESSION['username'] = $username;
                $_SESSION['empType'] = $empType;
                $_SESSION['userId'] = $row['User_id'];
                $_SESSION['workStatus'] = $row['Work_status'];
            } else {    
                $messagge = "Incorrect password.";
            }
        } else {
            $messagge = "Username does not exist in the database.";
        }

        mysqli_stmt_close($stmt1);
    } else {
        $messagge = "Database error: failed to prepare statement.";
    }

    if ($success) {
        $stmt2 = mysqli_prepare($dbc, 'UPDATE users SET Log_status = 1 WHERE username = ?');
        if ($stmt2) {
            mysqli_stmt_bind_param($stmt2, 's', $username);
            mysqli_stmt_execute($stmt2);
            mysqli_stmt_close($stmt2);
        } else {
            $messagge = "Database error: failed to prepare log status update.";
        }
    }

    echo json_encode(["message" => $messagge, "success" => $success, "empType" => $empType]);
?>