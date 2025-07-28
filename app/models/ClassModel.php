<?php
// app/models/ClassModel.php
namespace App\Models;

use App\Core\Database;
use Exception;
use DateTime;
use DateInterval;

class ClassModel
{
    private static $table = 'classes';

    public static function create($data)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "INSERT INTO " . self::$table . " (class_parent_id, name, description, class_type, frequency,
                original_start_date, original_end_date, instance_date_time, duration_minutes, capacity, auto_book)
                VALUES (:class_parent_id, :name, :description, :class_type, :frequency,
                :original_start_date, :original_end_date, :instance_date_time, :duration_minutes, :capacity, :auto_book)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'class_parent_id' => $data['class_parent_id'] ?? null,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'class_type' => $data['class_type'], // 'single', 'recurring_parent', 'recurring_instance'
            'frequency' => $data['frequency'] ?? null,
            'original_start_date' => $data['original_start_date'] ?? null, // For recurring_parent
            'original_end_date' => $data['original_end_date'] ?? null,     // For recurring_parent
            'instance_date_time' => $data['instance_date_time'] ?? null, // Actual datetime for single/instance
            'duration_minutes' => $data['duration_minutes'] ?? null,
            'capacity' => $data['capacity'] ?? null,
            'auto_book' => $data['auto_book'] ?? 0
        ]);
        $classId = $db->lastInsertId();

        // Associate with subscriptions if provided
        if (isset($data['subscription_ids']) && is_array($data['subscription_ids'])) {
            foreach ($data['subscription_ids'] as $subId) {
                ClassSubscription::addSubscriptionToClass($classId, $subId);
            }
        }
        return $classId;
    }

    public static function findById($id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM " . self::$table . " WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public static function getAllClasses()
    {
        $db = Database::getInstance()->getConnection();
        // Fetch all classes, including recurring parents and instances.
        // Might need filtering for display (e.g., only recurring parents for class definition list)
        $sql = "SELECT c.*,
                    GROUP_CONCAT(s.name SEPARATOR ', ') AS associated_subscriptions
                FROM " . self::$table . " c
                LEFT JOIN class_subscriptions cs ON c.id = cs.class_id
                LEFT JOIN subscriptions s ON cs.subscription_id = s.id
                GROUP BY c.id
                ORDER BY c.instance_date_time ASC, c.original_start_date ASC";
        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }

    public static function getAllClassesWithBookings()
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT c.*,
                    GROUP_CONCAT(DISTINCT CONCAT(m.first_name, ' ', m.last_name) SEPARATOR ';') as booked_members_names,
                    COUNT(cb.id) as current_bookings
                FROM " . self::$table . " c
                LEFT JOIN class_bookings cb ON c.id = cb.class_instance_id AND cb.status = 'booked'
                LEFT JOIN members m ON cb.member_id = m.id
                WHERE c.class_type IN ('single', 'recurring_instance') -- Only show bookable instances on calendar
                GROUP BY c.id
                ORDER BY c.instance_date_time ASC";
        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }

    public static function getClassesForSubscriptionBetweenDates($subscriptionId, $startDate, $endDate)
    {
        $db = Database::getInstance()->getConnection();
        // This needs to fetch relevant *instances* of classes that apply to the subscription
        // It could be single classes, or instances generated from a recurring parent.
        $sql = "SELECT c_instance.*
                FROM " . self::$table . " c_instance
                JOIN class_subscriptions cs ON c_instance.class_parent_id = cs.class_id OR (c_instance.class_type = 'single' AND c_instance.id = cs.class_id)
                WHERE cs.subscription_id = :subscription_id
                AND c_instance.class_type IN ('single', 'recurring_instance')
                AND c_instance.instance_date_time >= :start_date
                AND c_instance.instance_date_time <= :end_date
                ORDER BY c_instance.instance_date_time ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'subscription_id' => $subscriptionId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        return $stmt->fetchAll();
    }

    public static function getAvailableFreeTrialClasses()
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT c.*, s.name as subscription_name FROM " . self::$table . " c
                JOIN class_subscriptions cs ON c.id = cs.class_id
                JOIN subscriptions s ON cs.subscription_id = s.id
                WHERE s.free_trial_enabled = 1
                AND c.class_type IN ('single', 'recurring_parent') -- Free trial can apply to single class or a recurring series (member picks one instance)
                AND c.instance_date_time >= NOW() -- Only future single classes
                AND (c.original_end_date IS NULL OR c.original_end_date >= CURDATE()) -- Or future recurring series
                AND (c.capacity IS NULL OR (SELECT COUNT(*) FROM class_bookings WHERE class_instance_id = c.id AND status = 'booked') < c.capacity)";

        // Note: Age filtering is handled in ClassBooking::isClassBookable after a trial class is selected/attempted to book.
        $sql .= " GROUP BY c.id ORDER BY c.instance_date_time ASC, c.original_start_date ASC";

        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }

    public static function getUpcomingClasses($limit = 5)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT c.* FROM " . self::$table . " c
                WHERE c.instance_date_time >= NOW()
                AND c.class_type IN ('single', 'recurring_instance') -- Only actual bookable instances
                ORDER BY c.instance_date_time ASC
                LIMIT :limit";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Generates instances for a recurring class series for a given period.
     * This method would typically be called by a cron job.
     * @param int $classParentId The ID of the recurring_parent class.
     * @param string $startDate The start date for instance generation (YYYY-MM-DD).
     * @param string $endDate The end date for instance generation (YYYY-MM-DD).
     */
    public static function generateRecurringClassInstances($classParentId, $startDate, $endDate)
    {
        $db = Database::getInstance()->getConnection();
        $parentClass = self::findById($classParentId);

        if (!$parentClass || $parentClass['class_type'] !== 'recurring_parent') {
            throw new Exception("Class ID {$classParentId} is not a recurring parent.");
        }

        $currentDate = new DateTime($startDate);
        $endDateObj = new DateTime($endDate);
        $originalClassTime = (new DateTime($parentClass['instance_date_time']))->format('H:i:s'); // Get time from template

        $interval = new DateInterval('P1D'); // Default 1 day for daily
        switch ($parentClass['frequency']) {
            case 'daily':
                $interval = new DateInterval('P1D');
                break;
            case 'weekly':
                $interval = new DateInterval('P1W');
                break;
            case 'fortnightly':
                $interval = new DateInterval('P2W');
                break;
            case '4_weekly':
                $interval = new DateInterval('P4W');
                break;
            case 'monthly':
                $interval = new DateInterval('P1M');
                break;
            default:
                throw new Exception("Unsupported frequency: " . $parentClass['frequency']);
        }

        $instancesGenerated = 0;
        while ($currentDate <= $endDateObj) {
            // Combine current date with original class time
            $instanceDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $currentDate->format('Y-m-d') . ' ' . $originalClassTime);

            // Ensure the generated instance is not beyond the parent's original_end_date (if set)
            if ($parentClass['original_end_date'] && $instanceDateTime->format('Y-m-d') > $parentClass['original_end_date']) {
                break; // Stop generating if past parent's series end date
            }

            // Check if this specific instance already exists
            $stmt = $db->prepare("SELECT id FROM " . self::$table . " WHERE class_parent_id = :parent_id AND instance_date_time = :instance_date_time");
            $stmt->execute([
                'parent_id' => $classParentId,
                'instance_date_time' => $instanceDateTime->format('Y-m-d H:i:s')
            ]);

            if (!$stmt->fetch()) {
                // Instance does not exist, create it
                self::create([
                    'class_parent_id' => $classParentId,
                    'name' => $parentClass['name'] . ' - ' . $instanceDateTime->format('Y-m-d'), // Instance name
                    'description' => $parentClass['description'],
                    'class_type' => 'recurring_instance',
                    'instance_date_time' => $instanceDateTime->format('Y-m-d H:i:s'),
                    'duration_minutes' => $parentClass['duration_minutes'],
                    'capacity' => $parentClass['capacity'],
                    'auto_book' => $parentClass['auto_book'],
                    // No subscription_ids needed here, they link to the parent
                ]);
                $instancesGenerated++;
            }

            // Move to the next date based on frequency
            // Special handling for monthly/4-weekly to maintain day of month if not daily/weekly
            if ($parentClass['frequency'] === 'monthly') {
                $currentDate->add($interval);
            } else if ($parentClass['frequency'] === '4_weekly') {
                 // For 4-weekly, add 4 weeks
                $currentDate->add(new DateInterval('P4W'));
            } else {
                $currentDate->add($interval);
            }
        }
        return $instancesGenerated;
    }
}
