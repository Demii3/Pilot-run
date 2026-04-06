<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Total Hours</th>
                <th>Rate per Hour</th>
                <th>Special Holiday</th>
                <th>Legal Holiday</th>
                <th>Overtime Rate</th>
                <th>Late</th>
                <th>Absent</th>
                <th>Cash Advance</th>
                <th>SSS</th>
                <th>PhilHealth</th>
                <th>Pag-IBIG</th>
                <th>Tax</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $result = $conn->query("SELECT * FROM employees");
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['employee_id'] . "</td>";
                        echo "<td>" . $row['total_hours'] . "</td>";
                        echo "<td>" . $row['rate_per_hour'] . "</td>";
                        echo "<td>" . $row['special_holiday'] . "</td>";
                        echo "<td>" . $row['legal_holiday'] . "</td>";
                        echo "<td>" . $row['overtime_rate'] . "</td>";
                        echo "<td>" . $row['late'] . "</td>"; // Display late deduction
                        echo "<td>" . $row['absent'] . "</td>"; // Display absent deduction
                        echo "<td>" . $row['cash_advance'] . "</td>";
                        echo "<td>" . $row['sss'] . "</td>";
                        echo "<td>" . $row['philhealth'] . "</td>";
                        echo "<td>" . $row['pagibig'] . "</td>";
                        echo "<td>" . $row['tax'] . "</td>";
                        echo "<td>";
                        echo "<button class='btn btn-update' onclick='editEmployee(" . json_encode($row) . ")'>Edit</button>";
                        echo "<form action='employee_management.php' method='POST' style='display:inline;'>";
                        echo "<input type='hidden' name='employee_id' value='" . $row['employee_id'] . "'>";
                        echo "<button type='submit' name='delete' class='btn btn-delete'>Delete</button>";
                        echo "</form>";
                        echo "</td>";
                        echo "</tr>";
                }
                } else {
                    echo "<tr><td colspan='14'>No employee records found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
