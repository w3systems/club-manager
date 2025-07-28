<?php
// app/models/Permission.php
namespace App\Models;

use App\Core\Database;

class Permission extends BaseModel
{
    private static $table = 'permissions';

    public static function create($data)
    {
		//print_r($name);
        $db = Database::getInstance()->getConnection();
        $tableName = 'permissions'; // The name of your permissions table

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$tableName} (name,description) VALUES ({$placeholders})";

        $stmt = $db->prepare($sql);
        $stmt->execute(array_values($data));

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
}
