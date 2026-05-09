<?php
    session_start();
    if (!isset($_SESSION['login']) || $_SESSION['type'] != "HR") {
        header("location: ../");
        exit();
    };

    include '../Modules/dbcon.php';

    // Recent tap-in
    $query_recent = "SELECT e.name as employee_name, ea.Clock_in FROM employee_attendance ea JOIN employees e ON ea.Emp_id = e.id ORDER BY ea.Clock_in DESC LIMIT 10";
    $result_recent = mysqli_query($dbc, $query_recent);
    $recent_tapins = mysqli_fetch_all($result_recent, MYSQLI_ASSOC);

    // Current employed
    $query_employed = "SELECT name, join_date FROM employees ORDER BY join_date DESC LIMIT 10";
    $result_employed = mysqli_query($dbc, $query_employed);
    $recent_employed = mysqli_fetch_all($result_employed, MYSQLI_ASSOC);

    // Active employees
    $query_active = "SELECT COUNT(*) as total FROM employees WHERE status = 'Active'";
    $result_active = mysqli_query($dbc, $query_active);
    $active_employees = mysqli_fetch_assoc($result_active)['total'];

    // 15th and last-day reminders
    $today = new DateTime('today');
    $currentYear = (int) $today->format('Y');
    $currentMonth = (int) $today->format('m');
    $dayOfMonth = (int) $today->format('j');

    $monthLastDay = (int) $today->format('t');
    $fifteenth = new DateTime("{$currentYear}-{$currentMonth}-15");
    $lastDay = new DateTime("{$currentYear}-{$currentMonth}-{$monthLastDay}");

    $daysTo15 = max(0, (int)$today->diff($fifteenth)->format('%r%a'));
    $daysToLastDay = max(0, (int)$today->diff($lastDay)->format('%r%a'));

    $reminder_15 = $dayOfMonth === 15 ? 'Today is the 15th.' : ($dayOfMonth < 15 ? "{$daysTo15} day(s) until the 15th" : "The 15th has passed for this month");
    $reminder_last = $dayOfMonth === $monthLastDay ? 'Today is the last day of the month.' : "{$daysToLastDay} day(s) until the last day";

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
        <title>HR Dashboard</title>

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

        <?php include './HR_modules/background.php'; ?>
        <?php include '../Modules/navbar.php'; ?>

        <div class="dashboard-layout">
            <aside class="side-dashboard">

                <div class="sidebar-title">DASHBOARD</div>

                <nav class="sidebar-nav">
                    <a href="./Employee_attendance" class="sidebar-link">
                        <span>Attendance</span>
                    </a>
                    <a href="./Employee_management" class="sidebar-link">
                        <span>Manage Employees</span>
                    </a>
                    <a href="./Employee_payroll" class="sidebar-link">
                        <span>Payroll</span>
                    </a>
                </nav>
            </aside>

            <main class="dashboard-main">

                <?php include '../Modules/welcome_card.php'; ?>

                <div class="stats-cards">
                    <div class="stat-card">
                        <h3>Recent Tap-ins</h3>
                        <div class="recent-list">
                            <?php if ($recent_tapins): ?>
                                <?php foreach ($recent_tapins as $tapin): ?>
                                    <p><?php echo $tapin['employee_name'] . ' at ' . date('H:i', strtotime($tapin['Clock_in'])); ?></p>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No recent tap-ins</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="stat-card">
                        <h3>Recently Employed</h3>
                        <div class="recent-list">
                            <?php if ($recent_employed): ?>
                                <?php foreach ($recent_employed as $emp): ?>
                                    <p><?php echo $emp['name'] . ' - ' . date('M d, Y', strtotime($emp['join_date'])); ?></p>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No employees found</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="stat-card">
                        <h3>Active Employees</h3>
                        <p><?php echo $active_employees; ?> employees</p>
                    </div>
                    <div class="stat-card">
                        <h3>Reminder Dates</h3>
                        <div class="recent-list">
                            <p><strong>15th day:</strong> <?php echo $reminder_15; ?></p>
                            <p><strong>Last day:</strong> <?php echo $reminder_last; ?></p>
                        </div>
                    </div>
                </div>
        </div>


        <!-- JAVA RICE -->
        <?php include '../Modules/navbar_and_welcome_card_script.php'; ?>

        <script>
            $(document).ready(function(){
                $.get('../Modules/check_session.php', function(data){
                    if(data == '0'){
                        window.location = '../';
                    }
                });
            });
        </script>

    </body>
</html>