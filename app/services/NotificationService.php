<?php
// app/services/NotificationService.php
namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationSetting;
use App\Models\Member;
use Exception;
use App\Helpers\functions as Helpers;

class NotificationService
{
    /**
     * Sends a notification to a member based on their preferences.
     *
     * @param int $memberId
     * @param string $type The type of notification (e.g., 'payment_received', 'subscription_renewed')
     * @param string $message The primary message content for in-app.
     * @param array $data Additional data for email templating if needed.
     */
    public static function send($memberId, $type, $message, $data = [])
    {
        $member = Member::findById($memberId);
        if (!$member) {
            return; // Member not found
        }

        $preferenceString = NotificationSetting::getPreference($memberId, $type);
        $deliveryMethods = explode(',', $preferenceString ?: 'in_app'); // Default to in_app if no preference

        $methodsSent = [];

        foreach ($deliveryMethods as $method) {
            $method = trim($method);
            if (empty($method)) continue; // Skip empty strings from explode

            if ($method === 'in_app') {
                Notification::create($memberId, $type, $message, 'in_app');
                $methodsSent[] = 'in_app';
            } elseif ($method === 'email') {
                try {
                    $emailSubject = self::getEmailSubjectForType($type);
                    $emailBody = self::getEmailBodyForType($type, $member, $message, $data);
                    $graphService = new MicrosoftGraphService();
                    $graphService->sendEmail($member['email'], $emailSubject, $emailBody);
                    Notification::create($memberId, $type, $message, 'email'); // Record email sent
                    $methodsSent[] = 'email';
                } catch (Exception $e) {
                    error_log("Failed to send email for notification type '{$type}' to {$member['email']}: " . $e->getMessage());
                    // Optionally, record a failed email notification or retry
                }
            }
        }

        // Update the notification record with all successful delivery methods
        // (This might require creating the notification first with status, then updating it)
        // For simplicity, Notification::create above records one type.
        // A more complex system might have one notification row with multiple delivery statuses.
        // For now, assume a separate Notification::create call for each.
    }

    private static function getEmailSubjectForType($type)
    {
        switch ($type) {
            case 'subscription_renewed':
                return 'Your Subscription Has Been Renewed!';
            case 'payment_received':
                return 'Payment Confirmation';
            case 'payment_failed':
                return 'Important: Your Payment Has Failed';
            case 'saved_card_expired':
                return 'Action Required: Your Saved Card Is Expiring Soon';
            case 'message_from_admin':
                return 'New Message From Administrator';
            case 'upcoming_class_details':
                return 'Reminder: Your Upcoming Class Details';
            case 'subscription_changed':
                return 'Your Subscription Has Changed';
            default:
                return 'Notification from ' . APP_URL;
        }
    }

