<?php
// UPDATED: public/admin-test.php - Fixed to load .env properly

// Load environment variables first
require_once __DIR__ . '/../vendor/autoload.php';

// Load .env file BEFORE bootstrap
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

require_once __DIR__ . '/../app/bootstrap.php';

use App\Models\Admin;
use App\Config\Database;

echo "<h1>Admin Model Debug Test</h1>";

// Show environment variables
echo "<h2>Environment Check:</h2>";
echo "<pre>";
echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? 'NOT SET') . "\n";
echo "DB_DATABASE: " . ($_ENV['DB_DATABASE'] ?? 'NOT SET') . "\n";
echo "DB_USERNAME: " . ($_ENV['DB_USERNAME'] ?? 'NOT SET') . "\n";
echo "DB_PASSWORD: " . (isset($_ENV['DB_PASSWORD']) ? '[HIDDEN]' : 'NOT SET') . "\n";
echo "</pre>";

// Test database connection
try {
    $db = Database::getConnection();
    echo "<p style='color: green;'>✅ Database connected</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
    echo "<p>Check your .env file configuration!</p>";
    exit;
}

// Test direct database query
echo "<h2>Direct Database Query:</h2>";
try {
    $stmt = $db->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute(['mit@w3systems.net']);
    $dbResult = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($dbResult) {
        echo "<pre>Database result: " . print_r($dbResult, true) . "</pre>";
        echo "<p>Direct DB admin ID: " . $dbResult['id'] . " (type: " . gettype($dbResult['id']) . ")</p>";
    } else {
        echo "<p style='color: red;'>No admin found in database</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Database query error: " . $e->getMessage() . "</p>";
}

// Test Admin::findByEmail
echo "<h2>Admin::findByEmail Test:</h2>";
try {
    $admin = Admin::findByEmail('mit@w3systems.net');
    
    if ($admin) {
        echo "<p style='color: green;'>✅ Admin found via model</p>";
        
        // Debug the admin object
        echo "<h3>Admin Object Debug:</h3>";
        echo "<pre>";
        echo "Class: " . get_class($admin) . "\n";
        echo "ID via ->id: " . var_export($admin->id, true) . " (type: " . gettype($admin->id) . ")\n";
        
        if (method_exists($admin, 'getAttribute')) {
            echo "ID via getAttribute: " . var_export($admin->getAttribute('id'), true) . "\n";
        }
        
        // Check the raw attributes
        if (property_exists($admin, 'attributes')) {
            $reflection = new ReflectionClass($admin);
            $attributesProp = $reflection->getProperty('attributes');
            $attributesProp->setAccessible(true);
            $attributes = $attributesProp->getValue($admin);
            echo "Raw attributes: " . print_r($attributes, true) . "\n";
        }
        echo "</pre>";
        
    } else {
        echo "<p style='color: red;'>❌ Admin not found via model</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Admin model error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}