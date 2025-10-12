<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InnoStart Database Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .setup-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            margin: 50px auto;
            max-width: 800px;
            overflow: hidden;
        }
        .setup-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .setup-body {
            padding: 40px;
        }
        .step {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            background: #f8f9fa;
        }
        .step.completed {
            background: #d4edda;
            border-color: #c3e6cb;
        }
        .step.error {
            background: #f8d7da;
            border-color: #f5c6cb;
        }
        .log-output {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 20px;
            border-radius: 10px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            max-height: 400px;
            overflow-y: auto;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="setup-container">
            <div class="setup-header">
                <h1><i class="fas fa-database me-2"></i>InnoStart Database Setup</h1>
                <p class="mb-0">Set up your database for the InnoStart platform</p>
            </div>
            
            <div class="setup-body">
                <div class="row">
                    <div class="col-md-6">
                        <h3>Setup Steps</h3>
                        
                        <div class="step" id="step1">
                            <h5><i class="fas fa-check-circle me-2"></i>Step 1: Check Database Connection</h5>
                            <p>Verify that the database connection is working properly.</p>
                            <button class="btn btn-primary" onclick="checkConnection()">Check Connection</button>
                        </div>
                        
                        <div class="step" id="step2">
                            <h5><i class="fas fa-table me-2"></i>Step 2: Create Database Tables</h5>
                            <p>Create all necessary database tables and indexes.</p>
                            <button class="btn btn-primary" onclick="createTables()" disabled>Create Tables</button>
                        </div>
                        
                        <div class="step" id="step3">
                            <h5><i class="fas fa-seedling me-2"></i>Step 3: Insert Default Data</h5>
                            <p>Insert default admin user, templates, and resources.</p>
                            <button class="btn btn-primary" onclick="insertDefaultData()" disabled>Insert Default Data</button>
                        </div>
                        
                        <div class="step" id="step4">
                            <h5><i class="fas fa-check-double me-2"></i>Step 4: Verify Setup</h5>
                            <p>Verify that all tables and data are created correctly.</p>
                            <button class="btn btn-primary" onclick="verifySetup()" disabled>Verify Setup</button>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h3>Setup Log</h3>
                        <div class="log-output" id="logOutput">Ready to start database setup...\n</div>
                        
                        <div class="mt-3">
                            <button class="btn btn-success" onclick="runFullSetup()" id="fullSetupBtn">
                                <i class="fas fa-play me-2"></i>Run Full Setup
                            </button>
                            <button class="btn btn-secondary" onclick="clearLog()">
                                <i class="fas fa-trash me-2"></i>Clear Log
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle me-2"></i>Database Configuration</h5>
                            <p>Make sure your database configuration in <code>config/database.php</code> is correct:</p>
                            <ul>
                                <li><strong>Host:</strong> localhost (or your database server)</li>
                                <li><strong>Database:</strong> innostart_db</li>
                                <li><strong>Username:</strong> root (or your database username)</li>
                                <li><strong>Password:</strong> (your database password)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script>
        let setupInProgress = false;
        
        function log(message) {
            const logOutput = document.getElementById('logOutput');
            const timestamp = new Date().toLocaleTimeString();
            logOutput.textContent += `[${timestamp}] ${message}\n`;
            logOutput.scrollTop = logOutput.scrollHeight;
        }
        
        function clearLog() {
            document.getElementById('logOutput').textContent = 'Log cleared...\n';
        }
        
        function updateStep(stepId, status) {
            const step = document.getElementById(stepId);
            step.className = `step ${status}`;
            
            if (status === 'completed') {
                const button = step.querySelector('button');
                if (button) {
                    button.disabled = true;
                    button.innerHTML = '<i class="fas fa-check me-2"></i>Completed';
                    button.className = 'btn btn-success';
                }
            } else if (status === 'error') {
                const button = step.querySelector('button');
                if (button) {
                    button.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Error';
                    button.className = 'btn btn-danger';
                }
            }
        }
        
        function enableNextStep(currentStep) {
            const nextStep = currentStep + 1;
            if (nextStep <= 4) {
                const nextButton = document.querySelector(`#step${nextStep} button`);
                if (nextButton) {
                    nextButton.disabled = false;
                }
            }
        }
        
        async function checkConnection() {
            if (setupInProgress) return;
            setupInProgress = true;
            
            log('Checking database connection...');
            
            try {
                const response = await fetch('api/setup.php?action=check_connection');
                const result = await response.json();
                
                if (result.success) {
                    log('✅ Database connection successful!');
                    log(`Database: ${result.data.database}`);
                    log(`Version: ${result.data.version}`);
                    updateStep('step1', 'completed');
                    enableNextStep(1);
                } else {
                    log('❌ Database connection failed!');
                    log(`Error: ${result.message}`);
                    updateStep('step1', 'error');
                }
            } catch (error) {
                log('❌ Connection check failed!');
                log(`Error: ${error.message}`);
                updateStep('step1', 'error');
            }
            
            setupInProgress = false;
        }
        
        async function createTables() {
            if (setupInProgress) return;
            setupInProgress = true;
            
            log('Creating database tables...');
            
            try {
                const response = await fetch('api/setup.php?action=create_tables');
                const result = await response.json();
                
                if (result.success) {
                    log('✅ Database tables created successfully!');
                    log(`Created ${result.data.tables_created} tables`);
                    updateStep('step2', 'completed');
                    enableNextStep(2);
                } else {
                    log('❌ Failed to create tables!');
                    log(`Error: ${result.message}`);
                    updateStep('step2', 'error');
                }
            } catch (error) {
                log('❌ Table creation failed!');
                log(`Error: ${error.message}`);
                updateStep('step2', 'error');
            }
            
            setupInProgress = false;
        }
        
        async function insertDefaultData() {
            if (setupInProgress) return;
            setupInProgress = true;
            
            log('Inserting default data...');
            
            try {
                const response = await fetch('api/setup.php?action=insert_default_data');
                const result = await response.json();
                
                if (result.success) {
                    log('✅ Default data inserted successfully!');
                    log(`Admin users: ${result.data.admin_users}`);
                    log(`Templates: ${result.data.templates}`);
                    log(`Resources: ${result.data.resources}`);
                    updateStep('step3', 'completed');
                    enableNextStep(3);
                } else {
                    log('❌ Failed to insert default data!');
                    log(`Error: ${result.message}`);
                    updateStep('step3', 'error');
                }
            } catch (error) {
                log('❌ Data insertion failed!');
                log(`Error: ${error.message}`);
                updateStep('step3', 'error');
            }
            
            setupInProgress = false;
        }
        
        async function verifySetup() {
            if (setupInProgress) return;
            setupInProgress = true;
            
            log('Verifying database setup...');
            
            try {
                const response = await fetch('api/setup.php?action=verify_setup');
                const result = await response.json();
                
                if (result.success) {
                    log('✅ Database setup verified successfully!');
                    log(`Tables: ${result.data.tables_found}/${result.data.tables_expected}`);
                    log(`Admin users: ${result.data.admin_users}`);
                    log(`Templates: ${result.data.templates}`);
                    log(`Resources: ${result.data.resources}`);
                    updateStep('step4', 'completed');
                    
                    // Show success message
                    setTimeout(() => {
                        alert('🎉 Database setup completed successfully!\n\nYou can now use the InnoStart platform with full database functionality.');
                    }, 1000);
                } else {
                    log('❌ Setup verification failed!');
                    log(`Error: ${result.message}`);
                    updateStep('step4', 'error');
                }
            } catch (error) {
                log('❌ Verification failed!');
                log(`Error: ${error.message}`);
                updateStep('step4', 'error');
            }
            
            setupInProgress = false;
        }
        
        async function runFullSetup() {
            if (setupInProgress) return;
            
            const fullSetupBtn = document.getElementById('fullSetupBtn');
            fullSetupBtn.disabled = true;
            fullSetupBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Running Setup...';
            
            log('🚀 Starting full database setup...');
            
            await checkConnection();
            if (document.getElementById('step1').classList.contains('completed')) {
                await createTables();
                if (document.getElementById('step2').classList.contains('completed')) {
                    await insertDefaultData();
                    if (document.getElementById('step3').classList.contains('completed')) {
                        await verifySetup();
                    }
                }
            }
            
            fullSetupBtn.disabled = false;
            fullSetupBtn.innerHTML = '<i class="fas fa-play me-2"></i>Run Full Setup';
            
            log('🏁 Full setup process completed!');
        }
    </script>
</body>
</html>
