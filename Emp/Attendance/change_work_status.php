<?php
    session_start();
    $_SESSION['Work_status'] = 'Tapped-out';
    $msg = "Work status updated to Tapped-out";
    echo $msg;
?>