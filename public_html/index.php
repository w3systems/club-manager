<?php
/*error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load Composer autoloader (though it's not being fully utilized for your App namespace)
require_once __DIR__ . '/../vendor/autoload.php';

// --- Load Core Files ---
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/core/Router.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Auth.php';
require_once __DIR__ . '/../app/helpers/functions.php';

// --- Load Model Files (ADD THIS ENTIRE SECTION) ---
require_once __DIR__ . '/../app/models/Admin.php';
require_once __DIR__ . '/../app/models/Member.php';
require_once __DIR__ . '/../app/models/Setting.php';
require_once __DIR__ . '/../app/models/ClassModel.php';
require_once __DIR__ . '/../app/models/ClassBooking.php';
// Add any other models you create here...

// --- Load Controller Files ---
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/ErrorController.php';
// Add other controllers as you create them...

use App\Core\Router;

session_start();

$router = new Router();
$router->dispatch();
*/

error_reporting(E_ALL);
ini_set('display_errors', 1);
/*// public/index.php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Controller.php';

require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/ErrorController.php';

require_once __DIR__ . '/../app/core/Router.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Auth.php';
require_once __DIR__ . '/../app/helpers/functions.php';

require_once __DIR__ . '/../app/models/Setting.php';

use App\Core\Router;*/


require_once __DIR__ . '/../vendor/autoload.php';

// --- 1. CORE FILES & HELPERS ---
require_once __DIR__ . '/../app/config/config.php';

require_once __DIR__ . '/../app/core/exceptions/DuplicateEntryException.php';

require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Auth.php'; // Auth uses Models, so it must come before them
require_once __DIR__ . '/../app/helpers/functions.php';

require_once __DIR__ . '/../app/models/BaseModel.php';


// --- 2. MODELS ---
require_once __DIR__ . '/../app/models/Admin.php';
require_once __DIR__ . '/../app/models/AdminRole.php';
require_once __DIR__ . '/../app/models/Member.php';
require_once __DIR__ . '/../app/models/Setting.php';
require_once __DIR__ . '/../app/models/ClassModel.php';
require_once __DIR__ . '/../app/models/ClassBooking.php';



require_once __DIR__ . '/../app/models/ClassSubscription.php';
require_once __DIR__ . '/../app/models/Message.php';
require_once __DIR__ . '/../app/models/Notification.php';
require_once __DIR__ . '/../app/models/NotificationSetting.php';
require_once __DIR__ . '/../app/models/Payment.php';
require_once __DIR__ . '/../app/models/PaymentMethod.php';
require_once __DIR__ . '/../app/models/Permission.php';
require_once __DIR__ . '/../app/models/Role.php';
require_once __DIR__ . '/../app/models/RolePermission.php';
require_once __DIR__ . '/../app/models/Subscription.php';






// --- 3. CONTROLLERS ---
require_once __DIR__ . '/../app/controllers/AdminController.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/ErrorController.php';
// ... all other controllers

// --- 4. ROUTER ---
// The Router uses Controllers, so it should come after them.
require_once __DIR__ . '/../app/core/Router.php';

use App\Core\Router;

use App\Core\Database;

session_start();

// Database connection setup
Database::getInstance(); // Initialize the database connection

$router = new Router();

// Define routes
// Admin Routes
$router->get('/admin', 'AdminController@dashboard', ['auth' => 'admin']);
$router->get('/admin/members', 'AdminController@members', ['auth' => 'admin']);
$router->get('/admin/members/(\d+)', 'AdminController@memberProfile', ['auth' => 'admin']);
$router->post('/admin/members/(\d+)/update', 'AdminController@updateMember', ['auth' => 'admin']);
$router->post('/admin/members/(\d+)/subscription/add', 'AdminController@addMemberSubscription', ['auth' => 'admin']);
$router->post('/admin/members/(\d+)/subscription/update', 'AdminController@updateMemberSubscription', ['auth' => 'admin']);
$router->post('/admin/members/(\d+)/subscription/cancel', 'AdminController@cancelMemberSubscription', ['auth' => 'admin']);
$router->get('/admin/subscriptions', 'AdminController@subscriptions', ['auth' => 'admin']);
$router->get('/admin/classes', 'AdminController@classes', ['auth' => 'admin']);
$router->get('/admin/classes/calendar', 'AdminController@classCalendar', ['auth' => 'admin']);
$router->get('/admin/payments', 'AdminController@payments', ['auth' => 'admin']);
$router->get('/admin/settings', 'AdminController@settings', ['auth' => 'admin']);
$router->post('/admin/settings/save', 'AdminController@saveSettings', ['auth' => 'admin']);
$router->get('/admin/users', 'AdminController@users', ['auth' => 'admin']); // For granular roles
$router->post('/admin/users/create', 'AdminController@createUser', ['auth' => 'admin']);

