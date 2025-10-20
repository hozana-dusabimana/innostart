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

        // Initialize specific sections when shown
        if (sectionId === 'my-data') {
            initMyDataSection();
        }

        // Update content area class for chat section
        const contentArea = document.querySelector('.content-area');
        if (sectionId === 'chat') {
            contentArea.classList.add('chat-section');
        } else {
            contentArea.classList.remove('chat-section');
        }

        // Initialize section-specific functionality
        if (sectionId === 'analytics') {
            this.initCharts();
        } else if (sectionId === 'settings') {
            this.loadSettings();
        } else if (sectionId === 'business-plan') {
            this.loadBusinessPlanTemplates();
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
                // Call the enhanced chat API (now with Gemini AI integration)
                console.log('Sending message to API:', message);
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

                console.log('API response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log('API response data:', data);
                console.log('Response source:', data.source);
                console.log('ML enhanced:', data.ml_enhanced);

                // Hide typing indicator
                this.hideTypingIndicator();

                // Check if we have a valid response
                if (data && data.response) {
                    // Add AI response
                    this.addMessage(data.response, 'assistant');
                    this.chatHistory.push({ role: 'assistant', content: data.response });

                    // Log if we got dataset response
                    if (data.source === 'local_dataset' || data.ml_enhanced) {
                        console.log('✅ Successfully received dataset response!');
                    } else {
                        console.log('⚠️ Received non-dataset response from:', data.source);
                    }

                    // Add activity to dashboard
                    this.addActivity('chat', 'AI Chat Session', `Discussed: ${message.substring(0, 50)}${message.length > 50 ? '...' : ''}`);
                } else {
                    throw new Error('Invalid response format');
                }

            } catch (error) {
                console.error('Chat API error:', error);
                this.hideTypingIndicator();

                // Enhanced fallback - try to get response from enhanced system
                try {
                    const fallbackResponse = await this.getEnhancedFallbackResponse(message);
                    this.addMessage(fallbackResponse, 'assistant');
                    this.chatHistory.push({ role: 'assistant', content: fallbackResponse });
                } catch (fallbackError) {
                    console.error('Fallback error:', fallbackError);
                    // Final fallback to basic response
                    const response = this.generateAIResponse(message);
                    this.addMessage(response, 'assistant');
                    this.chatHistory.push({ role: 'assistant', content: response });
                }

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

        // Also add export buttons for detailed business information
        if (content.includes('DETAILED BUSINESS INFORMATION:') && content.includes('Export Options:')) {
            formatted = this.addExportButtons(formatted, content);
        }

        // Force add export buttons for any detailed business information
        if (content.includes('DETAILED BUSINESS INFORMATION:') && !formatted.includes('Export Business Plan')) {
            formatted = this.addExportButtons(formatted, content);
        }

        // Additional check - if we have export text but no buttons, force add them
        if (formatted.includes('💼 **Export Options:**') && !formatted.includes('Export Business Plan')) {
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

        // Check for business type in different formats
        for (const type of businessTypes) {
            if (content.includes(type + ' Business in Musanze') ||
                content.includes('DETAILED BUSINESS INFORMATION: ' + type.toUpperCase()) ||
                content.includes(type.toUpperCase())) {
                businessType = type;
                break;
            }
        }

        console.log('Adding export buttons for business type:', businessType);
        console.log('Formatted content before replacement:', formatted);

        // Replace export text with well-designed buttons
        const exportButtonsHtml = `
            <div style="margin: 20px 0; padding: 20px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border: 1px solid #dee2e6; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                <h4 style="margin: 0 0 15px 0; color: #495057; font-weight: 600; text-align: center;">
                    <i class="fas fa-download" style="margin-right: 8px; color: #6c757d;"></i>Export Business Plan
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 12px; margin-bottom: 15px;">
                    <button onclick="exportBusinessPlanFromChat('${businessType}', 'pdf')" 
                            style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); color: white; border: none; padding: 12px 8px; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(231, 76, 60, 0.3);"
                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(231, 76, 60, 0.4)'"
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(231, 76, 60, 0.3)'">
                        <i class="fas fa-file-pdf" style="display: block; font-size: 18px; margin-bottom: 4px;"></i>
                        <span>PDF</span>
                    </button>
                    <button onclick="exportBusinessPlanFromChat('${businessType}', 'word')" 
                            style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; border: none; padding: 12px 8px; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(52, 152, 219, 0.3);"
                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(52, 152, 219, 0.4)'"
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(52, 152, 219, 0.3)'">
                        <i class="fas fa-file-word" style="display: block; font-size: 18px; margin-bottom: 4px;"></i>
                        <span>Word</span>
                    </button>
                    <button onclick="exportBusinessPlanFromChat('${businessType}', 'excel')" 
                            style="background: linear-gradient(135deg, #27ae60 0%, #229954 100%); color: white; border: none; padding: 12px 8px; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(39, 174, 96, 0.3);"
                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(39, 174, 96, 0.4)'"
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(39, 174, 96, 0.3)'">
                        <i class="fas fa-file-excel" style="display: block; font-size: 18px; margin-bottom: 4px;"></i>
                        <span>Excel</span>
                    </button>
                    <button onclick="exportBusinessPlanFromChat('${businessType}', 'powerpoint')" 
                            style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); color: white; border: none; padding: 12px 8px; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(243, 156, 18, 0.3);"
                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(243, 156, 18, 0.4)'"
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(243, 156, 18, 0.3)'">
                        <i class="fas fa-file-powerpoint" style="display: block; font-size: 18px; margin-bottom: 4px;"></i>
                        <span>PPT</span>
                    </button>
                    <button onclick="saveBusinessPlanFromChat('${businessType}')" 
                            style="background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%); color: white; border: none; padding: 12px 8px; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(155, 89, 182, 0.3);"
                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(155, 89, 182, 0.4)'"
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(155, 89, 182, 0.3)'">
                        <i class="fas fa-save" style="display: block; font-size: 18px; margin-bottom: 4px;"></i>
                        <span>Save</span>
                    </button>
                </div>
                <p style="margin: 0; font-size: 12px; color: #6c757d; text-align: center;">
                    <i class="fas fa-info-circle" style="margin-right: 4px;"></i>Click any button to generate and download your business plan or save to My Data
                </p>
            </div>`;

        // Replace the export options text with buttons - try multiple patterns
        const patterns = [
            // Pattern 1: Exact match with <br> tags
            /💼 \*\*Export Options:\*\* PDF, Word, Excel, PowerPoint formats available<br>📄 \*\*PDF Export:\*\* Click to generate PDF business plan<br>📝 \*\*Word Export:\*\* Click to generate Word document<br>📊 \*\*Excel Export:\*\* Click to generate Excel spreadsheet<br>📽️ \*\*PowerPoint Export:\*\* Click to generate presentation/g,

            // Pattern 2: With additional text
            /💼 \*\*Export Options:\*\* PDF, Word, Excel, PowerPoint formats available<br>📄 \*\*PDF Export:\*\* Click to generate PDF business plan<br>📝 \*\*Word Export:\*\* Click to generate Word document<br>📊 \*\*Excel Export:\*\* Click to generate Excel spreadsheet<br>📽️ \*\*PowerPoint Export:\*\* Click to generate presentation<br><br>💡 \*\*Need help with business planning, funding, or legal requirements\? I can provide detailed guidance for each step!\*\*/g,

            // Pattern 3: More flexible pattern
            /💼 \*\*Export Options:\*\* PDF, Word, Excel, PowerPoint formats available.*?💡 \*\*Need help with business planning, funding, or legal requirements\? I can provide detailed guidance for each step!\*\*/gs,

            // Pattern 4: Even more flexible - just look for export options
            /💼 \*\*Export Options:\*\* PDF, Word, Excel, PowerPoint formats available.*?(?=💡|$)/gs
        ];

        let replaced = false;
        console.log('Trying patterns...');
        for (let i = 0; i < patterns.length; i++) {
            const pattern = patterns[i];
            console.log(`Pattern ${i + 1}:`, pattern);
            if (pattern.test(formatted)) {
                console.log(`Pattern ${i + 1} matched!`);
                formatted = formatted.replace(pattern, exportButtonsHtml);
                replaced = true;
                break;
            }
        }
        console.log('Pattern replacement result:', replaced);

        // If no pattern matched, try a simple replacement approach
        if (!replaced && formatted.includes('💼 **Export Options:**')) {
            // Find the start and end of the export section
            const startIndex = formatted.indexOf('💼 **Export Options:**');
            const endIndex = formatted.indexOf('💡 **Need help with business planning');

            if (startIndex !== -1 && endIndex !== -1) {
                const beforeExport = formatted.substring(0, startIndex);
                const afterExport = formatted.substring(endIndex);
                formatted = beforeExport + exportButtonsHtml + afterExport;
            }
        }

        // If still no replacement happened, try to replace the export text directly
        if (!replaced && !formatted.includes('Export Business Plan')) {
            console.log('Trying direct text replacement...');
            // Try to find and replace the export text section
            const exportTextPattern = /💼 \*\*Export Options:\*\* PDF, Word, Excel, PowerPoint formats available.*?💡 \*\*Need help with business planning, funding, or legal requirements\? I can provide detailed guidance for each step!\*\*/gs;

            if (exportTextPattern.test(formatted)) {
                console.log('Direct text pattern matched!');
                formatted = formatted.replace(exportTextPattern, exportButtonsHtml);
            } else {
                console.log('No pattern matched, appending buttons at the end');
                // If no pattern matches, just append the buttons at the end
                formatted += exportButtonsHtml;
            }
        }

        console.log('Final formatted content:', formatted);
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
                    <button onclick="saveBusinessPlanFromChat('${businessType}')" style="background: #9b59b6; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">💾 Save</button>
                </div>
                <p style="margin: 10px 0 0 0; font-size: 12px; color: rgba(255,255,255,0.8);">Click any button to generate your ${businessType} business plan or save to My Data</p>
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

    async getEnhancedFallbackResponse(message) {
        // Try to get response from enhanced system using direct Python call
        try {
            const response = await fetch('api/enhanced_chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message: message,
                    history: this.chatHistory
                })
            });

            if (response.ok) {
                const data = await response.json();
                if (data && data.response) {
                    return data.response;
                }
            }
        } catch (error) {
            console.error('Enhanced fallback error:', error);
        }

        throw new Error('Enhanced fallback failed');
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
            const businessType = result.getAttribute('data-business-type');

            // Enhanced search logic
            let matches = false;

            // Search in title, description, and business type
            if (text.includes(lowerQuery)) {
                matches = true;
            }

            // Search for specific business types
            if (businessType && businessType.toLowerCase().includes(lowerQuery)) {
                matches = true;
            }

            // Search for keywords
            const keywords = ['hiking', 'volcano', 'restaurant', 'eco', 'food', 'coffee', 'transport', 'souvenir', 'guide', 'farming', 'guesthouse', 'internet', 'cafe'];
            if (keywords.some(keyword => keyword.includes(lowerQuery) && text.includes(keyword))) {
                matches = true;
            }

            if (matches) {
                result.style.display = 'block';
                result.style.animation = 'fadeIn 0.3s ease-in';
            } else {
                result.style.display = 'none';
            }
        });

        // Update search results count
        this.updateSearchResultsCount();
    }

    filterSearchResults(category) {
        const results = document.querySelectorAll('.search-result-item');

        results.forEach(result => {
            const resultCategory = result.getAttribute('data-category');

            if (category === 'all') {
                result.style.display = 'block';
            } else if (resultCategory === category) {
                result.style.display = 'block';
            } else {
                result.style.display = 'none';
            }
        });

        // Update search results count
        this.updateSearchResultsCount();
    }

    updateSearchResultsCount() {
        const visibleResults = document.querySelectorAll('.search-result-item[style*="block"], .search-result-item:not([style*="none"])');
        const count = visibleResults.length;

        // Add or update results count display
        let countElement = document.getElementById('search-results-count');
        if (!countElement) {
            countElement = document.createElement('div');
            countElement.id = 'search-results-count';
            countElement.className = 'search-results-count';
            countElement.style.cssText = 'margin: 10px 0; padding: 8px 12px; background: #e3f2fd; border-radius: 4px; font-size: 14px; color: #1976d2;';

            const searchResults = document.getElementById('search-results');
            searchResults.parentNode.insertBefore(countElement, searchResults);
        }

        countElement.textContent = `Found ${count} result${count !== 1 ? 's' : ''}`;
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
        window.loadQuickTemplate = this.loadQuickTemplate.bind(this);
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

    // Load real analytics data from API
    async loadAnalyticsData() {
        try {
            const response = await fetch('api/analytics.php?action=all_analytics');
            const result = await response.json();

            if (result.success) {
                return result.data;
            } else {
                throw new Error(result.error || 'Failed to load analytics data');
            }
        } catch (error) {
            console.error('Error loading analytics data:', error);
            throw error;
        }
    }

    // Load specific analytics data
    async loadSpecificAnalytics(type) {
        try {
            const response = await fetch(`api/analytics.php?action=${type}`);
            const result = await response.json();

            if (result.success) {
                return result.data;
            } else {
                throw new Error(result.error || 'Failed to load analytics data');
            }
        } catch (error) {
            console.error(`Error loading ${type} analytics:`, error);
            throw error;
        }
    }

    async initCharts() {
        try {
            // Load real analytics data
            const analyticsData = await this.loadAnalyticsData();

            // Revenue Chart - Musanze Business Revenue Trends
            const revenueCtx = document.getElementById('revenueChart');
            if (revenueCtx && !revenueCtx.chart) {
                revenueCtx.chart = new Chart(revenueCtx.getContext('2d'), {
                    type: 'line',
                    data: analyticsData.revenue_trends,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            },
                            title: {
                                display: true,
                                text: 'Musanze Business Revenue Trends (Real Data)',
                                font: {
                                    size: 16,
                                    weight: 'bold'
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Revenue (M RWF)'
                                },
                                grid: {
                                    color: 'rgba(0,0,0,0.1)'
                                },
                                ticks: {
                                    callback: function (value) {
                                        return value + 'M';
                                    }
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Month'
                                },
                                grid: {
                                    color: 'rgba(0,0,0,0.1)'
                                }
                            }
                        }
                    }
                });
            }

            // User Growth Chart - New Business Registrations
            const userGrowthCtx = document.getElementById('userGrowthChart');
            if (userGrowthCtx && !userGrowthCtx.chart) {
                userGrowthCtx.chart = new Chart(userGrowthCtx.getContext('2d'), {
                    type: 'bar',
                    data: analyticsData.business_registrations,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            },
                            title: {
                                display: true,
                                text: 'New Business Registrations in Musanze (Real Data)',
                                font: {
                                    size: 16,
                                    weight: 'bold'
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Number of Businesses'
                                },
                                grid: {
                                    color: 'rgba(0,0,0,0.1)'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Month'
                                },
                                grid: {
                                    color: 'rgba(0,0,0,0.1)'
                                }
                            }
                        }
                    }
                });
            }

            // Add additional analytics cards with real data
            this.addAnalyticsCards(analyticsData.monthly_stats);

        } catch (error) {
            console.error('Error loading analytics data:', error);
            // Fallback to static data if API fails
            this.initChartsWithStaticData();
        }
    }

    // Fallback method with static data
    initChartsWithStaticData() {
        // Revenue Chart - Musanze Business Revenue Trends
        const revenueCtx = document.getElementById('revenueChart');
        if (revenueCtx && !revenueCtx.chart) {
            revenueCtx.chart = new Chart(revenueCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Business Revenue (M RWF)',
                        data: [12, 19, 15, 25, 22, 30],
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#007bff',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        title: {
                            display: true,
                            text: 'Musanze Business Revenue Trends',
                            font: {
                                size: 16,
                                weight: 'bold'
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Revenue (M RWF)'
                            },
                            grid: {
                                color: 'rgba(0,0,0,0.1)'
                            },
                            ticks: {
                                callback: function (value) {
                                    return value + 'M';
                                }
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Month'
                            },
                            grid: {
                                color: 'rgba(0,0,0,0.1)'
                            }
                        }
                    }
                }
            });
        }

        // User Growth Chart - New Business Registrations
        const userGrowthCtx = document.getElementById('userGrowthChart');
        if (userGrowthCtx && !userGrowthCtx.chart) {
            userGrowthCtx.chart = new Chart(userGrowthCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'New Business Registrations',
                        data: [8, 12, 15, 18, 22, 28],
                        backgroundColor: [
                            'rgba(40, 167, 69, 0.8)',
                            'rgba(40, 167, 69, 0.8)',
                            'rgba(40, 167, 69, 0.8)',
                            'rgba(40, 167, 69, 0.8)',
                            'rgba(40, 167, 69, 0.8)',
                            'rgba(40, 167, 69, 0.8)'
                        ],
                        borderColor: '#28a745',
                        borderWidth: 2,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        title: {
                            display: true,
                            text: 'New Business Registrations in Musanze',
                            font: {
                                size: 16,
                                weight: 'bold'
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Businesses'
                            },
                            grid: {
                                color: 'rgba(0,0,0,0.1)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Month'
                            },
                            grid: {
                                color: 'rgba(0,0,0,0.1)'
                            }
                        }
                    }
                }
            });
        }

        // Add additional analytics cards
        this.addAnalyticsCards();
    }

    // Add additional analytics cards
    addAnalyticsCards(realData = null) {
        const analyticsCard = document.querySelector('#analytics .dashboard-card .card-body');
        if (analyticsCard && !document.getElementById('analytics-cards')) {
            const analyticsCardsDiv = document.createElement('div');
            analyticsCardsDiv.id = 'analytics-cards';
            analyticsCardsDiv.className = 'row mt-4';

            // Use real data if available, otherwise use static data
            const totalBusinesses = realData?.total_businesses || 156;
            const activeUsers = realData?.active_users || 2847;
            const revenueGenerated = realData?.revenue_generated ? (realData.revenue_generated / 1000000).toFixed(1) : '32.4';
            const successRate = realData?.success_rate || 94;
            const newRegistrations = realData?.new_registrations || 23;
            const userGrowth = realData ? '+8% this month' : '+8% this month';
            const businessGrowth = realData ? `+${Math.round((newRegistrations / totalBusinesses) * 100)}% this month` : '+12% this month';

            analyticsCardsDiv.innerHTML = `
                <div class="col-md-3 mb-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h5 class="card-title">Total Businesses</h5>
                            <h2 class="mb-0">${totalBusinesses}</h2>
                            <small>${businessGrowth}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h5 class="card-title">Active Users</h5>
                            <h2 class="mb-0">${activeUsers.toLocaleString()}</h2>
                            <small>${userGrowth}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h5 class="card-title">Revenue Generated</h5>
                            <h2 class="mb-0">${revenueGenerated}M</h2>
                            <small>RWF this month</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h5 class="card-title">Success Rate</h5>
                            <h2 class="mb-0">${successRate}%</h2>
                            <small>Business success rate</small>
                        </div>
                    </div>
                </div>
            `;

            analyticsCard.appendChild(analyticsCardsDiv);
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

    // Export guide functionality
    async exportGuide(guideType, format) {
        try {
            this.showNotification('Generating guide...', 'info');

            const response = await fetch('api/export-business-plan.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    business_type: guideType,
                    format: format,
                    business_data: { type: 'guide' }
                })
            });

            const result = await response.json();

            if (result.success) {
                if (format === 'pdf' || format === 'word') {
                    const newWindow = window.open('', '_blank');
                    newWindow.document.write(result.html);
                    newWindow.document.close();
                    this.showNotification('Guide opened in new window! Use browser print function to save as ' + format.toUpperCase(), 'success');
                }
            } else {
                this.showNotification('Error generating guide: ' + result.error, 'error');
            }
        } catch (error) {
            console.error('Guide export error:', error);
            this.showNotification('Error generating guide. Please try again.', 'error');
        }
    }

    // Export toolkit functionality
    async exportToolkit(toolkitType, format) {
        try {
            this.showNotification('Generating toolkit...', 'info');

            const response = await fetch('api/export-business-plan.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    business_type: toolkitType,
                    format: format,
                    business_data: { type: 'toolkit' }
                })
            });

            const result = await response.json();

            if (result.success) {
                if (format === 'pdf') {
                    const newWindow = window.open('', '_blank');
                    newWindow.document.write(result.html);
                    newWindow.document.close();
                    this.showNotification('Toolkit opened in new window! Use browser print function to save as PDF', 'success');
                } else if (format === 'excel') {
                    const blob = new Blob([result.csv], { type: 'text/csv' });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = result.filename;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                    this.showNotification('Toolkit downloaded successfully!', 'success');
                }
            } else {
                this.showNotification('Error generating toolkit: ' + result.error, 'error');
            }
        } catch (error) {
            console.error('Toolkit export error:', error);
            this.showNotification('Error generating toolkit. Please try again.', 'error');
        }
    }

    // Export checklist functionality
    async exportChecklist(checklistType, format) {
        try {
            this.showNotification('Generating checklist...', 'info');

            const response = await fetch('api/export-business-plan.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    business_type: checklistType,
                    format: format,
                    business_data: { type: 'checklist' }
                })
            });

            const result = await response.json();

            if (result.success) {
                if (format === 'pdf' || format === 'word') {
                    const newWindow = window.open('', '_blank');
                    newWindow.document.write(result.html);
                    newWindow.document.close();
                    this.showNotification('Checklist opened in new window! Use browser print function to save as ' + format.toUpperCase(), 'success');
                }
            } else {
                this.showNotification('Error generating checklist: ' + result.error, 'error');
            }
        } catch (error) {
            console.error('Checklist export error:', error);
            this.showNotification('Error generating checklist. Please try again.', 'error');
        }
    }

    // Export framework functionality
    async exportFramework(frameworkType, format) {
        try {
            this.showNotification('Generating framework...', 'info');

            const response = await fetch('api/export-business-plan.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    business_type: frameworkType,
                    format: format,
                    business_data: { type: 'framework' }
                })
            });

            const result = await response.json();

            if (result.success) {
                if (format === 'pdf' || format === 'powerpoint') {
                    const newWindow = window.open('', '_blank');
                    newWindow.document.write(result.html);
                    newWindow.document.close();
                    this.showNotification('Framework opened in new window! Use browser print function to save as ' + format.toUpperCase(), 'success');
                }
            } else {
                this.showNotification('Error generating framework: ' + result.error, 'error');
            }
        } catch (error) {
            console.error('Framework export error:', error);
            this.showNotification('Error generating framework. Please try again.', 'error');
        }
    }

    // Load settings functionality
    loadSettings() {
        // Load user settings from localStorage or API
        const savedSettings = localStorage.getItem('innostart_settings');
        if (savedSettings) {
            const settings = JSON.parse(savedSettings);

            // Apply saved settings
            const nameInput = document.querySelector('#settings input[type="text"]');
            const emailInput = document.querySelector('#settings input[type="email"]');
            const notificationsCheckbox = document.getElementById('notifications');
            const darkModeCheckbox = document.getElementById('darkMode');

            if (nameInput && settings.name) nameInput.value = settings.name;
            if (emailInput && settings.email) emailInput.value = settings.email;
            if (notificationsCheckbox) notificationsCheckbox.checked = settings.notifications !== false;
            if (darkModeCheckbox) darkModeCheckbox.checked = settings.darkMode === true;
        }

        // Setup settings save functionality
        const saveButton = document.querySelector('#settings .btn-primary');
        if (saveButton) {
            saveButton.addEventListener('click', () => this.saveSettings());
        }

        // Setup dark mode toggle
        const darkModeCheckbox = document.getElementById('darkMode');
        if (darkModeCheckbox) {
            darkModeCheckbox.addEventListener('change', (e) => this.toggleDarkMode(e.target.checked));
        }
    }

    // Save settings functionality
    saveSettings() {
        const settings = {
            name: document.querySelector('#settings input[type="text"]').value,
            email: document.querySelector('#settings input[type="email"]').value,
            notifications: document.getElementById('notifications').checked,
            darkMode: document.getElementById('darkMode').checked
        };

        // Save to localStorage
        localStorage.setItem('innostart_settings', JSON.stringify(settings));

        // Show success notification
        this.showNotification('Settings saved successfully!', 'success');

        // Apply dark mode if enabled
        this.toggleDarkMode(settings.darkMode);
    }

    // Toggle dark mode
    toggleDarkMode(enabled) {
        if (enabled) {
            document.body.classList.add('dark-mode');
        } else {
            document.body.classList.remove('dark-mode');
        }
    }

    // Load business plan templates
    loadBusinessPlanTemplates() {
        // Update business type options with Musanze-specific businesses
        const businessTypeSelect = document.getElementById('businessType');
        if (businessTypeSelect) {
            businessTypeSelect.innerHTML = `
                <option value="">Select Business Type</option>
                <option value="mountain-hiking">Mountain Hiking Tours</option>
                <option value="volcano-trekking">Volcano Trekking</option>
                <option value="local-restaurant">Local Restaurant</option>
                <option value="eco-lodges">Eco-lodges</option>
                <option value="food-processing">Food Processing</option>
                <option value="coffee-processing">Coffee Processing</option>
                <option value="local-transport">Local Transport</option>
                <option value="souvenir-shop">Souvenir Shop</option>
                <option value="local-guide">Local Guide Services</option>
                <option value="organic-farming">Organic Farming</option>
                <option value="guesthouse">Guesthouse</option>
                <option value="internet-cafe">Internet Cafe</option>
                <option value="retail">Retail/E-commerce</option>
                <option value="service">Service Business</option>
                <option value="manufacturing">Manufacturing</option>
                <option value="technology">Technology/Software</option>
                <option value="food">Food & Beverage</option>
                <option value="consulting">Consulting</option>
            `;
        }

        // Add quick template buttons
        this.addQuickTemplateButtons();
    }

    // Add quick template buttons
    addQuickTemplateButtons() {
        const businessPlanCard = document.querySelector('#business-plan .dashboard-card .card-body');
        if (businessPlanCard && !document.getElementById('quick-templates')) {
            const quickTemplatesDiv = document.createElement('div');
            quickTemplatesDiv.id = 'quick-templates';
            quickTemplatesDiv.className = 'mb-4';
            quickTemplatesDiv.innerHTML = `
                <h6 class="mb-3">🚀 Quick Templates</h6>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <button class="btn btn-outline-primary btn-sm w-100" onclick="loadQuickTemplate('mountain-hiking')">
                            🏔️ Mountain Hiking
                        </button>
                    </div>
                    <div class="col-md-4 mb-2">
                        <button class="btn btn-outline-primary btn-sm w-100" onclick="loadQuickTemplate('volcano-trekking')">
                            🌋 Volcano Trekking
                        </button>
                    </div>
                    <div class="col-md-4 mb-2">
                        <button class="btn btn-outline-primary btn-sm w-100" onclick="loadQuickTemplate('local-restaurant')">
                            🍽️ Local Restaurant
                        </button>
                    </div>
                    <div class="col-md-4 mb-2">
                        <button class="btn btn-outline-primary btn-sm w-100" onclick="loadQuickTemplate('eco-lodges')">
                            🌿 Eco-lodges
                        </button>
                    </div>
                    <div class="col-md-4 mb-2">
                        <button class="btn btn-outline-primary btn-sm w-100" onclick="loadQuickTemplate('coffee-processing')">
                            ☕ Coffee Processing
                        </button>
                    </div>
                    <div class="col-md-4 mb-2">
                        <button class="btn btn-outline-primary btn-sm w-100" onclick="loadQuickTemplate('organic-farming')">
                            🌱 Organic Farming
                        </button>
                    </div>
                </div>
            `;

            businessPlanCard.insertBefore(quickTemplatesDiv, businessPlanCard.firstChild);
        }
    }

    // Load quick template
    loadQuickTemplate(templateType) {
        const templates = {
            'mountain-hiking': {
                name: 'Mountain Hiking Tours',
                type: 'mountain-hiking',
                market: 'International tourists and adventure seekers visiting Musanze for volcano and mountain experiences',
                mission: 'To provide safe, educational, and memorable mountain hiking experiences while promoting sustainable tourism in Musanze',
                advantage: 'Experienced local guides, unique volcano access, cultural integration, safety protocols',
                funding: '4.3M - 16M RWF'
            },
            'volcano-trekking': {
                name: 'Volcano Trekking Services',
                type: 'volcano-trekking',
                market: 'Premium adventure tourists seeking unique volcano trekking experiences',
                mission: 'To offer world-class volcano trekking experiences with expert guides and safety equipment',
                advantage: 'Premium equipment, certified guides, exclusive access, safety record',
                funding: '5M - 18M RWF'
            },
            'local-restaurant': {
                name: 'Local Restaurant',
                type: 'local-restaurant',
                market: 'International tourists, local residents, and business travelers in Musanze',
                mission: 'To serve authentic Rwandan cuisine using fresh local ingredients',
                advantage: 'Authentic recipes, local sourcing, cultural experience, tourist-friendly',
                funding: '4.8M - 18M RWF'
            },
            'eco-lodges': {
                name: 'Eco-lodges',
                type: 'eco-lodges',
                market: 'Eco-conscious tourists and nature lovers',
                mission: 'To provide sustainable accommodation that respects the environment',
                advantage: 'Eco-friendly practices, unique location, sustainability focus',
                funding: '26M - 86M RWF'
            },
            'coffee-processing': {
                name: 'Coffee Processing',
                type: 'coffee-processing',
                market: 'Local cafes, hotels, tourists, and export markets',
                mission: 'To process and export premium Rwandan coffee from Musanze',
                advantage: 'Premium quality, direct farmer relationships, export experience',
                funding: '33M - 93M RWF'
            },
            'organic-farming': {
                name: 'Organic Farming',
                type: 'organic-farming',
                market: 'Health-conscious consumers, restaurants, hotels, and export markets',
                mission: 'To produce high-quality organic vegetables and fruits sustainably',
                advantage: 'Organic certification, sustainable practices, local market knowledge',
                funding: '10M - 38M RWF'
            }
        };

        const template = templates[templateType];
        if (template) {
            document.getElementById('businessName').value = template.name;
            document.getElementById('businessType').value = template.type;
            document.getElementById('targetMarket').value = template.market;
            document.getElementById('missionStatement').value = template.mission;
            document.getElementById('competitiveAdvantage').value = template.advantage;
            document.getElementById('fundingNeeds').value = template.funding;

            this.showNotification('Template loaded successfully!', 'success');
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

// Save business plan from chat
async function saveBusinessPlanFromChat(businessType) {
    try {
        // Check if user is authenticated
        const authResponse = await fetch('api/check-session.php');
        if (!authResponse.ok) {
            showNotification('Please log in to save business plans', 'warning');
            return;
        }

        const authResult = await authResponse.json();
        if (!authResult.success || !authResult.logged_in) {
            showNotification('Please log in to save business plans', 'warning');
            return;
        }

        // Extract detailed business information from the chat messages
        const chatMessages = document.querySelectorAll('.message.assistant .message-content');
        let detailedBusinessInfo = '';
        let extractedData = {
            businessName: businessType,
            businessType: businessType.toLowerCase().replace(/\s+/g, '_'),
            targetMarket: 'Local customers in Musanze',
            missionStatement: `To provide excellent ${businessType.toLowerCase()} services in Musanze, Rwanda`,
            competitiveAdvantage: 'Local expertise and quality service',
            fundingNeeds: 'To be determined based on business requirements',
            detailedInfo: '',
            financialProjections: '',
            businessStrategy: '',
            growthOpportunities: '',
            keyConsiderations: ''
        };

        // Look for the most recent detailed business information
        for (let i = chatMessages.length - 1; i >= 0; i--) {
            const messageContent = chatMessages[i].innerHTML;
            if (messageContent.includes('DETAILED BUSINESS INFORMATION:') ||
                messageContent.includes('Business Overview:') ||
                messageContent.includes('Financial Projections:') ||
                messageContent.includes('Business Strategy:')) {

                detailedBusinessInfo = messageContent;

                // Extract specific information from the detailed response
                const textContent = messageContent.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();

                // Extract startup cost
                const startupCostMatch = textContent.match(/Startup Cost[:\s]*([^,\n]+)/i);
                if (startupCostMatch) {
                    extractedData.fundingNeeds = startupCostMatch[1].trim();
                }

                // Extract monthly revenue
                const revenueMatch = textContent.match(/Monthly Revenue[:\s]*([^,\n]+)/i);
                if (revenueMatch) {
                    extractedData.financialProjections = `Monthly Revenue: ${revenueMatch[1].trim()}`;
                }

                // Extract target market
                const targetMarketMatch = textContent.match(/Target Market[:\s]*([^,\n]+)/i);
                if (targetMarketMatch) {
                    extractedData.targetMarket = targetMarketMatch[1].trim();
                }

                // Extract location
                const locationMatch = textContent.match(/Location[:\s]*([^,\n]+)/i);
                if (locationMatch) {
                    extractedData.targetMarket += ` in ${locationMatch[1].trim()}`;
                }

                // Extract skills required
                const skillsMatch = textContent.match(/Skills Required[:\s]*([^,\n]+)/i);
                if (skillsMatch) {
                    extractedData.competitiveAdvantage = `Skills required: ${skillsMatch[1].trim()}`;
                }

                // Store the full detailed information
                extractedData.detailedInfo = textContent;

                break;
            }
        }

        // If no detailed info found, try to get it from the AI
        if (!detailedBusinessInfo) {
            try {
                const response = await fetch('api/chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        message: `Give me detailed information about ${businessType} business including startup costs, revenue projections, target market, and business strategy`
                    })
                });

                if (response.ok) {
                    const result = await response.json();
                    if (result.response) {
                        extractedData.detailedInfo = result.response;

                        // Extract information from the AI response
                        const textContent = result.response.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();

                        // Extract startup cost
                        const startupCostMatch = textContent.match(/startup[:\s]*([^,\n]+)/i);
                        if (startupCostMatch) {
                            extractedData.fundingNeeds = startupCostMatch[1].trim();
                        }

                        // Extract revenue
                        const revenueMatch = textContent.match(/revenue[:\s]*([^,\n]+)/i);
                        if (revenueMatch) {
                            extractedData.financialProjections = `Revenue: ${revenueMatch[1].trim()}`;
                        }
                    }
                }
            } catch (error) {
                console.log('Could not fetch additional details from AI:', error);
            }
        }

        // Prompt for title and description
        const title = prompt('Enter a title for your business plan:', `${businessType} - Business Plan`);
        if (!title) return;

        const description = prompt('Enter a description (optional):', `Detailed business plan for ${businessType} generated from AI Chat`);

        // Save the business plan with comprehensive data
        const dataId = await userDataManager.saveBusinessPlan(extractedData, title, description);

        if (dataId) {
            showNotification('Business plan with detailed information saved successfully to My Data!', 'success');
        }
    } catch (error) {
        console.error('Save business plan from chat error:', error);
        showNotification('Failed to save business plan: ' + error.message, 'error');
    }
}

