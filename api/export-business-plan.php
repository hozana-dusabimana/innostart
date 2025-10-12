<?php
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

// Get business plan data
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

if ($result['success']) {
    echo json_encode($result);
} else {
    http_response_code(500);
    echo json_encode($result);
}

function getBusinessPlanData($businessType, $customData) {
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
        ul { margin: 10px 0; }
        li { margin: 5px 0; }
        .footer { text-align: center; margin-top: 50px; padding-top: 20px; border-top: 1px solid #bdc3c7; color: #7f8c8d; }
        @media print { body { margin: 20px; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>' . htmlspecialchars($data['title']) . '</h1>
        <p>Musanze, Rwanda Business Plan</p>
        <p>Generated on ' . date('F j, Y') . '</p>
    </div>

    <div class="section">
        <h2>Executive Summary</h2>
        <p>' . htmlspecialchars($data['executive_summary']) . '</p>
    </div>

    <div class="section">
        <h2>Key Investment Information</h2>
        <div class="highlight">
            <h3>Startup Investment</h3>
            <p><strong>' . htmlspecialchars($data['startup_investment']) . '</strong></p>
            
            <h3>Revenue Potential</h3>
            <p><strong>' . htmlspecialchars($data['revenue_potential']) . '</strong></p>
            
            <h3>Break-even Point</h3>
            <p><strong>' . htmlspecialchars($data['break_even']) . '</strong></p>
            
            <h3>Expected ROI</h3>
            <p><strong>' . htmlspecialchars($data['roi']) . '</strong></p>
        </div>
    </div>

    <div class="section">
        <h2>Business Plan Sections</h2>
        <ul>';
    
    foreach ($data['sections'] as $section) {
        $html .= '<li>' . htmlspecialchars($section) . '</li>';
    }
    
    $html .= '</ul>
    </div>

    <div class="section">
        <h2>Financial Projections (5-Year Plan)</h2>
        <div class="financial">
            <h3>Revenue Forecast</h3>
            <ul>
                <li><strong>Year 1:</strong> Startup phase - Initial revenue generation</li>
                <li><strong>Year 2:</strong> Growth phase - Market expansion</li>
                <li><strong>Year 3:</strong> Expansion phase - Scale operations</li>
                <li><strong>Year 4:</strong> Maturity phase - Optimize efficiency</li>
                <li><strong>Year 5:</strong> Optimization phase - Maximize profits</li>
            </ul>
        </div>
    </div>

    <div class="section">
        <h2>Next Steps</h2>
        <ol>
            <li>Review this business plan thoroughly</li>
            <li>Conduct market research in Musanze</li>
            <li>Secure funding and permits</li>
            <li>Develop detailed operational procedures</li>
            <li>Create marketing and sales strategies</li>
            <li>Establish partnerships and suppliers</li>
            <li>Launch your business</li>
        </ol>
    </div>

    <div class="footer">
        <p>This business plan was generated by InnoStart AI Assistant</p>
        <p>For detailed guidance, visit: <a href="http://localhost/innostart">http://localhost/innostart</a></p>
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
?>
