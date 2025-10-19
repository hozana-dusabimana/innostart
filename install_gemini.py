#!/usr/bin/env python3
"""
Installation script for Gemini AI integration
This script installs the required dependencies for the enhanced chat system
"""

import subprocess
import sys
import os

def install_package(package):
    """Install a Python package using pip"""
    try:
        subprocess.check_call([sys.executable, "-m", "pip", "install", package])
        print(f"✅ Successfully installed {package}")
        return True
    except subprocess.CalledProcessError as e:
        print(f"❌ Failed to install {package}: {e}")
        return False

def main():
    """Main installation function"""
    print("🚀 Installing Gemini AI dependencies for InnoStart...")
    print("=" * 50)
    
    # Required packages
    packages = [
        "google-generativeai==0.3.2",
        "pandas==1.5.3",
        "numpy==1.24.3",
        "scikit-learn==1.2.0",
        "requests==2.28.2",
        "flask==2.2.3"
    ]
    
    success_count = 0
    total_packages = len(packages)
    
    for package in packages:
        print(f"Installing {package}...")
        if install_package(package):
            success_count += 1
    
    print("=" * 50)
    print(f"Installation complete: {success_count}/{total_packages} packages installed successfully")
    
    if success_count == total_packages:
        print("🎉 All dependencies installed successfully!")
        print("You can now use the enhanced chat system with Gemini AI integration.")
    else:
        print("⚠️  Some packages failed to install. Please check the errors above.")
        print("You may need to install them manually or check your Python environment.")
    
    # Test the installation
    print("\n🧪 Testing Gemini AI integration...")
    try:
        import google.generativeai as genai
        print("✅ Gemini AI library imported successfully")
        
        # Test basic functionality
        genai.configure(api_key="AIzaSyAfZ4MVyx-t4lvr0kiqwVEfoMYPzKIG-3A")
        model = genai.GenerativeModel('gemini-2.0-flash-exp')
        print("✅ Gemini AI model initialized successfully")
        
    except Exception as e:
        print(f"❌ Gemini AI test failed: {e}")
        print("Please check your internet connection and API key.")

if __name__ == "__main__":
    main()
