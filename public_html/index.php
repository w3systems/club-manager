<?php
// UPDATED public/index.php - Move session config BEFORE session_start()

/**
 * Club Management System
 * Application Entry Point
 */

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

// SESSION CONFIGURATION - MUST BE BEFORE session_start()
ini_set('session.gc_maxlifetime', 3600);
ini_set('session.cookie_lifetime', 3600);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');

// Create sessions directory if it doesn't exist
$sessionPath = __DIR__ . '/../storage/sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0755, true);
}
ini_set('session.save_path', $sessionPath);

// NOW start the session
session_start();

// Bootstrap the application
require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Application;
use App\Core\Router;
use App\Config\Database;

try {
    // Initialize database connection
    Database::initialize();
    
    // Create application instance
    $app = new Application();
    
    // Create router instance
    $router = new Router();
    
    // Define routes
    require_once __DIR__ . '/../app/routes.php';
    
    // Handle the request
    $router->dispatch();
    
} catch (Exception $e) {
    // Log the error
    error_log("Application Error: " . $e->getMessage());
    
    // Show error page in production
    if ($_ENV['APP_ENV'] === 'production') {
        http_response_code(500);
        include __DIR__ . '/../app/Views/errors/500.php';
    } else {
        throw $e;
    }
}