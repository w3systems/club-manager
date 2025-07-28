<?php
// app/Models/Setting.php

namespace App\Models;

use App\Core\Model;

/**
 * Setting Model
 * Represents application settings
 */
class Setting extends Model
{
    protected static string $table = 'settings';
    protected static array $fillable = [
        'setting_key', 'setting_value', 'description', 'data_type'
    ];
    
    private static array $cache = [];
    
    /**
     * Get setting value by key
     */
    public static function get(string $key, $default = null)
    {
        // Check cache first
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }
        
        $sql = "SELECT setting_value, data_type FROM settings WHERE setting_key = ? LIMIT 1";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$key]);
        
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$row) {
            self::$cache[$key] = $default;
            return $default;
        }
        
        // Cast value based on data type
        $value = self::castValue($row['setting_value'], $row['data_type']);
        self::$cache[$key] = $value;
        
        return $value;
    }
    
    /**
     * Set setting value
     */
    public static function set(string $key, $value, string $dataType = 'string'): bool
    {
        // Convert value to string for storage
        $stringValue = self::valueToString($value, $dataType);
        
        $sql = "INSERT INTO settings (setting_key, setting_value, data_type) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE setting_value = ?, data_type = ?";
        $stmt = self::getConnection()->prepare($sql);
        $result = $stmt->execute([$key, $stringValue, $dataType, $stringValue, $dataType]);
        
        if ($result) {
            self::$cache[$key] = $value;
        }
        
        return $result;
    }
    
    /**
     * Get all settings as key-value pairs
     */
    public static function getAll(): array
    {
        $sql = "SELECT setting_key, setting_value, data_type FROM settings";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute();
        
        $settings = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = self::castValue($row['setting_value'], $row['data_type']);
        }
        
        return $settings;
    }
    
    /**
     * Get settings by prefix
     */
    public static function getByPrefix(string $prefix): array
    {
        $sql = "SELECT setting_key, setting_value, data_type FROM settings WHERE setting_key LIKE ?";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$prefix . '%']);
        
        $settings = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = self::castValue($row['setting_value'], $row['data_type']);
        }
        
        return $settings;
    }
    
    /**
     * Clear settings cache
     */
    public static function clearCache(): void
    {
        self::$cache = [];
    }
    
    /**
     * Cast value based on data type
     */
    private static function castValue($value, string $dataType)
    {
        return match($dataType) {
            'int', 'integer' => (int) $value,
            'bool', 'boolean' => (bool) $value,
            'float' => (float) $value,
            'json' => json_decode($value, true),
            'string' => (string) $value,
            default => $value
        };
    }
    
    /**
     * Convert value to string for storage
     */
    private static function valueToString($value, string $dataType): string
    {
        return match($dataType) {
            'json' => json_encode($value),
            'bool', 'boolean' => $value ? '1' : '0',
            default => (string) $value
        };
    }
}
