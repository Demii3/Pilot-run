<?php

    //connect to db
    $username = "root";                     /* if0_41495707 */
    $password = "";                         /* F3g46pnhsK8UCm */
    $hostname = "localhost";                /* sql201.infinityfree.com */
    $dbasename = "simpletest_db";           /* if0_41495707_st */

    $dbc = mysqli_connect($hostname, $username, $password);
    mysqli_select_db($dbc, $dbasename);
    mysqli_set_charset($dbc, 'utf8mb4');

?>