// Export function for business plan from chat
async function exportBusinessPlanFromChat(businessType, format) {
    try {
        if (window.dashboard) {
            window.dashboard.showNotification(`Generating ${format.toUpperCase()} business plan for ${businessType}...`, 'info');
        }

        // Call the export API with business type
        const response = await fetch('api/export-business-plan.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                business_type: businessType,
                format: format,
                business_data: {
                    businessName: businessType,
                    targetMarket: 'Local Market',
                    missionStatement: `Professional ${businessType} services in Musanze, Rwanda`,
                    competitiveAdvantage: 'Local expertise and quality service',
                    fundingNeeds: 'To be determined based on business requirements'
                }
            })
        });

        if (response.ok) {
            const result = await response.json();

            if (result.success) {
                let blob, filename, mimeType;

                if (format === 'excel') {
                    // Handle CSV data for Excel
                    blob = new Blob([result.csv], { type: 'text/csv;charset=utf-8;' });
                    filename = `${businessType}_Business_Plan.csv`;
                    mimeType = 'text/csv';
                } else {
                    // Handle HTML data for PDF, Word, PowerPoint
                    blob = new Blob([result.html], { type: 'text/html;charset=utf-8;' });
                    filename = `${businessType}_Business_Plan.html`;
                    mimeType = 'text/html';
                }

                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);

                if (window.dashboard) {
                    window.dashboard.showNotification(`✅ ${format.toUpperCase()} business plan for ${businessType} downloaded successfully!`, 'success');
                }
            } else {
                throw new Error(result.error || 'Failed to generate business plan');
            }
        } else {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

    } catch (error) {
        console.error('Export error:', error);
        if (window.dashboard) {
            window.dashboard.showNotification(`Error generating ${format.toUpperCase()} business plan. Please try again.`, 'error');
        }
    }
}

