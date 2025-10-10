<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit();
}

$location = $input['location'] ?? '';
$interests = $input['interests'] ?? '';
$budget = $input['budget'] ?? '';

// Business idea templates based on location and interests
function generateBusinessIdeas($location, $interests, $budget) {
    $ideas = [];
    
    // Parse interests
    $interestKeywords = array_map('trim', explode(',', strtolower($interests)));
    
    // Location-based ideas
    $locationIdeas = getLocationBasedIdeas($location);
    
    // Interest-based ideas
    $interestIdeas = getInterestBasedIdeas($interestKeywords);
    
    // Budget-appropriate ideas
    $budgetIdeas = getBudgetBasedIdeas($budget);
    
    // Combine and filter ideas
    $allIdeas = array_merge($locationIdeas, $interestIdeas, $budgetIdeas);
    
    // Remove duplicates and select best matches
    $uniqueIdeas = [];
    $seen = [];
    
    foreach ($allIdeas as $idea) {
        $key = md5($idea['title'] . $idea['description']);
        if (!isset($seen[$key])) {
            $seen[$key] = true;
            $uniqueIdeas[] = $idea;
        }
    }
    
    // Score and rank ideas based on relevance
    $scoredIdeas = [];
    foreach ($uniqueIdeas as $idea) {
        $score = calculateIdeaScore($idea, $location, $interestKeywords, $budget);
        $scoredIdeas[] = ['idea' => $idea, 'score' => $score];
    }
    
    // Sort by score and return top 5
    usort($scoredIdeas, function($a, $b) {
        return $b['score'] - $a['score'];
    });
    
    $topIdeas = array_slice($scoredIdeas, 0, 5);
    return array_map(function($item) {
        return $item['idea'];
    }, $topIdeas);
}

function getLocationBasedIdeas($location) {
    $location = strtolower($location);
    $ideas = [];
    
    // Urban area ideas
    if (strpos($location, 'city') !== false || strpos($location, 'urban') !== false) {
        $ideas = array_merge($ideas, [
            [
                'title' => 'Urban Food Delivery Service',
                'description' => 'Fast food delivery service for busy urban professionals, focusing on healthy and local options.',
                'category' => 'Food & Delivery',
                'budget' => 'Medium',
                'difficulty' => 'Medium',
                'location_relevance' => 'high'
            ],
            [
                'title' => 'Co-working Space Management',
                'description' => 'Manage and operate flexible co-working spaces for freelancers and remote workers.',
                'category' => 'Real Estate',
                'budget' => 'High',
                'difficulty' => 'Hard',
                'location_relevance' => 'high'
            ]
        ]);
    }
    
    // Rural area ideas
    if (strpos($location, 'rural') !== false || strpos($location, 'country') !== false) {
        $ideas = array_merge($ideas, [
            [
                'title' => 'Agricultural Consulting Service',
                'description' => 'Provide consulting services to local farmers on modern farming techniques and technology.',
                'category' => 'Agriculture',
                'budget' => 'Low',
                'difficulty' => 'Medium',
                'location_relevance' => 'high'
            ],
            [
                'title' => 'Rural Tourism Experience',
                'description' => 'Create unique rural tourism experiences like farm stays, nature tours, and local crafts workshops.',
                'category' => 'Tourism',
                'budget' => 'Medium',
                'difficulty' => 'Medium',
                'location_relevance' => 'high'
            ]
        ]);
    }
    
    // Coastal area ideas
    if (strpos($location, 'coast') !== false || strpos($location, 'beach') !== false) {
        $ideas = array_merge($ideas, [
            [
                'title' => 'Marine Equipment Rental',
                'description' => 'Rent out water sports equipment, boats, and fishing gear to tourists and locals.',
                'category' => 'Tourism',
                'budget' => 'Medium',
                'difficulty' => 'Medium',
                'location_relevance' => 'high'
            ]
        ]);
    }
    
    return $ideas;
}

