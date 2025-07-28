<?php
// app/models/Subscription.php
namespace App\Models;

use App\Core\Database;
use App\Services\StripeService;
use App\Services\NotificationService;
use Exception;
use App\Helpers\functions as Helpers; // Use alias to avoid function name conflicts

class Subscription
{
    private static $table = 'subscriptions';
    private static $memberSubscriptionTable = 'member_subscriptions';

    // CRUD for Subscription Types
    public static function create($data)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "INSERT INTO " . self::$table . " (name, description, type, price, term_length, term_unit,
                fixed_start_day, prorata_enabled, prorata_price, admin_fee, capacity, free_trial_enabled,
                min_age, max_age, next_subscription_id, charge_on_start_date, stripe_price_id)
                VALUES (:name, :description, :type, :price, :term_length, :term_unit,
                :fixed_start_day, :prorata_enabled, :prorata_price, :admin_fee, :capacity, :free_trial_enabled,
                :min_age, :max_age, :next_subscription_id, :charge_on_start_date, :stripe_price_id)";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'price' => $data['price'],
            'term_length' => $data['term_length'] ?? null,
            'term_unit' => $data['term_unit'] ?? null,
            'fixed_start_day' => $data['fixed_start_day'] ?? null,
            'prorata_enabled' => $data['prorata_enabled'] ?? 0,
            'prorata_price' => $data['prorata_price'] ?? null,
            'admin_fee' => $data['admin_fee'] ?? 0.00,
            'capacity' => $data['capacity'] ?? null,
            'free_trial_enabled' => $data['free_trial_enabled'] ?? 0,
            'min_age' => $data['min_age'] ?? null,
            'max_age' => $data['max_age'] ?? null,
            'next_subscription_id' => $data['next_subscription_id'] ?? null,
            'charge_on_start_date' => $data['charge_on_start_date'] ?? 0,
            'stripe_price_id' => $data['stripe_price_id'] ?? null,
        ]);
    }

    public static function findById($id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM " . self::$table . " WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public static function getAllSubscriptions()
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM " . self::$table . " ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    // Member Subscription Management
    public static function enrollMember($memberId, $subscriptionId, $stripePaymentMethodId)
    {
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        try {
            $subscription = self::findById($subscriptionId);
            if (!$subscription) {
                throw new Exception("Subscription type not found.");
            }
            if ($subscription['capacity'] !== null && self::getCurrentCapacity($subscriptionId) >= $subscription['capacity']) {
                throw new Exception("Subscription capacity reached.");
            }

            $member = Member::findById($memberId);
            if (!$member || !$member['stripe_customer_id']) {
                throw new Exception("Stripe customer ID not found for member. Please ensure a payment method is added first.");
            }

            $stripeService = new StripeService();
            $currentDate = date('Y-m-d');
            $startDateTime = new DateTime();
            $endDateTime = null;
            $firstChargeAmount = $subscription['price'];
            $stripeSubscriptionId = null;
            $internalStatus = 'active';

            if ($subscription['type'] === 'recurring') {
                $trialEnd = null;
                if ($subscription['free_trial_enabled']) {
                    $trialEnd = (new DateTime())->modify('+1 month')->getTimestamp(); // 1 month free trial
                    $internalStatus = 'trial';
                }

                // Calculate first charge date and amount based on prorata and fixed_start_day
                if ($subscription['prorata_enabled'] && $subscription['fixed_start_day']) {
                    $dayOfMonth = (int)$subscription['fixed_start_day'];
                    $today = new DateTime();
                    $nextBillingCycleAnchor = (new DateTime())
                                            ->setDate($today->format('Y'), $today->format('m'), $dayOfMonth)
                                            ->modify('+1 month')
                                            ->getTimestamp();

                    // If today is past the fixed day of the month, the current month is pro-rated until next fixed day
                    if ($today->format('d') >= $dayOfMonth) {
                        // The initial charge will be the prorated amount + admin fee
                        $firstChargeAmount = $subscription['prorata_price'] + $subscription['admin_fee'];
                    }
                    // For Stripe subscription, we'd set billing_cycle_anchor and proration_behavior
                    // For simplicity here, assume the price_id handles recurring payment, initial charge is managed separately or by Stripe's proration.
                    // If Stripe's proration is desired, the `firstChargeAmount` would be handled by `collection_method: 'send_invoice'` and `invoice_item` for proration.
                    // For this example, we proceed with Stripe Subscription and assume the first charge is handled by its terms.
                    // The `prorata_price` in our DB would be for our internal tracking/invoicing if not fully automated by Stripe's proration rules.
                }

                // Create Stripe Subscription
                $stripeProductPriceId = $subscription['stripe_price_id'];
                if (!$stripeProductPriceId) {
                    throw new Exception("Stripe Price ID not configured for this subscription type.");
                }
                $stripeSub = $stripeService->createStripeSubscription(
                    $member['stripe_customer_id'],
                    $stripeProductPriceId,
                    $stripePaymentMethodId,
                    $trialEnd
                );
                $stripeSubscriptionId = $stripeSub->id;

            } else { // Fixed length or session-based
                // For fixed-length/session, we create a one-time charge immediately
                $stripeService->createOneTimeCharge($member['stripe_customer_id'], $stripePaymentMethodId, $firstChargeAmount, $subscription['name']);
            }

            // Determine effective start/end dates for internal record
            if ($subscription['type'] === 'fixed_length') {
                $endDateTime = (new DateTime())->modify("+$subscription[term_length] {$subscription['term_unit']}");
            } else if ($subscription['type'] === 'session_based') {
                $endDateTime = null; // No fixed end date, depends on sessions used
            }

            // Record member subscription in DB
            $sql = "INSERT INTO " . self::$memberSubscriptionTable . " (member_id, subscription_id, start_date, end_date,
                    status, payment_method_id, stripe_subscription_id, first_charge_amount)
                    VALUES (:member_id, :subscription_id, :start_date, :end_date,
                    :status, :payment_method_id, :stripe_subscription_id, :first_charge_amount)";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                'member_id' => $memberId,
                'subscription_id' => $subscriptionId,
                'start_date' => $startDateTime->format('Y-m-d'),
                'end_date' => $endDateTime ? $endDateTime->format('Y-m-d') : null,
                'status' => $internalStatus,
                'payment_method_id' => PaymentMethod::findByStripeId($stripePaymentMethodId)['id'], // Store internal PM ID
                'stripe_subscription_id' => $stripeSubscriptionId,
                'first_charge_amount' => $firstChargeAmount
            ]);
            $memberSubscriptionId = $db->lastInsertId();

            // Auto-book classes based on subscription type and payment status (for immediate or next period)
            self::autoBookClassesForMemberSubscription($memberId, $subscriptionId, $startDateTime->format('Y-m-d'), $endDateTime ? $endDateTime->format('Y-m-d') : null);

            $db->commit();
            return $memberSubscriptionId;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function adminEnrollMember($memberId, $subscriptionId, $startDate, $overrideFee = null)
    {
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        try {
            $subscription = self::findById($subscriptionId);
            if (!$subscription) {
                throw new Exception("Subscription type not found.");
            }

            // Admin override logic: no immediate Stripe charge unless specified
            $chargeAmount = $overrideFee !== null ? $overrideFee : 0;

            $end_date = null;
            if ($subscription['type'] === 'fixed_length') {
                $end_date = (new DateTime($startDate))->modify("+$subscription[term_length] {$subscription['term_unit']}")->format('Y-m-d');
            }

            $sql = "INSERT INTO " . self::$memberSubscriptionTable . " (member_id, subscription_id, start_date, end_date,
                    status, admin_override_fee)
                    VALUES (:member_id, :subscription_id, :start_date, :end_date,
                    :status, :admin_override_fee)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'member_id' => $memberId,
                'subscription_id' => $subscriptionId,
                'start_date' => $startDate,
                'end_date' => $end_date,
                'status' => 'active',
                'admin_override_fee' => $chargeAmount
            ]);
            $memberSubscriptionId = $db->lastInsertId();

            // Record manual payment if an override fee is applied
            if ($overrideFee !== null && $overrideFee > 0) {
                Payment::recordPayment([
                    'member_id' => $memberId,
                    'member_subscription_id' => $memberSubscriptionId,
                    'amount' => $overrideFee,
                    'currency' => 'GBP', // Or from settings
                    'payment_date' => date('Y-m-d H:i:s'),
                    'status' => 'succeeded',
                    'payment_gateway' => 'manual', // Cash/Bank Transfer
                    'transaction_id' => 'ADMIN-' . $memberSubscriptionId . '-' . time(),
                    'invoice_id' => null,
                    'description' => 'Admin added subscription (override fee)'
                ]);
            }

            // Auto-book classes immediately for the new subscription's initial period
            self::autoBookClassesForMemberSubscription($memberId, $subscriptionId, $startDate, $end_date);

            $db->commit();
            return $memberSubscriptionId;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function autoBookClassesForMemberSubscription($memberId, $subscriptionId, $startDate, $endDate = null)
    {
        $subscriptionDetails = self::findById($subscriptionId);
        if (!$subscriptionDetails) {
            throw new Exception("Subscription type not found for auto-booking.");
        }

        $bookingPeriodStart = new DateTime($startDate);
        $bookingPeriodEnd = null;

        if ($subscriptionDetails['type'] === 'recurring') {
            // For recurring, book for the current month/period.
            // If the subscription starts mid-month, book for the remainder of current month
            // and the full next period if that's the prorata arrangement.
            // For simplicity here, we book for the month of the start_date.
            $bookingPeriodEnd = (new DateTime($startDate))->modify('last day of this month');
        } elseif ($subscriptionDetails['type'] === 'fixed_length') {
            $bookingPeriodEnd = new DateTime($endDate ?? $startDate); // Use provided end date or just start date if short
        } elseif ($subscriptionDetails['type'] === 'session_based') {
            // For session-based, auto-book based on available sessions
            // This would require a more complex system to track sessions used vs total
            // For now, will book classes available.
            $bookingPeriodEnd = (new DateTime($startDate))->modify('+1 year'); // Example: book for a year or until sessions exhaust
        }

        if ($bookingPeriodEnd) {
            $classes = ClassModel::getClassesForSubscriptionBetweenDates(
                $subscriptionId,
                $bookingPeriodStart->format('Y-m-d H:i:s'),
                $bookingPeriodEnd->format('Y-m-d H:i:s')
            );
            foreach ($classes as $classInstance) {
                // Check if the class instance is auto-bookable and member is eligible (capacity, age handled by isClassBookable)
                if ($classInstance['auto_book'] == 1 && ClassBooking::isClassBookable($classInstance['id'], $memberId)) {
                    ClassBooking::bookAutoClass($memberId, $classInstance['id']);
                }
            }
        }
    }


    public static function getMemberActiveSubscriptions($memberId)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT ms.*, s.name as subscription_name, s.description as subscription_description,
                    s.type, s.price, s.term_length, s.term_unit, s.capacity
                FROM " . self::$memberSubscriptionTable . " ms
                JOIN " . self::$table . " s ON ms.subscription_id = s.id
                WHERE ms.member_id = :member_id AND ms.status = 'active'
                ORDER BY ms.start_date DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute(['member_id' => $memberId]);
        return $stmt->fetchAll();
    }

    public static function getMemberPastSubscriptions($memberId)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT ms.*, s.name as subscription_name, s.description as subscription_description
                FROM " . self::$memberSubscriptionTable . " ms
                JOIN " . self::$table . " s ON ms.subscription_id = s.id
                WHERE ms.member_id = :member_id AND ms.status != 'active'
                ORDER BY ms.start_date DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute(['member_id' => $memberId]);
        return $stmt->fetchAll();
    }

    public static function getMemberAllSubscriptions($memberId)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT ms.*, s.name as subscription_name, s.description as subscription_description,
                    s.type, s.price, s.term_length, s.term_unit, s.capacity
                FROM " . self::$memberSubscriptionTable . " ms
                JOIN " . self::$table . " s ON ms.subscription_id = s.id
                WHERE ms.member_id = :member_id
                ORDER BY ms.start_date DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute(['member_id' => $memberId]);
        return $stmt->fetchAll();
    }

    public static function getMemberSubscriptionDetails($memberSubscriptionId)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT ms.*, s.name as subscription_name, s.type as subscription_type, s.price as subscription_price,
                       s.min_age, s.max_age, s.next_subscription_id, s.term_length, s.term_unit
                FROM " . self::$memberSubscriptionTable . " ms
                JOIN " . self::$table . " s ON ms.subscription_id = s.id
                WHERE ms.id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute(['id' => $memberSubscriptionId]);
        return $stmt->fetch();
    }

    public static function getMemberSubscriptionByStripeSubscriptionId($stripeSubscriptionId)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT ms.*, s.name as subscription_name, s.type as subscription_type
                FROM " . self::$memberSubscriptionTable . " ms
                JOIN " . self::$table . " s ON ms.subscription_id = s.id
                WHERE ms.stripe_subscription_id = :stripe_subscription_id";
        $stmt = $db->prepare($sql);
        $stmt->execute(['stripe_subscription_id' => $stripeSubscriptionId]);
        return $stmt->fetch();
    }

    public static function countActiveSubscriptions()
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT COUNT(*) FROM " . self::$memberSubscriptionTable . " WHERE status = 'active'");
        return $stmt->fetchColumn();
    }

    public static function getCurrentCapacity($subscriptionId)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM " . self::$memberSubscriptionTable . " WHERE subscription_id = :subscription_id AND status = 'active'");
        $stmt->execute(['subscription_id' => $subscriptionId]);
        return $stmt->fetchColumn();
    }

    public static function hasLiveSubscription($memberId)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM " . self::$memberSubscriptionTable . " WHERE member_id = :member_id AND status = 'active'");
        $stmt->execute(['member_id' => $memberId]);
        return $stmt->fetchColumn() > 0;
    }

    // Admin Actions
    public static function suspendMemberSubscription($memberSubscriptionId, $effectiveDate)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "UPDATE " . self::$memberSubscriptionTable . " SET status = 'suspended', suspension_date = :effective_date WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute(['id' => $memberSubscriptionId, 'effective_date' => $effectiveDate]);
    }

    public static function activateMemberSubscription($memberSubscriptionId, $effectiveDate)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "UPDATE " . self::$memberSubscriptionTable . " SET status = 'active', suspension_date = NULL, updated_at = NOW() WHERE id = :id";
        $stmt = $db->prepare($sql);
        $executed = $stmt->execute(['id' => $memberSubscriptionId]);

        // If reactivating a recurring Stripe subscription, might need to re-enable it on Stripe
        $memberSub = self::getMemberSubscriptionDetails($memberSubscriptionId);
        if ($executed && $memberSub && $memberSub['stripe_subscription_id'] && $memberSub['subscription_type'] === 'recurring') {
            try {
                $stripeService = new StripeService();
                $stripeService->resumeStripeSubscription($memberSub['stripe_subscription_id']);
                // Also, re-book classes for the next period
                self::autoBookClassesForMemberSubscription($memberSub['member_id'], $memberSub['subscription_id'], $effectiveDate);
            } catch (Exception $e) {
                error_log("Failed to resume Stripe subscription {$memberSub['stripe_subscription_id']}: " . $e->getMessage());
                // Consider marking as pending or alerting admin
            }
        }
        return $executed;
    }

    public static function cancelMemberSubscription($memberSubscriptionId, $effectiveDate)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "UPDATE " . self::$memberSubscriptionTable . " SET status = 'cancelled', cancellation_date = :effective_date, end_date = :effective_date WHERE id = :id";
        $stmt = $db->prepare($sql);
        // Also need to cancel Stripe subscription if exists
        $memberSub = self::getMemberSubscriptionDetails($memberSubscriptionId);
        if ($memberSub && $memberSub['stripe_subscription_id'] && $memberSub['subscription_type'] === 'recurring') {
            try {
                $stripeService = new StripeService();
                $stripeService->cancelStripeSubscription($memberSub['stripe_subscription_id']);
            } catch (Exception $e) {
                // Log error but don't prevent local cancellation
                error_log("Failed to cancel Stripe subscription {$memberSub['stripe_subscription_id']}: " . $e->getMessage());
            }
        }
        return $stmt->execute(['id' => $memberSubscriptionId, 'effective_date' => $effectiveDate]);
    }

    public static function deleteMemberSubscription($memberSubscriptionId)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "DELETE FROM " . self::$memberSubscriptionTable . " WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute(['id' => $memberSubscriptionId]);
    }

    public static function updateMemberSubscriptionStatus($memberSubscriptionId, $newStatus)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "UPDATE " . self::$memberSubscriptionTable . " SET status = :new_status WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute(['new_status' => $newStatus, 'id' => $memberSubscriptionId]);
    }

    public static function changeMemberSubscription($memberId, $oldMemberSubscriptionId, $newSubscriptionId, $effectiveDate)
    {
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        try {
            $oldMemberSub = self::getMemberSubscriptionDetails($oldMemberSubscriptionId);
            if (!$oldMemberSub || $oldMemberSub['member_id'] !== $memberId) {
                throw new Exception("Original member subscription not found or does not belong to this member.");
            }

            $newSubscriptionType = self::findById($newSubscriptionId);
            if (!$newSubscriptionType) {
                throw new Exception("New subscription type not found.");
            }

            // 1. Cancel the old subscription (with immediate effect or at period end based on your business logic)
            self::cancelMemberSubscription($oldMemberSubscriptionId, $effectiveDate); // Marks as cancelled in DB and Stripe

            // 2. Enroll in the new subscription (admin context, so no immediate Stripe charge)
            $newSubscriptionStartDate = $effectiveDate;
            $newSubscriptionEndDate = null;
            if ($newSubscriptionType['type'] === 'fixed_length') {
                $newSubscriptionEndDate = (new DateTime($newSubscriptionStartDate))->modify("+" . $newSubscriptionType['term_length'] . " " . $newSubscriptionType['term_unit'])->format('Y-m-d');
            }

            $sql = "INSERT INTO " . self::$memberSubscriptionTable . " (member_id, subscription_id, start_date, end_date, status, notes)
                    VALUES (:member_id, :new_subscription_id, :start_date, :end_date, 'active', :notes)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'member_id' => $memberId,
                'new_subscription_id' => $newSubscriptionId,
                'start_date' => $newSubscriptionStartDate,
                'end_date' => $newSubscriptionEndDate,
                'notes' => 'Changed from subscription ID ' . $oldSubscriptionId . ' (admin action)'
            ]);
            $newMemberSubscriptionId = $db->lastInsertId();

            // Auto-book classes for the new subscription
            self::autoBookClassesForMemberSubscription($memberId, $newSubscriptionId, $newSubscriptionStartDate, $newSubscriptionEndDate);

            // Send notification about subscription change
            NotificationService::sendSubscriptionChangedNotification(
                $memberId,
                $oldMemberSub['subscription_name'],
                $newSubscriptionType['name'],
                'admin_action'
            );

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function getAvailableSubscriptionsForMember($memberId)
    {
        $db = Database::getInstance()->getConnection();
        $member = Member::findById($memberId);
        if (!$member) {
            return [];
        }
        $memberAge = Helpers\calculateAge($member['date_of_birth']);

        $sql = "SELECT s.* FROM " . self::$table . " s
                LEFT JOIN " . self::$memberSubscriptionTable . " ms ON s.id = ms.subscription_id AND ms.member_id = :member_id AND ms.status = 'active'
                WHERE ms.id IS NULL"; // Only show subscriptions the member doesn't currently have active

        $params = ['member_id' => $memberId];

        // Apply age filtering
        if ($memberAge !== null) {
            $sql .= " AND (:member_age BETWEEN s.min_age AND s.max_age OR (s.min_age IS NULL AND s.max_age IS NULL))";
            $params['member_age'] = $memberAge;
        }

        // Apply capacity filtering
        $sql .= " AND (s.capacity IS NULL OR (SELECT COUNT(*) FROM " . self::$memberSubscriptionTable . " WHERE subscription_id = s.id AND status = 'active') < s.capacity)";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function handleSubscriptionRenewal($memberSubscriptionId)
    {
        $db = Database::getInstance()->getConnection();
        $memberSub = self::getMemberSubscriptionDetails($memberSubscriptionId);
        if (!$memberSub || $memberSub['status'] !== 'active' || $memberSub['subscription_type'] !== 'recurring') {
            return; // Only process active recurring subscriptions
        }

        $member = Member::findById($memberSub['member_id']);
        if (!$member) {
            return;
        }

        // Age-based automatic subscription change
        if ($memberSub['max_age'] && Helpers\calculateAge($member['date_of_birth']) > $memberSub['max_age'] && $memberSub['next_subscription_id']) {
            try {
                self::changeMemberSubscription(
                    $memberSub['member_id'],
                    $memberSubscriptionId,
                    $memberSub['next_subscription_id'],
                    date('Y-m-d') // Effective date of change
                );
                // Send notification about age-based change (already handled in changeMemberSubscription)
                return; // Change handled, no further renewal needed for old sub
            } catch (\Exception $e) {
                error_log("Error during age-based subscription change for member {$memberSub['member_id']}: " . $e->getMessage());
            }
        }

        // If not changed, proceed with regular renewal for recurring
        // This process is primarily triggered by Stripe webhooks for `invoice.payment_succeeded`
        // and `invoice.payment_failed`.
        // This function (if called by cron) primarily ensures auto-booking for the new period
        // and sends renewal notifications.
        self::autoBookClassesForRecurring($memberSub['member_id'], $memberSub['subscription_id']);

        // Update last_renewal_date and next_renewal_date
        $nextRenewalDate = (new DateTime($memberSub['next_renewal_date'] ?? $memberSub['start_date']))
                            ->modify('+1 ' . $memberSub['term_unit']) // Adjust based on subscription term
                            ->format('Y-m-d');
        $stmt = $db->prepare("UPDATE " . self::$memberSubscriptionTable . " SET last_renewal_date = NOW(), next_renewal_date = :next_renewal_date WHERE id = :id");
        $stmt->execute(['next_renewal_date' => $nextRenewalDate, 'id' => $memberSubscriptionId]);

        // Send renewal notification
        NotificationService::sendSubscriptionRenewedNotification(
            $memberSub['member_id'],
            $memberSub['subscription_name'],
            $memberSub['subscription_price']
        );
    }
}