// Enhanced export function for business plan form
async function exportBusinessPlanForm(format) {
    try {
        // Get form data
        const formData = {
            businessName: document.getElementById('businessName').value,
            businessType: document.getElementById('businessType').value,
            targetMarket: document.getElementById('targetMarket').value,
            missionStatement: document.getElementById('missionStatement').value,
            competitiveAdvantage: document.getElementById('competitiveAdvantage').value,
            fundingNeeds: document.getElementById('fundingNeeds').value
        };

        // Validate form data
        if (!formData.businessName || !formData.businessType) {
            if (window.dashboard) {
                window.dashboard.showNotification('Please fill in the business name and type first!', 'warning');
            }
            return;
        }

        // Show loading state
        const exportButtons = document.querySelectorAll('.export-btn');
        exportButtons.forEach(btn => btn.disabled = true);

        if (window.dashboard) {
            window.dashboard.showNotification(`Generating ${format.toUpperCase()} business plan...`, 'info');
        }

        // Call the export API
        const response = await fetch('api/export-business-plan.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                ...formData,
                format: format
            })
        });

        if (response.ok) {
            const result = await response.json();

            if (result.success) {
                let blob, filename;

                if (format === 'excel') {
                    // Handle CSV data for Excel
                    blob = new Blob([result.csv], { type: 'text/csv;charset=utf-8;' });
                    filename = `${formData.businessName}_Business_Plan.csv`;
                } else {
                    // Handle HTML data for PDF, Word, PowerPoint
                    blob = new Blob([result.html], { type: 'text/html;charset=utf-8;' });
                    filename = `${formData.businessName}_Business_Plan.html`;
                }

                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);

                if (window.dashboard) {
                    window.dashboard.showNotification(`✅ ${format.toUpperCase()} business plan downloaded successfully!`, 'success');
                }
            } else {
                throw new Error(result.error || 'Failed to generate business plan');
            }
        } else {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

    } catch (error) {
        console.error('Export error:', error);
        if (window.dashboard) {
            window.dashboard.showNotification(`Error generating ${format.toUpperCase()} business plan. Please try again.`, 'error');
        }
    } finally {
        // Re-enable export buttons
        const exportButtons = document.querySelectorAll('.export-btn');
        exportButtons.forEach(btn => btn.disabled = false);
    }
}

