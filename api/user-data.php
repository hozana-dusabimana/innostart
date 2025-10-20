<?php
// Suppress all output buffering and errors
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Start session
session_start();

// Database connection without including config files
function getDatabaseConnection() {
    try {
        $host = 'localhost';
        $dbname = 'innostart_db';
        $username = 'root';
        $password = '';
        $charset = 'utf8mb4';
        
        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        return new PDO($dsn, $username, $password, $options);
    } catch(PDOException $e) {
        return null;
    }
}

// Get user ID from session
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Send JSON response
function sendResponse($data, $statusCode = 200) {
    // Clear any output buffer
    ob_clean();
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

// Handle the request
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? '';

try {
    $pdo = getDatabaseConnection();
    if (!$pdo) {
        sendResponse(['error' => 'Database connection failed'], 500);
    }
    
    // Check authentication
    $userId = getUserId();
    if (!$userId) {
        sendResponse(['error' => 'User not authenticated'], 401);
    }
    
    switch ($method) {
        case 'GET':
            handleGet($pdo, $userId, $action);
            break;
        case 'POST':
            handlePost($pdo, $userId);
            break;
        case 'PUT':
            handlePut($pdo, $userId);
            break;
        case 'DELETE':
            handleDelete($pdo, $userId);
            break;
        default:
            sendResponse(['error' => 'Method not allowed'], 405);
    }
    
} catch (Exception $e) {
    sendResponse(['error' => 'Server error: ' . $e->getMessage()], 500);
}

function handleGet($pdo, $userId, $action) {
    switch ($action) {
        case 'get_user_data':
            getUserData($pdo, $userId);
            break;
        case 'get_data_history':
            getDataHistory($pdo, $userId);
            break;
        default:
            sendResponse(['error' => 'Invalid action'], 400);
    }
}

function getUserData($pdo, $userId) {
    $dataType = $_GET['data_type'] ?? '';
    $dataId = $_GET['data_id'] ?? '';
    $limit = intval($_GET['limit'] ?? 50);
    $offset = intval($_GET['offset'] ?? 0);
    
    $whereConditions = ["user_id = ?"];
    $bindValues = [$userId];
    
    if (!empty($dataType)) {
        $whereConditions[] = "data_type = ?";
        $bindValues[] = $dataType;
    }
    
    if (!empty($dataId)) {
        $whereConditions[] = "id = ?";
        $bindValues[] = $dataId;
    }
    
    $whereClause = implode(" AND ", $whereConditions);
    
    $sql = "SELECT id, data_type, title, description, data_content, tags, 
                   is_favorite, is_public, status, created_at, updated_at
            FROM user_saved_data 
            WHERE $whereClause
            ORDER BY updated_at DESC
            LIMIT ? OFFSET ?";
    
    $bindValues[] = $limit;
    $bindValues[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($bindValues);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Decode JSON fields
    foreach ($data as &$row) {
        $row['data_content'] = json_decode($row['data_content'], true);
        $row['tags'] = json_decode($row['tags'], true);
    }
    
    // If requesting a specific data_id, return single object
    if (!empty($dataId)) {
        if (empty($data)) {
            sendResponse(['error' => 'Data not found'], 404);
        }
        sendResponse([
            'success' => true,
            'data' => $data[0]
        ]);
    }
    
    // Get total count for list requests
    $countSql = "SELECT COUNT(*) as total FROM user_saved_data WHERE $whereClause";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute(array_slice($bindValues, 0, -2));
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    sendResponse([
        'success' => true,
        'data' => $data,
        'total' => $totalCount,
        'limit' => $limit,
        'offset' => $offset
    ]);
}

function getDataHistory($pdo, $userId) {
    $dataId = $_GET['data_id'] ?? null;
    $dataType = $_GET['data_type'] ?? '';
    $limit = intval($_GET['limit'] ?? 100);
    $offset = intval($_GET['offset'] ?? 0);
    
    $whereConditions = ["user_id = ?"];
    $bindValues = [$userId];
    
    if ($dataId) {
        $whereConditions[] = "data_id = ?";
        $bindValues[] = $dataId;
    }
    
    if (!empty($dataType)) {
        $whereConditions[] = "data_type = ?";
        $bindValues[] = $dataType;
    }
    
    $whereClause = implode(" AND ", $whereConditions);
    
    $sql = "SELECT id, data_id, data_type, action, old_data, new_data, 
                   change_summary, created_at
            FROM user_data_history 
            WHERE $whereClause
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?";
    
    $bindValues[] = $limit;
    $bindValues[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($bindValues);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Decode JSON fields
    foreach ($history as &$item) {
        $item['old_data'] = json_decode($item['old_data'], true);
        $item['new_data'] = json_decode($item['new_data'], true);
    }
    
    sendResponse([
        'success' => true,
        'history' => $history
    ]);
}

function handlePost($pdo, $userId) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        sendResponse(['error' => 'Invalid JSON input'], 400);
    }
    
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'save_data':
            saveUserData($pdo, $userId, $input);
            break;
        default:
            sendResponse(['error' => 'Invalid action'], 400);
    }
}

