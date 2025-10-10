<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Simple session management
session_start();

// Demo users database (in a real app, this would be a proper database)
$users = [
    'admin@innostart.com' => [
        'id' => 1,
        'name' => 'John Doe',
        'email' => 'admin@innostart.com',
        'password' => 'admin123', // In real app, this would be hashed
        'role' => 'admin',
        'avatar' => 'JD'
    ],
    'user@innostart.com' => [
        'id' => 2,
        'name' => 'Jane Smith',
        'email' => 'user@innostart.com',
        'password' => 'user123',
        'role' => 'user',
        'avatar' => 'JS'
    ],
    'demo@innostart.com' => [
        'id' => 3,
        'name' => 'Demo User',
        'email' => 'demo@innostart.com',
        'password' => 'demo123',
        'role' => 'user',
        'avatar' => 'DU'
    ]
];

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit();
    }
    
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
    $rememberMe = $input['rememberMe'] ?? false;
    
    // Validate input
    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        exit();
    }
    
    // Check if user exists
    if (!isset($users[$email])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit();
    }
    
    $user = $users[$email];
    
    // Verify password (in real app, use password_verify with hashed passwords)
    if ($user['password'] !== $password) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit();
    }
    
    // Create user session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['logged_in'] = true;
    
    // Set session cookie parameters
    if ($rememberMe) {
        // Extend session lifetime for "remember me"
        ini_set('session.cookie_lifetime', 86400 * 30); // 30 days
    }
    
    // Remove password from response
    unset($user['password']);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user' => $user,
        'session_id' => session_id()
    ]);
    exit();
}

// Handle logout
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Destroy session
    session_destroy();
    
    // Clear session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
    exit();
}

// Handle session check
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'check') {
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        $user = [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role'],
            'avatar' => substr($_SESSION['user_name'], 0, 2)
        ];
        
        echo json_encode([
            'success' => true,
            'logged_in' => true,
            'user' => $user
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'logged_in' => false
        ]);
    }
    exit();
}

// Handle user registration (demo)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'register') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit();
    }
    
    $name = $input['name'] ?? '';
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
    
    // Validate input
    if (empty($name) || empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit();
    }
    
    // Check if user already exists
    if (isset($users[$email])) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'User already exists']);
        exit();
    }
    
    // In a real app, you would:
    // 1. Hash the password
    // 2. Save to database
    // 3. Send verification email
    
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful! You can now login.',
        'user' => [
            'name' => $name,
            'email' => $email,
            'role' => 'user',
            'avatar' => substr($name, 0, 2)
        ]
    ]);
    exit();
}

// Default response
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
?>






