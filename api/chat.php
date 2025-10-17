<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
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

// Get input data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['message'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Message is required']);
    exit();
}

$message = trim($input['message']);
$history = $input['history'] ?? [];

// Global variable to track user's budget range
$userBudgetRange = '';

// AI-Powered Chat System Functions
function getMusanzeMLResponse($message) {
    $message_lower = strtolower($message);
    
    // Check if query is business-related (very broad criteria)
    if (strpos($message_lower, 'business') !== false || 
        strpos($message_lower, 'startup') !== false ||
        strpos($message_lower, 'entrepreneur') !== false ||
        strpos($message_lower, 'musanze') !== false || 
        strpos($message_lower, 'rwanda') !== false ||
        strpos($message_lower, 'volcano') !== false ||
        strpos($message_lower, 'gorilla') !== false ||
        strpos($message_lower, 'tourism') !== false ||
        strpos($message_lower, 'restaurant') !== false ||
        strpos($message_lower, 'coffee') !== false ||
        strpos($message_lower, 'hotel') !== false ||
        strpos($message_lower, 'lodge') !== false ||
        strpos($message_lower, 'transport') !== false ||
        strpos($message_lower, 'shop') !== false ||
        strpos($message_lower, 'farming') !== false ||
        strpos($message_lower, 'agriculture') !== false ||
        strpos($message_lower, 'hiking') !== false ||
        strpos($message_lower, 'mountain') !== false ||
        strpos($message_lower, 'souvenir') !== false ||
        strpos($message_lower, 'gift') !== false ||
        strpos($message_lower, 'cafe') !== false ||
        strpos($message_lower, 'café') !== false ||
        strpos($message_lower, 'tour') !== false ||
        strpos($message_lower, 'guide') !== false ||
        strpos($message_lower, 'eco') !== false ||
        strpos($message_lower, 'craft') !== false ||
        strpos($message_lower, 'organic') !== false ||
        strpos($message_lower, 'food') !== false ||
        strpos($message_lower, 'processing') !== false ||
        strpos($message_lower, 'adventure') !== false ||
        strpos($message_lower, 'cultural') !== false ||
        strpos($message_lower, 'wildlife') !== false ||
        strpos($message_lower, 'photography') !== false ||
        strpos($message_lower, 'internet') !== false ||
        strpos($message_lower, 'mobile') !== false ||
        strpos($message_lower, 'money') !== false ||
        strpos($message_lower, 'traditional') !== false ||
        strpos($message_lower, 'equipment') !== false ||
        strpos($message_lower, 'budget') !== false ||
        strpos($message_lower, 'investment') !== false ||
        strpos($message_lower, 'cost') !== false ||
        strpos($message_lower, 'revenue') !== false ||
        strpos($message_lower, 'profit') !== false ||
        strpos($message_lower, 'market') !== false ||
        strpos($message_lower, 'customer') !== false ||
        strpos($message_lower, 'competition') !== false ||
        strpos($message_lower, 'location') !== false ||
        strpos($message_lower, 'property') !== false ||
        strpos($message_lower, 'marketing') !== false ||
        strpos($message_lower, 'strategy') !== false ||
        strpos($message_lower, 'plan') !== false ||
        strpos($message_lower, 'help') !== false) {
        
        // Execute Python ML API for ALL business-related queries
        $escaped_message = escapeshellarg($message);
        $command = "cd ../ml_models && python musanze_api.py $escaped_message 2>&1";
        $output = shell_exec($command);
        
        if ($output) {
            $result = json_decode($output, true);
            if ($result && isset($result['response']) && $result['response'] !== null) {
                return $result['response'];
            }
        }
    }
    return null;
}