function saveUserData($pdo, $userId, $data) {
    $dataType = $data['data_type'] ?? '';
    $title = $data['title'] ?? '';
    $description = $data['description'] ?? '';
    $dataContent = $data['data_content'] ?? [];
    $tags = $data['tags'] ?? [];
    $isFavorite = $data['is_favorite'] ?? false;
    $isPublic = $data['is_public'] ?? false;
    $status = $data['status'] ?? 'draft';
    
    if (empty($dataType) || empty($title) || empty($dataContent)) {
        sendResponse(['error' => 'Missing required fields'], 400);
    }
    
    $dataContentJson = json_encode($dataContent);
    $tagsJson = json_encode($tags);
    
    $sql = "INSERT INTO user_saved_data 
            (user_id, data_type, title, description, data_content, tags, is_favorite, is_public, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$userId, $dataType, $title, $description, 
        $dataContentJson, $tagsJson, $isFavorite, $isPublic, $status])) {
        
        $dataId = $pdo->lastInsertId();
        
        // Log the creation
        logDataHistory($pdo, $userId, $dataId, $dataType, 'created', null, $dataContent, 'Data created');
        
        sendResponse([
            'success' => true,
            'message' => 'Data saved successfully',
            'data_id' => $dataId
        ]);
    } else {
        sendResponse(['error' => 'Failed to save data'], 500);
    }
}

function handlePut($pdo, $userId) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        sendResponse(['error' => 'Invalid JSON input'], 400);
    }
    
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'update_data':
            updateUserData($pdo, $userId, $input);
            break;
        default:
            sendResponse(['error' => 'Invalid action'], 400);
    }
}

function updateUserData($pdo, $userId, $data) {
    $dataId = $data['data_id'] ?? null;
    if (!$dataId) {
        sendResponse(['error' => 'Data ID required'], 400);
    }
    
    $updateFields = [];
    $bindValues = [];
    
    if (isset($data['title'])) {
        $updateFields[] = "title = ?";
        $bindValues[] = $data['title'];
    }
    
    if (isset($data['description'])) {
        $updateFields[] = "description = ?";
        $bindValues[] = $data['description'];
    }
    
    if (isset($data['status'])) {
        $updateFields[] = "status = ?";
        $bindValues[] = $data['status'];
    }
    
    if (empty($updateFields)) {
        sendResponse(['error' => 'No fields to update'], 400);
    }
    
    $updateFields[] = "updated_at = NOW()";
    $bindValues[] = $dataId;
    $bindValues[] = $userId;
    
    $sql = "UPDATE user_saved_data 
            SET " . implode(", ", $updateFields) . "
            WHERE id = ? AND user_id = ?";
    
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute($bindValues)) {
        sendResponse([
            'success' => true,
            'message' => 'Data updated successfully'
        ]);
    } else {
        sendResponse(['error' => 'Failed to update data'], 500);
    }
}

function handleDelete($pdo, $userId) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        sendResponse(['error' => 'Invalid JSON input'], 400);
    }
    
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'delete_data':
            deleteUserData($pdo, $userId, $input);
            break;
        default:
            sendResponse(['error' => 'Invalid action'], 400);
    }
}

function deleteUserData($pdo, $userId, $data) {
    $dataId = $data['data_id'] ?? null;
    if (!$dataId) {
        sendResponse(['error' => 'Data ID required'], 400);
    }
    
    $sql = "DELETE FROM user_saved_data WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$dataId, $userId])) {
        sendResponse([
            'success' => true,
            'message' => 'Data deleted successfully'
        ]);
    } else {
        sendResponse(['error' => 'Failed to delete data'], 500);
    }
}

function logDataHistory($pdo, $userId, $dataId, $dataType, $action, $oldData, $newData, $changeSummary) {
    try {
        $sql = "INSERT INTO user_data_history 
                (user_id, data_id, data_type, action, old_data, new_data, change_summary) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $oldDataJson = $oldData ? json_encode($oldData) : null;
        $newDataJson = $newData ? json_encode($newData) : null;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $dataId, $dataType, $action, 
            $oldDataJson, $newDataJson, $changeSummary]);
    } catch (Exception $e) {
        // Log error but don't fail the main operation
        error_log("Failed to log data history: " . $e->getMessage());
    }
}
?>
