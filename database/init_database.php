<?php
/**
 * Database Initialization Script for InnoStart
 * Run this script to set up the database
 */

require_once __DIR__ . '/../config/database.php';

class DatabaseInitializer {
    private $database;
    private $sqlFile;
    
    public function __construct() {
        $this->database = new Database();
        $this->sqlFile = __DIR__ . '/innostart_database.sql';
    }
    
    /**
     * Initialize the database
     */
    public function initialize() {
        echo "🚀 Initializing InnoStart Database...\n\n";
        
        // Test connection
        if (!$this->database->testConnection()) {
            echo "❌ Database connection failed!\n";
            echo "Please check your database configuration in config/database.php\n";
            return false;
        }
        
        echo "✅ Database connection successful!\n";
        
        // Read and execute SQL file
        if (!file_exists($this->sqlFile)) {
            echo "❌ SQL file not found: {$this->sqlFile}\n";
            return false;
        }
        
        $sql = file_get_contents($this->sqlFile);
        if ($sql === false) {
            echo "❌ Failed to read SQL file\n";
            return false;
        }
        
        echo "📄 Reading SQL file...\n";
        
        // Split SQL into individual statements
        $statements = $this->splitSQL($sql);
        echo "📊 Found " . count($statements) . " SQL statements\n\n";
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($statements as $index => $statement) {
            $statement = trim($statement);
            if (empty($statement) || strpos($statement, '--') === 0) {
                continue;
            }
            
            try {
                if ($this->database->executeSQL($statement)) {
                    $successCount++;
                    echo "✅ Statement " . ($index + 1) . " executed successfully\n";
                } else {
                    $errorCount++;
                    echo "❌ Statement " . ($index + 1) . " failed\n";
                }
            } catch (Exception $e) {
                $errorCount++;
                echo "❌ Statement " . ($index + 1) . " error: " . $e->getMessage() . "\n";
            }
        }
        
        echo "\n📈 Database Initialization Summary:\n";
        echo "✅ Successful: $successCount\n";
        echo "❌ Failed: $errorCount\n";
        
        if ($errorCount === 0) {
            echo "\n🎉 Database initialized successfully!\n";
            $this->verifyTables();
            return true;
        } else {
            echo "\n⚠️  Some statements failed. Please check the errors above.\n";
            return false;
        }
    }
    
    /**
     * Split SQL into individual statements
     */
    private function splitSQL($sql) {
        // Remove comments
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        
        // Split by semicolon
        $statements = explode(';', $sql);
        
        // Clean up statements
        $cleanStatements = [];
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                $cleanStatements[] = $statement;
            }
        }
        
        return $cleanStatements;
    }
    
    /**
     * Verify that tables were created
     */
    private function verifyTables() {
        echo "\n🔍 Verifying database tables...\n";
        
        $expectedTables = [
            'users',
            'business_plans',
            'chat_conversations',
            'chat_messages',
            'business_ideas',
            'analytics_data',
            'resources',
            'user_sessions',
            'user_preferences',
            'business_plan_templates'
        ];
        
        $existingTables = [];
        foreach ($expectedTables as $table) {
            if ($this->database->tableExists($table)) {
                $existingTables[] = $table;
                echo "✅ Table '$table' exists\n";
            } else {
                echo "❌ Table '$table' missing\n";
            }
        }
        
        echo "\n📊 Tables Summary: " . count($existingTables) . "/" . count($expectedTables) . " tables created\n";
        
        // Check for default data
        $this->checkDefaultData();
    }
    
    /**
     * Check if default data was inserted
     */
    private function checkDefaultData() {
        echo "\n🔍 Checking default data...\n";
        
        try {
            $pdo = $this->database->getConnection();
            
            // Check admin user
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'admin'");
            $adminCount = $stmt->fetch()['count'];
            echo "👤 Admin users: $adminCount\n";
            
            // Check business plan templates
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM business_plan_templates");
            $templateCount = $stmt->fetch()['count'];
            echo "📋 Business plan templates: $templateCount\n";
            
            // Check resources
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM resources");
            $resourceCount = $stmt->fetch()['count'];
            echo "📚 Resources: $resourceCount\n";
            
        } catch (Exception $e) {
            echo "❌ Error checking default data: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Get database information
     */
    public function getDatabaseInfo() {
        $info = $this->database->getDatabaseInfo();
        if ($info) {
            echo "\n📊 Database Information:\n";
            echo "Version: " . $info['version'] . "\n";
            echo "Database: " . $info['database'] . "\n";
            echo "Host: " . $info['host'] . "\n";
            echo "Charset: " . $info['charset'] . "\n";
        }
    }
}

// Run initialization if called directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    $initializer = new DatabaseInitializer();
    $initializer->getDatabaseInfo();
    $initializer->initialize();
}
?>
