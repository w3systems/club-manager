<?php
/**
 * Application Bootstrap
 * Sets up autoloading, error handling, and core services
 */

// Define application constants
define('APP_ROOT', dirname(__DIR__));
define('APP_PATH', APP_ROOT . '/app');
define('PUBLIC_PATH', APP_ROOT . '/public_html');
define('STORAGE_PATH', APP_ROOT . '/storage');
define('VIEW_PATH', APP_PATH . '/Views');

// Set default timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');

// PSR-4 Autoloader for app namespace
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = APP_PATH . '/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Load helper functions
require_once APP_PATH . '/Helpers/functions.php';
require_once APP_PATH . '/Helpers/constants.php';

// Set error handler
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    
    $error = new ErrorException($message, 0, $severity, $file, $line);
    
    if ($_ENV['APP_ENV'] === 'production') {
        error_log($error->getMessage() . ' in ' . $error->getFile() . ':' . $error->getLine());
        return true;
    }
    
    throw $error;
});

// Set exception handler
set_exception_handler(function($exception) {
    error_log("Uncaught exception: " . $exception->getMessage());
    
    if ($_ENV['APP_ENV'] === 'production') {
        http_response_code(500);
        include VIEW_PATH . '/errors/500.php';
    } else {
        echo "<pre>" . $exception . "</pre>";
    }
});

// Initialize services container
App\Core\Container::getInstance();