<?php
// app/models/NotificationSetting.php
namespace App\Models;

use App\Core\Database;

class NotificationSetting
{
    private static $table = 'notification_settings';

    /**
     * Gets notification settings for a specific member.
     * @param int $memberId
     * @return array Associative array of notification_type => delivery_method_preference
     */
    public static function getNotificationSettingsForMember($memberId)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT notification_type, delivery_method_preference FROM " . self::$table . " WHERE member_id = :member_id";
        $stmt = $db->prepare($sql);
        $stmt->execute(['member_id' => $memberId]);
        // Fetch as key-value pairs
        $settings = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $settings[$row['notification_type']] = $row['delivery_method_preference'];
        }
        return $settings;
    }

    /**
     * Updates notification settings for a member.
     * @param int $memberId
     * @param array $settings Associative array of notification_type => delivery_method_preference (e.g., ['payment_received' => 'in_app,email'])
     * @return bool
     */
    public static function updateNotificationSettings($memberId, array $settings)
    {
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        try {
            // Delete existing settings for the user
            $sqlDelete = "DELETE FROM " . self::$table . " WHERE member_id = :member_id";
            $stmtDelete = $db->prepare($sqlDelete);
            $stmtDelete->execute(['member_id' => $memberId]);

            // Insert new settings
            $sqlInsert = "INSERT INTO " . self::$table . " (member_id, notification_type, delivery_method_preference) VALUES (:member_id, :type, :method)";
            $stmtInsert = $db->prepare($sqlInsert);

            foreach ($settings as $type => $deliveryMethods) {
                // $deliveryMethods could be an array of values from a checkbox group, or a string
                $methodString = is_array($deliveryMethods) ? implode(',', $deliveryMethods) : (string)$deliveryMethods;
                $stmtInsert->execute([
                    'member_id' => $memberId,
                    'type' => $type,
                    'method' => $methodString
                ]);
            }
            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Get a single notification preference for a type.
     * @param int $memberId
     * @param string $notificationType
     * @return string|null Comma-separated methods, or null if not set.
     */
    public static function getPreference($memberId, $notificationType)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT delivery_method_preference FROM " . self::$table . " WHERE member_id = :member_id AND notification_type = :type";
        $stmt = $db->prepare($sql);
        $stmt->execute(['member_id' => $memberId, 'type' => $notificationType]);
        $result = $stmt->fetch();
        return $result['delivery_method_preference'] ?? null;
    }
}
