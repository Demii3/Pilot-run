<?php
    session_start();

    if(isset($_GET['logout'])){
        $_SESSION = [];
        session_destroy();
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

    <link rel="icon" type="image/png" href="Images/logo.jpg"/>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery/jquery.form.js"></script>
</head>

<body>

<!-- Background -->
<div class="bg-container">
    <img src="Images/bgimg.jpg" class="bg-image">
    <div class="overlay"></div>
</div>

<?php
    $page = "";
    if(isset($_GET['page'])){
        $page = $_GET['page'];
    };

    if (!isset($_SESSION['login'])) {
        include("Modules/login_card.php");
    } else {
        if ($_SESSION['type'] == 'HR') {
            header("location: Modules/home_hr.php");
        } else {
            header("location: Modules/home_employee.php");
        }
        /* switch ($page) {
            case 'home':
                include("Modules/Home.php");
                break;
            case 'about':
                include("Modules/About.php");
                break;
            case 'contact':
                include("Modules/Contact.php");
                break;
            default:
                include("Modules/Home.php");
        }; */
    };
?>  
</body>
</html>