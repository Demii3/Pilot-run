<?php
    session_start();
    if(isset($_GET['logout'])){
        session_destroy();
        $_SESSION = [];
        header("location: ./");
    }
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
        <link rel="stylesheet" href="Assets/login.css">

        <!-- Company Logo -->
        <link rel="icon" type="image/png" href="Images/logo.jpg"/>

        <!-- Scripts -->
        <script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="vendor/jquery/jquery.form.js"></script>
    </head>

    <body>

        <?php include 'Modules/background.php'; ?>

        <?php
            if (!isset($_SESSION['login'])) {
                include("Modules/login_card.php");
            } else {
                if ($_SESSION['Work-status'] == 'Inactive') {
                    echo "<script>alert('Your account is inactive. Please contact HR for assistance.');</script>";
                    header("location: ./?logout=true");
                } else {
                    echo "<script>alert('Wrong password');</script>";
                    header("location: ./?logout=true");
                }
                ; 

                if ($_SESSION['type'] == 'HR') {
                    header("location: ./HR");
                } else {
                    header("location: ./Emp");
                }
            };
        ?> 

    </body>
</html>