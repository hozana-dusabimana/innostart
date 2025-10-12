// InnoStart Main JavaScript File

// Global variables
let chatHistory = [];
let currentProjections = null;

// Scroll to specific section - Define early to avoid reference errors
function scrollToSection(sectionId) {
    console.log('scrollToSection called with:', sectionId);

    // Try multiple ways to find the section
    let section = document.getElementById(sectionId);

    if (!section) {
        // Try finding by class or other attributes
        section = document.querySelector(`[id="${sectionId}"]`);
    }

    if (!section) {
        // Try finding by data attribute
        section = document.querySelector(`[data-section="${sectionId}"]`);
    }

    console.log('Section found:', section);

    if (section) {
        try {
            section.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
            console.log('Successfully scrolling to section:', sectionId);
        } catch (error) {
            console.error('Error scrolling to section:', error);
            // Fallback to instant scroll
            section.scrollIntoView();
        }
    } else {
        console.error('Section not found:', sectionId);
        // Fallback: scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

// Initialize the application
document.addEventListener('DOMContentLoaded', function () {
    console.log('DOM loaded, initializing InnoStart...');
    initializeApp();
    setupEventListeners();
    setupSmoothScrolling();
    setupButtonHandlers();

    // Test scrollToSection function
    console.log('scrollToSection function available:', typeof scrollToSection === 'function');
});

// Setup button handlers with multiple fallback methods
function setupButtonHandlers() {
    console.log('Setting up button handlers...');

    // Get Started Free button - multiple selectors
    const getStartedSelectors = [
        'a[href="login.html"]',
        '.btn-primary',
        'a.btn-primary'
    ];

    let getStartedBtn = null;
    for (let selector of getStartedSelectors) {
        getStartedBtn = document.querySelector(selector);
        if (getStartedBtn) break;
    }

    if (getStartedBtn) {
        console.log('Get Started Free button found:', getStartedBtn);

        // Remove any existing event listeners
        getStartedBtn.onclick = null;

        // Add multiple event listeners
        getStartedBtn.addEventListener('click', function (e) {
            console.log('Get Started Free button clicked!');
            // Let the default link behavior work
        });

        // Also add onclick as backup
        getStartedBtn.onclick = function (e) {
            console.log('Get Started Free button clicked via onclick!');
            window.location.href = 'login.html';
        };

        console.log('Get Started Free button handlers added');
    } else {
        console.log('Get Started Free button not found');
    }

    // See How It Works button - multiple selectors
    const seeHowSelectors = [
        '#seeHowItWorksBtn',
        'button[onclick*="scrollToSection"]',
        '.btn-outline-light'
    ];

    let seeHowBtn = null;
    for (let selector of seeHowSelectors) {
        seeHowBtn = document.querySelector(selector);
        if (seeHowBtn && seeHowBtn.textContent.includes('See How It Works')) break;
    }

    if (seeHowBtn) {
        console.log('See How It Works button found:', seeHowBtn);

        // Remove any existing event listeners
        seeHowBtn.onclick = null;

        // Add multiple event listeners
        seeHowBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('See How It Works button clicked via addEventListener!');
            scrollToSection('features');
        });

        // Also add onclick as backup
        seeHowBtn.onclick = function (e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('See How It Works button clicked via onclick!');
            scrollToSection('features');
        };

        console.log('See How It Works button handlers added');
    } else {
        console.log('See How It Works button not found');
    }
}

// Initialize application
function initializeApp() {
    console.log('InnoStart initialized');

    // Add fade-in animation to sections
    const sections = document.querySelectorAll('section');
    sections.forEach(section => {
        section.classList.add('fade-in');
    });
}

// Setup event listeners
function setupEventListeners() {
    // Chat functionality
    const chatInput = document.getElementById('chatInput');
    const sendButton = document.getElementById('sendMessage');

    if (chatInput && sendButton) {
        sendButton.addEventListener('click', sendChatMessage);
        chatInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                sendChatMessage();
            }
        });
    }

    // Business ideas form
    const ideaForm = document.getElementById('ideaForm');
    if (ideaForm) {
        ideaForm.addEventListener('submit', generateBusinessIdeas);
    }

    // Financial projections form
    const financialForm = document.getElementById('financialForm');
    if (financialForm) {
        financialForm.addEventListener('submit', calculateProjections);
    }

    // Business plan form
    const businessPlanForm = document.getElementById('businessPlanForm');
    if (businessPlanForm) {
        businessPlanForm.addEventListener('submit', generateBusinessPlan);
    }
}

// Smooth scrolling for navigation
function setupSmoothScrolling() {
    const navLinks = document.querySelectorAll('a[href^="#"]');
    navLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            if (targetSection) {
                targetSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}


// Chat functionality
async function sendChatMessage() {
    const chatInput = document.getElementById('chatInput');
    const chatMessages = document.getElementById('chatMessages');
    const message = chatInput.value.trim();

    if (!message) return;

    // Add user message to chat
    addMessageToChat(message, 'user');
    chatInput.value = '';

    // Show loading indicator
    const loadingMessage = addMessageToChat('Thinking...', 'bot', true);

    try {
        // Send message to AI API
        const response = await fetch('api/chat.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                message: message,
                history: chatHistory
            })
        });

        const data = await response.json();

        // Remove loading message
        loadingMessage.remove();

        // Add AI response
        addMessageToChat(data.response, 'bot');

        // Update chat history
        chatHistory.push({ role: 'user', content: message });
        chatHistory.push({ role: 'assistant', content: data.response });

    } catch (error) {
        console.error('Chat error:', error);
        loadingMessage.remove();
        addMessageToChat('Sorry, I encountered an error. Please try again.', 'bot');
    }
}

