<?php
    session_start();
    if (!isset($_SESSION['login']) || $_SESSION['type'] != "Emp") {
        header("location: ../");
        exit();
    };

    echo '<pre>';
    print_r($_SESSION);
    echo '</pre>';
    header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: post-check=0, pre-check=0', FALSE);
    header('Pragma: no-cache');
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>

        <!-- Bootstrap -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Google Font -->
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

        <!-- Your CSS -->
        <link rel="stylesheet" href="../Assets/home_employee.css">

        <!-- Company Logo -->
        <link rel="icon" type="image/png" href="../Images/logo.jpg"/>

        <!-- Scripts -->
        <script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin ="anonymous"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="vendor/jquery/jquery.form.js"></script>
    </head>

    <body>
        <?php include '../Modules/background.php'; ?>
        <?php include '../Modules/navbar.php'; ?>
        <?php include '../Modules/welcome_card.php'; ?>

        <!-- functions card -->
        <div class="functions-card">
            <div class="card-icons">

                <a href="#" class="icon-item">
                    <img src="../Images/attendance.png" alt="Attendance">
                    <p>Attendance</p>
                </a>

                <a href="#" class="icon-item">
                    <img src="../Images/payslip.png" alt="Payslip">
                    <p>Payslip</p>
                </a>

            </div>
        </div>


        <!-- JAVA RICE -->
        <script src="../Modules/navbar_and_welcome_card_script.php"></script>

    </body>
</html>