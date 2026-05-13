<?php
$username = "root";
$password = "";
$hostname = "localhost";
$dbasename = "simpletest_db";

$dbc = mysqli_connect($hostname, $username, $password);
mysqli_select_db($dbc, $dbasename);

$query = "SELECT * FROM employee_13th_month WHERE process_year = 2026";
$result = mysqli_query($dbc, $query);

if ($result) {
    echo "Row count: " . mysqli_num_rows($result) . "\n";
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    if (count($rows) > 0) {
        echo "Sample rows:\n";
        print_r(array_slice($rows, 0, 3));
    } else {
        echo "No rows found for 2026.\n";
    }
} else {
    echo "Error: " . mysqli_error($dbc) . "\n";
}
?>
