<?php
/**
 * InnoStart Configuration File
 * Contains all configuration settings for the application
 */

// Database Configuration (if needed in future)
define('DB_HOST', 'localhost');
define('DB_NAME', 'innostart');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// API Configuration
define('OPENAI_API_KEY', getenv('OPENAI_API_KEY') ?: '');
define('OPENAI_API_URL', 'https://api.openai.com/v1');

// Application Configuration
define('APP_NAME', 'InnoStart');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/innostart');
define('APP_DEBUG', true);

// Security Configuration
define('SECRET_KEY', 'your-secret-key-here-change-in-production');
define('ENCRYPTION_METHOD', 'AES-256-CBC');

// File Upload Configuration
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_FILE_TYPES', ['pdf', 'doc', 'docx', 'txt']);

// Email Configuration (for future use)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('FROM_EMAIL', 'noreply@innostart.com');
define('FROM_NAME', 'InnoStart');

// Business Plan Configuration
define('BUSINESS_PLAN_TEMPLATE', 'default');
define('DEFAULT_PROJECTION_MONTHS', 12);
define('DEFAULT_GROWTH_RATE', 0.05);

// AI Configuration
define('AI_MODEL', 'gpt-3.5-turbo');
define('AI_MAX_TOKENS', 1500);
define('AI_TEMPERATURE', 0.7);
define('AI_TIMEOUT', 30);

// Rate Limiting Configuration
define('RATE_LIMIT_REQUESTS', 100);
define('RATE_LIMIT_WINDOW', 3600); // 1 hour

// Cache Configuration
define('CACHE_ENABLED', true);
define('CACHE_DURATION', 3600); // 1 hour

// Logging Configuration
define('LOG_ENABLED', true);
define('LOG_LEVEL', 'INFO');
define('LOG_FILE', 'logs/innostart.log');

// Feature Flags
define('FEATURE_AI_CHAT', true);
define('FEATURE_BUSINESS_IDEAS', true);
define('FEATURE_FINANCIAL_CALCULATOR', true);
define('FEATURE_BUSINESS_PLAN_GENERATOR', true);
define('FEATURE_PDF_EXPORT', true);
define('FEATURE_USER_ACCOUNTS', false); // Future feature

// Business Categories
$BUSINESS_CATEGORIES = [
    'retail' => 'Retail/E-commerce',
    'service' => 'Service Business',
    'manufacturing' => 'Manufacturing',
    'technology' => 'Technology/Software',
    'food' => 'Food & Beverage',
    'consulting' => 'Consulting',
    'healthcare' => 'Healthcare',
    'education' => 'Education',
    'real_estate' => 'Real Estate',
    'finance' => 'Finance',
    'tourism' => 'Tourism',
    'agriculture' => 'Agriculture',
    'creative' => 'Creative Services',
    'logistics' => 'Logistics',
    'energy' => 'Energy'
];

// Budget Ranges
$BUDGET_RANGES = [
    '0-1000' => '$0 - $1,000',
    '1000-5000' => '$1,000 - $5,000',
    '5000-10000' => '$5,000 - $10,000',
    '10000-50000' => '$10,000 - $50,000',
    '50000+' => '$50,000+'
];

// Difficulty Levels
$DIFFICULTY_LEVELS = [
    'easy' => 'Easy',
    'medium' => 'Medium',
    'hard' => 'Hard'
];

// Sample Business Ideas Database
$SAMPLE_BUSINESS_IDEAS = [
    [
        'title' => 'Local Food Delivery Service',
        'description' => 'A hyperlocal food delivery service focusing on home-cooked meals and local restaurants.',
        'category' => 'Food & Beverage',
        'budget' => 'Medium',
        'difficulty' => 'Medium',
        'tags' => ['food', 'delivery', 'local', 'technology']
    ],
    [
        'title' => 'Digital Marketing Agency',
        'description' => 'Provide social media management, SEO, and digital advertising services to local businesses.',
        'category' => 'Services',
        'budget' => 'Low',
        'difficulty' => 'Medium',
        'tags' => ['marketing', 'digital', 'social media', 'seo']
    ],
    [
        'title' => 'Eco-Friendly Product Store',
        'description' => 'Online store selling sustainable and eco-friendly products for environmentally conscious consumers.',
        'category' => 'E-commerce',
        'budget' => 'Medium',
        'difficulty' => 'Medium',
        'tags' => ['eco-friendly', 'sustainable', 'e-commerce', 'environment']
    ],
    [
        'title' => 'Virtual Assistant Services',
        'description' => 'Offer remote administrative, technical, or creative assistance to busy professionals.',
        'category' => 'Services',
        'budget' => 'Low',
        'difficulty' => 'Easy',
        'tags' => ['virtual', 'assistant', 'remote', 'administrative']
    ],
    [
        'title' => 'Mobile App Development',
        'description' => 'Create custom mobile applications for local businesses and startups.',
        'category' => 'Technology',
        'budget' => 'High',
        'difficulty' => 'Hard',
        'tags' => ['mobile', 'app', 'development', 'technology']
    ],
    [
        'title' => 'Personal Training Service',
        'description' => 'Provide one-on-one fitness training and wellness coaching.',
        'category' => 'Health & Fitness',
        'budget' => 'Low',
        'difficulty' => 'Medium',
        'tags' => ['fitness', 'health', 'training', 'wellness']
    ],
    [
        'title' => 'Art Classes & Workshops',
        'description' => 'Teach various art forms including painting, drawing, and crafts.',
        'category' => 'Education',
        'budget' => 'Low',
        'difficulty' => 'Easy',
        'tags' => ['art', 'education', 'workshops', 'creative']
    ],
    [
        'title' => 'Home-based Catering Service',
        'description' => 'Provide catering services for small events, parties, and corporate meetings.',
        'category' => 'Food & Beverage',
        'budget' => 'Low',
        'difficulty' => 'Easy',
        'tags' => ['catering', 'food', 'events', 'home-based']
    ]
];

