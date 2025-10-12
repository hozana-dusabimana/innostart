<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Load Musanze ML Model
function loadMusanzeModel() {
    $model_path = '../ml_models/musanze_smart_model.py';
    if (file_exists($model_path)) {
        return true;
    }
    return false;
}

// Get ML prediction for ALL business-related queries
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
        strpos($message_lower, 'cafe') !== false ||
        strpos($message_lower, 'mobile') !== false ||
        strpos($message_lower, 'money') !== false ||
        strpos($message_lower, 'traditional') !== false ||
        strpos($message_lower, 'equipment') !== false ||
        strpos($message_lower, '1-5m') !== false ||
        strpos($message_lower, '5-15m') !== false ||
        strpos($message_lower, '15-50m') !== false ||
        strpos($message_lower, '50m+') !== false ||
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
    
    // Business plan structure (general guidance only)
    if (strpos($message, 'business plan structure') !== false || strpos($message, 'business plan sections') !== false) {
        return "I'll help you create a comprehensive business plan! Here's the structure you need:\n\n📋 **Essential Sections:**\n1. **Executive Summary** - Overview of your business\n2. **Company Description** - What you do and why\n3. **Market Analysis** - Target customers and competition\n4. **Organization Structure** - Team and management\n5. **Service/Product Line** - What you're selling\n6. **Marketing Strategy** - How you'll reach customers\n7. **Financial Projections** - Revenue, costs, and profits\n\n💡 **Pro Tips:**\n• Keep it concise (10-20 pages)\n• Use data and research to support claims\n• Include realistic financial projections\n• Update it regularly as your business grows\n\nWould you like me to help you with any specific section?";
    }
    
    if (strpos($message, 'funding') !== false || strpos($message, 'investment') !== false || strpos($message, 'money') !== false) {
        return "Here are the main funding options for startups:\n\n💰 **Self-Funding (Bootstrapping):**\n• Use personal savings\n• Reinvest profits\n• Keep full control\n• Best for: Small businesses, service companies\n\n👥 **Angel Investors:**\n• Individual investors\n• 25,000,000 - 500,000,000 RWF typically\n• Provide mentorship\n• Best for: Early-stage startups\n\n🏢 **Venture Capital:**\n• Professional investors\n• 500,000,000+ RWF typically\n• Expect high returns\n• Best for: High-growth tech companies\n\n🏦 **Bank Loans:**\n• Traditional financing\n• Requires collateral\n• Fixed repayment terms\n• Best for: Established businesses\n\n🌐 **Crowdfunding:**\n• Online platforms (Kickstarter, GoFundMe)\n• Pre-sell products\n• Build customer base\n• Best for: Product-based businesses\n\nWhat's your business stage and funding needs?";
    }
    
    if (strpos($message, 'market research') !== false) {
        return "Market research is crucial for understanding your customers and competition. Key steps include: defining your target market, analyzing competitors, conducting surveys/interviews, studying industry trends, and identifying market gaps. Would you like help with any specific aspect of market research?";
    }
    
    // Marketing responses are handled by specific business type responses below
    
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
        return "Great choice! Website development is a profitable business. Here are specific ideas:\n\n💻 **Web Development Services:**\n• Custom website design and development\n• E-commerce websites (online stores)\n• Business websites with CMS\n• Portfolio websites for professionals\n• Restaurant websites with online ordering\n• Real estate websites with property listings\n\n🎯 **Target Markets:**\n• Small businesses needing online presence\n• Restaurants wanting online ordering\n• Real estate agents\n• Freelancers and consultants\n• Non-profit organizations\n\n💰 **Pricing:**\n• Basic websites: 500,000-2,000,000 RWF\n• E-commerce sites: 2,000,000-10,000,000 RWF\n• Custom applications: 5,000,000+ RWF\n\nWould you like guidance on getting started or finding clients?";
    }
    
    if (strpos($message, 'automation software') !== false || strpos($message, 'automation') !== false) {
        return "Excellent! Automation software is a high-demand business. Here are specific opportunities:\n\n🤖 **Automation Software Ideas:**\n• Business process automation (BPA)\n• Social media scheduling tools\n• Email marketing automation\n• Inventory management systems\n• Customer service chatbots\n• Data entry automation\n• Workflow management tools\n• HR process automation\n\n🎯 **Target Industries:**\n• Small businesses wanting efficiency\n• E-commerce stores\n• Real estate agencies\n• Healthcare practices\n• Educational institutions\n• Manufacturing companies\n\n💰 **Business Models:**\n• SaaS (Software as a Service) - 29,000-299,000 RWF/month\n• One-time software sales - 500,000-5,000,000 RWF\n• Custom automation projects - 2,000,000-50,000,000 RWF\n• Consulting and implementation services\n\nWhat type of automation interests you most?";
    }
    
    if (strpos($message, 'mobile app') !== false || strpos($message, 'app development') !== false) {
        return "Mobile app development is a lucrative business! Here are specific opportunities:\n\n📱 **App Development Ideas:**\n• Business productivity apps\n• E-commerce mobile apps\n• Food delivery apps\n• Fitness and health apps\n• Educational apps\n• Social networking apps\n• Utility apps (calculators, converters)\n• Gaming apps\n\n🎯 **Target Markets:**\n• Local businesses wanting mobile presence\n• Startups needing MVP apps\n• Enterprises wanting employee apps\n• Non-profits needing engagement apps\n\n💰 **Pricing:**\n• Simple apps: 5,000,000-15,000,000 RWF\n• Complex apps: 15,000,000-100,000,000+ RWF\n• Maintenance: 500,000-2,000,000 RWF/month\n\nWhat type of app are you interested in developing?";
    }
    
    if (strpos($message, 'digital services') !== false) {
        return "Excellent choice! Digital services are in high demand. Here are specific opportunities:\n\n💻 **Digital Marketing Services:**\n• Social media management and content creation\n• SEO and website optimization\n• Google Ads and Facebook advertising\n• Email marketing campaigns\n• Influencer marketing coordination\n\n📱 **Web & App Development:**\n• Custom website design and development\n• E-commerce platform creation\n• Mobile app development\n• WordPress theme customization\n• Web application development\n\n🎨 **Creative Services:**\n• Graphic design and branding\n• Video production and editing\n• Photography services\n• Logo and brand identity design\n• Content writing and copywriting\n\n💰 **Pricing Examples:**\n• Social media management: 500,000-3,000,000 RWF/month\n• Website development: 1,000,000-10,000,000 RWF\n• SEO services: 500,000-2,000,000 RWF/month\n• Graphic design: 50,000-200,000 RWF/hour\n\nWhich digital service interests you most? I can provide detailed guidance!";
    }
    
    if (strpos($message, 'technology') !== false || strpos($message, 'tech') !== false) {
        return "Technology can give your startup a competitive edge. Consider: website development, mobile apps, CRM systems, analytics tools, automation software, and cloud services. What technology needs does your business have?";
    }
    
    if (strpos($message, 'restaurant') !== false || strpos($message, 'café') !== false || strpos($message, 'cafe') !== false) {
        return "Excellent choice! A restaurant or café with local cuisine is a fantastic business opportunity. Here's your complete startup guide:\n\n🍽️ **Restaurant/Café Business Plan:**\n\n**1. Concept & Menu:**\n• Focus on authentic local cuisine\n• Create signature dishes and drinks\n• Offer both traditional and modern interpretations\n• Include vegetarian/vegan options\n• Develop seasonal menu variations\n\n**2. Location Strategy:**\n• Tourist areas with high foot traffic\n• Near hotels, attractions, or cultural sites\n• Accessible parking and public transport\n• Consider outdoor seating for better ambiance\n\n**3. Target Customers:**\n• International tourists seeking authentic experiences\n• Local food enthusiasts\n• Business travelers\n• Cultural tourists and food bloggers\n• Local residents celebrating special occasions\n\n**4. Startup Costs (Estimated):**\n• Kitchen equipment: 15,000,000-50,000,000 RWF\n• Interior design & furniture: 10,000,000-30,000,000 RWF\n• Initial inventory: 5,000,000-15,000,000 RWF\n• Licenses & permits: 2,000,000-5,000,000 RWF\n• Marketing & branding: 3,000,000-10,000,000 RWF\n• Working capital: 10,000,000-25,000,000 RWF\n\n**5. Revenue Streams:**\n• Dine-in sales (main revenue)\n• Takeaway and delivery services\n• Catering for events and tours\n• Cooking classes and food experiences\n• Merchandise (spices, sauces, cookbooks)\n• Private dining and special events\n\n**6. Marketing Strategy:**\n• Social media showcasing local dishes\n• Partner with tour operators and hotels\n• Food blogger and influencer collaborations\n• Local food festivals and events\n• Online delivery platforms\n\n**7. Success Tips:**\n• Hire local chefs who know traditional recipes\n• Source ingredients from local suppliers\n• Create an authentic cultural atmosphere\n• Train staff in local food history and stories\n• Offer cooking demonstrations for tourists\n\nWould you like me to help you with any specific aspect, such as menu planning, location selection, or financial projections?";
    }
    
    if (strpos($message, 'tourism') !== false || strpos($message, 'travel') !== false || strpos($message, 'hospitality') !== false) {
        return "Great choice! Tourism and hospitality is a thriving industry. Here are specific business ideas:\n\n🏔️ **Tourism & Hospitality Business Ideas:**\n• Tour guide services (city tours, nature tours)\n• Bed & breakfast or guesthouse\n• Restaurant or café with local cuisine\n• Travel agency or booking service\n• Adventure tourism (hiking, biking, water sports)\n• Cultural experiences and workshops\n• Transportation services (airport shuttles, city tours)\n• Souvenir and gift shops\n\n🎯 **Target Markets:**\n• International tourists\n• Local weekend travelers\n• Business travelers\n• Adventure seekers\n• Cultural enthusiasts\n• Food lovers\n\n💰 **Revenue Streams:**\n• Direct bookings and reservations\n• Commission from tour bookings\n• Food and beverage sales\n• Souvenir and merchandise sales\n• Transportation fees\n• Workshop and experience fees\n\nWhat type of tourism business interests you most?";
    }
    
    if (strpos($message, 'competition') !== false || strpos($message, 'competitor') !== false) {
        return "Competitive analysis helps you understand your market position. Research: direct and indirect competitors, their strengths and weaknesses, pricing strategies, marketing approaches, and customer reviews. This information helps you differentiate your business.";
    }
    
    if (($message === 'hello' || $message === 'hi' || strpos($message, 'hello ') === 0 || strpos($message, 'hi ') === 0) && 
        strpos($message, 'hiking') === false && strpos($message, 'business') === false) {
        return "Hello! I'm your AI business assistant for Musanze, Rwanda. I can help you explore business opportunities, startup costs, and planning. What would you like to know about?";
    }
    
    // Budget range responses are now handled by ML model from dataset
    


    // Local Transport business specific response
    if (strpos($message, 'local transport') !== false || strpos($message, 'transportation') !== false) {
        return "Excellent choice! Local transport is a thriving business in Musanze. Here's your complete guide:

🚗 **Local Transport Business in Musanze:**

**1. Business Types:**
• **Motorcycle Taxi (Moto):** Most popular, low startup cost
• **Car Taxi Service:** Higher investment, premium service
• **Minibus Transport:** Group transport, higher revenue
• **Bicycle Taxi:** Eco-friendly, tourist appeal

**2. Startup Requirements:**
• **Motorcycle:** 800,000-1,500,000 RWF
• **Car:** 8,000,000-15,000,000 RWF
• **Minibus:** 12,000,000-25,000,000 RWF
• **Licenses & Permits:** 200,000-500,000 RWF
• **Insurance:** 100,000-300,000 RWF annually

**3. Revenue Potential:**
• **Motorcycle:** 50,000-150,000 RWF per day
• **Car:** 100,000-300,000 RWF per day
• **Minibus:** 200,000-500,000 RWF per day
• **Monthly Revenue:** 1,500,000-15,000,000 RWF

**4. Target Markets:**
• **Tourists:** Airport transfers, park visits, city tours
• **Locals:** Daily commuting, market trips, business travel
• **Students:** School transport, university routes
• **Business Travelers:** Hotel transfers, meeting transport

**5. Key Locations:**
• **Musanze Town:** High demand, competition
• **Ruhengeri:** Tourist hub, premium pricing
• **Kinigi:** Park access, specialized routes
• **Volcanoes National Park:** Tourist transport

**6. Success Factors:**
• **Reliability:** On-time service, consistent availability
• **Safety:** Good driving record, vehicle maintenance
• **Customer Service:** Friendly, helpful, multilingual
• **Fair Pricing:** Competitive rates, transparent costs

**7. Marketing Strategy:**
• **Hotel Partnerships:** Referral agreements
• **Tourist Information Centers:** Brochures and flyers
• **Social Media:** Instagram, Facebook showcasing services
• **Local Networks:** Word-of-mouth recommendations

**8. Operational Tips:**
• **Vehicle Maintenance:** Regular servicing, safety checks
• **Driver Training:** Customer service, local knowledge
• **Route Planning:** Efficient paths, traffic awareness
• **Safety Equipment:** First aid, emergency contacts

**9. Legal Requirements:**
• **Driver's License:** Valid for vehicle type
• **Business Registration:** RDB registration
• **Tax Registration:** RRA tax compliance
• **Insurance:** Comprehensive vehicle insurance

**10. Growth Opportunities:**
• **Fleet Expansion:** Add more vehicles
• **Route Diversification:** New destinations
• **Service Upgrades:** Premium vehicles, guided tours
• **Technology Integration:** Booking apps, GPS tracking

**11. Financial Projections (5-Year Plan):**
• **Year 1:** 1,500,000-4,500,000 RWF revenue (startup phase)
• **Year 2:** 4,500,000-8,000,000 RWF revenue (growth phase)
• **Year 3:** 8,000,000-15,000,000 RWF revenue (expansion phase)
• **Year 4:** 15,000,000-25,000,000 RWF revenue (maturity phase)
• **Year 5:** 25,000,000-40,000,000 RWF revenue (optimization phase)
• **Break-even Point:** 4-8 months
• **ROI:** 250-400% by Year 3

**12. Business Plan Generation:**
📋 **Generate Complete Business Plan** - Click to create a comprehensive business plan including:
• Executive Summary & Company Description
• Market Analysis & Competitive Analysis
• Financial Projections & Cash Flow Analysis
• Marketing Strategy & Sales Forecasts
• Operations Plan & Management Structure
• Risk Analysis & Mitigation Strategies

💼 **Export Options:** PDF, Word, Excel, PowerPoint formats available

Would you like details on vehicle selection, route planning, marketing strategies, or generate a complete business plan?";
    }
    
    // Souvenir Shop business specific response
    if (strpos($message, 'souvenir shop') !== false || strpos($message, 'gift shop') !== false) {
        return "Great choice! Souvenir shops are highly profitable in Musanze's tourism market. Here's your complete guide:

🎁 **Souvenir Shop Business in Musanze:**

**1. Product Categories:**
• **Traditional Crafts:** Baskets, pottery, wood carvings
• **Coffee Products:** Local coffee beans, branded packaging
• **Textiles:** Traditional clothing, fabrics, accessories
• **Art & Jewelry:** Local artwork, handmade jewelry
• **Tourist Items:** Postcards, magnets, keychains

**2. Startup Investment:**
• **Shop Rent:** 200,000-800,000 RWF per month
• **Initial Inventory:** 2,000,000-8,000,000 RWF
• **Shop Setup:** 1,000,000-3,000,000 RWF
• **Licenses & Permits:** 300,000-600,000 RWF
• **Total Startup:** 3,500,000-12,400,000 RWF

**3. Revenue Potential:**
• **Daily Sales:** 50,000-300,000 RWF
• **Monthly Revenue:** 1,500,000-9,000,000 RWF
• **Tourist Season:** 2-3x higher sales
• **Profit Margin:** 40-60% on most items

**4. Prime Locations:**
• **Kinigi:** Near Volcanoes National Park entrance
• **Ruhengeri:** Tourist hub, high foot traffic
• **Musanze Town:** Central location, local + tourist mix
• **Airport Area:** Last-minute purchases, premium pricing

**5. Target Customers:**
• **International Tourists:** 70% of revenue
• **Local Tourists:** 20% of revenue
• **Expatriates:** 10% of revenue
• **Gift Buyers:** Corporate, personal gifts

**6. Product Sourcing:**
• **Local Artisans:** Direct partnerships, fair trade
• **Cooperatives:** Bulk purchasing, consistent supply
• **Import Items:** Select international products
• **Custom Orders:** Personalized, branded items

**7. Marketing Strategies:**
• **Hotel Partnerships:** In-room catalogs, referral commissions
• **Tour Operator Deals:** Group discounts, package deals
• **Social Media:** Instagram, Facebook showcasing products
• **Tourist Information Centers:** Brochures, maps

**8. Operational Tips:**
• **Inventory Management:** Track fast/slow movers
• **Seasonal Planning:** Stock up for peak seasons
• **Customer Service:** Multilingual staff, cultural knowledge
• **Pricing Strategy:** Competitive but profitable margins

**9. Legal Requirements:**
• **Business Registration:** RDB registration
• **Tax Registration:** RRA compliance
• **Import Permits:** For international products
• **Health Certificates:** For food items (coffee, honey)

**10. Success Factors:**
• **Quality Products:** Authentic, well-crafted items
• **Fair Pricing:** Competitive but sustainable margins
• **Customer Experience:** Friendly service, cultural stories
• **Location:** High visibility, tourist traffic

**11. Growth Opportunities:**
• **Online Sales:** E-commerce, social media selling
• **Wholesale:** Supply to other shops, hotels
• **Custom Manufacturing:** Private label products
• **Export:** International markets, online platforms

**12. Seasonal Considerations:**
• **Peak Season (June-Aug, Dec-Feb):** 3x normal sales
• **Low Season:** Focus on locals, online sales
• **Festival Periods:** Special products, increased demand
• **Weather Impact:** Indoor/outdoor product mix

Would you like details on product sourcing, location selection, or marketing strategies?";
    }
    
    if (strpos($message, 'help') !== false) {
        return "I'm your specialized Musanze business assistant! Here's how I can help you:

🏔️ **Business Ideas & Opportunities:**
• Tourism & hospitality (hiking, eco-lodges, cultural experiences)
• Agriculture & food processing (coffee, organic farming)
• Technology & digital services
• Local services & transportation

💰 **Financial Guidance:**
• Startup costs in RWF for each business type
• Funding options and investment ranges
• Revenue projections and profit margins
• Budget planning and financial planning

📋 **Business Development:**
• Complete business plan creation
• Market research and competitive analysis
• Marketing strategies and customer targeting
• Legal requirements and registration

Just ask me about any specific business idea, budget range, or topic you're interested in!";
    }
    
    // Technology business ideas requests
    if (strpos($message, 'technology business idea') !== false || strpos($message, 'tech business idea') !== false) {
        return "Here are specific technology business ideas you can start:\n\n💻 **Web Development:**\n• Custom website design and development\n• E-commerce website creation\n• WordPress theme development\n• Web application development\n\n📱 **Mobile Apps:**\n• Business productivity apps\n• E-commerce mobile apps\n• Utility apps (calculators, converters)\n• Educational apps\n\n🤖 **Automation & Software:**\n• Business process automation\n• Social media management tools\n• Email marketing automation\n• Inventory management systems\n\n☁️ **Cloud Services:**\n• Cloud migration consulting\n• Data backup solutions\n• Cloud security services\n• Remote work tools\n\n🎯 **Digital Marketing:**\n• SEO services\n• Social media management\n• Content marketing\n• PPC advertising management\n\nWhich technology area interests you most? I can provide detailed guidance!";
    }
    
    // Property selection specific response
    if (strpos($message, 'property selection') !== false || strpos($message, 'location selection') !== false) {
        return "Choosing the right property or location is crucial for your business in Musanze. Here's what to consider:

📍 **Property/Location Selection Guide:**

**1. Business Type Considerations:**
• **Tourism:** Near Volcanoes National Park, scenic views, accessible roads
• **Agriculture:** Fertile volcanic soil, water access, sufficient land size
• **Retail/Services:** High foot traffic areas, town center, near markets

**2. Accessibility Factors:**
• Proximity to main roads and public transport
• Ease of access for suppliers and employees
• Distance to target customer base

**3. Infrastructure Requirements:**
• Availability of utilities (water, electricity, internet)
• Existing buildings or need for new construction
• Parking and storage facilities

**4. Local Regulations:**
• Zoning laws and building permits
• Environmental restrictions
• Consult with Musanze District and RDB

**5. Cost Analysis:**
• Land purchase: 500,000-5,000,000 RWF per hectare
• Rental costs: 50,000-500,000 RWF per month
• Property taxes and maintenance

**6. Location-Specific Tips:**
• **Near Volcanoes National Park:** Premium tourism locations
• **Town Center:** High visibility, foot traffic
• **Rural Areas:** Lower costs, agricultural opportunities

Would you like details on specific locations, legal requirements, or cost breakdowns?";
    }
    
    // Renovation costs specific response
    if (strpos($message, 'renovation costs') !== false || strpos($message, 'renovation expense') !== false || strpos($message, 'construction costs') !== false) {
        return "Renovation and construction costs in Musanze vary based on scope and materials. Here's a detailed breakdown:

🏗️ **Renovation & Construction Costs (RWF):**

**1. Basic Renovation (per square meter):**
• **Minor repairs:** 10,000-30,000 RWF/sqm (painting, flooring)
• **Moderate renovation:** 30,000-70,000 RWF/sqm (plumbing, electrical)
• **Major renovation:** 70,000-150,000 RWF/sqm (structural changes)

**2. New Construction (per square meter):**
• **Basic residential:** 150,000-300,000 RWF/sqm
• **Commercial/Tourism:** 300,000-700,000 RWF/sqm
• **Luxury facilities:** 700,000-1,500,000 RWF/sqm

**3. Specific Business Types:**
• **Restaurant/Café:** 400,000-800,000 RWF/sqm
• **Eco-lodge:** 500,000-1,200,000 RWF/sqm
• **Gift shop:** 200,000-400,000 RWF/sqm
• **Coffee processing:** 300,000-600,000 RWF/sqm

**4. Key Cost Factors:**
• **Materials:** Local vs. imported (20-40% cost difference)
• **Labor:** 15,000-50,000 RWF per day per worker
• **Permits:** 100,000-500,000 RWF
• **Design fees:** 5-10% of total project cost

**5. Cost Management Tips:**
• Get 3-5 quotes from contractors
• Use local materials when possible
• Supervise work closely to avoid delays
• Plan for 10-20% cost overruns

**6. Timeline:**
• **Minor renovation:** 2-4 weeks
• **Moderate renovation:** 1-3 months
• **New construction:** 3-12 months

What type of property or project are you planning?";
    }
    
    // Marketing strategies specific response
    if (strpos($message, 'marketing strategies') !== false || strpos($message, 'marketing plan') !== false || strpos($message, 'promote business') !== false) {
        return "Effective marketing is key to success in Musanze. Here are comprehensive strategies:

📈 **Marketing Strategies for Musanze Businesses:**

**1. Digital Marketing:**
• **Social Media:** Facebook, Instagram showcasing Musanze's beauty
• **Website/SEO:** Optimize for 'Musanze tours', 'Rwanda coffee', etc.
• **Google My Business:** Local SEO optimization
• **Online Travel Agencies:** TripAdvisor, Booking.com for tourism

**2. Local Marketing:**
• **Partnerships:** Hotels, tour operators, local guides
• **Community Events:** Local festivals, markets, cultural events
• **Word-of-Mouth:** Encourage reviews and referrals
• **Local Media:** Radio, newspapers, community boards

**3. Tourism-Specific Marketing:**
• **Tourist Information Centers:** Brochures and displays
• **Hotel Concierge:** Direct referrals and recommendations
• **Airport Marketing:** Welcome materials and displays
• **Travel Bloggers:** Collaborate with travel influencers

**4. Budget Allocation:**
• **Digital Marketing:** 30-40% of marketing budget
• **Local Partnerships:** 25-30% (commissions and fees)
• **Content Creation:** 20-25% (photos, videos, materials)
• **Traditional Marketing:** 10-15% (brochures, events)

**5. Unique Selling Propositions:**
• **Eco-friendly:** Sustainable practices and local sourcing
• **Cultural Immersion:** Authentic Rwandan experiences
• **Premium Quality:** High-end services and products
• **Local Expertise:** Deep knowledge of Musanze region

**6. Success Metrics:**
• **Online Reviews:** Maintain 4.5+ star ratings
• **Social Media:** Regular posting and engagement
• **Customer Retention:** 60%+ repeat customers
• **Referral Rate:** 30%+ new customers from referrals

What type of business are you marketing, and what's your budget range?";
    }
    
    // Generate and export business plan response
    if (strpos($message, 'generate business plan') !== false || strpos($message, 'export business plan') !== false || strpos($message, 'create business plan') !== false) {
        return "Yes! I can help you generate and export a comprehensive business plan. Here's how:

📝 **Generate & Export Business Plan:**

**1. Use the Business Plan Section:**
• Navigate to the 'Business Plan' section in your dashboard
• Fill in all sections with your business details
• I'll provide AI-assisted suggestions based on our Musanze dataset

**2. Business Plan Sections:**
• **Executive Summary:** Overview of your business concept
• **Company Description:** What you do and why
• **Market Analysis:** Target customers and competition
• **Organization Structure:** Team and management
• **Service/Product Line:** What you're offering
• **Marketing Strategy:** How you'll reach customers
• **Financial Projections:** Revenue, costs, and profits

**3. AI-Assisted Content:**
• Ask me specific questions while filling sections
• Example: 'What are typical startup costs for a coffee shop in Musanze?'
• I'll provide data from our 1000-row Musanze dataset
• Get accurate RWF pricing and local market insights

**4. Export Options:**
• **PDF Format:** Professional document for investors
• **Word Document:** Editable format for modifications
• **Excel Spreadsheets:** Financial projections and budgets
• **PowerPoint Presentation:** Investor pitch deck

**5. Financial Projections Included:**
• **5-Year Revenue Forecasts:** Month-by-month projections
• **Break-even Analysis:** When you'll start profiting
• **Cash Flow Statements:** Money in vs. money out
• **ROI Calculations:** Return on investment analysis
• **Sensitivity Analysis:** Best-case, worst-case scenarios
• **PowerPoint:** Presentation format for pitches

**5. Export Features:**
• **Professional Formatting:** Clean, business-ready layout
• **Charts & Graphs:** Visual financial projections
• **Local Data Integration:** Musanze-specific market data
• **RWF Currency:** All costs in Rwandan Francs

**6. Next Steps:**
• Start with the Business Plan section
• Ask me questions as you fill each section
• Review and refine your plan
• Export when complete

Would you like me to guide you through a specific section, or do you have questions about the export process?";
    }
    
    // Souvenir and gift shop specific response
    if (strpos($message, 'souvenir') !== false || strpos($message, 'gift shop') !== false || strpos($message, 'gift') !== false) {
        return "Excellent choice! Souvenir and gift shops in Musanze are very profitable. Here's your complete guide:

🎁 **Souvenir & Gift Shop Business Plan:**

**1. Investment Requirements:**
• **Startup Cost:** 3,000,000-15,000,000 RWF
• **Inventory:** 2,000,000-8,000,000 RWF
• **Store Setup:** 1,000,000-5,000,000 RWF
• **Licenses:** Business registration, tourism license
• **Location:** Tourist areas, near hotels, airport

**2. Product Categories:**
• **Traditional Crafts:** Wooden carvings, baskets, pottery
• **Textiles:** Traditional clothing, fabrics, bags
• **Art & Jewelry:** Local artwork, beaded jewelry
• **Coffee & Tea:** Premium Rwandan coffee, tea blends
• **Books & Maps:** Travel guides, local literature
• **Tourist Essentials:** Postcards, magnets, keychains

**3. Target Customers:**
• International tourists (80% of revenue)
• Local residents buying gifts
• Business travelers
• Cultural enthusiasts
• Online customers

**4. Revenue Projections:**
• **Average Sale:** 5,000-50,000 RWF per customer
• **Daily Sales:** 20-100 customers
• **Daily Revenue:** 100,000-5,000,000 RWF
• **Monthly Revenue:** 3,000,000-150,000,000 RWF

**5. Location Strategy:**
• Near Volcanoes National Park entrance
• Tourist information centers
• Hotel lobbies and gift shops
• Airport departure areas
• Main tourist streets in Musanze

**6. Product Sourcing:**
• Local artisans and craftspeople
• Cooperatives and women's groups
• Direct from coffee farmers
• Import unique African items
• Custom-made products

**7. Marketing Strategy:**
• Partner with tour operators
• Hotel concierge referrals
• Social media showcasing products
• Tourist information center displays
• Online sales and shipping

**8. Success Tips:**
• Learn about each product's cultural significance
• Offer authentic, high-quality items
• Provide excellent customer service
• Support local artisans
• Create unique packaging

Would you like details on product sourcing, location selection, or marketing strategies?";
    }
    
    // Eco-lodge specific response
    if (strpos($message, 'eco-lodge') !== false || strpos($message, 'ecolodge') !== false || strpos($message, 'lodge') !== false) {
        return "Excellent choice! Eco-lodges in Musanze are highly profitable. Here's your complete guide:

🏨 **Eco-Lodge Business Plan:**

**1. Investment Requirements:**
• **Startup Cost:** 50,000,000-200,000,000 RWF
• **Land & Construction:** 30,000,000-120,000,000 RWF
• **Furniture & Equipment:** 10,000,000-40,000,000 RWF
• **Infrastructure:** 5,000,000-20,000,000 RWF
• **Licenses:** Tourism accommodation, environmental permits

**2. Room Types & Pricing:**
• **Standard Rooms:** 100,000-200,000 RWF per night
• **Deluxe Rooms:** 200,000-400,000 RWF per night
• **Family Suites:** 300,000-600,000 RWF per night
• **Peak Season:** 30-50% higher rates

**3. Target Guests:**
• International tourists (gorilla trekking)
• Eco-tourists and nature lovers
• Adventure travelers
• Luxury travelers seeking unique experiences
• Corporate retreats and groups

**4. Revenue Projections:**
• **Occupancy Rate:** 70-90% (peak season), 50-70% (low season)
• **Monthly Revenue:** 15,000,000-60,000,000 RWF
• **Annual Revenue:** 180,000,000-720,000,000 RWF

**5. Key Features:**
• Sustainable construction and operations
• Local cultural experiences
• Guided nature walks and bird watching
• Traditional Rwandan cuisine
• Community involvement and support

**6. Location Advantages:**
• Near Volcanoes National Park
• Scenic mountain views
• Access to hiking trails
• Close to gorilla trekking sites
• Peaceful, natural setting

**7. Marketing Strategy:**
• International travel websites
• Eco-tourism platforms
• Partner with tour operators
• Social media showcasing nature
• Travel blogger collaborations

Would you like details on construction costs, sustainability features, or marketing strategies?";
    }
    
    // Coffee processing specific response
    if (strpos($message, 'coffee processing') !== false || strpos($message, 'coffee business') !== false) {
        return "Perfect choice! Coffee processing in Musanze is very profitable. Here's your complete guide:

☕ **Coffee Processing Business Plan:**

**1. Investment Requirements:**
• **Startup Cost:** 5,000,000-25,000,000 RWF
• **Processing Equipment:** 3,000,000-15,000,000 RWF
• **Storage Facilities:** 1,000,000-5,000,000 RWF
• **Transportation:** 1,000,000-5,000,000 RWF
• **Licenses:** Food processing, export permits

**2. Processing Services:**
• **Wet Processing:** 500-1,000 RWF per kg
• **Dry Processing:** 300-800 RWF per kg
• **Roasting Services:** 1,000-2,000 RWF per kg
• **Packaging:** 200-500 RWF per kg
• **Export Preparation:** 1,500-3,000 RWF per kg

**3. Target Markets:**
• Local coffee farmers (processing services)
• International coffee buyers
• Local coffee shops and restaurants
• Export markets (Europe, USA, Asia)
• Specialty coffee roasters

**4. Revenue Projections:**
• **Processing Volume:** 1,000-10,000 kg per month
• **Monthly Revenue:** 2,000,000-20,000,000 RWF
• **Annual Revenue:** 24,000,000-240,000,000 RWF

**5. Key Advantages:**
• Premium Arabica coffee region
• High-quality volcanic soil
• Established coffee farming community
• Growing international demand
• Government support for coffee sector

**6. Equipment Needed:**
• Coffee pulping machines
• Fermentation tanks
• Drying beds or machines
• Sorting and grading equipment
• Packaging machines
• Quality testing equipment

**7. Marketing Strategy:**
• Direct relationships with farmers
• International coffee trade shows
• Online coffee marketplaces
• Specialty coffee certifications
• Brand development and packaging

Would you like details on equipment costs, farmer partnerships, or export procedures?";
    }
    
    // Specific business type searches
    if (strpos($message, 'hiking') !== false || strpos($message, 'mountain') !== false || strpos($message, 'volcano') !== false) {
        return "Mountain hiking tours in Musanze are highly profitable! Here's what you need to know:

🏔️ **Mountain Hiking Business:**
• **Startup Cost:** 15,000,000-50,000,000 RWF
• **Equipment:** Hiking gear, safety equipment, vehicles
• **Location:** Near Volcanoes National Park
• **Target:** International tourists, adventure seekers
• **Revenue:** 50,000-200,000 RWF per tour group
• **Season:** Year-round, peak during dry seasons

**Requirements:** Guide certification, park permits, insurance
**Competition:** Medium - focus on unique experiences and safety

Would you like details on getting started or specific costs?";
    }
    
    if (strpos($message, 'coffee') !== false || strpos($message, 'arabica') !== false) {
        return "Coffee processing in Musanze is excellent! Here's the breakdown:

☕ **Coffee Processing Business:**
• **Startup Cost:** 5,000,000-15,000,000 RWF
• **Equipment:** Processing machines, drying facilities, storage
• **Location:** Near coffee farms in Musanze
• **Target:** Export markets, specialty coffee shops
• **Revenue:** 2,000-5,000 RWF per kg processed
• **Season:** Year-round processing

**Advantages:** Premium Arabica beans, volcanic soil quality
**Requirements:** Processing licenses, quality certifications

Need specific equipment costs or market details?";
    }
    
    if (strpos($message, 'lodging') !== false || strpos($message, 'hotel') !== false || strpos($message, 'accommodation') !== false) {
        return "Eco-lodges near Volcanoes National Park are in high demand:

🏨 **Eco-lodge Business:**
• **Startup Cost:** 50,000,000-100,000,000 RWF
• **Location:** Near gorilla trekking sites
• **Target:** International tourists, nature lovers
• **Revenue:** 100,000-500,000 RWF per night
• **Occupancy:** 60-80% during peak season

**Features:** Sustainable design, local materials, cultural experiences
**Requirements:** Tourism licenses, environmental permits

Want details on construction costs or marketing strategies?";
    }
    
    if (strpos($message, 'farming') !== false || strpos($message, 'agriculture') !== false || strpos($message, 'organic') !== false) {
        return "Organic farming in Musanze's volcanic soil is very profitable:

🌱 **Organic Farming Business:**
• **Startup Cost:** 5,000,000-15,000,000 RWF
• **Land:** 1-5 hectares recommended
• **Crops:** Vegetables, fruits, herbs
• **Target:** Local markets, export, restaurants
• **Revenue:** 2,000-8,000 RWF per kg

**Advantages:** Fertile volcanic soil, premium prices
**Requirements:** Organic certification, irrigation systems

Interested in specific crop recommendations or market analysis?";
    }
    
    // Tour guide services specific response
    if (strpos($message, 'tour guide') !== false || strpos($message, 'city tours') !== false || strpos($message, 'nature tours') !== false) {
        return "Excellent choice! Tour guide services in Musanze are highly profitable. Here's your complete guide:

🗺️ **Tour Guide Services Business Plan:**

**1. Business Setup:**
• **Startup Cost:** 2,000,000-8,000,000 RWF
• **Licenses:** Tour guide certification (500,000-1,000,000 RWF)
• **Insurance:** Professional liability (200,000-500,000 RWF/year)
• **Equipment:** Vehicle, communication devices, first aid

**2. Tour Packages:**
• **City Tours:** 15,000-25,000 RWF per person (2-4 hours)
• **Nature Tours:** 25,000-50,000 RWF per person (full day)
• **Volcano Tours:** 50,000-100,000 RWF per person (premium)
• **Cultural Tours:** 20,000-35,000 RWF per person

**3. Target Markets:**
• International tourists (70% of revenue)
• Local weekend travelers
• Business travelers
• Adventure seekers

**4. Revenue Projections:**
• **Small groups (2-4 people):** 30,000-200,000 RWF per tour
• **Medium groups (5-8 people):** 75,000-400,000 RWF per tour
• **Large groups (9+ people):** 150,000-800,000 RWF per tour
• **Monthly potential:** 2,000,000-8,000,000 RWF

**5. Key Requirements:**
• Tour guide certification from Rwanda Development Board
• First aid training
• Knowledge of local history, culture, and wildlife
• Vehicle and driver's license
• Business registration

**6. Marketing Strategy:**
• Partner with hotels and lodges
• Online booking platforms
• Social media presence
• Tourist information centers

**7. Success Tips:**
• Learn multiple languages (English, French, Kinyarwanda)
• Specialize in specific areas (history, wildlife, culture)
• Build relationships with local communities
• Offer unique experiences

Would you like details on getting certified, marketing strategies, or specific tour routes?";
    }
    
    // Follow-up responses for tour guide services - moved higher for priority
    if (strpos($message, 'certification') !== false || strpos($message, 'certified') !== false) {
        return "Here's how to get tour guide certification in Rwanda:

📋 **Tour Guide Certification Process:**

**1. Requirements:**
• High school diploma or equivalent
• Good knowledge of English and French
• Basic knowledge of Kinyarwanda
• First aid certification
• Clean criminal record

**2. Application Process:**
• Apply to Rwanda Development Board (RDB)
• Submit required documents
• Pay application fee: 50,000 RWF
• Written exam on Rwanda history, culture, geography
• Practical assessment

**3. Training Program:**
• Duration: 2-4 weeks
• Cost: 200,000-500,000 RWF
• Covers: History, culture, wildlife, safety
• Includes: Field trips and practical training

**4. Exam Details:**
• Written test: 100 questions
• Practical test: Lead a mock tour
• Language assessment
• Pass rate: 70% required

**5. License Renewal:**
• Valid for 2 years
• Renewal fee: 100,000 RWF
• Continuing education required
• Performance review

**6. Specializations:**
• Cultural heritage guide
• Wildlife and nature guide
• Adventure tourism guide
• City tour guide

Need help with the application process or study materials?";
    }
    
    if (strpos($message, 'marketing') !== false || strpos($message, 'promote') !== false) {
        return "Here's your complete marketing strategy for tour guide services:

📢 **Marketing Strategy for Tour Guide Services:**

**1. Online Presence:**
• **Website:** Professional site with tour packages
• **Social Media:** Instagram, Facebook showcasing tours
• **Google My Business:** Local SEO optimization
• **TripAdvisor:** Customer reviews and ratings

**2. Partnership Network:**
• **Hotels & Lodges:** Referral agreements (10-15% commission)
• **Tour Operators:** Wholesale partnerships
• **Travel Agencies:** International connections
• **Airbnb Hosts:** Local recommendations

**3. Digital Marketing:**
• **Google Ads:** Target tourists searching for tours
• **Facebook Ads:** Retargeting website visitors
• **Instagram:** Visual content of tour experiences
• **YouTube:** Tour videos and testimonials

**4. Local Marketing:**
• **Tourist Information Centers:** Brochures and flyers
• **Airport Displays:** Welcome materials
• **Hotel Concierge:** Direct referrals
• **Local Events:** Tourism fairs and exhibitions

**5. Content Marketing:**
• **Blog Posts:** Travel tips, local insights
• **Video Content:** Tour highlights, cultural experiences
• **Photo Galleries:** Stunning local landscapes
• **Customer Stories:** Testimonials and reviews

**6. Pricing Strategy:**
• **Competitive Rates:** Research local market
• **Package Deals:** Multiple tours discounts
• **Group Discounts:** 10-20% for 5+ people
• **Seasonal Pricing:** Peak season adjustments

**7. Customer Retention:**
• **Follow-up:** Thank you emails and feedback requests
• **Loyalty Program:** Repeat customer discounts
• **Referral Program:** Rewards for bringing friends
• **Newsletter:** Updates on new tours and offers

**8. Budget Allocation:**
• **Digital Marketing:** 30% of marketing budget
• **Partnerships:** 25% (commissions and fees)
• **Content Creation:** 20% (photos, videos)
• **Local Marketing:** 15% (materials, events)
• **Tools & Software:** 10% (booking systems, CRM)

What specific marketing channel would you like to focus on first?";
    }
    
    // Bed & breakfast specific response
    if (strpos($message, 'bed & breakfast') !== false || strpos($message, 'guesthouse') !== false || strpos($message, 'accommodation') !== false) {
        return "Great choice! Bed & breakfast/guesthouse in Musanze is very profitable. Here's your detailed guide:

🏨 **Bed & Breakfast Business Plan:**

**1. Investment Requirements:**
• **Startup Cost:** 20,000,000-80,000,000 RWF
• **Property:** 3-8 rooms recommended
• **Renovation:** 10,000,000-30,000,000 RWF
• **Furniture & Equipment:** 5,000,000-15,000,000 RWF
• **Licenses:** Tourism accommodation license

**2. Room Pricing:**
• **Standard Room:** 25,000-50,000 RWF per night
• **Deluxe Room:** 50,000-100,000 RWF per night
• **Family Room:** 75,000-150,000 RWF per night
• **Peak Season:** 30% higher rates

**3. Target Guests:**
• International tourists (gorilla trekking)
• Adventure travelers
• Budget-conscious tourists
• Local weekend travelers

**4. Revenue Projections:**
• **Occupancy Rate:** 60-80% (peak season), 40-60% (low season)
• **Monthly Revenue:** 3,000,000-12,000,000 RWF
• **Annual Revenue:** 36,000,000-144,000,000 RWF

**5. Operating Costs:**
• **Staff:** 800,000-2,000,000 RWF/month
• **Utilities:** 200,000-500,000 RWF/month
• **Maintenance:** 300,000-800,000 RWF/month
• **Marketing:** 200,000-500,000 RWF/month

**6. Key Features to Offer:**
• Traditional Rwandan breakfast
• Free WiFi and parking
• Airport transfers
• Tour booking assistance
• Cultural experiences

**7. Location Advantages:**
• Near Volcanoes National Park
• Close to gorilla trekking sites
• Accessible to main roads
• Scenic mountain views

**8. Marketing Strategy:**
• Online booking platforms (Booking.com, Airbnb)
• Partner with tour operators
• Social media marketing
• Local tourism board listings

Would you like details on property selection, renovation costs, or marketing strategies?";
    }
    
    // Restaurant/café specific response
    if (strpos($message, 'restaurant') !== false || strpos($message, 'café') !== false || strpos($message, 'local cuisine') !== false) {
        return "Perfect choice! A restaurant with local cuisine in Musanze is highly profitable. Here's your complete guide:

🍽️ **Local Cuisine Restaurant Business Plan:**

**1. Investment Breakdown:**
• **Startup Cost:** 15,000,000-50,000,000 RWF
• **Kitchen Equipment:** 8,000,000-20,000,000 RWF
• **Interior Design:** 5,000,000-15,000,000 RWF
• **Initial Inventory:** 2,000,000-5,000,000 RWF
• **Licenses & Permits:** 1,000,000-2,000,000 RWF

**2. Menu Pricing:**
• **Traditional Dishes:** 3,000-8,000 RWF
• **International Options:** 5,000-12,000 RWF
• **Beverages:** 1,000-3,000 RWF
• **Tourist Packages:** 8,000-15,000 RWF

**3. Target Customers:**
• International tourists (60% of revenue)
• Local residents (25% of revenue)
• Business travelers (15% of revenue)

**4. Revenue Projections:**
• **Daily Covers:** 50-150 customers
• **Average Bill:** 5,000-8,000 RWF per person
• **Daily Revenue:** 250,000-1,200,000 RWF
• **Monthly Revenue:** 7,500,000-36,000,000 RWF

**5. Key Menu Items:**
• **Traditional:** Ugali, Isombe, Ibihaza
• **Popular:** Brochettes, Tilapia, Plantains
• **Tourist Favorites:** Rwandan coffee, fresh juices
• **International:** Pasta, sandwiches, salads

**6. Location Strategy:**
• Tourist areas with high foot traffic
• Near hotels and lodges
• Accessible parking
• Outdoor seating for ambiance

**7. Marketing Approach:**
• Social media showcasing local dishes
• Partner with tour operators
• Food blogger collaborations
• Local tourism events

**8. Success Factors:**
• Authentic traditional recipes
• Fresh local ingredients
• Friendly service
• Cultural atmosphere

Would you like details on menu planning, location selection, or staff training?";
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
    
    // More direct and actionable responses
    $defaultResponses = [
        "I can help you find the perfect business opportunity in Musanze! Instead of listing everything, let me ask: What's your budget range and what interests you most?\n\n💰 **Quick Budget Guide:**\n• **1-5M RWF:** Services, small retail, internet café\n• **5-15M RWF:** Coffee processing, organic farming, restaurant\n• **15-50M RWF:** Mountain tours, eco-lodges, gift shops\n• **50M+ RWF:** Large eco-lodges, major tourism facilities\n\n🎯 **What interests you?** Tourism, agriculture, services, or something specific?",
        
        "Great question! Let me help you find the right opportunity. What's most important to you?\n\n⏰ **Time to Start:**\n• **Quick Start (1-3 months):** Services, retail, internet café\n• **Medium (3-6 months):** Restaurant, coffee processing, tours\n• **Long-term (6-12 months):** Eco-lodges, large facilities\n\n💡 **Your Skills:**\n• **Hospitality:** Tours, accommodation, restaurants\n• **Agriculture:** Coffee, farming, food processing\n• **Services:** Transportation, digital, retail\n• **Technology:** Apps, websites, digital services\n\nWhat matches your situation?",
        
        "Perfect! Let's find your ideal business. What's your main goal?\n\n🎯 **Business Goals:**\n• **High Revenue:** Eco-lodges, premium tours, coffee export\n• **Steady Income:** Services, retail, transportation\n• **Quick Profit:** Gift shops, restaurants, tours\n• **Long-term Growth:** Large tourism facilities, processing plants\n\n🏔️ **Musanze Advantages:**\n• Volcanoes National Park proximity\n• Premium coffee region\n• Growing tourism market\n• Fertile volcanic soil\n\nWhat's your primary goal and budget range?"
    ];
    
    return $defaultResponses[array_rand($defaultResponses)];
}