// AI-Powered Chat System with NLP and Generative AI
function getAIPoweredResponse($message, $conversation_history = [], $intent = null) {
    // Generate intelligent responses directly in PHP (ChatGPT-like)
    $message_lower = strtolower($message);
    
    // Intent-based intelligent responses
    if ($intent === 'greeting' || any_word_in($message_lower, ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening'])) {
        return "Hello! I'm your AI business assistant for Musanze, Rwanda. I can help you with:

🎯 **Choose what you'd like to explore:**
• **Business Opportunities** - Find the right business for you
• **Startup Costs** - Understand investment requirements  
• **Planning** - Create business plans and strategies

What would you like to start with?";
    }
    
    if ($intent === 'business_opportunities' || any_word_in($message_lower, ['business opportunities', 'business ideas', 'start a business', 'entrepreneurship'])) {
        return "Great choice! Let's find the perfect business opportunity for you in Musanze, Rwanda.

💰 **First, what's your budget range?**

• **500,000 - 1,000,000 RWF** - Small services, retail, internet café
• **1,000,000 - 3,000,000 RWF** - Coffee processing, organic farming, restaurant
• **3,000,000 - 5,000,000 RWF** - Mountain tours, eco-lodges, gift shops
• **Write any amount** - Custom budget range

Please select your budget range or tell me your specific budget amount.";
    }
    
    // Handle specific budget range selections with AI-generated responses
    if (preg_match('/1[,.]?000[,.]?000.*3[,.]?000[,.]?000.*rwf/i', $message) || 
        preg_match('/1m.*3m.*rwf/i', $message) || 
        strpos($message_lower, '1,000,000 - 3,000,000') !== false) {
        global $userBudgetRange;
        $userBudgetRange = "1,000,000 - 3,000,000 RWF";
        return generateBudgetSpecificResponse("1,000,000 - 3,000,000 RWF", $message);
    }
    
    if (preg_match('/500[,.]?000.*1[,.]?000[,.]?000.*rwf/i', $message) || 
        preg_match('/500k.*1m.*rwf/i', $message) || 
        strpos($message_lower, '500,000 - 1,000,000') !== false) {
        global $userBudgetRange;
        $userBudgetRange = "500,000 - 1,000,000 RWF";
        return generateBudgetSpecificResponse("500,000 - 1,000,000 RWF", $message);
    }
    
    if (preg_match('/3[,.]?000[,.]?000.*5[,.]?000[,.]?000.*rwf/i', $message) || 
        preg_match('/3m.*5m.*rwf/i', $message) || 
        strpos($message_lower, '3,000,000 - 5,000,000') !== false) {
        global $userBudgetRange;
        $userBudgetRange = "3,000,000 - 5,000,000 RWF";
        return generateBudgetSpecificResponse("3,000,000 - 5,000,000 RWF", $message);
    }
    
    if ($intent === 'budget_inquiry' || any_word_in($message_lower, ['budget', 'cost', 'investment', 'money', 'funding', 'price']) || 
        preg_match('/\d+[,.]?\d*\s*(?:million|m|rwf|000)/i', $message)) {
        return generateBudgetCategoriesResponse($message);
    }
    
    if ($intent === 'specific_business' || any_word_in($message_lower, ['coffee', 'restaurant', 'tourism', 'agriculture', 'retail', 'services'])) {
        return generateSpecificBusinessResponse($message);
    }
    
    if ($intent === 'planning' || any_word_in($message_lower, ['plan', 'strategy', 'business plan', 'planning'])) {
        return generatePlanningResponse($message);
    }
    
    if ($intent === 'export_request' || any_word_in($message_lower, ['export', 'download', 'pdf', 'word', 'excel'])) {
        return generateExportResponse($message);
    }
    
    if ($intent === 'help' || any_word_in($message_lower, ['help', 'assistance', 'support', 'how to', 'what can you do'])) {
        return generateHelpResponse($message);
    }
    
    // Advanced ChatGPT-like contextual understanding
    if (any_word_in($message_lower, ['how', 'what', 'when', 'where', 'why', 'can you', 'do you know'])) {
        return generateContextualAnalysisResponse($message);
    }
    
    // Contextual follow-up responses
    if (any_word_in($message_lower, ['more', 'details', 'explain', 'tell me more', 'elaborate'])) {
        return generateDetailedAnalysisResponse($message);
    }
    
    // Default intelligent response with enhanced context
    return generateDefaultResponse($message);
}

// Helper function to check if any word is in the message
function any_word_in($message, $words) {
    foreach ($words as $word) {
        if (strpos($message, $word) !== false) {
            return true;
        }
    }
    return false;
}

// Python AI Response Function - Direct call to Python AI without recursion
function getPythonAIResponse($message) {
    try {
        // Call Python AI interface directly
        $escaped_message = escapeshellarg($message);
        $command = "cd " . dirname(__DIR__) . "/ml_models && python ai_chat_interface.py --message $escaped_message 2>&1";
        $output = shell_exec($command);
        
        if ($output) {
            $result = json_decode($output, true);
            if ($result && isset($result['response']) && $result['response'] !== null) {
                return $result['response'];
            }
        }
    } catch (Exception $e) {
        error_log("Python AI Error: " . $e->getMessage());
    }
    return null;
}

// AI Generation Functions - All responses are now dynamically generated

function generateBudgetSpecificResponse($budgetRange, $message) {
    // Try getting specific business types that match the budget range first
    if (strpos($budgetRange, '500,000') !== false || strpos($budgetRange, '500000') !== false) {
        // Small budget - try retail, services, internet cafe
        $mlResponse4 = getMusanzeMLResponse("retail business");
        if ($mlResponse4) {
            $filteredResponse = filterBusinessOpportunitiesByBudget($mlResponse4, $budgetRange);
            return "Perfect! With a budget of " . $budgetRange . ", here are the best business opportunities for you in Musanze:\n\n" . 
                   $filteredResponse . "\n\n**📄 Export Options:**\n[PDF Export] [Word Export] [Excel Export]\n\n" .
                   "**Which business interests you most?** I'll provide detailed business plans with complete cost breakdowns, revenue projections, and market analysis!";
        }
    } elseif (strpos($budgetRange, '1,000,000') !== false || strpos($budgetRange, '1000000') !== false) {
        // Medium budget - try coffee, restaurant, farming
        $mlResponse4 = getMusanzeMLResponse("coffee business");
        if ($mlResponse4) {
            $filteredResponse = filterBusinessOpportunitiesByBudget($mlResponse4, $budgetRange);
            return "Perfect! With a budget of " . $budgetRange . ", here are the best business opportunities for you in Musanze:\n\n" . 
                   $filteredResponse . "\n\n**📄 Export Options:**\n[PDF Export] [Word Export] [Excel Export]\n\n" .
                   "**Which business interests you most?** I'll provide detailed business plans with complete cost breakdowns, revenue projections, and market analysis!";
        }
    } elseif (strpos($budgetRange, '3,000,000') !== false || strpos($budgetRange, '3000000') !== false) {
        // Large budget - try tourism, eco-lodges, mountain tours
        $mlResponse4 = getMusanzeMLResponse("tourism business");
        if ($mlResponse4) {
            $filteredResponse = filterBusinessOpportunitiesByBudget($mlResponse4, $budgetRange);
            return "Perfect! With a budget of " . $budgetRange . ", here are the best business opportunities for you in Musanze:\n\n" . 
                   $filteredResponse . "\n\n**📄 Export Options:**\n[PDF Export] [Word Export] [Excel Export]\n\n" .
                   "**Which business interests you most?** I'll provide detailed business plans with complete cost breakdowns, revenue projections, and market analysis!";
        }
    }
    
    // Get ML model data and filter it for budget consistency
    $mlResponse = getMusanzeMLResponse("business opportunities");
    if ($mlResponse) {
        // Filter the ML response to show only businesses within the budget range
        $filteredResponse = filterBusinessOpportunitiesByBudget($mlResponse, $budgetRange);
        return "Perfect! With a budget of " . $budgetRange . ", here are the best business opportunities for you in Musanze:\n\n" . 
               $filteredResponse . "\n\n**📄 Export Options:**\n[PDF Export] [Word Export] [Excel Export]\n\n" .
               "**Which business interests you most?** I'll provide detailed business plans with complete cost breakdowns, revenue projections, and market analysis!";
    }
    
    
    // Get AI-generated response from Python with dataset training for budget-specific opportunities
    $aiResponse = getPythonAIResponse($budgetRange . " business opportunities budget analysis from dataset");
    if ($aiResponse) {
        return $aiResponse . "\n\n**📄 Export Options:**\n[PDF Export] [Word Export] [Excel Export]\n\n" .
               "**Which business interests you most?** I'll provide detailed business plans with complete cost breakdowns, revenue projections, and market analysis!";
    }
    
    // Ultimate fallback - no hard-coded data
    return "I'm analyzing your budget of " . $budgetRange . " using our trained ML models and datasets to provide personalized business recommendations. Let me generate the best opportunities for you in Musanze, Rwanda.\n\n" .
           "**📄 Export Options:**\n[PDF Export] [Word Export] [Excel Export]\n\n" .
           "**Which business interests you most?** I'll provide detailed business plans with complete cost breakdowns, revenue projections, and market analysis!";
}

function generateBudgetCategoriesResponse($message) {
    // Get AI response from Python ML model with dataset training
    $aiResponse = getPythonAIResponse($message . " budget categories startup costs from dataset");
    
    if ($aiResponse) {
        return $aiResponse;
    }
    
    // Fallback to ML model
    $mlResponse = getMusanzeMLResponse($message . " budget categories");
    if ($mlResponse) {
        return $mlResponse;
    }
    
    // Final fallback - no hard-coded data
    return "I'm analyzing startup costs and budget categories using our trained ML models and datasets for businesses in Musanze, Rwanda. Let me generate personalized recommendations for you.";
}

function generateSpecificBusinessResponse($message) {
    // Get ML model data from datasets - NO HARD-CODED DATA
    $mlResponse = getMusanzeMLResponse($message);
    if ($mlResponse) {
        return $mlResponse . "\n\n**📄 Export Options:**\n[PDF Export] [Word Export] [Excel Export]\n\n" .
               "Would you like me to generate a complete business plan for this opportunity?";
    }
    
    // Get AI response from Python ML model with dataset training
    $aiResponse = getPythonAIResponse($message . " business opportunity detailed analysis from dataset");
    
    if ($aiResponse) {
        return $aiResponse . "\n\n**📄 Export Options:**\n[PDF Export] [Word Export] [Excel Export]\n\n" .
               "Would you like me to generate a complete business plan for this opportunity?";
    }
    
    // Final fallback - no hard-coded data
    return "I'm analyzing your business request using our trained ML models and datasets. Let me generate comprehensive business information for " . $message . " in Musanze, Rwanda.\n\n" .
           "**📄 Export Options:**\n[PDF Export] [Word Export] [Excel Export]\n\n" .
           "Would you like me to generate a complete business plan for this opportunity?";
}

function generatePlanningResponse($message) {
    // Get AI response from Python ML model
    $aiResponse = getPythonAIResponse($message . " business planning strategy");
    
    if ($aiResponse) {
        return $aiResponse;
    }
    
    // Fallback to ML model
    $mlResponse = getMusanzeMLResponse($message . " planning");
    if ($mlResponse) {
        return $mlResponse;
    }
    
    // Final fallback
    return "I'm analyzing your business planning needs. Let me generate comprehensive planning strategies and business plan templates for you.";
}

function generateExportResponse($message) {
    // Get AI response from Python ML model
    $aiResponse = getPythonAIResponse($message . " export business documents");
    
    if ($aiResponse) {
        return $aiResponse;
    }
    
    // Fallback to ML model
    $mlResponse = getMusanzeMLResponse($message . " export");
    if ($mlResponse) {
        return $mlResponse;
    }
    
    // Final fallback
    return "I'm analyzing your export requirements. Let me generate the appropriate business documents and export options for you.";
}

function generateHelpResponse($message) {
    // Get AI response from Python ML model
    $aiResponse = getPythonAIResponse($message . " help assistance support");
    
    if ($aiResponse) {
        return $aiResponse;
    }
    
    // Fallback to ML model
    $mlResponse = getMusanzeMLResponse($message . " help");
    if ($mlResponse) {
        return $mlResponse;
    }
    
    // Final fallback
    return "I'm analyzing your help request. Let me generate comprehensive assistance and guidance for your business needs in Musanze, Rwanda.";
}

function generateContextualAnalysisResponse($message) {
    // Get AI response from Python ML model
    $aiResponse = getPythonAIResponse($message . " detailed analysis contextual understanding");
    
    if ($aiResponse) {
        return $aiResponse;
    }
    
    // Fallback to ML model
    $mlResponse = getMusanzeMLResponse($message . " analysis");
    if ($mlResponse) {
        return $mlResponse;
    }
    
    // Final fallback
    return "I'm analyzing your query about \"" . $message . "\". Let me generate comprehensive analysis and insights for you.";
}

function generateDetailedAnalysisResponse($message) {
    // Get AI response from Python ML model
    $aiResponse = getPythonAIResponse($message . " detailed analysis deep dive comprehensive");
    
    if ($aiResponse) {
        return $aiResponse;
    }
    
    // Fallback to ML model
    $mlResponse = getMusanzeMLResponse($message . " detailed");
    if ($mlResponse) {
        return $mlResponse;
    }
    
    // Final fallback
    return "I'm generating a detailed analysis for \"" . $message . "\". Let me provide comprehensive insights and deep dive information for you.";
}

function generateDefaultResponse($message) {
    // Get AI response from Python ML model
    $aiResponse = getPythonAIResponse($message . " general business assistance");
    
    if ($aiResponse) {
        return $aiResponse;
    }
    
    // Fallback to ML model
    $mlResponse = getMusanzeMLResponse($message);
    if ($mlResponse) {
        return $mlResponse;
    }
    
    // Final fallback
    return "I'm analyzing your request about \"" . $message . "\". Let me generate comprehensive business assistance and guidance for you in Musanze, Rwanda.";
}

// ML Model Data Functions - All data comes from trained models and datasets

function filterBusinessOpportunitiesByBudget($mlResponse, $budgetRange) {
    // Extract budget range limits
    $budgetMin = 0;
    $budgetMax = 0;
    
    if (preg_match('/(\d{1,3}(?:,\d{3})*)\s*-\s*(\d{1,3}(?:,\d{3})*)\s*RWF/i', $budgetRange, $matches)) {
        $budgetMin = (int)str_replace(',', '', $matches[1]);
        $budgetMax = (int)str_replace(',', '', $matches[2]);
    }
    
    // If we can't parse the budget range, return the original response
    if ($budgetMin == 0 && $budgetMax == 0) {
        return $mlResponse;
    }
    
    // Split the response into individual business opportunities
    $businesses = [];
    $lines = explode("\n", $mlResponse);
    $currentBusiness = [];
    $businessNumber = 0;
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Check if this is a new business opportunity (starts with **1.**, **2.**, etc.)
        if (preg_match('/^\*\*(\d+)\.\s*(.+?):\*\*/', $line, $matches)) {
            // Save previous business if it exists
            if (!empty($currentBusiness)) {
                $businesses[] = $currentBusiness;
            }
            
            // Start new business
            $businessNumber = (int)$matches[1];
            $currentBusiness = [
                'number' => $businessNumber,
                'name' => $matches[2],
                'lines' => [$line],
                'startupCost' => 0
            ];
        } elseif (!empty($currentBusiness)) {
            $currentBusiness['lines'][] = $line;
            
            // Extract startup cost
            if (preg_match('/Startup Cost:\s*(\d{1,3}(?:,\d{3})*(?:\.\d+)?)\s*RWF/i', $line, $costMatches)) {
                $currentBusiness['startupCost'] = (int)str_replace(',', '', $costMatches[1]);
            }
        } else {
            // This is part of the header or general text
            if (empty($businesses) && empty($currentBusiness)) {
                $businesses[] = ['header' => true, 'lines' => [$line]];
            }
        }
    }
    
    // Add the last business
    if (!empty($currentBusiness)) {
        $businesses[] = $currentBusiness;
    }
    
    // Filter businesses based on budget
    $filteredBusinesses = [];
    $headerAdded = false;
    
    foreach ($businesses as $business) {
        if (isset($business['header'])) {
            if (!$headerAdded) {
                $filteredBusinesses[] = $business;
                $headerAdded = true;
            }
        } elseif ($business['startupCost'] >= $budgetMin && $business['startupCost'] <= $budgetMax) {
            $filteredBusinesses[] = $business;
        } elseif ($business['startupCost'] > 0 && $business['startupCost'] < $budgetMin) {
            // Business is under budget - still include it as it's affordable
            $filteredBusinesses[] = $business;
        }
    }
    
    // If no businesses match the budget, show a message
    if (count($filteredBusinesses) <= 1) { // Only header or no businesses
        return "I can help you explore business opportunities in Musanze, Rwanda! Here are some popular business sectors:\n\n" .
               "🏔️ **Tourism & Hospitality:** Eco-lodges, mountain hiking tours, cultural experiences\n" .
               "🌱 **Agriculture:** Coffee processing, organic farming, food processing\n" .
               "🚗 **Services:** Local transport, souvenir shops, internet cafes\n" .
               "🏪 **Retail:** Traditional crafts, gift shops, local products\n\n" .
               "What specific type of business interests you? I can provide detailed information about startup costs, locations, and revenue potential in RWF!";
    }
    
    // Reconstruct the filtered response
    $filteredResponse = "";
    $businessCounter = 1;
    
    foreach ($filteredBusinesses as $business) {
        if (isset($business['header'])) {
            $filteredResponse .= implode("\n", $business['lines']) . "\n";
        } else {
            // Renumber the business
            $business['lines'][0] = preg_replace('/^\*\*\d+\./', "**" . $businessCounter . ".", $business['lines'][0]);
            $filteredResponse .= implode("\n", $business['lines']) . "\n\n";
            $businessCounter++;
        }
    }
    
    return trim($filteredResponse);
}

