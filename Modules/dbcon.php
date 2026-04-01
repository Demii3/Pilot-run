<?php

    //connect to db
    $username = "if0_41495707";
    $password = "F3g46pnhsK8UCm";
    $server   = "sql201.infinityfree.com";
    $dbasename = "if0_41495707_st";

    $dbc = mysqli_connect($server, $username, $password);
    mysqli_select_db($dbc, $dbasename);

?>