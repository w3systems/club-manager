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

/**
     * Get all roles assigned to this admin.
     */
    public function getRoles(): array
    {
        $sql = "SELECT r.* FROM roles r
                INNER JOIN admin_roles ar ON r.id = ar.role_id
                WHERE ar.admin_id = ?";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$this->id]);
        
        $roles = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $roles[] = Role::createFromRow($row);
        }
        return $roles;
    }

    /**
     * Sync roles for this admin.
     */
    public function syncRoles(array $roleIds): void
    {
        $db = self::getConnection();
        $db->beginTransaction();
        try {
            // Delete existing roles
            $stmt = $db->prepare("DELETE FROM admin_roles WHERE admin_id = ?");
            $stmt->execute([$this->id]);

            // Add new roles
            if (!empty($roleIds)) {
                $stmt = $db->prepare("INSERT INTO admin_roles (admin_id, role_id) VALUES (?, ?)");
                foreach ($roleIds as $roleId) {
                    $stmt->execute([$this->id, $roleId]);
                }
            }
            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e; // Re-throw exception to be caught by controller
        }
    }

}