// Close export modal
function closeExportModal() {
    const modal = document.getElementById('exportModal');
    if (modal) {
        modal.remove();
    }
}

// Export guide function
async function exportGuide(guideType, format) {
    if (window.dashboard) {
        await window.dashboard.exportGuide(guideType, format);
    }
}

// Export toolkit function
async function exportToolkit(toolkitType, format) {
    if (window.dashboard) {
        await window.dashboard.exportToolkit(toolkitType, format);
    }
}

// Export checklist function
async function exportChecklist(checklistType, format) {
    if (window.dashboard) {
        await window.dashboard.exportChecklist(checklistType, format);
    }
}

// Export framework function
async function exportFramework(frameworkType, format) {
    if (window.dashboard) {
        await window.dashboard.exportFramework(frameworkType, format);
    }
}

// Load quick template function
function loadQuickTemplate(templateType) {
    if (window.dashboard) {
        window.dashboard.loadQuickTemplate(templateType);
    }
}

function handleChatKeyPress(event) {
    if (event.key === 'Enter') {
        sendMessage();
    }
}

// Quick message function for chat buttons
function sendQuickMessage(message) {
    const chatInput = document.getElementById('chat-input');
    if (chatInput) {
        chatInput.value = message;
        sendMessage();
    }
}

function logout() {
    if (window.dashboard) {
        window.dashboard.logout();
    }
}

// Financial Projections Functions
let financialProjectionsData = null;

function generateFinancialProjections() {
    // Get form data
    const businessName = document.getElementById('fpBusinessName').value;
    const businessType = document.getElementById('fpBusinessType').value;
    const projectionPeriod = parseInt(document.getElementById('projectionPeriod').value);
    const initialInvestment = parseFloat(document.getElementById('initialInvestment').value) || 0;
    const monthlyRevenue = parseFloat(document.getElementById('monthlyRevenue').value) || 0;
    const monthlyExpenses = parseFloat(document.getElementById('monthlyExpenses').value) || 0;
    const growthRate = parseFloat(document.getElementById('growthRate').value) || 10;
    const taxRate = parseFloat(document.getElementById('taxRate').value) || 15;
    const inflationRate = parseFloat(document.getElementById('inflationRate').value) || 5;
    const discountRate = parseFloat(document.getElementById('discountRate').value) || 12;
    const breakEvenMonths = parseInt(document.getElementById('breakEvenMonths').value) || 12;

    // Validate inputs
    if (!businessName || !businessType) {
        alert('Please fill in business name and type');
        return;
    }

    if (monthlyRevenue <= 0 || monthlyExpenses <= 0) {
        alert('Please enter valid revenue and expense amounts');
        return;
    }

    // Show loading state
    showFinancialLoading();

    // Calculate projections
    setTimeout(() => {
        const projections = calculateFinancialProjections({
            businessName,
            businessType,
            projectionPeriod,
            initialInvestment,
            monthlyRevenue,
            monthlyExpenses,
            growthRate,
            taxRate,
            inflationRate,
            discountRate,
            breakEvenMonths
        });

        financialProjectionsData = projections;
        displayFinancialProjections(projections);
        hideFinancialLoading();
    }, 1000);
}

function calculateFinancialProjections(params) {
    const {
        businessName,
        businessType,
        projectionPeriod,
        initialInvestment,
        monthlyRevenue,
        monthlyExpenses,
        growthRate,
        taxRate,
        inflationRate,
        discountRate,
        breakEvenMonths
    } = params;

    const months = projectionPeriod * 12;
    const projections = [];
    let cumulativeProfit = -initialInvestment;
    let totalRevenue = 0;
    let totalExpenses = 0;

    for (let month = 1; month <= months; month++) {
        // Calculate growth-adjusted revenue and expenses
        const growthFactor = Math.pow(1 + growthRate / 100, (month - 1) / 12);
        const inflationFactor = Math.pow(1 + inflationRate / 100, (month - 1) / 12);

        const currentRevenue = monthlyRevenue * growthFactor * inflationFactor;
        const currentExpenses = monthlyExpenses * inflationFactor;

        // Calculate profit before tax
        const grossProfit = currentRevenue - currentExpenses;

        // Calculate tax
        const tax = Math.max(0, grossProfit * (taxRate / 100));

        // Calculate net profit
        const netProfit = grossProfit - tax;

        // Update cumulative values
        cumulativeProfit += netProfit;
        totalRevenue += currentRevenue;
        totalExpenses += currentExpenses;

        projections.push({
            month: month,
            monthName: getMonthName(month),
            revenue: currentRevenue,
            expenses: currentExpenses,
            grossProfit: grossProfit,
            tax: tax,
            netProfit: netProfit,
            cumulativeProfit: cumulativeProfit
        });
    }

    // Calculate summary metrics
    const totalNetProfit = totalRevenue - totalExpenses - (totalRevenue * taxRate / 100);
    const profitMargin = totalRevenue > 0 ? ((totalNetProfit / totalRevenue) * 100) : 0;

    return {
        businessName,
        businessType,
        summary: {
            totalRevenue,
            totalExpenses,
            totalNetProfit,
            profitMargin,
            initialInvestment,
            finalCumulativeProfit: cumulativeProfit
        },
        projections,
        parameters: params
    };
}

function getMonthName(monthNumber) {
    const monthNames = [
        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
    ];

    const year = Math.floor((monthNumber - 1) / 12) + 1;
    const month = ((monthNumber - 1) % 12) + 1;

    return `${monthNames[month - 1]} Y${year}`;
}

