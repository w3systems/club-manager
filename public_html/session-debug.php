<?php
session_start();

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/bootstrap.php';

use App\Models\Admin;
use App\Core\Auth;

echo "<h1>Session Debug Test</h1>";

// Test 1: Manual session setting
if (isset($_POST['set_session'])) {
    $admin = Admin::find(1); // Get your admin
    if ($admin) {
        $_SESSION['admin_id'] = (int) $admin->id;
        $_SESSION['user_type'] = 'admin';
        echo "<p style='color: green;'>Session set manually with admin ID: " . $admin->id . "</p>";
    }
}

// Test 2: Clear session
if (isset($_POST['clear_session'])) {
    $_SESSION = [];
    echo "<p style='color: red;'>Session cleared</p>";
}

// Display current session
echo "<h2>Current Session:</h2>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

// Test Auth class
echo "<h2>Auth Class Test:</h2>";
$auth = new Auth();
echo "<pre>";
echo "Auth Check: " . ($auth->check() ? 'TRUE' : 'FALSE') . "\n";
echo "Is Admin: " . ($auth->isAdmin() ? 'TRUE' : 'FALSE') . "\n";
echo "User: " . ($auth->user() ? $auth->user()->getFullName() : 'NULL') . "\n";
echo "</pre>";

// Test admin retrieval
echo "<h2>Admin Model Test:</h2>";
$testAdmin = Admin::find(1);
if ($testAdmin) {
    echo "<pre>";
    echo "Admin ID: " . $testAdmin->id . " (type: " . gettype($testAdmin->id) . ")\n";
    echo "Admin Name: " . $testAdmin->getFullName() . "\n";
    echo "Admin Array: " . print_r($testAdmin->toArray(), true);
    echo "</pre>";
} else {
    echo "<p style='color: red;'>Could not find admin with ID 1</p>";
}

?>

<form method="post">
    <button type="submit" name="set_session">Set Session Manually</button>
    <button type="submit" name="clear_session">Clear Session</button>
</form>