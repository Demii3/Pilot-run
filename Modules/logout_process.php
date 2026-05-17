<?php

    include("dbcon.php");
    /** @var mysqli $dbc */

    session_start();

    $success = true;
    $userId = $_SESSION['userId'] ?? $_SESSION['id'] ?? null;

    if ($userId !== null) {
        $stmt = mysqli_prepare($dbc, 'UPDATE users SET Log_status = 0 WHERE User_id = ?');
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'i', $userId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();

    // Return JSON response
    echo json_encode(['success' => $success]);
?>
