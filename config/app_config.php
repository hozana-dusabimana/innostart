<?php
/**
 * Application Configuration for InnoStart
 */

// Database Configuration
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'innostart_db');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

// Application Configuration
define('APP_NAME', 'InnoStart');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'development');
define('APP_DEBUG', $_ENV['APP_DEBUG'] ?? true);
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost/innostart');

// Security
define('APP_KEY', $_ENV['APP_KEY'] ?? 'innostart-secret-key-2024');
define('SESSION_LIFETIME', $_ENV['SESSION_LIFETIME'] ?? 1440); // 24 hours

// File Upload Configuration
define('MAX_FILE_SIZE', $_ENV['MAX_FILE_SIZE'] ?? 10485760); // 10MB
define('ALLOWED_FILE_TYPES', $_ENV['ALLOWED_FILE_TYPES'] ?? 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx');

// API Configuration
define('API_RATE_LIMIT', $_ENV['API_RATE_LIMIT'] ?? 100);
define('API_RATE_WINDOW', $_ENV['API_RATE_WINDOW'] ?? 3600);

// Currency Configuration
define('DEFAULT_CURRENCY', 'RWF');
define('CURRENCY_SYMBOL', 'RWF');

// Business Configuration
define('DEFAULT_LOCATION', 'Musanze');
define('DEFAULT_COUNTRY', 'Rwanda');

// Chat Configuration
define('CHAT_HISTORY_LIMIT', 50);
define('CHAT_SESSION_TIMEOUT', 3600); // 1 hour

// Business Plan Configuration
define('MAX_BUSINESS_PLANS', 10);
define('MAX_BUSINESS_IDEAS', 50);

// Analytics Configuration
define('ANALYTICS_RETENTION_DAYS', 365);
define('ANALYTICS_CLEANUP_INTERVAL', 86400); // 24 hours

// Error Reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set('Africa/Kigali');

// Session Configuration
ini_set('session.cookie_lifetime', SESSION_LIFETIME * 60);
ini_set('session.gc_maxlifetime', SESSION_LIFETIME * 60);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.cookie_httponly', true);
ini_set('session.use_strict_mode', true);
?>
