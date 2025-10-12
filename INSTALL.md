# Quick Installation Guide - InnoStart

## 🚀 Quick Start (5 Minutes)

### Step 1: Install XAMPP
1. Download from: https://www.apachefriends.org/
2. Install with default settings
3. Start Apache and MySQL from XAMPP Control Panel

### Step 2: Setup Database
1. Open browser: `http://localhost/innostart/setup_database.php`
2. Click **"Run Full Setup"**
3. Wait for completion message

### Step 3: Access Application
1. Go to: `http://localhost/innostart/`
2. Login with: `admin@innostart.com` / `admin123`
3. Or create new account via signup

## 🔧 Alternative Setup

### Windows Users
- Run `setup.bat` for automated setup
- Follow on-screen instructions

### Manual Database Setup
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create database: `innostart_db`
3. Import: `database/innostart_database.sql`

## 🐍 Python AI Features Setup

### Method 1: Automated Setup (Recommended)

```bash
# Run the automated setup script
python setup_python.py

# This will:
# - Check Python version
# - Install all dependencies
# - Verify installation
# - Optionally start the ML server
```

### Method 2: Using Requirements File

```bash
# Install from existing requirements.txt
pip install -r requirements.txt

# Or install individually
pip install pandas scikit-learn openai requests numpy flask
```

### Method 3: Manual Installation

```bash
# Install core dependencies
pip install pandas
pip install scikit-learn
pip install openai
pip install requests
pip install numpy

# Verify installation
python -c "import pandas, sklearn, openai, requests, numpy; print('All packages installed successfully!')"
```

### Starting Python ML Server

1. **Open Command Prompt/Terminal**:
   - Windows: Press `Win + R`, type `cmd`, press Enter
   - Or use PowerShell/VS Code terminal

2. **Navigate to project directory**:
```bash
cd C:\xampp\htdocs\innostart
# or wherever your project is located
```

3. **Start the ML server**:
```bash
# Start the server
python ml_models/musanze_api.py

# You should see output like:
# * Running on http://127.0.0.1:5000
# * Press CTRL+C to quit
```

4. **Keep server running**:
   - Keep the terminal window open
   - The server must stay running for AI features to work
   - Press `Ctrl+C` to stop the server

### Python Setup Verification

```bash
# Check Python version (should be 3.8+)
python --version

# Check if packages are installed
python -c "import pandas; print('Pandas version:', pandas.__version__)"
python -c "import sklearn; print('Scikit-learn version:', sklearn.__version__)"
python -c "import openai; print('OpenAI version:', openai.__version__)"
```

### Troubleshooting Python Setup

| Issue | Solution |
|-------|----------|
| `python` not found | Install Python from python.org or use `python3` |
| Permission denied | Run as Administrator or use `pip install --user` |
| Package not found | Update pip: `python -m pip install --upgrade pip` |
| Port 5000 in use | Change port in `ml_models/musanze_api.py` |
| Import errors | Reinstall packages: `pip uninstall package_name && pip install package_name` |

## ✅ Verification

After setup, you should see:
- ✅ Database tables created
- ✅ Admin user available
- ✅ Login page working
- ✅ Dashboard accessible

## 🆘 Troubleshooting

| Issue | Solution |
|-------|----------|
| Database error | Check MySQL is running in XAMPP |
| Login failed | Use default credentials or create new account |
| AI not working | Install Python dependencies and start ML server |
| Port conflicts | Change ports in XAMPP settings |

## 📞 Support

- Read full README.md for detailed instructions
- Check setup_database.php for database status
- Ensure all services are running in XAMPP

---

**Ready to start your entrepreneurial journey! 🚀**