$router->get('/admin/users/edit/{id}', 'AdminController@editUser', ['auth' => 'admin']); // For granular roles
$router->post('/admin/users/update/{id}', 'AdminController@updateUser', ['auth' => 'admin']);



$router->post('/admin/messages', 'AdminController@adminMessages', ['auth' => 'admin']); // For admin replies


$router->get('/admin/roles', 'AdminController@roles', ['auth' => 'admin']); // For granular roles
$router->post('/admin/roles/create', 'AdminController@createRole', ['auth' => 'admin']); // For admin replies
$router->post('/admin/permissions/create', 'AdminController@createPermission', ['auth' => 'admin']); // For admin replies
$router->post('/admin/roles/{id}/create', 'AdminController@updateRolePermissions', ['auth' => 'admin']); // For admin replies




// Member Routes
$router->get('/', 'MemberController@dashboard', ['auth' => 'member']);
$router->get('/profile', 'MemberController@profile', ['auth' => 'member']);
$router->post('/profile/update', 'MemberController@updateProfile', ['auth' => 'member']);
$router->get('/subscriptions', 'MemberController@subscriptions', ['auth' => 'member']);
$router->get('/subscriptions/new', 'MemberController@newSubscription', ['auth' => 'member']);
$router->post('/subscriptions/signup', 'MemberController@signupSubscription', ['auth' => 'member']);
$router->get('/classes', 'MemberController@classes', ['auth' => 'member']);
$router->get('/classes/book/(\d+)', 'MemberController@bookClass', ['auth' => 'member']); // Manual booking
$router->get('/payments', 'MemberController@payments', ['auth' => 'member']);
$router->get('/payment-methods', 'MemberController@paymentMethods', ['auth' => 'member']);
$router->post('/payment-methods/add', 'MemberController@addPaymentMethod', ['auth' => 'member']);
$router->post('/payment-methods/set-default', 'MemberController@setDefaultPaymentMethod', ['auth' => 'member']);
$router->post('/payment-methods/delete', 'MemberController@deletePaymentMethod', ['auth' => 'member']);
$router->get('/notifications', 'MemberController@notifications', ['auth' => 'member']);
$router->post('/notifications/settings/update', 'MemberController@updateNotificationSettings', ['auth' => 'member']);
$router->post('/messages', 'MemberController@memberMessages', ['auth' => 'member']); // For member messages to admin

// Public Routes (Login, Register, Free Trial)
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register');
$router->get('/logout', 'AuthController@logout');
$router->get('/free-trial', 'MemberController@showFreeTrialClasses');
$router->post('/free-trial/book', 'MemberController@bookFreeTrial');

// Stripe Webhook
$router->post('/stripe/webhook', 'StripeController@webhook');

// Microsoft Graph API Callback
$router->get('/auth/microsoft/callback', 'MicrosoftGraphController@callback');
$router->get('/auth/microsoft/setup', 'MicrosoftGraphController@setupAuth', ['auth' => 'admin']); // Admin setup

// Route not found
$router->setNotFound('ErrorController@notFound');

// Dispatch the request
$router->dispatch();

// Clear old input and errors after dispatch
if (isset($_SESSION['old_input'])) {
    unset($_SESSION['old_input']);
}
if (isset($_SESSION['errors'])) {
    unset($_SESSION['errors']);
}
if (isset($_SESSION['success_message'])) {
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    unset($_SESSION['error_message']);
}