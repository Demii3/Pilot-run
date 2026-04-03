<?php

    //connect to db
    $username = "root";
    $password = "";
    $hostname = "localhost";
    $dbasename = "simpletest_db";

    $dbc = mysqli_connect($hostname, $username, $password);
    mysqli_select_db($dbc, $dbasename);

?>