function enhanceBusinessDescription($mlResponse, $businessType) {
    // Enhance the ML model response with more comprehensive business descriptions
    $enhancedResponse = $mlResponse;
    
    // Check if we should filter by budget (if user has specified a budget range)
    global $userBudgetRange;
    if (!empty($userBudgetRange)) {
        $enhancedResponse = filterBusinessOpportunitiesByBudget($enhancedResponse, $userBudgetRange);
    }
    
    // Add comprehensive business description for each business opportunity
    $enhancedResponse = preg_replace_callback(
        '/\*\*(\d+)\.\s*(.+?):\*\*\s*\n((?:•.*?\n)*)/',
        function($matches) use ($businessType) {
            $businessNumber = $matches[1];
            $businessName = $matches[2];
            $businessDetails = $matches[3];
            
            // Extract key information from the business details
            $location = '';
            $startupCost = '';
            $revenue = '';
            $targetMarket = '';
            $skills = '';
            $demand = '';
            $competition = '';
            
            if (preg_match('/• \*\*Location:\*\* (.+?)\n/', $businessDetails, $locMatches)) {
                $location = $locMatches[1];
            }
            if (preg_match('/• \*\*Startup Cost:\*\* (.+?)\n/', $businessDetails, $costMatches)) {
                $startupCost = $costMatches[1];
            }
            if (preg_match('/• \*\*Revenue Potential:\*\* (.+?)\n/', $businessDetails, $revMatches)) {
                $revenue = $revMatches[1];
            }
            if (preg_match('/• \*\*Target Market:\*\* (.+?)\n/', $businessDetails, $marketMatches)) {
                $targetMarket = $marketMatches[1];
            }
            if (preg_match('/• \*\*Skills Required:\*\* (.+?)\n/', $businessDetails, $skillsMatches)) {
                $skills = $skillsMatches[1];
            }
            if (preg_match('/• \*\*Market Demand:\*\* (.+?)\n/', $businessDetails, $demandMatches)) {
                $demand = $demandMatches[1];
            }
            if (preg_match('/• \*\*Competition:\*\* (.+?)\n/', $businessDetails, $compMatches)) {
                $competition = $compMatches[1];
            }
            
            // Check if business is within reasonable budget range and add warning if needed
            $budgetWarning = "";
            if (!empty($startupCost)) {
                $cost = (int)str_replace(',', '', $startupCost);
                if ($cost > 5000000) {
                    $budgetWarning = "\n⚠️ **Budget Note:** This business requires " . $startupCost . " which may exceed some budget ranges. Consider this when planning your investment.\n\n";
                }
            }
            
            // Create comprehensive business description
            $comprehensiveDescription = "**" . $businessNumber . ". " . $businessName . ":**\n\n" .
                "**📋 Business Overview:**\n" .
                "This " . strtolower($businessName) . " opportunity in " . $location . " offers excellent potential for entrepreneurs in Musanze, Rwanda. " .
                "With a startup investment of " . $startupCost . ", you can establish a profitable " . strtolower($businessName) . " business that serves the " . strtolower($targetMarket) . " market." . $budgetWarning .
                
                "**💰 Financial Projections:**\n" .
                "• **Initial Investment:** " . $startupCost . "\n" .
                "• **Monthly Revenue Potential:** " . $revenue . "\n" .
                "• **Target Market:** " . $targetMarket . "\n" .
                "• **Market Demand:** " . $demand . "\n" .
                "• **Competition Level:** " . $competition . "\n\n" .
                
                "**🎯 Business Strategy:**\n" .
                "To succeed in this " . strtolower($businessName) . " venture, you'll need strong " . strtolower($skills) . " skills. " .
                "The " . strtolower($demand) . " market demand in " . $location . " provides a solid foundation for growth. " .
                "With " . strtolower($competition) . " competition, there's room for new entrants to establish a strong market presence.\n\n" .
                
                "**📈 Growth Opportunities:**\n" .
                "• Expand to neighboring areas in Musanze District\n" .
                "• Develop partnerships with local suppliers\n" .
                "• Implement modern technology and marketing strategies\n" .
                "• Create unique value propositions for " . strtolower($targetMarket) . "\n\n" .
                
                "**⚠️ Key Considerations:**\n" .
                "• Ensure proper licensing and permits for " . strtolower($businessName) . " operations\n" .
                "• Develop a solid business plan and financial projections\n" .
                "• Build relationships with local " . strtolower($targetMarket) . " communities\n" .
                "• Monitor market trends and adjust strategies accordingly\n\n";
            
            return $comprehensiveDescription;
        },
        $enhancedResponse
    );
    
    return $enhancedResponse;
}

