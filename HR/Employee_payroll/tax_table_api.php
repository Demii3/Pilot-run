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

function ensureTaxTableExists($dbc) {
    $createSql = "CREATE TABLE IF NOT EXISTS `tax_table` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `year` INT NOT NULL,
      `income_from` DECIMAL(15,2) NOT NULL,
      `income_to` DECIMAL(15,2),
      `tax_rate` DECIMAL(5,2) NOT NULL,
      `base_tax` DECIMAL(15,2) DEFAULT 0,
      `description` VARCHAR(255),
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `idx_year` (`year`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!mysqli_query($dbc, $createSql)) {
        throw new Exception('Failed to create tax_table: ' . mysqli_error($dbc));
    }
}

try {
    ensureTaxTableExists($dbc);
    
    $method = $_SERVER['REQUEST_METHOD'];
    $year = isset($_GET['year']) ? intval($_GET['year']) : 2026;
    
    if ($method === 'GET') {
        // Fetch tax table data by year
        $sql = "SELECT * FROM `tax_table` WHERE `year` = ? ORDER BY `income_from` ASC";
        $stmt = mysqli_prepare($dbc, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $year);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        mysqli_stmt_close($stmt);
        
        respond(true, $data, 'Tax table data retrieved');
    } 
    else if ($method === 'POST') {
        // Create new tax table entry
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['year']) || !isset($input['income_from']) || !isset($input['tax_rate'])) {
            respond(false, null, 'Missing required fields', 400);
        }
        
        $year = intval($input['year']);
        $incomeFrom = floatval($input['income_from']);
        $incomeTo = isset($input['income_to']) ? floatval($input['income_to']) : null;
        $taxRate = floatval($input['tax_rate']);
        $baseTax = isset($input['base_tax']) ? floatval($input['base_tax']) : 0;
        $description = isset($input['description']) ? $input['description'] : null;
        
        $sql = "INSERT INTO `tax_table` (`year`, `income_from`, `income_to`, `tax_rate`, `base_tax`, `description`) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($dbc, $sql);
        mysqli_stmt_bind_param($stmt, 'idddsss', $year, $incomeFrom, $incomeTo, $taxRate, $baseTax, $description);
        
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            respond(false, null, 'Failed to insert tax record: ' . mysqli_error($dbc), 500);
        }
        
        $newId = mysqli_insert_id($dbc);
        mysqli_stmt_close($stmt);
        respond(true, ['id' => $newId], 'Tax record created successfully');
    } 
    else if ($method === 'PUT') {
        // Update tax table entry
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['id'])) {
            respond(false, null, 'ID is required for update', 400);
        }
        
        $id = intval($input['id']);
        $incomeFrom = isset($input['income_from']) ? floatval($input['income_from']) : null;
        $incomeTo = isset($input['income_to']) ? floatval($input['income_to']) : null;
        $taxRate = isset($input['tax_rate']) ? floatval($input['tax_rate']) : null;
        $baseTax = isset($input['base_tax']) ? floatval($input['base_tax']) : null;
        $description = isset($input['description']) ? $input['description'] : null;
        
        $updates = [];
        $params = [];
        $types = '';
        
        if ($incomeFrom !== null) {
            $updates[] = '`income_from` = ?';
            $params[] = $incomeFrom;
            $types .= 'd';
        }
        if ($incomeTo !== null) {
            $updates[] = '`income_to` = ?';
            $params[] = $incomeTo;
            $types .= 'd';
        }
        if ($taxRate !== null) {
            $updates[] = '`tax_rate` = ?';
            $params[] = $taxRate;
            $types .= 'd';
        }
        if ($baseTax !== null) {
            $updates[] = '`base_tax` = ?';
            $params[] = $baseTax;
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
        
        $sql = "UPDATE `tax_table` SET " . implode(', ', $updates) . " WHERE `id` = ?";
        $stmt = mysqli_prepare($dbc, $sql);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            respond(false, null, 'Failed to update tax record: ' . mysqli_error($dbc), 500);
        }
        
        mysqli_stmt_close($stmt);
        respond(true, null, 'Tax record updated successfully');
    } 
    else if ($method === 'DELETE') {
        // Delete tax table entry
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        
        if (!$id) {
            respond(false, null, 'ID is required for deletion', 400);
        }
        
        $sql = "DELETE FROM `tax_table` WHERE `id` = ?";
        $stmt = mysqli_prepare($dbc, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            respond(false, null, 'Failed to delete tax record: ' . mysqli_error($dbc), 500);
        }
        
        mysqli_stmt_close($stmt);
        respond(true, null, 'Tax record deleted successfully');
    } 
    else {
        respond(false, null, 'Invalid request method', 405);
    }
} catch (Exception $e) {
    respond(false, null, 'Error: ' . $e->getMessage(), 500);
}
?>
