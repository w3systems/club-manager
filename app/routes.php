<?php
/**
 * Application Routes
 * Define all URL routes for the application
 */

// Authentication Routes (Public)
$router->get('/login', 'Auth\\LoginController@showMemberLogin');
$router->post('/login', 'Auth\\LoginController@memberLogin');
$router->get('/register', 'Auth\\RegisterController@showRegister');
$router->post('/register', 'Auth\\RegisterController@register');
$router->get('/logout', 'Auth\\LogoutController@logout');

// Admin Authentication Routes
$router->get('/admin/login', 'Auth\\LoginController@showAdminLogin');
$router->post('/admin/login', 'Auth\\LoginController@adminLogin');
$router->get('/admin/logout', 'Auth\\LogoutController@adminLogout');

// Member Routes (Protected)
$router->get('/', 'Member\\DashboardController@index', ['middleware' => ['auth', 'member']]);
$router->get('/profile', 'Member\\ProfileController@show', ['middleware' => ['auth', 'member']]);
$router->post('/profile', 'Member\\ProfileController@update', ['middleware' => ['auth', 'member']]);

// Member Subscriptions
$router->get('/subscriptions', 'Member\\SubscriptionController@index', ['middleware' => ['auth', 'member']]);
$router->get('/subscriptions/new', 'Member\\SubscriptionController@create', ['middleware' => ['auth', 'member']]);
$router->post('/subscriptions', 'Member\\SubscriptionController@store', ['middleware' => ['auth', 'member']]);
$router->post('/subscriptions/(\d+)/cancel', 'Member\\SubscriptionController@cancel', ['middleware' => ['auth', 'member']]);

// Member Classes
$router->get('/classes', 'Member\\ClassController@index', ['middleware' => ['auth', 'member']]);
$router->post('/classes/(\d+)/book', 'Member\\ClassController@book', ['middleware' => ['auth', 'member']]);
$router->post('/classes/(\d+)/cancel', 'Member\\ClassController@cancelBooking', ['middleware' => ['auth', 'member']]);

// Member Payments
$router->get('/payments', 'Member\\PaymentController@index', ['middleware' => ['auth', 'member']]);
$router->get('/payment-methods', 'Member\\PaymentController@paymentMethods', ['middleware' => ['auth', 'member']]);
$router->post('/payment-methods', 'Member\\PaymentController@addPaymentMethod', ['middleware' => ['auth', 'member']]);
$router->post('/payment-methods/(\d+)/default', 'Member\\PaymentController@setDefault', ['middleware' => ['auth', 'member']]);
$router->delete('/payment-methods/(\d+)', 'Member\\PaymentController@deletePaymentMethod', ['middleware' => ['auth', 'member']]);

// Member Notifications
$router->get('/notifications', 'Member\\NotificationController@index', ['middleware' => ['auth', 'member']]);
$router->post('/notifications/(\d+)/read', 'Member\\NotificationController@markAsRead', ['middleware' => ['auth', 'member']]);
$router->post('/notifications/settings', 'Member\\NotificationController@updateSettings', ['middleware' => ['auth', 'member']]);

// Member Messages
$router->get('/messages', 'Member\\MessageController@index', ['middleware' => ['auth', 'member']]);
$router->post('/messages', 'Member\\MessageController@send', ['middleware' => ['auth', 'member']]);

// Free Trial (Public)
$router->get('/free-trial', 'Member\\ClassController@freeTrial');
$router->post('/free-trial/book', 'Member\\ClassController@bookFreeTrial');

// Admin Dashboard (Protected)
$router->get('/admin', 'Admin\\DashboardController@index', ['middleware' => ['auth', 'admin'], 'permission' => 'view_dashboard']);

// Admin Members Management
$router->get('/admin/members', 'Admin\\MemberController@index', ['middleware' => ['auth', 'admin'], 'permission' => 'view_members']);
$router->get('/admin/members/create', 'Admin\\MemberController@create', ['middleware' => ['auth', 'admin'], 'permission' => 'edit_members']);
$router->post('/admin/members', 'Admin\\MemberController@store', ['middleware' => ['auth', 'admin'], 'permission' => 'edit_members']);
$router->get('/admin/members/(\d+)', 'Admin\\MemberController@show', ['middleware' => ['auth', 'admin'], 'permission' => 'view_members']);
$router->get('/admin/members/(\d+)/edit', 'Admin\\MemberController@edit', ['middleware' => ['auth', 'admin'], 'permission' => 'edit_members']);
$router->post('/admin/members/(\d+)', 'Admin\\MemberController@update', ['middleware' => ['auth', 'admin'], 'permission' => 'edit_members']);
$router->delete('/admin/members/(\d+)', 'Admin\\MemberController@delete', ['middleware' => ['auth', 'admin'], 'permission' => 'edit_members']);