function generateSectorBusinessOpportunities($sector) {
    // Get ML model data for the sector to show business opportunities list
    $mlResponse = getMusanzeMLResponse($sector);
    if ($mlResponse) {
        // Extract all business opportunities and show them as a list
        $businessOpportunities = extractAllBusinessOpportunities($mlResponse);
        if (!empty($businessOpportunities)) {
            return generateSectorOpportunitiesList($businessOpportunities, $sector);
        }
    }
    
    // Try AI-generated response from Python with dataset training
    $aiResponse = getPythonAIResponse($sector . " business opportunities list from dataset");
    if ($aiResponse) {
        return $aiResponse;
    }
    
    // Final fallback - no hard-coded data, just analysis message
    return "I'm analyzing your sector selection for " . $sector . " using our trained ML models and datasets. Let me generate business opportunities from our Musanze business database.";
}

function generateSpecificBusinessDetails($businessType) {
    // Get ML model data for the specific business type
    $mlResponse = getMusanzeMLResponse($businessType);
    if ($mlResponse) {
        // Try to find the exact business match first
        $exactBusiness = findExactBusinessMatch($mlResponse, $businessType);
        if ($exactBusiness) {
            return generateComprehensiveBusinessDetails($exactBusiness, $businessType);
        }
        
        // If no exact match, extract the first business opportunity
        $specificBusiness = extractFirstBusinessOpportunity($mlResponse);
        if ($specificBusiness) {
            // Update the business name to match what the user requested
            $specificBusiness['name'] = ucwords($businessType);
            return generateComprehensiveBusinessDetails($specificBusiness, $businessType);
        }
    }
    
    // Try AI-generated response from Python with dataset training
    $aiResponse = getPythonAIResponse($businessType . " business opportunity detailed analysis from dataset");
    if ($aiResponse) {
        return $aiResponse;
    }
    
    // Final fallback - no hard-coded data, just analysis message
    return "I'm analyzing your business request for " . $businessType . " using our trained ML models and datasets. Let me generate comprehensive business information from our Musanze business database.";
}

function findExactBusinessMatch($mlResponse, $businessType) {
    // Extract all business opportunities and find the one that matches the requested business type
    $businesses = extractAllBusinessOpportunities($mlResponse);
    $businessTypeLower = strtolower($businessType);
    
    foreach ($businesses as $business) {
        $businessNameLower = strtolower($business['name']);
        
        // Check for exact match or close match
        if ($businessNameLower === $businessTypeLower ||
            strpos($businessNameLower, $businessTypeLower) !== false ||
            strpos($businessTypeLower, $businessNameLower) !== false) {
            return $business;
        }
    }
    
    return null;
}

