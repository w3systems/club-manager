<?php

namespace App\Models;

use App\Core\Model;
use DateTime;
use DateInterval;
use DatePeriod;

class ClassModel extends Model
{
    protected static string $table = 'classes';

    protected static array $fillable = [
        'name', 'description', 'class_type', 'frequency', 'start_time', 'day_of_week',
        'original_start_date', 'original_end_date', 'duration_minutes', 'capacity', 'auto_book',
        'session_price', 'allow_booking_outside_subscription'
    ];

    protected static array $casts = [
        'duration_minutes' => 'integer',
        'capacity' => 'integer',
        'auto_book' => 'boolean',
        'allow_booking_outside_subscription' => 'boolean',
        'day_of_week' => 'integer',
        'session_price' => 'float',
        'original_start_date' => 'datetime',
        'original_end_date' => 'datetime',
    ];

    public function getSubscriptionIds(): array
    {
        $sql = "SELECT subscription_id FROM class_subscriptions WHERE class_id = ?";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function syncSubscriptions(array $subscriptionIds): void
    {
        $db = self::getConnection();
        
        $stmt = $db->prepare("DELETE FROM class_subscriptions WHERE class_id = ?");
        $stmt->execute([$this->id]);
        
        if (!empty($subscriptionIds)) {
            $stmt = $db->prepare("INSERT INTO class_subscriptions (class_id, subscription_id) VALUES (?, ?)");
            foreach ($subscriptionIds as $subId) {
                $stmt->execute([$this->id, $subId]);
            }
        }
    }

/**
     * Generate recurring instances for this class within a specific date range.
     */
    public function generateInstances(DateTime $startDate, DateTime $endDate): void
    {
        if ($this->class_type !== 'recurring_parent') {
            // ... (logic for single classes remains the same)
            return;
        }

        $db = self::getConnection();
        
        // Delete future, unbooked instances within the generation range
        $stmt = $db->prepare("
            DELETE ci FROM class_instances ci
            LEFT JOIN class_bookings cb ON ci.id = cb.class_instance_id
            WHERE ci.class_parent_id = ? 
              AND ci.instance_date_time >= ? 
              AND ci.instance_date_time <= ?
              AND cb.id IS NULL
        ");
        $stmt->execute([$this->id, $startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s')]);

        $time = $this->start_time;
        $dayOfWeek = $this->day_of_week;

        $interval = match($this->frequency) {
            'weekly' => new \DateInterval('P1W'),
            'fortnightly' => new \DateInterval('P2W'),
            'monthly' => new \DateInterval('P1M'),
            default => new \DateInterval('P1D'),
        };

        // Find the first valid occurrence on or after the start date
        $currentDate = clone $startDate;
        while ($currentDate->format('N') != $dayOfWeek) {
            $currentDate->modify('+1 day');
        }
        
        // **THE FIX**: Loop through the dates and stop once we go past the end date.
        while ($currentDate <= $endDate) {
            $instanceDateTime = $currentDate->format('Y-m-d') . ' ' . $time;
            
            $stmt = $db->prepare("SELECT id FROM class_instances WHERE class_parent_id = ? AND instance_date_time = ?");
            $stmt->execute([$this->id, $instanceDateTime]);
            
            if (!$stmt->fetch()) {
                ClassInstance::create([
                    'class_parent_id' => $this->id,
                    'instance_date_time' => $instanceDateTime
                ]);
            }
            
            // Move to the next potential date in the sequence
            $currentDate->add($interval);
        }
    }

    /**
     * Determines the "schedule lock" of a class based on its existing subscriptions.
     */
    /**
     * Determines the "schedule lock" of a class based on its existing subscriptions.
     * Returns the schedule details if locked, or null if it's not linked to anything.
     */
    public function getScheduleLockType(): ?array
    {
        $db = self::getConnection();
        $stmt = $db->prepare("
            SELECT DISTINCT s.type, s.term_length, s.term_unit, s.fixed_start_day
            FROM subscriptions s
            INNER JOIN class_subscriptions cs ON s.id = cs.subscription_id
            WHERE cs.class_id = ?
        ");
        $stmt->execute([$this->id]);
        $linkedSubscriptions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($linkedSubscriptions)) {
            return null; // Not locked, can be linked to anything.
        }

        // For this implementation, we lock the class to the schedule type of the first subscription it was linked to.
        $firstSub = $linkedSubscriptions[0];

        return [
            'type' => $firstSub['type'],
            'term_length' => $firstSub['term_length'],
            'term_unit' => $firstSub['term_unit'],
            'fixed_start_day' => $firstSub['fixed_start_day'],
        ];
    }
}