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
        <link rel="stylesheet" href="../Assets/home_hr.css">

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
        <?php include './welcome_card.php'; ?>


        <!-- JAVA RICE -->
        <script>
        function toggleMenu() {
            document.getElementById("profileMenu").classList.toggle("active");
        }

        document.addEventListener("click", function(e) {
            const menu = document.getElementById("profileMenu");
            const avatar = document.querySelector(".avatar");

            if (!avatar.contains(e.target) && !menu.contains(e.target)) {
            menu.classList.remove("active");
            }
        });

        function updateDateTime() {
        const now = new Date();

        const month = now.toLocaleString('default', { month: 'long' });
        const day = now.getDate();
        const year = now.getFullYear();

        let hours = now.getHours();
        let minutes = now.getMinutes();
        let ampm = hours >= 12 ? 'PM' : 'AM';

        hours = hours % 12;
        hours = hours ? hours : 12; // 0 becomes 12
        minutes = minutes < 10 ? '0' + minutes : minutes;

        const time = hours + ":" + minutes + " " + ampm;

        document.getElementById("month").textContent = month;
        document.getElementById("day").textContent = day;
        document.getElementById("year").textContent = year;
        document.getElementById("time").textContent = time;
        }
        updateDateTime();
        setInterval(updateDateTime, 1000);
        </script>


    </body>
</html>