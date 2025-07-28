<?php
// app/models/Setting.php
namespace App\Models;

use App\Core\Database;

class Setting
{
    private static $table = 'settings';

    public static function get($key)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT setting_value FROM " . self::$table . " WHERE setting_key = :key");
        $stmt->execute(['key' => $key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : null;
    }

    public static function set($key, $value, $description = null, $dataType = 'string')
    {
        $db = Database::getInstance()->getConnection();
        $sql = "INSERT INTO " . self::$table . " (setting_key, setting_value, description, data_type) VALUES (:key, :value, :description, :data_type)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), description = VALUES(description), data_type = VALUES(data_type)";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            'key' => $key,
            'value' => $value,
            'description' => $description,
            'data_type' => $dataType
        ]);
    }

    public static function getAllSettings()
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM " . self::$table);
        return $stmt->fetchAll();
    }

    public static function updateSettings($data)
    {
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        try {
            foreach ($data as $key => $value) {
                // Fetch existing setting to preserve description and data_type if not provided in $data
                $existingSetting = self::getSettingRow($key);
                self::set(
                    $key,
                    $value,
                    $existingSetting['description'] ?? null,
                    $existingSetting['data_type'] ?? 'string'
                );
            }
            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function getSettingRow($key) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM " . self::$table . " WHERE setting_key = :key");
        $stmt->execute(['key' => $key]);
        return $stmt->fetch();
    }
}
