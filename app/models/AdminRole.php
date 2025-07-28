<?php
// app/models/AdminRole.php
namespace App\Models;

use App\Core\Database;

class AdminRole
{
    private static $table = 'admin_roles';

    public static function assignRole($adminId, $roleId)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "INSERT IGNORE INTO " . self::$table . " (admin_id, role_id) VALUES (:admin_id, :role_id)";
        $stmt = $db->prepare($sql);
        return $stmt->execute(['admin_id' => $adminId, 'role_id' => $roleId]);
    }

    public static function removeRole($adminId, $roleId)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "DELETE FROM " . self::$table . " WHERE admin_id = :admin_id AND role_id = :role_id";
        $stmt = $db->prepare($sql);
        return $stmt->execute(['admin_id' => $adminId, 'role_id' => $roleId]);
    }

    public static function getRolesForAdmin($adminId)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT r.* FROM " . self::$table . " ar JOIN roles r ON ar.role_id = r.id WHERE ar.admin_id = :admin_id";
        $stmt = $db->prepare($sql);
        $stmt->execute(['admin_id' => $adminId]);
        return $stmt->fetchAll();
    }

    public static function hasPermission($adminId, $permissionName)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT COUNT(rp.permission_id)
                FROM admin_roles ar
                JOIN role_permissions rp ON ar.role_id = rp.role_id
                JOIN permissions p ON rp.permission_id = p.id
                WHERE ar.admin_id = :admin_id AND p.name = :permission_name";
        $stmt = $db->prepare($sql);
        $stmt->execute(['admin_id' => $adminId, 'permission_name' => $permissionName]);
        return $stmt->fetchColumn() > 0;
    }


	/**
	 * Gets an array of role IDs assigned to a specific admin user.
	 *
	 * @param int $adminId The ID of the admin.
	 * @return array An array of role IDs.
	 */
	public static function getRoleIdsForAdmin($adminId)
	{
		$db = Database::getInstance()->getConnection();
		$stmt = $db->prepare("SELECT role_id FROM admin_roles WHERE admin_id = ?");
		$stmt->execute([$adminId]);
		// Use PDO::FETCH_COLUMN to get a simple, flat array of IDs
		return $stmt->fetchAll(\PDO::FETCH_COLUMN);
	}

	/**
	 * Updates the roles for a specific admin user.
	 * Deletes all existing roles and assigns the new set.
	 *
	 * @param int $adminId The ID of the admin to update.
	 * @param array $roleIds An array of role IDs to assign.
	 */
	public static function updateRolesForAdmin($adminId, $roleIds)
	{
		$db = Database::getInstance()->getConnection();
		$db->beginTransaction();
		try {
			// 1. Delete all existing roles for this admin
			$stmt = $db->prepare("DELETE FROM admin_roles WHERE admin_id = ?");
			$stmt->execute([$adminId]);

			// 2. Insert the new set of roles
			if (!empty($roleIds)) {
				$stmt = $db->prepare("INSERT INTO admin_roles (admin_id, role_id) VALUES (?, ?)");
				foreach ($roleIds as $roleId) {
					$stmt->execute([$adminId, (int)$roleId]);
				}
			}
			$db->commit();
		} catch (\Exception $e) {
			$db->rollBack();
			throw $e;
		}
	}
}
