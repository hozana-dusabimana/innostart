-- InnoStart Database Structure
-- Created for comprehensive startup assistant platform

-- Create database
CREATE DATABASE IF NOT EXISTS innostart_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE innostart_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    country VARCHAR(100) DEFAULT 'Rwanda',
    city VARCHAR(100) DEFAULT 'Musanze',
    profile_image VARCHAR(255),
    bio TEXT,
    user_type ENUM('entrepreneur', 'investor', 'mentor', 'admin') DEFAULT 'entrepreneur',
    status ENUM('active', 'inactive', 'suspended', 'pending') DEFAULT 'active',
    email_verified BOOLEAN DEFAULT FALSE,
    email_verification_token VARCHAR(255),
    password_reset_token VARCHAR(255),
    password_reset_expires DATETIME,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_user_type (user_type)
);

-- Business plans table
CREATE TABLE IF NOT EXISTS business_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    business_type VARCHAR(100) NOT NULL,
    industry VARCHAR(100),
    location VARCHAR(100) DEFAULT 'Musanze',
    description TEXT,
    executive_summary TEXT,
    market_analysis TEXT,
    competitive_analysis TEXT,
    marketing_strategy TEXT,
    operations_plan TEXT,
    financial_projections JSON,
    funding_requirements DECIMAL(15,2),
    currency VARCHAR(3) DEFAULT 'RWF',
    status ENUM('draft', 'in_progress', 'completed', 'archived') DEFAULT 'draft',
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_business_type (business_type),
    INDEX idx_status (status),
    INDEX idx_location (location)
);

-- Chat conversations table
CREATE TABLE IF NOT EXISTS chat_conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_id VARCHAR(255) NOT NULL,
    title VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_session_id (session_id)
);

-- Chat messages table
CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    user_id INT NOT NULL,
    message_type ENUM('user', 'assistant', 'system') NOT NULL,
    content TEXT NOT NULL,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES chat_conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_conversation_id (conversation_id),
    INDEX idx_user_id (user_id),
    INDEX idx_message_type (message_type),
    INDEX idx_created_at (created_at)
);

-- Business ideas table
CREATE TABLE IF NOT EXISTS business_ideas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(100) NOT NULL,
    industry VARCHAR(100),
    location VARCHAR(100) DEFAULT 'Musanze',
    budget_range VARCHAR(50),
    difficulty_level ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
    market_size VARCHAR(100),
    target_audience TEXT,
    competitive_advantage TEXT,
    revenue_model TEXT,
    startup_costs DECIMAL(15,2),
    monthly_revenue DECIMAL(15,2),
    currency VARCHAR(3) DEFAULT 'RWF',
    status ENUM('idea', 'planning', 'development', 'launched', 'paused') DEFAULT 'idea',
    tags JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_category (category),
    INDEX idx_industry (industry),
    INDEX idx_location (location),
    INDEX idx_status (status)
);

-- Analytics data table
CREATE TABLE IF NOT EXISTS analytics_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    metric_name VARCHAR(100) NOT NULL,
    metric_value DECIMAL(15,2) NOT NULL,
    metric_type ENUM('revenue', 'users', 'businesses', 'conversions', 'engagement') NOT NULL,
    period_type ENUM('daily', 'weekly', 'monthly', 'yearly') DEFAULT 'monthly',
    period_date DATE NOT NULL,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_metric_name (metric_name),
    INDEX idx_metric_type (metric_type),
    INDEX idx_period_date (period_date)
);

-- Resources table (for search functionality)
CREATE TABLE IF NOT EXISTS resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100) NOT NULL,
    resource_type ENUM('template', 'guide', 'tool', 'checklist', 'framework') NOT NULL,
    file_path VARCHAR(255),
    file_type VARCHAR(50),
    file_size INT,
    download_count INT DEFAULT 0,
    tags JSON,
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_category (category),
    INDEX idx_resource_type (resource_type),
    INDEX idx_is_active (is_active),
    INDEX idx_is_featured (is_featured)
);

-- User sessions table
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_session_token (session_token),
    INDEX idx_expires_at (expires_at)
);

-- User preferences table
CREATE TABLE IF NOT EXISTS user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    preference_key VARCHAR(100) NOT NULL,
    preference_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_preference (user_id, preference_key),
    INDEX idx_user_id (user_id)
);