function displayFinancialProjections(data) {
    // Show results section first
    const resultsDiv = document.getElementById('projectionResults');
    resultsDiv.style.display = 'block';

    // Wait a moment for the DOM to update, then update summary cards
    setTimeout(() => {
        const totalRevenueEl = document.getElementById('totalRevenue');
        const totalExpensesEl = document.getElementById('totalExpenses');
        const netProfitEl = document.getElementById('netProfit');
        const profitMarginEl = document.getElementById('profitMargin');

        if (totalRevenueEl) totalRevenueEl.textContent = formatCurrency(data.summary.totalRevenue);
        if (totalExpensesEl) totalExpensesEl.textContent = formatCurrency(data.summary.totalExpenses);
        if (netProfitEl) netProfitEl.textContent = formatCurrency(data.summary.totalNetProfit);
        if (profitMarginEl) profitMarginEl.textContent = data.summary.profitMargin.toFixed(1) + '%';

        // Add color coding to profit values
        if (netProfitEl && profitMarginEl) {
            if (data.summary.totalNetProfit >= 0) {
                netProfitEl.className = 'metric-positive';
                profitMarginEl.className = 'metric-positive';
            } else {
                netProfitEl.className = 'metric-negative';
                profitMarginEl.className = 'metric-negative';
            }
        }

        // Populate projections table
        populateProjectionsTable(data.projections);

        // Create charts
        createFinancialCharts(data);

        // Scroll to results
        resultsDiv.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }, 100); // Small delay to ensure DOM is updated
}

function populateProjectionsTable(projections) {
    const tbody = document.getElementById('projectionsTableBody');
    tbody.innerHTML = '';

    projections.forEach(projection => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${projection.monthName}</td>
            <td>${formatCurrency(projection.revenue)}</td>
            <td>${formatCurrency(projection.expenses)}</td>
            <td class="${projection.grossProfit >= 0 ? 'metric-positive' : 'metric-negative'}">${formatCurrency(projection.grossProfit)}</td>
            <td>${formatCurrency(projection.tax)}</td>
            <td class="${projection.netProfit >= 0 ? 'metric-positive' : 'metric-negative'}">${formatCurrency(projection.netProfit)}</td>
            <td class="${projection.cumulativeProfit >= 0 ? 'metric-positive' : 'metric-negative'}">${formatCurrency(projection.cumulativeProfit)}</td>
        `;
        tbody.appendChild(row);
    });
}

function createFinancialCharts(data) {
    // Revenue vs Expenses Chart
    createRevenueExpensesChart(data.projections);

    // Profit Distribution Chart
    createProfitChart(data.summary);
}

function createRevenueExpensesChart(projections) {
    // Check if Chart.js is available
    if (typeof Chart === 'undefined') {
        console.error('Chart.js library is not loaded');
        return;
    }

    const canvas = document.getElementById('revenueExpensesChart');
    if (!canvas) {
        console.error('Revenue expenses chart canvas not found');
        return;
    }
    const ctx = canvas.getContext('2d');

    // Destroy existing chart if it exists and has destroy method
    if (window.revenueExpensesChart && typeof window.revenueExpensesChart.destroy === 'function') {
        window.revenueExpensesChart.destroy();
    }

    const labels = projections.map(p => p.monthName);
    const revenueData = projections.map(p => p.revenue);
    const expensesData = projections.map(p => p.expenses);
    const profitData = projections.map(p => p.netProfit);

    try {
        window.revenueExpensesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Revenue',
                        data: revenueData,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        borderWidth: 3,
                        fill: false,
                        tension: 0.4
                    },
                    {
                        label: 'Expenses',
                        data: expensesData,
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        borderWidth: 3,
                        fill: false,
                        tension: 0.4
                    },
                    {
                        label: 'Net Profit',
                        data: profitData,
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        borderWidth: 3,
                        fill: false,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Monthly Financial Performance'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return formatCurrency(value);
                            }
                        }
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error creating revenue expenses chart:', error);
    }
}

function createProfitChart(summary) {
    // Check if Chart.js is available
    if (typeof Chart === 'undefined') {
        console.error('Chart.js library is not loaded');
        return;
    }

    const canvas = document.getElementById('profitChart');
    if (!canvas) {
        console.error('Profit chart canvas not found');
        return;
    }
    const ctx = canvas.getContext('2d');

    // Destroy existing chart if it exists and has destroy method
    if (window.profitChart && typeof window.profitChart.destroy === 'function') {
        window.profitChart.destroy();
    }

    const profitData = [
        summary.totalNetProfit,
        summary.totalExpenses
    ];

    try {
        window.profitChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Net Profit', 'Total Expenses'],
                datasets: [{
                    data: profitData,
                    backgroundColor: [
                        '#28a745',
                        '#dc3545'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    title: {
                        display: true,
                        text: 'Profit vs Expenses Distribution'
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error creating profit chart:', error);
    }
}

function formatCurrency(amount) {
    if (amount >= 1000000) {
        return (amount / 1000000).toFixed(1) + 'M RWF';
    } else if (amount >= 1000) {
        return (amount / 1000).toFixed(1) + 'K RWF';
    } else {
        return Math.round(amount).toLocaleString() + ' RWF';
    }
}

function showFinancialLoading() {
    const resultsDiv = document.getElementById('projectionResults');

    // Show loading overlay instead of replacing content
    const loadingOverlay = document.createElement('div');
    loadingOverlay.id = 'financialLoadingOverlay';
    loadingOverlay.style.cssText = `
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    `;
    loadingOverlay.innerHTML = `
        <div class="financial-loading">
            <div class="spinner"></div>
            <p style="margin-top: 15px; font-weight: 600;">Generating financial projections...</p>
        </div>
    `;

    resultsDiv.style.position = 'relative';
    resultsDiv.style.display = 'block';
    resultsDiv.appendChild(loadingOverlay);
}

function hideFinancialLoading() {
    const loadingOverlay = document.getElementById('financialLoadingOverlay');
    if (loadingOverlay) {
        loadingOverlay.remove();
    }
}

function exportFinancialProjections(format) {
    if (!financialProjectionsData) {
        alert('Please generate financial projections first');
        return;
    }

    const button = event.target.closest('button');
    const originalText = button.innerHTML;

    // Show loading state
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Exporting...';
    button.disabled = true;

    setTimeout(() => {
        try {
            switch (format) {
                case 'pdf':
                    exportToPDF(financialProjectionsData);
                    break;
                case 'excel':
                    exportToExcel(financialProjectionsData);
                    break;
                case 'csv':
                    exportToCSV(financialProjectionsData);
                    break;
                case 'json':
                    exportToJSON(financialProjectionsData);
                    break;
                default:
                    throw new Error('Unsupported format');
            }

            // Show success message
            showNotification('Financial projections exported successfully!', 'success');
        } catch (error) {
            console.error('Export error:', error);
            showNotification('Export failed. Please try again.', 'error');
        } finally {
            // Restore button state
            button.innerHTML = originalText;
            button.disabled = false;
        }
    }, 1500);
}

function exportToPDF(data) {
    // Create a simple PDF-like export using window.print
    const printWindow = window.open('', '_blank');
    const htmlContent = generatePDFContent(data);

    printWindow.document.write(htmlContent);
    printWindow.document.close();
    printWindow.print();
}

function exportToExcel(data) {
    // Create CSV content that can be opened in Excel
    const csvContent = generateCSVContent(data);
    downloadFile(csvContent, `${data.businessName}_financial_projections.csv`, 'text/csv');
}

function exportToCSV(data) {
    const csvContent = generateCSVContent(data);
    downloadFile(csvContent, `${data.businessName}_financial_projections.csv`, 'text/csv');
}

function exportToJSON(data) {
    const jsonContent = JSON.stringify(data, null, 2);
    downloadFile(jsonContent, `${data.businessName}_financial_projections.json`, 'application/json');
}

function generatePDFContent(data) {
    return `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Financial Projections - ${data.businessName}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .summary { margin-bottom: 30px; }
                .summary-item { margin: 10px 0; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .positive { color: green; }
                .negative { color: red; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Financial Projections</h1>
                <h2>${data.businessName}</h2>
                <p>Business Type: ${data.businessType}</p>
                <p>Generated on: ${new Date().toLocaleDateString()}</p>
            </div>
            
            <div class="summary">
                <h3>Financial Summary</h3>
                <div class="summary-item">Total Revenue: ${formatCurrency(data.summary.totalRevenue)}</div>
                <div class="summary-item">Total Expenses: ${formatCurrency(data.summary.totalExpenses)}</div>
                <div class="summary-item">Net Profit: <span class="${data.summary.totalNetProfit >= 0 ? 'positive' : 'negative'}">${formatCurrency(data.summary.totalNetProfit)}</span></div>
                <div class="summary-item">Profit Margin: ${data.summary.profitMargin.toFixed(1)}%</div>
            </div>
            
            <h3>Monthly Projections</h3>
            <table>
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Revenue</th>
                        <th>Expenses</th>
                        <th>Gross Profit</th>
                        <th>Tax</th>
                        <th>Net Profit</th>
                        <th>Cumulative Profit</th>
                    </tr>
                </thead>
                <tbody>
                    ${data.projections.map(p => `
                        <tr>
                            <td>${p.monthName}</td>
                            <td>${formatCurrency(p.revenue)}</td>
                            <td>${formatCurrency(p.expenses)}</td>
                            <td class="${p.grossProfit >= 0 ? 'positive' : 'negative'}">${formatCurrency(p.grossProfit)}</td>
                            <td>${formatCurrency(p.tax)}</td>
                            <td class="${p.netProfit >= 0 ? 'positive' : 'negative'}">${formatCurrency(p.netProfit)}</td>
                            <td class="${p.cumulativeProfit >= 0 ? 'positive' : 'negative'}">${formatCurrency(p.cumulativeProfit)}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </body>
        </html>
    `;
}

function generateCSVContent(data) {
    const headers = ['Month', 'Revenue', 'Expenses', 'Gross Profit', 'Tax', 'Net Profit', 'Cumulative Profit'];
    const rows = data.projections.map(p => [
        p.monthName,
        p.revenue,
        p.expenses,
        p.grossProfit,
        p.tax,
        p.netProfit,
        p.cumulativeProfit
    ]);

    const csvContent = [headers, ...rows]
        .map(row => row.map(cell => `"${cell}"`).join(','))
        .join('\n');

    return csvContent;
}

function downloadFile(content, filename, mimeType) {
    const blob = new Blob([content], { type: mimeType });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(notification);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}

// User Data Management Functions
let userDataManager = {
    currentData: null,

    // Save financial projection data
    async saveFinancialProjection(data, title = null, description = null) {
        try {
            const saveData = {
                action: 'save_data',
                data_type: 'financial_projection',
                title: title || `${data.businessName} - Financial Projections`,
                description: description || `Financial projections for ${data.businessType} business`,
                data_content: data,
                tags: ['financial', 'projections', data.businessType],
                status: 'completed'
            };

            const response = await fetch('api/user-data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(saveData)
            });

            // Check if response is ok
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Non-JSON response:', text);
                throw new Error('Server returned non-JSON response');
            }

            const result = await response.json();

            if (result.success) {
                showNotification('Financial projection saved successfully!', 'success');
                return result.data_id;
            } else {
                throw new Error(result.error || 'Failed to save projection');
            }
        } catch (error) {
            console.error('Save error:', error);
            showNotification('Failed to save projection: ' + error.message, 'error');
            return null;
        }
    },

    // Save business plan data
    async saveBusinessPlan(data, title = null, description = null) {
        try {
            const saveData = {
                action: 'save_data',
                data_type: 'business_plan',
                title: title || `${data.businessName} - Business Plan`,
                description: description || `Business plan for ${data.businessType} business`,
                data_content: data,
                tags: ['business', 'plan', data.businessType],
                status: 'completed'
            };

            const response = await fetch('api/user-data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(saveData)
            });

            // Check if response is ok
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Non-JSON response:', text);
                throw new Error('Server returned non-JSON response');
            }

            const result = await response.json();

            if (result.success) {
                showNotification('Business plan saved successfully!', 'success');
                return result.data_id;
            } else {
                throw new Error(result.error || 'Failed to save business plan');
            }
        } catch (error) {
            console.error('Save business plan error:', error);
            showNotification('Failed to save business plan: ' + error.message, 'error');
            return null;
        }
    },

    // Load user data
    async loadUserData(dataType = 'financial_projection', limit = 50) {
        try {
            const response = await fetch(`api/user-data.php?action=get_user_data&data_type=${dataType}&limit=${limit}`);

            // Check if response is ok
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Non-JSON response:', text);
                throw new Error('Server returned non-JSON response');
            }

            const result = await response.json();

            if (result.success) {
                return result.data;
            } else {
                throw new Error(result.error || 'Failed to load data');
            }
        } catch (error) {
            console.error('Load error:', error);
            showNotification('Failed to load data: ' + error.message, 'error');
            return [];
        }
    },

    // Load specific data by ID
    async loadDataById(dataId) {
        try {
            console.log('Loading data by ID:', dataId);
            const response = await fetch(`api/user-data.php?action=get_user_data&data_id=${dataId}`);

            // Check if response is ok
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Non-JSON response:', text);
                throw new Error('Server returned non-JSON response');
            }

            const result = await response.json();
            console.log('API response:', result);

            if (result.success && result.data) {
                // When data_id is provided, API returns single object, not array
                console.log('Returning data:', result.data);
                return result.data;
            } else {
                console.log('No data found in response');
                throw new Error('Data not found');
            }
        } catch (error) {
            console.error('Load by ID error:', error);
            showNotification('Failed to load data: ' + error.message, 'error');
            return null;
        }
    },

    // Delete data
    async deleteData(dataId) {
        try {
            const response = await fetch('api/user-data.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'delete_data',
                    data_id: dataId
                })
            });

            // Check if response is ok
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Non-JSON response:', text);
                throw new Error('Server returned non-JSON response');
            }

            const result = await response.json();

            if (result.success) {
                showNotification('Data deleted successfully!', 'success');
                return true;
            } else {
                throw new Error(result.error || 'Failed to delete data');
            }
        } catch (error) {
            console.error('Delete error:', error);
            showNotification('Failed to delete data: ' + error.message, 'error');
            return false;
        }
    },

    // Search data
    async searchData(query, dataType = '') {
        try {
            let url = `api/user-data.php?action=search_data&query=${encodeURIComponent(query)}`;
            if (dataType) url += `&data_type=${dataType}`;

            const response = await fetch(url);

            // Check if response is ok
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Non-JSON response:', text);
                throw new Error('Server returned non-JSON response');
            }

            const result = await response.json();

            if (result.success) {
                return result.data;
            } else {
                throw new Error(result.error || 'Search failed');
            }
        } catch (error) {
            console.error('Search error:', error);
            showNotification('Search failed: ' + error.message, 'error');
            return [];
        }
    },

    // Get data history
    async getDataHistory(dataId = null, dataType = '') {
        try {
            let url = 'api/user-data.php?action=get_data_history';
            if (dataId) url += `&data_id=${dataId}`;
            if (dataType) url += `&data_type=${dataType}`;

            const response = await fetch(url);

            // Check if response is ok
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Non-JSON response:', text);
                throw new Error('Server returned non-JSON response');
            }

            const result = await response.json();

            if (result.success) {
                return result.history;
            } else {
                throw new Error(result.error || 'Failed to load history');
            }
        } catch (error) {
            console.error('History error:', error);
            showNotification('Failed to load history: ' + error.message, 'error');
            return [];
        }
    }
};

