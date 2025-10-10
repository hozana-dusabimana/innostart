<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit();
}

$businessName = $input['businessName'] ?? 'My Business';
$businessType = $input['businessType'] ?? 'General';
$targetMarket = $input['targetMarket'] ?? '';
$missionStatement = $input['missionStatement'] ?? '';
$competitiveAdvantage = $input['competitiveAdvantage'] ?? '';
$fundingNeeds = $input['fundingNeeds'] ?? '';

$html = generateBusinessPlanHTML($businessName, $businessType, $targetMarket, $missionStatement, $competitiveAdvantage, $fundingNeeds);

function generateBusinessPlanHTML($businessName, $businessType, $targetMarket, $missionStatement, $competitiveAdvantage, $fundingNeeds) {
    return "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Business Plan - $businessName</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; color: #333; }
        h1 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; text-align: center; }
        h2 { color: #34495e; margin-top: 30px; border-left: 4px solid #3498db; padding-left: 15px; }
        h3 { color: #7f8c8d; margin-top: 20px; }
        .header { text-align: center; margin-bottom: 40px; }
        .section { margin-bottom: 30px; }
        .highlight { background-color: #f8f9fa; padding: 15px; border-left: 4px solid #3498db; margin: 15px 0; }
        ul { margin: 10px 0; padding-left: 25px; }
        li { margin: 5px 0; }
        .footer { margin-top: 50px; text-align: center; color: #7f8c8d; font-style: italic; }
        .print-instructions { 
            background: #e3f2fd; padding: 20px; margin: 20px 0; border-radius: 8px; 
            border-left: 4px solid #2196f3; text-align: center;
        }
        .print-instructions h2 { border: none; padding: 0; margin: 0 0 15px 0; color: #1976d2; }
        .steps { display: flex; justify-content: space-around; margin: 20px 0; }
        .step { text-align: center; flex: 1; margin: 0 10px; }
        .step-number { 
            background: #2196f3; color: white; border-radius: 50%; width: 30px; height: 30px; 
            display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-weight: bold;
        }
        @media print { .print-instructions { display: none; } }
    </style>
</head>
<body>
    <div class='print-instructions'>
        <h2>📄 Convert to PDF - Easy Steps</h2>
        <div class='steps'>
            <div class='step'>
                <div class='step-number'>1</div>
                <p><strong>Press Ctrl+P</strong><br>(Windows) or <strong>Cmd+P</strong> (Mac)</p>
            </div>
            <div class='step'>
                <div class='step-number'>2</div>
                <p><strong>Select 'Save as PDF'</strong></p>
            </div>
            <div class='step'>
                <div class='step-number'>3</div>
                <p><strong>Set margins to 'Minimum'</strong></p>
            </div>
            <div class='step'>
                <div class='step-number' style='background: #4caf50;'>✓</div>
                <p><strong>Click 'Save'</strong></p>
            </div>
        </div>
        <p style='margin: 15px 0 0 0; color: #e65100;'><strong>💡 Pro Tip:</strong> This creates a professional PDF identical to a PDF generator!</p>
    </div>

    <div class='header'>
        <h1>BUSINESS PLAN</h1>
        <h2>$businessName</h2>
        <p>Generated on " . date('F j, Y') . "</p>
    </div>

    <div class='section'>
        <h2>1. Executive Summary</h2>
        <p><strong>Business Name:</strong> $businessName</p>
        <p><strong>Business Type:</strong> " . ucfirst($businessType) . "</p>
        <p><strong>Mission Statement:</strong> $missionStatement</p>
        <p><strong>Funding Requirements:</strong> $fundingNeeds</p>
    </div>

    <div class='section'>
        <h2>2. Company Description</h2>
        <p>$businessName is a " . strtolower($businessType) . " business that aims to serve the market through innovative solutions and exceptional service delivery.</p>
        
        <h3>Mission Statement</h3>
        <div class='highlight'>
            <p>$missionStatement</p>
        </div>

        <h3>Business Objectives</h3>
        <ul>
            <li>Establish a strong market presence within the first year</li>
            <li>Achieve profitability within 18 months</li>
            <li>Build a loyal customer base through quality service</li>
            <li>Expand operations based on market demand</li>
        </ul>
    </div>

    <div class='section'>
        <h2>3. Market Analysis</h2>
        <h3>Target Market</h3>
        <p>$targetMarket</p>

        <h3>Market Opportunity</h3>
        <p>The target market presents significant opportunities for growth and expansion. Market research indicates strong demand for the proposed products/services.</p>
    </div>

    <div class='section'>
        <h2>4. Competitive Advantage</h2>
        <div class='highlight'>
            <p>$competitiveAdvantage</p>
        </div>
        
        <h3>Key Differentiators</h3>
        <ul>
            <li>Superior customer service and support</li>
            <li>Innovative approach to market challenges</li>
            <li>Competitive pricing strategy</li>
            <li>Strong brand positioning</li>
        </ul>
    </div>

    <div class='section'>
        <h2>5. Marketing Strategy</h2>
        <h3>Marketing Objectives</h3>
        <ul>
            <li>Increase brand awareness in target market</li>
            <li>Generate qualified leads and conversions</li>
            <li>Build customer loyalty and retention</li>
            <li>Establish strategic partnerships</li>
        </ul>
    </div>

    <div class='section'>
        <h2>6. Operations Plan</h2>
        <h3>Business Operations</h3>
        <p>The business will operate efficiently through streamlined processes and effective resource management.</p>

        <h3>Key Operational Activities</h3>
        <ul>
            <li>Product/service delivery and quality control</li>
            <li>Customer relationship management</li>
            <li>Inventory and supply chain management</li>
            <li>Financial management and reporting</li>
        </ul>
    </div>

    <div class='section'>
        <h2>7. Financial Projections</h2>
        <h3>Funding Requirements</h3>
        <p><strong>Total Funding Needed:</strong> $fundingNeeds</p>
        <p>Funding will be used for:</p>
        <ul>
            <li>Initial setup and equipment costs</li>
            <li>Working capital for first 6 months</li>
            <li>Marketing and customer acquisition</li>
            <li>Technology and infrastructure</li>
        </ul>
    </div>

    <div class='section'>
        <h2>8. Risk Analysis</h2>
        <h3>Potential Risks</h3>
        <ul>
            <li>Market competition and saturation</li>
            <li>Economic downturns affecting customer spending</li>
            <li>Regulatory changes impacting operations</li>
            <li>Technology disruptions in the industry</li>
        </ul>

        <h3>Risk Mitigation Strategies</h3>
        <ul>
            <li>Diversification of revenue streams</li>
            <li>Strong financial management and cash reserves</li>
            <li>Continuous market monitoring and adaptation</li>
            <li>Investment in technology and innovation</li>
        </ul>
    </div>

    <div class='section'>
        <h2>9. Implementation Timeline</h2>
        <h3>Phase 1: Foundation (Months 1-3)</h3>
        <ul>
            <li>Business registration and legal setup</li>
            <li>Initial funding acquisition</li>
            <li>Team building and hiring</li>
            <li>Basic infrastructure setup</li>
        </ul>

        <h3>Phase 2: Launch (Months 4-6)</h3>
        <ul>
            <li>Product/service development and testing</li>
            <li>Marketing campaign launch</li>
            <li>Customer acquisition and onboarding</li>
            <li>Operational process refinement</li>
        </ul>

        <h3>Phase 3: Growth (Months 7-12)</h3>
        <ul>
            <li>Market expansion and scaling</li>
            <li>Product/service line extensions</li>
            <li>Strategic partnerships development</li>
            <li>Performance optimization and growth</li>
        </ul>
    </div>

    <div class='section'>
        <h2>10. Conclusion</h2>
        <p>$businessName presents a compelling business opportunity with strong market potential and clear competitive advantages. With proper execution of this business plan, the company is positioned for sustainable growth and profitability.</p>
        
        <p>The combination of market opportunity, competitive positioning, and strategic planning provides a solid foundation for business success. Continued focus on customer satisfaction, operational excellence, and market adaptation will be key to achieving long-term objectives.</p>
    </div>

    <div class='footer'>
        <p><em>This business plan was generated by InnoStart AI Assistant</em></p>
        <p><em>Generated on " . date('F j, Y \a\t g:i A') . "</em></p>
    </div>
</body>
</html>";
}

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
echo $html;
?>