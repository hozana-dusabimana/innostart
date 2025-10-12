<?php
/**
 * Database-based Authentication System for InnoStart
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include database configuration
require_once '../config/database.php';

// Start session
session_start();

class AuthManager {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Authenticate user login
     */
    public function login($email, $password, $rememberMe = false) {
        try {
            // Get user from database
            $stmt = $this->db->prepare("
                SELECT id, email, password_hash, first_name, last_name, user_type, status, email_verified
                FROM users 
                WHERE email = ? AND status = 'active'
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Invalid email or password'
                ];
            }
            
            // Verify password
            if (!password_verify($password, $user['password_hash'])) {
                return [
                    'success' => false,
                    'message' => 'Invalid email or password'
                ];
            }
            
            // Check if email is verified
            if (!$user['email_verified']) {
                return [
                    'success' => false,
                    'message' => 'Please verify your email address before logging in'
                ];
            }
            
            // Update last login
            $this->updateLastLogin($user['id']);
            
            // Create session
            $sessionToken = $this->createSession($user['id'], $rememberMe);
            
            // Prepare user data (without password)
            $userData = [
                'id' => $user['id'],
                'email' => $user['email'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'name' => $user['first_name'] . ' ' . $user['last_name'],
                'user_type' => $user['user_type'],
                'avatar' => strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1))
            ];
            
            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => $userData,
                'session_token' => $sessionToken
            ];
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Login failed. Please try again.'
            ];
        }
    }
    
    /**
     * Register new user
     */
    public function register($userData) {
        try {
            // Validate required fields
            $required = ['email', 'password', 'first_name', 'last_name'];
            foreach ($required as $field) {
                if (empty($userData[$field])) {
                    return [
                        'success' => false,
                        'message' => "Field '$field' is required"
                    ];
                }
            }
            
            // Check if email already exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$userData['email']]);
            if ($stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Email address already registered'
                ];
            }
            
            // Hash password
            $passwordHash = password_hash($userData['password'], PASSWORD_DEFAULT);
            
            // Generate email verification token
            $verificationToken = bin2hex(random_bytes(32));
            
            // Insert user
            $stmt = $this->db->prepare("
                INSERT INTO users (email, password_hash, first_name, last_name, phone, country, city, user_type, email_verification_token)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $userData['email'],
                $passwordHash,
                $userData['first_name'],
                $userData['last_name'],
                $userData['phone'] ?? null,
                $userData['country'] ?? 'Rwanda',
                $userData['city'] ?? 'Musanze',
                $userData['user_type'] ?? 'entrepreneur',
                $verificationToken
            ]);
            
            if ($result) {
                $userId = $this->db->lastInsertId();
                
                // TODO: Send verification email
                
                return [
                    'success' => true,
                    'message' => 'Registration successful. Please check your email to verify your account.',
                    'user_id' => $userId
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Registration failed. Please try again.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Registration failed. Please try again.'
            ];
        }
    }
    
    /**
     * Verify user session
     */
    public function verifySession($sessionToken) {
        try {
            $stmt = $this->db->prepare("
                SELECT u.id, u.email, u.first_name, u.last_name, u.user_type, u.status
                FROM users u
                JOIN user_sessions s ON u.id = s.user_id
                WHERE s.session_token = ? AND s.expires_at > NOW() AND u.status = 'active'
            ");
            $stmt->execute([$sessionToken]);
            $user = $stmt->fetch();
            
            if ($user) {
                return [
                    'success' => true,
                    'user' => [
                        'id' => $user['id'],
                        'email' => $user['email'],
                        'first_name' => $user['first_name'],
                        'last_name' => $user['last_name'],
                        'name' => $user['first_name'] . ' ' . $user['last_name'],
                        'user_type' => $user['user_type'],
                        'avatar' => strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1))
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Invalid or expired session'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Session verification error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Session verification failed'
            ];
        }
    }
    
    /**
     * Logout user
     */
    public function logout($sessionToken) {
        try {
            $stmt = $this->db->prepare("DELETE FROM user_sessions WHERE session_token = ?");
            $stmt->execute([$sessionToken]);
            
            return [
                'success' => true,
                'message' => 'Logged out successfully'
            ];
            
        } catch (Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Logout failed'
            ];
        }
    }
    
    /**
     * Update last login timestamp
     */
    private function updateLastLogin($userId) {
        try {
            $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$userId]);
        } catch (Exception $e) {
            error_log("Update last login error: " . $e->getMessage());
        }
    }
    
    /**
     * Create user session
     */
    private function createSession($userId, $rememberMe = false) {
        try {
            // Generate session token
            $sessionToken = bin2hex(random_bytes(32));
            
            // Set expiration (7 days if remember me, 24 hours otherwise)
            $expiresAt = $rememberMe ? 
                date('Y-m-d H:i:s', strtotime('+7 days')) : 
                date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            // Get client info
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            
            // Insert session
            $stmt = $this->db->prepare("
                INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, expires_at)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $sessionToken, $ipAddress, $userAgent, $expiresAt]);
            
            return $sessionToken;
            
        } catch (Exception $e) {
            error_log("Create session error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Clean expired sessions
     */
    public function cleanExpiredSessions() {
        try {
            $stmt = $this->db->prepare("DELETE FROM user_sessions WHERE expires_at < NOW()");
            $stmt->execute();
            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log("Clean sessions error: " . $e->getMessage());
            return 0;
        }
    }
}

// Handle requests
$auth = new AuthManager();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit();
    }
    
    $action = $input['action'] ?? 'login';
    
    switch ($action) {
        case 'login':
            $email = $input['email'] ?? '';
            $password = $input['password'] ?? '';
            $rememberMe = $input['rememberMe'] ?? false;
            
            $result = $auth->login($email, $password, $rememberMe);
            echo json_encode($result);
            break;
            
        case 'register':
            $result = $auth->register($input);
            echo json_encode($result);
            break;
            
        case 'logout':
            $sessionToken = $input['session_token'] ?? '';
            $result = $auth->logout($sessionToken);
            echo json_encode($result);
            break;
            
        case 'verify':
            $sessionToken = $input['session_token'] ?? '';
            $result = $auth->verifySession($sessionToken);
            echo json_encode($result);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