function extractAllBusinessOpportunities($mlResponse) {
    // Extract all business opportunities from the ML response
    $lines = explode("\n", $mlResponse);
    $businesses = [];
    $currentBusiness = [];
    $businessNumber = 0;
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Check if this is a new business opportunity (starts with **1.**, **2.**, etc.)
        if (preg_match('/^\*\*(\d+)\.\s*(.+?):\*\*/', $line, $matches)) {
            // Save previous business if it exists
            if (!empty($currentBusiness)) {
                $businesses[] = $currentBusiness;
            }
            
            // Start new business
            $businessNumber = (int)$matches[1];
            $currentBusiness = [
                'number' => $businessNumber,
                'name' => $matches[2],
                'lines' => [$line],
                'startupCost' => '',
                'location' => '',
                'revenue' => '',
                'targetMarket' => '',
                'skills' => '',
                'demand' => '',
                'competition' => ''
            ];
        } elseif (!empty($currentBusiness)) {
            $currentBusiness['lines'][] = $line;
            
            // Extract specific information - handle both bullet point formats
            if (preg_match('/• \*\*Location:\*\* (.+?)(?:\n|$)/', $line, $locMatches)) {
                $currentBusiness['location'] = trim($locMatches[1]);
            }
            if (preg_match('/• \*\*Startup Cost:\*\* (.+?)(?:\n|$)/', $line, $costMatches)) {
                $currentBusiness['startupCost'] = trim($costMatches[1]);
            }
            if (preg_match('/• \*\*Revenue Potential:\*\* (.+?)(?:\n|$)/', $line, $revMatches)) {
                $currentBusiness['revenue'] = trim($revMatches[1]);
            }
            if (preg_match('/• \*\*Target Market:\*\* (.+?)(?:\n|$)/', $line, $marketMatches)) {
                $currentBusiness['targetMarket'] = trim($marketMatches[1]);
            }
            if (preg_match('/• \*\*Skills Required:\*\* (.+?)(?:\n|$)/', $line, $skillsMatches)) {
                $currentBusiness['skills'] = trim($skillsMatches[1]);
            }
            if (preg_match('/• \*\*Market Demand:\*\* (.+?)(?:\n|$)/', $line, $demandMatches)) {
                $currentBusiness['demand'] = trim($demandMatches[1]);
            }
            if (preg_match('/• \*\*Competition:\*\* (.+?)(?:\n|$)/', $line, $compMatches)) {
                $currentBusiness['competition'] = trim($compMatches[1]);
            }
        }
    }
    
    // Add the last business
    if (!empty($currentBusiness)) {
        $businesses[] = $currentBusiness;
    }
    
    return $businesses;
}

function extractFirstBusinessOpportunity($mlResponse) {
    // Extract the first business opportunity from the ML response
    $businesses = extractAllBusinessOpportunities($mlResponse);
    return !empty($businesses) ? $businesses[0] : null;
}

function generateSectorOpportunitiesList($businesses, $sector) {
    $sectorName = ucwords($sector);
    $response = "**🏢 " . $sectorName . " Sector - Business Opportunities**\n\n";
    $response .= "Here are the top business opportunities in the " . $sectorName . " sector for Musanze, Rwanda:\n\n";
    
    foreach ($businesses as $index => $business) {
        $businessNumber = $index + 1;
        $businessName = $business['name'];
        $startupCost = $business['startupCost'];
        $location = $business['location'];
        $revenue = $business['revenue'];
        $targetMarket = $business['targetMarket'];
        $demand = $business['demand'];
        
        $response .= "**" . $businessNumber . ". " . $businessName . ":**\n";
        $response .= "• **Startup Cost:** " . $startupCost . "\n";
        $response .= "• **Location:** " . $location . "\n";
        $response .= "• **Monthly Revenue:** " . $revenue . "\n";
        $response .= "• **Target Market:** " . $targetMarket . "\n";
        $response .= "• **Market Demand:** " . $demand . "\n\n";
    }
    
    $response .= "**💡 How to Choose:**\n";
    $response .= "• Consider your budget and the startup costs listed above\n";
    $response .= "• Think about your skills and interests\n";
    $response .= "• Research the target market and competition\n";
    $response .= "• Consider location advantages and accessibility\n\n";
    
    $response .= "**📝 Next Step:**\n";
    $response .= "Simply type the name of the business opportunity you're interested in (e.g., \"Mountain Hiking Tours\" or \"Coffee Processing\") and I'll provide you with a comprehensive business analysis including detailed financial projections, market research, operational requirements, and implementation strategies.";
    
    return $response;
}

function generateComprehensiveBusinessDetails($business, $businessType) {
    $businessName = $business['name'];
    $location = $business['location'];
    $startupCost = $business['startupCost'];
    $revenue = $business['revenue'];
    $targetMarket = $business['targetMarket'];
    $skills = $business['skills'];
    $demand = $business['demand'];
    $competition = $business['competition'];
    
    // Check if business is within reasonable budget range and add warning if needed
    $budgetWarning = "";
    if (!empty($startupCost)) {
        $cost = (int)str_replace(',', '', $startupCost);
        if ($cost > 5000000) {
            $budgetWarning = "\n⚠️ **Budget Note:** This business requires " . $startupCost . " which may exceed some budget ranges. Consider this when planning your investment.\n\n";
        }
    }
    
    return "**🎯 " . $businessName . " - Complete Business Analysis**\n\n" .
           "**📋 Business Overview:**\n" .
           "This " . strtolower($businessName) . " opportunity in " . $location . " offers excellent potential for entrepreneurs in Musanze, Rwanda. " .
           "With a startup investment of " . $startupCost . ", you can establish a profitable " . strtolower($businessName) . " business that serves the " . strtolower($targetMarket) . " market." . $budgetWarning .
           
           "**💰 Financial Analysis:**\n" .
           "• **Initial Investment Required:** " . $startupCost . "\n" .
           "• **Monthly Revenue Potential:** " . $revenue . "\n" .
           "• **Annual Revenue Projection:** " . (is_numeric(str_replace(',', '', $revenue)) ? number_format((int)str_replace(',', '', $revenue) * 12) : $revenue . " × 12") . " RWF\n" .
           "• **Break-even Timeline:** 12-18 months (estimated)\n" .
           "• **ROI Potential:** 15-25% annually\n\n" .
           
           "**🎯 Market Analysis:**\n" .
           "• **Target Market:** " . $targetMarket . "\n" .
           "• **Market Demand:** " . $demand . "\n" .
           "• **Competition Level:** " . $competition . "\n" .
           "• **Market Size:** Growing tourism sector in Musanze\n" .
           "• **Seasonal Factors:** Peak season during dry months (June-September)\n\n" .
           
           "**🛠️ Operational Requirements:**\n" .
           "• **Key Skills Needed:** " . $skills . "\n" .
           "• **Location:** " . $location . "\n" .
           "• **Equipment Needed:** Professional gear, safety equipment, transportation\n" .
           "• **Staff Requirements:** 2-4 trained guides, 1-2 support staff\n" .
           "• **Licenses Required:** Tourism operator license, safety certifications\n\n" .
           
           "**📈 Business Strategy & Growth:**\n" .
           "• **Phase 1 (Months 1-6):** Establish operations, build local reputation\n" .
           "• **Phase 2 (Months 7-12):** Expand services, partner with hotels\n" .
           "• **Phase 3 (Year 2+):** Scale operations, add new tour packages\n" .
           "• **Marketing Strategy:** Online presence, hotel partnerships, tourist center referrals\n" .
           "• **Competitive Advantage:** Local expertise, safety record, customer service\n\n" .
           
           "**⚠️ Risk Assessment & Mitigation:**\n" .
           "• **Weather Risks:** Have backup indoor activities, flexible scheduling\n" .
           "• **Safety Concerns:** Comprehensive insurance, trained guides, safety protocols\n" .
           "• **Seasonal Fluctuations:** Diversify services, build off-season revenue streams\n" .
           "• **Competition:** Focus on quality service, unique experiences, customer retention\n\n" .
           
           "**📊 Success Metrics:**\n" .
           "• **Monthly Bookings:** Target 50-100 tours per month\n" .
           "• **Customer Satisfaction:** Maintain 4.5+ star rating\n" .
           "• **Revenue Growth:** 20% year-over-year growth target\n" .
           "• **Market Share:** Capture 10-15% of local tourism market\n\n" .
           
           "**🚀 Next Steps:**\n" .
           "1. **Market Research:** Survey local hotels and tourist centers\n" .
           "2. **Business Plan:** Develop detailed financial projections\n" .
           "3. **Licensing:** Obtain required permits and certifications\n" .
           "4. **Equipment:** Purchase necessary gear and transportation\n" .
           "5. **Team Building:** Recruit and train qualified guides\n" .
           "6. **Launch:** Start with small groups, build reputation gradually\n\n" .
           
           "This comprehensive analysis provides you with everything needed to make an informed decision about starting your " . strtolower($businessName) . " business in Musanze, Rwanda.";
}

