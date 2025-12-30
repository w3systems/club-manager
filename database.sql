-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 30, 2025 at 04:22 PM
-- Server version: 10.5.27-MariaDB-cll-lve
-- PHP Version: 8.1.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `perruftn_club`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_roles`
--

DROP TABLE IF EXISTS `admin_roles`;
CREATE TABLE `admin_roles` (
  `admin_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

DROP TABLE IF EXISTS `classes`;
CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `class_type` enum('single','recurring_parent','recurring_instance') NOT NULL COMMENT 'Single event, template for series, or instance of a series',
  `frequency` enum('daily','weekly','fortnightly','4_weekly','monthly') DEFAULT NULL COMMENT 'For recurring_parent',
  `start_time` time DEFAULT NULL,
  `day_of_week` int(1) DEFAULT NULL,
  `original_start_date` date DEFAULT NULL COMMENT 'For recurring_parent: start date of the series',
  `original_end_date` date DEFAULT NULL COMMENT 'For recurring_parent: optional end date of the series',
  `duration_minutes` int(11) DEFAULT NULL COMMENT 'Duration of the class in minutes',
  `capacity` int(11) DEFAULT NULL,
  `session_price` decimal(10,2) DEFAULT NULL,
  `allow_booking_outside_subscription` tinyint(1) NOT NULL DEFAULT 0,
  `auto_book` tinyint(1) DEFAULT 1 COMMENT 'If members with applicable subscription are auto-booked',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_bookings`
--

DROP TABLE IF EXISTS `class_bookings`;
CREATE TABLE `class_bookings` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `class_instance_id` int(11) NOT NULL COMMENT 'References a class where class_type is single or recurring_instance',
  `booking_date` datetime DEFAULT current_timestamp(),
  `status` enum('booked','cancelled','attended','no_show') DEFAULT 'booked',
  `is_auto_booked` tinyint(1) DEFAULT 0 COMMENT 'True if booked automatically by subscription',
  `is_free_trial` tinyint(1) DEFAULT 0 COMMENT 'True if this was a free trial booking',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_instances`
--

DROP TABLE IF EXISTS `class_instances`;
CREATE TABLE `class_instances` (
  `id` int(11) NOT NULL,
  `class_parent_id` int(11) NOT NULL,
  `instance_date_time` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_subscriptions`
--

DROP TABLE IF EXISTS `class_subscriptions`;
CREATE TABLE `class_subscriptions` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL COMMENT 'Can be recurring_parent or single',
  `subscription_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

DROP TABLE IF EXISTS `members`;
CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `consent_photography` tinyint(1) DEFAULT 0,
  `consent_first_aid` tinyint(1) DEFAULT 0,
  `terms_conditions_acceptance` tinyint(1) DEFAULT 0,
  `emergency_contact_name` varchar(255) DEFAULT NULL,
  `emergency_contact_phone` varchar(20) DEFAULT NULL,
  `emergency_contact_relationship` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `parent_guardian_email` varchar(255) DEFAULT NULL,
  `parent_guardian_phone` varchar(20) DEFAULT NULL,
  `stripe_customer_id` varchar(255) DEFAULT NULL COMMENT 'Stripe Customer ID',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `parent_guardian_first_name` varchar(50) DEFAULT NULL,
  `parent_guardian_last_name` varchar(50) DEFAULT NULL,
  `parent_guardian_relationship` varchar(50) DEFAULT NULL,
  `consent_marketing` tinyint(1) DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `member_subscriptions`
--

DROP TABLE IF EXISTS `member_subscriptions`;
CREATE TABLE `member_subscriptions` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `subscription_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL COMMENT 'For fixed-length or cancelled recurring subs',
  `status` enum('active','suspended','cancelled','ended','trial') DEFAULT 'active',
  `payment_method_id` int(11) DEFAULT NULL COMMENT 'Reference to payment_methods.id for current billing',
  `stripe_subscription_id` varchar(255) DEFAULT NULL COMMENT 'Stripe Subscription ID for recurring subscriptions',
  `last_renewal_date` date DEFAULT NULL,
  `next_renewal_date` date DEFAULT NULL,
  `admin_override_fee` decimal(10,2) DEFAULT NULL COMMENT 'Fee set by admin overriding subscription price',
  `suspension_date` date DEFAULT NULL,
  `cancellation_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_member_id` int(11) DEFAULT NULL COMMENT 'Member who sent the message (if member_to_admin)',
  `sender_admin_id` int(11) DEFAULT NULL COMMENT 'Admin who sent the message (if admin_to_member)',
  `recipient_member_id` int(11) DEFAULT NULL COMMENT 'Member who receives the message (if admin_to_member)',
  `recipient_admin_id` int(11) DEFAULT NULL COMMENT 'Admin who receives the message (if member_to_admin)',
  `content` text NOT NULL,
  `type` enum('member_to_admin','admin_to_member') NOT NULL,
  `is_read_by_recipient` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `type` varchar(100) NOT NULL COMMENT 'e.g., payment_received, subscription_renewed, message_from_admin',
  `message` text NOT NULL,
  `delivery_method_sent` varchar(255) DEFAULT 'in_app' COMMENT 'Comma-separated: in_app,email - actual methods sent',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_settings`
--

DROP TABLE IF EXISTS `notification_settings`;
CREATE TABLE `notification_settings` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `notification_type` varchar(100) NOT NULL,
  `delivery_method_preference` varchar(255) NOT NULL COMMENT 'Comma-separated: in_app,email',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `member_subscription_id` int(11) DEFAULT NULL COMMENT 'Optional: link to specific member subscription if applicable',
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) NOT NULL,
  `payment_date` datetime NOT NULL,
  `status` enum('pending','succeeded','failed','refunded') NOT NULL,
  `payment_gateway` enum('stripe','cash','bank_transfer','manual') NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL COMMENT 'Stripe Charge/Payment Intent ID, or custom ID for manual',
  `invoice_id` varchar(255) DEFAULT NULL COMMENT 'Stripe Invoice ID if applicable',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

