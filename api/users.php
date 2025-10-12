<?php
/**
 * User Management API for InnoStart
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';

class UserManager {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Get user profile
     */
    public function getUserProfile($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, email, first_name, last_name, phone, country, city, 
                       profile_image, bio, user_type, status, email_verified, 
                       last_login, created_at, updated_at
                FROM users 
                WHERE id = ? AND status = 'active'
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Remove sensitive data
                unset($user['password_hash']);
                return [
                    'success' => true,
                    'user' => $user
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Get user profile error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to get user profile'
            ];
        }
    }
    
    /**
     * Update user profile
     */
    public function updateUserProfile($userId, $userData) {
        try {
            // Validate allowed fields
            $allowedFields = ['first_name', 'last_name', 'phone', 'country', 'city', 'bio'];
            $updateFields = [];
            $updateValues = [];
            
            foreach ($allowedFields as $field) {
                if (isset($userData[$field])) {
                    $updateFields[] = "$field = ?";
                    $updateValues[] = $userData[$field];
                }
            }
            
            if (empty($updateFields)) {
                return [
                    'success' => false,
                    'message' => 'No valid fields to update'
                ];
            }
            
            $updateValues[] = $userId;
            $sql = "UPDATE users SET " . implode(', ', $updateFields) . ", updated_at = NOW() WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($updateValues);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Profile updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update profile'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Update user profile error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update profile'
            ];
        }
    }
    
    /**
     * Change password
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // Get current password hash
            $stmt = $this->db->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }
            
            // Verify current password
            if (!password_verify($currentPassword, $user['password_hash'])) {
                return [
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ];
            }
            
            // Hash new password
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password
            $stmt = $this->db->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$newPasswordHash, $userId]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Password changed successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to change password'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Change password error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to change password'
            ];
        }
    }
    
    /**
     * Get user statistics
     */
    public function getUserStats($userId) {
        try {
            $stats = [];
            
            // Business plans count
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM business_plans WHERE user_id = ?");
            $stmt->execute([$userId]);
            $stats['business_plans'] = $stmt->fetch()['count'];
            
            // Business ideas count
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM business_ideas WHERE user_id = ?");
            $stmt->execute([$userId]);
            $stats['business_ideas'] = $stmt->fetch()['count'];
            
            // Chat conversations count
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM chat_conversations WHERE user_id = ?");
            $stmt->execute([$userId]);
            $stats['chat_conversations'] = $stmt->fetch()['count'];
            
            // Total chat messages
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM chat_messages WHERE user_id = ?");
            $stmt->execute([$userId]);
            $stats['chat_messages'] = $stmt->fetch()['count'];
            
            return [
                'success' => true,
                'stats' => $stats
            ];
            
        } catch (Exception $e) {
            error_log("Get user stats error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to get user statistics'
            ];
        }
    }
    
    /**
     * Get user preferences
     */
    public function getUserPreferences($userId) {
        try {
            $stmt = $this->db->prepare("SELECT preference_key, preference_value FROM user_preferences WHERE user_id = ?");
            $stmt->execute([$userId]);
            $preferences = $stmt->fetchAll();
            
            $prefs = [];
            foreach ($preferences as $pref) {
                $prefs[$pref['preference_key']] = $pref['preference_value'];
            }
            
            return [
                'success' => true,
                'preferences' => $prefs
            ];
            
        } catch (Exception $e) {
            error_log("Get user preferences error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to get user preferences'
            ];
        }
    }
    
    /**
     * Update user preferences
     */
    public function updateUserPreferences($userId, $preferences) {
        try {
            $this->db->beginTransaction();
            
            foreach ($preferences as $key => $value) {
                $stmt = $this->db->prepare("
                    INSERT INTO user_preferences (user_id, preference_key, preference_value) 
                    VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE preference_value = VALUES(preference_value), updated_at = NOW()
                ");
                $stmt->execute([$userId, $key, $value]);
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Preferences updated successfully'
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Update user preferences error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update preferences'
            ];
        }
    }
    
    /**
     * Delete user account
     */
    public function deleteUser($userId, $password) {
        try {
            // Verify password
            $stmt = $this->db->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($password, $user['password_hash'])) {
                return [
                    'success' => false,
                    'message' => 'Invalid password'
                ];
            }
            
            // Delete user (cascade will handle related data)
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $result = $stmt->execute([$userId]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Account deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete account'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Delete user error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to delete account'
            ];
        }
    }
}

// Handle requests
$userManager = new UserManager();

// Get user ID from session or token (simplified for demo)
$userId = $_GET['user_id'] ?? null;

if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'profile';

switch ($method) {
    case 'GET':
        switch ($action) {
            case 'profile':
                $result = $userManager->getUserProfile($userId);
                break;
            case 'stats':
                $result = $userManager->getUserStats($userId);
                break;
            case 'preferences':
                $result = $userManager->getUserPreferences($userId);
                break;
            default:
                http_response_code(400);
                $result = ['success' => false, 'message' => 'Invalid action'];
        }
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        switch ($action) {
            case 'update-profile':
                $result = $userManager->updateUserProfile($userId, $input);
                break;
            case 'change-password':
                $currentPassword = $input['current_password'] ?? '';
                $newPassword = $input['new_password'] ?? '';
                $result = $userManager->changePassword($userId, $currentPassword, $newPassword);
                break;
            case 'update-preferences':
                $result = $userManager->updateUserPreferences($userId, $input);
                break;
            case 'delete-account':
                $password = $input['password'] ?? '';
                $result = $userManager->deleteUser($userId, $password);
                break;
            default:
                http_response_code(400);
                $result = ['success' => false, 'message' => 'Invalid action'];
        }
        break;
        
    default:
        http_response_code(405);
        $result = ['success' => false, 'message' => 'Method not allowed'];
}

echo json_encode($result);
?>
