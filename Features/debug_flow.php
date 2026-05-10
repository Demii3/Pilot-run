<?php
// Comprehensive debugging script
@include __DIR__ . '/../Modules/dbcon.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>OTP Flow Debug</title>
    <style>
        body { font-family: monospace; background: #1e1e1e; color: #0f0; padding: 20px; }
        .section { background: #2d2d2d; padding: 15px; margin: 10px 0; border: 1px solid #444; }
        .title { background: #0f0; color: #000; padding: 10px; font-weight: bold; margin: -15px -15px 15px -15px; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 8px; border: 1px solid #444; text-align: left; }
        th { background: #0f0; color: #000; }
        tr:nth-child(even) { background: #252525; }
        .error { color: #f00; }
        .success { color: #0f0; }
        .info { color: #ff0; }
    </style>
</head>
<body>

<div class="section">
    <div class="title">DATABASE CONNECTION</div>
    <?php if ($dbc) { ?>
        <span class="success">✓ Connected</span><br>
        Server: <?php echo mysqli_get_server_info($dbc); ?><br>
        DB: <?php echo mysqli_get_client_info(); ?>
    <?php } else { ?>
        <span class="error">✗ Connection Failed</span>
    <?php } ?>
</div>

<div class="section">
    <div class="title">OTP TOKENS TABLE</div>
    <?php
    $result = mysqli_query($dbc, "SHOW TABLES LIKE 'otp_tokens'");
    if (mysqli_num_rows($result) > 0) {
        echo "<span class='success'>✓ Table exists</span><br><br>";
        
        $records = mysqli_query($dbc, "SELECT * FROM otp_tokens ORDER BY created_at DESC");
        if (mysqli_num_rows($records) > 0) {
            echo "Records: " . mysqli_num_rows($records) . "<br><br>";
            ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>User ID</th>
                    <th>User Email</th>
                    <th>OTP Code</th>
                    <th>Verified</th>
                    <th>Expires At</th>
                    <th>Created At</th>
                    <th>Time Till Expiry</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($records)) { ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['user_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['user_email']); ?></td>
                        <td><strong><?php echo $row['otp_code']; ?></strong></td>
                        <td><?php echo $row['is_verified'] ? 'YES (1)' : 'NO (0)'; ?></td>
                        <td><?php echo $row['expires_at']; ?></td>
                        <td><?php echo $row['created_at']; ?></td>
                        <td>
                            <?php 
                            $now = new DateTime('now');
                            $expires = new DateTime($row['expires_at']);
                            $diff = $expires->diff($now);
                            if ($expires > $now) {
                                echo "<span class='success'>" . $diff->format('%i min %s sec remaining') . "</span>";
                            } else {
                                echo "<span class='error'>EXPIRED</span>";
                            }
                            ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
            <?php
        } else {
            echo "<span class='error'>✗ No OTP records found</span>";
        }
    } else {
        echo "<span class='error'>✗ Table does not exist</span>";
    }
    ?>
</div>

<div class="section">
    <div class="title">PASSWORD RESET SESSIONS TABLE</div>
    <?php
    $result = mysqli_query($dbc, "SHOW TABLES LIKE 'password_reset_sessions'");
    if (mysqli_num_rows($result) > 0) {
        echo "<span class='success'>✓ Table exists</span><br><br>";
        
        $records = mysqli_query($dbc, "SELECT * FROM password_reset_sessions ORDER BY created_at DESC LIMIT 5");
        if (mysqli_num_rows($records) > 0) {
            echo "Recent Records: " . mysqli_num_rows($records) . "<br><br>";
            ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Session ID (short)</th>
                    <th>User ID</th>
                    <th>Email</th>
                    <th>Completed</th>
                    <th>Expires At</th>
                    <th>Created At</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($records)) { ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo substr($row['session_id'], 0, 16) . '...'; ?></td>
                        <td><?php echo $row['user_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['user_email']); ?></td>
                        <td><?php echo $row['is_completed'] ? 'YES' : 'NO'; ?></td>
                        <td><?php echo $row['expires_at']; ?></td>
                        <td><?php echo $row['created_at']; ?></td>
                    </tr>
                <?php } ?>
            </table>
            <?php
        } else {
            echo "<span class='error'>✗ No session records found</span>";
        }
    } else {
        echo "<span class='error'>✗ Table does not exist</span>";
    }
    ?>
</div>

<div class="section">
    <div class="title">EMPLOYEES TABLE</div>
    <?php
    $result = mysqli_query($dbc, "SELECT COUNT(*) as cnt FROM employees");
    $row = mysqli_fetch_assoc($result);
    echo "Total employees: " . $row['cnt'] . "<br><br>";
    
    // Check if any have email set
    $result = mysqli_query($dbc, "SELECT id, name, email FROM employees WHERE email IS NOT NULL AND email != '' LIMIT 5");
    if (mysqli_num_rows($result) > 0) {
        ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                </tr>
            <?php } ?>
        </table>
        <?php
    } else {
        echo "<span class='error'>✗ No employees with emails found</span>";
    }
    ?>
</div>

<div class="section">
    <div class="title">USERS TABLE</div>
    <?php
    $result = mysqli_query($dbc, "SELECT COUNT(*) as cnt FROM users");
    $row = mysqli_fetch_assoc($result);
    echo "Total users: " . $row['cnt'] . "<br><br>";
    
    $result = mysqli_query($dbc, "SELECT User_id, Username FROM users LIMIT 5");
    if (mysqli_num_rows($result) > 0) {
        ?>
        <table>
            <tr>
                <th>User ID</th>
                <th>Username</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?php echo $row['User_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['Username']); ?></td>
                </tr>
            <?php } ?>
        </table>
        <?php
    }
    ?>
</div>

<div class="section">
    <div class="title">SERVER TIME</div>
    <?php echo "Current DateTime: " . date('Y-m-d H:i:s'); ?>
</div>

</body>
</html>