// Add message to chat
function addMessageToChat(message, sender, isLoading = false) {
    const chatMessages = document.getElementById('chatMessages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${sender}-message`;

    const contentDiv = document.createElement('div');
    contentDiv.className = 'message-content';

    if (sender === 'bot' && !isLoading) {
        contentDiv.innerHTML = `<i class="fas fa-robot me-2"></i>${message}`;
    } else if (isLoading) {
        contentDiv.innerHTML = `<i class="fas fa-robot me-2"></i><span class="loading"></span> ${message}`;
    } else {
        contentDiv.textContent = message;
    }

    messageDiv.appendChild(contentDiv);
    chatMessages.appendChild(messageDiv);

    // Scroll to bottom
    chatMessages.scrollTop = chatMessages.scrollHeight;

    return messageDiv;
}

// Generate business ideas
async function generateBusinessIdeas(e) {
    e.preventDefault();

    const location = document.getElementById('location').value;
    const interests = document.getElementById('interests').value;
    const budget = document.getElementById('budget').value;

    const ideasList = document.getElementById('ideasList');
    ideasList.innerHTML = '<div class="text-center"><div class="loading"></div> <p class="mt-2">Generating ideas...</p></div>';

    try {
        const response = await fetch('api/ideas.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                location: location,
                interests: interests,
                budget: budget
            })
        });

        const data = await response.json();
        displayBusinessIdeas(data.ideas);

    } catch (error) {
        console.error('Ideas generation error:', error);
        ideasList.innerHTML = '<p class="text-danger text-center">Error generating ideas. Please try again.</p>';
    }
}

// Display business ideas
function displayBusinessIdeas(ideas) {
    const ideasList = document.getElementById('ideasList');

    if (!ideas || ideas.length === 0) {
        ideasList.innerHTML = '<p class="text-muted text-center">No ideas generated. Please try different parameters.</p>';
        return;
    }

    let html = '';
    ideas.forEach((idea, index) => {
        html += `
            <div class="idea-item slide-in-left" style="animation-delay: ${index * 0.1}s">
                <div class="idea-title">${idea.title}</div>
                <div class="idea-description">${idea.description}</div>
                <div class="idea-tags">
                    <span class="idea-tag">${idea.category}</span>
                    <span class="idea-tag">${idea.budget}</span>
                    <span class="idea-tag">${idea.difficulty}</span>
                </div>
            </div>
        `;
    });

    ideasList.innerHTML = html;
}

// Calculate financial projections
function calculateProjections(e) {
    e.preventDefault();

    const monthlyRevenue = parseFloat(document.getElementById('monthlyRevenue').value) || 0;
    const growthRate = parseFloat(document.getElementById('growthRate').value) || 0;
    const monthlyExpenses = parseFloat(document.getElementById('monthlyExpenses').value) || 0;
    const initialInvestment = parseFloat(document.getElementById('initialInvestment').value) || 0;
    const projectionMonths = parseInt(document.getElementById('projectionMonths').value) || 12;
    const breakEvenMonths = parseInt(document.getElementById('breakEvenMonths').value) || 6;

    // Calculate projections
    const projections = calculateFinancialProjections({
        monthlyRevenue,
        growthRate,
        monthlyExpenses,
        initialInvestment,
        projectionMonths,
        breakEvenMonths
    });

    currentProjections = projections;
    displayProjectionResults(projections);
}

// Calculate financial projections
function calculateFinancialProjections(params) {
    const { monthlyRevenue, growthRate, monthlyExpenses, initialInvestment, projectionMonths } = params;

    let totalRevenue = 0;
    let totalExpenses = 0;
    let currentRevenue = monthlyRevenue;
    const monthlyData = [];

    for (let month = 1; month <= projectionMonths; month++) {
        const monthlyRevenueAmount = currentRevenue;
        const monthlyExpenseAmount = monthlyExpenses;
        const monthlyProfit = monthlyRevenueAmount - monthlyExpenseAmount;

        totalRevenue += monthlyRevenueAmount;
        totalExpenses += monthlyExpenseAmount;

        monthlyData.push({
            month: month,
            revenue: monthlyRevenueAmount,
            expenses: monthlyExpenseAmount,
            profit: monthlyProfit
        });

        // Apply growth rate
        currentRevenue *= (1 + growthRate / 100);
    }

    const netProfit = totalRevenue - totalExpenses - initialInvestment;

    return {
        totalRevenue,
        totalExpenses,
        netProfit,
        initialInvestment,
        monthlyData,
        projectionMonths
    };
}

