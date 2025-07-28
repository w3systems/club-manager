<?php
// app/models/Member.php
namespace App\Models;

use App\Core\Database;

class Member
{
    private static $table = 'members';

    public static function create($data)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "INSERT INTO " . self::$table . " (first_name, last_name, email, password_hash, mobile, date_of_birth,
                consent_photography, consent_first_aid, terms_conditions_acceptance,
                emergency_contact_name, emergency_contact_number, emergency_contact_relationship,
                parent_guardian_email, parent_guardian_mobile)
                VALUES (:first_name, :last_name, :email, :password_hash, :mobile, :date_of_birth,
                :consent_photography, :consent_first_aid, :terms_conditions_acceptance,
                :emergency_contact_name, :emergency_contact_number, :emergency_contact_relationship,
                :parent_guardian_email, :parent_guardian_mobile)";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password_hash' => $data['password_hash'],
            'mobile' => $data['mobile'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'consent_photography' => $data['consent_photography'] ?? 0,
            'consent_first_aid' => $data['consent_first_aid'] ?? 0,
            'terms_conditions_acceptance' => $data['terms_conditions_acceptance'] ?? 0,
            'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
            'emergency_contact_number' => $data['emergency_contact_number'] ?? null,
            'emergency_contact_relationship' => $data['emergency_contact_relationship'] ?? null,
            'parent_guardian_email' => $data['parent_guardian_email'] ?? null,
            'parent_guardian_mobile' => $data['parent_guardian_mobile'] ?? null,
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

    public static function findByStripeCustomerId($customerId)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM " . self::$table . " WHERE stripe_customer_id = :stripe_customer_id");
        $stmt->execute(['stripe_customer_id' => $customerId]);
        return $stmt->fetch();
    }

    public static function update($id, $data)
    {
        $db = Database::getInstance()->getConnection();
        $updateFields = [];
        $params = ['id' => $id];

        foreach ($data as $key => $value) {
            // Prevent updating sensitive fields directly through this generic update
            if (in_array($key, ['id', 'password_hash', 'stripe_customer_id'])) {
                continue;
            }
            $updateFields[] = "`{$key}` = :{$key}";
            $params[$key] = $value;
        }
        if (empty($updateFields)) {
            return false; // No fields to update
        }

        $sql = "UPDATE " . self::$table . " SET " . implode(', ', $updateFields) . " WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }

    public static function getStripeCustomerId($memberId)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT stripe_customer_id FROM " . self::$table . " WHERE id = :id");
        $stmt->execute(['id' => $memberId]);
        $result = $stmt->fetch();
        return $result['stripe_customer_id'] ?? null;
    }

    public static function updateStripeCustomerId($memberId, $customerId)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE " . self::$table . " SET stripe_customer_id = :stripe_customer_id WHERE id = :id");
        return $stmt->execute([
            'stripe_customer_id' => $customerId,
            'id' => $memberId
        ]);
    }

    public static function countAll()
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT COUNT(*) FROM " . self::$table);
        return $stmt->fetchColumn();
    }

    public static function getAll()
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM " . self::$table . " ORDER BY last_name, first_name ASC");
        return $stmt->fetchAll();
    }
}
