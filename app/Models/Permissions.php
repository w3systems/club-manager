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
        $permissions = self::all(); // Assuming a static 'all' method exists in your base Model
        $grouped = [];
        
        foreach ($permissions as $permission) {
            $category = explode('_', $permission->name)[0];
            $grouped[ucfirst($category)][] = $permission;
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