// Global Functions for Business Plans
async function saveCurrentBusinessPlan() {
    try {
        // Check if user is authenticated
        const authResponse = await fetch('api/check-session.php');
        if (!authResponse.ok) {
            showNotification('Please log in to save business plans', 'warning');
            return;
        }

        const authResult = await authResponse.json();
        if (!authResult.success || !authResult.logged_in) {
            showNotification('Please log in to save business plans', 'warning');
            return;
        }

        // Get form data
        const businessPlanData = {
            businessName: document.getElementById('businessName').value,
            businessType: document.getElementById('businessType').value,
            targetMarket: document.getElementById('targetMarket').value,
            missionStatement: document.getElementById('missionStatement').value,
            competitiveAdvantage: document.getElementById('competitiveAdvantage').value,
            fundingNeeds: document.getElementById('fundingNeeds').value
        };

        // Validate required fields
        if (!businessPlanData.businessName || !businessPlanData.businessType) {
            showNotification('Please fill in business name and type', 'warning');
            return;
        }

        // Prompt for title and description
        const title = prompt('Enter a title for your business plan:', `${businessPlanData.businessName} - Business Plan`);
        if (!title) return;

        const description = prompt('Enter a description (optional):', `Business plan for ${businessPlanData.businessType} business`);

        // Save the business plan
        const dataId = await userDataManager.saveBusinessPlan(businessPlanData, title, description);

        if (dataId) {
            showNotification('Business plan saved successfully!', 'success');
        }
    } catch (error) {
        console.error('Save business plan error:', error);
        showNotification('Failed to save business plan: ' + error.message, 'error');
    }
}

async function loadSavedBusinessPlans() {
    try {
        // Check if user is authenticated
        const authResponse = await fetch('api/check-session.php');
        if (!authResponse.ok) {
            showNotification('Please log in to load saved business plans', 'warning');
            return;
        }

        const authResult = await authResponse.json();
        if (!authResult.success || !authResult.logged_in) {
            showNotification('Please log in to load saved business plans', 'warning');
            return;
        }

        // Load saved business plans
        const businessPlans = await userDataManager.loadUserData('business_plan', 50);

        if (businessPlans.length === 0) {
            showNotification('No saved business plans found', 'info');
            return;
        }

        // Show modal with saved business plans
        showSavedBusinessPlansModal(businessPlans);
    } catch (error) {
        console.error('Load business plans error:', error);
        showNotification('Failed to load business plans: ' + error.message, 'error');
    }
}

function showSavedBusinessPlansModal(businessPlans) {
    const modalHtml = `
        <div class="modal fade" id="savedBusinessPlansModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-folder-open me-2"></i>Saved Business Plans</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="list-group">
                            ${businessPlans.map(plan => `
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">${plan.title}</h6>
                                        <small class="text-muted">${new Date(plan.updated_at).toLocaleDateString()}</small>
                                    </div>
                                    <p class="mb-1">${plan.description || 'No description'}</p>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="loadBusinessPlan(${plan.id})">
                                            <i class="fas fa-upload me-1"></i>Load
                                        </button>
                                        <button class="btn btn-outline-info" onclick="viewBusinessPlan(${plan.id})">
                                            <i class="fas fa-eye me-1"></i>View
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="deleteBusinessPlan(${plan.id})">
                                            <i class="fas fa-trash me-1"></i>Delete
                                        </button>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Remove existing modal if any
    const existingModal = document.getElementById('savedBusinessPlansModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('savedBusinessPlansModal'));
    modal.show();
}

async function loadBusinessPlan(dataId) {
    const data = await userDataManager.loadDataById(dataId);

    if (!data) return;

    const businessPlanData = data.data_content;

    // Populate the form with loaded data
    if (businessPlanData.businessName) {
        document.getElementById('businessName').value = businessPlanData.businessName;
    }
    if (businessPlanData.businessType) {
        document.getElementById('businessType').value = businessPlanData.businessType;
    }
    if (businessPlanData.targetMarket) {
        document.getElementById('targetMarket').value = businessPlanData.targetMarket;
    }
    if (businessPlanData.missionStatement) {
        document.getElementById('missionStatement').value = businessPlanData.missionStatement;
    }
    if (businessPlanData.competitiveAdvantage) {
        document.getElementById('competitiveAdvantage').value = businessPlanData.competitiveAdvantage;
    }
    if (businessPlanData.fundingNeeds) {
        document.getElementById('fundingNeeds').value = businessPlanData.fundingNeeds;
    }

    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('savedBusinessPlansModal'));
    if (modal) modal.hide();

    showNotification('Business plan loaded successfully!', 'success');
}

async function viewBusinessPlan(dataId) {
    const data = await userDataManager.loadDataById(dataId);

    if (!data) return;

    // Show data in a modal for viewing
    myDataManager.showDataModal(data, 'view');
}

async function deleteBusinessPlan(dataId) {
    if (!confirm('Are you sure you want to delete this business plan? This action cannot be undone.')) {
        return;
    }

    const success = await userDataManager.deleteData(dataId);
    if (success) {
        showNotification('Business plan deleted successfully!', 'success');
        // Refresh the modal
        await loadSavedBusinessPlans();
    }
}

// Save current financial projection
async function saveCurrentFinancialProjection() {
    try {
        // Check if user is authenticated
        const authResponse = await fetch('api/check-session.php');
        const authResult = await authResponse.json();

        if (!authResult.success || !authResult.logged_in) {
            showNotification('Please log in to save your data.', 'warning');
            return;
        }

        if (!financialProjectionsData) {
            showNotification('No projection data to save. Please generate projections first.', 'warning');
            return;
        }

        const title = prompt('Enter a title for this projection:',
            `${financialProjectionsData.businessName} - Financial Projections`);

        if (!title) return;

        const description = prompt('Enter a description (optional):',
            `Financial projections for ${financialProjectionsData.businessType} business`);

        const dataId = await userDataManager.saveFinancialProjection(
            financialProjectionsData,
            title,
            description
        );

        if (dataId) {
            // Update the current data with the saved ID
            financialProjectionsData.savedDataId = dataId;
        }
    } catch (error) {
        console.error('Error saving financial projection:', error);
        showNotification('Error saving projection: ' + error.message, 'error');
    }
}

// Load saved financial projections
async function loadSavedFinancialProjections() {
    try {
        // Check if user is authenticated
        const authResponse = await fetch('api/check-session.php');
        const authResult = await authResponse.json();

        if (!authResult.success || !authResult.logged_in) {
            showNotification('Please log in to load your saved data.', 'warning');
            return;
        }

        const savedData = await userDataManager.loadUserData('financial_projection');

        if (savedData.length === 0) {
            showNotification('No saved projections found.', 'info');
            return;
        }

        // Create a modal to display saved projections
        showSavedProjectionsModal(savedData);
    } catch (error) {
        console.error('Error loading saved projections:', error);
        showNotification('Error loading saved projections: ' + error.message, 'error');
    }
}

