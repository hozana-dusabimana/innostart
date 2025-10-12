# InnoStart - AI-Powered Startup Assistant Platform

InnoStart is a comprehensive startup assistant platform that helps entrepreneurs in Musanze, Rwanda, and beyond to develop their business ideas, create business plans, and access AI-powered guidance for their entrepreneurial journey.

## 🚀 Features

- **AI-Powered Chat Assistant** - Get personalized business advice and guidance
- **Business Plan Generator** - Create comprehensive business plans with financial projections
- **Market Research Tools** - Access market data and analysis for Musanze region
- **Resource Library** - Templates, guides, and frameworks for entrepreneurs
- **Analytics Dashboard** - Track your business progress and metrics
- **User Management** - Secure registration and authentication system
- **Export Functionality** - Export business plans in multiple formats (PDF, Word, Excel, PowerPoint)

## 🛠️ Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript (ES6+), Bootstrap 5
- **Backend**: PHP 7.4+ with RESTful APIs
- **Database**: MySQL 5.7+ with comprehensive data structure
- **AI/ML**: Python 3.8+ with scikit-learn, OpenAI integration
- **Server**: XAMPP/WAMP compatible, Apache/Nginx ready

## 📋 Prerequisites

Before setting up InnoStart, ensure you have the following installed:

- **XAMPP** (recommended) or **WAMP** - Includes Apache, MySQL, and PHP
- **Python 3.8+** - For AI/ML functionality
- **Git** - For version control (optional)

## 🔧 Installation & Setup

### Step 1: Download and Install XAMPP

1. Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install XAMPP with default settings
3. Start Apache and MySQL services from XAMPP Control Panel

### Step 2: Clone/Download InnoStart

1. Download the InnoStart project files
2. Extract to `C:\xampp\htdocs\innostart\` (or your XAMPP htdocs directory)

### Step 3: Database Setup

#### Option A: Web-Based Setup (Recommended)

1. Open your web browser and navigate to:
   ```
   http://localhost/innostart/setup_database.php
   ```

2. Click **"Run Full Setup"** button
3. Wait for all steps to complete successfully
4. You should see "Database setup completed successfully!" message

#### Option B: Manual Database Setup

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create a new database named `innostart_db`
3. Import the SQL file: `database/innostart_database.sql`

### Step 4: Python Dependencies (for AI/ML features)

#### Option A: Automated Setup (Recommended)
```cmd
cd C:\xampp\htdocs\innostart
python setup_python.py
```

#### Option B: Manual Setup
1. Open Command Prompt as Administrator
2. Navigate to the project directory:
   ```cmd
   cd C:\xampp\htdocs\innostart
   ```

3. Install Python dependencies:
   ```cmd
   pip install -r requirements.txt
   ```

4. Start the Python ML API server:
   ```cmd
   python ml_models/musanze_api.py
   ```

### Step 5: Configuration

1. Edit `config/app_config.php` if needed:
   ```php
   // Database Configuration
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'innostart_db');
   define('DB_USER', 'root');
   define('DB_PASS', ''); // Your MySQL password
   ```

## 🎯 How to Run

### Starting the Application

1. **Start XAMPP Services**:
   - Open XAMPP Control Panel
   - Start **Apache** and **MySQL** services

2. **Start Python ML Server** (for AI features):
   ```cmd
   cd C:\xampp\htdocs\innostart
   python ml_models/musanze_api.py
   ```

3. **Access the Application**:
   - Open your web browser
   - Navigate to: `http://localhost/innostart/`

### Default Login Credentials

After database setup, you can use these credentials:

- **Email**: `admin@innostart.com`
- **Password**: `admin123`
- **Role**: Administrator

Or create a new account using the signup page.

## 📱 Usage Guide

### 1. Getting Started

1. **Visit the Homepage**: `http://localhost/innostart/`
2. **Sign Up**: Click "Get Started Free" to create a new account
3. **Login**: Use your credentials to access the dashboard

### 2. Dashboard Features

- **Recent Activity**: View your latest business activities
- **Quick Actions**: Access common features quickly
- **Analytics**: Monitor your business metrics
- **AI Chat**: Get personalized business advice

### 3. AI Chat Assistant

1. Click on the chat icon in the dashboard
2. Ask questions like:
   - "What business opportunities are available in Musanze?"
   - "Help me create a business plan for a restaurant"
   - "What's the budget range for starting a coffee shop?"

### 4. Business Plan Creation

1. Go to **Business Plans** section
2. Click **"Create New Plan"**
3. Choose from templates or start from scratch
4. Fill in your business details
5. Export in your preferred format

### 5. Search Resources

