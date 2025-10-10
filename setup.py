#!/usr/bin/env python3
"""
InnoStart Setup Script
Automated installation and setup for the InnoStart application
"""

import os
import sys
import subprocess
import platform
import webbrowser
import time
from pathlib import Path

class InnoStartSetup:
    def __init__(self):
        self.project_root = Path(__file__).parent
        self.system = platform.system().lower()
        self.python_version = sys.version_info
        
    def print_header(self):
        """Print setup header"""
        print("=" * 60)
        print("InnoStart - AI-Powered Startup Assistant")
        print("   Automated Installation and Setup")
        print("=" * 60)
        print()
        
    def check_python_version(self):
        """Check if Python version is compatible"""
        print("Checking Python version...")
        
        if self.python_version < (3, 8):
            print("ERROR: Python 3.8 or higher is required")
            print(f"   Current version: {self.python_version.major}.{self.python_version.minor}")
            return False
        
        print(f"SUCCESS: Python {self.python_version.major}.{self.python_version.minor} detected")
        return True
        
    def install_python_dependencies(self):
        """Install Python dependencies"""
        print("\n📦 Installing Python dependencies...")
        
        try:
            # Install core dependencies first
            core_deps = [
                "requests>=2.28.0",
                "openai>=0.27.0",
                "python-dotenv>=0.19.0"
            ]
            
            for dep in core_deps:
                print(f"   Installing {dep}...")
                subprocess.check_call([sys.executable, "-m", "pip", "install", dep])
            
            # Install optional dependencies
            optional_deps = [
                "numpy>=1.21.0",
                "pandas>=1.3.0",
                "matplotlib>=3.5.0"
            ]
            
            print("   Installing optional dependencies...")
            for dep in optional_deps:
                try:
                    subprocess.check_call([sys.executable, "-m", "pip", "install", dep], 
                                        stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
                except subprocess.CalledProcessError:
                    print(f"   ⚠️  Warning: Could not install {dep} (optional)")
            
            print("✅ Python dependencies installed successfully")
            return True
            
        except subprocess.CalledProcessError as e:
            print(f"❌ Error installing Python dependencies: {e}")
            return False
            
    def create_directories(self):
        """Create necessary directories"""
        print("\n📁 Creating project directories...")
        
        directories = [
            "logs",
            "uploads", 
            "cache",
            "temp",
            "assets/css",
            "assets/js",
            "api",
            "config",
            "python"
        ]
        
        for directory in directories:
            dir_path = self.project_root / directory
            dir_path.mkdir(parents=True, exist_ok=True)
            print(f"   ✅ Created: {directory}")
            
    def create_env_file(self):
        """Create .env file with default configuration"""
        print("\n⚙️  Creating environment configuration...")
        
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
        
        env_file = self.project_root / ".env"
        with open(env_file, "w") as f:
            f.write(env_content)
            
        print("✅ Created .env file")
        print("   ⚠️  Please update .env file with your actual configuration values")
        
    def test_python_integration(self):
        """Test Python AI integration"""
        print("\n🧪 Testing Python AI integration...")
        
        try:
            # Test basic imports
            import requests
            import json
            print("   ✅ Core Python modules imported successfully")
            
            # Test AI integration script
            ai_script = self.project_root / "python" / "ai_integration.py"
            if ai_script.exists():
                print("   ✅ AI integration script found")
                
                # Test script syntax
                with open(ai_script, 'r') as f:
                    code = f.read()
                compile(code, str(ai_script), 'exec')
                print("   ✅ AI integration script syntax is valid")
            else:
                print("   ⚠️  AI integration script not found")
                
            return True
            
        except Exception as e:
            print(f"   ❌ Python integration test failed: {e}")
            return False
            
    def start_web_server(self):
        """Start a simple web server for testing"""
        print("\n🌐 Starting web server...")
        
        try:
            # Check if we're on Windows
            if self.system == "windows":
                print("   Starting Python HTTP server on Windows...")
                print("   📍 Server will be available at: http://localhost:8000")
                print("   📍 Main application: http://localhost:8000/index.html")
                print("   📍 Installation test: http://localhost:8000/install.php")
                print("   📍 System test: http://localhost:8000/test.php")
                print("\n   Press Ctrl+C to stop the server")
                
                # Start server in background
                os.chdir(self.project_root)
                subprocess.Popen([sys.executable, "-m", "http.server", "8000"])
                
                # Wait a moment for server to start
                time.sleep(2)
                
                # Try to open browser
                try:
                    webbrowser.open("http://localhost:8000/index.html")
                    print("   🌐 Browser opened automatically")
                except:
                    print("   ⚠️  Could not open browser automatically")
                    
            else:
                print("   Starting Python HTTP server...")
                print("   📍 Server will be available at: http://localhost:8000")
                print("   📍 Main application: http://localhost:8000/index.html")
                print("\n   Press Ctrl+C to stop the server")
                
                os.chdir(self.project_root)
                subprocess.run([sys.executable, "-m", "http.server", "8000"])
                
        except KeyboardInterrupt:
            print("\n   🛑 Server stopped by user")
        except Exception as e:
            print(f"   ❌ Error starting web server: {e}")
            
    def run_installation_tests(self):
        """Run installation tests"""
        print("\n🔍 Running installation tests...")
        
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
            full_path = self.project_root / file_path
            if full_path.exists():
                print(f"   ✅ {file_path}")
            else:
                print(f"   ❌ {file_path} (missing)")
                all_files_exist = False
                
        if all_files_exist:
            print("   ✅ All required files are present")
        else:
            print("   ⚠️  Some files are missing - please check the installation")
            
        return all_files_exist
        
    def print_next_steps(self):
        """Print next steps for the user"""
        print("\n" + "=" * 60)
        print("🎉 InnoStart Setup Complete!")
        print("=" * 60)
        print()
        print("📋 Next Steps:")
        print("1. 🌐 Open your browser and go to: http://localhost:8000/index.html")
        print("2. 🧪 Run the system test: http://localhost:8000/test.php")
        print("3. ⚙️  Run the installation check: http://localhost:8000/install.php")
        print("4. 🔑 (Optional) Set up OpenAI API key in .env file for enhanced AI features")
        print("5. 📚 Read the README.md for detailed usage instructions")
        print()
        print("🚀 Features Available:")
        print("   • AI Chatbot for business advice")
        print("   • Location-based business idea generation")
        print("   • Financial projection calculator")
        print("   • Business plan generator")
        print("   • Modern responsive UI")
        print()
        print("💡 Tips:")
        print("   • The application works offline with fallback AI responses")
        print("   • Add your OpenAI API key for enhanced AI features")
        print("   • All data is processed locally for privacy")
        print()
        print("🆘 Need Help?")
        print("   • Check the README.md file")
        print("   • Run test.php to diagnose any issues")
        print("   • Review the logs/ directory for error messages")
        print()
        
    def run_setup(self):
        """Run the complete setup process"""
        self.print_header()
        
        # Check Python version
        if not self.check_python_version():
            return False
            
        # Create directories
        self.create_directories()
        
        # Install Python dependencies
        if not self.install_python_dependencies():
            print("⚠️  Warning: Some Python dependencies failed to install")
            
        # Create environment file
        self.create_env_file()
        
        # Test Python integration
        self.test_python_integration()
        
        # Run installation tests
        self.run_installation_tests()
        
        # Print next steps
        self.print_next_steps()
        
        # Ask if user wants to start web server
        try:
            response = input("\n🌐 Would you like to start the web server now? (y/n): ").lower().strip()
            if response in ['y', 'yes']:
                self.start_web_server()
            else:
                print("\n💡 To start the server later, run: python -m http.server 8000")
                print("   Then visit: http://localhost:8000/index.html")
        except KeyboardInterrupt:
            print("\n👋 Setup completed. Run the server when ready!")
            
        return True

def main():
    """Main function"""
    setup = InnoStartSetup()
    success = setup.run_setup()
    
    if success:
        print("\n✅ InnoStart setup completed successfully!")
    else:
        print("\n❌ Setup encountered some issues. Please check the error messages above.")
        sys.exit(1)

if __name__ == "__main__":
    main()
