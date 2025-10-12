#!/usr/bin/env python3
"""
InnoStart Complete Project Runner
Single script to run everything in the InnoStart project
"""

import os
import sys
import json
import time
import subprocess
import argparse
import logging
from datetime import datetime
from pathlib import Path

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('innostart.log'),
        logging.StreamHandler()
    ]
)
logger = logging.getLogger(__name__)

class InnoStartRunner:
    """Complete InnoStart project runner"""
    
    def __init__(self):
        self.project_root = Path(__file__).parent
        self.server_process = None
        self.config = {
            'server_port': 8000,
            'python_path': sys.executable,
            'ml_model_path': 'ml_models/business_response_model.pkl',
            'training_data_path': 'training_data/business_training_data.csv'
        }
    
    def print_banner(self):
        """Print project banner"""
        banner = """
================================================================
                    InnoStart Runner
================================================================

  Complete AI-Powered Startup Assistant Platform
  - Business Planning & Strategy
  - AI-Powered Chat & Advice
  - Financial Projections & Analysis
  - Business Plan Generation
  - Machine Learning Integration

================================================================
        """
        print(banner)
    
    def check_requirements(self):
        """Check if all requirements are met"""
        logger.info("Checking requirements...")
        
        requirements = {
            'python': sys.version_info >= (3, 8),
            'pip': self._check_command('pip'),
            'php': self._check_command('php'),
            'node': self._check_command('node', optional=True),
            'npm': self._check_command('npm', optional=True)
        }
        
        missing = [k for k, v in requirements.items() if not v]
        if missing:
            logger.error(f"Missing requirements: {missing}")
            return False
        
        logger.info("[OK] All requirements met")
        return True
    
    def _check_command(self, command, optional=False):
        """Check if a command is available"""
        try:
            subprocess.run([command, '--version'], 
                         capture_output=True, check=True)
            return True
        except (subprocess.CalledProcessError, FileNotFoundError):
            if not optional:
                logger.warning(f"Command not found: {command}")
            return False
    
    def install_dependencies(self):
        """Install Python dependencies"""
        logger.info("Installing Python dependencies...")
        
        try:
            # Install from requirements.txt
            if os.path.exists('requirements.txt'):
                subprocess.run([sys.executable, '-m', 'pip', 'install', '-r', 'requirements.txt'], 
                             check=True, cwd=self.project_root)
                logger.info("[OK] Dependencies installed from requirements.txt")
            
            # Install additional ML dependencies
            ml_packages = [
                'scikit-learn', 'pandas', 'numpy', 'requests',
                'transformers', 'torch', 'kaggle'
            ]
            
            for package in ml_packages:
                try:
                    subprocess.run([sys.executable, '-m', 'pip', 'install', package], 
                                 check=True, capture_output=True)
                except subprocess.CalledProcessError:
                    logger.warning(f"Could not install {package}")
            
            logger.info("[OK] ML dependencies installed")
            return True
            
        except subprocess.CalledProcessError as e:
            logger.error(f"Failed to install dependencies: {e}")
            return False
    
    def setup_directories(self):
        """Create necessary directories"""
        logger.info("Setting up directories...")
        
        directories = [
            'ml_models', 'training_data', 'datasets', 'logs',
            'uploads', 'api', 'assets/css', 'assets/js'
        ]
        
        for directory in directories:
            os.makedirs(directory, exist_ok=True)
        
        logger.info("[OK] Directories created")
    
    def train_ml_model(self):
        """Train the machine learning model"""
        logger.info("Training ML model...")
        
        try:
            # Run training script
            result = subprocess.run([
                sys.executable, 'ml_models/train_response_model.py'
            ], capture_output=True, text=True, cwd=self.project_root)
            
            if result.returncode == 0:
                logger.info("[OK] ML model trained successfully")
                return True
            else:
                logger.error(f"Training failed: {result.stderr}")
                return False
                
        except Exception as e:
            logger.error(f"Error training model: {e}")
            return False
    
    def test_ml_integration(self):
        """Test the ML integration"""
        logger.info("Testing ML integration...")
        
        try:
            result = subprocess.run([
                sys.executable, 'ml_models/simple_test.py'
            ], capture_output=True, text=True, cwd=self.project_root)
            
            if result.returncode == 0:
                logger.info("[OK] ML integration tests passed")
                return True
            else:
                logger.error(f"Tests failed: {result.stderr}")
                return False
                
        except Exception as e:
            logger.error(f"Error running tests: {e}")
            return False
    
    def start_web_server(self, port=None):
        """Start the web server"""
        port = port or self.config['server_port']
        logger.info(f"Starting web server on port {port}...")
        
        try:
            # Start Python HTTP server
            self.server_process = subprocess.Popen([
                sys.executable, '-m', 'http.server', str(port)
            ], cwd=self.project_root)
            
            # Wait a moment for server to start
            time.sleep(2)
            
            # Check if server is running
            if self.server_process.poll() is None:
                logger.info(f"[OK] Web server started on http://localhost:{port}")
                return True
            else:
                logger.error("Failed to start web server")
                return False
                
        except Exception as e:
            logger.error(f"Error starting server: {e}")
            return False
    
    def stop_web_server(self):
        """Stop the web server"""
        if self.server_process:
            logger.info("Stopping web server...")
            self.server_process.terminate()
            self.server_process.wait()
            logger.info("[OK] Web server stopped")
    
    def check_installation(self):
        """Check installation status"""
        logger.info("Checking installation...")
        
        try:
            # Check if install.php exists and run it
            if os.path.exists('install.php'):
                result = subprocess.run([
                    'php', 'install.php'
                ], capture_output=True, text=True, cwd=self.project_root)
                
                if result.returncode == 0:
                    logger.info("[OK] Installation check passed")
                    return True
                else:
                    logger.warning(f"Installation check issues: {result.stderr}")
                    return False
            else:
                logger.warning("install.php not found")
                return False
                
        except Exception as e:
            logger.error(f"Error checking installation: {e}")
            return False
    
    def open_browser(self, url=None):
        """Open browser to the application"""
        urls_to_open = []
        
        if url:
            urls_to_open.append(url)
        else:
            # Open both Python server and XAMPP URLs
            urls_to_open.extend([
                f"http://localhost:{self.config['server_port']}/index.html",
                "http://localhost/innostart/"
            ])
        
        try:
            import webbrowser
            for url in urls_to_open:
                webbrowser.open(url)
                logger.info(f"[OK] Browser opened to {url}")
        except Exception as e:
            logger.warning(f"Could not open browser: {e}")
            print("Please open your browser to:")
            for url in urls_to_open:
                print(f"  - {url}")
    
    def run_complete_setup(self):
        """Run complete project setup"""
        logger.info("Running complete InnoStart setup...")
        
        steps = [
            ("Checking requirements", self.check_requirements),
            ("Setting up directories", self.setup_directories),
            ("Installing dependencies", self.install_dependencies),
            ("Training ML model", self.train_ml_model),
            ("Testing ML integration", self.test_ml_integration),
            ("Checking installation", self.check_installation),
            ("Starting web server", lambda: self.start_web_server()),
        ]
        
        for step_name, step_func in steps:
            logger.info(f"Step: {step_name}")
            if not step_func():
                logger.error(f"Setup failed at: {step_name}")
                return False
        
        logger.info("[OK] Complete setup finished successfully!")
        return True
    
    def run_development_mode(self):
        """Run in development mode with auto-reload"""
        logger.info("Starting development mode...")
        
        if not self.start_web_server():
            return False
        
        try:
            self.open_browser()
            
            print("\n" + "="*60)
            print("InnoStart Development Server Running!")
            print("="*60)
            print("Python Server (Port 8000):")
            print(f"  Main Application: http://localhost:{self.config['server_port']}/index.html")
            print(f"  Dashboard: http://localhost:{self.config['server_port']}/dashboard.html")
            print(f"  Installation Check: http://localhost:{self.config['server_port']}/install.php")
            print()
            print("XAMPP Server (Port 80):")
            print("  Main Application: http://localhost/innostart/")
            print("  Dashboard: http://localhost/innostart/dashboard.html")
            print("  Login: http://localhost/innostart/login.html")
            print()
            print("AI Chat: Available in both applications")
            print("="*60)
            print("Press Ctrl+C to stop the server")
            print("="*60)
            
            # Keep server running
            while True:
                time.sleep(1)
                
        except KeyboardInterrupt:
            logger.info("Development mode stopped by user")
            self.stop_web_server()
            return True
    
    def run_production_mode(self):
        """Run in production mode"""
        logger.info("Starting production mode...")
        
        # Run complete setup first
        if not self.run_complete_setup():
            return False
        
        # Start server
        if not self.start_web_server():
            return False
        
        try:
            self.open_browser()
            
            print("\n" + "="*60)
            print("InnoStart Production Server Running!")
            print("="*60)
            print("Python Server (Port 8000):")
            print(f"  Application: http://localhost:{self.config['server_port']}")
            print()
            print("XAMPP Server (Port 80):")
            print("  Application: http://localhost/innostart/")
            print()
            print("AI Features: Fully integrated and trained")
            print("Analytics: Available in dashboard")
            print("="*60)
            print("Press Ctrl+C to stop the server")
            print("="*60)
            
            # Keep server running
            while True:
                time.sleep(1)
                
        except KeyboardInterrupt:
            logger.info("Production mode stopped by user")
            self.stop_web_server()
            return True
    
    def run_training_only(self):
        """Run only the ML training"""
        logger.info("Running ML training only...")
        
        steps = [
            ("Installing dependencies", self.install_dependencies),
            ("Training ML model", self.train_ml_model),
            ("Testing ML integration", self.test_ml_integration),
        ]
        
        for step_name, step_func in steps:
            logger.info(f"Step: {step_name}")
            if not step_func():
                logger.error(f"Training failed at: {step_name}")
                return False
        
        logger.info("[OK] ML training completed successfully!")
        return True
    
    def run_server_only(self):
        """Run only the web server"""
        logger.info("Starting web server only...")
        
        if not self.start_web_server():
            return False
        
        try:
            self.open_browser()
            
            print("\n" + "="*60)
            print("InnoStart Web Server Running!")
            print("="*60)
            print("Python Server (Port 8000):")
            print(f"  Application: http://localhost:{self.config['server_port']}")
            print()
            print("XAMPP Server (Port 80):")
            print("  Application: http://localhost/innostart/")
            print("="*60)
            print("Press Ctrl+C to stop the server")
            print("="*60)
            
            # Keep server running
            while True:
                time.sleep(1)
                
        except KeyboardInterrupt:
            logger.info("Server stopped by user")
            self.stop_web_server()
            return True
    
    def show_status(self):
        """Show project status"""
        logger.info("Checking project status...")
        
        status = {
            'ml_model': os.path.exists(self.config['ml_model_path']),
            'training_data': os.path.exists(self.config['training_data_path']),
            'enhanced_api': os.path.exists('api/enhanced_chat.php'),
            'main_app': os.path.exists('index.html'),
            'dashboard': os.path.exists('dashboard.html'),
            'server_running': self.server_process and self.server_process.poll() is None
        }
        
        print("\n" + "="*50)
        print("InnoStart Project Status")
        print("="*50)
        
        for component, exists in status.items():
            status_icon = "[OK]" if exists else "[X]"
            print(f"{status_icon} {component.replace('_', ' ').title()}")
        
        print("="*50)
        
        if all(status.values()):
            print("All components are ready!")
        else:
            print("Some components need attention")
        
        return status
    
    def cleanup(self):
        """Clean up temporary files and processes"""
        logger.info("Cleaning up...")
        
        self.stop_web_server()
        
        # Clean up temporary files
        temp_files = ['*.pyc', '__pycache__', '*.log']
        for pattern in temp_files:
            # Implementation would go here
            pass
        
        logger.info("[OK] Cleanup completed")

