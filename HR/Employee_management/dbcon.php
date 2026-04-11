<?php

    //connect to db
    $username = "root";                     /* if0_41495707 */
    $password = "";                         /* F3g46pnhsK8UCm */
    $hostname = "localhost";                /* sql201.infinityfree.com */
    $dbasename = "simpletest_db";           /* if0_41495707_st */

    $dbc = mysqli_connect($hostname, $username, $password);
    if (!$dbc) {
        throw new Exception('Database connection failed: ' . mysqli_connect_error());
    }
    if (!mysqli_select_db($dbc, $dbasename)) {
        throw new Exception('Database selection failed: ' . mysqli_error($dbc));
    }

?>