-- Business plan templates table
CREATE TABLE IF NOT EXISTS business_plan_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    business_type VARCHAR(100) NOT NULL,
    industry VARCHAR(100),
    location VARCHAR(100) DEFAULT 'Musanze',
    template_data JSON NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_business_type (business_type),
    INDEX idx_industry (industry),
    INDEX idx_location (location),
    INDEX idx_is_active (is_active)
);

-- Insert default admin user
INSERT INTO users (email, password_hash, first_name, last_name, user_type, status, email_verified) 
VALUES ('admin@innostart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin', 'active', TRUE)
ON DUPLICATE KEY UPDATE email = email;

-- Insert default business plan templates
INSERT INTO business_plan_templates (name, business_type, industry, location, template_data, is_default, is_active) VALUES
('Mountain Hiking Tours', 'Tourism', 'Adventure Tourism', 'Musanze', '{"sections": {"executive_summary": "Mountain hiking tours in Musanze", "market_analysis": "Target international tourists", "financial_projections": {"startup_costs": "4300000-16000000", "monthly_revenue": "1500000-8000000"}}}', TRUE, TRUE),
('Volcano Trekking', 'Tourism', 'Adventure Tourism', 'Musanze', '{"sections": {"executive_summary": "Volcano trekking experiences", "market_analysis": "Premium adventure tourism", "financial_projections": {"startup_costs": "5000000-18000000", "monthly_revenue": "2000000-10000000"}}}', TRUE, TRUE),
('Local Restaurant', 'Food & Beverage', 'Restaurant', 'Musanze', '{"sections": {"executive_summary": "Traditional Rwandan cuisine restaurant", "market_analysis": "Local and tourist market", "financial_projections": {"startup_costs": "4800000-18000000", "monthly_revenue": "1500000-6000000"}}}', TRUE, TRUE),
('Eco-lodges', 'Hospitality', 'Eco-tourism', 'Musanze', '{"sections": {"executive_summary": "Sustainable accommodation", "market_analysis": "Eco-conscious tourists", "financial_projections": {"startup_costs": "26000000-86000000", "monthly_revenue": "3000000-12000000"}}}', TRUE, TRUE),
('Food Processing', 'Manufacturing', 'Food Processing', 'Musanze', '{"sections": {"executive_summary": "Local food processing business", "market_analysis": "Local and export markets", "financial_projections": {"startup_costs": "26000000-81000000", "monthly_revenue": "1400000-6300000"}}}', TRUE, TRUE),
('Coffee Processing', 'Manufacturing', 'Coffee Processing', 'Musanze', '{"sections": {"executive_summary": "Premium coffee processing", "market_analysis": "Local and international markets", "financial_projections": {"startup_costs": "33000000-93000000", "monthly_revenue": "2800000-13000000"}}}', TRUE, TRUE);

-- Insert default resources
INSERT INTO resources (title, description, category, resource_type, tags, is_featured, is_active) VALUES
('Market Research Guide', 'Comprehensive guide to conducting market research for startups', 'Research', 'guide', '["market research", "startup", "analysis"]', TRUE, TRUE),
('Legal Requirements Checklist', 'Essential legal requirements for starting a business in Rwanda', 'Legal', 'checklist', '["legal", "requirements", "Rwanda", "business"]', TRUE, TRUE),
('Marketing Strategy Framework', 'Step-by-step framework for developing marketing strategies', 'Marketing', 'framework', '["marketing", "strategy", "framework"]', TRUE, TRUE),
('Financial Projections Template', 'Excel template for creating financial projections', 'Finance', 'template', '["financial", "projections", "template", "excel"]', TRUE, TRUE),
('Business Plan Structure', 'Complete business plan structure and guidelines', 'Planning', 'template', '["business plan", "structure", "template"]', TRUE, TRUE),
('Funding Strategy Toolkit', 'Tools and strategies for securing business funding', 'Funding', 'toolkit', '["funding", "investment", "strategy"]', TRUE, TRUE);

-- Create indexes for better performance
CREATE INDEX idx_users_created_at ON users(created_at);
CREATE INDEX idx_business_plans_created_at ON business_plans(created_at);
CREATE INDEX idx_chat_messages_created_at ON chat_messages(created_at);
CREATE INDEX idx_business_ideas_created_at ON business_ideas(created_at);
CREATE INDEX idx_analytics_period ON analytics_data(period_date, metric_type);
