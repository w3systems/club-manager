<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;

echo "<h1>Authentication Debug</h1>";

echo "<h2>Session Info</h2>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session Data: " . print_r($_SESSION, true);
echo "</pre>";

echo "<h2>Auth Class Test</h2>";
$auth = new Auth();
echo "<pre>";
echo "Auth Check: " . ($auth->check() ? 'TRUE' : 'FALSE') . "\n";
echo "Is Admin: " . ($auth->isAdmin() ? 'TRUE' : 'FALSE') . "\n";
echo "User Type: " . ($auth->userType() ?: 'NULL') . "\n";
echo "User ID: " . ($auth->id() ?: 'NULL') . "\n";

if (method_exists($auth, 'debugAuth')) {
    echo "Debug Data: " . print_r($auth->debugAuth(), true);
}
echo "</pre>";

echo "<h2>Manual Session Test</h2>";
echo "<form method='post'>";
echo "<button type='submit' name='set_session'>Set Test Session</button>";
echo "<button type='submit' name='clear_session'>Clear Session</button>";
echo "</form>";

if (isset($_POST['set_session'])) {
    $_SESSION['admin_id'] = 1;
    $_SESSION['user_type'] = 'admin';
    echo "<p style='color: green;'>Test session data set!</p>";
    echo "<script>location.reload();</script>";
}

if (isset($_POST['clear_session'])) {
    session_destroy();
    echo "<p style='color: red;'>Session cleared!</p>";
    echo "<script>setTimeout(() => location.reload(), 1000);</script>";
}

