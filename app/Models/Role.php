<?php
// app/Models/Role.php

namespace App\Models;

use App\Core\Model;

/**
 * Role Model
 * Represents admin user roles
 */
class Role extends Model
{
    protected static string $table = 'roles';
    protected static array $fillable = ['name', 'description'];
    
    /**
     * Get all permissions for this role
     */
    public function getPermissions(): array
    {
        $sql = "SELECT p.* FROM permissions p
                INNER JOIN role_permissions rp ON p.id = rp.permission_id
                WHERE rp.role_id = ?
                ORDER BY p.name ASC";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$this->id]);
        
        $permissions = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $permissions[] = Permission::createFromRow($row);
        }
        
        return $permissions;
    }
    
    /**
     * Get admins with this role
     */
    public function getAdmins(): array
    {
        $sql = "SELECT a.* FROM admins a
                INNER JOIN admin_roles ar ON a.id = ar.admin_id
                WHERE ar.role_id = ?
                ORDER BY a.first_name, a.last_name";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$this->id]);
        
        $admins = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $admins[] = Admin::createFromRow($row);
        }
        
        return $admins;
    }
    
    /**
     * Sync permissions for this role
     */
    public function syncPermissions(array $permissionIds): bool
    {
        try {
            self::getConnection()->beginTransaction();
            
            // Remove existing permissions
            $sql = "DELETE FROM role_permissions WHERE role_id = ?";
            $stmt = self::getConnection()->prepare($sql);
            $stmt->execute([$this->id]);
            
            // Add new permissions
            if (!empty($permissionIds)) {
                $sql = "INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)";
                $stmt = self::getConnection()->prepare($sql);
                
                foreach ($permissionIds as $permissionId) {
                    $stmt->execute([$this->id, $permissionId]);
                }
            }
            
            self::getConnection()->commit();
            return true;
            
        } catch (\Exception $e) {
            self::getConnection()->rollback();
            return false;
        }
    }
    
    /**
     * Check if role has permission
     */
    public function hasPermission(string $permissionName): bool
    {
        $sql = "SELECT COUNT(*) FROM permissions p
                INNER JOIN role_permissions rp ON p.id = rp.permission_id
                WHERE rp.role_id = ? AND p.name = ?";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$this->id, $permissionName]);
        
        return $stmt->fetchColumn() > 0;
    }
}

// =====================================

<?php
// app/Models/Permission.php

namespace App\Models;

use App\Core\Model;

/**
 * Permission Model
 * Represents system permissions
 */
class Permission extends Model
{
    protected static string $table = 'permissions';
    protected static array $fillable = ['name', 'description'];
    
    /**
     * Get all permissions grouped by category
     */
    public static function getAllGrouped(): array
    {
        $permissions = self::all();
        $grouped = [];
        
        foreach ($permissions as $permission) {
            $category = explode('_', $permission->name)[0];
            $grouped[$category][] = $permission;
        }
        
        return $grouped;
    }
    
    /**
     * Get roles that have this permission
     */
    public function getRoles(): array
    {
        $sql = "SELECT r.* FROM roles r
                INNER JOIN role_permissions rp ON r.id = rp.role_id
                WHERE rp.permission_id = ?
                ORDER BY r.name ASC";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$this->id]);
        
        $roles = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $roles[] = Role::createFromRow($row);
        }
        
        return $roles;
    }
    
    /**
     * Get permission category
     */
    public function getCategory(): string
    {
        return ucfirst(explode('_', $this->name)[0]);
    }
    
    /**
     * Get permission display name
     */
    public function getDisplayName(): string
    {
        return ucwords(str_replace('_', ' ', $this->name));
    }
}