DROP TABLE IF EXISTS `payment_methods`;
CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `stripe_payment_method_id` varchar(255) NOT NULL COMMENT 'Stripe PaymentMethod ID',
  `last_four` varchar(4) NOT NULL,
  `card_brand` varchar(50) NOT NULL,
  `exp_month` int(11) DEFAULT NULL,
  `exp_year` int(11) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'e.g., manage_members, view_payments, edit_subscriptions, send_emails',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'manage_all', 'Grants full access to all features (typically for Super Admin).', '2025-07-25 13:04:46', '2025-07-25 13:04:46'),
(2, 'view_members', 'Allows viewing member profiles.', '2025-07-25 13:04:46', '2025-07-25 13:04:46'),
(3, 'edit_members', 'Allows editing member profiles and details.', '2025-07-25 13:04:46', '2025-07-25 13:04:46'),
(4, 'manage_subscriptions', 'Allows creating, editing, suspending, and cancelling member subscriptions.', '2025-07-25 13:04:46', '2025-07-25 13:04:46'),
(5, 'view_payments', 'Allows viewing all payment records.', '2025-07-25 13:04:46', '2025-07-25 13:04:46'),
(6, 'manage_payments', 'Allows managing payment statuses and refunds.', '2025-07-25 13:04:46', '2025-07-25 13:04:46'),
(7, 'view_classes', 'Allows viewing all classes and schedules.', '2025-07-25 13:04:46', '2025-07-25 13:04:46'),
(8, 'manage_classes', 'Allows creating, editing, and deleting classes and class series.', '2025-07-25 13:04:46', '2025-07-25 13:04:46'),
(9, 'view_bookings', 'Allows viewing class bookings and attendance.', '2025-07-25 13:04:46', '2025-07-25 13:04:46'),
(10, 'manage_bookings', 'Allows making or cancelling class bookings for members.', '2025-07-25 13:04:46', '2025-07-25 13:04:46'),
(11, 'manage_users', 'Allows creating, editing, and managing admin users and roles.', '2025-07-25 13:04:46', '2025-07-31 16:28:55'),
(12, 'manage_settings', 'Allows access and modification of application settings.', '2025-07-25 13:04:46', '2025-07-25 13:04:46'),
(13, 'send_member_messages', 'Allows sending direct messages to members.', '2025-07-25 13:04:46', '2025-07-31 16:29:03'),
(14, 'view_member_messages', 'Allows viewing messages from members.', '2025-07-25 13:04:46', '2025-07-25 13:04:46'),
(15, 'view_dashboard', 'Allows user to view the admin dashboard', '2025-07-25 23:57:49', '2025-07-25 23:57:49'),
(16, 'view_subscriptions', 'Allows viewing all subscription records.', '2025-07-25 13:04:46', '2025-07-25 13:04:46'),
(17, 'manage_roles', 'Allows access and modification of user roles and permissions.', '2025-07-25 13:04:46', '2025-07-25 13:04:46'),
(18, 'edit_classes', NULL, '2025-07-26 10:41:50', '2025-07-26 10:52:10'),
(19, 'edit_subscriptions', '', '2025-07-26 10:46:27', '2025-07-26 10:46:27');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL COMMENT 'e.g., Super Admin, Subscription Manager, Class Scheduler, Support Staff',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'Full unrestricted access to all administrative functions.', '2025-07-25 13:04:46', '2025-07-25 13:04:46'),
(2, 'Subscription Manager', 'Manages subscriptions, payments, and member accounts.', '2025-07-25 13:04:46', '2025-07-25 13:04:46'),
(3, 'Class Scheduler', 'Manages classes, schedules, and class bookings.', '2025-07-25 13:04:46', '2025-07-31 15:30:06'),
(4, 'Support Staff', 'Handles member inquiries and basic profile updates.', '2025-07-25 13:04:46', '2025-07-25 13:04:46'),
(5, 'Coach', 'Teaches Classes', '2025-07-26 10:06:35', '2025-07-31 23:50:57');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(2, 6),
(2, 13),
(2, 14),
(3, 2),
(3, 7),
(3, 8),
(3, 9),
(3, 10),
(3, 13),
(4, 2),
(4, 3),
(4, 9),
(4, 13),
(4, 14);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(255) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `data_type` enum('string','int','boolean','json') DEFAULT 'string',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `type` enum('fixed_length','recurring','session_based') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'GBP',
  `term_length` int(11) DEFAULT NULL COMMENT 'e.g., 12 for 12 months, or number of sessions',
  `term_unit` enum('day','week','month','year','session') DEFAULT NULL,
  `fixed_start_day` int(11) DEFAULT NULL COMMENT 'Day of month for recurring subscriptions (e.g., 1st, 15th)',
  `prorata_enabled` tinyint(1) DEFAULT 0,
  `prorata_price` decimal(10,2) DEFAULT NULL COMMENT 'Price for the pro-rata first month',
  `auto_book` tinyint(1) NOT NULL DEFAULT 0,
  `admin_fee` decimal(10,2) DEFAULT 0.00,
  `capacity` int(11) DEFAULT NULL COMMENT 'Max number of members for this subscription type',
  `free_trial_enabled` tinyint(1) DEFAULT 0,
  `min_age` int(11) DEFAULT NULL,
  `max_age` int(11) DEFAULT NULL,
  `next_subscription_id` int(11) DEFAULT NULL COMMENT 'For automatic age-based upgrades',
  `charge_on_start_date` tinyint(1) DEFAULT 0 COMMENT 'If true, charged on start date, else immediately on signup',
  `stripe_price_id` varchar(255) DEFAULT NULL COMMENT 'Stripe Price ID for recurring subscriptions',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `admin_roles`