function getInterestBasedIdeas($interests) {
    $ideas = [];
    
    foreach ($interests as $interest) {
        switch ($interest) {
            case 'technology':
            case 'tech':
                $ideas[] = [
                    'title' => 'Local Tech Support Service',
                    'description' => 'Provide in-home and remote tech support for individuals and small businesses.',
                    'category' => 'Technology',
                    'budget' => 'Low',
                    'difficulty' => 'Easy',
                    'interest_relevance' => 'high'
                ];
                $ideas[] = [
                    'title' => 'Mobile App Development Agency',
                    'description' => 'Create custom mobile applications for local businesses and startups.',
                    'category' => 'Technology',
                    'budget' => 'Medium',
                    'difficulty' => 'Hard',
                    'interest_relevance' => 'high'
                ];
                break;
                
            case 'food':
            case 'cooking':
                $ideas[] = [
                    'title' => 'Home-based Catering Service',
                    'description' => 'Provide catering services for small events, parties, and corporate meetings.',
                    'category' => 'Food & Beverage',
                    'budget' => 'Low',
                    'difficulty' => 'Easy',
                    'interest_relevance' => 'high'
                ];
                $ideas[] = [
                    'title' => 'Cooking Classes & Workshops',
                    'description' => 'Offer cooking classes for different skill levels and dietary preferences.',
                    'category' => 'Education',
                    'budget' => 'Low',
                    'difficulty' => 'Easy',
                    'interest_relevance' => 'high'
                ];
                break;
                
            case 'fashion':
            case 'clothing':
                $ideas[] = [
                    'title' => 'Online Fashion Boutique',
                    'description' => 'Curate and sell unique fashion items through an online store.',
                    'category' => 'E-commerce',
                    'budget' => 'Medium',
                    'difficulty' => 'Medium',
                    'interest_relevance' => 'high'
                ];
                $ideas[] = [
                    'title' => 'Personal Styling Service',
                    'description' => 'Offer personal styling and wardrobe consulting services.',
                    'category' => 'Services',
                    'budget' => 'Low',
                    'difficulty' => 'Easy',
                    'interest_relevance' => 'high'
                ];
                break;
                
            case 'fitness':
            case 'health':
                $ideas[] = [
                    'title' => 'Personal Training Service',
                    'description' => 'Provide one-on-one fitness training and wellness coaching.',
                    'category' => 'Health & Fitness',
                    'budget' => 'Low',
                    'difficulty' => 'Medium',
                    'interest_relevance' => 'high'
                ];
                $ideas[] = [
                    'title' => 'Online Fitness Coaching',
                    'description' => 'Offer virtual fitness programs and nutrition coaching.',
                    'category' => 'Health & Fitness',
                    'budget' => 'Low',
                    'difficulty' => 'Easy',
                    'interest_relevance' => 'high'
                ];
                break;
                
            case 'art':
            case 'creative':
                $ideas[] = [
                    'title' => 'Art Classes & Workshops',
                    'description' => 'Teach various art forms including painting, drawing, and crafts.',
                    'category' => 'Education',
                    'budget' => 'Low',
                    'difficulty' => 'Easy',
                    'interest_relevance' => 'high'
                ];
                $ideas[] = [
                    'title' => 'Custom Art Commission Service',
                    'description' => 'Create custom artwork for individuals and businesses.',
                    'category' => 'Creative Services',
                    'budget' => 'Low',
                    'difficulty' => 'Medium',
                    'interest_relevance' => 'high'
                ];
                break;
        }
    }
    
    return $ideas;
}

