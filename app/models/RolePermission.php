<?php
// app/models/RolePermission.php
namespace App\Models;

use App\Core\Database;

class RolePermission
{
    private static $table = 'role_permissions';

    public static function assignPermission($roleId, $permissionId)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "INSERT IGNORE INTO " . self::$table . " (role_id, permission_id) VALUES (:role_id, :permission_id)";
        $stmt = $db->prepare($sql);
        return $stmt->execute(['role_id' => $roleId, 'permission_id' => $permissionId]);
    }

    public static function removePermission($roleId, $permissionId)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "DELETE FROM " . self::$table . " WHERE role_id = :role_id AND permission_id = :permission_id";
        $stmt = $db->prepare($sql);
        return $stmt->execute(['role_id' => $roleId, 'permission_id' => $permissionId]);
    }

    public static function getPermissionsForRole($roleId)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT p.* FROM " . self::$table . " rp JOIN permissions p ON rp.permission_id = p.id WHERE rp.role_id = :role_id";
        $stmt = $db->prepare($sql);
        $stmt->execute(['role_id' => $roleId]);
        return $stmt->fetchAll();
    }

	// Updates permissions for a role by deleting all existing ones and adding the new set.
	public static function updatePermissionsForRole($roleId, $permissionIds)
	{
		$db = Database::getInstance();
		
		// It's safest to wrap this in a transaction
		$db->beginTransaction();
		
		try {
			// 1. Delete all existing permissions for this role
			$stmt = $db->prepare("DELETE FROM role_permissions WHERE role_id = ?");
			$stmt->execute([$roleId]);
			
			// 2. Insert the new permissions
			if (!empty($permissionIds)) {
				$stmt = $db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
				foreach ($permissionIds as $permissionId) {
					$stmt->execute([$roleId, (int)$permissionId]);
				}
			}
			
			$db->commit();
		} catch (\Exception $e) {
			$db->rollBack();
			throw $e; // Re-throw the exception to be caught by the controller
		}
	}
}
