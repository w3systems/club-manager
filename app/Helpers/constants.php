<?php
/**
 * Application Constants
 * Define constant values used throughout the application
 */

// Subscription Types
define('SUBSCRIPTION_TYPE_FIXED_LENGTH', 'fixed_length');
define('SUBSCRIPTION_TYPE_RECURRING', 'recurring');
define('SUBSCRIPTION_TYPE_SESSION_BASED', 'session_based');

// Subscription Status
define('SUBSCRIPTION_STATUS_ACTIVE', 'active');
define('SUBSCRIPTION_STATUS_SUSPENDED', 'suspended');
define('SUBSCRIPTION_STATUS_CANCELLED', 'cancelled');
define('SUBSCRIPTION_STATUS_ENDED', 'ended');
define('SUBSCRIPTION_STATUS_TRIAL', 'trial');

// Class Types
define('CLASS_TYPE_SINGLE', 'single');
define('CLASS_TYPE_RECURRING_PARENT', 'recurring_parent');
define('CLASS_TYPE_RECURRING_INSTANCE', 'recurring_instance');

// Class Frequencies
define('CLASS_FREQUENCY_DAILY', 'daily');
define('CLASS_FREQUENCY_WEEKLY', 'weekly');
define('CLASS_FREQUENCY_FORTNIGHTLY', 'fortnightly');
define('CLASS_FREQUENCY_4_WEEKLY', '4_weekly');
define('CLASS_FREQUENCY_MONTHLY', 'monthly');

// Booking Status
define('BOOKING_STATUS_BOOKED', 'booked');
define('BOOKING_STATUS_CANCELLED', 'cancelled');
define('BOOKING_STATUS_ATTENDED', 'attended');
define('BOOKING_STATUS_NO_SHOW', 'no_show');

// Payment Status
define('PAYMENT_STATUS_PENDING', 'pending');
define('PAYMENT_STATUS_SUCCEEDED', 'succeeded');
define('PAYMENT_STATUS_FAILED', 'failed');
define('PAYMENT_STATUS_REFUNDED', 'refunded');

// Payment Gateways
define('PAYMENT_GATEWAY_STRIPE', 'stripe');
define('PAYMENT_GATEWAY_CASH', 'cash');
define('PAYMENT_GATEWAY_BANK_TRANSFER', 'bank_transfer');
define('PAYMENT_GATEWAY_MANUAL', 'manual');

// Term Units
define('TERM_UNIT_DAY', 'day');
define('TERM_UNIT_WEEK', 'week');
define('TERM_UNIT_MONTH', 'month');
define('TERM_UNIT_YEAR', 'year');
define('TERM_UNIT_SESSION', 'session');

// Message Types
define('MESSAGE_TYPE_MEMBER_TO_ADMIN', 'member_to_admin');
define('MESSAGE_TYPE_ADMIN_TO_MEMBER', 'admin_to_member');

// Notification Types
define('NOTIFICATION_TYPE_PAYMENT_RECEIVED', 'payment_received');
define('NOTIFICATION_TYPE_PAYMENT_FAILED', 'payment_failed');
define('NOTIFICATION_TYPE_SUBSCRIPTION_RENEWED', 'subscription_renewed');
define('NOTIFICATION_TYPE_SUBSCRIPTION_CANCELLED', 'subscription_cancelled');
define('NOTIFICATION_TYPE_SUBSCRIPTION_SUSPENDED', 'subscription_suspended');
define('NOTIFICATION_TYPE_CLASS_BOOKED', 'class_booked');
define('NOTIFICATION_TYPE_CLASS_CANCELLED', 'class_cancelled');
define('NOTIFICATION_TYPE_CLASS_REMINDER', 'class_reminder');
define('NOTIFICATION_TYPE_MESSAGE_FROM_ADMIN', 'message_from_admin');
define('NOTIFICATION_TYPE_CARD_EXPIRED', 'card_expired');
define('NOTIFICATION_TYPE_TRIAL_ENDING', 'trial_ending');

// Delivery Methods
define('DELIVERY_METHOD_IN_APP', 'in_app');
define('DELIVERY_METHOD_EMAIL', 'email');

// Default Permissions
define('PERMISSION_MANAGE_ALL', 'manage_all');
define('PERMISSION_VIEW_DASHBOARD', 'view_dashboard');
define('PERMISSION_VIEW_MEMBERS', 'view_members');
define('PERMISSION_EDIT_MEMBERS', 'edit_members');
define('PERMISSION_VIEW_SUBSCRIPTIONS', 'view_subscriptions');
define('PERMISSION_MANAGE_SUBSCRIPTIONS', 'manage_subscriptions');
define('PERMISSION_VIEW_CLASSES', 'view_classes');
define('PERMISSION_MANAGE_CLASSES', 'manage_classes');
define('PERMISSION_VIEW_BOOKINGS', 'view_bookings');
define('PERMISSION_MANAGE_BOOKINGS', 'manage_bookings');
define('PERMISSION_VIEW_PAYMENTS', 'view_payments');
define('PERMISSION_MANAGE_PAYMENTS', 'manage_payments');
define('PERMISSION_VIEW_MEMBER_MESSAGES', 'view_member_messages');
define('PERMISSION_SEND_MEMBER_MESSAGES', 'send_member_messages');
define('PERMISSION_MANAGE_USERS', 'manage_users');
define('PERMISSION_MANAGE_ROLES', 'manage_roles');
define('PERMISSION_MANAGE_SETTINGS', 'manage_settings');