    private static function getEmailBodyForType($type, $member, $message, $data)
    {
        // Basic HTML email template. In a real app, you'd use a templating engine (e.g., Twig)
        // and separate email templates for each type.
        $body = "<p>Dear " . Helpers\esc($member['first_name']) . ",</p>";
        $body .= "<p>" . Helpers\esc($message) . "</p>";

        switch ($type) {
            case 'subscription_renewed':
                $body .= "<p>Your <strong>" . Helpers\esc($data['subscription_name'] ?? 'subscription') . "</strong> has been successfully renewed for &pound;" . number_format($data['amount'] ?? 0, 2) . ".</p>";
                $body .= "<p>Thank you for your continued membership!</p>";
                break;
            case 'payment_received':
                $body .= "<p>We have successfully received your payment of &pound;" . number_format($data['amount'] ?? 0, 2) . ".</p>";
                $body .= "<p>This payment is for: " . Helpers\esc($data['description'] ?? 'your subscription/classes') . ".</p>";
                break;
            case 'payment_failed':
                $body .= "<p>We were unable to process your recent payment of &pound;" . number_format($data['amount'] ?? 0, 2) . ".</p>";
                $body .= "<p>Please log in to your portal at <a href='" . APP_URL . "/payment-methods'>" . APP_URL . "/payment-methods</a> to update your payment method or make a manual payment.</p>";
                $body .= "<p>Ignoring this may lead to suspension of your services.</p>";
                break;
            case 'saved_card_expired':
                $body .= "<p>Your saved card ending in ****" . Helpers\esc($data['last_four'] ?? '') . " is expiring soon (Expires: " . Helpers\esc($data['exp_month'] ?? '') . "/" . Helpers\esc($data['exp_year'] ?? '') . ").</p>";
                $body .= "<p>Please update your payment details at <a href='" . APP_URL . "/payment-methods'>" . APP_URL . "/payment-methods</a> to avoid any disruption to your subscription.</p>";
                break;
            case 'message_from_admin':
                $body .= "<p>You have received a new message from our administrator. Please log in to your portal to view it:</p>";
                $body .= "<p><a href='" . APP_URL . "/messages'>" . APP_URL . "/messages</a></p>";
                break;
            case 'upcoming_class_details':
                $body .= "<p>Here are the details for your upcoming class:</p>";
                $body .= "<ul>";
                $body .= "<li><strong>Class:</strong> " . Helpers\esc($data['class_name'] ?? 'N/A') . "</li>";
                $body .= "<li><strong>Date:</strong> " . Helpers\esc($data['class_date'] ?? 'N/A') . "</li>";
                $body .= "<li><strong>Time:</strong> " . Helpers\esc($data['class_time'] ?? 'N/A') . "</li>";
                $body .= "</ul>";
                break;
            case 'subscription_changed':
                $oldSubName = Helpers\esc($data['old_subscription_name'] ?? 'N/A');
                $newSubName = Helpers\esc($data['new_subscription_name'] ?? 'N/A');
                $reason = Helpers\esc($data['reason'] ?? 'admin_action');
                $reasonText = '';
                if ($reason === 'age_limit') {
                    $reasonText = ' due to age limits and your eligibility for a new plan';
                }
                $body .= "<p>Your subscription has been changed{$reasonText}.</p>";
                $body .= "<p>Your previous subscription: <strong>{$oldSubName}</strong></p>";
                $body .= "<p>Your new subscription: <strong>{$newSubName}</strong></p>";
                $body .= "<p>Please review your new subscription details in your member portal.</p>";
                break;
            default:
                $body .= "<p>For more details, please log in to your account at <a href='" . APP_URL . "'>" . APP_URL . "</a>.</p>";
        }
        $body .= "<p>Sincerely,<br>" . Helpers\esc(parse_url(APP_URL, PHP_URL_HOST)) . " Team</p>";
        return $body;
    }

    // Specific notification triggers (called from controllers/models)
    public static function sendPaymentFailedNotification($memberId, $amount)
    {
        self::send($memberId, 'payment_failed', "A payment of £" . number_format($amount, 2) . " for your subscription has failed.", ['amount' => $amount]);
    }

    public static function sendPaymentReceivedNotification($memberId, $amount, $description)
    {
        self::send($memberId, 'payment_received', "We received your payment of £" . number_format($amount, 2) . ".", ['amount' => $amount, 'description' => $description]);
    }

    public static function sendSubscriptionRenewedNotification($memberId, $subscriptionName, $amount)
    {
        self::send($memberId, 'subscription_renewed', "Your " . $subscriptionName . " subscription has been renewed.", ['subscription_name' => $subscriptionName, 'amount' => $amount]);
    }

    public static function sendSavedCardExpiryNotification($memberId, $lastFour, $expMonth, $expYear)
    {
        self::send($memberId, 'saved_card_expired', "One of your saved payment methods is expiring soon.", ['last_four' => $lastFour, 'exp_month' => $expMonth, 'exp_year' => $expYear]);
    }

    public static function sendAdminMessageNotification($memberId, $messageContent)
    {
        self::send($memberId, 'message_from_admin', "You have a new message from the administrator: " . $messageContent);
    }

    public static function sendUpcomingClassNotification($memberId, $classInstanceId)
    {
        $class = ClassModel::findById($classInstanceId);
        if ($class) {
            self::send($memberId, 'upcoming_class_details', "Reminder: Your class " . $class['name'] . " is coming up soon.", [
                'class_name' => $class['name'],
                'class_date' => (new \DateTime($class['instance_date_time']))->format('D, M jS, Y'),
                'class_time' => (new \DateTime($class['instance_date_time']))->format('H:i'),
            ]);
        }
    }

    public static function sendSubscriptionChangedNotification($memberId, $oldSubscriptionName, $newSubscriptionName, $reason)
    {
        self::send($memberId, 'subscription_changed', "Your subscription has changed from " . $oldSubscriptionName . " to " . $newSubscriptionName . ".", [
            'old_subscription_name' => $oldSubscriptionName,
            'new_subscription_name' => $newSubscriptionName,
            'reason' => $reason
        ]);
    }
}
