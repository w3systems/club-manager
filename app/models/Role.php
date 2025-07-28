<?php
// app/models/Role.php
namespace App\Models;

use App\Core\Database;

class Role extends BaseModel
{
    private static $table = 'roles';

    public static function create($name, $description = null)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "INSERT INTO " . self::$table . " (name, description) VALUES (:name, :description)";
        $stmt = $db->prepare($sql);
        $stmt->execute(['name' => $name, 'description' => $description]);
        return $db->lastInsertId();
    }

    public static function findById($id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM " . self::$table . " WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public static function findByName($name)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM " . self::$table . " WHERE name = :name");
        $stmt->execute(['name' => $name]);
        return $stmt->fetch();
    }

    public static function getAll()
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM " . self::$table . " ORDER BY name ASC");
        return $stmt->fetchAll();
    }

	// Gets all roles and also fetches a comma-separated list of permission IDs for each role.
	public static function getAllWithPermissions()
	{
		$db = Database::getInstance();
		$sql = "
			SELECT 
				r.*, 
				GROUP_CONCAT(rp.permission_id) as permission_ids
			FROM roles r
			LEFT JOIN role_permissions rp ON r.id = rp.role_id
			GROUP BY r.id
		";
		$stmt = $db->query($sql);
		return $stmt->fetchAll(\PDO::FETCH_ASSOC);
	}
}