// Default Roles
define('ROLE_SUPER_ADMIN', 'Super Admin');
define('ROLE_SUBSCRIPTION_MANAGER', 'Subscription Manager');
define('ROLE_CLASS_SCHEDULER', 'Class Scheduler');
define('ROLE_SUPPORT_STAFF', 'Support Staff');
define('ROLE_COACH', 'Coach');

// Validation Rules
define('MIN_PASSWORD_LENGTH', 8);
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx']);

// Date Formats
define('DATE_FORMAT_DISPLAY', 'd/m/Y');
define('DATE_FORMAT_DATABASE', 'Y-m-d');
define('DATETIME_FORMAT_DISPLAY', 'd/m/Y H:i');
define('DATETIME_FORMAT_DATABASE', 'Y-m-d H:i:s');

// Pagination
define('DEFAULT_PER_PAGE', 15);
define('MAX_PER_PAGE', 100);

// Session Settings
define('SESSION_LIFETIME', 120); // minutes
define('REMEMBER_TOKEN_LIFETIME', 30); // days

// Cache Settings
define('CACHE_LIFETIME', 3600); // seconds

// Rate Limiting
define('LOGIN_ATTEMPTS_LIMIT', 5);
define('LOGIN_LOCKOUT_DURATION', 900); // seconds (15 minutes)

// Email Settings
define('EMAIL_QUEUE_ENABLED', true);
define('EMAIL_BATCH_SIZE', 50);

// Stripe Settings
define('STRIPE_CURRENCY', 'gbp');
define('STRIPE_WEBHOOK_TOLERANCE', 300); // seconds

// Age Restrictions
define('MIN_MEMBER_AGE', 5);
define('MAX_MEMBER_AGE', 120);
define('ADULT_AGE', 18);

// Class Settings
define('MAX_CLASS_CAPACITY', 100);
define('CLASS_BOOKING_ADVANCE_DAYS', 30);
define('CLASS_CANCELLATION_HOURS', 24);

// Subscription Settings
define('MAX_SUBSCRIPTION_MONTHS', 24);
define('MIN_SUBSCRIPTION_PRICE', 0.01);
define('MAX_SUBSCRIPTION_PRICE', 999.99);

// File Storage
define('STORAGE_DISK', 'local');
define('PROFILE_PHOTOS_PATH', 'uploads/profiles');
define('CLASS_IMAGES_PATH', 'uploads/classes');
define('DOCUMENTS_PATH', 'uploads/documents');

// Application Status
define('APP_STATUS_ACTIVE', 'active');
define('APP_STATUS_MAINTENANCE', 'maintenance');

// API Settings
define('API_RATE_LIMIT', 60); // requests per minute
define('API_TOKEN_LIFETIME', 3600); // seconds

// Logging Levels
define('LOG_LEVEL_DEBUG', 'debug');
define('LOG_LEVEL_INFO', 'info');
define('LOG_LEVEL_WARNING', 'warning');
define('LOG_LEVEL_ERROR', 'error');
define('LOG_LEVEL_CRITICAL', 'critical');

// Currency Codes
define('SUPPORTED_CURRENCIES', ['GBP', 'USD', 'EUR']);
define('DEFAULT_CURRENCY', 'GBP');

// Time Zones
define('DEFAULT_TIMEZONE', 'Europe/London');
define('SUPPORTED_TIMEZONES', [
    'Europe/London',
    'Europe/Paris',
    'Europe/Berlin',
    'America/New_York',
    'America/Chicago',
    'America/Denver',
    'America/Los_Angeles',
]);

// Feature Flags
define('FEATURE_FREE_TRIALS', true);
define('FEATURE_PRORATA_BILLING', true);
define('FEATURE_AUTO_BOOKING', true);
define('FEATURE_EMAIL_NOTIFICATIONS', true);
define('FEATURE_SMS_NOTIFICATIONS', false);
define('FEATURE_MOBILE_APP', false);

// Default Settings
define('DEFAULT_SITE_COLORS', [
    'primary' => '#971b1e',
    'secondary' => '#cda22d',
    'success' => '#10b981',
    'warning' => '#f59e0b',
    'error' => '#ef4444',
    'info' => '#3b82f6',
]);

// HTTP Status Codes
define('HTTP_OK', 200);
define('HTTP_CREATED', 201);
define('HTTP_NO_CONTENT', 204);
define('HTTP_BAD_REQUEST', 400);
define('HTTP_UNAUTHORIZED', 401);
define('HTTP_FORBIDDEN', 403);
define('HTTP_NOT_FOUND', 404);
define('HTTP_METHOD_NOT_ALLOWED', 405);
define('HTTP_UNPROCESSABLE_ENTITY', 422);
define('HTTP_INTERNAL_SERVER_ERROR', 500);
define('HTTP_SERVICE_UNAVAILABLE', 503);