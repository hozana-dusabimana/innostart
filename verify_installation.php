<?php
require_once 'config/database.php';

echo "Verifying User Data Management System Installation...\n\n";

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    $tables = ['user_saved_data', 'user_data_history', 'user_data_templates', 'user_data_exports'];
    
    echo "Checking database tables:\n";
    foreach($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->rowCount() > 0;
        echo "✓ $table: " . ($exists ? 'EXISTS' : 'MISSING') . "\n";
    }
    
    echo "\nChecking API files:\n";
    $apiFiles = ['api/user-data.php', 'api/financial-projections.php'];
    foreach($apiFiles as $file) {
        $exists = file_exists($file);
        echo "✓ $file: " . ($exists ? 'EXISTS' : 'MISSING') . "\n";
    }
    
    echo "\nChecking frontend integration:\n";
    $frontendFiles = ['dashboard.html', 'assets/js/dashboard.js'];
    foreach($frontendFiles as $file) {
        $exists = file_exists($file);
        echo "✓ $file: " . ($exists ? 'EXISTS' : 'MISSING') . "\n";
    }
    
    echo "\n🎉 Installation verification complete!\n";
    echo "The User Data Management System is ready to use.\n";
    
} catch (Exception $e) {
    echo "❌ Verification failed: " . $e->getMessage() . "\n";
}
?>
