<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include database configuration
require_once '../config/database.php';

class FinancialProjectionsAPI {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch ($method) {
            case 'POST':
                $this->handlePost();
                break;
            case 'GET':
                $this->handleGet();
                break;
            default:
                $this->sendResponse(['error' => 'Method not allowed'], 405);
        }
    }
    
    private function handlePost() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $this->sendResponse(['error' => 'Invalid JSON input'], 400);
            return;
        }
        
        $action = $input['action'] ?? '';
        
        switch ($action) {
            case 'save_projection':
                $this->saveProjection($input);
                break;
            case 'get_projections':
                $this->getUserProjections($input);
                break;
            case 'delete_projection':
                $this->deleteProjection($input);
                break;
            case 'calculate_projection':
                $this->calculateProjection($input);
                break;
            default:
                $this->sendResponse(['error' => 'Invalid action'], 400);
        }
    }
    
    private function handleGet() {
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'get_templates':
                $this->getBusinessTemplates();
                break;
            case 'get_metrics':
                $this->getFinancialMetrics();
                break;
            default:
                $this->sendResponse(['error' => 'Invalid action'], 400);
        }
    }
    
    private function calculateProjection($data) {
        try {
            $params = $data['parameters'] ?? [];
            
            // Validate required parameters
            $required = ['businessName', 'businessType', 'projectionPeriod', 'monthlyRevenue', 'monthlyExpenses'];
            foreach ($required as $field) {
                if (!isset($params[$field]) || empty($params[$field])) {
                    $this->sendResponse(['error' => "Missing required field: $field"], 400);
                    return;
                }
            }
            
            // Calculate financial projections
            $projections = $this->performCalculations($params);
            
            $this->sendResponse([
                'success' => true,
                'data' => $projections
            ]);
            
        } catch (Exception $e) {
            $this->sendResponse(['error' => 'Calculation failed: ' . $e->getMessage()], 500);
        }
    }
    
    private function performCalculations($params) {
        $businessName = $params['businessName'];
        $businessType = $params['businessType'];
        $projectionPeriod = intval($params['projectionPeriod']);
        $initialInvestment = floatval($params['initialInvestment'] ?? 0);
        $monthlyRevenue = floatval($params['monthlyRevenue']);
        $monthlyExpenses = floatval($params['monthlyExpenses']);
        $growthRate = floatval($params['growthRate'] ?? 10);
        $taxRate = floatval($params['taxRate'] ?? 15);
        $inflationRate = floatval($params['inflationRate'] ?? 5);
        $discountRate = floatval($params['discountRate'] ?? 12);
        $breakEvenMonths = intval($params['breakEvenMonths'] ?? 12);
        
        $months = $projectionPeriod * 12;
        $projections = [];
        $cumulativeProfit = -$initialInvestment;
        $totalRevenue = 0;
        $totalExpenses = 0;
        
        for ($month = 1; $month <= $months; $month++) {
            // Calculate growth-adjusted revenue and expenses
            $growthFactor = pow(1 + $growthRate / 100, ($month - 1) / 12);
            $inflationFactor = pow(1 + $inflationRate / 100, ($month - 1) / 12);
            
            $currentRevenue = $monthlyRevenue * $growthFactor * $inflationFactor;
            $currentExpenses = $monthlyExpenses * $inflationFactor;
            
            // Calculate profit before tax
            $grossProfit = $currentRevenue - $currentExpenses;
            
            // Calculate tax
            $tax = max(0, $grossProfit * ($taxRate / 100));
            
            // Calculate net profit
            $netProfit = $grossProfit - $tax;
            
            // Update cumulative values
            $cumulativeProfit += $netProfit;
            $totalRevenue += $currentRevenue;
            $totalExpenses += $currentExpenses;
            
            $projections[] = [
                'month' => $month,
                'monthName' => $this->getMonthName($month),
                'revenue' => round($currentRevenue, 2),
                'expenses' => round($currentExpenses, 2),
                'grossProfit' => round($grossProfit, 2),
                'tax' => round($tax, 2),
                'netProfit' => round($netProfit, 2),
                'cumulativeProfit' => round($cumulativeProfit, 2)
            ];
        }
        
        // Calculate summary metrics
        $totalNetProfit = $totalRevenue - $totalExpenses - ($totalRevenue * $taxRate / 100);
        $profitMargin = $totalRevenue > 0 ? (($totalNetProfit / $totalRevenue) * 100) : 0;
        
        // Calculate additional metrics
        $roi = $initialInvestment > 0 ? (($cumulativeProfit / $initialInvestment) * 100) : 0;
        $paybackPeriod = $this->calculatePaybackPeriod($projections, $initialInvestment);
        
        return [
            'businessName' => $businessName,
            'businessType' => $businessType,
            'summary' => [
                'totalRevenue' => round($totalRevenue, 2),
                'totalExpenses' => round($totalExpenses, 2),
                'totalNetProfit' => round($totalNetProfit, 2),
                'profitMargin' => round($profitMargin, 2),
                'initialInvestment' => round($initialInvestment, 2),
                'finalCumulativeProfit' => round($cumulativeProfit, 2),
                'roi' => round($roi, 2),
                'paybackPeriod' => $paybackPeriod
            ],
            'projections' => $projections,
            'parameters' => $params,
            'generatedAt' => date('Y-m-d H:i:s')
        ];
    }
    
    private function getMonthName($monthNumber) {
        $monthNames = [
            'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
        ];
        
        $year = floor(($monthNumber - 1) / 12) + 1;
        $month = (($monthNumber - 1) % 12) + 1;
        
        return $monthNames[$month - 1] . ' Y' . $year;
    }
    
    private function calculatePaybackPeriod($projections, $initialInvestment) {
        $cumulativeCashFlow = -$initialInvestment;
        
        foreach ($projections as $projection) {
            $cumulativeCashFlow += $projection['netProfit'];
            if ($cumulativeCashFlow >= 0) {
                return $projection['month'];
            }
        }
        
        return count($projections); // If never breaks even
    }
    
    private function saveProjection($data) {
        try {
            $userId = $this->getUserId();
            if (!$userId) {
                $this->sendResponse(['error' => 'User not authenticated'], 401);
                return;
            }
            
            $projectionData = $data['projection'] ?? [];
            if (empty($projectionData)) {
                $this->sendResponse(['error' => 'No projection data provided'], 400);
                return;
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO financial_projections 
                (user_id, business_name, business_type, projection_data, created_at, updated_at) 
                VALUES (?, ?, ?, ?, NOW(), NOW())
            ");
            
            $projectionJson = json_encode($projectionData);
            $stmt->bind_param("isss", $userId, $projectionData['businessName'], $projectionData['businessType'], $projectionJson);
            
            if ($stmt->execute()) {
                $projectionId = $this->db->insert_id;
                $this->sendResponse([
                    'success' => true,
                    'projectionId' => $projectionId,
                    'message' => 'Projection saved successfully'
                ]);
            } else {
                $this->sendResponse(['error' => 'Failed to save projection'], 500);
            }
            
        } catch (Exception $e) {
            $this->sendResponse(['error' => 'Save failed: ' . $e->getMessage()], 500);
        }
    }
    
    private function getUserProjections($data) {
        try {
            $userId = $this->getUserId();
            if (!$userId) {
                $this->sendResponse(['error' => 'User not authenticated'], 401);
                return;
            }
            
            $stmt = $this->db->prepare("
                SELECT id, business_name, business_type, projection_data, created_at, updated_at 
                FROM financial_projections 
                WHERE user_id = ? 
                ORDER BY created_at DESC
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $projections = [];
            while ($row = $result->fetch_assoc()) {
                $projections[] = [
                    'id' => $row['id'],
                    'businessName' => $row['business_name'],
                    'businessType' => $row['business_type'],
                    'projectionData' => json_decode($row['projection_data'], true),
                    'createdAt' => $row['created_at'],
                    'updatedAt' => $row['updated_at']
                ];
            }
            
            $this->sendResponse([
                'success' => true,
                'projections' => $projections
            ]);
            
        } catch (Exception $e) {
            $this->sendResponse(['error' => 'Failed to retrieve projections: ' . $e->getMessage()], 500);
        }
    }
    
    private function deleteProjection($data) {
        try {
            $userId = $this->getUserId();
            if (!$userId) {
                $this->sendResponse(['error' => 'User not authenticated'], 401);
                return;
            }
            
            $projectionId = $data['projectionId'] ?? null;
            if (!$projectionId) {
                $this->sendResponse(['error' => 'Projection ID required'], 400);
                return;
            }
            
            $stmt = $this->db->prepare("
                DELETE FROM financial_projections 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->bind_param("ii", $projectionId, $userId);
            
            if ($stmt->execute()) {
                $this->sendResponse([
                    'success' => true,
                    'message' => 'Projection deleted successfully'
                ]);
            } else {
                $this->sendResponse(['error' => 'Failed to delete projection'], 500);
            }
            
        } catch (Exception $e) {
            $this->sendResponse(['error' => 'Delete failed: ' . $e->getMessage()], 500);
        }
    }
    
    private function getBusinessTemplates() {
        $templates = [
            'restaurant' => [
                'name' => 'Restaurant/Food Service',
                'defaultRevenue' => 5000000,
                'defaultExpenses' => 3500000,
                'defaultGrowthRate' => 15,
                'description' => 'Full-service restaurant with dining and takeout'
            ],
            'retail' => [
                'name' => 'Retail/E-commerce',
                'defaultRevenue' => 3000000,
                'defaultExpenses' => 2000000,
                'defaultGrowthRate' => 20,
                'description' => 'Physical store or online retail business'
            ],
            'service' => [
                'name' => 'Service Business',
                'defaultRevenue' => 2000000,
                'defaultExpenses' => 1200000,
                'defaultGrowthRate' => 25,
                'description' => 'Professional services or consulting'
            ],
            'tourism' => [
                'name' => 'Tourism/Hospitality',
                'defaultRevenue' => 8000000,
                'defaultExpenses' => 5000000,
                'defaultGrowthRate' => 12,
                'description' => 'Tourism, accommodation, or hospitality services'
            ],
            'agriculture' => [
                'name' => 'Agriculture',
                'defaultRevenue' => 4000000,
                'defaultExpenses' => 2500000,
                'defaultGrowthRate' => 10,
                'description' => 'Farming, livestock, or agricultural processing'
            ],
            'technology' => [
                'name' => 'Technology/Software',
                'defaultRevenue' => 6000000,
                'defaultExpenses' => 3000000,
                'defaultGrowthRate' => 30,
                'description' => 'Software development or technology services'
            ]
        ];
        
        $this->sendResponse([
            'success' => true,
            'templates' => $templates
        ]);
    }
    
    private function getFinancialMetrics() {
        $metrics = [
            'taxRates' => [
                'rwanda' => [
                    'corporate' => 30,
                    'small_business' => 15,
                    'vat' => 18
                ]
            ],
            'inflationRates' => [
                'rwanda' => [
                    'current' => 5.2,
                    'average_5_year' => 4.8
                ]
            ],
            'discountRates' => [
                'conservative' => 8,
                'moderate' => 12,
                'aggressive' => 18
            ],
            'growthRates' => [
                'restaurant' => [10, 20],
                'retail' => [15, 30],
                'service' => [20, 40],
                'tourism' => [8, 15],
                'agriculture' => [5, 15],
                'technology' => [25, 50]
            ]
        ];
        
        $this->sendResponse([
            'success' => true,
            'metrics' => $metrics
        ]);
    }
    
    private function getUserId() {
        // Check for user session or token
        session_start();
        return $_SESSION['user_id'] ?? null;
    }
    
    private function sendResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data);
        exit();
    }
}

// Initialize and handle the request
$api = new FinancialProjectionsAPI();
$api->handleRequest();
?>