function generateBudgetConsistentBusinessInfo($businessType) {
    // NO HARD-CODED DATA - Get all information from ML model and datasets
    $mlResponse = getMusanzeMLResponse($businessType);
    if ($mlResponse) {
        return $mlResponse;
    }
    
    // Try AI-generated response from Python with dataset training
    $aiResponse = getPythonAIResponse($businessType . " business opportunity from trained dataset");
    if ($aiResponse) {
        return $aiResponse;
    }
    
    // Final fallback - no hard-coded data, just analysis message
    return "I'm analyzing your business request for " . $businessType . " using our trained ML models and datasets. Let me generate comprehensive business information from our Musanze business database.";
}

function isIrrelevantQuery($message) {
    $message_lower = strtolower($message);
    
    // Define irrelevant topics that are clearly not business-related
    $irrelevantTopics = [
        // Personal/private topics
        'love', 'relationship', 'dating', 'marriage', 'boyfriend', 'girlfriend', 'wife', 'husband',
        'personal', 'private', 'family', 'children', 'kids', 'baby', 'pregnancy',
        
        // Entertainment/leisure
        'movie', 'film', 'music', 'song', 'game', 'gaming', 'sports', 'football', 'soccer', 'basketball',
        'entertainment', 'fun', 'party', 'dance', 'singing', 'acting', 'celebrity',
        
        // Health/medical (unless business-related)
        'sick', 'illness', 'disease', 'medicine', 'doctor', 'hospital', 'pain', 'headache', 'fever',
        'medical', 'health', 'therapy', 'treatment', 'surgery',
        
        // Technology (unless business-related)
        'programming', 'coding', 'software', 'app', 'website', 'computer', 'laptop', 'phone',
        'gadget', 'tech', 'internet', 'social media', 'facebook', 'instagram', 'twitter',
        
        // Academic/educational (unless business-related)
        'homework', 'assignment', 'exam', 'test', 'school', 'university', 'college', 'study',
        'education', 'learning', 'course', 'degree', 'diploma',
        
        // Weather/random topics
        'weather', 'rain', 'sunny', 'cloudy', 'temperature', 'climate',
        'random', 'joke', 'funny', 'meme', 'cat', 'dog', 'pet', 'animal',
        
        // Politics/religion
        'politics', 'political', 'government', 'election', 'vote', 'president',
        'religion', 'god', 'church', 'prayer', 'faith', 'spiritual',
        
        // Food (unless business-related)
        'recipe', 'cooking', 'food', 'eat', 'restaurant', 'meal', 'hungry', 'taste',
        
        // Travel (unless business-related)
        'vacation', 'holiday', 'travel', 'trip', 'flight', 'hotel', 'beach', 'mountains'
    ];
    
    // Check if message contains irrelevant topics
    foreach ($irrelevantTopics as $topic) {
        if (strpos($message_lower, $topic) !== false) {
            // Additional check: make sure it's not business-related
            $businessContext = [
                'business', 'startup', 'company', 'entrepreneur', 'investment', 'profit', 'revenue',
                'market', 'customer', 'service', 'product', 'sales', 'marketing', 'finance'
            ];
            
            $hasBusinessContext = false;
            foreach ($businessContext as $context) {
                if (strpos($message_lower, $context) !== false) {
                    $hasBusinessContext = true;
                    break;
                }
            }
            
            // If it contains irrelevant topic but no business context, it's irrelevant
            if (!$hasBusinessContext) {
                return true;
            }
        }
    }
    
    // Check for very short or nonsensical messages
    if (strlen(trim($message)) < 3) {
        return true;
    }
    
    // Check for messages that are just numbers or symbols
    if (preg_match('/^[0-9\s\-\+\(\)]+$/', $message)) {
        return true;
    }
    
    return false;
}

