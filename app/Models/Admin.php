<?php

namespace App\Models;

use App\Core\Model;
use App\Models\Role;
use App\Models\Permission;

/**
 * Admin Model
 */
class Admin extends Model
{
    protected static string $table = 'admins';
    protected static array $fillable = [
        'first_name', 'last_name', 'email', 'password_hash', 'mobile'
    ];
    protected static array $hidden = ['password_hash'];
    
    // ADD THIS - Ensure ID is cast to integer
    protected static array $casts = [
        'id' => 'integer'
    ];
    
    /**
     * Find admin by email
     */
    public static function findByEmail(string $email): ?self
    {
        $sql = "SELECT * FROM " . self::$table . " WHERE email = ? LIMIT 1";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$email]);
        
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$row) {
            return null;
        }
        
        return self::createFromRow($row);
    }
    
    // ... rest of the methods remain the same ...
    
    /**
     * Get full name
     */
    public function getFullName(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
    
    /**
     * Get initials
     */
    public function getInitials(): string
    {
        return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
    }
    
    /**
     * Update last login time
     */
    public function updateLastLogin(): bool
    {
        $sql = "UPDATE " . self::$table . " SET updated_at = NOW() WHERE id = ?";
        $stmt = self::getConnection()->prepare($sql);
        return $stmt->execute([$this->id]);
    }
    
    /**
     * Check if admin has permission (placeholder for now)
     */
    public function hasPermission(string $permission): bool
    {
        // For now, return true for Super Admin
        // You'll implement full permission checking later
        return true;
    }
}