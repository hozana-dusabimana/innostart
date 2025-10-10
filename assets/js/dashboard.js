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

                const data = await response.json();

                // Hide typing indicator
                this.hideTypingIndicator();

                // Add AI response
                this.addMessage(data.response, 'assistant');
                this.chatHistory.push({ role: 'assistant', content: data.response });

            } catch (error) {
                console.error('Chat API error:', error);
                this.hideTypingIndicator();

                // Fallback to local response
                const response = this.generateAIResponse(message);
                this.addMessage(response, 'assistant');
                this.chatHistory.push({ role: 'assistant', content: response });
            }
        }
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
        return content
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/`(.*?)`/g, '<code>$1</code>')
            .replace(/\n/g, '<br>');
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
