<?php
/**
 * Database Setup API for InnoStart
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';

class DatabaseSetup {
    private $database;
    private $sqlFile;
    
    public function __construct() {
        $this->database = new Database();
        $this->sqlFile = __DIR__ . '/../database/innostart_database.sql';
    }
    
    /**
     * Check database connection
     */
    public function checkConnection() {
        try {
            if (!$this->database->testConnection()) {
                return [
                    'success' => false,
                    'message' => 'Database connection failed'
                ];
            }
            
            $info = $this->database->getDatabaseInfo();
            return [
                'success' => true,
                'data' => $info
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Create database tables
     */
    public function createTables() {
        try {
            if (!file_exists($this->sqlFile)) {
                return [
                    'success' => false,
                    'message' => 'SQL file not found'
                ];
            }
            
            $sql = file_get_contents($this->sqlFile);
            if ($sql === false) {
                return [
                    'success' => false,
                    'message' => 'Failed to read SQL file'
                ];
            }
            
            // Split SQL into statements
            $statements = $this->splitSQL($sql);
            $tablesCreated = 0;
            $errors = [];
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (empty($statement) || strpos($statement, '--') === 0) {
                    continue;
                }
                
                // Skip INSERT statements for now (we'll handle them separately)
                if (stripos($statement, 'INSERT INTO') === 0) {
                    continue;
                }
                
                try {
                    if ($this->database->executeSQL($statement)) {
                        if (stripos($statement, 'CREATE TABLE') === 0) {
                            $tablesCreated++;
                        }
                    }
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
            
            return [
                'success' => true,
                'data' => [
                    'tables_created' => $tablesCreated,
                    'errors' => $errors
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Table creation error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Insert default data
     */
    public function insertDefaultData() {
        try {
            $pdo = $this->database->getConnection();
            $adminUsers = 0;
            $templates = 0;
            $resources = 0;
            
            // Insert default admin user
            $stmt = $pdo->prepare("
                INSERT INTO users (email, password_hash, first_name, last_name, user_type, status, email_verified) 
                VALUES ('admin@innostart.com', ?, 'Admin', 'User', 'admin', 'active', TRUE)
                ON DUPLICATE KEY UPDATE email = email
            ");
            $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
            if ($stmt->execute([$passwordHash])) {
                $adminUsers = 1;
            }
            
            // Insert business plan templates
            $templatesData = [
                ['Mountain Hiking Tours', 'Tourism', 'Adventure Tourism', 'Musanze'],
                ['Volcano Trekking', 'Tourism', 'Adventure Tourism', 'Musanze'],
                ['Local Restaurant', 'Food & Beverage', 'Restaurant', 'Musanze'],
                ['Eco-lodges', 'Hospitality', 'Eco-tourism', 'Musanze'],
                ['Food Processing', 'Manufacturing', 'Food Processing', 'Musanze'],
                ['Coffee Processing', 'Manufacturing', 'Coffee Processing', 'Musanze']
            ];
            
            foreach ($templatesData as $template) {
                $stmt = $pdo->prepare("
                    INSERT INTO business_plan_templates (name, business_type, industry, location, template_data, is_default, is_active) 
                    VALUES (?, ?, ?, ?, ?, TRUE, TRUE)
                    ON DUPLICATE KEY UPDATE name = name
                ");
                $templateData = json_encode([
                    'sections' => [
                        'executive_summary' => $template[0] . ' business plan',
                        'market_analysis' => 'Target market analysis',
                        'financial_projections' => [
                            'startup_costs' => '1000000-10000000',
                            'monthly_revenue' => '500000-5000000'
                        ]
                    ]
                ]);
                if ($stmt->execute([$template[0], $template[1], $template[2], $template[3], $templateData])) {
                    $templates++;
                }
            }
            
            // Insert default resources
            $resourcesData = [
                ['Market Research Guide', 'Comprehensive guide to conducting market research for startups', 'Research', 'guide'],
                ['Legal Requirements Checklist', 'Essential legal requirements for starting a business in Rwanda', 'Legal', 'checklist'],
                ['Marketing Strategy Framework', 'Step-by-step framework for developing marketing strategies', 'Marketing', 'framework'],
                ['Financial Projections Template', 'Excel template for creating financial projections', 'Finance', 'template'],
                ['Business Plan Structure', 'Complete business plan structure and guidelines', 'Planning', 'template'],
                ['Funding Strategy Toolkit', 'Tools and strategies for securing business funding', 'Funding', 'toolkit']
            ];
            
            foreach ($resourcesData as $resource) {
                $stmt = $pdo->prepare("
                    INSERT INTO resources (title, description, category, resource_type, tags, is_featured, is_active) 
                    VALUES (?, ?, ?, ?, ?, TRUE, TRUE)
                    ON DUPLICATE KEY UPDATE title = title
                ");
                $tags = json_encode([strtolower($resource[2]), 'startup', 'business']);
                if ($stmt->execute([$resource[0], $resource[1], $resource[2], $resource[3], $tags])) {
                    $resources++;
                }
            }
            
            return [
                'success' => true,
                'data' => [
                    'admin_users' => $adminUsers,
                    'templates' => $templates,
                    'resources' => $resources
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Data insertion error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Verify setup
     */
    public function verifySetup() {
        try {
            $pdo = $this->database->getConnection();
            
            $expectedTables = [
                'users', 'business_plans', 'chat_conversations', 'chat_messages',
                'business_ideas', 'analytics_data', 'resources', 'user_sessions',
                'user_preferences', 'business_plan_templates'
            ];
            
            $tablesFound = 0;
            foreach ($expectedTables as $table) {
                if ($this->database->tableExists($table)) {
                    $tablesFound++;
                }
            }
            
            // Count default data
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'admin'");
            $adminUsers = $stmt->fetch()['count'];
            
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM business_plan_templates");
            $templates = $stmt->fetch()['count'];
            
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM resources");
            $resources = $stmt->fetch()['count'];
            
            return [
                'success' => true,
                'data' => [
                    'tables_found' => $tablesFound,
                    'tables_expected' => count($expectedTables),
                    'admin_users' => $adminUsers,
                    'templates' => $templates,
                    'resources' => $resources
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Verification error: ' . $e->getMessage()
            ];
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
}

// Handle requests
$setup = new DatabaseSetup();
$action = $_GET['action'] ?? 'check_connection';

switch ($action) {
    case 'check_connection':
        $result = $setup->checkConnection();
        break;
    case 'create_tables':
        $result = $setup->createTables();
        break;
    case 'insert_default_data':
        $result = $setup->insertDefaultData();
        break;
    case 'verify_setup':
        $result = $setup->verifySetup();
        break;
    default:
        $result = [
            'success' => false,
            'message' => 'Invalid action'
        ];
}

echo json_encode($result);
?>