// Show saved projections modal
function showSavedProjectionsModal(projections) {
    const modalHtml = `
        <div class="modal fade" id="savedProjectionsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Saved Financial Projections</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="list-group">
                            ${projections.map(proj => `
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">${proj.title}</h6>
                                        <small>${new Date(proj.updated_at).toLocaleDateString()}</small>
                                    </div>
                                    <p class="mb-1">${proj.description}</p>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-primary" onclick="loadProjection(${proj.id})">
                                            <i class="fas fa-folder-open"></i> Load
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteProjection(${proj.id})">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Remove existing modal if any
    const existingModal = document.getElementById('savedProjectionsModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('savedProjectionsModal'));
    modal.show();
}

// Load a specific projection
async function loadProjection(dataId) {
    const data = await userDataManager.loadDataById(dataId);

    if (!data) return;

    const projectionData = data.data_content;

    // Populate the form with loaded data
    if (projectionData.businessName) {
        document.getElementById('fpBusinessName').value = projectionData.businessName;
    }
    if (projectionData.businessType) {
        document.getElementById('fpBusinessType').value = projectionData.businessType;
    }
    if (projectionData.parameters) {
        const params = projectionData.parameters;
        if (params.projectionPeriod) document.getElementById('projectionPeriod').value = params.projectionPeriod;
        if (params.initialInvestment) document.getElementById('initialInvestment').value = params.initialInvestment;
        if (params.monthlyRevenue) document.getElementById('monthlyRevenue').value = params.monthlyRevenue;
        if (params.monthlyExpenses) document.getElementById('monthlyExpenses').value = params.monthlyExpenses;
        if (params.growthRate) document.getElementById('growthRate').value = params.growthRate;
        if (params.taxRate) document.getElementById('taxRate').value = params.taxRate;
        if (params.inflationRate) document.getElementById('inflationRate').value = params.inflationRate;
        if (params.discountRate) document.getElementById('discountRate').value = params.discountRate;
        if (params.breakEvenMonths) document.getElementById('breakEvenMonths').value = params.breakEvenMonths;
    }

    // Set the current data and display results
    financialProjectionsData = projectionData;
    displayFinancialProjections(projectionData);

    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('savedProjectionsModal'));
    if (modal) modal.hide();

    showNotification('Projection loaded successfully!', 'success');
}

// Delete a projection
async function deleteProjection(dataId) {
    if (!confirm('Are you sure you want to delete this projection? This action cannot be undone.')) {
        return;
    }

    const success = await userDataManager.deleteData(dataId);

    if (success) {
        // Refresh the modal
        loadSavedFinancialProjections();
    }
}

