<?php
// Suppress error reporting to prevent warnings from being sent before JSON
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering to prevent PHP warnings from being sent before JSON
ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['business_type']) || !isset($input['format'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

$businessType = $input['business_type'];
$format = strtolower($input['format']);
$businessData = $input['business_data'] ?? [];

// Validate format
$allowedFormats = ['pdf', 'word', 'excel', 'powerpoint'];
if (!in_array($format, $allowedFormats)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid format. Allowed: pdf, word, excel, powerpoint']);
    exit;
}

// Get business plan data with budget consideration
$businessPlanData = getBusinessPlanData($businessType, $businessData);

// Generate file based on format
switch ($format) {
    case 'pdf':
        $result = generatePDF($businessPlanData, $businessType);
        break;
    case 'word':
        $result = generateWord($businessPlanData, $businessType);
        break;
    case 'excel':
        $result = generateExcel($businessPlanData, $businessType);
        break;
    case 'powerpoint':
        $result = generatePowerPoint($businessPlanData, $businessType);
        break;
}

// Clean any output that might have been generated
ob_clean();

if ($result['success']) {
    echo json_encode($result);
} else {
    http_response_code(500);
    echo json_encode($result);
}

function getBusinessPlanData($businessType, $customData) {
    // Get user's budget constraint from session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $userBudget = $_SESSION['user_budget_range'] ?? '';
    
    // Get comprehensive business data for the specific business type
    $businessPlans = [
        'Mountain Hiking Tours' => [
            'title' => 'Mountain Hiking Tours Business Plan',
            'executive_summary' => 'A comprehensive guide to starting a mountain hiking tours business in Musanze, Rwanda.',
            'startup_investment' => '4,300,000-16,000,000 RWF',
            'revenue_potential' => '1,500,000-8,000,000 RWF per month',
            'break_even' => '6-10 months',
            'roi' => '250-400% by Year 3',
            'sections' => [
                'Business Overview',
                'Startup Investment',
                'Revenue Potential',
                'Prime Locations',
                'Target Customers',
                'Service Offerings',
                'Marketing Strategy',
                'Operational Tips',
                'Legal Requirements',
                'Success Factors',
                'Growth Opportunities',
                'Challenges & Solutions',
                'Financial Projections',
                'Business Plan Generation'
            ]
        ],
        'Volcano Trekking' => [
            'title' => 'Volcano Trekking Business Plan',
            'executive_summary' => 'A comprehensive guide to starting a volcano trekking business in Musanze, Rwanda.',
            'startup_investment' => '5,000,000-18,000,000 RWF',
            'revenue_potential' => '2,000,000-10,000,000 RWF per month',
            'break_even' => '6-10 months',
            'roi' => '300-500% by Year 3',
            'sections' => [
                'Business Overview',
                'Startup Investment',
                'Revenue Potential',
                'Prime Locations',
                'Target Customers',
                'Service Offerings',
                'Marketing Strategy',
                'Operational Tips',
                'Legal Requirements',
                'Success Factors',
                'Growth Opportunities',
                'Challenges & Solutions',
                'Financial Projections',
                'Business Plan Generation'
            ]
        ],
        'Local Restaurant' => [
            'title' => 'Local Restaurant Business Plan',
            'executive_summary' => 'A comprehensive guide to starting a local restaurant in Musanze, Rwanda.',
            'startup_investment' => '4,800,000-18,000,000 RWF',
            'revenue_potential' => '1,500,000-6,000,000 RWF per month',
            'break_even' => '6-12 months',
            'roi' => '200-350% by Year 3',
            'sections' => [
                'Business Overview',
                'Startup Investment',
                'Revenue Potential',
                'Prime Locations',
                'Target Customers',
                'Menu Categories',
                'Marketing Strategy',
                'Operational Tips',
                'Legal Requirements',
                'Success Factors',
                'Growth Opportunities',
                'Challenges & Solutions',
                'Financial Projections',
                'Business Plan Generation'
            ]
        ],
        'Eco-lodges' => [
            'title' => 'Eco-lodges Business Plan',
            'executive_summary' => 'A comprehensive guide to starting an eco-lodge business in Musanze, Rwanda.',
            'startup_investment' => '26,000,000-86,000,000 RWF',
            'revenue_potential' => '3,000,000-12,000,000 RWF per month',
            'break_even' => '12-18 months',
            'roi' => '150-300% by Year 3',
            'sections' => [
                'Business Overview',
                'Startup Investment',
                'Revenue Potential',
                'Prime Locations',
                'Target Customers',
                'Service Offerings',
                'Marketing Strategy',
                'Operational Tips',
                'Legal Requirements',
                'Success Factors',
                'Growth Opportunities',
                'Challenges & Solutions',
                'Financial Projections',
                'Business Plan Generation'
            ]
        ],
        'Food Processing' => [
            'title' => 'Food Processing Business Plan',
            'executive_summary' => 'A comprehensive guide to starting a food processing business in Musanze, Rwanda.',
            'startup_investment' => '26,000,000-81,000,000 RWF',
            'revenue_potential' => '1,400,000-6,300,000 RWF per month',
            'break_even' => '8-12 months',
            'roi' => '200-400% by Year 3',
            'sections' => [
                'Business Overview',
                'Startup Investment',
                'Revenue Potential',
                'Prime Locations',
                'Target Customers',
                'Product Categories',
                'Processing Methods',
                'Marketing Strategy',
                'Operational Tips',
                'Legal Requirements',
                'Success Factors',
                'Growth Opportunities',
                'Challenges & Solutions',
                'Financial Projections',
                'Business Plan Generation'
            ]
        ],
        'Coffee Processing' => [
            'title' => 'Coffee Processing Business Plan',
            'executive_summary' => 'A comprehensive guide to starting a coffee processing business in Musanze, Rwanda.',
            'startup_investment' => '33,000,000-93,000,000 RWF',
            'revenue_potential' => '2,800,000-13,000,000 RWF per month',
            'break_even' => '10-15 months',
            'roi' => '250-450% by Year 3',
            'sections' => [
                'Business Overview',
                'Startup Investment',
                'Revenue Potential',
                'Prime Locations',
                'Target Customers',
                'Product Categories',
                'Processing Methods',
                'Marketing Strategy',
                'Operational Tips',
                'Legal Requirements',
                'Success Factors',
                'Growth Opportunities',
                'Challenges & Solutions',
                'Financial Projections',
                'Business Plan Generation'
            ]
        ],
        'Local Transport' => [
            'title' => 'Local Transport Business Plan',
            'executive_summary' => 'A comprehensive guide to starting a local transport business in Musanze, Rwanda.',
            'startup_investment' => '1,000,000-6,000,000 RWF',
            'revenue_potential' => '1,500,000-4,500,000 RWF per month',
            'break_even' => '4-8 months',
            'roi' => '250-400% by Year 3',
            'sections' => [
                'Business Overview',
                'Startup Investment',
                'Revenue Potential',
                'Prime Locations',
                'Target Customers',
                'Service Offerings',
                'Marketing Strategy',
                'Operational Tips',
                'Legal Requirements',
                'Success Factors',
                'Growth Opportunities',
                'Challenges & Solutions',
                'Financial Projections',
                'Business Plan Generation'
            ]
        ],
        'Souvenir Shop' => [
            'title' => 'Souvenir Shop Business Plan',
            'executive_summary' => 'A comprehensive guide to starting a souvenir shop in Musanze, Rwanda.',
            'startup_investment' => '1,500,000-8,000,000 RWF',
            'revenue_potential' => '1,500,000-4,500,000 RWF per month',
            'break_even' => '6-10 months',
            'roi' => '200-350% by Year 3',
            'sections' => [
                'Business Overview',
                'Startup Investment',
                'Revenue Potential',
                'Prime Locations',
                'Target Customers',
                'Product Categories',
                'Marketing Strategy',
                'Operational Tips',
                'Legal Requirements',
                'Success Factors',
                'Growth Opportunities',
                'Seasonal Considerations',
                'Financial Projections',
                'Business Plan Generation'
            ]
        ],
        'Local Guide Services' => [
            'title' => 'Local Guide Services Business Plan',
            'executive_summary' => 'A comprehensive guide to starting a local guide services business in Musanze, Rwanda.',
            'startup_investment' => '4,300,000-18,000,000 RWF',
            'revenue_potential' => '2,000,000-12,000,000 RWF per month',
            'break_even' => '6-10 months',
            'roi' => '300-500% by Year 3',
            'sections' => [
                'Business Overview',
                'Startup Investment',
                'Revenue Potential',
                'Prime Locations',
                'Target Customers',
                'Service Offerings',
                'Marketing Strategy',
                'Operational Tips',
                'Legal Requirements',
                'Success Factors',
                'Growth Opportunities',
                'Challenges & Solutions',
                'Financial Projections',
                'Business Plan Generation'
            ]
        ],
        'Organic Farming' => [
            'title' => 'Organic Farming Business Plan',
            'executive_summary' => 'A comprehensive guide to starting an organic farming business in Musanze, Rwanda.',
            'startup_investment' => '10,000,000-38,000,000 RWF',
            'revenue_potential' => '4,000,000-15,500,000 RWF per month',
            'break_even' => '8-12 months',
            'roi' => '200-400% by Year 3',
            'sections' => [
                'Business Overview',
                'Startup Investment',
                'Revenue Potential',
                'Prime Locations',
                'Target Customers',
                'Product Categories',
                'Farming Methods',
                'Marketing Strategy',
                'Operational Tips',
                'Legal Requirements',
                'Success Factors',
                'Growth Opportunities',
                'Challenges & Solutions',
                'Financial Projections',
                'Business Plan Generation'
            ]
        ],
        'Guesthouse' => [
            'title' => 'Guesthouse Business Plan',
            'executive_summary' => 'A comprehensive guide to starting a guesthouse business in Musanze, Rwanda.',
            'startup_investment' => '5,000,000-30,000,000 RWF',
            'revenue_potential' => '2,000,000-6,000,000 RWF per month',
            'break_even' => '8-12 months',
            'roi' => '200-350% by Year 3',
            'sections' => [
                'Business Overview',
                'Startup Investment',
                'Revenue Potential',
                'Prime Locations',
                'Target Customers',
                'Service Offerings',
                'Marketing Strategy',
                'Operational Tips',
                'Legal Requirements',
                'Success Factors',
                'Growth Opportunities',
                'Challenges & Solutions',
                'Financial Projections',
                'Business Plan Generation'
            ]
        ],
        'Internet Cafe' => [
            'title' => 'Internet Cafe Business Plan',
            'executive_summary' => 'A comprehensive guide to starting an internet cafe business in Musanze, Rwanda.',
            'startup_investment' => '20,000,000-38,000,000 RWF',
            'revenue_potential' => '1,500,000-6,000,000 RWF per month',
            'break_even' => '6-10 months',
            'roi' => '200-350% by Year 3',
            'sections' => [
                'Business Overview',
                'Startup Investment',
                'Revenue Potential',
                'Prime Locations',
                'Target Customers',
                'Service Offerings',
                'Marketing Strategy',
                'Operational Tips',
                'Legal Requirements',
                'Success Factors',
                'Growth Opportunities',
                'Challenges & Solutions',
                'Financial Projections',
                'Business Plan Generation'
            ]
        ]
    ];

    $basePlan = $businessPlans[$businessType] ?? $businessPlans['Mountain Hiking Tours'];
    
    // Adjust startup investment based on user's budget constraint
    if (!empty($userBudget)) {
        $basePlan['startup_investment'] = adjustStartupInvestmentForBudget($basePlan['startup_investment'], $userBudget);
    }
    
    // Merge with custom data if provided
    if (!empty($customData)) {
        $basePlan = array_merge($basePlan, $customData);
    }
    
    return $basePlan;
}

function generatePDF($data, $businessType) {
    // For now, return a simple HTML version that can be printed to PDF
    $html = generateHTML($data, $businessType);
    
    return [
        'success' => true,
        'format' => 'pdf',
        'html' => $html,
        'filename' => sanitizeFilename($data['title']) . '.html',
        'message' => 'Business plan generated successfully. Use browser print to PDF function.'
    ];
}

function generateWord($data, $businessType) {
    // Generate HTML that can be opened in Word
    $html = generateHTML($data, $businessType);
    
    return [
        'success' => true,
        'format' => 'word',
        'html' => $html,
        'filename' => sanitizeFilename($data['title']) . '.html',
        'message' => 'Business plan generated successfully. Open in Word and save as .docx'
    ];
}

function generateExcel($data, $businessType) {
    // Generate CSV data for Excel
    $csvData = generateCSV($data, $businessType);
    
    return [
        'success' => true,
        'format' => 'excel',
        'csv' => $csvData,
        'filename' => sanitizeFilename($data['title']) . '.csv',
        'message' => 'Business plan data generated successfully for Excel import.'
    ];
}

function generatePowerPoint($data, $businessType) {
    // Generate HTML that can be used as a presentation
    $html = generatePresentationHTML($data, $businessType);
    
    return [
        'success' => true,
        'format' => 'powerpoint',
        'html' => $html,
        'filename' => sanitizeFilename($data['title']) . '_presentation.html',
        'message' => 'Business plan presentation generated successfully.'
    ];
}

function generateHTML($data, $businessType) {
    // Get user's budget for context
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $userBudget = $_SESSION['user_budget_range'] ?? '';
    
    // Generate AI content for auto-filling
    $aiContent = generateAIBusinessPlanContent($businessType, $data, $userBudget);
    
    $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($data['title']) . '</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 40px; color: #333; }
        .header { text-align: center; border-bottom: 3px solid #2c3e50; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { color: #2c3e50; margin: 0; font-size: 28px; }
        .header p { color: #7f8c8d; margin: 10px 0 0 0; font-size: 16px; }
        .section { margin-bottom: 30px; }
        .section h2 { color: #34495e; border-left: 4px solid #3498db; padding-left: 15px; margin-bottom: 15px; }
        .section h3 { color: #2c3e50; margin-top: 20px; margin-bottom: 10px; }
        .highlight { background-color: #ecf0f1; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .financial { background-color: #e8f5e8; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .placeholder { background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; border-radius: 5px; margin: 10px 0; font-style: italic; color: #856404; }
        .budget-note { background-color: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 15px 0; color: #0c5460; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        table th, table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        table th { background-color: #f8f9fa; font-weight: bold; }
        ul { margin: 10px 0; }
        li { margin: 5px 0; }
        .footer { text-align: center; margin-top: 50px; padding-top: 20px; border-top: 1px solid #bdc3c7; color: #7f8c8d; }
        @media print { body { margin: 20px; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>🧭 Business Plan Template</h1>
        <p>Musanze, Rwanda</p>
        <p>Generated on ' . date('F j, Y') . '</p>
    </div>';

    // Add budget note if available
    if (!empty($userBudget)) {
        $html .= '<div class="budget-note">
            <strong>📊 Budget Constraint:</strong> This business plan has been adjusted to fit within your budget of ' . htmlspecialchars($userBudget) . '.
        </div>';
    }

    $html .= '
    <div class="section">
        <h2>1. Executive Summary</h2>
        <div class="highlight">
            <strong>Business Name:</strong> ' . htmlspecialchars($aiContent['business_name']) . '<br>
            <strong>Business Location:</strong> Musanze, Rwanda<br>
            <strong>Mission Statement:</strong> ' . htmlspecialchars($aiContent['mission_statement']) . '<br>
            <strong>Business Model:</strong> ' . htmlspecialchars($aiContent['business_model']) . '<br>
            <strong>Products/Services:</strong> ' . htmlspecialchars($aiContent['products_services']) . '<br>
            <strong>Market Opportunity:</strong> ' . htmlspecialchars($aiContent['market_opportunity']) . '<br>
            <strong>Funding Required:</strong> ' . htmlspecialchars($data['startup_investment']) . '<br>
            <strong>Projected ROI or Profit:</strong> ' . htmlspecialchars($data['roi']) . '
        </div>
        <p><strong>📋 Executive Summary:</strong></p>
        <p>' . htmlspecialchars($aiContent['business_name']) . ' is a ' . htmlspecialchars($businessType) . ' that provides ' . htmlspecialchars($aiContent['products_services']) . ' to ' . htmlspecialchars($aiContent['target_market']) . '. We seek ' . htmlspecialchars($data['startup_investment']) . ' investment to cover startup and growth costs, expecting to achieve ' . htmlspecialchars($data['revenue_potential']) . ' within ' . htmlspecialchars($data['break_even']) . '.</p>
    </div>

    <div class="section">
        <h2>2. Business Description</h2>
        <div class="highlight">
            <strong>Industry:</strong> ' . htmlspecialchars($aiContent['industry']) . '<br>
            <strong>Problem Statement:</strong> ' . htmlspecialchars($aiContent['problem_statement']) . '<br>
            <strong>Solution:</strong> ' . htmlspecialchars($aiContent['solution']) . '<br>
            <strong>Vision Statement:</strong> ' . htmlspecialchars($aiContent['vision_statement']) . '<br>
            <strong>Mission Statement:</strong> ' . htmlspecialchars($aiContent['mission_statement']) . '
        </div>
        <p><strong>Objectives:</strong></p>
        <ul>
            <li>' . htmlspecialchars($aiContent['objective_1']) . '</li>
            <li>' . htmlspecialchars($aiContent['objective_2']) . '</li>
            <li>' . htmlspecialchars($aiContent['objective_3']) . '</li>
        </ul>
    </div>

    <div class="section">
        <h2>3. Products or Services</h2>
        <div class="highlight">
            <strong>Product/Service Name:</strong> ' . htmlspecialchars($aiContent['product_name']) . '<br>
            <strong>Description:</strong> ' . htmlspecialchars($aiContent['product_description']) . '<br>
            <strong>Unique Value Proposition (UVP):</strong> ' . htmlspecialchars($aiContent['unique_value_proposition']) . '<br>
            <strong>Pricing Model:</strong> ' . htmlspecialchars($aiContent['pricing_model']) . '<br>
            <strong>Future Development:</strong> ' . htmlspecialchars($aiContent['future_development']) . '
        </div>
    </div>

    <div class="section">
        <h2>4. Market Analysis</h2>
        <div class="highlight">
            <strong>Target Market:</strong> ' . htmlspecialchars($aiContent['target_market']) . '<br>
            <strong>Market Size:</strong> ' . htmlspecialchars($aiContent['market_size']) . '
        </div>
        <p><strong>Customer Segments:</strong></p>
        <ul>
            <li>' . htmlspecialchars($aiContent['customer_segment_1']) . '</li>
            <li>' . htmlspecialchars($aiContent['customer_segment_2']) . '</li>
        </ul>
        
        <p><strong>Competitor Analysis:</strong></p>
        <table>
            <tr>
                <th>Competitor</th>
                <th>Strengths</th>
                <th>Weaknesses</th>
            </tr>
            <tr>
                <td>' . htmlspecialchars($aiContent['competitor_1_name']) . '</td>
                <td>' . htmlspecialchars($aiContent['competitor_1_strength']) . '</td>
                <td>' . htmlspecialchars($aiContent['competitor_1_weakness']) . '</td>
            </tr>
            <tr>
                <td>' . htmlspecialchars($aiContent['competitor_2_name']) . '</td>
                <td>' . htmlspecialchars($aiContent['competitor_2_strength']) . '</td>
                <td>' . htmlspecialchars($aiContent['competitor_2_weakness']) . '</td>
            </tr>
        </table>
        
        <div class="highlight">
            <strong>Market Trends:</strong> ' . htmlspecialchars($aiContent['market_trends']) . '<br>
            <strong>Competitive Advantage:</strong> ' . htmlspecialchars($aiContent['competitive_advantage']) . '
        </div>
    </div>

    <div class="section">
        <h2>5. Marketing and Sales Strategy</h2>
        <div class="highlight">
            <strong>Brand Positioning:</strong> ' . htmlspecialchars($aiContent['brand_positioning']) . '<br>
            <strong>Promotion Channels:</strong> ' . htmlspecialchars($aiContent['promotion_channels']) . '<br>
            <strong>Sales Approach:</strong> ' . htmlspecialchars($aiContent['sales_approach']) . '<br>
            <strong>Customer Retention:</strong> ' . htmlspecialchars($aiContent['customer_retention']) . '<br>
            <strong>Pricing Strategy:</strong> ' . htmlspecialchars($aiContent['pricing_strategy']) . '
        </div>
        <p><strong>📈 Marketing Strategy:</strong></p>
        <p>' . htmlspecialchars($aiContent['marketing_strategy']) . '</p>
    </div>

    <div class="section">
        <h2>6. Operational Plan</h2>
        <div class="highlight">
            <strong>Business Location:</strong> Musanze, Rwanda<br>
            <strong>Facilities and Equipment:</strong> ' . htmlspecialchars($aiContent['facilities_equipment']) . '<br>
            <strong>Production/Service Process:</strong> ' . htmlspecialchars($aiContent['production_process']) . '<br>
            <strong>Suppliers/Partners:</strong> ' . htmlspecialchars($aiContent['suppliers_partners']) . '<br>
            <strong>Operational Timeline:</strong> ' . htmlspecialchars($aiContent['operational_timeline']) . '
        </div>
    </div>

    <div class="section">
        <h2>7. Management and Organization</h2>
        <div class="highlight">
            <strong>Organizational Structure:</strong> ' . htmlspecialchars($aiContent['organizational_structure']) . '
        </div>
        <p><strong>Key Team Members:</strong></p>
        <table>
            <tr>
                <th>Name</th>
                <th>Position</th>
                <th>Role/Responsibility</th>
                <th>Experience</th>
            </tr>
            <tr>
                <td>' . htmlspecialchars($aiContent['team_member_1_name']) . '</td>
                <td>' . htmlspecialchars($aiContent['team_member_1_position']) . '</td>
                <td>' . htmlspecialchars($aiContent['team_member_1_role']) . '</td>
                <td>' . htmlspecialchars($aiContent['team_member_1_experience']) . '</td>
            </tr>
            <tr>
                <td>' . htmlspecialchars($aiContent['team_member_2_name']) . '</td>
                <td>' . htmlspecialchars($aiContent['team_member_2_position']) . '</td>
                <td>' . htmlspecialchars($aiContent['team_member_2_role']) . '</td>
                <td>' . htmlspecialchars($aiContent['team_member_2_experience']) . '</td>
            </tr>
        </table>
        <div class="highlight">
            <strong>Advisors or Mentors:</strong> ' . htmlspecialchars($aiContent['advisors_mentors']) . '<br>
            <strong>Hiring Plan:</strong> ' . htmlspecialchars($aiContent['hiring_plan']) . '
        </div>
    </div>

    <div class="section">
        <h2>8. Financial Plan</h2>
        <p><strong>Startup Costs:</strong></p>
        <table>
            <tr>
                <th>Item</th>
                <th>Description</th>
                <th>Cost (RWF)</th>
            </tr>
            <tr>
                <td>[Item 1]</td>
                <td>[Details]</td>
                <td>[Amount]</td>
            </tr>
            <tr>
                <td>[Item 2]</td>
                <td>[Details]</td>
                <td>[Amount]</td>
            </tr>
            <tr style="font-weight: bold; background-color: #f8f9fa;">
                <td colspan="2">Total Startup Cost</td>
                <td>' . htmlspecialchars($data['startup_investment']) . '</td>
            </tr>
        </table>
        
        <div class="highlight">
            <strong>Investment Required:</strong> ' . htmlspecialchars($data['startup_investment']) . '<br>
            <strong>Use of Funds:</strong><br>
            • ' . htmlspecialchars($aiContent['expense_1']) . ' – ' . htmlspecialchars($aiContent['expense_1_amount']) . '<br>
            • ' . htmlspecialchars($aiContent['expense_2']) . ' – ' . htmlspecialchars($aiContent['expense_2_amount']) . '<br>
            • ' . htmlspecialchars($aiContent['working_capital']) . ' – ' . htmlspecialchars($aiContent['working_capital_amount']) . '
        </div>
        
        <p><strong>Financial Projections (3–5 years):</strong></p>
        <ul>
            <li><strong>Year 1 Revenue:</strong> ' . htmlspecialchars($aiContent['year_1_revenue']) . '</li>
            <li><strong>Year 2 Revenue:</strong> ' . htmlspecialchars($aiContent['year_2_revenue']) . '</li>
            <li><strong>Year 3 Revenue:</strong> ' . htmlspecialchars($aiContent['year_3_revenue']) . '</li>
            <li><strong>Break-even Point:</strong> ' . htmlspecialchars($data['break_even']) . '</li>
        </ul>
        <p><strong>📍 Note:</strong> It\'s fine if investment > startup cost — just explain how the extra will support growth, operations, or reserves.</p>
    </div>

    <div class="section">
        <h2>9. Risk Analysis</h2>
        <p><strong>Key Risks:</strong></p>
        <ul>
            <li><strong>' . htmlspecialchars($aiContent['risk_1_name']) . ':</strong> ' . htmlspecialchars($aiContent['risk_1_description']) . '</li>
            <li><strong>' . htmlspecialchars($aiContent['risk_2_name']) . ':</strong> ' . htmlspecialchars($aiContent['risk_2_description']) . '</li>
            <li><strong>' . htmlspecialchars($aiContent['risk_3_name']) . ':</strong> ' . htmlspecialchars($aiContent['risk_3_description']) . '</li>
        </ul>
        <p><strong>Mitigation Strategies:</strong></p>
        <ul>
            <li>' . htmlspecialchars($aiContent['mitigation_strategy_1']) . '</li>
            <li>' . htmlspecialchars($aiContent['mitigation_strategy_2']) . '</li>
            <li>' . htmlspecialchars($aiContent['mitigation_strategy_3']) . '</li>
        </ul>
    </div>

    <div class="section">
        <h2>10. Appendices</h2>
        <p><strong>Include:</strong></p>
        <ul>
            <li>Founders\' CVs or bios</li>
            <li>Product mockups/screenshots</li>
            <li>Legal documents or registrations</li>
            <li>Partnership agreements</li>
            <li>Market research data</li>
            <li>Financial statements (if any)</li>
        </ul>
    </div>

    <div class="footer">
        <p>Generated by InnoStart Business Plan Generator</p>
        <p>For more information, visit our platform</p>
    </div>
</body>
</html>';

    return $html;
}

function generatePresentationHTML($data, $businessType) {
    $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($data['title']) . ' - Presentation</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f8f9fa; }
        .slide { width: 100%; height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; padding: 40px; box-sizing: border-box; }
        .slide h1 { font-size: 48px; color: #2c3e50; margin-bottom: 20px; }
        .slide h2 { font-size: 36px; color: #34495e; margin-bottom: 30px; }
        .slide h3 { font-size: 24px; color: #2c3e50; margin-bottom: 20px; }
        .slide p { font-size: 20px; color: #7f8c8d; max-width: 800px; line-height: 1.6; }
        .slide ul { font-size: 18px; color: #34495e; text-align: left; max-width: 600px; }
        .slide li { margin: 10px 0; }
        .highlight { background: #3498db; color: white; padding: 20px; border-radius: 10px; margin: 20px 0; }
        .financial { background: #27ae60; color: white; padding: 20px; border-radius: 10px; margin: 20px 0; }
        .navigation { position: fixed; bottom: 20px; right: 20px; z-index: 1000; }
        .nav-btn { background: #3498db; color: white; border: none; padding: 10px 20px; margin: 0 5px; border-radius: 5px; cursor: pointer; }
        .nav-btn:hover { background: #2980b9; }
    </style>
</head>
<body>
    <div class="slide">
        <h1>' . htmlspecialchars($data['title']) . '</h1>
        <p>Musanze, Rwanda</p>
        <p>Generated on ' . date('F j, Y') . '</p>
    </div>

    <div class="slide">
        <h2>Executive Summary</h2>
        <p>' . htmlspecialchars($data['executive_summary']) . '</p>
    </div>

    <div class="slide">
        <h2>Investment Overview</h2>
        <div class="highlight">
            <h3>Startup Investment</h3>
            <p>' . htmlspecialchars($data['startup_investment']) . '</p>
        </div>
        <div class="highlight">
            <h3>Revenue Potential</h3>
            <p>' . htmlspecialchars($data['revenue_potential']) . '</p>
        </div>
    </div>

    <div class="slide">
        <h2>Financial Projections</h2>
        <div class="financial">
            <h3>5-Year Plan</h3>
            <ul>
                <li>Year 1: Startup Phase</li>
                <li>Year 2: Growth Phase</li>
                <li>Year 3: Expansion Phase</li>
                <li>Year 4: Maturity Phase</li>
                <li>Year 5: Optimization Phase</li>
            </ul>
            <p>Break-even: ' . htmlspecialchars($data['break_even']) . '</p>
            <p>ROI: ' . htmlspecialchars($data['roi']) . '</p>
        </div>
    </div>

    <div class="slide">
        <h2>Business Plan Sections</h2>
        <ul>';
    
    foreach ($data['sections'] as $section) {
        $html .= '<li>' . htmlspecialchars($section) . '</li>';
    }
    
    $html .= '</ul>
    </div>

    <div class="slide">
        <h2>Next Steps</h2>
        <ol>
            <li>Review business plan</li>
            <li>Conduct market research</li>
            <li>Secure funding</li>
            <li>Obtain permits</li>
            <li>Launch business</li>
        </ol>
    </div>

    <div class="navigation">
        <button class="nav-btn" onclick="window.print()">Print</button>
        <button class="nav-btn" onclick="window.close()">Close</button>
    </div>
</body>
</html>';

    return $html;
}

function generateCSV($data, $businessType) {
    $csv = "Business Plan Data\n";
    $csv .= "Business Type," . $businessType . "\n";
    $csv .= "Title," . $data['title'] . "\n";
    $csv .= "Executive Summary," . $data['executive_summary'] . "\n";
    $csv .= "Startup Investment," . $data['startup_investment'] . "\n";
    $csv .= "Revenue Potential," . $data['revenue_potential'] . "\n";
    $csv .= "Break Even," . $data['break_even'] . "\n";
    $csv .= "ROI," . $data['roi'] . "\n";
    $csv .= "Generated Date," . date('Y-m-d') . "\n\n";
    
    $csv .= "Business Plan Sections\n";
    foreach ($data['sections'] as $index => $section) {
        $csv .= ($index + 1) . "," . $section . "\n";
    }
    
    return $csv;
}

function sanitizeFilename($filename) {
    // Remove or replace invalid filename characters
    $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
    $filename = preg_replace('/_+/', '_', $filename);
    $filename = trim($filename, '_');
    return $filename;
}

// Helper function to adjust startup investment based on user's budget
function adjustStartupInvestmentForBudget($originalInvestment, $userBudget) {
    // Extract budget range
    $budgetRange = extractBudgetRangeFromString($userBudget);
    if (!$budgetRange) {
        return $originalInvestment;
    }
    
    // Parse original investment range
    $investmentRange = parseInvestmentRange($originalInvestment);
    if (!$investmentRange) {
        return $originalInvestment;
    }
    
    // If original investment is within budget, return as is
    if ($investmentRange['max'] <= $budgetRange['max']) {
        return $originalInvestment;
    }
    
    // Scale down the investment to fit within budget
    $scaledMin = min($investmentRange['min'], $budgetRange['max'] * 0.7);
    $scaledMax = min($investmentRange['max'], $budgetRange['max']);
    
    // Format the scaled investment
    return formatInvestmentRange($scaledMin, $scaledMax);
}

// Helper function to extract budget range from user budget string
function extractBudgetRangeFromString($userBudget) {
    // Handle different budget formats
    if (preg_match('/(\d+(?:,\d{3})*)\s*-\s*(\d+(?:,\d{3})*)\s*RWF/i', $userBudget, $matches)) {
        return [
            'min' => (int)str_replace(',', '', $matches[1]),
            'max' => (int)str_replace(',', '', $matches[2])
        ];
    }
    
    // Handle single budget amount
    if (preg_match('/(\d+(?:,\d{3})*)\s*RWF/i', $userBudget, $matches)) {
        $amount = (int)str_replace(',', '', $matches[1]);
        return [
            'min' => $amount * 0.8, // 80% of stated amount
            'max' => $amount * 1.2  // 120% of stated amount
        ];
    }
    
    return null;
}

// Helper function to parse investment range from string
function parseInvestmentRange($investmentString) {
    if (preg_match('/(\d+(?:,\d{3})*)\s*-\s*(\d+(?:,\d{3})*)\s*RWF/i', $investmentString, $matches)) {
        return [
            'min' => (int)str_replace(',', '', $matches[1]),
            'max' => (int)str_replace(',', '', $matches[2])
        ];
    }
    
    return null;
}

// Helper function to format investment range
function formatInvestmentRange($min, $max) {
    if ($min >= 1000000) {
        $minFormatted = number_format($min / 1000000, 1) . 'M';
    } else {
        $minFormatted = number_format($min);
    }
    
    if ($max >= 1000000) {
        $maxFormatted = number_format($max / 1000000, 1) . 'M';
    } else {
        $maxFormatted = number_format($max);
    }
    
    return $minFormatted . '-' . $maxFormatted . ' RWF';
}

// Function to generate AI business plan content
function generateAIBusinessPlanContent($businessType, $data, $userBudget) {
    // Get AI response for comprehensive business plan
    $aiResponse = getPythonAIResponse($businessType . " comprehensive business plan analysis with detailed sections for executive summary, business description, products/services, market analysis, marketing strategy, operational plan, management, financial plan, and risk analysis");
    
    // Default content structure
    $content = [
        'business_name' => ucwords($businessType) . ' Enterprise',
        'mission_statement' => 'To provide exceptional ' . strtolower($businessType) . ' services that create value for our customers and contribute to the economic development of Musanze, Rwanda.',
        'business_model' => 'Revenue generation through ' . strtolower($businessType) . ' services with focus on quality, customer satisfaction, and sustainable growth.',
        'products_services' => 'Comprehensive ' . strtolower($businessType) . ' solutions tailored to meet the needs of our target market in Musanze and surrounding areas.',
        'market_opportunity' => 'Growing demand for ' . strtolower($businessType) . ' services in Musanze due to increasing population, tourism, and economic development.',
        'target_market' => 'Local residents, tourists, and businesses in Musanze and surrounding areas seeking quality ' . strtolower($businessType) . ' services.',
        'industry' => 'Service Industry - ' . $businessType,
        'problem_statement' => 'Limited access to high-quality, professional ' . strtolower($businessType) . ' services in the Musanze region.',
        'solution' => 'Providing professional, reliable, and affordable ' . strtolower($businessType) . ' services with excellent customer service.',
        'vision_statement' => 'To become the leading ' . strtolower($businessType) . ' service provider in Musanze, known for excellence and innovation.',
        'objective_1' => 'Establish a strong market presence in Musanze within the first year',
        'objective_2' => 'Achieve break-even point within ' . $data['break_even'],
        'objective_3' => 'Expand services to neighboring regions within 3 years',
        'product_name' => $businessType . ' Services',
        'product_description' => 'Professional ' . strtolower($businessType) . ' services designed to meet the specific needs of our customers.',
        'unique_value_proposition' => 'Local expertise, personalized service, competitive pricing, and commitment to customer satisfaction.',
        'pricing_model' => 'Service-based pricing with flexible packages to accommodate different customer needs.',
        'future_development' => 'Expansion of service offerings, technology integration, and potential franchising opportunities.',
        'market_size' => 'Growing market with significant potential for ' . strtolower($businessType) . ' services in Musanze region.',
        'customer_segment_1' => 'Local residents seeking quality ' . strtolower($businessType) . ' services',
        'customer_segment_2' => 'Tourists and visitors requiring ' . strtolower($businessType) . ' assistance',
        'competitor_1_name' => 'Local Competitor A',
        'competitor_1_strength' => 'Established local presence',
        'competitor_1_weakness' => 'Limited service range',
        'competitor_2_name' => 'Regional Competitor B',
        'competitor_2_strength' => 'Brand recognition',
        'competitor_2_weakness' => 'Higher pricing',
        'market_trends' => 'Increasing demand for professional services, growing tourism industry, and economic development in Musanze.',
        'competitive_advantage' => 'Local knowledge, personalized service, competitive pricing, and strong customer relationships.',
        'brand_positioning' => 'Professional, reliable, and customer-focused ' . strtolower($businessType) . ' service provider.',
        'promotion_channels' => 'Social media, local advertising, word-of-mouth, partnerships with local businesses.',
        'sales_approach' => 'Direct sales, online presence, referral programs, and strategic partnerships.',
        'customer_retention' => 'Excellent customer service, loyalty programs, and regular follow-up.',
        'pricing_strategy' => 'Competitive pricing with value-added services to differentiate from competitors.',
        'marketing_strategy' => 'Focus on building local reputation through quality service delivery and customer satisfaction.',
        'facilities_equipment' => 'Professional equipment and facilities required for ' . strtolower($businessType) . ' operations.',
        'production_process' => 'Systematic approach to service delivery ensuring quality and efficiency.',
        'suppliers_partners' => 'Reliable suppliers and strategic partners to support business operations.',
        'operational_timeline' => 'Phased approach to business launch and growth over 12-18 months.',
        'organizational_structure' => 'Flat organizational structure with clear roles and responsibilities.',
        'team_member_1_name' => 'Business Owner/Manager',
        'team_member_1_position' => 'CEO/Manager',
        'team_member_1_role' => 'Overall business management and strategic planning',
        'team_member_1_experience' => 'Relevant experience in business management and ' . strtolower($businessType) . ' industry',
        'team_member_2_name' => 'Operations Manager',
        'team_member_2_position' => 'Operations Manager',
        'team_member_2_role' => 'Day-to-day operations and service delivery',
        'team_member_2_experience' => 'Experience in operations management and customer service',
        'advisors_mentors' => 'Local business mentors and industry advisors to provide guidance and support.',
        'hiring_plan' => 'Gradual hiring based on business growth and service demand.',
        'expense_1' => 'Equipment and Facilities',
        'expense_1_amount' => '40% of total investment',
        'expense_2' => 'Marketing and Promotion',
        'expense_2_amount' => '20% of total investment',
        'working_capital' => 'Working Capital and Reserves',
        'working_capital_amount' => '40% of total investment',
        'year_1_revenue' => 'Initial revenue generation phase',
        'year_2_revenue' => 'Growth and expansion phase',
        'year_3_revenue' => 'Maturity and optimization phase',
        'risk_1_name' => 'Market Competition',
        'risk_1_description' => 'Intense competition from established players in the market',
        'risk_2_name' => 'Economic Factors',
        'risk_2_description' => 'Economic downturns affecting customer spending',
        'risk_3_name' => 'Operational Challenges',
        'risk_3_description' => 'Challenges in maintaining service quality and customer satisfaction',
        'mitigation_strategy_1' => 'Focus on differentiation through superior service quality and customer experience',
        'mitigation_strategy_2' => 'Diversify customer base and maintain flexible pricing strategies',
        'mitigation_strategy_3' => 'Implement robust quality control systems and continuous staff training'
    ];
    
    // If AI response is available, try to extract more specific content
    if ($aiResponse) {
        // Parse AI response and update content where possible
        $content = parseAIResponseForBusinessPlan($aiResponse, $content, $businessType);
    }
    
    return $content;
}

// Function to parse AI response and extract business plan content
function parseAIResponseForBusinessPlan($aiResponse, $defaultContent, $businessType) {
    // This function would parse the AI response and extract relevant information
    // For now, we'll use the default content with some AI-enhanced elements
    
    // Extract key information from AI response if available
    if (strpos($aiResponse, 'mission') !== false) {
        $defaultContent['mission_statement'] = 'AI-enhanced mission statement for ' . $businessType . ' business.';
    }
    
    if (strpos($aiResponse, 'market') !== false) {
        $defaultContent['market_opportunity'] = 'AI-analyzed market opportunity for ' . $businessType . ' in Musanze region.';
    }
    
    return $defaultContent;
}

// Function to get Python AI response (placeholder - would integrate with your AI system)
function getPythonAIResponse($prompt) {
    // This would call your Python AI system
    // For now, return null to use default content
    return null;
}
?>