// Display projection results
function displayProjectionResults(projections) {
    const resultsDiv = document.getElementById('projectionResults');
    const totalRevenueEl = document.getElementById('totalRevenue');
    const totalExpensesEl = document.getElementById('totalExpenses');
    const netProfitEl = document.getElementById('netProfit');
    const chartDiv = document.getElementById('projectionChart');

    // Update summary values
    totalRevenueEl.textContent = formatCurrency(projections.totalRevenue);
    totalExpensesEl.textContent = formatCurrency(projections.totalExpenses);
    netProfitEl.textContent = formatCurrency(projections.netProfit);

    // Create chart
    createProjectionChart(projections.monthlyData);

    // Show results
    resultsDiv.style.display = 'block';
    resultsDiv.scrollIntoView({ behavior: 'smooth' });
}

// Create projection chart
function createProjectionChart(monthlyData) {
    const chartDiv = document.getElementById('projectionChart');
    chartDiv.innerHTML = '<canvas id="projectionChartCanvas" width="400" height="200"></canvas>';

    const ctx = document.getElementById('projectionChartCanvas').getContext('2d');

    const labels = monthlyData.map(data => `Month ${data.month}`);
    const revenueData = monthlyData.map(data => data.revenue);
    const expenseData = monthlyData.map(data => data.expenses);
    const profitData = monthlyData.map(data => data.profit);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Revenue',
                    data: revenueData,
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Expenses',
                    data: expenseData,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Profit',
                    data: profitData,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Financial Projections Over Time'
                },
                legend: {
                    display: true,
                    position: 'top'
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
}

// Function to add print button to HTML content
function addPrintButtonToHtml(htmlContent, businessName) {
    // Add print button and enhanced styling
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
        
        // Hide print controls when printing
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

    // Insert the print controls after the opening body tag
    return htmlContent.replace('<body>', '<body>' + printButtonHtml);
}

// Generate business plan
async function generateBusinessPlan(e) {
    e.preventDefault();

    const businessName = document.getElementById('businessName').value;
    const businessType = document.getElementById('businessType').value;
    const targetMarket = document.getElementById('targetMarket').value;
    const missionStatement = document.getElementById('missionStatement').value;
    const competitiveAdvantage = document.getElementById('competitiveAdvantage').value;
    const fundingNeeds = document.getElementById('fundingNeeds').value;

    // Show loading state
    const submitButton = e.target.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<span class="loading"></span> Opening Business Plan...';
    submitButton.disabled = true;

    try {
        console.log('Opening business plan in new window - NO DOWNLOAD');
        const response = await fetch('api/business-plan.php?' + Date.now(), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                businessName,
                businessType,
                targetMarket,
                missionStatement,
                competitiveAdvantage,
                fundingNeeds
            })
        });

        if (response.ok) {
            const htmlContent = await response.text();

            // Create a new window to display the business plan
            const newWindow = window.open('', '_blank', 'width=1000,height=700,scrollbars=yes,resizable=yes');

            if (newWindow) {
                // Add print button and styling to the HTML content
                const enhancedHtml = addPrintButtonToHtml(htmlContent, businessName);

                // Write the enhanced HTML to the new window
                newWindow.document.open();
                newWindow.document.write(enhancedHtml);
                newWindow.document.close();

                // Focus the new window
                newWindow.focus();

                showNotification('✅ Business plan opened in new window! Click "Print to PDF" button to create PDF. NO DOWNLOAD!', 'success');
            } else {
                throw new Error('Popup blocked. Please allow popups for this site.');
            }
        } else {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

    } catch (error) {
        console.error('Business plan generation error:', error);
        showNotification('Error generating business plan. Please try again.', 'error');
    } finally {
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    }
}

// Utility functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount);
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
            notification.remove();
        }
    }, 5000);
}

// Sample business ideas for demonstration
const sampleIdeas = [
    {
        title: "Local Food Delivery Service",
        description: "A hyperlocal food delivery service focusing on home-cooked meals and local restaurants in your area.",
        category: "Food & Beverage",
        budget: "Low",
        difficulty: "Easy"
    },
    {
        title: "Digital Marketing Agency",
        description: "Provide social media management, SEO, and digital advertising services to local businesses.",
        category: "Services",
        budget: "Medium",
        difficulty: "Medium"
    },
    {
        title: "Eco-Friendly Product Store",
        description: "Online store selling sustainable and eco-friendly products for environmentally conscious consumers.",
        category: "E-commerce",
        budget: "Medium",
        difficulty: "Medium"
    },
    {
        title: "Virtual Assistant Services",
        description: "Offer remote administrative, technical, or creative assistance to busy professionals and entrepreneurs.",
        category: "Services",
        budget: "Low",
        difficulty: "Easy"
    },
    {
        title: "Mobile App Development",
        description: "Create custom mobile applications for local businesses and startups.",
        category: "Technology",
        budget: "High",
        difficulty: "Hard"
    }
];

// Fallback for when API is not available
function generateSampleIdeas() {
    const shuffled = sampleIdeas.sort(() => 0.5 - Math.random());
    return shuffled.slice(0, 3);
}