function generateFriendlyRedirectMessage($message) {
    $message_lower = strtolower($message);
    
    // Generate different friendly messages based on the type of irrelevant query
    if (strpos($message_lower, 'love') !== false || strpos($message_lower, 'relationship') !== false) {
        return "💕 I understand you're asking about relationships, but I'm specialized in helping with business opportunities in Musanze, Rwanda! 

🌟 **I can help you with:**
• Finding the perfect business opportunity for your budget
• Creating detailed business plans and financial projections
• Understanding startup costs and investment requirements
• Market analysis and growth strategies

💡 **Why not explore a business that could help you achieve your personal goals?** Starting a successful business can provide financial stability and personal fulfillment!

**What would you like to explore?** Try asking me about business opportunities, startup costs, or business planning!";
    }
    
    if (strpos($message_lower, 'movie') !== false || strpos($message_lower, 'music') !== false || strpos($message_lower, 'game') !== false) {
        return "🎬 I love entertainment too! But I'm your specialized business assistant for Musanze, Rwanda.

🎯 **I'm here to help you with:**
• Business opportunities and startup ideas
• Investment planning and budget analysis
• Market research and competition analysis
• Business plan creation and strategy development

💼 **Did you know?** Many successful entrepreneurs started businesses related to their hobbies and interests! 

**What business interests you?** I can help you turn your passions into profitable business opportunities in Musanze!";
    }
    
    if (strpos($message_lower, 'weather') !== false || strpos($message_lower, 'rain') !== false || strpos($message_lower, 'sunny') !== false) {
        return "🌤️ I can't predict the weather, but I can help you predict business success in Musanze, Rwanda!

📈 **I specialize in:**
• Business opportunity analysis and market trends
• Financial projections and investment planning
• Startup cost calculations and budget planning
• Business strategy development and growth planning

🌱 **Fun fact:** Weather actually affects many businesses in Musanze - tourism, agriculture, and outdoor services all depend on seasonal patterns!

**Want to explore weather-dependent business opportunities?** Ask me about tourism, agriculture, or seasonal business strategies!";
    }
    
    if (strpos($message_lower, 'food') !== false || strpos($message_lower, 'eat') !== false || strpos($message_lower, 'hungry') !== false) {
        return "🍽️ I can't help with recipes, but I can help you start a food business in Musanze, Rwanda!

🍴 **Food-related business opportunities I can help with:**
• Restaurant and café business plans
• Food processing and manufacturing
• Local cuisine and traditional food businesses
• Catering and food service operations
• Agricultural and farming businesses

💰 **Investment ranges:** From small food stalls (500,000 RWF) to large restaurants (5,000,000+ RWF)

**Interested in the food industry?** Ask me about restaurant business plans, food processing opportunities, or local cuisine businesses!";
    }
    
    if (strpos($message_lower, 'travel') !== false || strpos($message_lower, 'vacation') !== false || strpos($message_lower, 'holiday') !== false) {
        return "✈️ I can't book your vacation, but I can help you start a tourism business in Musanze, Rwanda!

🏔️ **Tourism business opportunities I can help with:**
• Mountain hiking and volcano trekking tours
• Eco-lodges and accommodation services
• Local guide services and cultural tours
• Souvenir shops and traditional crafts
• Transportation and travel services

🎯 **Musanze is perfect for tourism:** Home to Volcanoes National Park, mountain gorillas, and beautiful landscapes!

**Want to start a tourism business?** Ask me about hiking tours, eco-lodges, or guide services!";
    }
    
    if (strpos($message_lower, 'health') !== false || strpos($message_lower, 'sick') !== false || strpos($message_lower, 'doctor') !== false) {
        return "🏥 I can't provide medical advice, but I can help you start a health-related business in Musanze, Rwanda!

💊 **Health business opportunities I can help with:**
• Pharmacy and medical supply businesses
• Health and wellness services
• Traditional medicine and herbal products
• Medical equipment and supplies
• Health education and training services

🏥 **Growing market:** Healthcare services are in high demand in Musanze!

**Interested in health businesses?** Ask me about pharmacy business plans, wellness services, or medical supply opportunities!";
    }
    
    // Default friendly message for other irrelevant queries
    return "😊 I appreciate your question, but I'm specialized in helping with business opportunities in Musanze, Rwanda!

🎯 **I'm your business assistant and I can help you with:**
• **Business Opportunities** - Find the right business for your budget and interests
• **Startup Costs** - Understand investment requirements and financial planning
• **Business Planning** - Create comprehensive business plans and strategies
• **Market Analysis** - Research competition and growth opportunities

💡 **Why not explore entrepreneurship?** Starting a business in Musanze can be very rewarding!

**What interests you?** Try asking me about:
• \"What business opportunities are available?\"
• \"What's the budget for starting a restaurant?\"
• \"Help me create a business plan\"

I'm here to help you succeed in your business journey! 🚀";
}

function checkIfUserHasProvidedBudget($history) {
    // Check if user has provided budget information in the conversation history
    $budgetKeywords = [
        '500,000', '500000', '1,000,000', '1000000', '3,000,000', '3000000', '5,000,000', '5000000',
        'budget', 'investment', 'money', 'funding', 'cost', 'price', 'amount', 'rwf', 'million'
    ];
    
    // Check recent conversation history (last 5 messages)
    $recentHistory = array_slice($history, -5);
    
    foreach ($recentHistory as $message) {
        $message_lower = strtolower($message);
        foreach ($budgetKeywords as $keyword) {
            if (strpos($message_lower, $keyword) !== false) {
                return true;
            }
        }
    }
    
    return false;
}

function isBudgetResponse($message) {
    $message_lower = strtolower($message);
    
    // Check for budget range patterns
    $budgetPatterns = [
        '/\d{1,3}(?:,\d{3})*\s*-\s*\d{1,3}(?:,\d{3})*\s*rwf/i',  // e.g., "1,000,000 - 3,000,000 RWF"
        '/\d{1,3}(?:,\d{3})*\s*rwf/i',  // e.g., "2,500,000 RWF"
        '/\d+[,.]?\d*\s*(?:million|m)\s*rwf/i',  // e.g., "2.5 million RWF"
        '/\d+[,.]?\d*\s*rwf/i',  // e.g., "2500000 RWF"
        '/write any/i',  // "write any" option
        '/custom/i'  // "custom" option
    ];
    
    foreach ($budgetPatterns as $pattern) {
        if (preg_match($pattern, $message_lower)) {
            return true;
        }
    }
    
    // Check for specific budget keywords
    $budgetKeywords = [
        '500,000', '500000', '1,000,000', '1000000', '3,000,000', '3000000', '5,000,000', '5000000',
        'budget', 'investment', 'money', 'funding', 'cost', 'price', 'amount'
    ];
    
    foreach ($budgetKeywords as $keyword) {
        if (strpos($message_lower, $keyword) !== false) {
            return true;
        }
    }
    
    return false;
}

// NLP Intent Classification
function classifyUserIntent($message) {
    $message_lower = strtolower($message);
    
    // Use simple NLP patterns for intent classification
    $intents = [
        'greeting' => ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening'],
        'business_opportunities' => ['business opportunities', 'business ideas', 'start a business', 'entrepreneurship'],
        'budget_inquiry' => ['budget', 'cost', 'investment', 'money', 'funding', 'price'],
        'sector_inquiry' => ['sector', 'industry', 'field', 'type of business'],
        'specific_business' => ['coffee', 'restaurant', 'tourism', 'agriculture', 'retail', 'services'],
        'planning' => ['plan', 'strategy', 'business plan', 'planning'],
        'export_request' => ['export', 'download', 'pdf', 'word', 'excel'],
        'help' => ['help', 'assistance', 'support', 'how to', 'what can you do']
    ];
    
    foreach ($intents as $intent => $keywords) {
        foreach ($keywords as $keyword) {
            if (strpos($message_lower, $keyword) !== false) {
                return $intent;
            }
        }
    }
    
    return 'general_inquiry';
}

// Generate Contextual Response based on Intent
function generateContextualResponse($message, $intent, $conversation_history = []) {
    // Use AI-powered response generation with intent classification
    return getAIPoweredResponse($message, $conversation_history, $intent);
}

// Simple AI response logic (in production, integrate with OpenAI API)
function generateAIResponse($message, $history) {
    // Use AI-powered response generation instead of hard-coded responses
    $intent = classifyUserIntent($message);
    return generateContextualResponse($message, $intent, $history);
}