// My Data Management Functions
let myDataManager = {
    currentData: [],
    currentHistory: [],

    // Initialize the My Data section
    async init() {
        await this.loadAllData();
        await this.loadDataHistory();
        this.setupEventListeners();
    },

    // Load all user data
    async loadAllData() {
        try {
            // Check if user is authenticated first
            const authResponse = await fetch('api/check-session.php');

            if (!authResponse.ok) {
                console.warn('Authentication check failed, showing empty data list');
                this.displayDataList([]);
                return;
            }

            const authResult = await authResponse.json();
            console.log('Auth result:', authResult);

            if (!authResult.success || !authResult.logged_in) {
                console.warn('User not authenticated, showing empty data list');
                this.displayDataList([]);
                return;
            }

            const data = await userDataManager.loadUserData('', 100);
            this.currentData = data;
            this.updateDataStatistics();
            this.displayDataList(data);
        } catch (error) {
            console.error('Failed to load data:', error);
            this.displayDataList([]);
        }
    },

    // Load data history
    async loadDataHistory() {
        try {
            // Check if user is authenticated first
            const authResponse = await fetch('api/check-session.php');

            if (!authResponse.ok) {
                console.warn('Authentication check failed, showing empty history');
                this.displayDataHistory([]);
                return;
            }

            const authResult = await authResponse.json();
            console.log('Auth result for history:', authResult);

            if (!authResult.success || !authResult.logged_in) {
                console.warn('User not authenticated, showing empty history');
                this.displayDataHistory([]);
                return;
            }

            const history = await userDataManager.getDataHistory();
            this.currentHistory = history;
            this.displayDataHistory(history);
        } catch (error) {
            console.error('Failed to load history:', error);
            this.displayDataHistory([]);
        }
    },

    // Update data statistics
    updateDataStatistics() {
        const total = this.currentData.length;
        const completed = this.currentData.filter(item => item.status === 'completed').length;
        const draft = this.currentData.filter(item => item.status === 'draft').length;
        const favorite = this.currentData.filter(item => item.is_favorite).length;

        document.getElementById('totalDataCount').textContent = total;
        document.getElementById('completedDataCount').textContent = completed;
        document.getElementById('draftDataCount').textContent = draft;
        document.getElementById('favoriteDataCount').textContent = favorite;
    },

    // Check authentication status
    checkAuthenticationStatus() {
        // Simple check - if we have user data, we're likely authenticated
        // This is a fallback method for display purposes
        return this.currentData !== null && this.currentData !== undefined;
    },

    // Display data list
    displayDataList(data) {
        const container = document.getElementById('myDataList');

        if (data.length === 0) {
            // Check if user is authenticated
            const isAuthenticated = this.checkAuthenticationStatus();

            if (!isAuthenticated) {
                container.innerHTML = `
                    <div class="text-center py-4">
                        <i class="fas fa-lock fa-3x text-warning mb-3"></i>
                        <h5 class="text-warning">Authentication Required</h5>
                        <p class="text-muted">Please log in to view and manage your saved data.</p>
                        <button class="btn btn-primary" onclick="window.location.href='login.html'">
                            <i class="fas fa-sign-in-alt me-2"></i>Go to Login
                        </button>
                    </div>
                `;
            } else {
                container.innerHTML = `
                    <div class="text-center py-4">
                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No data found</h5>
                        <p class="text-muted">Start by creating financial projections or other business data.</p>
                    </div>
                `;
            }
            return;
        }

        container.innerHTML = data.map(item => `
            <div class="data-item border rounded p-3 mb-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center mb-2">
                            <h6 class="mb-0 me-2">${item.title}</h6>
                            <span class="badge bg-${this.getStatusColor(item.status)}">${item.status}</span>
                            ${item.is_favorite ? '<i class="fas fa-heart text-danger ms-2"></i>' : ''}
                        </div>
                        <p class="text-muted mb-2">${item.description || 'No description'}</p>
                        <div class="d-flex align-items-center text-muted small">
                            <i class="fas fa-tag me-1"></i>
                            <span class="me-3">${this.getDataTypeLabel(item.data_type)}</span>
                            <i class="fas fa-calendar me-1"></i>
                            <span>${new Date(item.updated_at).toLocaleDateString()}</span>
                        </div>
                    </div>
                    <div class="btn-group-vertical">
                        <button class="btn btn-sm btn-outline-primary" onclick="myDataManager.viewData(${item.id})">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="myDataManager.editData(${item.id})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="myDataManager.deleteData(${item.id})">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    },

    // Display data history
    displayDataHistory(history) {
        const container = document.getElementById('dataHistoryList');

        if (history.length === 0) {
            container.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No activity yet</h5>
                    <p class="text-muted">Your data activity will appear here.</p>
                </div>
            `;
            return;
        }

        container.innerHTML = history.slice(0, 10).map(item => `
            <div class="activity-item d-flex align-items-center p-2 border-bottom">
                <div class="activity-icon me-3">
                    <i class="fas fa-${this.getActionIcon(item.action)} text-${this.getActionColor(item.action)}"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-bold">${this.getActionLabel(item.action)}</div>
                    <div class="text-muted small">${this.getDataTypeLabel(item.data_type)}</div>
                </div>
                <div class="text-muted small">
                    ${new Date(item.created_at).toLocaleString()}
                </div>
            </div>
        `).join('');
    },

    // Get status color
    getStatusColor(status) {
        const colors = {
            'draft': 'warning',
            'completed': 'success',
            'archived': 'secondary'
        };
        return colors[status] || 'secondary';
    },

    // Get data type label
    getDataTypeLabel(type) {
        const labels = {
            'financial_projection': 'Financial Projection',
            'business_plan': 'Business Plan',
            'market_analysis': 'Market Analysis',
            'competitor_analysis': 'Competitor Analysis',
            'marketing_strategy': 'Marketing Strategy',
            'custom': 'Custom Data'
        };
        return labels[type] || type;
    },

    // Get action icon
    getActionIcon(action) {
        const icons = {
            'created': 'plus',
            'updated': 'edit',
            'deleted': 'trash',
            'archived': 'archive',
            'restored': 'undo'
        };
        return icons[action] || 'circle';
    },

    // Get action color
    getActionColor(action) {
        const colors = {
            'created': 'success',
            'updated': 'primary',
            'deleted': 'danger',
            'archived': 'warning',
            'restored': 'info'
        };
        return colors[action] || 'secondary';
    },

    // Get action label
    getActionLabel(action) {
        const labels = {
            'created': 'Created',
            'updated': 'Updated',
            'deleted': 'Deleted',
            'archived': 'Archived',
            'restored': 'Restored'
        };
        return labels[action] || action;
    },

    // Setup event listeners
    setupEventListeners() {
        // Search functionality
        const searchInput = document.getElementById('dataSearchInput');
        if (searchInput) {
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.searchData();
                }
            });
        }

        // Filter functionality
        const typeFilter = document.getElementById('dataTypeFilter');
        const statusFilter = document.getElementById('dataStatusFilter');

        if (typeFilter) {
            typeFilter.addEventListener('change', () => this.filterData());
        }

        if (statusFilter) {
            statusFilter.addEventListener('change', () => this.filterData());
        }
    },

    // Search data
    async searchData() {
        const query = document.getElementById('dataSearchInput').value;
        if (!query.trim()) {
            this.displayDataList(this.currentData);
            return;
        }

        try {
            const results = await userDataManager.searchData(query);
            this.displayDataList(results);
        } catch (error) {
            console.error('Search failed:', error);
        }
    },

    // Filter data
    filterData() {
        const typeFilter = document.getElementById('dataTypeFilter').value;
        const statusFilter = document.getElementById('dataStatusFilter').value;

        let filteredData = this.currentData;

        if (typeFilter) {
            filteredData = filteredData.filter(item => item.data_type === typeFilter);
        }

        if (statusFilter) {
            filteredData = filteredData.filter(item => item.status === statusFilter);
        }

        this.displayDataList(filteredData);
    },

    // View data
    async viewData(dataId) {
        console.log('Viewing data with ID:', dataId);
        const data = await userDataManager.loadDataById(dataId);
        if (!data) return;

        console.log('Loaded data:', data);
        // Show data in a modal
        this.showDataModal(data, 'view');
    },

    // Edit data
    async editData(dataId) {
        const data = await userDataManager.loadDataById(dataId);
        if (!data) return;

        // Show data in a modal for editing
        this.showDataModal(data, 'edit');
    },

    // Delete data
    async deleteData(dataId) {
        if (!confirm('Are you sure you want to delete this data? This action cannot be undone.')) {
            return;
        }

        const success = await userDataManager.deleteData(dataId);
        if (success) {
            await this.loadAllData();
            await this.loadDataHistory();
        }
    },

    // Show data modal
    showDataModal(data, mode) {
        const modalHtml = `
            <div class="modal fade" id="dataModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${mode === 'view' ? 'View' : 'Edit'} Data</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" class="form-control" id="modalTitle" value="${data.title}" ${mode === 'view' ? 'readonly' : ''}>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" id="modalDescription" rows="3" ${mode === 'view' ? 'readonly' : ''}>${data.description || ''}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Data Type</label>
                                <input type="text" class="form-control" value="${this.getDataTypeLabel(data.data_type)}" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="modalStatus" ${mode === 'view' ? 'disabled' : ''}>
                                    <option value="draft" ${data.status === 'draft' ? 'selected' : ''}>Draft</option>
                                    <option value="completed" ${data.status === 'completed' ? 'selected' : ''}>Completed</option>
                                    <option value="archived" ${data.status === 'archived' ? 'selected' : ''}>Archived</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Data Content</label>
                                <div class="form-control" style="height: 200px; overflow-y: auto; padding: 15px;">
                                    ${this.formatDataContent(data.data_content, data.data_type)}
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            ${mode === 'edit' ? `
                                <button type="button" class="btn btn-primary" onclick="myDataManager.saveDataChanges(${data.id})">Save Changes</button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal if any
        const existingModal = document.getElementById('dataModal');
        if (existingModal) {
            existingModal.remove();
        }

        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('dataModal'));
        modal.show();
    },

    // Save data changes
    async saveDataChanges(dataId) {
        const title = document.getElementById('modalTitle').value;
        const description = document.getElementById('modalDescription').value;
        const status = document.getElementById('modalStatus').value;

        try {
            const response = await fetch('api/user-data.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'update_data',
                    data_id: dataId,
                    title: title,
                    description: description,
                    status: status
                })
            });

            // Check if response is ok
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Non-JSON response:', text);
                throw new Error('Server returned non-JSON response');
            }

            const result = await response.json();

            if (result.success) {
                showNotification('Data updated successfully!', 'success');

                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('dataModal'));
                if (modal) modal.hide();

                // Refresh data
                await this.loadAllData();
                await this.loadDataHistory();
            } else {
                throw new Error(result.error || 'Failed to update data');
            }
        } catch (error) {
            console.error('Update error:', error);
            showNotification('Failed to update data: ' + error.message, 'error');
        }
    },

    // Format data content based on data type
    formatDataContent(dataContent, dataType) {
        if (!dataContent) return '<p class="text-muted">No data content available</p>';

        switch (dataType) {
            case 'financial_projection':
                return this.formatFinancialProjectionData(dataContent);
            case 'business_plan':
                return this.formatBusinessPlanData(dataContent);
            case 'market_analysis':
                return this.formatMarketAnalysisData(dataContent);
            case 'competitor_analysis':
                return this.formatCompetitorAnalysisData(dataContent);
            case 'marketing_strategy':
                return this.formatMarketingStrategyData(dataContent);
            default:
                return this.formatGenericData(dataContent);
        }
    },

    // Format financial projection data
    formatFinancialProjectionData(data) {
        if (!data) return '<p class="text-muted">No financial data available</p>';

        let html = '<div class="financial-data-display">';

        // Business Information
        if (data.businessName || data.businessType) {
            html += '<h6 class="text-primary mb-3"><i class="fas fa-building me-2"></i>Business Information</h6>';
            html += '<div class="row mb-3">';
            if (data.businessName) {
                html += `<div class="col-md-6"><strong>Business Name:</strong> ${data.businessName}</div>`;
            }
            if (data.businessType) {
                html += `<div class="col-md-6"><strong>Business Type:</strong> ${data.businessType}</div>`;
            }
            html += '</div>';
        }

        // Financial Summary
        if (data.summary) {
            html += '<h6 class="text-success mb-3"><i class="fas fa-chart-line me-2"></i>Financial Summary</h6>';
            html += '<div class="table-responsive">';
            html += '<table class="table table-sm table-bordered">';
            html += '<tbody>';

            if (data.summary.totalRevenue !== undefined) {
                html += `<tr><td><strong>Total Revenue</strong></td><td class="text-end">${this.formatCurrency(data.summary.totalRevenue)}</td></tr>`;
            }
            if (data.summary.totalExpenses !== undefined) {
                html += `<tr><td><strong>Total Expenses</strong></td><td class="text-end">${this.formatCurrency(data.summary.totalExpenses)}</td></tr>`;
            }
            if (data.summary.totalNetProfit !== undefined) {
                html += `<tr><td><strong>Net Profit</strong></td><td class="text-end ${data.summary.totalNetProfit >= 0 ? 'text-success' : 'text-danger'}">${this.formatCurrency(data.summary.totalNetProfit)}</td></tr>`;
            }
            if (data.summary.profitMargin !== undefined) {
                html += `<tr><td><strong>Profit Margin</strong></td><td class="text-end">${data.summary.profitMargin.toFixed(2)}%</td></tr>`;
            }

            html += '</tbody>';
            html += '</table>';
            html += '</div>';
        }

        // Projection Period
        if (data.projectionPeriod) {
            html += '<div class="mb-3">';
            html += '<strong>Projection Period:</strong> ' + data.projectionPeriod + ' years';
            html += '</div>';
        }

        // Monthly Projections (first 6 months)
        if (data.projections && data.projections.length > 0) {
            html += '<h6 class="text-info mb-3"><i class="fas fa-calendar-alt me-2"></i>Monthly Projections (First 6 Months)</h6>';
            html += '<div class="table-responsive">';
            html += '<table class="table table-sm table-striped">';
            html += '<thead class="table-dark">';
            html += '<tr><th>Month</th><th class="text-end">Revenue</th><th class="text-end">Expenses</th><th class="text-end">Net Profit</th></tr>';
            html += '</thead>';
            html += '<tbody>';

            data.projections.slice(0, 6).forEach(projection => {
                html += '<tr>';
                html += `<td>${projection.month}</td>`;
                html += `<td class="text-end">${this.formatCurrency(projection.revenue)}</td>`;
                html += `<td class="text-end">${this.formatCurrency(projection.expenses)}</td>`;
                html += `<td class="text-end ${projection.netProfit >= 0 ? 'text-success' : 'text-danger'}">${this.formatCurrency(projection.netProfit)}</td>`;
                html += '</tr>';
            });

            html += '</tbody>';
            html += '</table>';
            html += '</div>';
        }

        html += '</div>';
        return html;
    },

    // Format business plan data
    formatBusinessPlanData(data) {
        let html = '<div class="business-plan-data">';
        html += '<h6 class="text-primary mb-3"><i class="fas fa-file-alt me-2"></i>Business Plan Summary</h6>';

        if (data.businessName) {
            html += `<p><strong>Business Name:</strong> ${data.businessName}</p>`;
        }
        if (data.businessType) {
            html += `<p><strong>Business Type:</strong> ${data.businessType}</p>`;
        }
        if (data.targetMarket) {
            html += `<p><strong>Target Market:</strong> ${data.targetMarket}</p>`;
        }
        if (data.missionStatement) {
            html += `<p><strong>Mission Statement:</strong> ${data.missionStatement}</p>`;
        }
        if (data.competitiveAdvantage) {
            html += `<p><strong>Competitive Advantage:</strong> ${data.competitiveAdvantage}</p>`;
        }
        if (data.fundingNeeds) {
            html += `<p><strong>Funding Needs:</strong> ${data.fundingNeeds}</p>`;
        }
        if (data.financialProjections) {
            html += `<p><strong>Financial Projections:</strong> ${data.financialProjections}</p>`;
        }

        // Show detailed information if available
        if (data.detailedInfo) {
            html += '<h6 class="text-info mb-3 mt-4"><i class="fas fa-info-circle me-2"></i>Detailed Information</h6>';
            html += `<div class="detailed-info" style="max-height: 200px; overflow-y: auto; background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #17a2b8;">`;
            html += `<pre style="white-space: pre-wrap; font-size: 12px; margin: 0;">${data.detailedInfo}</pre>`;
            html += '</div>';
        }

        html += '</div>';
        return html;
    },

    // Format market analysis data
    formatMarketAnalysisData(data) {
        let html = '<div class="market-analysis-data">';
        html += '<h6 class="text-info mb-3"><i class="fas fa-chart-bar me-2"></i>Market Analysis</h6>';

        if (data.marketSize) {
            html += `<p><strong>Market Size:</strong> ${data.marketSize}</p>`;
        }
        if (data.targetMarket) {
            html += `<p><strong>Target Market:</strong> ${data.targetMarket}</p>`;
        }

        html += '</div>';
        return html;
    },

    // Format competitor analysis data
    formatCompetitorAnalysisData(data) {
        let html = '<div class="competitor-analysis-data">';
        html += '<h6 class="text-warning mb-3"><i class="fas fa-users me-2"></i>Competitor Analysis</h6>';

        if (data.competitors && Array.isArray(data.competitors)) {
            html += '<ul>';
            data.competitors.forEach(competitor => {
                html += `<li>${competitor}</li>`;
            });
            html += '</ul>';
        }

        html += '</div>';
        return html;
    },

    // Format marketing strategy data
    formatMarketingStrategyData(data) {
        let html = '<div class="marketing-strategy-data">';
        html += '<h6 class="text-success mb-3"><i class="fas fa-bullhorn me-2"></i>Marketing Strategy</h6>';

        if (data.strategy) {
            html += `<p>${data.strategy}</p>`;
        }

        html += '</div>';
        return html;
    },

    // Format generic data
    formatGenericData(data) {
        return `<pre class="mb-0" style="font-size: 12px;">${JSON.stringify(data, null, 2)}</pre>`;
    },

    // Format currency helper function
    formatCurrency(amount) {
        if (typeof amount !== 'number') return 'N/A';
        return new Intl.NumberFormat('en-RW', {
            style: 'currency',
            currency: 'RWF',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    }
};

// Initialize My Data section when it's shown
function initMyDataSection() {
    if (myDataManager) {
        myDataManager.init();
    }
}

// Search My Data
function searchMyData() {
    if (myDataManager) {
        myDataManager.searchData();
    }
}

// Make functions globally accessible
window.saveCurrentBusinessPlan = saveCurrentBusinessPlan;
window.loadSavedBusinessPlans = loadSavedBusinessPlans;
window.loadBusinessPlan = loadBusinessPlan;
window.viewBusinessPlan = viewBusinessPlan;
window.deleteBusinessPlan = deleteBusinessPlan;
window.saveBusinessPlanFromChat = saveBusinessPlanFromChat;
window.saveCurrentFinancialProjection = saveCurrentFinancialProjection;
window.loadSavedFinancialProjections = loadSavedFinancialProjections;
window.searchMyData = searchMyData;
window.initMyDataSection = initMyDataSection;

// Initialize userDataManager if not already initialized
if (typeof userDataManager === 'undefined') {
    console.warn('userDataManager not found, initializing...');
    // The userDataManager is defined above, this is just a safety check
}

// Debug function to check if everything is loaded
window.debugUserDataSystem = function () {
    console.log('=== User Data System Debug ===');
    console.log('saveCurrentBusinessPlan:', typeof saveCurrentBusinessPlan);
    console.log('loadSavedBusinessPlans:', typeof loadSavedBusinessPlans);
    console.log('saveBusinessPlanFromChat:', typeof saveBusinessPlanFromChat);
    console.log('saveCurrentFinancialProjection:', typeof saveCurrentFinancialProjection);
    console.log('loadSavedFinancialProjections:', typeof loadSavedFinancialProjections);
    console.log('userDataManager:', typeof userDataManager);
    console.log('myDataManager:', typeof myDataManager);
    console.log('showNotification:', typeof showNotification);
    console.log('==============================');
};

// Auto-debug on load
document.addEventListener('DOMContentLoaded', function () {
    setTimeout(() => {
        window.debugUserDataSystem();
    }, 2000);
});
