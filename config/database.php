<?php
/**
 * Database Configuration for InnoStart
 * Handles database connection and configuration
 */

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $charset;
    private $pdo;
    
    public function __construct() {
        // Include app configuration
        require_once __DIR__ . '/app_config.php';
        
        // Database configuration
        $this->host = DB_HOST;
        $this->db_name = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        $this->charset = 'utf8mb4';
    }
    
    /**
     * Get database connection
     */
    public function getConnection() {
        $this->pdo = null;
        
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $exception) {
            error_log("Database connection error: " . $exception->getMessage());
            throw new Exception("Database connection failed");
        }
        
        return $this->pdo;
    }
    
    /**
     * Test database connection
     */
    public function testConnection() {
        try {
            $pdo = $this->getConnection();
            $stmt = $pdo->query("SELECT 1");
            return $stmt !== false;
        } catch(Exception $e) {
            return false;
        }
    }
    
    /**
     * Get database info
     */
    public function getDatabaseInfo() {
        try {
            $pdo = $this->getConnection();
            $stmt = $pdo->query("SELECT VERSION() as version");
            $version = $stmt->fetch();
            
            $stmt = $pdo->query("SELECT DATABASE() as database_name");
            $db_name = $stmt->fetch();
            
            return [
                'version' => $version['version'],
                'database' => $db_name['database_name'],
                'host' => $this->host,
                'charset' => $this->charset
            ];
        } catch(Exception $e) {
            return null;
        }
    }
    
    /**
     * Execute raw SQL (for migrations)
     */
    public function executeSQL($sql) {
        try {
            $pdo = $this->getConnection();
            $pdo->exec($sql);
            return true;
        } catch(PDOException $e) {
            error_log("SQL execution error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if table exists
     */
    public function tableExists($tableName) {
        try {
            $pdo = $this->getConnection();
            $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$tableName]);
            return $stmt->rowCount() > 0;
        } catch(Exception $e) {
            return false;
        }
    }
    
    /**
     * Get table structure
     */
    public function getTableStructure($tableName) {
        try {
            $pdo = $this->getConnection();
            $stmt = $pdo->prepare("DESCRIBE `$tableName`");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(Exception $e) {
            return [];
        }
    }
}

// Global database instance
$database = new Database();

// Helper functions
function getDB() {
    global $database;
    return $database->getConnection();
}

function testDBConnection() {
    global $database;
    return $database->testConnection();
}

function getDBInfo() {
    global $database;
    return $database->getDatabaseInfo();
}
?>
