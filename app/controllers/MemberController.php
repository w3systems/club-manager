<?php
// app/controllers/MemberController.php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Member; // Now using Member model
use App\Models\Subscription;
use App\Models\PaymentMethod;
use App\Models\ClassModel;
use App\Models\ClassBooking;
use App\Models\Notification;
use App\Services\StripeService;
use App\Services\NotificationService;

class MemberController extends Controller
{
    public function __construct()
    {
        Auth::requireMemberLogin(); // Ensure a member is logged in
    }

    public function dashboard()
    {
        $member = Auth::member(); // Get the logged-in member
        $upcomingClasses = ClassBooking::getUpcomingClassesForMember($member['id']);
        $notifications = Notification::getLatestNotificationsForMember($member['id'], 5);
        $this->view('member.dashboard', [
            'member' => $member,
            'upcomingClasses' => $upcomingClasses,
            'notifications' => $notifications
        ]);
    }

    public function profile()
    {
        $member = Auth::member();
        $this->view('member.profile', ['member' => $member]);
    }

    public function updateProfile()
    {
        $member = Auth::member();
        $data = $_POST;

        $rules = [
            'first_name' => ['required'],
            'last_name' => ['required'],
            'email' => ['required', 'email'],
            'mobile' => ['required'],
            'date_of_birth' => ['required'],
            'emergency_contact_name' => ['required'],
            'emergency_contact_number' => ['required'],
            'emergency_contact_relationship' => ['required'],
        ];

        if (calculateAge($data['date_of_birth']) < 18) {
            $rules['parent_guardian_email'] = ['required', 'email'];
            $rules['parent_guardian_mobile'] = ['required'];
        }

        $errors = $this->validate($data, $rules);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $this->redirect('/profile');
        }

