<?php
    session_start();
    if (isset($_SESSION["login"]) && $_SESSION["login"] === true) {
        $empType = $_SESSION['empType'] ?? '';
        $redirectTo = '';

        switch ($empType) {
            case 'HR':
                $redirectTo = './HR';
                break;
            case 'Emp':
                $redirectTo = './Emp';
                break;
            default:
                // If empType is not recognized, log out the user for safety
                session_unset();
                session_destroy();
                echo json_encode(["loggedIn" => false]);
                exit;
        }

        echo json_encode(["loggedIn" => true, "redirectTo" => $redirectTo]);
    } else {
        echo json_encode(["loggedIn" => false]);

    }
?>