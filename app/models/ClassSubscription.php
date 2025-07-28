<?php
// app/models/ClassSubscription.php
namespace App\Models;

use App\Core\Database;

class ClassSubscription
{
    private static $table = 'class_subscriptions';

    public static function addSubscriptionToClass($classId, $subscriptionId)
    {
        $db = Database::getInstance()->getConnection();
        // Use INSERT IGNORE to prevent duplicate entries if already linked
        $sql = "INSERT IGNORE INTO " . self::$table . " (class_id, subscription_id) VALUES (:class_id, :subscription_id)";
        $stmt = $db->prepare($sql);
        return $stmt->execute(['class_id' => $classId, 'subscription_id' => $subscriptionId]);
    }

    public static function removeSubscriptionFromClass($classId, $subscriptionId)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "DELETE FROM " . self::$table . " WHERE class_id = :class_id AND subscription_id = :subscription_id";
        $stmt = $db->prepare($sql);
        return $stmt->execute(['class_id' => $classId, 'subscription_id' => $subscriptionId]);
    }

    public static function getSubscriptionsForClass($classId)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT s.* FROM " . self::$table . " cs JOIN subscriptions s ON cs.subscription_id = s.id WHERE cs.class_id = :class_id";
        $stmt = $db->prepare($sql);
        $stmt->execute(['class_id' => $classId]);
        return $stmt->fetchAll();
    }

    public static function getClassesForSubscription($subscriptionId)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT c.* FROM " . self::$table . " cs JOIN classes c ON cs.class_id = c.id WHERE cs.subscription_id = :subscription_id";
        $stmt = $db->prepare($sql);
        $stmt->execute(['subscription_id' => $subscriptionId]);
        return $stmt->fetchAll();
    }
}