        try {
            Member::update($member['id'], $data);
            $_SESSION['success_message'] = 'Profile updated successfully.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Error updating profile: ' . $e->getMessage();
        }
        $this->redirect('/profile');
    }

    public function subscriptions()
    {
        $member = Auth::member();
        $activeSubscriptions = Subscription::getMemberActiveSubscriptions($member['id']);
        $pastSubscriptions = Subscription::getMemberPastSubscriptions($member['id']);
        $availableSubscriptions = Subscription::getAvailableSubscriptionsForMember($member['id']); // Filter by age, capacity
        $this->view('member.subscriptions', [
            'member' => $member,
            'activeSubscriptions' => $activeSubscriptions,
            'pastSubscriptions' => $pastSubscriptions,
            'availableSubscriptions' => $availableSubscriptions
        ]);
    }

    public function newSubscription()
    {
        $subscriptionId = $_GET['id'] ?? null;
        if (!$subscriptionId) {
            $this->redirect('/subscriptions');
        }
        $subscription = Subscription::findById($subscriptionId);
        if (!$subscription) {
            $_SESSION['error_message'] = 'Subscription not found.';
            $this->redirect('/subscriptions');
        }

        $this->view('member.new_subscription_signup', ['subscription' => $subscription]);
    }

    public function signupSubscription()
    {
        $memberId = Auth::member()['id'];
        $subscriptionId = $_POST['subscription_id'] ?? null;
        $paymentMethodLocalId = $_POST['payment_method_id'] ?? null; // If existing method is selected
        $stripeToken = $_POST['stripeToken'] ?? null; // If new card is added

        if (!$subscriptionId) {
            $_SESSION['error_message'] = 'Subscription not specified.';
            $this->redirect('/subscriptions');
        }

        $subscription = Subscription::findById($subscriptionId);
        if (!$subscription) {
            $_SESSION['error_message'] = 'Subscription not found.';
            $this->redirect('/subscriptions');
        }

        // Logic for payment method
        $customer = Member::getStripeCustomerId($memberId);
        $pmStripeId = null; // Stripe's Payment Method ID

        if ($stripeToken) {
            // New card provided via Stripe.js token
            try {
                $stripeService = new StripeService();
                $pmStripeId = $stripeService->createPaymentMethodFromToken($stripeToken);
                if (!$customer) {
                    $customer = $stripeService->createCustomer($memberId, Auth::member()['email'], Auth::member()['first_name'] . ' ' . Auth::member()['last_name']);
                    Member::updateStripeCustomerId($memberId, $customer);
                }
                $stripeService->attachPaymentMethodToCustomer($pmStripeId, $customer);
                PaymentMethod::create([
                    'member_id' => $memberId,
                    'stripe_payment_method_id' => $pmStripeId,
                    'last_four' => $_POST['last_four'], // From client-side
                    'card_brand' => $_POST['card_brand'], // From client-side
                    'is_default' => 1
                ]);
                PaymentMethod::setDefault($memberId, $pmStripeId); // Ensure newly added is default
            } catch (\Exception $e) {
                $_SESSION['error_message'] = 'Error adding payment method: ' . $e->getMessage();
                $this->redirect('/subscriptions/new?id=' . $subscriptionId);
            }
        } elseif ($paymentMethodLocalId) {
            // Existing payment method selected (using its local DB ID)
            $pm = PaymentMethod::findById($paymentMethodLocalId);
            if ($pm && $pm['member_id'] == $memberId) {
                $pmStripeId = $pm['stripe_payment_method_id'];
                PaymentMethod::setDefault($memberId, $pmStripeId); // Make selected default
            } else {
                $_SESSION['error_message'] = 'Invalid payment method selected.';
                $this->redirect('/subscriptions/new?id=' . $subscriptionId);
            }
        } else {
            $_SESSION['error_message'] = 'No payment method provided or selected.';
            $this->redirect('/subscriptions/new?id=' . $subscriptionId);
        }

        try {
            // Enroll member into subscription
            Subscription::enrollMember($memberId, $subscriptionId, $pmStripeId);
            $_SESSION['success_message'] = 'Subscription successfully activated!';
            $this->redirect('/subscriptions');
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Error signing up for subscription: ' . $e->getMessage();
            $this->redirect('/subscriptions/new?id=' . $subscriptionId);
        }
    }


    public function classes()
    {
        $member = Auth::member();
        $memberClasses = ClassBooking::getMemberBookedClasses($member['id']);
        $this->view('member.classes', ['classes' => $memberClasses]);
    }

    public function bookClass($classInstanceId)
    {
        $memberId = Auth::member()['id'];
        try {
            // Check if member is eligible to book (e.g., has relevant subscription)
            if (ClassBooking::canMemberBookClass($memberId, $classInstanceId)) {
                ClassBooking::bookManualClass($memberId, $classInstanceId);
                $_SESSION['success_message'] = 'Class booked successfully!';
            } else {
                $_SESSION['error_message'] = 'You are not eligible to book this class.';
            }
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Error booking class: ' . $e->getMessage();
        }
        $this->redirect('/classes'); // Or redirect to class detail page
    }

    public function payments()
    {
        $member = Auth::member();
        $payments = \App\Models\Payment::getMemberPayments($member['id']);
        $this->view('member.payments', ['payments' => $payments]);
    }

    public function paymentMethods()
    {
        $member = Auth::member();
        $paymentMethods = PaymentMethod::getMemberPaymentMethods($member['id']);
        $stripePublishableKey = STRIPE_PUBLISHABLE_KEY; // Pass to view for Stripe JS
        $this->view('member.payment_methods', ['paymentMethods' => $paymentMethods, 'stripePublishableKey' => $stripePublishableKey]);
    }

    public function addPaymentMethod()
    {
        $memberId = Auth::member()['id'];
        $stripeToken = $_POST['stripeToken'] ?? null;
        $lastFour = $_POST['last_four'] ?? null;
        $cardBrand = $_POST['card_brand'] ?? null;

        if (!$stripeToken || !$lastFour || !$cardBrand) {
            $_SESSION['error_message'] = 'Invalid payment method data provided.';
            $this->redirect('/payment-methods');
        }

        try {
            $stripeService = new StripeService();
            $customer = Member::getStripeCustomerId($memberId);

            if (!$customer) {
                $customer = $stripeService->createCustomer($memberId, Auth::member()['email'], Auth::member()['first_name'] . ' ' . Auth::member()['last_name']);
                Member::updateStripeCustomerId($memberId, $customer);
            }

            $paymentMethodStripeId = $stripeService->createPaymentMethodFromToken($stripeToken);
            $stripeService->attachPaymentMethodToCustomer($paymentMethodStripeId, $customer);

            PaymentMethod::create([
                'member_id' => $memberId,
                'stripe_payment_method_id' => $paymentMethodStripeId,
                'last_four' => $lastFour,
                'card_brand' => $cardBrand,
                'is_default' => 0 // Not default by default
            ]);

            // If this is the first payment method, make it default
            if (count(PaymentMethod::getMemberPaymentMethods($memberId)) === 1) {
                PaymentMethod::setDefault($memberId, $paymentMethodStripeId);
            }

            $_SESSION['success_message'] = 'Payment method added successfully!';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Error adding payment method: ' . $e->getMessage();
        }
        $this->redirect('/payment-methods');
    }

    public function setDefaultPaymentMethod()
    {
        $memberId = Auth::member()['id'];
        $paymentMethodLocalId = $_POST['payment_method_id'] ?? null;

        if (!$paymentMethodLocalId) {
            $_SESSION['error_message'] = 'No payment method selected.';
            $this->redirect('/payment-methods');
        }

        try {
            $pm = PaymentMethod::findById($paymentMethodLocalId);
            if (!$pm || $pm['member_id'] != $memberId) {
                throw new \Exception("Invalid payment method.");
            }
            PaymentMethod::setDefault($memberId, $pm['stripe_payment_method_id']);
            $_SESSION['success_message'] = 'Default payment method updated.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Error setting default payment method: ' . $e->getMessage();
        }
        $this->redirect('/payment-methods');
    }

    public function deletePaymentMethod()
    {
        $memberId = Auth::member()['id'];
        $paymentMethodLocalId = $_POST['payment_method_id'] ?? null;

        if (!$paymentMethodLocalId) {
            $_SESSION['error_message'] = 'No payment method selected.';
            $this->redirect('/payment-methods');
        }

        // Prevent deleting the only active payment method for live subscriptions
        if (Subscription::hasLiveSubscription($memberId) && PaymentMethod::countActiveMethods($memberId) <= 1) {
             $_SESSION['error_message'] = 'Cannot delete the only active payment method while you have a live subscription.';
             $this->redirect('/payment-methods');
        }

        try {
            PaymentMethod::deletePaymentMethod($memberId, $paymentMethodLocalId);
            $_SESSION['success_message'] = 'Payment method deleted.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Error deleting payment method: ' . $e->getMessage();
        }
        $this->redirect('/payment-methods');
    }


    public function notifications()
    {
        $member = Auth::member();
        $notifications = Notification::getNotificationsForMember($member['id']);
        $notificationSettings = Notification::getNotificationSettingsForMember($member['id']);
        $this->view('member.notifications', [
            'notifications' => $notifications,
            'notificationSettings' => $notificationSettings
        ]);
    }

    public function updateNotificationSettings()
    {
        $memberId = Auth::member()['id'];
        $settings = $_POST['settings'] ?? []; // Array of notification types and delivery methods

        try {
            \App\Models\NotificationSetting::updateNotificationSettings($memberId, $settings);
            $_SESSION['success_message'] = 'Notification settings updated.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Error updating notification settings: ' . $e->getMessage();
        }
        $this->redirect('/notifications');
    }

    public function memberMessages()
    {
        $memberId = Auth::member()['id'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $messageContent = $_POST['message_content'] ?? '';
            if (!empty($messageContent)) {
                try {
                    Message::createMessage($memberId, null, $messageContent, 'member_to_admin'); // recipient is admin, sender is member
                    $_SESSION['success_message'] = 'Message sent to admin.';
                } catch (\Exception $e) {
                    $_SESSION['error_message'] = 'Error sending message: ' . $e->getMessage();
                }
            } else {
                $_SESSION['error_message'] = 'Message content cannot be empty.';
            }
            $this->redirect('/messages'); // Redirect back to messages view
        } else {
            $messages = Message::getMessagesForMember($memberId);
            $this->view('member.messages', ['messages' => $messages]);
        }
    }

    public function showFreeTrialClasses()
    {
        // This is typically for unregistered users, but a logged-in member might try to access
        if (Auth::check()) {
            $_SESSION['error_message'] = 'You are already logged in. Please manage your subscriptions or view classes.';
            $this->redirect(Auth::isAdmin() ? '/admin' : '/');
        }
        $availableClasses = ClassModel::getAvailableFreeTrialClasses(); // No member_id filter needed here as it's public
        $this->view('public.free_trial', ['classes' => $availableClasses]);
    }

    public function bookFreeTrial()
    {
        // This action should primarily happen during registration (AuthController::register)
        // or for existing logged-out users completing registration.
        if (Auth::check()) {
            $_SESSION['error_message'] = 'You are already a member. Please sign up for a subscription.';
            $this->redirect('/');
        }
        // If a free trial booking is triggered here, it implies a registration flow
        $_SESSION['error_message'] = 'Please register to book a free trial.';
        $this->redirect('/register'); // Redirect to registration with selected class ID pre-filled or passed.
    }
}