try {
    // First check for specific business type selections (these should override ML system)
    $specificResponse = null;
    $mlResponse = null;
    
    // Business plan generation response (highest priority)
    if (strpos($message, 'generate business plan') !== false || strpos($message, 'export business plan') !== false || strpos($message, 'create business plan') !== false) {
        $specificResponse = "Yes! I can help you generate and export a comprehensive business plan. Here's how:

📝 **Generate & Export Business Plan:**

**1. Use the Business Plan Section:**
• Navigate to the 'Business Plan' section in your dashboard
• Fill in all sections with your business details
• I'll provide AI-assisted suggestions based on our Musanze dataset

**2. Business Plan Sections:**
• **Executive Summary:** Overview of your business concept
• **Company Description:** What you do and why
• **Market Analysis:** Target customers and competition
• **Organization Structure:** Team and management
• **Service/Product Line:** What you're offering
• **Marketing Strategy:** How you'll reach customers
• **Financial Projections:** Revenue, costs, and profits

**3. AI-Assisted Content:**
• Ask me specific questions while filling sections
• Example: 'What are typical startup costs for a coffee shop in Musanze?'
• I'll provide data from our 1000-row Musanze dataset
• Get accurate RWF pricing and local market insights

**4. Export Options:**
• **PDF Format:** Professional document for investors
• **Word Document:** Editable format for modifications
• **Excel Spreadsheets:** Financial projections and budgets
• **PowerPoint Presentation:** Investor pitch deck

**5. Financial Projections Included:**
• **5-Year Revenue Forecasts:** Month-by-month projections
• **Break-even Analysis:** When you'll start profiting
• **Cash Flow Statements:** Money in vs. money out
• **ROI Calculations:** Return on investment analysis
• **Sensitivity Analysis:** Best-case, worst-case scenarios

**6. Export Features:**
• **Professional Formatting:** Clean, business-ready layout
• **Charts & Graphs:** Visual financial projections
• **Custom Branding:** Your logo and company colors
• **Multiple Formats:** Choose what works best for you

**7. How to Start:**
1. **Choose Your Business Type:** Select from our templates
2. **Fill Required Information:** Complete the questionnaire
3. **AI Processing:** Our system generates content
4. **Review & Customize:** Edit and personalize
5. **Export:** Download in your preferred format

Would you like to start generating your business plan? Please specify your business type and I'll guide you through the process!";
    }
    
    // Mountain Hiking Tours business specific response
    if (strpos($message, 'Mountain Hiking Tours:') !== false || strpos($message, 'Mountain Hiking Tours') !== false) {
        $specificResponse = "Excellent choice! Mountain hiking tours are highly profitable in Musanze's adventure tourism market. Here's your complete guide:

🏔️ **Mountain Hiking Tours Business in Musanze:**

**1. Business Overview:**
• **Primary Services:** Guided mountain hikes, volcano treks, adventure tours
• **Target Market:** Adventure seekers, international tourists, nature enthusiasts
• **Unique Selling Point:** Volcanic landscapes, gorilla territory, cultural experiences

**2. Startup Investment:**
• **Guide Certification:** 500,000-2,000,000 RWF
• **Safety Equipment:** 1,000,000-3,000,000 RWF
• **Transportation:** 2,000,000-8,000,000 RWF
• **Marketing & Branding:** 500,000-2,000,000 RWF
• **Insurance & Permits:** 300,000-1,000,000 RWF
• **Total Startup:** 4,300,000-16,000,000 RWF

**3. Revenue Potential:**
• **Day Hikes:** 40,000-120,000 RWF per tour
• **Multi-day Expeditions:** 150,000-600,000 RWF per tour
• **Monthly Revenue:** 1,500,000-8,000,000 RWF
• **Peak Season:** 2-3x higher revenue

**4. Prime Locations:**
• **Volcanoes National Park:** Gorilla territory, volcano treks
• **Kinigi:** Park access, tourist hub
• **Musanze Town:** Cultural tours, city experiences
• **Ruhengeri:** Adventure activities, cultural sites

**5. Target Customers:**
• **International Tourists:** 60% of revenue
• **Adventure Seekers:** Mountain climbing, hiking
• **Cultural Enthusiasts:** Traditional experiences
• **Wildlife Lovers:** Gorilla watching, birding
• **Photography Tours:** Scenic locations, wildlife

**6. Service Offerings:**
• **Volcano Hiking:** Mount Karisimbi, Mount Bisoke
• **Gorilla Trekking:** Premium experience, high demand
• **Cultural Tours:** Traditional villages, local experiences
• **Wildlife Safaris:** Bird watching, nature walks
• **Photography Tours:** Scenic locations, wildlife
• **Adventure Sports:** Mountain biking, hiking

**7. Marketing Strategy:**
• **Online Platforms:** TripAdvisor, Booking.com, Airbnb Experiences
• **Hotel Partnerships:** Referral agreements, commission-based
• **Social Media:** Instagram, Facebook showcasing experiences
• **Tour Operator Networks:** International travel companies
• **Local Tourism Board:** Official listings and promotions

**8. Operational Tips:**
• **Guide Certification:** Obtain proper tourism guide licenses
• **Safety First:** First aid training, emergency procedures
• **Local Knowledge:** Deep understanding of area, culture, wildlife
• **Customer Service:** Multilingual skills, cultural sensitivity
• **Equipment Quality:** Reliable gear, backup supplies

**9. Legal Requirements:**
• **Guide License:** RDB tourism guide certification
• **Business Registration:** RDB registration
• **Insurance:** Comprehensive liability insurance
• **Park Permits:** Volcanoes National Park access permits
• **Tax Registration:** RRA compliance

**10. Success Factors:**
• **Expertise:** Deep local knowledge and experience
• **Safety Record:** Excellent safety reputation
• **Customer Reviews:** High ratings and testimonials
• **Network:** Strong relationships with hotels and operators
• **Flexibility:** Adapt to different customer needs

**11. Growth Opportunities:**
• **Specialized Tours:** Photography, birding, cultural focus
• **Group Tours:** Corporate retreats, educational groups
• **International Expansion:** Partner with global tour operators
• **Training Programs:** Guide certification courses
• **Equipment Rental:** Provide gear for self-guided tours

**12. Challenges & Solutions:**
• **Seasonal Demand:** Diversify with cultural and adventure tours
• **Competition:** Focus on unique experiences and quality service
• **Weather Dependencies:** Have indoor alternatives and backup plans
• **Language Barriers:** Invest in multilingual training
• **Safety Concerns:** Maintain excellent safety record and insurance

**13. Financial Projections (5-Year Plan):**
• **Year 1:** 1,500,000-5,000,000 RWF revenue (startup phase)
• **Year 2:** 5,000,000-8,000,000 RWF revenue (growth phase)
• **Year 3:** 8,000,000-15,000,000 RWF revenue (expansion phase)
• **Year 4:** 15,000,000-25,000,000 RWF revenue (maturity phase)
• **Year 5:** 25,000,000-40,000,000 RWF revenue (optimization phase)
• **Break-even Point:** 6-10 months
• **ROI:** 250-400% by Year 3

**14. Business Plan Generation:**
📋 **Generate Complete Business Plan** - Click to create a comprehensive business plan including:
• Executive Summary & Company Description
• Market Analysis & Competitive Analysis
• Financial Projections & Cash Flow Analysis
• Marketing Strategy & Sales Forecasts
• Operations Plan & Management Structure
• Risk Analysis & Mitigation Strategies

💼 **Export Options:** PDF, Word, Excel, PowerPoint formats available
📄 **PDF Export:** Click to generate PDF business plan
📝 **Word Export:** Click to generate Word document
📊 **Excel Export:** Click to generate Excel spreadsheet
📽️ **PowerPoint Export:** Click to generate presentation

Would you like details on guide certification, safety equipment, marketing strategies, or generate a complete business plan?";
    }
    
    // Volcano Trekking business specific response
    if (strpos($message, 'Volcano Trekking:') !== false || strpos($message, 'Volcano Trekking') !== false) {
        $specificResponse = "Excellent choice! Volcano trekking is a premium adventure tourism service in Musanze. Here's your complete guide:

🌋 **Volcano Trekking Business in Musanze:**

**1. Business Overview:**
• **Primary Services:** Guided volcano hikes, mountain climbing, adventure expeditions
• **Target Market:** Adventure seekers, international tourists, nature enthusiasts
• **Unique Selling Point:** Active volcanoes, gorilla territory, unique landscapes

**2. Startup Investment:**
• **Guide Certification:** 500,000-2,000,000 RWF
• **Safety Equipment:** 1,500,000-4,000,000 RWF
• **Transportation:** 2,000,000-8,000,000 RWF
• **Marketing & Branding:** 500,000-2,000,000 RWF
• **Insurance & Permits:** 500,000-1,500,000 RWF
• **Total Startup:** 5,000,000-18,000,000 RWF

**3. Revenue Potential:**
• **Day Treks:** 60,000-150,000 RWF per tour
• **Multi-day Expeditions:** 200,000-800,000 RWF per tour
• **Monthly Revenue:** 2,000,000-10,000,000 RWF
• **Peak Season:** 3-4x higher revenue

**4. Prime Locations:**
• **Volcanoes National Park:** Mount Karisimbi, Mount Bisoke
• **Kinigi:** Park access, tourist hub
• **Musanze Town:** Cultural tours, city experiences
• **Ruhengeri:** Adventure activities, cultural sites

**5. Target Customers:**
• **International Tourists:** 70% of revenue
• **Adventure Seekers:** Mountain climbing, hiking
• **Cultural Enthusiasts:** Traditional experiences
• **Wildlife Lovers:** Gorilla watching, birding
• **Photography Tours:** Scenic locations, wildlife

**6. Service Offerings:**
• **Mount Karisimbi:** Highest volcano, challenging trek
• **Mount Bisoke:** Crater lake, moderate difficulty
• **Gorilla Trekking:** Premium experience, high demand
• **Cultural Tours:** Traditional villages, local experiences
• **Wildlife Safaris:** Bird watching, nature walks
• **Photography Tours:** Scenic locations, wildlife

**7. Marketing Strategy:**
• **Online Platforms:** TripAdvisor, Booking.com, Airbnb Experiences
• **Hotel Partnerships:** Referral agreements, commission-based
• **Social Media:** Instagram, Facebook showcasing experiences
• **Tour Operator Networks:** International travel companies
• **Local Tourism Board:** Official listings and promotions

**8. Operational Tips:**
• **Guide Certification:** Obtain proper tourism guide licenses
• **Safety First:** First aid training, emergency procedures
• **Local Knowledge:** Deep understanding of area, culture, wildlife
• **Customer Service:** Multilingual skills, cultural sensitivity
• **Equipment Quality:** Reliable gear, backup supplies

**9. Legal Requirements:**
• **Guide License:** RDB tourism guide certification
• **Business Registration:** RDB registration
• **Insurance:** Comprehensive liability insurance
• **Park Permits:** Volcanoes National Park access permits
• **Tax Registration:** RRA compliance

**10. Success Factors:**
• **Expertise:** Deep local knowledge and experience
• **Safety Record:** Excellent safety reputation
• **Customer Reviews:** High ratings and testimonials
• **Network:** Strong relationships with hotels and operators
• **Flexibility:** Adapt to different customer needs

**11. Growth Opportunities:**
• **Specialized Tours:** Photography, birding, cultural focus
• **Group Tours:** Corporate retreats, educational groups
• **International Expansion:** Partner with global tour operators
• **Training Programs:** Guide certification courses
• **Equipment Rental:** Provide gear for self-guided tours

**12. Challenges & Solutions:**
• **Seasonal Demand:** Diversify with cultural and adventure tours
• **Competition:** Focus on unique experiences and quality service
• **Weather Dependencies:** Have indoor alternatives and backup plans
• **Language Barriers:** Invest in multilingual training
• **Safety Concerns:** Maintain excellent safety record and insurance

**13. Financial Projections (5-Year Plan):**
• **Year 1:** 2,000,000-6,000,000 RWF revenue (startup phase)
• **Year 2:** 6,000,000-10,000,000 RWF revenue (growth phase)
• **Year 3:** 10,000,000-18,000,000 RWF revenue (expansion phase)
• **Year 4:** 18,000,000-28,000,000 RWF revenue (maturity phase)
• **Year 5:** 28,000,000-45,000,000 RWF revenue (optimization phase)
• **Break-even Point:** 6-10 months
• **ROI:** 300-500% by Year 3

**14. Business Plan Generation:**
📋 **Generate Complete Business Plan** - Click to create a comprehensive business plan including:
• Executive Summary & Company Description
• Market Analysis & Competitive Analysis
• Financial Projections & Cash Flow Analysis
• Marketing Strategy & Sales Forecasts
• Operations Plan & Management Structure
• Risk Analysis & Mitigation Strategies

💼 **Export Options:** PDF, Word, Excel, PowerPoint formats available
📄 **PDF Export:** Click to generate PDF business plan
📝 **Word Export:** Click to generate Word document
📊 **Excel Export:** Click to generate Excel spreadsheet
📽️ **PowerPoint Export:** Click to generate presentation

Would you like details on guide certification, safety equipment, marketing strategies, or generate a complete business plan?";
    }
    
    // Local Restaurant business specific response
    if (strpos($message, 'Local Restaurant:') !== false || strpos($message, 'Local Restaurant') !== false) {
        $specificResponse = "Excellent choice! Local restaurants are highly profitable in Musanze's growing tourism and local market. Here's your complete guide:

🍽️ **Local Restaurant Business in Musanze:**

**1. Business Overview:**
• **Primary Services:** Traditional Rwandan cuisine, local dishes, tourist-friendly meals
• **Target Market:** International tourists, local residents, business travelers
• **Unique Selling Point:** Authentic Rwandan flavors, fresh local ingredients, cultural experience

**2. Startup Investment:**
• **Kitchen Equipment:** 2,000,000-8,000,000 RWF
• **Furniture & Decor:** 1,500,000-5,000,000 RWF
• **Initial Inventory:** 500,000-2,000,000 RWF
• **Licenses & Permits:** 300,000-1,000,000 RWF
• **Marketing & Branding:** 500,000-2,000,000 RWF
• **Total Startup:** 4,800,000-18,000,000 RWF

**3. Revenue Potential:**
• **Daily Revenue:** 50,000-200,000 RWF
• **Monthly Revenue:** 1,500,000-6,000,000 RWF
• **Peak Season:** 2-3x higher revenue
• **Tourist Season:** 3-4x higher revenue

**4. Prime Locations:**
• **Musanze Town:** High foot traffic, tourist area
• **Kinigi:** Near Volcanoes National Park
• **Ruhengeri:** Business district, local market
• **Musanze District:** Residential areas, local customers

**5. Target Customers:**
• **International Tourists:** 40% of revenue
• **Local Residents:** 35% of revenue
• **Business Travelers:** 15% of revenue
• **Adventure Seekers:** 10% of revenue

**6. Menu Categories:**
• **Traditional Rwandan:** Ugali, matoke, isombe, brochettes
• **International Favorites:** Pizza, pasta, burgers
• **Vegetarian Options:** Fresh vegetables, plant-based dishes
• **Beverages:** Local coffee, fresh juices, soft drinks
• **Desserts:** Traditional sweets, ice cream

**7. Marketing Strategy:**
• **Online Platforms:** TripAdvisor, Google Maps, social media
• **Hotel Partnerships:** Referral agreements, room service
• **Local Advertising:** Billboards, radio, community events
• **Tourist Information:** Brochures, hotel concierge
• **Social Media:** Instagram, Facebook showcasing dishes

**8. Operational Tips:**
• **Fresh Ingredients:** Source locally, maintain quality
• **Staff Training:** Customer service, food safety
• **Menu Planning:** Seasonal dishes, tourist preferences
• **Hygiene Standards:** Health department compliance
• **Cultural Sensitivity:** Respect local customs

**9. Legal Requirements:**
• **Business Registration:** RDB registration
• **Health Permits:** Food safety certification
• **Tax Registration:** RRA compliance
• **Liquor License:** If serving alcohol
• **Employment Permits:** For foreign staff

**10. Success Factors:**
• **Quality Food:** Consistent, delicious meals
• **Good Service:** Friendly, efficient staff
• **Clean Environment:** Hygiene, comfort
• **Fair Pricing:** Competitive but profitable
• **Location:** High visibility, easy access

**11. Growth Opportunities:**
• **Catering Services:** Events, hotels, offices
• **Food Delivery:** Online ordering, home delivery
• **Cooking Classes:** Tourist experiences, cultural exchange
• **Franchise Expansion:** Multiple locations
• **Export Products:** Packaged foods, sauces

**12. Challenges & Solutions:**
• **Seasonal Demand:** Diversify menu, target locals
• **Competition:** Focus on quality and unique offerings
• **Staff Turnover:** Good wages, training, benefits
• **Supply Chain:** Build relationships with local suppliers
• **Tourist Preferences:** Adapt menu to international tastes

**13. Financial Projections (5-Year Plan):**
• **Year 1:** 1,500,000-4,500,000 RWF revenue (startup phase)
• **Year 2:** 4,500,000-8,000,000 RWF revenue (growth phase)
• **Year 3:** 8,000,000-15,000,000 RWF revenue (expansion phase)
• **Year 4:** 15,000,000-25,000,000 RWF revenue (maturity phase)
• **Year 5:** 25,000,000-40,000,000 RWF revenue (optimization phase)
• **Break-even Point:** 6-12 months
• **ROI:** 200-350% by Year 3

**14. Business Plan Generation:**
📋 **Generate Complete Business Plan** - Click to create a comprehensive business plan including:
• Executive Summary & Company Description
• Market Analysis & Competitive Analysis
• Financial Projections & Cash Flow Analysis
• Marketing Strategy & Sales Forecasts
• Operations Plan & Management Structure
• Risk Analysis & Mitigation Strategies

💼 **Export Options:** PDF, Word, Excel, PowerPoint formats available

Would you like details on menu planning, location selection, marketing strategies, or generate a complete business plan?";
    }
    
    // Eco-lodges business specific response
    if (strpos($message, 'Eco-lodges:') !== false || strpos($message, 'Eco-lodges') !== false) {
        $specificResponse = "Excellent choice! Eco-lodges are highly profitable in Musanze's sustainable tourism market. Here's your complete guide:

🌿 **Eco-lodges Business in Musanze:**

**1. Business Overview:**
• **Primary Services:** Sustainable accommodation, eco-tourism experiences, nature activities
• **Target Market:** Eco-conscious tourists, nature lovers, adventure seekers
• **Unique Selling Point:** Environmental sustainability, unique locations, authentic experiences

**2. Startup Investment:**
• **Land & Construction:** 15,000,000-50,000,000 RWF
• **Eco-friendly Materials:** 5,000,000-15,000,000 RWF
• **Solar & Water Systems:** 3,000,000-10,000,000 RWF
• **Furniture & Equipment:** 2,000,000-8,000,000 RWF
• **Marketing & Branding:** 1,000,000-3,000,000 RWF
• **Total Startup:** 26,000,000-86,000,000 RWF

**3. Revenue Potential:**
• **Room Rates:** 50,000-200,000 RWF per night
• **Monthly Revenue:** 3,000,000-12,000,000 RWF
• **Peak Season:** 2-3x higher revenue
• **Additional Services:** 20-30% extra revenue

**4. Prime Locations:**
• **Volcanoes National Park:** Near gorilla territory
• **Kinigi:** Park access, tourist hub
• **Musanze District:** Rural setting, nature access
• **Ruhengeri:** Adventure activities, cultural sites

**5. Target Customers:**
• **International Tourists:** 60% of revenue
• **Eco-conscious Travelers:** 25% of revenue
• **Adventure Seekers:** 10% of revenue
• **Cultural Enthusiasts:** 5% of revenue

**6. Service Offerings:**
• **Accommodation:** Eco-friendly rooms, traditional design
• **Nature Activities:** Bird watching, nature walks
• **Cultural Experiences:** Traditional village visits
• **Adventure Tours:** Hiking, volcano treks
• **Educational Programs:** Environmental awareness
• **Wellness Services:** Spa, meditation, yoga

**7. Marketing Strategy:**
• **Online Platforms:** Booking.com, Airbnb, eco-tourism sites
• **Eco-tourism Networks:** International sustainable travel
• **Social Media:** Instagram, Facebook showcasing nature
• **Travel Agencies:** Specialized eco-tourism operators
• **Certification Programs:** Green tourism certifications

**8. Operational Tips:**
• **Sustainability Practices:** Solar power, water conservation
• **Local Sourcing:** Local materials, local staff
• **Environmental Impact:** Minimal footprint, conservation
• **Guest Education:** Environmental awareness programs
• **Community Involvement:** Local partnerships, benefits

**9. Legal Requirements:**
• **Business Registration:** RDB registration
• **Environmental Permits:** EIA compliance
• **Tourism License:** RDB tourism certification
• **Land Use Permits:** Proper zoning
• **Tax Registration:** RRA compliance

**10. Success Factors:**
• **Authentic Experience:** Genuine eco-friendly practices
• **Location:** Unique, natural setting
• **Service Quality:** Excellent hospitality
• **Environmental Commitment:** Real sustainability
• **Community Relations:** Positive local impact

**11. Growth Opportunities:**
• **Expansion:** Additional rooms, facilities
• **Specialized Programs:** Birding, photography tours
• **Corporate Retreats:** Team building, conferences
• **Training Programs:** Eco-tourism education
• **Franchise Model:** Replicate successful model

**12. Challenges & Solutions:**
• **High Initial Investment:** Phased development, partnerships
• **Seasonal Demand:** Diversify activities, target locals
• **Environmental Regulations:** Stay compliant, get certified
• **Competition:** Focus on unique experiences
• **Maintenance Costs:** Quality materials, regular upkeep

**13. Financial Projections (5-Year Plan):**
• **Year 1:** 3,000,000-8,000,000 RWF revenue (startup phase)
• **Year 2:** 8,000,000-15,000,000 RWF revenue (growth phase)
• **Year 3:** 15,000,000-25,000,000 RWF revenue (expansion phase)
• **Year 4:** 25,000,000-40,000,000 RWF revenue (maturity phase)
• **Year 5:** 40,000,000-60,000,000 RWF revenue (optimization phase)
• **Break-even Point:** 12-18 months
• **ROI:** 150-300% by Year 3

**14. Business Plan Generation:**
📋 **Generate Complete Business Plan** - Click to create a comprehensive business plan including:
• Executive Summary & Company Description
• Market Analysis & Competitive Analysis
• Financial Projections & Cash Flow Analysis
• Marketing Strategy & Sales Forecasts
• Operations Plan & Management Structure
• Risk Analysis & Mitigation Strategies

💼 **Export Options:** PDF, Word, Excel, PowerPoint formats available

Would you like details on sustainability practices, location selection, marketing strategies, or generate a complete business plan?";
    }
    
    // Quick fix: Check for specific business selections first
    if (strpos($message, 'Internet Cafe:') !== false || strpos($message, 'Internet Cafe') !== false) {
        $specificResponse = "Excellent choice! Internet cafes are highly profitable in Musanze's growing digital economy. Here's your complete guide:

💻 **Internet Cafe Business in Musanze:**

**1. Business Overview:**
• **Primary Services:** Internet access, computer rental, printing, scanning
• **Additional Services:** Gaming, social media, online learning, business services
• **Target Market:** Students, tourists, local professionals, digital nomads

**2. Startup Investment:**
• **Computers (10-15 units):** 15,000,000-25,000,000 RWF
• **High-speed Internet:** 200,000-500,000 RWF per month
• **Furniture & Setup:** 3,000,000-8,000,000 RWF
• **Software & Licenses:** 1,000,000-3,000,000 RWF
• **Security System:** 1,000,000-2,000,000 RWF
• **Total Startup:** 20,000,000-38,000,000 RWF

**3. Revenue Potential:**
• **Hourly Rates:** 500-2,000 RWF per hour
• **Daily Revenue:** 50,000-200,000 RWF
• **Monthly Revenue:** 1,500,000-6,000,000 RWF
• **Additional Services:** 20-30% extra revenue

**4. Prime Locations:**
• **Near Universities:** High student traffic
• **Tourist Areas:** Travelers needing internet access
• **Business Districts:** Professionals and entrepreneurs
• **Residential Areas:** Local community access

**5. Target Customers:**
• **Students:** Research, assignments, online learning
• **Tourists:** Communication, travel planning, social media
• **Professionals:** Business meetings, document processing
• **Gamers:** Online gaming, esports tournaments
• **Digital Nomads:** Remote work, video calls

**6. Service Offerings:**
• **Basic Internet Access:** Hourly computer rental
• **Printing & Scanning:** Document services
• **Gaming Services:** High-performance gaming computers
• **Business Services:** Meeting rooms, video conferencing
• **Training Services:** Computer literacy classes

**7. Marketing Strategy:**
• **Social Media:** Facebook, Instagram showcasing facilities
• **Student Partnerships:** University collaborations
• **Tourist Information:** Hotel and travel agency referrals
• **Local Advertising:** Community boards, local media
• **Loyalty Programs:** Discounts for regular customers

**8. Operational Tips:**
• **Fast Internet:** Invest in reliable, high-speed connection
• **Regular Maintenance:** Keep computers updated and clean
• **Security:** Implement user management and content filtering
• **Customer Service:** Friendly, helpful staff
• **Flexible Hours:** Extended hours for different customer needs

**9. Legal Requirements:**
• **Business Registration:** RDB registration
• **Internet License:** RURA telecommunications license
• **Tax Registration:** RRA compliance
• **Health & Safety:** Fire safety, ventilation requirements
• **Content Compliance:** Age-appropriate content policies

**10. Success Factors:**
• **Reliable Technology:** Fast, stable internet and computers
• **Clean Environment:** Comfortable, well-maintained space
• **Competitive Pricing:** Market-appropriate rates
• **Customer Service:** Helpful, knowledgeable staff
• **Location:** High-traffic, accessible area

**11. Growth Opportunities:**
• **Co-working Space:** Add professional meeting areas
• **Gaming Center:** Esports tournaments and events
• **Training Center:** Computer literacy and skills training
• **Mobile Services:** Internet access for events
• **Franchise Model:** Expand to other locations

**12. Challenges & Solutions:**
• **Power Outages:** Invest in backup generators
• **Internet Reliability:** Multiple ISP connections
• **Competition:** Focus on unique services and customer experience
• **Technology Updates:** Regular equipment upgrades

**11. Financial Projections (5-Year Plan):**
• **Year 1:** 1,500,000-4,500,000 RWF revenue (startup phase)
• **Year 2:** 4,500,000-8,000,000 RWF revenue (growth phase)
• **Year 3:** 8,000,000-15,000,000 RWF revenue (expansion phase)
• **Year 4:** 15,000,000-25,000,000 RWF revenue (maturity phase)
• **Year 5:** 25,000,000-40,000,000 RWF revenue (optimization phase)
• **Break-even Point:** 6-10 months
• **ROI:** 200-350% by Year 3

**12. Business Plan Generation:**
📋 **Generate Complete Business Plan** - Click to create a comprehensive business plan including:
• Executive Summary & Company Description
• Market Analysis & Competitive Analysis
• Financial Projections & Cash Flow Analysis
• Marketing Strategy & Sales Forecasts
• Operations Plan & Management Structure
• Risk Analysis & Mitigation Strategies

💼 **Export Options:** PDF, Word, Excel, PowerPoint formats available

Would you like details on location selection, equipment specifications, marketing strategies, or generate a complete business plan?";
    }
    
    // Food Processing business specific response
    if (strpos($message, 'Food Processing:') !== false || strpos($message, 'Food Processing') !== false) {
        $specificResponse = "Excellent choice! Food processing is a highly profitable business in Musanze's agricultural economy. Here's your complete guide:

🍯 **Food Processing Business in Musanze:**

**1. Business Overview:**
• **Primary Products:** Coffee processing, honey production, fruit drying, vegetable processing
• **Target Market:** Local consumers, tourists, export markets, hotels and restaurants
• **Unique Selling Point:** High-quality, locally-sourced, traditional processing methods

**2. Startup Investment:**
• **Processing Equipment:** 8,000,000-25,000,000 RWF
• **Storage Facilities:** 5,000,000-15,000,000 RWF
• **Packaging Materials:** 2,000,000-8,000,000 RWF
• **Licenses & Permits:** 1,000,000-3,000,000 RWF
• **Working Capital:** 10,000,000-30,000,000 RWF
• **Total Startup:** 26,000,000-81,000,000 RWF

**3. Revenue Potential:**
• **Coffee Processing:** 500,000-2,000,000 RWF per month
• **Honey Production:** 300,000-1,500,000 RWF per month
• **Fruit Drying:** 200,000-1,000,000 RWF per month
• **Vegetable Processing:** 400,000-1,800,000 RWF per month
• **Combined Monthly Revenue:** 1,400,000-6,300,000 RWF

**4. Prime Locations:**
• **Agricultural Areas:** Close to coffee farms and fruit orchards
• **Industrial Zones:** Proper zoning for food processing
• **Transport Hubs:** Easy access to markets and export facilities
• **Near Water Sources:** For processing and cleaning requirements

**5. Target Customers:**
• **Local Markets:** Supermarkets, grocery stores, local consumers
• **Tourist Market:** Hotels, restaurants, souvenir shops
• **Export Market:** International buyers, specialty food stores
• **Institutional:** Schools, hospitals, government facilities
• **Wholesale:** Distributors, food service companies

**6. Product Categories:**
• **Coffee Processing:** Roasting, grinding, packaging specialty coffee
• **Honey Production:** Raw honey, flavored honey, honey-based products
• **Fruit Processing:** Dried fruits, jams, fruit leathers, fruit juices
• **Vegetable Processing:** Dried vegetables, pickled products, vegetable powders
• **Traditional Foods:** Local delicacies, cultural food products

**7. Processing Methods:**
• **Traditional Methods:** Sun drying, traditional fermentation
• **Modern Equipment:** Dehydrators, vacuum sealers, pasteurization
• **Quality Control:** Testing, grading, certification processes
• **Packaging:** Eco-friendly, attractive, export-ready packaging

**8. Marketing Strategy:**
• **Local Markets:** Direct sales to supermarkets and restaurants
• **Tourist Market:** Hotel partnerships, souvenir shop distribution
• **Export Market:** Trade shows, online platforms, international buyers
• **Brand Development:** Local brand identity, quality certifications
• **Social Media:** Instagram, Facebook showcasing products and process

**9. Operational Tips:**
• **Quality Control:** Maintain consistent quality standards
• **Seasonal Planning:** Plan production around harvest seasons
• **Storage Management:** Proper storage to maintain product quality
• **Staff Training:** Food safety, processing techniques, quality standards
• **Equipment Maintenance:** Regular servicing and upgrades

**10. Legal Requirements:**
• **Business Registration:** RDB registration
• **Food Safety License:** RDB food processing license
• **Health Certificates:** Regular health inspections
• **Export Permits:** For international sales
• **Quality Certifications:** Organic, fair trade, quality standards

**11. Success Factors:**
• **Quality Products:** Consistent, high-quality processed foods
• **Local Sourcing:** Direct relationships with farmers and suppliers
• **Market Knowledge:** Understanding local and international demand
• **Efficient Processing:** Cost-effective, quality processing methods
• **Strong Brand:** Recognizable, trusted brand identity

**12. Growth Opportunities:**
• **Product Expansion:** Add new processed food categories
• **Export Development:** Expand to international markets
• **Value Addition:** Premium products, specialty processing
• **Partnerships:** Collaborate with farmers, restaurants, hotels
• **Technology Upgrade:** Modern processing equipment and methods

**13. Challenges & Solutions:**
• **Seasonal Supply:** Diversify products and suppliers
• **Quality Control:** Invest in testing and certification
• **Market Access:** Build strong distribution networks
• **Competition:** Focus on quality and local authenticity
• **Regulatory Compliance:** Stay updated with food safety regulations

**14. Financial Projections (5-Year Plan):**
• **Year 1:** 1,400,000-4,200,000 RWF revenue (startup phase)
• **Year 2:** 4,200,000-8,400,000 RWF revenue (growth phase)
• **Year 3:** 8,400,000-15,750,000 RWF revenue (expansion phase)
• **Year 4:** 15,750,000-25,200,000 RWF revenue (maturity phase)
• **Year 5:** 25,200,000-37,800,000 RWF revenue (optimization phase)
• **Break-even Point:** 8-12 months
• **ROI:** 200-400% by Year 3

**15. Business Plan Generation:**
📋 **Generate Complete Business Plan** - Click to create a comprehensive business plan including:
• Executive Summary & Company Description
• Market Analysis & Competitive Analysis
• Financial Projections & Cash Flow Analysis
• Marketing Strategy & Sales Forecasts
• Operations Plan & Management Structure
• Risk Analysis & Mitigation Strategies

💼 **Export Options:** PDF, Word, Excel, PowerPoint formats available

Would you like details on specific processing methods, equipment selection, market development strategies, or generate a complete business plan?";
    }
    
    // Coffee Processing business specific response
    if (strpos($message, 'Coffee Processing:') !== false || strpos($message, 'Coffee Processing') !== false) {
        $specificResponse = "Excellent choice! Coffee processing is a premium business opportunity in Musanze's coffee-growing region. Here's your complete guide:

☕ **Coffee Processing Business in Musanze:**

**1. Business Overview:**
• **Primary Services:** Coffee roasting, grinding, packaging, quality grading
• **Target Market:** Local cafes, hotels, tourists, export markets, specialty coffee shops
• **Unique Selling Point:** Premium Rwandan coffee, direct from Musanze's volcanic soil

**2. Startup Investment:**
• **Roasting Equipment:** 15,000,000-40,000,000 RWF
• **Grinding Equipment:** 5,000,000-15,000,000 RWF
• **Packaging Equipment:** 3,000,000-10,000,000 RWF
• **Storage Facilities:** 8,000,000-20,000,000 RWF
• **Quality Testing Equipment:** 2,000,000-8,000,000 RWF
• **Total Startup:** 33,000,000-93,000,000 RWF

**3. Revenue Potential:**
• **Local Sales:** 800,000-3,000,000 RWF per month
• **Tourist Market:** 500,000-2,000,000 RWF per month
• **Export Sales:** 1,500,000-8,000,000 RWF per month
• **Total Monthly Revenue:** 2,800,000-13,000,000 RWF

**4. Prime Locations:**
• **Coffee Growing Areas:** Direct access to coffee farms
• **Tourist Hubs:** Near hotels and tourist attractions
• **Transport Hubs:** Easy access to export facilities
• **Industrial Zones:** Proper zoning for food processing

**5. Target Customers:**
• **Local Cafes:** Coffee shops, restaurants, hotels
• **Tourists:** Premium coffee experiences, souvenir purchases
• **Export Market:** International coffee buyers, specialty stores
• **Local Consumers:** Premium coffee for home use
• **Corporate:** Offices, hotels, conference centers

**6. Product Categories:**
• **Whole Bean Coffee:** Premium roasted beans
• **Ground Coffee:** Various grind sizes for different brewing methods
• **Coffee Blends:** Signature blends, single-origin varieties
• **Specialty Products:** Cold brew, coffee extracts, coffee-based products
• **Gift Sets:** Premium packaging for tourists and gifts

**7. Processing Methods:**
• **Roasting:** Light, medium, dark roast profiles
• **Grinding:** Various grind sizes (coarse, medium, fine, espresso)
• **Quality Control:** Cupping, grading, quality testing
• **Packaging:** Vacuum-sealed, eco-friendly packaging
• **Storage:** Climate-controlled storage for freshness

**8. Marketing Strategy:**
• **Local Partnerships:** Hotels, restaurants, cafes
• **Tourist Market:** Coffee tasting experiences, souvenir sales
• **Export Development:** Trade shows, international buyers
• **Brand Development:** Premium brand identity, certifications
• **Online Sales:** E-commerce, social media marketing

**9. Operational Tips:**
• **Quality Control:** Consistent roasting profiles, quality testing
• **Supplier Relationships:** Direct relationships with coffee farmers
• **Equipment Maintenance:** Regular servicing of roasting equipment
• **Staff Training:** Coffee knowledge, customer service, quality standards
• **Inventory Management:** Fresh stock rotation, proper storage

**10. Legal Requirements:**
• **Business Registration:** RDB registration
• **Food Safety License:** RDB food processing license
• **Export Permits:** For international sales
• **Quality Certifications:** Organic, fair trade, specialty coffee certifications
• **Tax Registration:** RRA compliance

**11. Success Factors:**
• **Quality Coffee:** Premium, consistent quality products
• **Local Sourcing:** Direct relationships with coffee farmers
• **Market Knowledge:** Understanding coffee market trends
• **Brand Recognition:** Strong, trusted brand identity
• **Customer Service:** Excellent customer experience

**12. Growth Opportunities:**
• **Coffee Shop Chain:** Open your own coffee shops
• **Export Expansion:** International market development
• **Product Diversification:** Coffee-based products, merchandise
• **Coffee Education:** Training programs, coffee workshops
• **Franchise Model:** License your coffee processing methods

**13. Challenges & Solutions:**
• **Seasonal Supply:** Diversify suppliers, proper inventory management
• **Quality Consistency:** Invest in quality control systems
• **Market Competition:** Focus on premium quality and local authenticity
• **Export Regulations:** Stay updated with international requirements
• **Equipment Costs:** Consider leasing or financing options

**14. Financial Projections (5-Year Plan):**
• **Year 1:** 2,800,000-7,800,000 RWF revenue (startup phase)
• **Year 2:** 7,800,000-15,600,000 RWF revenue (growth phase)
• **Year 3:** 15,600,000-28,600,000 RWF revenue (expansion phase)
• **Year 4:** 28,600,000-46,800,000 RWF revenue (maturity phase)
• **Year 5:** 46,800,000-70,200,000 RWF revenue (optimization phase)
• **Break-even Point:** 10-15 months
• **ROI:** 250-450% by Year 3

**15. Business Plan Generation:**
📋 **Generate Complete Business Plan** - Click to create a comprehensive business plan including:
• Executive Summary & Company Description
• Market Analysis & Competitive Analysis
• Financial Projections & Cash Flow Analysis
• Marketing Strategy & Sales Forecasts
• Operations Plan & Management Structure
• Risk Analysis & Mitigation Strategies

💼 **Export Options:** PDF, Word, Excel, PowerPoint formats available

Would you like details on roasting techniques, quality control methods, export market development, or generate a complete business plan?";
    }
    
    // Local Transport business specific response
    if (strpos($message, 'Local Transport:') !== false || strpos($message, 'Local Transport') !== false) {
        $specificResponse = "Excellent choice! Local transport is a thriving business in Musanze. Here's your complete guide:

🚗 **Local Transport Business in Musanze:**

**1. Business Types:**
• **Motorcycle Taxi (Moto):** Most popular, low startup cost
• **Car Taxi Service:** Higher investment, premium service
• **Minibus Transport:** Group transport, higher revenue
• **Bicycle Taxi:** Eco-friendly, tourist appeal

**2. Startup Requirements:**
• **Motorcycle:** 800,000-1,500,000 RWF
• **Car:** 8,000,000-15,000,000 RWF
• **Minibus:** 12,000,000-25,000,000 RWF
• **Licenses & Permits:** 200,000-500,000 RWF
• **Insurance:** 100,000-300,000 RWF annually

**3. Revenue Potential:**
• **Motorcycle:** 50,000-150,000 RWF per day
• **Car:** 100,000-300,000 RWF per day
• **Minibus:** 200,000-500,000 RWF per day
• **Monthly Revenue:** 1,500,000-15,000,000 RWF

**4. Target Markets:**
• **Tourists:** Airport transfers, park visits, city tours
• **Locals:** Daily commuting, market trips, business travel
• **Students:** School transport, university routes
• **Business Travelers:** Hotel transfers, meeting transport

**5. Key Locations:**
• **Musanze Town:** High demand, competition
• **Ruhengeri:** Tourist hub, premium pricing
• **Kinigi:** Park access, specialized routes
• **Volcanoes National Park:** Tourist transport

**6. Success Factors:**
• **Reliability:** On-time service, consistent availability
• **Safety:** Good driving record, vehicle maintenance
• **Customer Service:** Friendly, helpful, multilingual
• **Fair Pricing:** Competitive rates, transparent costs

**7. Marketing Strategy:**
• **Hotel Partnerships:** Referral agreements
• **Tourist Information Centers:** Brochures and flyers
• **Social Media:** Instagram, Facebook showcasing services
• **Local Networks:** Word-of-mouth recommendations

**8. Operational Tips:**
• **Vehicle Maintenance:** Regular servicing, safety checks
• **Driver Training:** Customer service, local knowledge
• **Route Planning:** Efficient paths, traffic awareness
• **Safety Equipment:** First aid, emergency contacts

**9. Legal Requirements:**
• **Driver's License:** Valid for vehicle type
• **Business Registration:** RDB registration
• **Tax Registration:** RRA tax compliance
• **Insurance:** Comprehensive vehicle insurance

**10. Growth Opportunities:**
• **Fleet Expansion:** Add more vehicles
• **Route Diversification:** New destinations
• **Service Upgrades:** Premium vehicles, guided tours
• **Technology Integration:** Booking apps, GPS tracking

**11. Financial Projections (5-Year Plan):**
• **Year 1:** 1,500,000-4,500,000 RWF revenue (startup phase)
• **Year 2:** 4,500,000-8,000,000 RWF revenue (growth phase)
• **Year 3:** 8,000,000-15,000,000 RWF revenue (expansion phase)
• **Year 4:** 15,000,000-25,000,000 RWF revenue (maturity phase)
• **Year 5:** 25,000,000-40,000,000 RWF revenue (optimization phase)
• **Break-even Point:** 4-8 months
• **ROI:** 250-400% by Year 3

**12. Business Plan Generation:**
📋 **Generate Complete Business Plan** - Click to create a comprehensive business plan including:
• Executive Summary & Company Description
• Market Analysis & Competitive Analysis
• Financial Projections & Cash Flow Analysis
• Marketing Strategy & Sales Forecasts
• Operations Plan & Management Structure
• Risk Analysis & Mitigation Strategies

💼 **Export Options:** PDF, Word, Excel, PowerPoint formats available

Would you like details on vehicle selection, route planning, marketing strategies, or generate a complete business plan?";
    }
    
    // Souvenir Shop business specific response
    if (strpos($message, 'Souvenir Shop:') !== false || strpos($message, 'Souvenir Shop') !== false) {
        $specificResponse = "Great choice! Souvenir shops are highly profitable in Musanze's tourism market. Here's your complete guide:

🎁 **Souvenir Shop Business in Musanze:**

**1. Product Categories:**
• **Traditional Crafts:** Baskets, pottery, wood carvings
• **Coffee Products:** Local coffee beans, branded packaging
• **Textiles:** Traditional clothing, fabrics, accessories
• **Art & Jewelry:** Local artwork, handmade jewelry
• **Tourist Items:** Postcards, magnets, keychains

**2. Startup Investment:**
• **Shop Rent:** 200,000-800,000 RWF per month
• **Initial Inventory:** 2,000,000-8,000,000 RWF
• **Shop Setup:** 1,000,000-3,000,000 RWF
• **Licenses & Permits:** 300,000-600,000 RWF
• **Total Startup:** 3,500,000-12,400,000 RWF

**3. Revenue Potential:**
• **Daily Sales:** 50,000-300,000 RWF
• **Monthly Revenue:** 1,500,000-9,000,000 RWF
• **Tourist Season:** 2-3x higher sales
• **Profit Margin:** 40-60% on most items

**4. Prime Locations:**
• **Kinigi:** Near Volcanoes National Park entrance
• **Ruhengeri:** Tourist hub, high foot traffic
• **Musanze Town:** Central location, local + tourist mix
• **Airport Area:** Last-minute purchases, premium pricing

**5. Target Customers:**
• **International Tourists:** 70% of revenue
• **Local Tourists:** 20% of revenue
• **Expatriates:** 10% of revenue
• **Gift Buyers:** Corporate, personal gifts

**6. Product Sourcing:**
• **Local Artisans:** Direct partnerships, fair trade
• **Cooperatives:** Bulk purchasing, consistent supply
• **Import Items:** Select international products
• **Custom Orders:** Personalized, branded items

**7. Marketing Strategies:**
• **Hotel Partnerships:** In-room catalogs, referral commissions
• **Tour Operator Deals:** Group discounts, package deals
• **Social Media:** Instagram, Facebook showcasing products
• **Tourist Information Centers:** Brochures, maps

**8. Operational Tips:**
• **Inventory Management:** Track fast/slow movers
• **Seasonal Planning:** Stock up for peak seasons
• **Customer Service:** Multilingual staff, cultural knowledge
• **Pricing Strategy:** Competitive but profitable margins

**9. Legal Requirements:**
• **Business Registration:** RDB registration
• **Tax Registration:** RRA compliance
• **Import Permits:** For international products
• **Health Certificates:** For food items (coffee, honey)

**10. Success Factors:**
• **Quality Products:** Authentic, well-crafted items
• **Fair Pricing:** Competitive but sustainable margins
• **Customer Experience:** Friendly service, cultural stories
• **Location:** High visibility, tourist traffic

**11. Growth Opportunities:**
• **Online Sales:** E-commerce, social media selling
• **Wholesale:** Supply to other shops, hotels
• **Custom Manufacturing:** Private label products
• **Export:** International markets, online platforms

**12. Seasonal Considerations:**
• **Peak Season (June-Aug, Dec-Feb):** 3x normal sales
• **Low Season:** Focus on locals, online sales
• **Festival Periods:** Special products, increased demand
• **Weather Impact:** Indoor/outdoor product mix

**13. Financial Projections (5-Year Plan):**
• **Year 1:** 1,500,000-4,500,000 RWF revenue (startup phase)
• **Year 2:** 4,500,000-8,000,000 RWF revenue (growth phase)
• **Year 3:** 8,000,000-15,000,000 RWF revenue (expansion phase)
• **Year 4:** 15,000,000-25,000,000 RWF revenue (maturity phase)
• **Year 5:** 25,000,000-40,000,000 RWF revenue (optimization phase)
• **Break-even Point:** 6-10 months
• **ROI:** 200-350% by Year 3

**14. Business Plan Generation:**
📋 **Generate Complete Business Plan** - Click to create a comprehensive business plan including:
• Executive Summary & Company Description
• Market Analysis & Competitive Analysis
• Financial Projections & Cash Flow Analysis
• Marketing Strategy & Sales Forecasts
• Operations Plan & Management Structure
• Risk Analysis & Mitigation Strategies

💼 **Export Options:** PDF, Word, Excel, PowerPoint formats available

Would you like details on product sourcing, location selection, marketing strategies, or generate a complete business plan?";
    }
    
    // Local Guide Services business specific response
    if (strpos($message, 'Local Guide Services:') !== false || strpos($message, 'Local Guide Services') !== false) {
        $specificResponse = "Excellent choice! Local guide services are highly profitable in Musanze's tourism market. Here's your complete guide:

🗺️ **Local Guide Services Business in Musanze:**

**1. Business Overview:**
• **Primary Services:** Guided tours, cultural experiences, adventure activities
• **Target Market:** International tourists, adventure seekers, cultural enthusiasts
• **Unique Selling Point:** Local expertise, authentic experiences, personalized service

**2. Startup Investment:**
• **Guide Certification:** 500,000-2,000,000 RWF
• **Equipment & Gear:** 1,000,000-5,000,000 RWF
• **Transportation:** 2,000,000-8,000,000 RWF
• **Marketing & Branding:** 500,000-2,000,000 RWF
• **Insurance & Permits:** 300,000-1,000,000 RWF
• **Total Startup:** 4,300,000-18,000,000 RWF

**3. Revenue Potential:**
• **Day Tours:** 50,000-150,000 RWF per tour
• **Multi-day Tours:** 200,000-800,000 RWF per tour
• **Monthly Revenue:** 2,000,000-12,000,000 RWF
• **Peak Season:** 3-4x higher revenue

**4. Prime Locations:**
• **Volcanoes National Park:** Gorilla trekking, volcano tours
• **Kinigi:** Park access, tourist hub
• **Musanze Town:** Cultural tours, city experiences
• **Ruhengeri:** Adventure activities, cultural sites

**5. Target Customers:**
• **International Tourists:** 70% of revenue
• **Adventure Seekers:** Mountain climbing, hiking
• **Cultural Enthusiasts:** Traditional experiences
• **Wildlife Lovers:** Gorilla watching, birding
• **Photography Tours:** Scenic locations, wildlife

**6. Service Offerings:**
• **Gorilla Trekking:** Premium experience, high demand
• **Volcano Tours:** Hiking, climbing adventures
• **Cultural Tours:** Traditional villages, local experiences
• **Wildlife Safaris:** Bird watching, nature walks
• **Photography Tours:** Scenic locations, wildlife
• **Adventure Sports:** Mountain biking, hiking

**7. Marketing Strategy:**
• **Online Platforms:** TripAdvisor, Booking.com, Airbnb Experiences
• **Hotel Partnerships:** Referral agreements, commission-based
• **Social Media:** Instagram, Facebook showcasing experiences
• **Tour Operator Networks:** International travel companies
• **Local Tourism Board:** Official listings and promotions

**8. Operational Tips:**
• **Guide Certification:** Obtain proper tourism guide licenses
• **Safety First:** First aid training, emergency procedures
• **Local Knowledge:** Deep understanding of area, culture, wildlife
• **Customer Service:** Multilingual skills, cultural sensitivity
• **Equipment Quality:** Reliable gear, backup supplies

**9. Legal Requirements:**
• **Guide License:** RDB tourism guide certification
• **Business Registration:** RDB registration
• **Insurance:** Comprehensive liability insurance
• **Park Permits:** Volcanoes National Park access permits
• **Tax Registration:** RRA compliance

**10. Success Factors:**
• **Expertise:** Deep local knowledge and experience
• **Safety Record:** Excellent safety reputation
• **Customer Reviews:** High ratings and testimonials
• **Network:** Strong relationships with hotels and operators
• **Flexibility:** Adapt to different customer needs

**11. Growth Opportunities:**
• **Specialized Tours:** Photography, birding, cultural focus
• **Group Tours:** Corporate retreats, educational groups
• **International Expansion:** Partner with global tour operators
• **Training Programs:** Guide certification courses
• **Equipment Rental:** Provide gear for self-guided tours

**12. Challenges & Solutions:**
• **Seasonal Demand:** Diversify with cultural and adventure tours
• **Competition:** Focus on unique experiences and quality service
• **Weather Dependencies:** Have indoor alternatives and backup plans
• **Language Barriers:** Invest in multilingual training
• **Safety Concerns:** Maintain excellent safety record and insurance

**13. Financial Projections (5-Year Plan):**
• **Year 1:** 2,000,000-6,000,000 RWF revenue (startup phase)
• **Year 2:** 6,000,000-10,000,000 RWF revenue (growth phase)
• **Year 3:** 10,000,000-18,000,000 RWF revenue (expansion phase)
• **Year 4:** 18,000,000-28,000,000 RWF revenue (maturity phase)
• **Year 5:** 28,000,000-45,000,000 RWF revenue (optimization phase)
• **Break-even Point:** 6-10 months
• **ROI:** 300-500% by Year 3

**14. Business Plan Generation:**
📋 **Generate Complete Business Plan** - Click to create a comprehensive business plan including:
• Executive Summary & Company Description
• Market Analysis & Competitive Analysis
• Financial Projections & Cash Flow Analysis
• Marketing Strategy & Sales Forecasts
• Operations Plan & Management Structure
• Risk Analysis & Mitigation Strategies

💼 **Export Options:** PDF, Word, Excel, PowerPoint formats available

Would you like details on guide certification, tour route planning, marketing strategies, or generate a complete business plan?";
    }
    
    // Organic Farming business specific response
    if (strpos($message, 'Organic Farming:') !== false || strpos($message, 'Organic Farming') !== false) {
        $specificResponse = "Excellent choice! Organic farming is a highly profitable and sustainable business in Musanze's fertile volcanic soil. Here's your complete guide:

🌱 **Organic Farming Business in Musanze:**

**1. Business Overview:**
• **Primary Products:** Organic vegetables, fruits, herbs, coffee, and specialty crops
• **Target Market:** Health-conscious consumers, restaurants, hotels, export markets
• **Unique Selling Point:** Volcanic soil fertility, organic certification, premium quality

**2. Startup Investment:**
• **Land Lease/Purchase:** 2,000,000-10,000,000 RWF per hectare
• **Organic Certification:** 500,000-2,000,000 RWF
• **Seeds & Planting Materials:** 1,000,000-3,000,000 RWF
• **Irrigation System:** 2,000,000-8,000,000 RWF
• **Tools & Equipment:** 1,500,000-5,000,000 RWF
• **Storage & Processing:** 3,000,000-10,000,000 RWF
• **Total Startup:** 10,000,000-38,000,000 RWF

**3. Revenue Potential:**
• **Vegetable Production:** 800,000-3,000,000 RWF per month
• **Fruit Production:** 1,200,000-4,500,000 RWF per month
• **Herbs & Spices:** 500,000-2,000,000 RWF per month
• **Organic Coffee:** 1,500,000-6,000,000 RWF per month
• **Total Monthly Revenue:** 4,000,000-15,500,000 RWF

**4. Prime Locations:**
• **Kinigi:** Volcanic soil, high altitude, premium quality
• **Musanze District:** Fertile land, good water access
• **Volcanoes National Park Buffer Zone:** Protected area, organic potential
• **Ruhengeri:** Market access, transportation hub

**5. Target Customers:**
• **Local Restaurants:** 30% of revenue - premium organic produce
• **Hotels & Lodges:** 25% of revenue - tourist market
• **Export Markets:** 20% of revenue - international organic demand
• **Local Consumers:** 15% of revenue - health-conscious buyers
• **Processing Companies:** 10% of revenue - value-added products

**6. Product Categories:**
• **Leafy Greens:** Lettuce, spinach, kale, arugula
• **Root Vegetables:** Carrots, potatoes, onions, garlic
• **Fruits:** Tomatoes, peppers, eggplants, berries
• **Herbs & Spices:** Basil, mint, rosemary, thyme
• **Specialty Crops:** Organic coffee, quinoa, chia seeds
• **Medicinal Plants:** Traditional herbs, natural remedies

**7. Farming Methods:**
• **Crop Rotation:** Maintain soil health and prevent pests
• **Composting:** Organic waste management and soil enrichment
• **Natural Pest Control:** Beneficial insects, companion planting
• **Water Conservation:** Drip irrigation, rainwater harvesting
• **Soil Management:** Cover crops, green manure, organic fertilizers

**8. Marketing Strategy:**
• **Direct Sales:** Farmers markets, roadside stands
• **Restaurant Partnerships:** Regular supply contracts
• **Hotel Supply:** Premium organic produce for tourists
• **Export Development:** International organic certification
• **Online Sales:** Social media, e-commerce platforms
• **Community Supported Agriculture (CSA):** Subscription-based sales

**9. Operational Tips:**
• **Soil Testing:** Regular analysis for optimal nutrient management
• **Crop Planning:** Year-round production with seasonal varieties
• **Quality Control:** Consistent standards and organic certification
• **Record Keeping:** Detailed farming logs for certification
• **Staff Training:** Organic farming techniques and standards

**10. Legal Requirements:**
• **Business Registration:** RDB registration
• **Organic Certification:** International organic standards
• **Land Use Permits:** Proper zoning and environmental compliance
• **Export Permits:** For international sales
• **Tax Registration:** RRA compliance

**11. Success Factors:**
• **Quality Products:** Consistent, premium organic produce
• **Market Knowledge:** Understanding demand and pricing
• **Sustainable Practices:** Long-term soil and environmental health
• **Certification:** Recognized organic standards
• **Customer Relationships:** Strong partnerships with buyers

**12. Growth Opportunities:**
• **Value Addition:** Processing, packaging, branded products
• **Export Expansion:** International organic markets
• **Agritourism:** Farm visits, educational tours
• **Seed Production:** Organic seed development and sales
• **Training Programs:** Organic farming education and consulting

**13. Challenges & Solutions:**
• **Certification Costs:** Plan for organic certification expenses
• **Market Access:** Build strong buyer relationships
• **Weather Dependencies:** Diversify crops and use protective structures
• **Labor Intensive:** Invest in efficient tools and training
• **Competition:** Focus on quality and unique varieties

**14. Financial Projections (5-Year Plan):**
• **Year 1:** 4,000,000-8,000,000 RWF revenue (startup phase)
• **Year 2:** 8,000,000-12,000,000 RWF revenue (growth phase)
• **Year 3:** 12,000,000-18,000,000 RWF revenue (expansion phase)
• **Year 4:** 18,000,000-25,000,000 RWF revenue (maturity phase)
• **Year 5:** 25,000,000-35,000,000 RWF revenue (optimization phase)
• **Break-even Point:** 8-12 months
• **ROI:** 200-400% by Year 3

**15. Business Plan Generation:**
📋 **Generate Complete Business Plan** - Click to create a comprehensive business plan including:
• Executive Summary & Company Description
• Market Analysis & Competitive Analysis
• Financial Projections & Cash Flow Analysis
• Marketing Strategy & Sales Forecasts
• Operations Plan & Management Structure
• Risk Analysis & Mitigation Strategies

💼 **Export Options:** PDF, Word, Excel, PowerPoint formats available

Would you like details on organic certification, crop selection, marketing strategies, or generate a complete business plan?";
    }
    
    // Guesthouse business specific response
    if (strpos($message, 'Guesthouse:') !== false || strpos($message, 'Guesthouse') !== false) {
        $specificResponse = "Excellent choice! Guesthouses are highly profitable in Musanze's tourism market. Here's your complete guide:

🏠 **Guesthouse Business in Musanze:**

**1. Business Overview:**
• **Accommodation Type:** Small-scale, intimate lodging (5-15 rooms)
• **Target Market:** Budget-conscious tourists, backpackers, business travelers
• **Unique Selling Point:** Personal service, local experience, affordable luxury

**2. Startup Investment:**
• **Property Purchase/Rent:** 50,000,000-200,000,000 RWF
• **Renovation & Setup:** 20,000,000-80,000,000 RWF
• **Furniture & Equipment:** 15,000,000-50,000,000 RWF
• **Licenses & Permits:** 2,000,000-8,000,000 RWF
• **Marketing & Branding:** 3,000,000-10,000,000 RWF
• **Total Startup:** 90,000,000-348,000,000 RWF

**3. Revenue Potential:**
• **Room Rates:** 25,000-80,000 RWF per night
• **Occupancy Rate:** 60-80% (peak season)
• **Monthly Revenue:** 2,250,000-9,600,000 RWF
• **Additional Services:** 20-30% extra revenue

**4. Prime Locations:**
• **Near Volcanoes National Park:** Premium tourism location
• **Musanze Town Center:** Business and cultural access
• **Kinigi:** Close to gorilla trekking
• **Ruhengeri:** Tourist hub with amenities

**5. Target Customers:**
• **International Tourists:** Budget-conscious travelers
• **Backpackers:** Young, adventurous travelers
• **Business Travelers:** Professionals on extended stays
• **Local Tourists:** Weekend getaways
• **Volunteers:** Long-term stays for NGO workers

**6. Service Offerings:**
• **Accommodation:** Clean, comfortable rooms
• **Breakfast Service:** Local and international options
• **Tour Arrangements:** Gorilla trekking, volcano tours
• **Airport Transfers:** Convenient transportation
• **Local Information:** Tourist guidance and recommendations

**7. Marketing Strategy:**
• **Online Booking:** Booking.com, Airbnb, TripAdvisor
• **Social Media:** Instagram, Facebook showcasing rooms and views
• **Tour Operator Partnerships:** Commission-based referrals
• **Local Tourism Board:** Official listings and promotions
• **Guest Reviews:** Encourage positive reviews and testimonials

**8. Operational Tips:**
• **Personal Service:** Owner-operated for authentic experience
• **Clean Standards:** Maintain high cleanliness and hygiene
• **Local Staff:** Hire knowledgeable local employees
• **Flexible Check-in:** Accommodate different arrival times
• **Cultural Integration:** Offer local experiences and meals

**9. Legal Requirements:**
• **Business Registration:** RDB registration
• **Tourism License:** RDB tourism accommodation license
• **Tax Registration:** RRA compliance
• **Health & Safety:** Fire safety, food handling permits
• **Insurance:** Comprehensive property and liability insurance

**10. Success Factors:**
• **Location:** Accessible, safe, and attractive area
• **Service Quality:** Friendly, helpful, professional staff
• **Cleanliness:** Spotless rooms and common areas
• **Value for Money:** Competitive pricing with good amenities
• **Local Knowledge:** Expert advice on attractions and activities

**11. Growth Opportunities:**
• **Room Expansion:** Add more rooms as demand grows
• **Service Upgrades:** Add restaurant, bar, or spa services
• **Tour Operations:** Organize and lead local tours
• **Event Hosting:** Weddings, conferences, retreats
• **Franchise Model:** Replicate successful model elsewhere

**12. Challenges & Solutions:**
• **Seasonal Demand:** Diversify with business travelers and events
• **Competition:** Focus on unique experiences and personal service
• **Staff Management:** Invest in training and fair compensation
• **Maintenance:** Regular upkeep and renovation schedule

**11. Financial Projections (5-Year Plan):**
• **Year 1:** 2,000,000-6,000,000 RWF revenue (startup phase)
• **Year 2:** 6,000,000-12,000,000 RWF revenue (growth phase)
• **Year 3:** 12,000,000-20,000,000 RWF revenue (expansion phase)
• **Year 4:** 20,000,000-30,000,000 RWF revenue (maturity phase)
• **Year 5:** 30,000,000-45,000,000 RWF revenue (optimization phase)
• **Break-even Point:** 8-12 months
• **ROI:** 200-350% by Year 3

**12. Business Plan Generation:**
📋 **Generate Complete Business Plan** - Click to create a comprehensive business plan including:
• Executive Summary & Company Description
• Market Analysis & Competitive Analysis
• Financial Projections & Cash Flow Analysis
• Marketing Strategy & Sales Forecasts
• Operations Plan & Management Structure
• Risk Analysis & Mitigation Strategies

💼 **Export Options:** PDF, Word, Excel, PowerPoint formats available

Would you like details on property selection, renovation planning, marketing strategies, or generate a complete business plan?";
    }
    
    // Internet Cafe business specific response
    if (strpos($message, 'internet cafe:') !== false || strpos($message, 'internet café:') !== false || 
        strpos($message, 'internet cafe') !== false || strpos($message, 'internet café') !== false) {
        $specificResponse = "Excellent choice! Internet cafes are highly profitable in Musanze's growing digital economy. Here's your complete guide:

💻 **Internet Cafe Business in Musanze:**

**1. Business Overview:**
• **Primary Services:** Internet access, computer rental, printing, scanning
• **Additional Services:** Gaming, social media, online learning, business services
• **Target Market:** Students, tourists, local professionals, digital nomads

**2. Startup Investment:**
• **Computers (10-15 units):** 15,000,000-25,000,000 RWF
• **High-speed Internet:** 200,000-500,000 RWF per month
• **Furniture & Setup:** 3,000,000-8,000,000 RWF
• **Software & Licenses:** 1,000,000-3,000,000 RWF
• **Security System:** 1,000,000-2,000,000 RWF
• **Total Startup:** 20,000,000-38,000,000 RWF

**3. Revenue Potential:**
• **Hourly Rates:** 500-2,000 RWF per hour
• **Daily Revenue:** 50,000-200,000 RWF
• **Monthly Revenue:** 1,500,000-6,000,000 RWF
• **Additional Services:** 20-30% extra revenue

**4. Prime Locations:**
• **Near Universities:** High student traffic
• **Tourist Areas:** Travelers needing internet access
• **Business Districts:** Professionals and entrepreneurs
• **Residential Areas:** Local community access

**5. Target Customers:**
• **Students:** Research, assignments, online learning
• **Tourists:** Communication, travel planning, social media
• **Professionals:** Business meetings, document processing
• **Gamers:** Online gaming, esports tournaments
• **Digital Nomads:** Remote work, video calls

**6. Service Offerings:**
• **Basic Internet Access:** Hourly computer rental
• **Printing & Scanning:** Document services
• **Gaming Services:** High-performance gaming computers
• **Business Services:** Meeting rooms, video conferencing
• **Training Services:** Computer literacy classes

**7. Marketing Strategy:**
• **Social Media:** Facebook, Instagram showcasing facilities
• **Student Partnerships:** University collaborations
• **Tourist Information:** Hotel and travel agency referrals
• **Local Advertising:** Community boards, local media
• **Loyalty Programs:** Discounts for regular customers

**8. Operational Tips:**
• **Fast Internet:** Invest in reliable, high-speed connection
• **Regular Maintenance:** Keep computers updated and clean
• **Security:** Implement user management and content filtering
• **Customer Service:** Friendly, helpful staff
• **Flexible Hours:** Extended hours for different customer needs

**9. Legal Requirements:**
• **Business Registration:** RDB registration
• **Internet License:** RURA telecommunications license
• **Tax Registration:** RRA compliance
• **Health & Safety:** Fire safety, ventilation requirements
• **Content Compliance:** Age-appropriate content policies

**10. Success Factors:**
• **Reliable Technology:** Fast, stable internet and computers
• **Clean Environment:** Comfortable, well-maintained space
• **Competitive Pricing:** Market-appropriate rates
• **Customer Service:** Helpful, knowledgeable staff
• **Location:** High-traffic, accessible area

**11. Growth Opportunities:**
• **Co-working Space:** Add professional meeting areas
• **Gaming Center:** Esports tournaments and events
• **Training Center:** Computer literacy and skills training
• **Mobile Services:** Internet access for events
• **Franchise Model:** Expand to other locations

**12. Challenges & Solutions:**
• **Power Outages:** Invest in backup generators
• **Internet Reliability:** Multiple ISP connections
• **Competition:** Focus on unique services and customer experience
• **Technology Updates:** Regular equipment upgrades

**13. Financial Projections (5-Year Plan):**
• **Year 1:** 1,500,000-4,500,000 RWF revenue (startup phase)
• **Year 2:** 4,500,000-8,000,000 RWF revenue (growth phase)
• **Year 3:** 8,000,000-15,000,000 RWF revenue (expansion phase)
• **Year 4:** 15,000,000-25,000,000 RWF revenue (maturity phase)
• **Year 5:** 25,000,000-40,000,000 RWF revenue (optimization phase)
• **Break-even Point:** 6-10 months
• **ROI:** 200-350% by Year 3

**14. Business Plan Generation:**
📋 **Generate Complete Business Plan** - Click to create a comprehensive business plan including:
• Executive Summary & Company Description
• Market Analysis & Competitive Analysis
• Financial Projections & Cash Flow Analysis
• Marketing Strategy & Sales Forecasts
• Operations Plan & Management Structure
• Risk Analysis & Mitigation Strategies

💼 **Export Options:** PDF, Word, Excel, PowerPoint formats available

Would you like details on location selection, equipment specifications, marketing strategies, or generate a complete business plan?";
    }
    
    // Guesthouse business specific response
    if (strpos($message, 'guesthouse:') !== false || strpos($message, 'guesthouse') !== false || 
        strpos($message, 'bed and breakfast:') !== false || strpos($message, 'bed and breakfast') !== false) {
        $specificResponse = "Excellent choice! Guesthouses are highly profitable in Musanze's tourism market. Here's your complete guide:

🏠 **Guesthouse Business in Musanze:**

**1. Business Overview:**
• **Accommodation Type:** Small-scale, intimate lodging (5-15 rooms)
• **Target Market:** Budget-conscious tourists, backpackers, business travelers
• **Unique Selling Point:** Personal service, local experience, affordable luxury

**2. Startup Investment:**
• **Property Purchase/Rent:** 50,000,000-200,000,000 RWF
• **Renovation & Setup:** 20,000,000-80,000,000 RWF
• **Furniture & Equipment:** 15,000,000-50,000,000 RWF
• **Licenses & Permits:** 2,000,000-8,000,000 RWF
• **Marketing & Branding:** 3,000,000-10,000,000 RWF
• **Total Startup:** 90,000,000-348,000,000 RWF

**3. Revenue Potential:**
• **Room Rates:** 25,000-80,000 RWF per night
• **Occupancy Rate:** 60-80% (peak season)
• **Monthly Revenue:** 2,250,000-9,600,000 RWF
• **Additional Services:** 20-30% extra revenue

**4. Prime Locations:**
• **Near Volcanoes National Park:** Premium tourism location
• **Musanze Town Center:** Business and cultural access
• **Kinigi:** Close to gorilla trekking
• **Ruhengeri:** Tourist hub with amenities

**5. Target Customers:**
• **International Tourists:** Budget-conscious travelers
• **Backpackers:** Young, adventurous travelers
• **Business Travelers:** Professionals on extended stays
• **Local Tourists:** Weekend getaways
• **Volunteers:** Long-term stays for NGO workers

**6. Service Offerings:**
• **Accommodation:** Clean, comfortable rooms
• **Breakfast Service:** Local and international options
• **Tour Arrangements:** Gorilla trekking, volcano tours
• **Airport Transfers:** Convenient transportation
• **Local Information:** Tourist guidance and recommendations

**7. Marketing Strategy:**
• **Online Booking:** Booking.com, Airbnb, TripAdvisor
• **Social Media:** Instagram, Facebook showcasing rooms and views
• **Tour Operator Partnerships:** Commission-based referrals
• **Local Tourism Board:** Official listings and promotions
• **Guest Reviews:** Encourage positive reviews and testimonials

**8. Operational Tips:**
• **Personal Service:** Owner-operated for authentic experience
• **Clean Standards:** Maintain high cleanliness and hygiene
• **Local Staff:** Hire knowledgeable local employees
• **Flexible Check-in:** Accommodate different arrival times
• **Cultural Integration:** Offer local experiences and meals

**9. Legal Requirements:**
• **Business Registration:** RDB registration
• **Tourism License:** RDB tourism accommodation license
• **Tax Registration:** RRA compliance
• **Health & Safety:** Fire safety, food handling permits
• **Insurance:** Comprehensive property and liability insurance

**10. Success Factors:**
• **Location:** Accessible, safe, and attractive area
• **Service Quality:** Friendly, helpful, professional staff
• **Cleanliness:** Spotless rooms and common areas
• **Value for Money:** Competitive pricing with good amenities
• **Local Knowledge:** Expert advice on attractions and activities

**11. Growth Opportunities:**
• **Room Expansion:** Add more rooms as demand grows
• **Service Upgrades:** Add restaurant, bar, or spa services
• **Tour Operations:** Organize and lead local tours
• **Event Hosting:** Weddings, conferences, retreats
• **Franchise Model:** Replicate successful model elsewhere

**12. Challenges & Solutions:**
• **Seasonal Demand:** Diversify with business travelers and events
• **Competition:** Focus on unique experiences and personal service
• **Staff Management:** Invest in training and fair compensation
• **Maintenance:** Regular upkeep and renovation schedule

Would you like details on property selection, renovation planning, or marketing strategies?";
    }
    
    // Eco-lodges business specific response
    if (strpos($message, 'eco-lodge:') !== false || strpos($message, 'eco-lodge') !== false || 
        strpos($message, 'ecolodge:') !== false || strpos($message, 'ecolodge') !== false) {
        $specificResponse = "Excellent choice! Eco-lodges are premium tourism businesses in Musanze. Here's your complete guide:

🌿 **Eco-Lodge Business in Musanze:**

**1. Business Overview:**
• **Accommodation Type:** Sustainable, environmentally-friendly luxury lodging
• **Target Market:** Eco-conscious tourists, nature lovers, premium travelers
• **Unique Selling Point:** Environmental sustainability, nature immersion, luxury comfort

**2. Startup Investment:**
• **Land Purchase:** 100,000,000-500,000,000 RWF
• **Eco-Friendly Construction:** 200,000,000-800,000,000 RWF
• **Solar/Green Systems:** 50,000,000-150,000,000 RWF
• **Furniture & Equipment:** 30,000,000-100,000,000 RWF
• **Licenses & Permits:** 5,000,000-15,000,000 RWF
• **Total Startup:** 385,000,000-1,565,000,000 RWF

**3. Revenue Potential:**
• **Room Rates:** 150,000-500,000 RWF per night
• **Occupancy Rate:** 70-90% (peak season)
• **Monthly Revenue:** 6,300,000-22,500,000 RWF
• **Additional Services:** 40-50% extra revenue

**4. Prime Locations:**
• **Volcanoes National Park Buffer Zone:** Premium nature location
• **Mountain Slopes:** Scenic views and hiking access
• **Forest Areas:** Immersive nature experience
• **Near Water Sources:** Rivers, lakes for eco-activities

**5. Target Customers:**
• **Eco-Tourists:** Environmentally conscious travelers
• **Nature Enthusiasts:** Bird watchers, hikers, photographers
• **Luxury Travelers:** High-end sustainable tourism
• **Wellness Seekers:** Yoga retreats, meditation, spa services
• **Adventure Travelers:** Volcano trekking, gorilla watching

**6. Service Offerings:**
• **Luxury Accommodation:** Eco-friendly, comfortable rooms
• **Nature Activities:** Guided hikes, bird watching, wildlife viewing
• **Wellness Services:** Spa, yoga, meditation sessions
• **Educational Programs:** Environmental awareness, conservation
• **Gourmet Dining:** Organic, locally-sourced meals

**7. Marketing Strategy:**
• **Eco-Tourism Platforms:** Green travel websites and directories
• **Luxury Travel Agents:** High-end tour operator partnerships
• **Social Media:** Instagram, Facebook showcasing nature and sustainability
• **Travel Bloggers:** Eco-tourism and luxury travel influencers
• **Certification Programs:** Green tourism certifications and awards

**8. Operational Tips:**
• **Sustainability Practices:** Solar power, water conservation, waste reduction
• **Local Sourcing:** Use local materials, food, and services
• **Staff Training:** Environmental education and conservation awareness
• **Guest Education:** Inform guests about local ecology and conservation
• **Community Involvement:** Support local conservation and community projects

**9. Legal Requirements:**
• **Business Registration:** RDB registration
• **Environmental Permits:** Environmental impact assessments
• **Tourism License:** RDB luxury accommodation license
• **Land Use Permits:** Proper zoning and land use approvals
• **Conservation Compliance:** National park and wildlife regulations

**10. Success Factors:**
• **Authentic Sustainability:** Genuine environmental practices
• **Location:** Breathtaking natural setting
• **Service Excellence:** Luxury service with environmental consciousness
• **Unique Experiences:** Exclusive nature and cultural activities
• **Community Integration:** Positive relationships with local communities

**11. Growth Opportunities:**
• **Room Expansion:** Add more eco-friendly accommodations
• **Activity Expansion:** More nature and adventure activities
• **Wellness Programs:** Yoga retreats, meditation workshops
• **Conservation Projects:** Partner with wildlife conservation organizations
• **Franchise Model:** Replicate successful eco-lodge concept

**12. Challenges & Solutions:**
• **High Initial Investment:** Secure funding from eco-tourism investors
• **Seasonal Demand:** Develop year-round activities and markets
• **Environmental Compliance:** Work closely with conservation authorities
• **Staff Training:** Invest in environmental and hospitality education

Would you like details on sustainable construction, environmental permits, or luxury service standards?";
    }
    
    // If specific response found, use it; otherwise try ML model
    if ($specificResponse) {
        $response = $specificResponse;
    } else {
        // Try ML model for Musanze-related queries
        $mlResponse = getMusanzeMLResponse($message);
        
        if ($mlResponse) {
            $response = $mlResponse;
        } else {
    $response = generateAIResponse($message, $history);
        }
    }
    
    echo json_encode([
        'response' => $response,
        'timestamp' => date('Y-m-d H:i:s'),
        'ml_enhanced' => isset($mlResponse) && $mlResponse ? true : false
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error',
        'message' => 'An error occurred while processing your request'
    ]);
}
?>

