#!/usr/bin/env python3
"""
Simple InnoStart Starter - XAMPP Version
One-click script to start InnoStart with XAMPP
"""

import os
import sys
import subprocess
import time
import webbrowser
import socket
from pathlib import Path

def check_xampp_running():
    """Check if XAMPP Apache is running on port 80"""
    try:
        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        result = sock.connect_ex(('localhost', 80))
        sock.close()
        return result == 0
    except:
        return False

def main():
    """Main function to start InnoStart with XAMPP"""
    
    print("="*60)
    print("Starting InnoStart - AI-Powered Startup Assistant")
    print("Using XAMPP (Apache) Server")
    print("="*60)
    
    # Check if we're in the right directory
    if not os.path.exists('index.html'):
        print("Error: Please run this script from the InnoStart project directory")
        return 1
    
    # Check if XAMPP is running
    print("Checking if XAMPP Apache is running...")
    if not check_xampp_running():
        print("Error: XAMPP Apache is not running on port 80")
        print("Please start XAMPP Control Panel and start Apache service")
        print("Then run this script again.")
        return 1
    
    print("[OK] XAMPP Apache is running!")
    
    # Open browser to XAMPP URL
    try:
        webbrowser.open('http://localhost/innostart/')
        print("[OK] Browser opened to XAMPP")
    except:
        print("Please open your browser to: http://localhost/innostart/")
    
    print("\n" + "="*60)
    print("InnoStart is now running with XAMPP!")
    print("="*60)
    print("Main Application: http://localhost/innostart/")
    print("Dashboard: http://localhost/innostart/dashboard.html")
    print("Login: http://localhost/innostart/login.html")
    print("="*60)
    print("Login credentials:")
    print("  Username: demo")
    print("  Password: demo123")
    print("="*60)
    print("Press Ctrl+C to exit")
    print("="*60)
    
    # Keep script running
    try:
        while True:
            time.sleep(1)
    except KeyboardInterrupt:
        print("\nGoodbye!")
        return 0

if __name__ == "__main__":
    sys.exit(main())
