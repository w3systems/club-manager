<?php
// app/models/PaymentMethod.php
namespace App\Models;

use App\Core\Database;
use App\Services\StripeService;
use Exception;

class PaymentMethod
{
    private static $table = 'payment_methods';

    public static function create($data)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "INSERT INTO " . self::$table . " (member_id, stripe_payment_method_id, last_four, card_brand, exp_month, exp_year, is_default, status)
                VALUES (:member_id, :stripe_payment_method_id, :last_four, :card_brand, :exp_month, :exp_year, :is_default, 'active')";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            'member_id' => $data['member_id'],
            'stripe_payment_method_id' => $data['stripe_payment_method_id'],
            'last_four' => $data['last_four'],
            'card_brand' => $data['card_brand'],
            'exp_month' => $data['exp_month'] ?? null,
            'exp_year' => $data['exp_year'] ?? null,
            'is_default' => $data['is_default'] ?? 0
        ]);
    }

    public static function findById($id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM " . self::$table . " WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public static function findByStripeId($stripePaymentMethodId)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM " . self::$table . " WHERE stripe_payment_method_id = :stripe_payment_method_id");
        $stmt->execute(['stripe_payment_method_id' => $stripePaymentMethodId]);
        return $stmt->fetch();
    }

    public static function getMemberPaymentMethods($memberId)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM " . self::$table . " WHERE member_id = :member_id AND status = 'active' ORDER BY is_default DESC, created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute(['member_id' => $memberId]);
        return $stmt->fetchAll();
    }

    public static function setDefault($memberId, $stripePaymentMethodId)
    {
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        try {
            // Unset current default
            $sql1 = "UPDATE " . self::$table . " SET is_default = 0 WHERE member_id = :member_id";
            $stmt1 = $db->prepare($sql1);
            $stmt1->execute(['member_id' => $memberId]);

            // Set new default
            $sql2 = "UPDATE " . self::$table . " SET is_default = 1 WHERE member_id = :member_id AND stripe_payment_method_id = :stripe_payment_method_id";
            $stmt2 = $db->prepare($sql2);
            $stmt2->execute(['member_id' => $memberId, 'stripe_payment_method_id' => $stripePaymentMethodId]);

            // Update Stripe customer's default payment method
            $member = Member::findById($memberId);
            if ($member && $member['stripe_customer_id']) {
                $stripeService = new StripeService();
                $stripeService->updateCustomerDefaultPaymentMethod($member['stripe_customer_id'], $stripePaymentMethodId);
            }

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function deletePaymentMethod($memberId, $paymentMethodLocalId)
    {
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        try {
            $pm = self::findById($paymentMethodLocalId);
            if (!$pm || $pm['member_id'] !== $memberId) {
                throw new Exception("Payment method not found or does not belong to member.");
            }

            // Detach from Stripe
            $stripeService = new StripeService();
            $stripeService->detachPaymentMethod($pm['stripe_payment_method_id']);

            // Mark as inactive in DB
            $sql = "UPDATE " . self::$table . " SET status = 'inactive' WHERE id = :id AND member_id = :member_id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $paymentMethodLocalId, 'member_id' => $memberId]);

            // If deleted method was default, set a new default if other methods exist
            if ($pm['is_default'] == 1) {
                $remainingMethods = self::getMemberPaymentMethods($memberId);
                if (!empty($remainingMethods)) {
                    self::setDefault($memberId, $remainingMethods[0]['stripe_payment_method_id']);
                }
            }

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function updateCardDetails($stripePaymentMethodId, $lastFour, $cardBrand, $expMonth, $expYear)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "UPDATE " . self::$table . " SET last_four = :last_four, card_brand = :card_brand,
                exp_month = :exp_month, exp_year = :exp_year, updated_at = NOW()
                WHERE stripe_payment_method_id = :stripe_payment_method_id";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            'last_four' => $lastFour,
            'card_brand' => $cardBrand,
            'exp_month' => $expMonth,
            'exp_year' => $expYear,
            'stripe_payment_method_id' => $stripePaymentMethodId
        ]);
    }

    public static function markAsInactive($stripePaymentMethodId)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "UPDATE " . self::$table . " SET status = 'inactive' WHERE stripe_payment_method_id = :stripe_payment_method_id";
        $stmt = $db->prepare($sql);
        return $stmt->execute(['stripe_payment_method_id' => $stripePaymentMethodId]);
    }

    public static function countActiveMethods($memberId)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM " . self::$table . " WHERE member_id = :member_id AND status = 'active'");
        $stmt->execute(['member_id' => $memberId]);
        return $stmt->fetchColumn();
    }
}
