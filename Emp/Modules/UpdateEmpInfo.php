<?php
    include 'dbcon.php';
    /** @var mysqli $dbc */

    $error = 'sss';

    $input = file_get_contents('php://input');
    $data = json_decode($input, true) ?? [];
    $userId = (int)($data['USER_ID']) ?? 0;
    $purpose = $data['purpose'] ?? '';
    $oldPassword = $data['currentPassword'] ?? '';
    $newPassword = $data['confirmNewPassword'] ?? '';
    $proceed = false;
    $testingvar = '';
    $testingvar2 = true;

    if ($purpose === 'updateInfoAndPassword') {
        $sql1 = mysqli_prepare($dbc, "SELECT DISTINCT employees.password
                FROM employees
                WHERE id = ?");
        mysqli_stmt_bind_param($sql1, 'i', $userId);
        mysqli_stmt_execute($sql1);
        $result = mysqli_stmt_get_result($sql1);

        if (mysqli_num_rows($result) > 0) {
            $querydata = mysqli_fetch_assoc($result);
            $proceed = password_verify($oldPassword, $querydata['password']);
            $testingvar2 = $proceed ? true : false;
        } else {
            $error = mysqli_error($dbc);
            $testingvar = 'Password is incorrect';
            echo json_encode(['error' => $error, 'testingvar' => $testingvar]);
            exit();
        };

        if ($proceed) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $sql2 = mysqli_prepare($dbc, "UPDATE employees SET password = ? WHERE id = ?");
            mysqli_stmt_bind_param($sql2, 'si', $hashedPassword, $userId);
            mysqli_stmt_execute($sql2);

            $sql3 = mysqli_prepare($dbc, "UPDATE users SET Password = ? WHERE User_id = ?");
            mysqli_stmt_bind_param($sql3, 'si', $hashedPassword, $userId);
            mysqli_stmt_execute($sql3);

            if (mysqli_stmt_affected_rows($sql2) > 0) {
                $testingvar = 'Password updated successfully';
            } else {
                $testingvar = 'Failed to update password';
                $error = mysqli_error($dbc);
            };
            mysqli_stmt_close($sql2);
            mysqli_stmt_close($sql3);
        } else {
            $testingvar = 'Current password is incorrect';
        };

        mysqli_stmt_close($sql1);
    }

    if (!$testingvar2) {
        $testingvar = 'Current password is incorrect';
        echo json_encode(['error' => $error, 'testingvar' => $testingvar]);
        exit();
    }

    $sql = mysqli_prepare($dbc, "UPDATE employees
            SET `email` = ?, `username` = ?
            WHERE id = ?");
    mysqli_stmt_bind_param($sql, 'ssi', $data['email'], $data['username'], $userId);
    mysqli_stmt_execute($sql);
    if (mysqli_stmt_affected_rows($sql) > 0) {
        $testingvar = $proceed ? 'Employee information and password updated successfully' : 'Employee information updated successfully';
    } else {
        $error = mysqli_error($dbc);
        $testingvar = 'Updated succesfully.';
        echo json_encode(['error' => $error, 'testingvar' => $testingvar]);
        exit();
    };
    mysqli_stmt_close($sql);

    echo json_encode(['error' => $error, 'testingvar' => $testingvar, 'proceed' => $proceed]);
?>