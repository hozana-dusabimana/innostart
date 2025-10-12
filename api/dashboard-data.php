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

// Simple file-based database simulation
// In a real application, this would connect to MySQL/PostgreSQL
class DashboardData {
    private $dataFile = '../data/dashboard_data.json';
    
    public function __construct() {
        $this->ensureDataFile();
    }
    
    private function ensureDataFile() {
        $dataDir = dirname($this->dataFile);
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }
        
        if (!file_exists($this->dataFile)) {
            $this->initializeData();
        }
    }
    
    private function initializeData() {
        $initialData = [
            'stats' => [
                'active_projects' => 8,
                'revenue_generated' => 32450,
                'total_users' => 847,
                'success_rate' => 92
            ],
            'recent_activities' => [
                [
                    'id' => 1,
                    'type' => 'business_plan',
                    'title' => 'Business Plan Created',
                    'description' => 'TechStart Solutions business plan completed',
                    'timestamp' => time() - 7200, // 2 hours ago
                    'icon' => 'fas fa-file-alt',
                    'color' => 'primary'
                ],
                [
                    'id' => 2,
                    'type' => 'chat',
                    'title' => 'AI Chat Session',
                    'description' => 'Discussed funding strategies with AI assistant',
                    'timestamp' => time() - 14400, // 4 hours ago
                    'icon' => 'fas fa-comments',
                    'color' => 'success'
                ],
                [
                    'id' => 3,
                    'type' => 'research',
                    'title' => 'Market Research',
                    'description' => 'Completed competitor analysis for retail sector',
                    'timestamp' => time() - 86400, // 1 day ago
                    'icon' => 'fas fa-search',
                    'color' => 'info'
                ],
                [
                    'id' => 4,
                    'type' => 'financial',
                    'title' => 'Financial Projections',
                    'description' => 'Updated Q4 revenue projections',
                    'timestamp' => time() - 172800, // 2 days ago
                    'icon' => 'fas fa-chart-line',
                    'color' => 'warning'
                ],
                [
                    'id' => 5,
                    'type' => 'business_plan',
                    'title' => 'Business Plan Updated',
                    'description' => 'Revised marketing strategy section',
                    'timestamp' => time() - 259200, // 3 days ago
                    'icon' => 'fas fa-file-alt',
                    'color' => 'primary'
                ]
            ],
            'business_plans' => [
                [
                    'id' => 1,
                    'name' => 'TechStart Solutions',
                    'type' => 'Technology',
                    'status' => 'completed',
                    'created_at' => time() - 7200
                ],
                [
                    'id' => 2,
                    'name' => 'Green Energy Co.',
                    'type' => 'Energy',
                    'status' => 'in_progress',
                    'created_at' => time() - 86400
                ],
                [
                    'id' => 3,
                    'name' => 'Local Food Delivery',
                    'type' => 'Food & Beverage',
                    'status' => 'completed',
                    'created_at' => time() - 172800
                ]
            ],
            'chat_sessions' => [
                [
                    'id' => 1,
                    'topic' => 'Funding Strategies',
                    'messages_count' => 12,
                    'last_activity' => time() - 14400
                ],
                [
                    'id' => 2,
                    'topic' => 'Market Analysis',
                    'messages_count' => 8,
                    'last_activity' => time() - 86400
                ]
            ]
        ];
        
        file_put_contents($this->dataFile, json_encode($initialData, JSON_PRETTY_PRINT));
    }
    
    public function getStats() {
        $data = json_decode(file_get_contents($this->dataFile), true);
        
        // Simulate some dynamic changes
        $data['stats']['active_projects'] = count($data['business_plans']);
        $data['stats']['total_users'] = 847 + rand(-10, 10);
        $data['stats']['success_rate'] = 92 + rand(-2, 2);
        
        return $data['stats'];
    }
    
    public function getRecentActivities($limit = 5) {
        $data = json_decode(file_get_contents($this->dataFile), true);
        
        // Sort by timestamp (most recent first)
        usort($data['recent_activities'], function($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });
        
        return array_slice($data['recent_activities'], 0, $limit);
    }
    
    public function addActivity($type, $title, $description) {
        $data = json_decode(file_get_contents($this->dataFile), true);
        
        $newActivity = [
            'id' => count($data['recent_activities']) + 1,
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'timestamp' => time(),
            'icon' => $this->getActivityIcon($type),
            'color' => $this->getActivityColor($type)
        ];
        
        array_unshift($data['recent_activities'], $newActivity);
        
        // Keep only last 20 activities
        $data['recent_activities'] = array_slice($data['recent_activities'], 0, 20);
        
        file_put_contents($this->dataFile, json_encode($data, JSON_PRETTY_PRINT));
        
        return $newActivity;
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
        $data = json_decode(file_get_contents($this->dataFile), true);
        $data['stats'] = array_merge($data['stats'], $stats);
        file_put_contents($this->dataFile, json_encode($data, JSON_PRETTY_PRINT));
        return $data['stats'];
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
