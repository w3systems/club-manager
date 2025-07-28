<?php
// app/models/Payment.php
namespace App\Models;

use App\Core\Database;

class Payment
{
    private static $table = 'payments';

    public static function recordPayment($data)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "INSERT INTO " . self::$table . " (member_id, member_subscription_id, amount, currency, payment_date, status,
                payment_gateway, transaction_id, invoice_id, description)
                VALUES (:member_id, :member_subscription_id, :amount, :currency, :payment_date, :status,
                :payment_gateway, :transaction_id, :invoice_id, :description)";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            'member_id' => $data['member_id'],
            'member_subscription_id' => $data['member_subscription_id'] ?? null,
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'payment_date' => $data['payment_date'],
            'status' => $data['status'],
            'payment_gateway' => $data['payment_gateway'],
            'transaction_id' => $data['transaction_id'] ?? null,
            'invoice_id' => $data['invoice_id'] ?? null,
            'description' => $data['description'] ?? null,
        ]);
    }

    public static function getMemberPayments($memberId)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT p.*, ms.start_date as subscription_start, s.name as subscription_name
                FROM " . self::$table . " p
                LEFT JOIN member_subscriptions ms ON p.member_subscription_id = ms.id
                LEFT JOIN subscriptions s ON ms.subscription_id = s.id
                WHERE p.member_id = :member_id ORDER BY p.payment_date DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute(['member_id' => $memberId]);
        return $stmt->fetchAll();
    }

    public static function getAllPayments()
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT p.*, m.first_name, m.last_name, m.email as member_email
                FROM " . self::$table . " p
                JOIN members m ON p.member_id = m.id
                ORDER BY p.payment_date DESC";
        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }

    public static function countPendingPayments()
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT COUNT(*) FROM " . self::$table . " WHERE status = 'pending'");
        return $stmt->fetchColumn();
    }

    public static function countFailedPayments()
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT COUNT(*) FROM " . self::$table . " WHERE status = 'failed'");
        return $stmt->fetchColumn();
    }

    public static function getFailedPayments()
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT p.*, m.first_name, m.last_name, m.email as member_email
                FROM " . self::$table . " p
                JOIN members m ON p.member_id = m.id
                WHERE p.status = 'failed'
                ORDER BY p.payment_date DESC";
        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }
}
