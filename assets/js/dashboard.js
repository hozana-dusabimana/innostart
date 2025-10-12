// Dashboard JavaScript Functionality

class Dashboard {
    constructor() {
        this.currentSection = 'dashboard';
        this.chatHistory = [];
        this.searchResults = [];
        this.init();
    }

    init() {
        this.checkAuthentication();
        this.setupNavigation();
        this.setupChat();
        this.setupSearch();
        this.setupBusinessPlan();
        this.setupCharts();
        this.setupMobileMenu();
        this.loadDashboardData();
    }

    // Authentication System
    async checkAuthentication() {
        try {
            const response = await fetch('api/check-session.php');
            const result = await response.json();

            if (result.success && result.logged_in) {
                // User is authenticated, update UI
                this.updateUserInfo(result.user);
            } else {
                // User is not authenticated, redirect to login
                this.redirectToLogin();
            }
        } catch (error) {
            console.error('Authentication check failed:', error);
            this.redirectToLogin();
        }
    }

    updateUserInfo(user) {
        document.getElementById('userAvatar').textContent = user.avatar || 'U';
        document.getElementById('userName').textContent = user.name || 'User';

        // Store user info for later use
        this.currentUser = user;
    }

    redirectToLogin() {
        // Clear any stored login data
        localStorage.removeItem('user');
        localStorage.removeItem('isLoggedIn');

        // Redirect to login page
        window.location.href = 'login.html';
    }

    async logout() {
        try {
            const response = await fetch('api/auth.php?action=logout');
            const result = await response.json();

            if (result.success) {
                // Clear local storage
                localStorage.removeItem('user');
                localStorage.removeItem('isLoggedIn');
                localStorage.removeItem('rememberMe');

                // Redirect to login
                window.location.href = 'login.html';
            }
        } catch (error) {
            console.error('Logout error:', error);
            // Force redirect even if logout fails
            window.location.href = 'login.html';
        }
    }