1. Use the **Search Resources** feature
2. Find templates, guides, and frameworks
3. Download resources for offline use

## 🔧 Troubleshooting

### Common Issues

#### Database Connection Error
```
Error: Database connection failed
```
**Solution**: 
- Ensure MySQL is running in XAMPP
- Check database credentials in `config/app_config.php`
- Verify database `innostart_db` exists

#### Python ML Server Not Working
```
Error: AI features not responding
```
**Solution**:
- Install Python dependencies: `pip install pandas scikit-learn openai requests`
- Start the ML server: `python ml_models/musanze_api.py`
- Check if port 5000 is available

#### Login Issues
```
Error: Invalid email or password
```
**Solution**:
- Use default credentials: `admin@innostart.com` / `admin123`
- Or create a new account via signup page
- Ensure database is properly set up

#### File Upload Issues
```
Error: File upload failed
```
**Solution**:
- Check PHP upload limits in `php.ini`
- Ensure `uploads/` directory has write permissions
- Verify file size is within limits

### Port Conflicts

If you encounter port conflicts:

1. **Apache Port 80**: Change to 8080 in XAMPP
2. **MySQL Port 3306**: Change to 3307 in XAMPP
3. **Python Port 5000**: Change in `ml_models/musanze_api.py`

## 📁 Project Structure

```
innostart/
├── api/                    # Backend API endpoints
│   ├── auth.php           # Authentication system
│   ├── chat.php           # AI chat functionality
│   ├── users.php          # User management
│   └── setup.php          # Database setup API
├── assets/                # Frontend assets
│   ├── css/               # Stylesheets
│   ├── js/                # JavaScript files
│   └── images/            # Images and icons
├── config/                # Configuration files
│   ├── database.php       # Database configuration
│   └── app_config.php     # Application settings
├── database/              # Database files
│   ├── innostart_database.sql
│   └── init_database.php
├── ml_models/             # AI/ML components
│   ├── musanze_api.py     # Python ML API
│   └── musanze_smart_model.py
├── datasets/              # ML training data
│   └── clean_musanze_dataset.csv
├── index.html             # Landing page
├── login.html             # Login page
├── signup.html            # Registration page
├── dashboard.html         # Main dashboard
└── setup_database.php     # Database setup interface
```

## 🔐 Security Features

- **Password Hashing**: Secure password storage using PHP's `password_hash()`
- **Session Management**: Secure session handling with expiration
- **Input Validation**: Server-side validation for all inputs
- **SQL Injection Prevention**: Prepared statements for all database queries
- **XSS Protection**: Output escaping and sanitization
- **CSRF Protection**: Token-based request validation

## 🌍 Localization

The platform is optimized for:
- **Primary Location**: Musanze, Rwanda
- **Currency**: Rwandan Franc (RWF)
- **Language**: English (with local business context)
- **Time Zone**: Africa/Kigali

## 📊 Database Schema

### Core Tables

- **users**: User accounts and profiles
- **business_plans**: User-generated business plans
- **business_ideas**: Business idea management
- **chat_conversations**: AI chat sessions
- **chat_messages**: Individual chat messages
- **analytics_data**: Platform analytics
- **resources**: Searchable business resources
- **user_sessions**: Session management
- **user_preferences**: User settings
- **business_plan_templates**: Pre-built templates

## 🚀 Deployment

### Production Deployment

1. **Web Server**: Use Apache or Nginx
2. **Database**: MySQL 5.7+ or MariaDB
3. **PHP**: Version 7.4 or higher
4. **SSL**: Enable HTTPS for security
5. **Environment**: Set `APP_ENV=production` in config

### Docker Deployment (Optional)

```dockerfile
# Dockerfile example
FROM php:7.4-apache
COPY . /var/www/html/
RUN docker-php-ext-install pdo pdo_mysql
EXPOSE 80
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🆘 Support

For support and questions:

1. **Documentation**: Check this README file
2. **Issues**: Report bugs via GitHub issues
3. **Email**: Contact the development team
4. **Community**: Join our developer community

## 🔄 Updates

### Version 1.0.0 (Current)
- ✅ Database integration
- ✅ User authentication system
- ✅ AI chat functionality
- ✅ Business plan generation
- ✅ Export functionality
- ✅ Analytics dashboard

### Upcoming Features
- 🔄 Email verification system
- 🔄 Password reset functionality
- 🔄 Advanced analytics
- 🔄 Mobile app
- 🔄 Multi-language support

---

**Happy Entrepreneuring with InnoStart! 🚀**

*Built with ❤️ for entrepreneurs in Musanze, Rwanda and beyond.*