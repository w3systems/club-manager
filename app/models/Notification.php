<?php
// app/models/Notification.php
namespace App\Models;

use App\Core\Database;

class Notification
{
    private static $table = 'notifications';

    public static function create($memberId, $type, $message, $deliveryMethodSent = 'in_app')
    {
        $db = Database::getInstance()->getConnection();
        $sql = "INSERT INTO " . self::$table . " (member_id, type, message, delivery_method_sent, is_read)
                VALUES (:member_id, :type, :message, :delivery_method_sent, 0)";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            'member_id' => $memberId,
            'type' => $type,
            'message' => $message,
            'delivery_method_sent' => $deliveryMethodSent
        ]);
    }

    public static function getNotificationsForMember($memberId)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM " . self::$table . " WHERE member_id = :member_id ORDER BY created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute(['member_id' => $memberId]);
        return $stmt->fetchAll();
    }

    public static function getLatestNotificationsForMember($memberId, $limit = 5)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM " . self::$table . " WHERE member_id = :member_id ORDER BY created_at DESC LIMIT :limit";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':member_id', $memberId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function markAsRead($notificationId)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "UPDATE " . self::$table . " SET is_read = 1 WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute(['id' => $notificationId]);
    }
}
