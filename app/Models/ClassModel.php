<?php
// app/Models/ClassModel.php

namespace App\Models;

use App\Core\Model;

/**
 * Class Model
 * Represents classes/sessions available to members
 */
class ClassModel extends Model
{
    protected static string $table = 'classes';
    protected static array $fillable = [
        'class_parent_id', 'name', 'description', 'class_type', 'frequency',
        'original_start_date', 'original_end_date', 'instance_date_time',
        'duration_minutes', 'capacity', 'auto_book'
    ];
    protected static array $casts = [
        'class_parent_id' => 'integer',
        'duration_minutes' => 'integer',
        'capacity' => 'integer',
        'auto_book' => 'boolean',
        'original_start_date' => 'datetime',
        'original_end_date' => 'datetime',
        'instance_date_time' => 'datetime'
    ];
    
    /**
     * Get upcoming class instances
     */
    public static function getUpcoming(int $limit = 50): array
    {
        $sql = "SELECT * FROM " . self::$table . " 
                WHERE class_type IN ('single', 'recurring_instance') 
                AND instance_date_time >= NOW()
                ORDER BY instance_date_time ASC
                LIMIT ?";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$limit]);
        
        $classes = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $classes[] = self::createFromRow($row);
        }
        
        return $classes;
    }
    
    /**
     * Get classes for date range
     */
    public static function getForDateRange(\DateTime $startDate, \DateTime $endDate): array
    {
        $sql = "SELECT * FROM " . self::$table . " 
                WHERE class_type IN ('single', 'recurring_instance') 
                AND instance_date_time BETWEEN ? AND ?
                ORDER BY instance_date_time ASC";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([
            $startDate->format('Y-m-d H:i:s'),
            $endDate->format('Y-m-d H:i:s')
        ]);
        
        $classes = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $classes[] = self::createFromRow($row);
        }
        
        return $classes;
    }
    
    /**
     * Get parent class (for recurring instances)
     */
    public function getParentClass(): ?self
    {
        if (!$this->class_parent_id) {
            return null;
        }
        
        return self::find($this->class_parent_id);
    }
    
    /**
     * Get child instances (for recurring parents)
     */
    public function getInstances(): array
    {
        if ($this->class_type !== 'recurring_parent') {
            return [];
        }
        
        $sql = "SELECT * FROM " . self::$table . " 
                WHERE class_parent_id = ? 
                ORDER BY instance_date_time ASC";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$this->id]);
        
        $instances = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $instances[] = self::createFromRow($row);
        }
        
        return $instances;
    }
    
    /**
     * Get subscriptions that include this class
     */
    public function getSubscriptions(): array
    {
        $sql = "SELECT s.* FROM subscriptions s
                INNER JOIN class_subscriptions cs ON s.id = cs.subscription_id
                WHERE cs.class_id = ?
                ORDER BY s.name ASC";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$this->id]);
        
        $subscriptions = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $subscriptions[] = Subscription::createFromRow($row);
        }
        
        return $subscriptions;
    }
    
    /**
     * Get class bookings
     */
    public function getBookings(string $status = null): array
    {
        $sql = "SELECT cb.*, m.first_name, m.last_name, m.email 
                FROM class_bookings cb
                INNER JOIN members m ON cb.member_id = m.id
                WHERE cb.class_instance_id = ?";
        
        $params = [$this->id];
        
        if ($status) {
            $sql .= " AND cb.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY cb.booking_date ASC";
        
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        
        $bookings = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $bookings[] = ClassBooking::createFromRow($row);
        }
        
        return $bookings;
    }
    
    /**
     * Get current booking count
     */
    public function getCurrentBookingCount(): int
    {
        $sql = "SELECT COUNT(*) FROM class_bookings 
                WHERE class_instance_id = ? AND status = 'booked'";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$this->id]);
        
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Check if class has capacity
     */
    public function hasCapacity(): bool
    {
        if (!$this->capacity) {
            return true;
        }
        
        return $this->getCurrentBookingCount() < $this->capacity;
    }
    
    /**
     * Check if member can book this class
     */
    public function canMemberBook(Member $member): bool
    {
        // Check if class is in the future
        if ($this->instance_date_time <= new \DateTime()) {
            return false;
        }
        
        // Check capacity
        if (!$this->hasCapacity()) {
            return false;
        }
        
        // Check if member already booked
        if ($this->isMemberBooked($member)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if member is already booked
     */
    public function isMemberBooked(Member $member): bool
    {
        $sql = "SELECT COUNT(*) FROM class_bookings 
                WHERE class_instance_id = ? AND member_id = ? AND status = 'booked'";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$this->id, $member->id]);
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Generate recurring instances
     */
    public function generateInstances(\DateTime $endDate = null): array
    {
        if ($this->class_type !== 'recurring_parent') {
            return [];
        }
        
        if (!$endDate) {
            $endDate = $this->original_end_date ?: (new \DateTime())->add(new \DateInterval('P3M'));
        }
        
        $instances = [];
        $currentDate = clone $this->original_start_date;
        
        while ($currentDate <= $endDate) {
            // Check if instance already exists
            $sql = "SELECT COUNT(*) FROM " . self::$table . " 
                    WHERE class_parent_id = ? AND DATE(instance_date_time) = ?";
            $stmt = self::getConnection()->prepare($sql);
            $stmt->execute([$this->id, $currentDate->format('Y-m-d')]);
            
            if ($stmt->fetchColumn() == 0) {
                // Create new instance
                $instance = new self([
                    'class_parent_id' => $this->id,
                    'name' => $this->name,
                    'description' => $this->description,
                    'class_type' => 'recurring_instance',
                    'instance_date_time' => $currentDate->format('Y-m-d H:i:s'),
                    'duration_minutes' => $this->duration_minutes,
                    'capacity' => $this->capacity,
                    'auto_book' => $this->auto_book
                ]);
                
                $instance->save();
                $instances[] = $instance;
            }
            
            // Move to next occurrence
            switch ($this->frequency) {
                case 'daily':
                    $currentDate->add(new \DateInterval('P1D'));
                    break;
                case 'weekly':
                    $currentDate->add(new \DateInterval('P1W'));
                    break;
                case 'fortnightly':
                    $currentDate->add(new \DateInterval('P2W'));
                    break;
                case '4_weekly':
                    $currentDate->add(new \DateInterval('P4W'));
                    break;
                case 'monthly':
                    $currentDate->add(new \DateInterval('P1M'));
                    break;
            }
        }
        
        return $instances;
    }
    
    /**
     * Get formatted date and time
     */
    public function getFormattedDateTime(): string
    {
        return $this->instance_date_time->format('d/m/Y H:i');
    }
    
    /**
     * Get formatted duration
     */
    public function getFormattedDuration(): string
    {
        if (!$this->duration_minutes) {
            return 'Not specified';
        }
        
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
        if ($hours > 0) {
            return $hours . 'h' . ($minutes > 0 ? ' ' . $minutes . 'm' : '');
        }
        
        return $minutes . 'm';
    }
    
    /**
     * Get class type label
     */
    public function getTypeLabel(): string
    {
        return match($this->class_type) {
            'single' => 'Single Class',
            'recurring_parent' => 'Recurring Series',
            'recurring_instance' => 'Class Instance',
            default => ucfirst($this->class_type)
        };
    }
    
    /**
     * Get frequency label
     */
    public function getFrequencyLabel(): string
    {
        return match($this->frequency) {
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'fortnightly' => 'Fortnightly',
            '4_weekly' => 'Every 4 Weeks',
            'monthly' => 'Monthly',
            default => ucfirst($this->frequency ?? '')
        };
    }
}