--
ALTER TABLE `admin_roles`
  ADD PRIMARY KEY (`admin_id`,`role_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `class_bookings`
--
ALTER TABLE `class_bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `member_id` (`member_id`,`class_instance_id`),
  ADD KEY `class_instance_id` (`class_instance_id`);

--
-- Indexes for table `class_instances`
--
ALTER TABLE `class_instances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_class_parent_id` (`class_parent_id`);

--
-- Indexes for table `class_subscriptions`
--
ALTER TABLE `class_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `class_id` (`class_id`,`subscription_id`),
  ADD KEY `subscription_id` (`subscription_id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `stripe_customer_id` (`stripe_customer_id`);

--
-- Indexes for table `member_subscriptions`
--
ALTER TABLE `member_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stripe_subscription_id` (`stripe_subscription_id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `subscription_id` (`subscription_id`),
  ADD KEY `payment_method_id` (`payment_method_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_member_id` (`sender_member_id`),
  ADD KEY `sender_admin_id` (`sender_admin_id`),
  ADD KEY `recipient_member_id` (`recipient_member_id`),
  ADD KEY `recipient_admin_id` (`recipient_admin_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `notification_settings`
--
ALTER TABLE `notification_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `member_id` (`member_id`,`notification_type`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `member_subscription_id` (`member_subscription_id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stripe_payment_method_id` (`stripe_payment_method_id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `next_subscription_id` (`next_subscription_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `class_bookings`
--
ALTER TABLE `class_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `class_instances`
--
ALTER TABLE `class_instances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `class_subscriptions`
--
ALTER TABLE `class_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `member_subscriptions`
--
ALTER TABLE `member_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_settings`
--
ALTER TABLE `notification_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
