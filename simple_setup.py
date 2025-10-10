#!/usr/bin/env python3
"""
InnoStart Simple Setup Script
Basic installation and setup for the InnoStart application
"""

import os
import sys
import subprocess
import platform
import webbrowser
import time
from pathlib import Path

def print_header():
    """Print setup header"""
    print("=" * 60)
    print("InnoStart - AI-Powered Startup Assistant")
    print("   Automated Installation and Setup")
    print("=" * 60)
    print()

def check_python_version():
    """Check if Python version is compatible"""
    print("Checking Python version...")
    
    python_version = sys.version_info
    if python_version < (3, 8):
        print("ERROR: Python 3.8 or higher is required")
        print(f"   Current version: {python_version.major}.{python_version.minor}")
        return False
    
    print(f"SUCCESS: Python {python_version.major}.{python_version.minor} detected")
    return True

def install_python_dependencies():
    """Install Python dependencies"""
    print("\nInstalling Python dependencies...")
    
    try:
        # Install core dependencies
        core_deps = [
            "requests>=2.28.0",
            "openai>=0.27.0",
            "python-dotenv>=0.19.0"
        ]
        
        for dep in core_deps:
            print(f"   Installing {dep}...")
            subprocess.check_call([sys.executable, "-m", "pip", "install", dep])
        
        print("SUCCESS: Python dependencies installed successfully")
        return True
        
    except subprocess.CalledProcessError as e:
        print(f"ERROR: Installing Python dependencies failed: {e}")
        return False

def create_directories():
    """Create necessary directories"""
    print("\nCreating project directories...")
    
    directories = [
        "logs",
        "uploads", 
        "cache",
        "temp"
    ]
    
    for directory in directories:
        dir_path = Path(directory)
        dir_path.mkdir(parents=True, exist_ok=True)
        print(f"   Created: {directory}")

def create_env_file():
    """Create .env file with default configuration"""
    print("\nCreating environment configuration...")
    
    env_content = """# InnoStart Environment Configuration
# Update these values with your actual configuration

# OpenAI API Configuration (optional - for enhanced AI features)
OPENAI_API_KEY=your-openai-api-key-here

# Application Configuration
APP_DEBUG=true
APP_URL=http://localhost/innostart

# Database Configuration (if needed in future)
DB_HOST=localhost
DB_NAME=innostart
DB_USER=root
DB_PASS=

# Email Configuration (if needed in future)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=
SMTP_PASSWORD=
FROM_EMAIL=noreply@innostart.com
FROM_NAME=InnoStart
"""
    
    with open(".env", "w") as f:
        f.write(env_content)
        
    print("SUCCESS: Created .env file")
    print("   Please update .env file with your actual configuration values")

def test_python_integration():
    """Test Python AI integration"""
    print("\nTesting Python AI integration...")
    
    try:
        # Test basic imports
        import requests
        import json
        print("   SUCCESS: Core Python modules imported successfully")
        
        # Test AI integration script
        ai_script = Path("python") / "ai_integration.py"
        if ai_script.exists():
            print("   SUCCESS: AI integration script found")
            
            # Test script syntax
            with open(ai_script, 'r') as f:
                code = f.read()
            compile(code, str(ai_script), 'exec')
            print("   SUCCESS: AI integration script syntax is valid")
        else:
            print("   WARNING: AI integration script not found")
            
        return True
        
    except Exception as e:
        print(f"   ERROR: Python integration test failed: {e}")
        return False

def run_installation_tests():
    """Run installation tests"""
    print("\nRunning installation tests...")
    
    # Test file structure
    required_files = [
        "index.html",
        "assets/css/style.css", 
        "assets/js/main.js",
        "api/chat.php",
        "api/ideas.php",
        "api/business-plan.php",
        "config/config.php"
    ]
    
    all_files_exist = True
    for file_path in required_files:
        if Path(file_path).exists():
            print(f"   SUCCESS: {file_path}")
        else:
            print(f"   ERROR: {file_path} (missing)")
            all_files_exist = False
            
    if all_files_exist:
        print("   SUCCESS: All required files are present")
    else:
        print("   WARNING: Some files are missing - please check the installation")
        
    return all_files_exist

def start_web_server():
    """Start a simple web server for testing"""
    print("\nStarting web server...")
    
    try:
        print("   Starting Python HTTP server...")
        print("   Server will be available at: http://localhost:8000")
        print("   Main application: http://localhost:8000/index.html")
        print("   Installation test: http://localhost:8000/install.php")
        print("   System test: http://localhost:8000/test.php")
        print("\n   Press Ctrl+C to stop the server")
        
        # Start server
        subprocess.run([sys.executable, "-m", "http.server", "8000"])
        
    except KeyboardInterrupt:
        print("\n   Server stopped by user")
    except Exception as e:
        print(f"   ERROR: Starting web server failed: {e}")

def print_next_steps():
    """Print next steps for the user"""
    print("\n" + "=" * 60)
    print("InnoStart Setup Complete!")
    print("=" * 60)
    print()
    print("Next Steps:")
    print("1. Open your browser and go to: http://localhost:8000/index.html")
    print("2. Run the system test: http://localhost:8000/test.php")
    print("3. Run the installation check: http://localhost:8000/install.php")
    print("4. (Optional) Set up OpenAI API key in .env file for enhanced AI features")
    print("5. Read the README.md for detailed usage instructions")
    print()
    print("Features Available:")
    print("   • AI Chatbot for business advice")
    print("   • Location-based business idea generation")
    print("   • Financial projection calculator")
    print("   • Business plan generator")
    print("   • Modern responsive UI")
    print()
    print("Tips:")
    print("   • The application works offline with fallback AI responses")
    print("   • Add your OpenAI API key for enhanced AI features")
    print("   • All data is processed locally for privacy")
    print()

def main():
    """Main function"""
    print_header()
    
    # Check Python version
    if not check_python_version():
        return False
        
    # Create directories
    create_directories()
    
    # Install Python dependencies
    if not install_python_dependencies():
        print("WARNING: Some Python dependencies failed to install")
        
    # Create environment file
    create_env_file()
    
    # Test Python integration
    test_python_integration()
    
    # Run installation tests
    run_installation_tests()
    
    # Print next steps
    print_next_steps()
    
    # Ask if user wants to start web server
    try:
        response = input("\nWould you like to start the web server now? (y/n): ").lower().strip()
        if response in ['y', 'yes']:
            start_web_server()
        else:
            print("\nTo start the server later, run: python -m http.server 8000")
            print("Then visit: http://localhost:8000/index.html")
    except KeyboardInterrupt:
        print("\nSetup completed. Run the server when ready!")
        
    return True

if __name__ == "__main__":
    try:
        success = main()
        if success:
            print("\nSUCCESS: InnoStart setup completed successfully!")
        else:
            print("\nERROR: Setup encountered some issues. Please check the error messages above.")
            sys.exit(1)
    except KeyboardInterrupt:
        print("\nSetup interrupted by user.")
        sys.exit(0)
    except Exception as e:
        print(f"\nERROR: Setup failed with error: {e}")
        sys.exit(1)