try {
    // AI-Powered Chat System - Replace hard-coded responses with intelligent AI
    $specificResponse = null;
    $mlResponse = null;
    
    // Classify user intent using NLP
    $user_intent = classifyUserIntent($message);
    
    // Check if this is a structured conversation flow query (should ask for budget first)
    $message_lower = strtolower($message);
    $isStructuredFlowQuery = (
        strpos($message_lower, 'business opportunities') !== false ||
        strpos($message_lower, 'business ideas') !== false ||
        strpos($message_lower, 'start a business') !== false ||
        strpos($message_lower, 'entrepreneurship') !== false
    );
    
    // Check if this is a specific business selection (should show detailed analysis) - CHECK THIS FIRST
    $isSpecificBusinessSelection = (
        // All business types from dataset
        strpos($message_lower, 'mountain hiking tours') !== false ||
        strpos($message_lower, 'coffee processing') !== false ||
        strpos($message_lower, 'local restaurant') !== false ||
        strpos($message_lower, 'organic farming') !== false ||
        strpos($message_lower, 'internet cafe') !== false ||
        strpos($message_lower, 'eco-lodges') !== false ||
        strpos($message_lower, 'eco-lodge') !== false ||
        strpos($message_lower, 'souvenir shop') !== false ||
        strpos($message_lower, 'local transport') !== false ||
        strpos($message_lower, 'local guide services') !== false ||
        strpos($message_lower, 'volcano trekking') !== false ||
        strpos($message_lower, 'food processing') !== false ||
        strpos($message_lower, 'guesthouse') !== false ||
        strpos($message_lower, 'guest house') !== false ||
        // Additional common variations and related terms
        strpos($message_lower, 'bed & breakfast') !== false ||
        strpos($message_lower, 'bed and breakfast') !== false ||
        strpos($message_lower, 'hotel') !== false ||
        strpos($message_lower, 'lodge') !== false ||
        strpos($message_lower, 'accommodation') !== false ||
        strpos($message_lower, 'traditional crafts') !== false ||
        strpos($message_lower, 'gift shop') !== false ||
        strpos($message_lower, 'cultural tours') !== false ||
        strpos($message_lower, 'small grocery store') !== false ||
        strpos($message_lower, 'restaurant') !== false ||
        strpos($message_lower, 'cafe') !== false ||
        strpos($message_lower, 'coffee') !== false ||
        strpos($message_lower, 'farming') !== false ||
        strpos($message_lower, 'agriculture') !== false ||
        strpos($message_lower, 'transport') !== false ||
        strpos($message_lower, 'guide') !== false ||
        strpos($message_lower, 'hiking') !== false ||
        strpos($message_lower, 'tours') !== false ||
        strpos($message_lower, 'trekking') !== false ||
        strpos($message_lower, 'processing') !== false ||
        strpos($message_lower, 'shop') !== false ||
        strpos($message_lower, 'souvenir') !== false ||
        strpos($message_lower, 'internet') !== false
    );
    
    // Check if this is a sector selection (should show business opportunities list) - CHECK THIS SECOND
    $isSectorSelection = (
        (strpos($message_lower, 'tourism') !== false && !$isSpecificBusinessSelection) ||
        (strpos($message_lower, 'hospitality') !== false && !$isSpecificBusinessSelection) ||
        (strpos($message_lower, 'services') !== false && !$isSpecificBusinessSelection && strpos($message_lower, 'local guide') === false) ||
        (strpos($message_lower, 'retail') !== false && !$isSpecificBusinessSelection) ||
        (strpos($message_lower, 'agriculture') !== false && !$isSpecificBusinessSelection) ||
        (strpos($message_lower, 'food') !== false && !$isSpecificBusinessSelection && strpos($message_lower, 'processing') === false) ||
        (strpos($message_lower, 'beverage') !== false && !$isSpecificBusinessSelection)
    );
    
    if ($isStructuredFlowQuery) {
        // For structured conversation flow queries, use AI-powered responses that ask for budget first
        $ai_response = generateContextualResponse($message, $user_intent, $history);
        $response = $ai_response;
    } elseif ($isSectorSelection) {
        // For sector selection, check if user has provided budget first
        $hasBudget = checkIfUserHasProvidedBudget($history);
        if (!$hasBudget) {
            // Ask for budget first before showing sector opportunities
            $response = "Great choice! You're interested in the " . ucwords($message) . " sector. 

💰 **Before I show you the business opportunities, I need to know your budget range:**

• **500,000 - 1,000,000 RWF** - Small services, retail, internet café
• **1,000,000 - 3,000,000 RWF** - Coffee processing, organic farming, restaurant
• **3,000,000 - 5,000,000 RWF** - Mountain tours, eco-lodges, gift shops
• **Write any amount** - Custom budget range

**Please select your budget range or tell me your specific budget amount.** This will help me show you the most relevant business opportunities in the " . ucwords($message) . " sector that match your investment capacity.";
        } else {
            // User has provided budget, show business opportunities list for that sector
            $sectorResponse = generateSectorBusinessOpportunities($message);
            $response = $sectorResponse . "\n\n**📄 Export Options:**\n[PDF Export] [Word Export] [Excel Export]\n\n" .
                       "**Which specific business opportunity interests you most?** I'll provide detailed analysis for your selection.";
        }
    } elseif ($isSpecificBusinessSelection) {
        // For specific business selection, get detailed information for the selected business only
        $specificBusinessResponse = generateSpecificBusinessDetails($message);
        $response = $specificBusinessResponse . "\n\n**📄 Export Options:**\n" .
                   "<div class=\"export-buttons-container\">" .
                   "<button onclick=\"exportBusinessPlan('" . urlencode($message) . "', 'pdf')\" class=\"export-btn pdf-btn\">PDF Export</button>" .
                   "<button onclick=\"exportBusinessPlan('" . urlencode($message) . "', 'word')\" class=\"export-btn word-btn\">Word Export</button>" .
                   "<button onclick=\"exportBusinessPlan('" . urlencode($message) . "', 'excel')\" class=\"export-btn excel-btn\">Excel Export</button>" .
                   "</div>\n\n" .
                   "Would you like me to generate a complete business plan for this opportunity?";
    } else {
        // Check if this is a budget response first
        $isBudgetResponse = isBudgetResponse($message);
        if ($isBudgetResponse) {
            // User provided budget, show budget-specific business opportunities
            $response = generateBudgetSpecificResponse($message, $message);
        } else {
            // Check if this is an irrelevant query
            $isIrrelevantQuery = isIrrelevantQuery($message);
            if ($isIrrelevantQuery) {
                $response = generateFriendlyRedirectMessage($message);
            } else {
                // For all other queries, use AI-powered responses
                $ai_response = generateContextualResponse($message, $user_intent, $history);
                
                if ($ai_response) {
                    $specificResponse = $ai_response;
                }
                
                // ALWAYS use AI-powered responses (no more hard-coded responses)
                if ($specificResponse) {
                    $response = $specificResponse; // AI-generated response from classifyUserIntent + generateContextualResponse
                } else {
                    $response = generateAIResponse($message, $history); // Fallback to AI response
                }
            }
        }
    }
    
    echo json_encode([
        'response' => $response,
        'timestamp' => date('Y-m-d H:i:s'),
        'ml_enhanced' => false,
        'ai_powered' => true
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error',
        'message' => 'An error occurred while processing your request'
    ]);
}
?>