// Admin Member Subscriptions
$router->post('/admin/members/(\d+)/subscriptions', 'Admin\\MemberController@addSubscription', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_subscriptions']);
$router->post('/admin/members/(\d+)/subscriptions/(\d+)/update', 'Admin\\MemberController@updateSubscription', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_subscriptions']);
$router->post('/admin/members/(\d+)/subscriptions/(\d+)/cancel', 'Admin\\MemberController@cancelSubscription', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_subscriptions']);
$router->post('/admin/members/(\d+)/subscriptions/(\d+)/suspend', 'Admin\\MemberController@suspendSubscription', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_subscriptions']);
$router->post('/admin/members/(\d+)/subscriptions/(\d+)/resume', 'Admin\\MemberController@resumeSubscription', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_subscriptions']);

// Admin Subscriptions Management
$router->get('/admin/subscriptions', 'Admin\SubscriptionController@index', ['middleware' => ['auth', 'admin'], 'permission' => 'view_subscriptions']);
$router->get('/admin/subscriptions/create', 'Admin\SubscriptionController@create', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_subscriptions']);
$router->post('/admin/subscriptions', 'Admin\SubscriptionController@store', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_subscriptions']);
$router->get('/admin/subscriptions/(\d+)', 'Admin\\SubscriptionController@show', ['middleware' => ['auth', 'admin'], 'permission' => 'view_subscriptions']);

$router->get('/admin/subscriptions/(\d+)/edit', 'Admin\SubscriptionController@edit', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_subscriptions']);
$router->post('/admin/subscriptions/(\d+)', 'Admin\SubscriptionController@update', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_subscriptions']);
$router->post('/admin/subscriptions/(\d+)/delete', 'Admin\SubscriptionController@delete', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_subscriptions']);

// Admin Classes Management
$router->get('/admin/classes', 'Admin\ClassController@index', ['middleware' => ['auth', 'admin'], 'permission' => 'view_classes']);
$router->get('/admin/classes/calendar', 'Admin\\ClassController@calendar', ['middleware' => ['auth', 'admin'], 'permission' => 'view_classes']);
$router->get('/admin/classes/create', 'Admin\ClassController@create', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_classes']);
$router->post('/admin/classes', 'Admin\ClassController@store', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_classes']);
$router->get('/admin/classes/(\d+)', 'Admin\\ClassController@show', ['middleware' => ['auth', 'admin'], 'permission' => 'view_classes']);
$router->get('/admin/classes/(\d+)/edit', 'Admin\ClassController@edit', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_classes']);
$router->post('/admin/classes/(\d+)', 'Admin\ClassController@update', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_classes']);
$router->post('/admin/classes/(\d+)/delete', 'Admin\ClassController@delete', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_classes']);

// Admin Class Instances Management
$router->get('/admin/classes/(\d+)/instances', 'Admin\ClassInstanceController@index', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_classes']);
$router->post('/admin/classes/(\d+)/instances/(\d+)/delete', 'Admin\ClassInstanceController@delete', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_classes']);

// Admin Class Bookings
$router->get('/admin/classes/(\d+)/bookings', 'Admin\\ClassController@bookings', ['middleware' => ['auth', 'admin'], 'permission' => 'view_bookings']);
$router->post('/admin/classes/(\d+)/book', 'Admin\\ClassController@bookMember', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_bookings']);
$router->post('/admin/bookings/(\d+)/cancel', 'Admin\\ClassController@cancelBooking', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_bookings']);
$router->post('/admin/bookings/(\d+)/attendance', 'Admin\\ClassController@markAttendance', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_bookings']);

// Admin Payments Management
$router->get('/admin/payments', 'Admin\\PaymentController@index', ['middleware' => ['auth', 'admin'], 'permission' => 'view_payments']);
$router->get('/admin/payments/(\d+)', 'Admin\\PaymentController@show', ['middleware' => ['auth', 'admin'], 'permission' => 'view_payments']);
$router->post('/admin/payments/(\d+)/refund', 'Admin\\PaymentController@refund', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_payments']);
$router->post('/admin/payments/manual', 'Admin\\PaymentController@recordPayment', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_payments']);

// Admin Users & Roles Management
$router->get('/admin/users', 'Admin\\UserController@index', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_users']);
$router->get('/admin/users/create', 'Admin\\UserController@create', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_users']);
$router->post('/admin/users', 'Admin\\UserController@store', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_users']);
$router->get('/admin/users/(\d+)', 'Admin\\UserController@show', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_users']);
$router->get('/admin/users/(\d+)/edit', 'Admin\\UserController@edit', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_users']);
$router->post('/admin/users/(\d+)', 'Admin\\UserController@update', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_users']);
$router->delete('/admin/users/(\d+)', 'Admin\\UserController@delete', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_users']);

/*// Admin Roles Management
$router->get('/admin/roles', 'Admin\\RoleController@index', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_roles']);
$router->get('/admin/roles/create', 'Admin\\RoleController@create', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_roles']);
$router->post('/admin/roles', 'Admin\\RoleController@store', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_roles']);
$router->get('/admin/roles/(\d+)', 'Admin\\RoleController@show', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_roles']);
$router->get('/admin/roles/(\d+)/edit', 'Admin\\RoleController@edit', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_roles']);
$router->post('/admin/roles/(\d+)', 'Admin\\RoleController@update', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_roles']);
$router->delete('/admin/roles/(\d+)', 'Admin\\RoleController@delete', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_roles']);

// Admin Permissions Management
$router->get('/admin/permissions', 'Admin\\PermissionController@index', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_roles']);
$router->post('/admin/permissions', 'Admin\\PermissionController@store', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_roles']);
*/

// Main unified management page
$router->get('/admin/roles', 'Admin\\RoleController@index', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_roles']);

// Role operations (simplified since we're doing inline editing)
$router->post('/admin/roles/store', 'Admin\\RoleController@store', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_roles']);
$router->post('/admin/roles/(\d+)/update', 'Admin\\RoleController@update', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_roles']);
$router->post('/admin/roles/(\d+)/delete', 'Admin\\RoleController@delete', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_roles']);

// Permission operations (inline editing)
$router->post('/admin/permissions/store', 'Admin\PermissionController@store', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_roles']);
$router->post('/admin/permissions/(\d+)/update', 'Admin\PermissionController@update', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_roles']);
$router->post('/admin/permissions/(\d+)/delete', 'Admin\PermissionController@delete', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_roles']);

// Bulk permission assignment
$router->post('/admin/roles/update-permissions', 'Admin\\RoleController@updatePermissions', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_roles']);

// Admin Messages
$router->get('/admin/messages', 'Admin\\MessageController@index', ['middleware' => ['auth', 'admin'], 'permission' => 'view_member_messages']);
$router->get('/admin/messages/(\d+)', 'Admin\\MessageController@show', ['middleware' => ['auth', 'admin'], 'permission' => 'view_member_messages']);
$router->post('/admin/messages/(\d+)/reply', 'Admin\\MessageController@reply', ['middleware' => ['auth', 'admin'], 'permission' => 'send_member_messages']);

// Admin Settings
$router->get('/admin/settings', 'Admin\SettingController@index', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_settings']);
$router->post('/admin/settings', 'Admin\SettingController@update', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_settings']);

// Admin Profile
$router->get('/admin/profile', 'Admin\ProfileController@show', ['middleware' => ['auth', 'admin']]);
$router->post('/admin/profile', 'Admin\ProfileController@update', ['middleware' => ['auth', 'admin']]);

// Admin Microsoft Graph Setup
$router->get('/admin/settings/microsoft', 'Admin\\SettingController@microsoftSetup', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_settings']);
$router->post('/admin/settings/microsoft', 'Admin\\SettingController@saveMicrosoftSettings', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_settings']);

// API Routes
$router->post('/api/stripe/webhook', 'Api\\StripeWebhookController@handle');
$router->get('/api/microsoft/callback', 'Api\\MicrosoftGraphController@callback');
$router->get('/api/microsoft/auth', 'Api\\MicrosoftGraphController@authorize', ['middleware' => ['auth', 'admin'], 'permission' => 'manage_settings']);

// Error Routes
$router->get('/404', 'ErrorController@notFound');
$router->get('/500', 'ErrorController@serverError');