<?php
// app/models/ClassBooking.php
namespace App\Models;

use App\Core\Database;
use Exception;
use App\Helpers\functions as Helpers; // Use alias to avoid function name conflicts

class ClassBooking
{
    private static $table = 'class_bookings';

    public static function bookFreeTrial($memberId, $classInstanceId)
    {
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        try {
            $class = ClassModel::findById($classInstanceId);
            if (!$class || ($class['class_type'] !== 'single' && $class['class_type'] !== 'recurring_instance')) {
                throw new Exception("Invalid class instance for booking.");
            }
            if (!self::isClassBookable($classInstanceId, $memberId)) {
                throw new Exception("Class is not bookable (capacity, age, or eligibility issues).");
            }
            if (self::hasHadFreeTrial($memberId)) {
                throw new Exception("You have already had a free trial.");
            }

            $sql = "INSERT INTO " . self::$table . " (member_id, class_instance_id, booking_date, status, is_free_trial)
                    VALUES (:member_id, :class_instance_id, NOW(), 'booked', 1)";
            $stmt = $db->prepare($sql);
            $stmt->execute(['member_id' => $memberId, 'class_instance_id' => $classInstanceId]);

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function bookAutoClass($memberId, $classInstanceId)
    {
        $db = Database::getInstance()->getConnection();
        // Check if already booked
        $existing = $db->prepare("SELECT id FROM " . self::$table . " WHERE member_id = :member_id AND class_instance_id = :class_instance_id AND status = 'booked'");
        $existing->execute(['member_id' => $memberId, 'class_instance_id' => $classInstanceId]);
        if ($existing->fetch()) {
            return; // Already booked
        }

        $sql = "INSERT INTO " . self::$table . " (member_id, class_instance_id, booking_date, status, is_auto_booked)
                VALUES (:member_id, :class_instance_id, NOW(), 'booked', 1)";
        $stmt = $db->prepare($sql);
        return $stmt->execute(['member_id' => $memberId, 'class_instance_id' => $classInstanceId]);
    }

    public static function bookManualClass($memberId, $classInstanceId)
    {
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        try {
            $class = ClassModel::findById($classInstanceId);
            if (!$class || ($class['class_type'] !== 'single' && $class['class_type'] !== 'recurring_instance')) {
                throw new Exception("Invalid class instance for booking.");
            }
            if (!self::isClassBookable($classInstanceId, $memberId)) {
                throw new Exception("Class is not bookable (capacity, age, or eligibility issues).");
            }

            // Check if member has appropriate active subscription for this class
            if (!self::canMemberBookClass($memberId, $classInstanceId)) {
                 throw new Exception("You do not have a valid subscription to book this class.");
            }

            $sql = "INSERT INTO " . self::$table . " (member_id, class_instance_id, booking_date, status)
                    VALUES (:member_id, :class_instance_id, NOW(), 'booked')";
            $stmt = $db->prepare($sql);
            $stmt->execute(['member_id' => $memberId, 'class_instance_id' => $classInstanceId]);

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function getMemberBookedClasses($memberId)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT cb.*, c.name as class_name, c.description, c.instance_date_time
                FROM " . self::$table . " cb
                JOIN classes c ON cb.class_instance_id = c.id
                WHERE cb.member_id = :member_id AND cb.status = 'booked'
                ORDER BY c.instance_date_time ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute(['member_id' => $memberId]);
        return $stmt->fetchAll();
    }

    public static function getUpcomingClassesForMember($memberId, $limit = 5)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT cb.*, c.name as class_name, c.description, c.instance_date_time, c.duration_minutes
                FROM " . self::$table . " cb
                JOIN classes c ON cb.class_instance_id = c.id
                WHERE cb.member_id = :member_id AND cb.status = 'booked' AND c.instance_date_time >= NOW()
                ORDER BY c.instance_date_time ASC LIMIT :limit";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':member_id', $memberId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function isClassBookable($classInstanceId, $memberId)
    {
        $class = ClassModel::findById($classInstanceId);
        if (!$class || ($class['class_type'] !== 'single' && $class['class_type'] !== 'recurring_instance')) {
            return false; // Not a bookable class instance
        }

        // Check if class is in the past
        if (new \DateTime($class['instance_date_time']) < new \DateTime()) {
            return false;
        }

        // Check capacity
        if ($class['capacity'] !== null && self::countBookingsForClass($classInstanceId) >= $class['capacity']) {
            return false; // Class full
        }

        // Check member age against associated subscriptions' age ranges (via parent class if recurring instance)
        $member = Member::findById($memberId);
        if ($member) {
            $memberAge = Helpers\calculateAge($member['date_of_birth']);
            $eligibleByAge = false;

            // Get the class (or its parent for instances) and then its associated subscriptions
            $targetClassIdForSubscriptions = ($class['class_type'] === 'recurring_instance' && $class['class_parent_id']) ? $class['class_parent_id'] : $class['id'];
            $subscriptionsForClass = ClassSubscription::getSubscriptionsForClass($targetClassIdForSubscriptions);

            if (empty($subscriptionsForClass)) {
                // If a class has no associated subscriptions, it might be free for all or requires manual admin booking
                // For this app, assume it's not bookable by member if no subscription applies
                return false;
            }

            foreach ($subscriptionsForClass as $sub) {
                if (($sub['min_age'] === null || $memberAge >= $sub['min_age']) &&
                    ($sub['max_age'] === null || $memberAge <= $sub['max_age'])) {
                    $eligibleByAge = true;
                    break;
                }
            }
            if (!$eligibleByAge) {
                return false; // Not eligible by age
            }
        } else {
            // If no member found (e.g., for free trial before full registration), age cannot be checked.
            // This might mean only free trials for new registrations are allowed,
            // or we'd need more logic here for truly public bookable classes.
        }

        return true;
    }

    public static function countBookingsForClass($classInstanceId)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM " . self::$table . " WHERE class_instance_id = :class_instance_id AND status = 'booked'");
        $stmt->execute(['class_instance_id' => $classInstanceId]);
        return $stmt->fetchColumn();
    }

    public static function hasHadFreeTrial($memberId)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM " . self::$table . " WHERE member_id = :member_id AND is_free_trial = 1 AND status = 'booked'");
        $stmt->execute(['member_id' => $memberId]);
        return $stmt->fetchColumn() > 0;
    }

    public static function getMemberTrialBookings($memberId)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM " . self::$table . " WHERE member_id = :member_id AND is_free_trial = 1 AND status = 'booked'");
        $stmt->execute(['member_id' => $memberId]);
        return $stmt->fetchAll();
    }

    public static function canMemberBookClass($memberId, $classInstanceId)
    {
        $db = Database::getInstance()->getConnection();
        $class = ClassModel::findById($classInstanceId);

        if (!$class) {
            return false;
        }

        // For recurring instances, check against the parent class's subscriptions
        $targetClassIdForSubscriptions = ($class['class_type'] === 'recurring_instance' && $class['class_parent_id']) ? $class['class_parent_id'] : $class['id'];


        $sql = "SELECT COUNT(ms.id)
                FROM member_subscriptions ms
                JOIN class_subscriptions cs ON ms.subscription_id = cs.subscription_id
                WHERE ms.member_id = :member_id
                AND cs.class_id = :class_id_for_sub_check
                AND ms.status = 'active'";
        $stmt = $db->prepare($sql);
        $stmt->execute(['member_id' => $memberId, 'class_id_for_sub_check' => $targetClassIdForSubscriptions]);
        return $stmt->fetchColumn() > 0;
    }
}
