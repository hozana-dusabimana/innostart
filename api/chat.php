1<?php
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

if (!$input || !isset($input['message'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Message is required']);
    exit();
}

$message = trim($input['message']);
$history = $input['history'] ?? [];

// Simple AI response logic (in production, integrate with OpenAI API)
function generateAIResponse($message, $history) {
    $message = strtolower($message);
    
    // Business-related responses
    if (strpos($message, 'business plan') !== false || strpos($message, 'write a business plan') !== false) {
        return "I'll help you create a comprehensive business plan! Here's the structure you need:\n\n📋 **Essential Sections:**\n1. **Executive Summary** - Overview of your business\n2. **Company Description** - What you do and why\n3. **Market Analysis** - Target customers and competition\n4. **Organization Structure** - Team and management\n5. **Service/Product Line** - What you're selling\n6. **Marketing Strategy** - How you'll reach customers\n7. **Financial Projections** - Revenue, costs, and profits\n\n💡 **Pro Tips:**\n• Keep it concise (10-20 pages)\n• Use data and research to support claims\n• Include realistic financial projections\n• Update it regularly as your business grows\n\nWould you like me to help you with any specific section?";
    }
    
    if (strpos($message, 'funding') !== false || strpos($message, 'investment') !== false || strpos($message, 'money') !== false) {
        return "Here are the main funding options for startups:\n\n💰 **Self-Funding (Bootstrapping):**\n• Use personal savings\n• Reinvest profits\n• Keep full control\n• Best for: Small businesses, service companies\n\n👥 **Angel Investors:**\n• Individual investors\n• $25K - $500K typically\n• Provide mentorship\n• Best for: Early-stage startups\n\n🏢 **Venture Capital:**\n• Professional investors\n• $500K+ typically\n• Expect high returns\n• Best for: High-growth tech companies\n\n🏦 **Bank Loans:**\n• Traditional financing\n• Requires collateral\n• Fixed repayment terms\n• Best for: Established businesses\n\n🌐 **Crowdfunding:**\n• Online platforms (Kickstarter, GoFundMe)\n• Pre-sell products\n• Build customer base\n• Best for: Product-based businesses\n\nWhat's your business stage and funding needs?";
    }
    
    if (strpos($message, 'market research') !== false) {
        return "Market research is crucial for understanding your customers and competition. Key steps include: defining your target market, analyzing competitors, conducting surveys/interviews, studying industry trends, and identifying market gaps. Would you like help with any specific aspect of market research?";
    }
    
    if (strpos($message, 'marketing') !== false) {
        return "Effective marketing strategies include: social media marketing, content marketing, SEO, email campaigns, partnerships, and local advertising. The best approach depends on your target audience and budget. What type of business are you planning to start?";
    }
    
    if (strpos($message, 'financial') !== false || strpos($message, 'budget') !== false) {
        return "Financial planning for startups should include: startup costs, operating expenses, revenue projections, cash flow analysis, and break-even calculations. I can help you create financial projections using our calculator tool. Would you like to try it?";
    }
    
    if (strpos($message, 'legal') !== false || strpos($message, 'registration') !== false) {
        return "Legal considerations for startups include: business registration, licenses and permits, tax obligations, intellectual property protection, contracts, and insurance. Requirements vary by location and business type. What specific legal aspect are you concerned about?";
    }
    
    if (strpos($message, 'team') !== false || strpos($message, 'hiring') !== false) {
        return "Building a strong team is essential for startup success. Consider: defining roles and responsibilities, creating job descriptions, networking, using recruitment platforms, offering competitive packages, and fostering company culture. What positions are you looking to fill?";
    }
    
    // Specific technology business ideas
    if (strpos($message, 'website development') !== false || strpos($message, 'web development') !== false) {
        return "Great choice! Website development is a profitable business. Here are specific ideas:\n\n💻 **Web Development Services:**\n• Custom website design and development\n• E-commerce websites (online stores)\n• Business websites with CMS\n• Portfolio websites for professionals\n• Restaurant websites with online ordering\n• Real estate websites with property listings\n\n🎯 **Target Markets:**\n• Small businesses needing online presence\n• Restaurants wanting online ordering\n• Real estate agents\n• Freelancers and consultants\n• Non-profit organizations\n\n💰 **Pricing:**\n• Basic websites: $500-$2,000\n• E-commerce sites: $2,000-$10,000\n• Custom applications: $5,000+\n\nWould you like guidance on getting started or finding clients?";
    }
    
    if (strpos($message, 'automation software') !== false || strpos($message, 'automation') !== false) {
        return "Excellent! Automation software is a high-demand business. Here are specific opportunities:\n\n🤖 **Automation Software Ideas:**\n• Business process automation (BPA)\n• Social media scheduling tools\n• Email marketing automation\n• Inventory management systems\n• Customer service chatbots\n• Data entry automation\n• Workflow management tools\n• HR process automation\n\n🎯 **Target Industries:**\n• Small businesses wanting efficiency\n• E-commerce stores\n• Real estate agencies\n• Healthcare practices\n• Educational institutions\n• Manufacturing companies\n\n💰 **Business Models:**\n• SaaS (Software as a Service) - $29-$299/month\n• One-time software sales - $500-$5,000\n• Custom automation projects - $2,000-$50,000\n• Consulting and implementation services\n\nWhat type of automation interests you most?";
    }
    
    if (strpos($message, 'mobile app') !== false || strpos($message, 'app development') !== false) {
        return "Mobile app development is a lucrative business! Here are specific opportunities:\n\n📱 **App Development Ideas:**\n• Business productivity apps\n• E-commerce mobile apps\n• Food delivery apps\n• Fitness and health apps\n• Educational apps\n• Social networking apps\n• Utility apps (calculators, converters)\n• Gaming apps\n\n🎯 **Target Markets:**\n• Local businesses wanting mobile presence\n• Startups needing MVP apps\n• Enterprises wanting employee apps\n• Non-profits needing engagement apps\n\n💰 **Pricing:**\n• Simple apps: $5,000-$15,000\n• Complex apps: $15,000-$100,000+\n• Maintenance: $500-$2,000/month\n\nWhat type of app are you interested in developing?";
    }
    
    if (strpos($message, 'technology') !== false || strpos($message, 'tech') !== false) {
        return "Technology can give your startup a competitive edge. Consider: website development, mobile apps, CRM systems, analytics tools, automation software, and cloud services. What technology needs does your business have?";
    }
    
    if (strpos($message, 'tourism') !== false || strpos($message, 'travel') !== false || strpos($message, 'hospitality') !== false) {
        return "Great choice! Tourism and hospitality is a thriving industry. Here are specific business ideas:\n\n🏔️ **Tourism & Hospitality Business Ideas:**\n• Tour guide services (city tours, nature tours)\n• Bed & breakfast or guesthouse\n• Restaurant or café with local cuisine\n• Travel agency or booking service\n• Adventure tourism (hiking, biking, water sports)\n• Cultural experiences and workshops\n• Transportation services (airport shuttles, city tours)\n• Souvenir and gift shops\n\n🎯 **Target Markets:**\n• International tourists\n• Local weekend travelers\n• Business travelers\n• Adventure seekers\n• Cultural enthusiasts\n• Food lovers\n\n💰 **Revenue Streams:**\n• Direct bookings and reservations\n• Commission from tour bookings\n• Food and beverage sales\n• Souvenir and merchandise sales\n• Transportation fees\n• Workshop and experience fees\n\nWhat type of tourism business interests you most?";
    }
    
    if (strpos($message, 'competition') !== false || strpos($message, 'competitor') !== false) {
        return "Competitive analysis helps you understand your market position. Research: direct and indirect competitors, their strengths and weaknesses, pricing strategies, marketing approaches, and customer reviews. This information helps you differentiate your business.";
    }
    
    if (strpos($message, 'hello') !== false || strpos($message, 'hi') !== false) {
        return "Hello! I'm InnoStart AI, your business assistant. I'm here to help you with business planning, idea generation, financial projections, and startup guidance. What would you like to explore today?";
    }
    
    if (strpos($message, 'help') !== false) {
        return "I can help you with various aspects of starting a business: business plan creation, market research, financial planning, funding strategies, marketing advice, legal considerations, and more. Just ask me about any specific topic you'd like to learn about!";
    }
    
    // Technology business ideas requests
    if (strpos($message, 'technology business idea') !== false || strpos($message, 'tech business idea') !== false) {
        return "Here are specific technology business ideas you can start:\n\n💻 **Web Development:**\n• Custom website design and development\n• E-commerce website creation\n• WordPress theme development\n• Web application development\n\n📱 **Mobile Apps:**\n• Business productivity apps\n• E-commerce mobile apps\n• Utility apps (calculators, converters)\n• Educational apps\n\n🤖 **Automation & Software:**\n• Business process automation\n• Social media management tools\n• Email marketing automation\n• Inventory management systems\n\n☁️ **Cloud Services:**\n• Cloud migration consulting\n• Data backup solutions\n• Cloud security services\n• Remote work tools\n\n🎯 **Digital Marketing:**\n• SEO services\n• Social media management\n• Content marketing\n• PPC advertising management\n\nWhich technology area interests you most? I can provide detailed guidance!";
    }
    
    // Business ideas requests
    if (strpos($message, 'business ideas') !== false || strpos($message, 'give me ideas') !== false || strpos($message, 'ideas') !== false) {
        // Check for specific locations
        if (strpos($message, 'musanze') !== false) {
            return "Great! Here are specific business ideas for Musanze, Rwanda:\n\n🏔️ **Tourism & Hospitality:**\n• Mountain hiking guide services\n• Eco-lodges and guesthouses\n• Cultural tourism experiences\n• Volcano trekking packages\n\n🌱 **Agriculture & Food:**\n• Organic vegetable farming\n• Coffee processing and export\n• Local food restaurants\n• Agricultural equipment rental\n\n🏪 **Retail & Services:**\n• Mobile money services\n• Internet café with printing\n• Motorcycle taxi services\n• Local grocery stores\n\n💻 **Technology:**\n• Mobile app development\n• Digital marketing services\n• Online education platforms\n• E-commerce for local products\n\nWhich of these interests you most? I can provide detailed guidance!";
        }
        
        if (strpos($message, 'kigali') !== false) {
            return "Here are business ideas for Kigali, Rwanda:\n\n🏢 **Tech & Innovation:**\n• Software development company\n• Mobile app development\n• Digital marketing agency\n• E-commerce platforms\n\n🍽️ **Food & Beverage:**\n• Restaurant chains\n• Food delivery services\n• Catering businesses\n• Coffee shops\n\n🚗 **Transportation:**\n• Ride-sharing services\n• Logistics and delivery\n• Car rental services\n• Public transport solutions\n\n🏥 **Healthcare:**\n• Telemedicine platforms\n• Health clinics\n• Pharmaceutical distribution\n• Medical equipment sales\n\nWhich sector interests you?";
        }
        
        // General business ideas
        return "Here are some profitable business ideas you can start:\n\n💻 **Technology:**\n• Mobile app development\n• Website design services\n• Digital marketing agency\n• E-commerce store\n\n🍽️ **Food & Beverage:**\n• Restaurant or café\n• Food delivery service\n• Catering business\n• Food truck\n\n🏪 **Retail & Services:**\n• Online store\n• Consulting services\n• Event planning\n• Cleaning services\n\n🌱 **Agriculture:**\n• Organic farming\n• Food processing\n• Agricultural consulting\n• Farm-to-table delivery\n\nWhat type of business interests you most? I can provide specific guidance!";
    }
    
    // Default responses for general business questions
    $defaultResponses = [
        "I'd be happy to help you with business ideas! Here are some popular categories to explore:\n\n💻 **Technology:** Website development, mobile apps, automation software\n🏔️ **Tourism:** Tour guide services, accommodation, restaurants\n🌱 **Agriculture:** Organic farming, food processing, farm-to-table\n🏪 **Retail:** Online stores, consulting services, event planning\n💰 **Finance:** Financial consulting, investment services, fintech\n\nWhich category interests you most?",
        
        "Great question! Let me suggest some trending business opportunities:\n\n📱 **Digital Services:** Social media management, content creation, online tutoring\n🍽️ **Food & Beverage:** Food delivery, catering, specialty restaurants\n🏥 **Health & Wellness:** Fitness coaching, mental health services, wellness products\n🎓 **Education:** Online courses, skill training, educational apps\n🌍 **Sustainability:** Green energy, eco-friendly products, waste management\n\nWhat type of business are you considering?",
        
        "I can help you explore various business opportunities! Here are some proven business models:\n\n🛒 **E-commerce:** Online retail, dropshipping, digital products\n🏢 **B2B Services:** Consulting, software solutions, professional services\n👥 **Marketplace:** Connecting buyers and sellers, platform businesses\n🏭 **Manufacturing:** Product creation, custom manufacturing, local production\n🎯 **Niche Services:** Specialized services for specific industries or demographics\n\nWhich business model appeals to you?",
        
        "That's an exciting question! Here are some high-potential business ideas:\n\n🤖 **Automation:** Business process automation, workflow optimization\n🌐 **Remote Services:** Virtual assistance, remote consulting, online coaching\n🏠 **Home Services:** Cleaning, maintenance, home improvement\n🚚 **Logistics:** Delivery services, supply chain solutions, last-mile delivery\n💡 **Innovation:** New product development, technology solutions, creative services\n\nWhat area would you like to explore further?",
        
        "I'm here to help you find the perfect business opportunity! Consider these factors:\n\n🎯 **Your Skills:** What are you good at? What do you enjoy doing?\n💰 **Investment:** How much capital do you have to start?\n⏰ **Time:** How much time can you dedicate to your business?\n🌍 **Location:** Where do you want to operate? Local, national, or global?\n📈 **Growth:** Do you want a lifestyle business or high-growth startup?\n\nTell me more about your preferences and I'll suggest specific ideas!"
    ];
    
    return $defaultResponses[array_rand($defaultResponses)];
}

try {
    $response = generateAIResponse($message, $history);
    
    echo json_encode([
        'response' => $response,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error',
        'message' => 'An error occurred while processing your request'
    ]);
}
?>

