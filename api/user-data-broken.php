<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include database configuration
require_once __DIR__ . '/../config/database.php';

class UserDataAPI {
    private $pdo;
    
    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }
    
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch ($method) {
            case 'POST':
                $this->handlePost();
                break;
            case 'GET':
                $this->handleGet();
                break;
            case 'PUT':
                $this->handlePut();
                break;
            case 'DELETE':
                $this->handleDelete();
                break;
            default:
                $this->sendResponse(['error' => 'Method not allowed'], 405);
        }
    }
    
    private function handlePost() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $this->sendResponse(['error' => 'Invalid JSON input'], 400);
            return;
        }
        
        $action = $input['action'] ?? '';
        
        switch ($action) {
            case 'save_data':
                $this->saveUserData($input);
                break;
            case 'create_template':
                $this->createTemplate($input);
                break;
            case 'export_data':
                $this->exportData($input);
                break;
            case 'import_data':
                $this->importData($input);
                break;
            default:
                $this->sendResponse(['error' => 'Invalid action'], 400);
        }
    }
    
    private function handleGet() {
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'get_user_data':
                $this->getUserData($_GET);
                break;
            case 'get_data_history':
                $this->getDataHistory($_GET);
                break;
            case 'get_templates':
                $this->getTemplates($_GET);
                break;
            case 'get_export_history':
                $this->getExportHistory($_GET);
                break;
            case 'search_data':
                $this->searchData($_GET);
                break;
            default:
                $this->sendResponse(['error' => 'Invalid action'], 400);
        }
    }
    
    private function handlePut() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $this->sendResponse(['error' => 'Invalid JSON input'], 400);
            return;
        }
        
        $action = $input['action'] ?? '';
        
        switch ($action) {
            case 'update_data':
                $this->updateUserData($input);
                break;
            case 'update_template':
                $this->updateTemplate($input);
                break;
            case 'archive_data':
                $this->archiveData($input);
                break;
            case 'restore_data':
                $this->restoreData($input);
                break;
            default:
                $this->sendResponse(['error' => 'Invalid action'], 400);
        }
    }
    
    private function handleDelete() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $this->sendResponse(['error' => 'Invalid JSON input'], 400);
            return;
        }
        
        $action = $input['action'] ?? '';
        
        switch ($action) {
            case 'delete_data':
                $this->deleteUserData($input);
                break;
            case 'delete_template':
                $this->deleteTemplate($input);
                break;
            default:
                $this->sendResponse(['error' => 'Invalid action'], 400);
        }
    }
    
    // Save user data
    private function saveUserData($data) {
        try {
            $userId = $this->getUserId();
            if (!$userId) {
                $this->sendResponse(['error' => 'User not authenticated'], 401);
                return;
            }
            
            $dataType = $data['data_type'] ?? '';
            $title = $data['title'] ?? '';
            $description = $data['description'] ?? '';
            $dataContent = $data['data_content'] ?? [];
            $tags = $data['tags'] ?? [];
            $isFavorite = $data['is_favorite'] ?? false;
            $isPublic = $data['is_public'] ?? false;
            $status = $data['status'] ?? 'draft';
            
            if (empty($dataType) || empty($title) || empty($dataContent)) {
                $this->sendResponse(['error' => 'Missing required fields'], 400);
                return;
            }
            
            // Check if this is an update or new save
            $dataId = $data['data_id'] ?? null;
            
            if ($dataId) {
                // Update existing data
                $stmt = $this->pdo->prepare("
                    UPDATE user_saved_data 
                    SET title = ?, description = ?, data_content = ?, tags = ?, 
                        is_favorite = ?, is_public = ?, status = ?, updated_at = NOW()
                    WHERE id = ? AND user_id = ?
                ");
                $dataContentJson = json_encode($dataContent);
                $tagsJson = json_encode($tags);
                $stmt->bind_param("ssssiisii", $title, $description, $dataContentJson, $tagsJson, 
                    $isFavorite, $isPublic, $status, $dataId, $userId);
                
                if ($stmt->execute()) {
                    // Log the update
                    $this->logDataHistory($userId, $dataId, $dataType, 'updated', null, $dataContent, 'Data updated');
                    
                    $this->sendResponse([
                        'success' => true,
                        'message' => 'Data updated successfully',
                        'data_id' => $dataId
                    ]);
                } else {
                    $this->sendResponse(['error' => 'Failed to update data'], 500);
                }
            } else {
                // Create new data
                $stmt = $this->pdo->prepare("
                    INSERT INTO user_saved_data 
                    (user_id, data_type, title, description, data_content, tags, is_favorite, is_public, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $dataContentJson = json_encode($dataContent);
                $tagsJson = json_encode($tags);
                if ($stmt->execute([$userId, $dataType, $title, $description, 
                    $dataContentJson, $tagsJson, $isFavorite, $isPublic, $status])) {
                    $newDataId = $this->pdo->lastInsertId();
                    
                    // Log the creation
                    $this->logDataHistory($userId, $newDataId, $dataType, 'created', null, $dataContent, 'Data created');
                    
                    $this->sendResponse([
                        'success' => true,
                        'message' => 'Data saved successfully',
                        'data_id' => $newDataId
                    ]);
                } else {
                    $this->sendResponse(['error' => 'Failed to save data'], 500);
                }
            }
            
        } catch (Exception $e) {
            $this->sendResponse(['error' => 'Save failed: ' . $e->getMessage()], 500);
        }
    }
    
    // Get user data
    private function getUserData($params) {
        try {
            $userId = $this->getUserId();
            if (!$userId) {
                $this->sendResponse(['error' => 'User not authenticated'], 401);
                return;
            }
            
            $dataType = $params['data_type'] ?? '';
            $status = $params['status'] ?? '';
            $isFavorite = $params['is_favorite'] ?? '';
            $limit = intval($params['limit'] ?? 50);
            $offset = intval($params['offset'] ?? 0);
            
            $whereConditions = ["user_id = ?"];
            $bindTypes = "i";
            $bindValues = [$userId];
            
            if (!empty($dataType)) {
                $whereConditions[] = "data_type = ?";
                $bindTypes .= "s";
                $bindValues[] = $dataType;
            }
            
            if (!empty($status)) {
                $whereConditions[] = "status = ?";
                $bindTypes .= "s";
                $bindValues[] = $status;
            }
            
            if ($isFavorite !== '') {
                $whereConditions[] = "is_favorite = ?";
                $bindTypes .= "i";
                $bindValues[] = $isFavorite;
            }
            
            $whereClause = implode(" AND ", $whereConditions);
            
            $stmt = $this->pdo->prepare("
                SELECT id, data_type, title, description, data_content, tags, 
                       is_favorite, is_public, status, created_at, updated_at
                FROM user_saved_data 
                WHERE $whereClause
                ORDER BY updated_at DESC
                LIMIT ? OFFSET ?
            ");
            
            $bindTypes .= "ii";
            $bindValues[] = $limit;
            $bindValues[] = $offset;
            
            $stmt->execute($bindValues);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode JSON fields
            foreach ($data as &$row) {
                $row['data_content'] = json_decode($row['data_content'], true);
                $row['tags'] = json_decode($row['tags'], true);
            }
            
            // Get total count
            $countStmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM user_saved_data WHERE $whereClause");
            $countStmt->execute(array_slice($bindValues, 0, -2));
            $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            $this->sendResponse([
                'success' => true,
                'data' => $data,
                'total' => $totalCount,
                'limit' => $limit,
                'offset' => $offset
            ]);
            
        } catch (Exception $e) {
            $this->sendResponse(['error' => 'Failed to retrieve data: ' . $e->getMessage()], 500);
        }
    }
    
    // Get data history
    private function getDataHistory($params) {
        try {
            $userId = $this->getUserId();
            if (!$userId) {
                $this->sendResponse(['error' => 'User not authenticated'], 401);
                return;
            }
            
            $dataId = $params['data_id'] ?? null;
            $dataType = $params['data_type'] ?? '';
            $limit = intval($params['limit'] ?? 100);
            $offset = intval($params['offset'] ?? 0);
            
            $whereConditions = ["user_id = ?"];
            $bindTypes = "i";
            $bindValues = [$userId];
            
            if ($dataId) {
                $whereConditions[] = "data_id = ?";
                $bindTypes .= "i";
                $bindValues[] = $dataId;
            }
            
            if (!empty($dataType)) {
                $whereConditions[] = "data_type = ?";
                $bindTypes .= "s";
                $bindValues[] = $dataType;
            }
            
            $whereClause = implode(" AND ", $whereConditions);
            
            $stmt = $this->pdo->prepare("
                SELECT id, data_id, data_type, action, old_data, new_data, 
                       change_summary, created_at
                FROM user_data_history 
                WHERE $whereClause
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?
            ");
            
            $bindTypes .= "ii";
            $bindValues[] = $limit;
            $bindValues[] = $offset;
            
            $stmt->bind_param($bindTypes, ...$bindValues);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $history = [];
            while ($row = $result->fetch_assoc()) {
                $row['old_data'] = json_decode($row['old_data'], true);
                $row['new_data'] = json_decode($row['new_data'], true);
                $history[] = $row;
            }
            
            $this->sendResponse([
                'success' => true,
                'history' => $history
            ]);
            
        } catch (Exception $e) {
            $this->sendResponse(['error' => 'Failed to retrieve history: ' . $e->getMessage()], 500);
        }
    }
    
    // Create template
    private function createTemplate($data) {
        try {
            $userId = $this->getUserId();
            if (!$userId) {
                $this->sendResponse(['error' => 'User not authenticated'], 401);
                return;
            }
            
            $templateName = $data['template_name'] ?? '';
            $templateType = $data['template_type'] ?? '';
            $templateData = $data['template_data'] ?? [];
            $isPublic = $data['is_public'] ?? false;
            
            if (empty($templateName) || empty($templateType) || empty($templateData)) {
                $this->sendResponse(['error' => 'Missing required fields'], 400);
                return;
            }
            
            $stmt = $this->pdo->prepare("
                INSERT INTO user_data_templates 
                (user_id, template_name, template_type, template_data, is_public) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $templateDataJson = json_encode($templateData);
            $stmt->bind_param("isssi", $userId, $templateName, $templateType, $templateDataJson, $isPublic);
            
            if ($stmt->execute()) {
                $templateId = $this->pdo->lastInsertId();
                $this->sendResponse([
                    'success' => true,
                    'message' => 'Template created successfully',
                    'template_id' => $templateId
                ]);
            } else {
                $this->sendResponse(['error' => 'Failed to create template'], 500);
            }
            
        } catch (Exception $e) {
            $this->sendResponse(['error' => 'Template creation failed: ' . $e->getMessage()], 500);
        }
    }
    
    // Get templates
    private function getTemplates($params) {
        try {
            $userId = $this->getUserId();
            if (!$userId) {
                $this->sendResponse(['error' => 'User not authenticated'], 401);
                return;
            }
            
            $templateType = $params['template_type'] ?? '';
            $includePublic = $params['include_public'] ?? true;
            
            $whereConditions = ["(user_id = ?"];
            $bindTypes = "i";
            $bindValues = [$userId];
            
            if ($includePublic) {
                $whereConditions[0] = "(user_id = ? OR is_public = 1";
            }
            
            if (!empty($templateType)) {
                $whereConditions[] = "template_type = ?";
                $bindTypes .= "s";
                $bindValues[] = $templateType;
            }
            
            $whereClause = implode(" AND ", $whereConditions) . ")";
            
            $stmt = $this->pdo->prepare("
                SELECT id, template_name, template_type, template_data, is_public, 
                       usage_count, created_at, updated_at
                FROM user_data_templates 
                WHERE $whereClause
                ORDER BY usage_count DESC, created_at DESC
            ");
            
            $stmt->bind_param($bindTypes, ...$bindValues);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $templates = [];
            while ($row = $result->fetch_assoc()) {
                $row['template_data'] = json_decode($row['template_data'], true);
                $templates[] = $row;
            }
            
            $this->sendResponse([
                'success' => true,
                'templates' => $templates
            ]);
            
        } catch (Exception $e) {
            $this->sendResponse(['error' => 'Failed to retrieve templates: ' . $e->getMessage()], 500);
        }
    }
    
    // Update user data
    private function updateUserData($data) {
        try {
            $userId = $this->getUserId();
            if (!$userId) {
                $this->sendResponse(['error' => 'User not authenticated'], 401);
                return;
            }
            
            $dataId = $data['data_id'] ?? null;
            if (!$dataId) {
                $this->sendResponse(['error' => 'Data ID required'], 400);
                return;
            }
            
            // Get current data for history
            $currentStmt = $this->pdo->prepare("SELECT * FROM user_saved_data WHERE id = ? AND user_id = ?");
            $currentStmt->bind_param("ii", $dataId, $userId);
            $currentStmt->execute();
            $currentData = $currentStmt->get_result()->fetch_assoc();
            
            if (!$currentData) {
                $this->sendResponse(['error' => 'Data not found'], 404);
                return;
            }
            
            // Update data
            $updateFields = [];
            $bindTypes = "";
            $bindValues = [];
            
            if (isset($data['title'])) {
                $updateFields[] = "title = ?";
                $bindTypes .= "s";
                $bindValues[] = $data['title'];
            }
            
            if (isset($data['description'])) {
                $updateFields[] = "description = ?";
                $bindTypes .= "s";
                $bindValues[] = $data['description'];
            }
            
            if (isset($data['data_content'])) {
                $updateFields[] = "data_content = ?";
                $bindTypes .= "s";
                $bindValues[] = json_encode($data['data_content']);
            }
            
            if (isset($data['tags'])) {
                $updateFields[] = "tags = ?";
                $bindTypes .= "s";
                $bindValues[] = json_encode($data['tags']);
            }
            
            if (isset($data['is_favorite'])) {
                $updateFields[] = "is_favorite = ?";
                $bindTypes .= "i";
                $bindValues[] = $data['is_favorite'];
            }
            
            if (isset($data['is_public'])) {
                $updateFields[] = "is_public = ?";
                $bindTypes .= "i";
                $bindValues[] = $data['is_public'];
            }
            
            if (isset($data['status'])) {
                $updateFields[] = "status = ?";
                $bindTypes .= "s";
                $bindValues[] = $data['status'];
            }
            
            if (empty($updateFields)) {
                $this->sendResponse(['error' => 'No fields to update'], 400);
                return;
            }
            
            $updateFields[] = "updated_at = NOW()";
            $bindTypes .= "ii";
            $bindValues[] = $dataId;
            $bindValues[] = $userId;
            
            $stmt = $this->pdo->prepare("
                UPDATE user_saved_data 
                SET " . implode(", ", $updateFields) . "
                WHERE id = ? AND user_id = ?
            ");
            
            $stmt->bind_param($bindTypes, ...$bindValues);
            
            if ($stmt->execute()) {
                // Log the update
                $this->logDataHistory($userId, $dataId, $currentData['data_type'], 'updated', 
                    $currentData['data_content'], $data['data_content'] ?? $currentData['data_content'], 
                    'Data updated');
                
                $this->sendResponse([
                    'success' => true,
                    'message' => 'Data updated successfully'
                ]);
            } else {
                $this->sendResponse(['error' => 'Failed to update data'], 500);
            }
            
        } catch (Exception $e) {
            $this->sendResponse(['error' => 'Update failed: ' . $e->getMessage()], 500);
        }
    }
    
    // Delete user data
    private function deleteUserData($data) {
        try {
            $userId = $this->getUserId();
            if (!$userId) {
                $this->sendResponse(['error' => 'User not authenticated'], 401);
                return;
            }
            
            $dataId = $data['data_id'] ?? null;
            if (!$dataId) {
                $this->sendResponse(['error' => 'Data ID required'], 400);
                return;
            }
            
            // Get data before deletion for history
            $currentStmt = $this->pdo->prepare("SELECT * FROM user_saved_data WHERE id = ? AND user_id = ?");
            $currentStmt->bind_param("ii", $dataId, $userId);
            $currentStmt->execute();
            $currentData = $currentStmt->get_result()->fetch_assoc();
            
            if (!$currentData) {
                $this->sendResponse(['error' => 'Data not found'], 404);
                return;
            }
            
            // Delete the data
            $stmt = $this->pdo->prepare("DELETE FROM user_saved_data WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $dataId, $userId);
            
            if ($stmt->execute()) {
                // Log the deletion
                $this->logDataHistory($userId, $dataId, $currentData['data_type'], 'deleted', 
                    $currentData['data_content'], null, 'Data deleted');
                
                $this->sendResponse([
                    'success' => true,
                    'message' => 'Data deleted successfully'
                ]);
            } else {
                $this->sendResponse(['error' => 'Failed to delete data'], 500);
            }
            
        } catch (Exception $e) {
            $this->sendResponse(['error' => 'Delete failed: ' . $e->getMessage()], 500);
        }
    }
    
    // Archive data
    private function archiveData($data) {
        try {
            $userId = $this->getUserId();
            if (!$userId) {
                $this->sendResponse(['error' => 'User not authenticated'], 401);
                return;
            }
            
            $dataId = $data['data_id'] ?? null;
            if (!$dataId) {
                $this->sendResponse(['error' => 'Data ID required'], 400);
                return;
            }
            
            $stmt = $this->pdo->prepare("
                UPDATE user_saved_data 
                SET status = 'archived', updated_at = NOW()
                WHERE id = ? AND user_id = ?
            ");
            $stmt->bind_param("ii", $dataId, $userId);
            
            if ($stmt->execute()) {
                $this->logDataHistory($userId, $dataId, 'unknown', 'archived', null, null, 'Data archived');
                
                $this->sendResponse([
                    'success' => true,
                    'message' => 'Data archived successfully'
                ]);
            } else {
                $this->sendResponse(['error' => 'Failed to archive data'], 500);
            }
            
        } catch (Exception $e) {
            $this->sendResponse(['error' => 'Archive failed: ' . $e->getMessage()], 500);
        }
    }
    
    // Search data
    private function searchData($params) {
        try {
            $userId = $this->getUserId();
            if (!$userId) {
                $this->sendResponse(['error' => 'User not authenticated'], 401);
                return;
            }
            
            $query = $params['query'] ?? '';
            $dataType = $params['data_type'] ?? '';
            $tags = $params['tags'] ?? '';
            $limit = intval($params['limit'] ?? 50);
            $offset = intval($params['offset'] ?? 0);
            
            if (empty($query) && empty($tags)) {
                $this->sendResponse(['error' => 'Search query or tags required'], 400);
                return;
            }
            
            $whereConditions = ["user_id = ?"];
            $bindTypes = "i";
            $bindValues = [$userId];
            
            if (!empty($dataType)) {
                $whereConditions[] = "data_type = ?";
                $bindTypes .= "s";
                $bindValues[] = $dataType;
            }
            
            if (!empty($query)) {
                $whereConditions[] = "(title LIKE ? OR description LIKE ?)";
                $bindTypes .= "ss";
                $searchTerm = "%$query%";
                $bindValues[] = $searchTerm;
                $bindValues[] = $searchTerm;
            }
            
            if (!empty($tags)) {
                $whereConditions[] = "JSON_SEARCH(tags, 'one', ?) IS NOT NULL";
                $bindTypes .= "s";
                $bindValues[] = $tags;
            }
            
            $whereClause = implode(" AND ", $whereConditions);
            
            $stmt = $this->pdo->prepare("
                SELECT id, data_type, title, description, data_content, tags, 
                       is_favorite, is_public, status, created_at, updated_at
                FROM user_saved_data 
                WHERE $whereClause
                ORDER BY updated_at DESC
                LIMIT ? OFFSET ?
            ");
            
            $bindTypes .= "ii";
            $bindValues[] = $limit;
            $bindValues[] = $offset;
            
            $stmt->bind_param($bindTypes, ...$bindValues);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $row['data_content'] = json_decode($row['data_content'], true);
                $row['tags'] = json_decode($row['tags'], true);
                $data[] = $row;
            }
            
            $this->sendResponse([
                'success' => true,
                'data' => $data,
                'query' => $query,
                'total' => count($data)
            ]);
            
        } catch (Exception $e) {
            $this->sendResponse(['error' => 'Search failed: ' . $e->getMessage()], 500);
        }
    }
    
    // Log data history
    private function logDataHistory($userId, $dataId, $dataType, $action, $oldData, $newData, $changeSummary) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO user_data_history 
                (user_id, data_id, data_type, action, old_data, new_data, change_summary) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $oldDataJson = $oldData ? json_encode($oldData) : null;
            $newDataJson = $newData ? json_encode($newData) : null;
            
            $stmt->bind_param("iisssss", $userId, $dataId, $dataType, $action, 
                $oldDataJson, $newDataJson, $changeSummary);
            $stmt->execute();
        } catch (Exception $e) {
            // Log error but don't fail the main operation
            error_log("Failed to log data history: " . $e->getMessage());
        }
    }
    
    // Get user ID from session
    private function getUserId() {
        session_start();
        return $_SESSION['user_id'] ?? null;
    }
    
    // Send JSON response
    private function sendResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data);
        exit();
    }
}

// Initialize and handle the request
$api = new UserDataAPI();
$api->handleRequest();
?>