function getBudgetBasedIdeas($budget) {
    $ideas = [];
    
    switch ($budget) {
        case '0-1000':
            $ideas = [
                [
                    'title' => 'Virtual Assistant Services',
                    'description' => 'Provide remote administrative and support services to businesses.',
                    'category' => 'Services',
                    'budget' => 'Low',
                    'difficulty' => 'Easy',
                    'budget_relevance' => 'high'
                ],
                [
                    'title' => 'Content Writing Service',
                    'description' => 'Offer blog writing, copywriting, and content creation services.',
                    'category' => 'Services',
                    'budget' => 'Low',
                    'difficulty' => 'Easy',
                    'budget_relevance' => 'high'
                ],
                [
                    'title' => 'Social Media Management',
                    'description' => 'Manage social media accounts for small businesses and entrepreneurs.',
                    'category' => 'Marketing',
                    'budget' => 'Low',
                    'difficulty' => 'Easy',
                    'budget_relevance' => 'high'
                ]
            ];
            break;
            
        case '1000-5000':
            $ideas = [
                [
                    'title' => 'E-commerce Store',
                    'description' => 'Launch an online store selling curated products in a specific niche.',
                    'category' => 'E-commerce',
                    'budget' => 'Medium',
                    'difficulty' => 'Medium',
                    'budget_relevance' => 'high'
                ],
                [
                    'title' => 'Local Service Business',
                    'description' => 'Start a service-based business like cleaning, landscaping, or home repairs.',
                    'category' => 'Services',
                    'budget' => 'Medium',
                    'difficulty' => 'Medium',
                    'budget_relevance' => 'high'
                ]
            ];
            break;
            
        case '5000-10000':
            $ideas = [
                [
                    'title' => 'Retail Store',
                    'description' => 'Open a physical retail store in a high-traffic location.',
                    'category' => 'Retail',
                    'budget' => 'High',
                    'difficulty' => 'Hard',
                    'budget_relevance' => 'high'
                ],
                [
                    'title' => 'Restaurant or Cafe',
                    'description' => 'Launch a small restaurant or cafe with a unique concept.',
                    'category' => 'Food & Beverage',
                    'budget' => 'High',
                    'difficulty' => 'Hard',
                    'budget_relevance' => 'high'
                ]
            ];
            break;
            
        case '10000+':
            $ideas = [
                [
                    'title' => 'Manufacturing Business',
                    'description' => 'Start a small manufacturing operation for specialized products.',
                    'category' => 'Manufacturing',
                    'budget' => 'High',
                    'difficulty' => 'Hard',
                    'budget_relevance' => 'high'
                ],
                [
                    'title' => 'Technology Startup',
                    'description' => 'Develop and launch a technology product or platform.',
                    'category' => 'Technology',
                    'budget' => 'High',
                    'difficulty' => 'Hard',
                    'budget_relevance' => 'high'
                ]
            ];
            break;
    }
    
    return $ideas;
}

function calculateIdeaScore($idea, $location, $interests, $budget) {
    $score = 0;
    
    // Location relevance
    if (isset($idea['location_relevance']) && $idea['location_relevance'] === 'high') {
        $score += 3;
    }
    
    // Interest relevance
    if (isset($idea['interest_relevance']) && $idea['interest_relevance'] === 'high') {
        $score += 3;
    }
    
    // Budget relevance
    if (isset($idea['budget_relevance']) && $idea['budget_relevance'] === 'high') {
        $score += 2;
    }
    
    // Base score for all ideas
    $score += 1;
    
    return $score;
}

try {
    $ideas = generateBusinessIdeas($location, $interests, $budget);
    
    // If no ideas generated, provide some general suggestions
    if (empty($ideas)) {
        $ideas = [
            [
                'title' => 'Local Service Business',
                'description' => 'Start a service-based business tailored to your local community needs.',
                'category' => 'Services',
                'budget' => 'Medium',
                'difficulty' => 'Medium'
            ],
            [
                'title' => 'Online Consulting',
                'description' => 'Offer consulting services in your area of expertise through online platforms.',
                'category' => 'Services',
                'budget' => 'Low',
                'difficulty' => 'Easy'
            ],
            [
                'title' => 'E-commerce Store',
                'description' => 'Create an online store selling products in a niche you\'re passionate about.',
                'category' => 'E-commerce',
                'budget' => 'Medium',
                'difficulty' => 'Medium'
            ]
        ];
    }
    
    echo json_encode([
        'ideas' => $ideas,
        'location' => $location,
        'interests' => $interests,
        'budget' => $budget,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error',
        'message' => 'An error occurred while generating business ideas'
    ]);
}
?>

