<?php
/**
 * InnoStart Installation Script
 * Run this script to set up the application
 */

// Check PHP version
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    die("Error: PHP 7.4 or higher is required. Current version: " . PHP_VERSION);
}

// Check required PHP extensions
$required_extensions = ['curl', 'json', 'mbstring'];
$missing_extensions = [];

foreach ($required_extensions as $extension) {
    if (!extension_loaded($extension)) {
        $missing_extensions[] = $extension;
    }
}

if (!empty($missing_extensions)) {
    die("Error: Missing required PHP extensions: " . implode(', ', $missing_extensions));
}

echo "<h1>InnoStart Installation</h1>";
echo "<p>Setting up InnoStart application...</p>";

// Create necessary directories
$directories = [
    'logs',
    'uploads',
    'cache',
    'temp'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "<p>✓ Created directory: $dir</p>";
        } else {
            echo "<p>✗ Failed to create directory: $dir</p>";
        }
    } else {
        echo "<p>✓ Directory exists: $dir</p>";
    }
}

// Check file permissions
$writable_dirs = ['logs', 'uploads', 'cache', 'temp'];
foreach ($writable_dirs as $dir) {
    if (is_writable($dir)) {
        echo "<p>✓ Directory is writable: $dir</p>";
    } else {
        echo "<p>⚠ Directory is not writable: $dir (chmod 755 recommended)</p>";
    }
}

// Test API endpoints
echo "<h2>Testing API Endpoints</h2>";

$api_endpoints = [
    'api/chat.php' => 'Chat API',
    'api/ideas.php' => 'Ideas API',
    'api/business-plan.php' => 'Business Plan API'
];

foreach ($api_endpoints as $endpoint => $name) {
    if (file_exists($endpoint)) {
        echo "<p>✓ $name endpoint exists</p>";
    } else {
        echo "<p>✗ $name endpoint missing: $endpoint</p>";
    }
}

// Test configuration
echo "<h2>Configuration Check</h2>";

if (file_exists('config/config.php')) {
    echo "<p>✓ Configuration file exists</p>";
    
    // Include config to test
    try {
        include 'config/config.php';
        echo "<p>✓ Configuration file loads successfully</p>";
        
        // Check if constants are defined
        $required_constants = ['APP_NAME', 'APP_VERSION', 'APP_URL'];
        foreach ($required_constants as $constant) {
            if (defined($constant)) {
                echo "<p>✓ Constant defined: $constant</p>";
            } else {
                echo "<p>⚠ Constant not defined: $constant</p>";
            }
        }
    } catch (Exception $e) {
        echo "<p>✗ Configuration file error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>✗ Configuration file missing: config/config.php</p>";
}

// Test Python integration (optional)
echo "<h2>Python Integration Check</h2>";

if (file_exists('python/ai_integration.py')) {
    echo "<p>✓ Python AI integration file exists</p>";
    
    // Check if Python is available
    $python_version = shell_exec('python --version 2>&1');
    if ($python_version) {
        echo "<p>✓ Python is available: " . trim($python_version) . "</p>";
        
        // Check if required Python packages are installed
        $required_packages = ['requests', 'openai'];
        foreach ($required_packages as $package) {
            $result = shell_exec("python -c \"import $package\" 2>&1");
            if (empty($result)) {
                echo "<p>✓ Python package installed: $package</p>";
            } else {
                echo "<p>⚠ Python package not installed: $package</p>";
            }
        }
    } else {
        echo "<p>⚠ Python is not available or not in PATH</p>";
    }
} else {
    echo "<p>⚠ Python AI integration file missing</p>";
}

// Create sample .env file
echo "<h2>Environment Configuration</h2>";

if (!file_exists('.env')) {
    $env_content = "# InnoStart Environment Configuration
# Copy this file and update with your actual values

# OpenAI API Configuration (optional)
OPENAI_API_KEY=your-openai-api-key-here

# Application Configuration
APP_DEBUG=true
APP_URL=http://localhost/innostart

# Database Configuration (if needed)
DB_HOST=localhost
DB_NAME=innostart
DB_USER=root
DB_PASS=

# Email Configuration (if needed)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=
SMTP_PASSWORD=
FROM_EMAIL=noreply@innostart.com
FROM_NAME=InnoStart
";
    
    if (file_put_contents('.env', $env_content)) {
        echo "<p>✓ Created sample .env file</p>";
        echo "<p>⚠ Please update .env file with your actual configuration values</p>";
    } else {
        echo "<p>✗ Failed to create .env file</p>";
    }
} else {
    echo "<p>✓ .env file already exists</p>";
}

// Test web server configuration
echo "<h2>Web Server Check</h2>";

if (isset($_SERVER['SERVER_SOFTWARE'])) {
    echo "<p>✓ Web server detected: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
} else {
    echo "<p>⚠ Web server information not available</p>";
}

// Check if mod_rewrite is available (for clean URLs)
if (function_exists('apache_get_modules')) {
    if (in_array('mod_rewrite', apache_get_modules())) {
        echo "<p>✓ mod_rewrite is available</p>";
    } else {
        echo "<p>⚠ mod_rewrite is not available (clean URLs may not work)</p>";
    }
}

// Final installation summary
echo "<h2>Installation Summary</h2>";
echo "<p>InnoStart installation check completed!</p>";

echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Update the .env file with your configuration</li>";
echo "<li>Set up your OpenAI API key (optional, for enhanced AI features)</li>";
echo "<li>Configure your web server if needed</li>";
echo "<li>Test the application by visiting the main page</li>";
echo "<li>Review the README.md for detailed usage instructions</li>";
echo "</ol>";

echo "<h3>Access Your Application:</h3>";
$app_url = $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
echo "<p><a href='index.html' target='_blank'>Open InnoStart Application</a></p>";
echo "<p>URL: <a href='http://$app_url/index.html' target='_blank'>http://$app_url/index.html</a></p>";

echo "<h3>Support:</h3>";
echo "<p>If you encounter any issues, please check:</p>";
echo "<ul>";
echo "<li>PHP version and extensions</li>";
echo "<li>File permissions</li>";
echo "<li>Web server configuration</li>";
echo "<li>Error logs in the logs/ directory</li>";
echo "</ul>";

echo "<p><strong>Installation completed at:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>

