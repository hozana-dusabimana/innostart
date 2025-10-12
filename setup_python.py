#!/usr/bin/env python3
"""
InnoStart Python Setup Script
Automatically installs dependencies and starts the ML server
"""

import subprocess
import sys
import os
import time

def check_python_version():
    """Check if Python version is 3.8 or higher"""
    version = sys.version_info
    if version.major < 3 or (version.major == 3 and version.minor < 8):
        print("❌ Python 3.8 or higher is required!")
        print(f"Current version: {version.major}.{version.minor}.{version.micro}")
        return False
    print(f"✅ Python {version.major}.{version.minor}.{version.micro} detected")
    return True

def install_requirements():
    """Install requirements from requirements.txt"""
    print("\n📦 Installing Python dependencies...")
    
    try:
        # Check if requirements.txt exists
        if not os.path.exists('requirements.txt'):
            print("❌ requirements.txt not found!")
            return False
        
        # Install requirements
        result = subprocess.run([
            sys.executable, '-m', 'pip', 'install', '-r', 'requirements.txt'
        ], capture_output=True, text=True)
        
        if result.returncode == 0:
            print("✅ All dependencies installed successfully!")
            return True
        else:
            print("❌ Failed to install dependencies:")
            print(result.stderr)
            return False
            
    except Exception as e:
        print(f"❌ Error installing dependencies: {e}")
        return False

def verify_installation():
    """Verify that all required packages are installed"""
    print("\n🔍 Verifying installation...")
    
    required_packages = [
        'pandas', 'numpy', 'sklearn', 'openai', 'requests', 'flask'
    ]
    
    missing_packages = []
    
    for package in required_packages:
        try:
            if package == 'sklearn':
                import sklearn
                print(f"✅ scikit-learn {sklearn.__version__}")
            else:
                module = __import__(package)
                version = getattr(module, '__version__', 'unknown')
                print(f"✅ {package} {version}")
        except ImportError:
            print(f"❌ {package} not found")
            missing_packages.append(package)
    
    if missing_packages:
        print(f"\n❌ Missing packages: {', '.join(missing_packages)}")
        return False
    
    print("\n✅ All packages verified successfully!")
    return True

def start_ml_server():
    """Start the ML API server"""
    print("\n🚀 Starting ML API server...")
    
    try:
        # Check if the ML API file exists
        ml_api_path = os.path.join('ml_models', 'musanze_api.py')
        if not os.path.exists(ml_api_path):
            print("❌ ML API file not found!")
            return False
        
        print("Starting server on http://127.0.0.1:5000")
        print("Press Ctrl+C to stop the server")
        print("-" * 50)
        
        # Start the server
        subprocess.run([sys.executable, ml_api_path])
        
    except KeyboardInterrupt:
        print("\n\n🛑 Server stopped by user")
        return True
    except Exception as e:
        print(f"❌ Error starting server: {e}")
        return False

def main():
    """Main setup function"""
    print("=" * 60)
    print("    InnoStart Python Setup Script")
    print("=" * 60)
    
    # Check Python version
    if not check_python_version():
        sys.exit(1)
    
    # Install requirements
    if not install_requirements():
        print("\n❌ Setup failed during dependency installation")
        sys.exit(1)
    
    # Verify installation
    if not verify_installation():
        print("\n❌ Setup failed during verification")
        sys.exit(1)
    
    print("\n" + "=" * 60)
    print("✅ Python setup completed successfully!")
    print("=" * 60)
    
    # Ask if user wants to start the server
    response = input("\n🚀 Start the ML API server now? (y/n): ").lower().strip()
    
    if response in ['y', 'yes']:
        start_ml_server()
    else:
        print("\n📝 To start the server manually, run:")
        print("   python ml_models/musanze_api.py")
        print("\n🌐 Then access InnoStart at: http://localhost/innostart/")

if __name__ == "__main__":
    main()
