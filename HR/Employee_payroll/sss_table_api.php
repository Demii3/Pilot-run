<?php
ob_start();
header('Content-Type: application/json');

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

include '../../Modules/dbcon.php';

function respond($success, $data = null, $message = '', $statusCode = 200) {
    http_response_code($statusCode);
    if (ob_get_length()) {
        ob_end_clean();
    }
    echo json_encode(['success' => $success, 'data' => $data, 'message' => $message]);
    exit;
}

function ensureSssTableExists($dbc) {
    $createSql = "CREATE TABLE IF NOT EXISTS `sss_table` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `year` INT NOT NULL,
      `salary_from` DECIMAL(15,2) NOT NULL,
      `salary_to` DECIMAL(15,2),
      `monthly_contribution` DECIMAL(10,2) NOT NULL,
      `description` VARCHAR(255),
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `idx_year` (`year`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!mysqli_query($dbc, $createSql)) {
        throw new Exception('Failed to create sss_table: ' . mysqli_error($dbc));
    }
}

try {
    ensureSssTableExists($dbc);
    
    $method = $_SERVER['REQUEST_METHOD'];
    $year = isset($_GET['year']) ? intval($_GET['year']) : 2026;
    
    if ($method === 'GET') {
        // Fetch SSS table data by year
        $sql = "SELECT * FROM `sss_table` WHERE `year` = ? ORDER BY `salary_from` ASC";
        $stmt = mysqli_prepare($dbc, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $year);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        mysqli_stmt_close($stmt);
        
        respond(true, $data, 'SSS table data retrieved');
    } 
    else if ($method === 'POST') {
        // Create new SSS table entry
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['year']) || !isset($input['salary_from']) || !isset($input['monthly_contribution'])) {
            respond(false, null, 'Missing required fields', 400);
        }
        
        $year = intval($input['year']);
        $salaryFrom = floatval($input['salary_from']);
        $salaryTo = isset($input['salary_to']) ? floatval($input['salary_to']) : null;
        $monthlyContribution = floatval($input['monthly_contribution']);
        $description = isset($input['description']) ? $input['description'] : null;
        
        $sql = "INSERT INTO `sss_table` (`year`, `salary_from`, `salary_to`, `monthly_contribution`, `description`) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($dbc, $sql);
        mysqli_stmt_bind_param($stmt, 'iddds', $year, $salaryFrom, $salaryTo, $monthlyContribution, $description);
        
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            respond(false, null, 'Failed to insert SSS record: ' . mysqli_error($dbc), 500);
        }
        
        $newId = mysqli_insert_id($dbc);
        mysqli_stmt_close($stmt);
        respond(true, ['id' => $newId], 'SSS record created successfully');
    } 
    else if ($method === 'PUT') {
        // Update SSS table entry
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['id'])) {
            respond(false, null, 'ID is required for update', 400);
        }
        
        $id = intval($input['id']);
        $salaryFrom = isset($input['salary_from']) ? floatval($input['salary_from']) : null;
        $salaryTo = isset($input['salary_to']) ? floatval($input['salary_to']) : null;
        $monthlyContribution = isset($input['monthly_contribution']) ? floatval($input['monthly_contribution']) : null;
        $description = isset($input['description']) ? $input['description'] : null;
        
        $updates = [];
        $params = [];
        $types = '';
        
        if ($salaryFrom !== null) {
            $updates[] = '`salary_from` = ?';
            $params[] = $salaryFrom;
            $types .= 'd';
        }
        if ($salaryTo !== null) {
            $updates[] = '`salary_to` = ?';
            $params[] = $salaryTo;
            $types .= 'd';
        }
        if ($monthlyContribution !== null) {
            $updates[] = '`monthly_contribution` = ?';
            $params[] = $monthlyContribution;
            $types .= 'd';
        }
        if ($description !== null) {
            $updates[] = '`description` = ?';
            $params[] = $description;
            $types .= 's';
        }
        
        if (empty($updates)) {
            respond(false, null, 'No fields to update', 400);
        }
        
        $params[] = $id;
        $types .= 'i';
        
        $sql = "UPDATE `sss_table` SET " . implode(', ', $updates) . " WHERE `id` = ?";
        $stmt = mysqli_prepare($dbc, $sql);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            respond(false, null, 'Failed to update SSS record: ' . mysqli_error($dbc), 500);
        }
        
        mysqli_stmt_close($stmt);
        respond(true, null, 'SSS record updated successfully');
    } 
    else if ($method === 'DELETE') {
        // Delete SSS table entry
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        
        if (!$id) {
            respond(false, null, 'ID is required for deletion', 400);
        }
        
        $sql = "DELETE FROM `sss_table` WHERE `id` = ?";
        $stmt = mysqli_prepare($dbc, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            respond(false, null, 'Failed to delete SSS record: ' . mysqli_error($dbc), 500);
        }
        
        mysqli_stmt_close($stmt);
        respond(true, null, 'SSS record deleted successfully');
    } 
    else {
        respond(false, null, 'Invalid request method', 405);
    }
} catch (Exception $e) {
    respond(false, null, 'Error: ' . $e->getMessage(), 500);
}
?>
