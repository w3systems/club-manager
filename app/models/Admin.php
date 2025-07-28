<?php
// app/models/Admin.php
namespace App\Models;

use App\Core\Database;

class Admin
{
    private static $table = 'admins';

    public static function create($data)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "INSERT INTO " . self::$table . " (first_name, last_name, email, password_hash, mobile)
                VALUES (:first_name, :last_name, :email, :password_hash, :mobile)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password_hash' => $data['password_hash'],
            'mobile' => $data['mobile'] ?? null
        ]);
        return $db->lastInsertId();
    }

    public static function findById($id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM " . self::$table . " WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public static function findByEmail($email)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM " . self::$table . " WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public static function update($id, $data)
    {
        $db = Database::getInstance()->getConnection();
        $updateFields = [];
        $params = ['id' => $id];

        foreach ($data as $key => $value) {
            if (in_array($key, ['id', 'password_hash'])) { // Prevent direct update of sensitive fields
                continue;
            }
            $updateFields[] = "`{$key}` = :{$key}";
            $params[$key] = $value;
        }
        if (empty($updateFields)) {
            return false;
        }

        $sql = "UPDATE " . self::$table . " SET " . implode(', ', $updateFields) . " WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }

    public static function getAll()
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT a.*, GROUP_CONCAT(r.name SEPARATOR ', ') AS roles
                FROM " . self::$table . " a
                LEFT JOIN admin_roles ar ON a.id = ar.admin_id
                LEFT JOIN roles r ON ar.role_id = r.id
                GROUP BY a.id
                ORDER BY a.last_name, a.first_name ASC";
        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }
}
