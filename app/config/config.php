<?php
// app/config/config.php

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// Database configuration
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_PORT', $_ENV['DB_PORT'] ?? '3306');
define('DB_NAME', $_ENV['DB_DATABASE'] ?? 'perruftn_club');
define('DB_USER', $_ENV['DB_USERNAME'] ?? 'perruftn_clubu');
define('DB_PASS', $_ENV['DB_PASSWORD'] ?? 'MPCEW9MradnuXCaG');

// Stripe configuration
define('STRIPE_SECRET_KEY', $_ENV['STRIPE_SECRET_KEY'] ?? '');
define('STRIPE_PUBLISHABLE_KEY', $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? '');
define('STRIPE_WEBHOOK_SECRET', $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '');

// Microsoft Graph API configuration
define('MSGRAPH_TENANT_ID', $_ENV['MSGRAPH_TENANT_ID'] ?? '');
define('MSGRAPH_CLIENT_ID', $_ENV['MSGRAPH_CLIENT_ID'] ?? '');
define('MSGRAPH_CLIENT_SECRET', $_ENV['MSGRAPH_CLIENT_SECRET'] ?? '');
define('MSGRAPH_REDIRECT_URI', $_ENV['MSGRAPH_REDIRECT_URI'] ?? '');
define('MSGRAPH_MAIL_USER_PRINCIPAL_NAME', $_ENV['MSGRAPH_MAIL_USER_PRINCIPAL_NAME'] ?? ''); // Email address to send from

// Application URL
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost');

// Paths
define('ROOT_PATH', dirname(__DIR__, 2));
define('APP_PATH', ROOT_PATH . '/app');
define('VIEW_PATH', APP_PATH . '/views');

//echo ROOT_PATH; die();
//echo APP_PATH; die();

// Default admin email (for initial setup if needed)
// This email should correspond to the admin user inserted in the database
define('DEFAULT_ADMIN_EMAIL', 'admin@example.com');
