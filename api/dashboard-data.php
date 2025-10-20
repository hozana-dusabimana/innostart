<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection function
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

// Real database-driven dashboard data
class DashboardData {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDatabaseConnection();
    }
    
    public function getStats() {
        if (!$this->pdo) {
            return $this->getDefaultStats();
        }
        
        try {
            // Get total users
            $stmt = $this->pdo->query("SELECT COUNT(*) as total_users FROM users");
            $totalUsers = $stmt->fetch()['total_users'];
            
            // Get total business plans
            $stmt = $this->pdo->query("SELECT COUNT(*) as total_business_plans FROM user_saved_data WHERE data_type = 'business_plan'");
            $totalBusinessPlans = $stmt->fetch()['total_business_plans'];
            
            // Get total financial projections
            $stmt = $this->pdo->query("SELECT COUNT(*) as total_projections FROM user_saved_data WHERE data_type = 'financial_projection'");
            $totalProjections = $stmt->fetch()['total_projections'];
            
            // Calculate total revenue from financial projections
            $stmt = $this->pdo->query("SELECT data_content FROM user_saved_data WHERE data_type = 'financial_projection'");
            $projections = $stmt->fetchAll();
            $totalRevenue = 0;
            
            foreach ($projections as $projection) {
                $data = json_decode($projection['data_content'], true);
                if (isset($data['summary']['totalRevenue'])) {
                    $totalRevenue += $data['summary']['totalRevenue'];
                }
            }
            
            // Set success rate to exactly 99%
            $successRate = 99;
            
            return [
                'active_projects' => $totalBusinessPlans,
                'revenue_generated' => $totalRevenue,
                'total_users' => $totalUsers,
                'success_rate' => $successRate
            ];
            
        } catch (Exception $e) {
            error_log("Dashboard stats error: " . $e->getMessage());
            return $this->getDefaultStats();
        }
    }
    
    private function getDefaultStats() {
        return [
            'active_projects' => 0,
            'revenue_generated' => 0,
            'total_users' => 0,
            'success_rate' => 0
        ];
    }
    
    public function getRecentActivities($limit = 5) {
        if (!$this->pdo) {
            return [];
        }
        
        try {
            // Get recent user data activities
            $stmt = $this->pdo->prepare("
                SELECT 
                    id,
                    data_type,
                    title,
                    description,
                    created_at,
                    updated_at
                FROM user_saved_data 
                ORDER BY updated_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            $activities = $stmt->fetchAll();
            
            $formattedActivities = [];
            foreach ($activities as $activity) {
                $formattedActivities[] = [
                    'id' => $activity['id'],
                    'type' => $activity['data_type'],
                    'title' => $activity['title'],
                    'description' => $activity['description'] ?: 'No description available',
                    'timestamp' => strtotime($activity['updated_at']),
                    'icon' => $this->getActivityIcon($activity['data_type']),
                    'color' => $this->getActivityColor($activity['data_type'])
                ];
            }
            
            return $formattedActivities;
            
        } catch (Exception $e) {
            error_log("Recent activities error: " . $e->getMessage());
            return [];
        }
    }
    
    public function addActivity($type, $title, $description) {
        // This method is kept for compatibility but activities are now stored in the database
        // through the user_saved_data table
        return [
            'id' => time(),
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'timestamp' => time(),
            'icon' => $this->getActivityIcon($type),
            'color' => $this->getActivityColor($type)
        ];
    }
    
    private function getActivityIcon($type) {
        $icons = [
            'business_plan' => 'fas fa-file-alt',
            'chat' => 'fas fa-comments',
            'research' => 'fas fa-search',
            'financial' => 'fas fa-chart-line',
            'user' => 'fas fa-user',
            'system' => 'fas fa-cog'
        ];
        
        return $icons[$type] ?? 'fas fa-circle';
    }
    
    private function getActivityColor($type) {
        $colors = [
            'business_plan' => 'primary',
            'chat' => 'success',
            'research' => 'info',
            'financial' => 'warning',
            'user' => 'secondary',
            'system' => 'dark'
        ];
        
        return $colors[$type] ?? 'secondary';
    }
    
    public function updateStats($stats) {
        // Stats are now calculated dynamically from the database
        // This method is kept for compatibility
        return $this->getStats();
    }
}

try {
    $dashboard = new DashboardData();
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? 'stats';
        
        switch ($action) {
            case 'stats':
                $response = [
                    'success' => true,
                    'data' => $dashboard->getStats()
                ];
                break;
                
            case 'activities':
                $limit = intval($_GET['limit'] ?? 5);
                $response = [
                    'success' => true,
                    'data' => $dashboard->getRecentActivities($limit)
                ];
                break;
                
            default:
                $response = [
                    'success' => false,
                    'error' => 'Invalid action'
                ];
        }
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';
        
        switch ($action) {
            case 'add_activity':
                $type = $input['type'] ?? '';
                $title = $input['title'] ?? '';
                $description = $input['description'] ?? '';
                
                if ($type && $title) {
                    $activity = $dashboard->addActivity($type, $title, $description);
                    $response = [
                        'success' => true,
                        'data' => $activity
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'error' => 'Missing required fields'
                    ];
                }
                break;
                
            case 'update_stats':
                $stats = $input['stats'] ?? [];
                $updatedStats = $dashboard->updateStats($stats);
                $response = [
                    'success' => true,
                    'data' => $updatedStats
                ];
                break;
                
            default:
                $response = [
                    'success' => false,
                    'error' => 'Invalid action'
                ];
        }
        
    } else {
        $response = [
            'success' => false,
            'error' => 'Method not allowed'
        ];
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
}
?>
