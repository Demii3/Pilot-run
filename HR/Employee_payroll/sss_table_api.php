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
        // Check for DataTables server-side processing
        if (isset($_GET['draw'])) {
            // DataTables server-side processing
            $draw = intval($_GET['draw']);
            $start = isset($_GET['start']) ? intval($_GET['start']) : 0;
            $length = isset($_GET['length']) ? intval($_GET['length']) : 10;
            $searchValue = isset($_GET['search']['value']) ? mysqli_real_escape_string($dbc, $_GET['search']['value']) : '';
            
            // Get sort parameters
            $orderColumn = 0;
            $orderDir = 'ASC';
            if (isset($_GET['order']) && is_array($_GET['order']) && count($_GET['order']) > 0) {
                $orderColumn = intval($_GET['order'][0]['column']);
                $orderDir = strtoupper($_GET['order'][0]['dir']) === 'DESC' ? 'DESC' : 'ASC';
            }
            
            $columns = ['id', 'salary_from', 'salary_to', 'monthly_contribution', 'description', 'year'];
            $orderColumnName = isset($columns[$orderColumn]) ? $columns[$orderColumn] : 'salary_from';
            
            // Build WHERE clause
            $whereClause = "`year` = ?";
            if (!empty($searchValue)) {
                $whereClause .= " AND (CAST(`salary_from` AS CHAR) LIKE '%$searchValue%' OR CAST(`salary_to` AS CHAR) LIKE '%$searchValue%' OR CAST(`monthly_contribution` AS CHAR) LIKE '%$searchValue%' OR `description` LIKE '%$searchValue%')";
            }
            
            // Get total count
            $countSql = "SELECT COUNT(*) as count FROM `sss_table` WHERE `year` = ?";
            $countStmt = mysqli_prepare($dbc, $countSql);
            mysqli_stmt_bind_param($countStmt, 'i', $year);
            mysqli_stmt_execute($countStmt);
            $countResult = mysqli_stmt_get_result($countStmt);
            $totalRecords = mysqli_fetch_assoc($countResult)['count'];
            mysqli_stmt_close($countStmt);
            
            // Get filtered count
            $filteredCountSql = "SELECT COUNT(*) as count FROM `sss_table` WHERE $whereClause";
            $filteredStmt = mysqli_prepare($dbc, $filteredCountSql);
            mysqli_stmt_bind_param($filteredStmt, 'i', $year);
            mysqli_stmt_execute($filteredStmt);
            $filteredResult = mysqli_stmt_get_result($filteredStmt);
            $filteredRecords = mysqli_fetch_assoc($filteredResult)['count'];
            mysqli_stmt_close($filteredStmt);
            
            // Get data
            $sql = "SELECT * FROM `sss_table` WHERE $whereClause ORDER BY `$orderColumnName` $orderDir LIMIT ?, ?";
            $stmt = mysqli_prepare($dbc, $sql);
            mysqli_stmt_bind_param($stmt, 'iii', $year, $start, $length);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $data = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
            mysqli_stmt_close($stmt);
            
            $response = [
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ];
            http_response_code(200);
            if (ob_get_length()) {
                ob_end_clean();
            }
            echo json_encode($response);
            exit;
        } else {
            // Legacy API call (for backwards compatibility)
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