// Error Messages
$ERROR_MESSAGES = [
    'invalid_input' => 'Invalid input provided',
    'api_error' => 'API error occurred',
    'file_upload_error' => 'File upload failed',
    'generation_error' => 'Content generation failed',
    'validation_error' => 'Validation error',
    'rate_limit_exceeded' => 'Rate limit exceeded. Please try again later.',
    'feature_disabled' => 'This feature is currently disabled'
];

// Success Messages
$SUCCESS_MESSAGES = [
    'ideas_generated' => 'Business ideas generated successfully',
    'projections_calculated' => 'Financial projections calculated successfully',
    'business_plan_generated' => 'Business plan generated successfully',
    'chat_response_sent' => 'Response sent successfully'
];

// Utility Functions
function getConfig($key, $default = null) {
    return defined($key) ? constant($key) : $default;
}

function isFeatureEnabled($feature) {
    $constant = 'FEATURE_' . strtoupper($feature);
    return defined($constant) ? constant($constant) : false;
}

function getBusinessCategory($key) {
    global $BUSINESS_CATEGORIES;
    return $BUSINESS_CATEGORIES[$key] ?? 'General';
}

function getBudgetRange($key) {
    global $BUDGET_RANGES;
    return $BUDGET_RANGES[$key] ?? 'Not specified';
}

function getDifficultyLevel($key) {
    global $DIFFICULTY_LEVELS;
    return $DIFFICULTY_LEVELS[$key] ?? 'Medium';
}

function getErrorMessage($key) {
    global $ERROR_MESSAGES;
    return $ERROR_MESSAGES[$key] ?? 'An error occurred';
}

function getSuccessMessage($key) {
    global $SUCCESS_MESSAGES;
    return $SUCCESS_MESSAGES[$key] ?? 'Operation completed successfully';
}

function logMessage($level, $message, $context = []) {
    if (!getConfig('LOG_ENABLED', false)) {
        return;
    }
    
    $logFile = getConfig('LOG_FILE', 'logs/innostart.log');
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
    $logEntry = "[$timestamp] [$level] $message$contextStr" . PHP_EOL;
    
    // Ensure log directory exists
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

function validateInput($data, $rules) {
    $errors = [];
    
    foreach ($rules as $field => $rule) {
        $value = $data[$field] ?? null;
        
        if (isset($rule['required']) && $rule['required'] && empty($value)) {
            $errors[$field] = "Field $field is required";
            continue;
        }
        
        if (isset($rule['type'])) {
            switch ($rule['type']) {
                case 'email':
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[$field] = "Invalid email format";
                    }
                    break;
                case 'numeric':
                    if (!is_numeric($value)) {
                        $errors[$field] = "Field $field must be numeric";
                    }
                    break;
                case 'string':
                    if (!is_string($value)) {
                        $errors[$field] = "Field $field must be a string";
                    }
                    break;
            }
        }
        
        if (isset($rule['min_length']) && strlen($value) < $rule['min_length']) {
            $errors[$field] = "Field $field must be at least {$rule['min_length']} characters";
        }
        
        if (isset($rule['max_length']) && strlen($value) > $rule['max_length']) {
            $errors[$field] = "Field $field must be no more than {$rule['max_length']} characters";
        }
    }
    
    return $errors;
}

function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set timezone
date_default_timezone_set('UTC');

// Error reporting
if (getConfig('APP_DEBUG', false)) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set memory limit
ini_set('memory_limit', '256M');

// Set execution time limit
set_time_limit(30);
?>

