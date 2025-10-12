@echo off
echo ========================================
echo    InnoStart Setup Script
echo ========================================
echo.

echo Checking XAMPP installation...
if not exist "C:\xampp\xampp-control.exe" (
    echo ERROR: XAMPP not found at C:\xampp\
    echo Please install XAMPP first from https://www.apachefriends.org/
    pause
    exit /b 1
)

echo XAMPP found! Starting services...
echo.

echo Starting Apache and MySQL services...
start "" "C:\xampp\xampp-control.exe"

echo.
echo Waiting for services to start...
timeout /t 5 /nobreak > nul

echo.
echo Checking if services are running...
netstat -an | findstr ":80 " > nul
if %errorlevel% neq 0 (
    echo WARNING: Apache may not be running on port 80
)

netstat -an | findstr ":3306 " > nul
if %errorlevel% neq 0 (
    echo WARNING: MySQL may not be running on port 3306
)

echo.
echo ========================================
echo    Setup Instructions
echo ========================================
echo.
echo 1. Wait for XAMPP Control Panel to open
echo 2. Start Apache and MySQL services if not already running
echo 3. Open your web browser and go to:
echo    http://localhost/innostart/setup_database.php
echo 4. Click "Run Full Setup" to initialize the database
echo 5. After database setup, go to:
echo    http://localhost/innostart/
echo.
echo Default login credentials:
echo Email: admin@innostart.com
echo Password: admin123
echo.
echo ========================================
echo    Python ML Setup (Optional)
echo ========================================
echo.
echo For AI features, you need to install Python dependencies:
echo.
echo Option 1 - Automated Setup:
echo 1. Run: python setup_python.py
echo.
echo Option 2 - Manual Setup:
echo 1. Run: pip install -r requirements.txt
echo 2. Start ML server: python ml_models/musanze_api.py
echo.
echo ========================================

echo.
echo Opening setup page in browser...
start "" "http://localhost/innostart/setup_database.php"

echo.
echo Setup script completed!
echo Check the browser window for database setup.
echo.
pause