    // Navigation System
    setupNavigation() {
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const sectionId = link.getAttribute('data-section');
                this.showSection(sectionId);
            });
        });
    }

    showSection(sectionId) {
        // Hide all sections
        document.querySelectorAll('.section').forEach(section => {
            section.classList.remove('active');
        });

        // Remove active class from all nav links
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });

        // Show selected section
        document.getElementById(sectionId).classList.add('active');

        // Add active class to corresponding nav link
        document.querySelector(`[data-section="${sectionId}"]`).classList.add('active');

        // Update content area class for chat section
        const contentArea = document.querySelector('.content-area');
        if (sectionId === 'chat') {
            contentArea.classList.add('chat-section');
        } else {
            contentArea.classList.remove('chat-section');
        }

        // Update page title
        const titles = {
            'dashboard': 'Dashboard',
            'chat': 'AI Chat',
            'search': 'Search',
            'business-plan': 'Business Plan',
            'analytics': 'Analytics',
            'settings': 'Settings'
        };
        document.getElementById('page-title').textContent = titles[sectionId];

        this.currentSection = sectionId;

        // Initialize section-specific functionality
        if (sectionId === 'analytics') {
            this.initCharts();
        }
    }

    // Chat System
    setupChat() {
        const chatInput = document.getElementById('chat-input');
        const sendBtn = document.querySelector('.send-btn');

        if (chatInput) {
            chatInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.sendMessage();
                }
            });
        }

        if (sendBtn) {
            sendBtn.addEventListener('click', () => {
                this.sendMessage();
            });
        }
    }

    async sendMessage() {
        const input = document.getElementById('chat-input');
        const message = input.value.trim();

        if (message) {
            // Check for export requests
            if (this.handleExportRequest(message)) {
                input.value = '';
                return;
            }

            this.addMessage(message, 'user');
            this.chatHistory.push({ role: 'user', content: message });
            input.value = '';

            // Show typing indicator
            this.showTypingIndicator();

            try {
                // Call the API for real responses
                const response = await fetch('api/chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        message: message,
                        history: this.chatHistory
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                // Hide typing indicator
                this.hideTypingIndicator();

                // Check if we have a valid response
                if (data && data.response) {
                    // Add AI response
                    this.addMessage(data.response, 'assistant');
                    this.chatHistory.push({ role: 'assistant', content: data.response });

                    // Add activity to dashboard
                    this.addActivity('chat', 'AI Chat Session', `Discussed: ${message.substring(0, 50)}${message.length > 50 ? '...' : ''}`);
                } else {
                    throw new Error('Invalid response format');
                }

            } catch (error) {
                console.error('Chat API error:', error);
                this.hideTypingIndicator();

                // Fallback to local response
                const response = this.generateAIResponse(message);
                this.addMessage(response, 'assistant');
                this.chatHistory.push({ role: 'assistant', content: response });

                // Add activity to dashboard
                this.addActivity('chat', 'AI Chat Session', `Discussed: ${message.substring(0, 50)}${message.length > 50 ? '...' : ''}`);
            }
        }
    }

    // Handle export requests from chat
    handleExportRequest(message) {
        const lowerMessage = message.toLowerCase();

        // Check for export requests
        if (lowerMessage.includes('pdf export') || lowerMessage.includes('generate pdf')) {
            this.showExportOptions('pdf');
            return true;
        }
        if (lowerMessage.includes('word export') || lowerMessage.includes('generate word')) {
            this.showExportOptions('word');
            return true;
        }
        if (lowerMessage.includes('excel export') || lowerMessage.includes('generate excel')) {
            this.showExportOptions('excel');
            return true;
        }
        if (lowerMessage.includes('powerpoint export') || lowerMessage.includes('generate powerpoint')) {
            this.showExportOptions('powerpoint');
            return true;
        }

        return false;
    }

    // Show export options modal
    showExportOptions(format) {
        const businessTypes = [
            'Mountain Hiking Tours', 'Volcano Trekking', 'Local Restaurant', 'Eco-lodges',
            'Food Processing', 'Coffee Processing', 'Local Transport', 'Souvenir Shop',
            'Local Guide Services', 'Organic Farming', 'Guesthouse', 'Internet Cafe'
        ];

        let modalHtml = `
            <div id="exportModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; display: flex; justify-content: center; align-items: center;">
                <div style="background: white; padding: 30px; border-radius: 10px; max-width: 500px; width: 90%; max-height: 80%; overflow-y: auto;">
                    <h3 style="margin: 0 0 20px 0; color: #2c3e50;">Export Business Plan - ${format.toUpperCase()}</h3>
                    <p style="margin: 0 0 20px 0; color: #7f8c8d;">Select a business type to generate your business plan:</p>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-bottom: 20px;">`;

        businessTypes.forEach(businessType => {
            modalHtml += `
                <button onclick="exportBusinessPlan('${businessType}', '${format}'); closeExportModal();" 
                        style="background: #3498db; color: white; border: none; padding: 12px; border-radius: 6px; cursor: pointer; text-align: left;">
                    ${businessType}
                </button>`;
        });

        modalHtml += `
                    </div>
                    <button onclick="closeExportModal()" style="background: #95a5a6; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer;">Cancel</button>
                </div>
            </div>`;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }

    addMessage(content, sender) {
        const messagesContainer = document.getElementById('chat-messages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}`;

        const avatar = sender === 'user' ? 'U' : 'AI';
        const avatarClass = sender === 'user' ? 'primary' : 'success';

        messageDiv.innerHTML = `
            <div class="message-avatar" style="background: var(--${avatarClass}-color);">${avatar}</div>
            <div class="message-content">${this.formatMessage(content)}</div>
        `;

        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    formatMessage(content) {
        // Basic markdown-like formatting
        let formatted = content
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/`(.*?)`/g, '<code>$1</code>')
            .replace(/\n/g, '<br>');

        // Add export buttons for business plan sections
        if (content.includes('Export Options:') && content.includes('PDF Export:')) {
            formatted = this.addExportButtons(formatted, content);
        }

        // Add quick export buttons for any business response
        if (content.includes('Business in Musanze') && content.includes('Financial Projections')) {
            formatted = this.addQuickExportButtons(formatted, content);
        }

        return formatted;
    }

    addExportButtons(formatted, content) {
        // Extract business type from the content
        let businessType = 'Mountain Hiking Tours'; // default

        // Try to detect business type from content
        const businessTypes = [
            'Mountain Hiking Tours', 'Volcano Trekking', 'Local Restaurant', 'Eco-lodges',
            'Food Processing', 'Coffee Processing', 'Local Transport', 'Souvenir Shop',
            'Local Guide Services', 'Organic Farming', 'Guesthouse', 'Internet Cafe'
        ];

        for (const type of businessTypes) {
            if (content.includes(type + ' Business in Musanze')) {
                businessType = type;
                break;
            }
        }

        // Replace export text with actual buttons
        const exportButtonsHtml = `
            <div style="margin: 15px 0; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #3498db;">
                <h4 style="margin: 0 0 10px 0; color: #2c3e50;">💼 Export Business Plan</h4>
                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                    <button onclick="exportBusinessPlan('${businessType}', 'pdf')" style="background: #e74c3c; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px;">📄 PDF</button>
                    <button onclick="exportBusinessPlan('${businessType}', 'word')" style="background: #3498db; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px;">📝 Word</button>
                    <button onclick="exportBusinessPlan('${businessType}', 'excel')" style="background: #27ae60; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px;">📊 Excel</button>
                    <button onclick="exportBusinessPlan('${businessType}', 'powerpoint')" style="background: #f39c12; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px;">📽️ PowerPoint</button>
                </div>
                <p style="margin: 10px 0 0 0; font-size: 12px; color: #7f8c8d;">Click any button to generate and download your business plan</p>
            </div>`;

        // Replace the export options text with buttons
        formatted = formatted.replace(
            /💼 \*\*Export Options:\*\* PDF, Word, Excel, PowerPoint formats available<br>📄 \*\*PDF Export:\*\* Click to generate PDF business plan<br>📝 \*\*Word Export:\*\* Click to generate Word document<br>📊 \*\*Excel Export:\*\* Click to generate Excel spreadsheet<br>📽️ \*\*PowerPoint Export:\*\* Click to generate presentation/g,
            exportButtonsHtml
        );

        return formatted;
    }

    addQuickExportButtons(formatted, content) {
        // Extract business type from the content
        let businessType = 'Mountain Hiking Tours'; // default

        // Try to detect business type from content
        const businessTypes = [
            'Mountain Hiking Tours', 'Volcano Trekking', 'Local Restaurant', 'Eco-lodges',
            'Food Processing', 'Coffee Processing', 'Local Transport', 'Souvenir Shop',
            'Local Guide Services', 'Organic Farming', 'Guesthouse', 'Internet Cafe'
        ];

        for (const type of businessTypes) {
            if (content.includes(type + ' Business in Musanze')) {
                businessType = type;
                break;
            }
        }

        // Add quick export buttons at the end of the message
        const quickExportButtons = `
            <div style="margin: 20px 0; padding: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; text-align: center;">
                <h4 style="margin: 0 0 15px 0; color: white; font-size: 16px;">🚀 Quick Export Business Plan</h4>
                <div style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;">
                    <button onclick="exportBusinessPlan('${businessType}', 'pdf')" style="background: #e74c3c; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">📄 PDF</button>
                    <button onclick="exportBusinessPlan('${businessType}', 'word')" style="background: #3498db; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">📝 Word</button>
                    <button onclick="exportBusinessPlan('${businessType}', 'excel')" style="background: #27ae60; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">📊 Excel</button>
                    <button onclick="exportBusinessPlan('${businessType}', 'powerpoint')" style="background: #f39c12; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">📽️ PowerPoint</button>
                </div>
                <p style="margin: 10px 0 0 0; font-size: 12px; color: rgba(255,255,255,0.8);">Click any button to generate your ${businessType} business plan</p>
            </div>`;

        return formatted + quickExportButtons;
    }

    showTypingIndicator() {
        const messagesContainer = document.getElementById('chat-messages');
        const typingDiv = document.createElement('div');
        typingDiv.className = 'message assistant typing-indicator';
        typingDiv.innerHTML = `
            <div class="message-avatar" style="background: var(--success-color);">AI</div>
            <div class="message-content">
                <div class="typing-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        `;

        messagesContainer.appendChild(typingDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    hideTypingIndicator() {
        const typingIndicator = document.querySelector('.typing-indicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }

    generateAIResponse(userMessage) {
        const responses = {
            'business ideas in musanze': "Great! Here are specific business ideas for Musanze, Rwanda:\n\n🏔️ **Tourism & Hospitality:**\n• Mountain hiking guide services\n• Eco-lodges and guesthouses\n• Cultural tourism experiences\n• Volcano trekking packages\n\n🌱 **Agriculture & Food:**\n• Organic vegetable farming\n• Coffee processing and export\n• Local food restaurants\n• Agricultural equipment rental\n\n🏪 **Retail & Services:**\n• Mobile money services\n• Internet café with printing\n• Motorcycle taxi services\n• Local grocery stores\n\n💻 **Technology:**\n• Mobile app development\n• Digital marketing services\n• Online education platforms\n• E-commerce for local products\n\nWhich of these interests you most? I can provide detailed guidance!",

            'business ideas in kigali': "Here are business ideas for Kigali, Rwanda:\n\n🏢 **Tech & Innovation:**\n• Software development company\n• Mobile app development\n• Digital marketing agency\n• E-commerce platforms\n\n🍽️ **Food & Beverage:**\n• Restaurant chains\n• Food delivery services\n• Catering businesses\n• Coffee shops\n\n🚗 **Transportation:**\n• Ride-sharing services\n• Logistics and delivery\n• Car rental services\n• Public transport solutions\n\n🏥 **Healthcare:**\n• Telemedicine platforms\n• Health clinics\n• Pharmaceutical distribution\n• Medical equipment sales\n\nWhich sector interests you?",

            'give me ideas': "Here are some profitable business ideas you can start:\n\n💻 **Technology:**\n• Mobile app development\n• Website design services\n• Digital marketing agency\n• E-commerce store\n\n🍽️ **Food & Beverage:**\n• Restaurant or café\n• Food delivery service\n• Catering business\n• Food truck\n\n🏪 **Retail & Services:**\n• Online store\n• Consulting services\n• Event planning\n• Cleaning services\n\n🌱 **Agriculture:**\n• Organic farming\n• Food processing\n• Agricultural consulting\n• Farm-to-table delivery\n\nWhat type of business interests you most? I can provide specific guidance!",

            'business plan': "I'll help you create a comprehensive business plan! Here's the structure you need:\n\n📋 **Essential Sections:**\n1. **Executive Summary** - Overview of your business\n2. **Company Description** - What you do and why\n3. **Market Analysis** - Target customers and competition\n4. **Organization Structure** - Team and management\n5. **Service/Product Line** - What you're selling\n6. **Marketing Strategy** - How you'll reach customers\n7. **Financial Projections** - Revenue, costs, and profits\n\n💡 **Pro Tips:**\n• Keep it concise (10-20 pages)\n• Use data and research to support claims\n• Include realistic financial projections\n• Update it regularly as your business grows\n\nWould you like me to help you with any specific section?",

            'funding': "Here are the main funding options for startups:\n\n💰 **Self-Funding (Bootstrapping):**\n• Use personal savings\n• Reinvest profits\n• Keep full control\n• Best for: Small businesses, service companies\n\n👥 **Angel Investors:**\n• Individual investors\n• $25K - $500K typically\n• Provide mentorship\n• Best for: Early-stage startups\n\n🏢 **Venture Capital:**\n• Professional investors\n• $500K+ typically\n• Expect high returns\n• Best for: High-growth tech companies\n\n🏦 **Bank Loans:**\n• Traditional financing\n• Requires collateral\n• Fixed repayment terms\n• Best for: Established businesses\n\n🌐 **Crowdfunding:**\n• Online platforms (Kickstarter, GoFundMe)\n• Pre-sell products\n• Build customer base\n• Best for: Product-based businesses\n\nWhat's your business stage and funding needs?",

            'market research': "Market research is crucial for understanding your customers and competition. Key steps include: defining your target market, analyzing competitors, conducting surveys/interviews, studying industry trends, and identifying market gaps. Would you like help with any specific aspect of market research?",

            'marketing': "Effective marketing strategies include: social media marketing, content marketing, SEO, email campaigns, partnerships, and local advertising. The best approach depends on your target audience and budget. What type of business are you planning to start?",

            'legal': "Legal considerations for startups include: business registration, licenses and permits, tax obligations, intellectual property protection, contracts, and insurance. Requirements vary by location and business type. What specific legal aspect are you concerned about?",

            'website development': "Great choice! Website development is a profitable business. Here are specific ideas:\n\n💻 **Web Development Services:**\n• Custom website design and development\n• E-commerce websites (online stores)\n• Business websites with CMS\n• Portfolio websites for professionals\n• Restaurant websites with online ordering\n• Real estate websites with property listings\n\n🎯 **Target Markets:**\n• Small businesses needing online presence\n• Restaurants wanting online ordering\n• Real estate agents\n• Freelancers and consultants\n• Non-profit organizations\n\n💰 **Pricing:**\n• Basic websites: $500-$2,000\n• E-commerce sites: $2,000-$10,000\n• Custom applications: $5,000+\n\nWould you like guidance on getting started or finding clients?",

            'automation software': "Excellent! Automation software is a high-demand business. Here are specific opportunities:\n\n🤖 **Automation Software Ideas:**\n• Business process automation (BPA)\n• Social media scheduling tools\n• Email marketing automation\n• Inventory management systems\n• Customer service chatbots\n• Data entry automation\n• Workflow management tools\n• HR process automation\n\n🎯 **Target Industries:**\n• Small businesses wanting efficiency\n• E-commerce stores\n• Real estate agencies\n• Healthcare practices\n• Educational institutions\n• Manufacturing companies\n\n💰 **Business Models:**\n• SaaS (Software as a Service) - $29-$299/month\n• One-time software sales - $500-$5,000\n• Custom automation projects - $2,000-$50,000\n• Consulting and implementation services\n\nWhat type of automation interests you most?",

            'technology business idea': "Here are specific technology business ideas you can start:\n\n💻 **Web Development:**\n• Custom website design and development\n• E-commerce website creation\n• WordPress theme development\n• Web application development\n\n📱 **Mobile Apps:**\n• Business productivity apps\n• E-commerce mobile apps\n• Utility apps (calculators, converters)\n• Educational apps\n\n🤖 **Automation & Software:**\n• Business process automation\n• Social media management tools\n• Email marketing automation\n• Inventory management systems\n\n☁️ **Cloud Services:**\n• Cloud migration consulting\n• Data backup solutions\n• Cloud security services\n• Remote work tools\n\n🎯 **Digital Marketing:**\n• SEO services\n• Social media management\n• Content marketing\n• PPC advertising management\n\nWhich technology area interests you most? I can provide detailed guidance!",

            'technology': "Technology can give your startup a competitive edge. Consider: website development, mobile apps, CRM systems, analytics tools, automation software, and cloud services. What technology needs does your business have?",

            'team': "Building a strong team is essential for startup success. Consider: defining roles and responsibilities, creating job descriptions, networking, using recruitment platforms, offering competitive packages, and fostering company culture. What positions are you looking to fill?",

            'tourism': "Great choice! Tourism and hospitality is a thriving industry. Here are specific business ideas:\n\n🏔️ **Tourism & Hospitality Business Ideas:**\n• Tour guide services (city tours, nature tours)\n• Bed & breakfast or guesthouse\n• Restaurant or café with local cuisine\n• Travel agency or booking service\n• Adventure tourism (hiking, biking, water sports)\n• Cultural experiences and workshops\n• Transportation services (airport shuttles, city tours)\n• Souvenir and gift shops\n\n🎯 **Target Markets:**\n• International tourists\n• Local weekend travelers\n• Business travelers\n• Adventure seekers\n• Cultural enthusiasts\n• Food lovers\n\n💰 **Revenue Streams:**\n• Direct bookings and reservations\n• Commission from tour bookings\n• Food and beverage sales\n• Souvenir and merchandise sales\n• Transportation fees\n• Workshop and experience fees\n\nWhat type of tourism business interests you most?",

            'competition': "Competitive analysis helps you understand your market position. Research: direct and indirect competitors, their strengths and weaknesses, pricing strategies, marketing approaches, and customer reviews. This information helps you differentiate your business."
        };

        const lowerMessage = userMessage.toLowerCase();

        for (const [keyword, response] of Object.entries(responses)) {
            if (lowerMessage.includes(keyword)) {
                return response;
            }
        }

        // Default responses
        const defaultResponses = [
            "I'd be happy to help you with business ideas! Here are some popular categories to explore:\n\n💻 **Technology:** Website development, mobile apps, automation software\n🏔️ **Tourism:** Tour guide services, accommodation, restaurants\n🌱 **Agriculture:** Organic farming, food processing, farm-to-table\n🏪 **Retail:** Online stores, consulting services, event planning\n💰 **Finance:** Financial consulting, investment services, fintech\n\nWhich category interests you most?",

            "Great question! Let me suggest some trending business opportunities:\n\n📱 **Digital Services:** Social media management, content creation, online tutoring\n🍽️ **Food & Beverage:** Food delivery, catering, specialty restaurants\n🏥 **Health & Wellness:** Fitness coaching, mental health services, wellness products\n🎓 **Education:** Online courses, skill training, educational apps\n🌍 **Sustainability:** Green energy, eco-friendly products, waste management\n\nWhat type of business are you considering?",

            "I can help you explore various business opportunities! Here are some proven business models:\n\n🛒 **E-commerce:** Online retail, dropshipping, digital products\n🏢 **B2B Services:** Consulting, software solutions, professional services\n👥 **Marketplace:** Connecting buyers and sellers, platform businesses\n🏭 **Manufacturing:** Product creation, custom manufacturing, local production\n🎯 **Niche Services:** Specialized services for specific industries or demographics\n\nWhich business model appeals to you?",

            "That's an exciting question! Here are some high-potential business ideas:\n\n🤖 **Automation:** Business process automation, workflow optimization\n🌐 **Remote Services:** Virtual assistance, remote consulting, online coaching\n🏠 **Home Services:** Cleaning, maintenance, home improvement\n🚚 **Logistics:** Delivery services, supply chain solutions, last-mile delivery\n💡 **Innovation:** New product development, technology solutions, creative services\n\nWhat area would you like to explore further?",

            "I'm here to help you find the perfect business opportunity! Consider these factors:\n\n🎯 **Your Skills:** What are you good at? What do you enjoy doing?\n💰 **Investment:** How much capital do you have to start?\n⏰ **Time:** How much time can you dedicate to your business?\n🌍 **Location:** Where do you want to operate? Local, national, or global?\n📈 **Growth:** Do you want a lifestyle business or high-growth startup?\n\nTell me more about your preferences and I'll suggest specific ideas!"
        ];

        return defaultResponses[Math.floor(Math.random() * defaultResponses.length)];
    }

    // Search System
    setupSearch() {
        const searchInput = document.getElementById('search-input');
        const searchFilter = document.getElementById('search-filter');

        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.performSearch(e.target.value);
            });
        }

        if (searchFilter) {
            searchFilter.addEventListener('change', (e) => {
                this.filterSearchResults(e.target.value);
            });
        }
    }

    performSearch(query) {
        const results = document.querySelectorAll('.search-result-item');
        const lowerQuery = query.toLowerCase();

        results.forEach(result => {
            const text = result.textContent.toLowerCase();
            if (text.includes(lowerQuery)) {
                result.style.display = 'block';
                result.style.animation = 'fadeIn 0.3s ease-in';
            } else {
                result.style.display = 'none';
            }
        });
    }

    filterSearchResults(category) {
        const results = document.querySelectorAll('.search-result-item');

        results.forEach(result => {
            if (category === 'all') {
                result.style.display = 'block';
            } else {
                // This would be enhanced with actual category data
                result.style.display = 'block';
            }
        });
    }

    // Business Plan System
    setupBusinessPlan() {
        const form = document.getElementById('businessPlanForm');

        if (form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                await this.generateBusinessPlan();
            });
        }

        // Add export functionality to global scope
        window.exportBusinessPlan = this.exportBusinessPlan.bind(this);
    }

    async generateBusinessPlan() {
        const formData = {
            businessName: document.getElementById('businessName').value,
            businessType: document.getElementById('businessType').value,
            targetMarket: document.getElementById('targetMarket').value,
            missionStatement: document.getElementById('missionStatement').value,
            competitiveAdvantage: document.getElementById('competitiveAdvantage').value,
            fundingNeeds: document.getElementById('fundingNeeds').value
        };

        const submitButton = document.querySelector('#businessPlanForm button[type="submit"]');
        const originalText = submitButton.innerHTML;

        try {
            submitButton.innerHTML = '<span class="loading"></span> Opening Business Plan...';
            submitButton.disabled = true;

            const response = await fetch('api/business-plan.php?' + Date.now(), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });

            if (response.ok) {
                const htmlContent = await response.text();

                const newWindow = window.open('', '_blank', 'width=1000,height=700,scrollbars=yes,resizable=yes');

                if (newWindow) {
                    const enhancedHtml = this.addPrintButtonToHtml(htmlContent, formData.businessName);

                    newWindow.document.open();
                    newWindow.document.write(enhancedHtml);
                    newWindow.document.close();

                    newWindow.focus();

                    this.showNotification('✅ Business plan opened in new window! Click "Print to PDF" button to create PDF.', 'success');

                    // Add activity to dashboard
                    this.addActivity('business_plan', 'Business Plan Created', `${formData.businessName} business plan generated successfully`);
                } else {
                    throw new Error('Popup blocked. Please allow popups for this site.');
                }
            } else {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

        } catch (error) {
            console.error('Business plan generation error:', error);
            this.showNotification('Error generating business plan. Please try again.', 'error');
        } finally {
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        }
    }

    addPrintButtonToHtml(htmlContent, businessName) {
        const printButtonHtml = `
            <div id="print-controls" style="
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 1000;
                background: white;
                padding: 15px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                border: 1px solid #ddd;
            ">
                <h3 style="margin: 0 0 10px 0; color: #2c3e50; font-size: 16px;">📄 Business Plan</h3>
                <button onclick="printToPDF()" style="
                    background: #007bff;
                    color: white;
                    border: none;
                    padding: 12px 20px;
                    border-radius: 6px;
                    cursor: pointer;
                    font-size: 14px;
                    font-weight: bold;
                    margin-right: 10px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                ">🖨️ Print to PDF</button>
                <button onclick="downloadHTML()" style="
                    background: #28a745;
                    color: white;
                    border: none;
                    padding: 12px 20px;
                    border-radius: 6px;
                    cursor: pointer;
                    font-size: 14px;
                    font-weight: bold;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                ">💾 Download HTML</button>
                <div style="margin-top: 10px; font-size: 12px; color: #666;">
                    <strong>Instructions:</strong><br>
                    1. Click "Print to PDF"<br>
                    2. Select "Save as PDF"<br>
                    3. Set margins to "Minimum"<br>
                    4. Click "Save"
                </div>
            </div>
            
            <script>
            function printToPDF() {
                window.print();
            }
            
            function downloadHTML() {
                const htmlContent = document.documentElement.outerHTML;
                const blob = new Blob([htmlContent], { type: 'text/html' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = '${businessName.replace(/\s+/g, '_')}_Business_Plan.html';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            }
            
            window.addEventListener('beforeprint', function() {
                document.getElementById('print-controls').style.display = 'none';
            });
            
            window.addEventListener('afterprint', function() {
                document.getElementById('print-controls').style.display = 'block';
            });
            </script>
            
            <style>
            @media print {
                #print-controls { display: none !important; }
                body { margin: 0; padding: 20px; }
            }
            </style>
        `;

        return htmlContent.replace('<body>', '<body>' + printButtonHtml);
    }

    // Charts System
    setupCharts() {
        // Charts will be initialized when analytics section is shown
    }

    initCharts() {
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart');
        if (revenueCtx && !revenueCtx.chart) {
            revenueCtx.chart = new Chart(revenueCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Revenue',
                        data: [12000, 19000, 15000, 25000, 22000, 30000],
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.1)'
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(0,0,0,0.1)'
                            }
                        }
                    }
                }
            });
        }

        // User Growth Chart
        const userGrowthCtx = document.getElementById('userGrowthChart');
        if (userGrowthCtx && !userGrowthCtx.chart) {
            userGrowthCtx.chart = new Chart(userGrowthCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'New Users',
                        data: [100, 150, 200, 180, 250, 300],
                        backgroundColor: 'rgba(40, 167, 69, 0.8)',
                        borderColor: '#28a745',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.1)'
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(0,0,0,0.1)'
                            }
                        }
                    }
                }
            });
        }
    }

    // Mobile Menu
    setupMobileMenu() {
        if (window.innerWidth <= 768) {
            const header = document.querySelector('.header');
            const menuButton = document.createElement('button');
            menuButton.innerHTML = '<i class="fas fa-bars"></i>';
            menuButton.className = 'btn btn-outline-primary me-3';
            menuButton.onclick = () => this.toggleSidebar();
            header.insertBefore(menuButton, header.firstChild);
        }
    }

    toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('open');
    }

    // Notification System
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notification);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    // Global Search
    setupGlobalSearch() {
        const globalSearch = document.getElementById('global-search');
        if (globalSearch) {
            globalSearch.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase();
                if (query.length > 2) {
                    this.performGlobalSearch(query);
                }
            });
        }
    }

    performGlobalSearch(query) {
        // This would integrate with a real search API
        console.log('Global search for:', query);
    }

    // Dashboard Data Management
    async loadDashboardData() {
        try {
            // Load stats
            await this.loadStats();

            // Load recent activities
            await this.loadRecentActivities();

        } catch (error) {
            console.error('Failed to load dashboard data:', error);
        }
    }

    async loadStats() {
        try {
            const response = await fetch('api/dashboard-data.php?action=stats');
            const result = await response.json();

            if (result.success) {
                this.updateStatsDisplay(result.data);
            }
        } catch (error) {
            console.error('Failed to load stats:', error);
        }
    }

    updateStatsDisplay(stats) {
        // Update Active Projects
        const activeProjectsElement = document.querySelector('.stat-card:nth-child(1) h3');
        if (activeProjectsElement) {
            activeProjectsElement.textContent = stats.active_projects || 0;
        }

        // Update Revenue Generated
        const revenueElement = document.querySelector('.stat-card:nth-child(2) h3');
        if (revenueElement) {
            const revenue = stats.revenue_generated || 0;
            revenueElement.textContent = this.formatCurrency(revenue);
        }

        // Update Total Users
        const usersElement = document.querySelector('.stat-card:nth-child(3) h3');
        if (usersElement) {
            usersElement.textContent = this.formatNumber(stats.total_users || 0);
        }

        // Update Success Rate
        const successElement = document.querySelector('.stat-card:nth-child(4) h3');
        if (successElement) {
            successElement.textContent = (stats.success_rate || 0) + '%';
        }
    }

    async loadRecentActivities() {
        try {
            const response = await fetch('api/dashboard-data.php?action=activities&limit=5');
            const result = await response.json();

            if (result.success) {
                this.updateActivitiesDisplay(result.data);
            }
        } catch (error) {
            console.error('Failed to load activities:', error);
        }
    }

    updateActivitiesDisplay(activities) {
        const activitiesContainer = document.querySelector('.activity-list');
        if (!activitiesContainer) return;

        activitiesContainer.innerHTML = '';

        activities.forEach(activity => {
            const activityElement = this.createActivityElement(activity);
            activitiesContainer.appendChild(activityElement);
        });
    }

    createActivityElement(activity) {
        const div = document.createElement('div');
        div.className = 'activity-item';

        const timeAgo = this.getTimeAgo(activity.timestamp);

        div.innerHTML = `
            <div class="activity-icon bg-${activity.color}">
                <i class="${activity.icon}"></i>
            </div>
            <div class="activity-content">
                <h6>${activity.title}</h6>
                <p>${activity.description}</p>
                <small class="text-muted">${timeAgo}</small>
            </div>
        `;

        return div;
    }

    formatCurrency(amount) {
        if (amount >= 1000000) {
            return (amount / 1000000).toFixed(1) + 'M RWF';
        } else if (amount >= 1000) {
            return (amount / 1000).toFixed(0) + 'K RWF';
        } else {
            return amount.toFixed(0) + ' RWF';
        }
    }

    formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    getTimeAgo(timestamp) {
        const now = Math.floor(Date.now() / 1000);
        const diff = now - timestamp;

        if (diff < 60) return 'Just now';
        if (diff < 3600) return Math.floor(diff / 60) + ' minutes ago';
        if (diff < 86400) return Math.floor(diff / 3600) + ' hours ago';
        if (diff < 2592000) return Math.floor(diff / 86400) + ' days ago';
        return Math.floor(diff / 2592000) + ' months ago';
    }

    // Method to add new activity (can be called from other parts of the app)
    async addActivity(type, title, description) {
        try {
            const response = await fetch('api/dashboard-data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'add_activity',
                    type: type,
                    title: title,
                    description: description
                })
            });

            const result = await response.json();

            if (result.success) {
                // Reload activities to show the new one
                await this.loadRecentActivities();
            }

            return result;
        } catch (error) {
            console.error('Failed to add activity:', error);
            return { success: false, error: error.message };
        }
    }

    // Export business plan functionality
    async exportBusinessPlan(businessType, format) {
        try {
            this.showNotification('Generating business plan...', 'info');

            const response = await fetch('api/export-business-plan.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    business_type: businessType,
                    format: format,
                    business_data: {}
                })
            });

            const result = await response.json();

            if (result.success) {
                if (format === 'pdf' || format === 'word' || format === 'powerpoint') {
                    // Open HTML in new window for printing/saving
                    const newWindow = window.open('', '_blank');
                    newWindow.document.write(result.html);
                    newWindow.document.close();

                    this.showNotification('Business plan opened in new window! Use browser print function to save as ' + format.toUpperCase(), 'success');
                } else if (format === 'excel') {
                    // Download CSV file
                    const blob = new Blob([result.csv], { type: 'text/csv' });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = result.filename;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);

                    this.showNotification('Excel file downloaded successfully!', 'success');
                }
            } else {
                this.showNotification('Error generating business plan: ' + result.error, 'error');
            }
        } catch (error) {
            console.error('Export error:', error);
            this.showNotification('Error generating business plan. Please try again.', 'error');
        }
    }
}

// Initialize Dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function () {
    window.dashboard = new Dashboard();
});

// Global functions for backward compatibility
function showSection(sectionId) {
    if (window.dashboard) {
        window.dashboard.showSection(sectionId);
    }
}

function sendMessage() {
    if (window.dashboard) {
        window.dashboard.sendMessage();
    }
}

// Global export function
async function exportBusinessPlan(businessType, format) {
    if (window.dashboard) {
        await window.dashboard.exportBusinessPlan(businessType, format);
    }
}

// Close export modal
function closeExportModal() {
    const modal = document.getElementById('exportModal');
    if (modal) {
        modal.remove();
    }
}

function handleChatKeyPress(event) {
    if (event.key === 'Enter') {
        sendMessage();
    }
}

function logout() {
    if (window.dashboard) {
        window.dashboard.logout();
    }
}
