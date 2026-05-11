<?php
    session_start();
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    // If not logged in, attempt remember-me cookie
    if(!isset($_SESSION['login']) && !empty($_COOKIE['rememberme'])){
        include('dbcon.php');
        list($selector, $validator) = explode(':', $_COOKIE['rememberme']);
        $selector = mysqli_real_escape_string($dbc, $selector);
        $query = "SELECT * FROM remember_tokens WHERE selector='".$selector."' AND expires > NOW() LIMIT 1";
        $res = mysqli_query($dbc, $query);
        if($res && mysqli_num_rows($res) > 0){
            $row = mysqli_fetch_assoc($res);
            $token_hash = $row['token_hash'];
            if(hash_equals($token_hash, hash('sha256', $validator))){
                // Valid token: log the user in
                $user_q = "SELECT employees.id, employees.username, employees.type, employees.status, users.Work_status FROM employees JOIN users ON employees.id = users.User_id WHERE employees.id='".mysqli_real_escape_string($dbc, $row['user_id'])."' LIMIT 1";
                $user_r = mysqli_query($dbc, $user_q);
                if($user_r && mysqli_num_rows($user_r) > 0){
                    $u = mysqli_fetch_assoc($user_r);
                    $_SESSION['login'] = true;
                    $_SESSION['id'] = $u['id'];
                    $_SESSION['username'] = $u['username'];
                    $_SESSION['type'] = $u['type'];
                    $_SESSION['status'] = $u['status'];
                    $_SESSION['work_status'] = $u['Work_status'];

                    // Rotate validator: create new validator and update DB + cookie
                    $newValidator = bin2hex(random_bytes(32));
                    $newHash = hash('sha256', $newValidator);
                    $newExpires = date('Y-m-d H:i:s', time() + (30*24*60*60));
                    $upd = "UPDATE remember_tokens SET token_hash='".mysqli_real_escape_string($dbc, $newHash)."', expires='".mysqli_real_escape_string($dbc, $newExpires)."' WHERE id='".mysqli_real_escape_string($dbc, $row['id'])."'";
                    mysqli_query($dbc, $upd);
                    $cookie_value = $selector . ':' . $newValidator;
                    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
                    setcookie('rememberme', $cookie_value, time() + (30*24*60*60), '/', '', $secure, true);
                }
            } else {
                // Invalid token: remove it
                mysqli_query($dbc, "DELETE FROM remember_tokens WHERE id='".mysqli_real_escape_string($dbc, $row['id'])."'");
                setcookie('rememberme', '', time() - 3600, '/');
            }
        } else {
            // No token found or expired
            setcookie('rememberme', '', time() - 3600, '/');
        }
    }

    echo isset($_SESSION['login']) ? '1' : '0';
?>