def main():
    """Main function"""
    parser = argparse.ArgumentParser(description='InnoStart Complete Project Runner')
    parser.add_argument('command', nargs='?', default='dev',
                       choices=['setup', 'dev', 'prod', 'train', 'server', 'status', 'cleanup'],
                       help='Command to run')
    parser.add_argument('--port', type=int, default=8000,
                       help='Port for web server')
    parser.add_argument('--verbose', '-v', action='store_true',
                       help='Verbose output')
    
    args = parser.parse_args()
    
    if args.verbose:
        logging.getLogger().setLevel(logging.DEBUG)
    
    runner = InnoStartRunner()
    runner.print_banner()
    
    try:
        if args.command == 'setup':
            success = runner.run_complete_setup()
        elif args.command == 'dev':
            success = runner.run_development_mode()
        elif args.command == 'prod':
            success = runner.run_production_mode()
        elif args.command == 'train':
            success = runner.run_training_only()
        elif args.command == 'server':
            success = runner.run_server_only()
        elif args.command == 'status':
            runner.show_status()
            success = True
        elif args.command == 'cleanup':
            runner.cleanup()
            success = True
        else:
            print(f"Unknown command: {args.command}")
            success = False
        
        return 0 if success else 1
        
    except KeyboardInterrupt:
        logger.info("Operation cancelled by user")
        runner.cleanup()
        return 0
    except Exception as e:
        logger.error(f"Unexpected error: {e}")
        runner.cleanup()
        return 1

if __name__ == "__main__":
    sys.exit(main())
