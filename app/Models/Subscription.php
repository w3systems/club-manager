<?php
// app/Models/Subscription.php

namespace App\Models;

use App\Core\Model;

/**
 * Subscription Model
 * Represents subscription plans available to members
 */
class Subscription extends Model
{
    protected static string $table = 'subscriptions';

protected static array $fillable = [
        'name', 'description', 'type', 'price', 'currency', 'term_length', 
        'term_unit', 'fixed_start_day', 'prorata_enabled', 'prorata_price', 
        'auto_book', 
        'admin_fee', 'capacity', 'free_trial_enabled', 'min_age', 'max_age', 
        'next_subscription_id', 'charge_on_start_date', 'stripe_price_id',
        'status'
    ];

    protected static array $casts = [
        'price' => 'float',
        'prorata_price' => 'float',
        'admin_fee' => 'float',
        'prorata_enabled' => 'boolean',
        'free_trial_enabled' => 'boolean',
        'charge_on_start_date' => 'boolean',
        'term_length' => 'integer',
        'fixed_start_day' => 'integer',
        'capacity' => 'integer',
        'min_age' => 'integer',
        'max_age' => 'integer',
        'next_subscription_id' => 'integer'
    ];
    
    /**
     * Get all active subscriptions
     */
    public static function getActive(): array
    {
        $sql = "SELECT * FROM " . self::$table . " ORDER BY name ASC";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute();
        
        $subscriptions = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $subscriptions[] = self::createFromRow($row);
        }
        
        return $subscriptions;
    }
    
    /**
     * Get subscriptions available for age
     */
    public static function getAvailableForAge(int $age): array
    {
        $sql = "SELECT * FROM " . self::$table . " 
                WHERE (min_age IS NULL OR min_age <= ?) 
                AND (max_age IS NULL OR max_age >= ?)
                ORDER BY name ASC";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$age, $age]);
        
        $subscriptions = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $subscriptions[] = self::createFromRow($row);
        }
        
        return $subscriptions;
    }
    
    /**
     * Get classes associated with this subscription
     */
    public function getClasses(): array
    {
        $sql = "SELECT c.* FROM classes c
                INNER JOIN class_subscriptions cs ON c.id = cs.class_id
                WHERE cs.subscription_id = ?
                ORDER BY c.name ASC";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$this->id]);
        
        $classes = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $classes[] = ClassModel::createFromRow($row);
        }
        
        return $classes;
    }
    
    /**
     * Get current member count
     */
    public function getCurrentMemberCount(): int
    {
        $sql = "SELECT COUNT(*) FROM member_subscriptions 
                WHERE subscription_id = ? AND status = 'active'";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$this->id]);
        
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Check if subscription has capacity
     */
    public function hasCapacity(): bool
    {
        if (!$this->capacity) {
            return true;
        }
        
        return $this->getCurrentMemberCount() < $this->capacity;
    }
    
    /**
     * Check if member is eligible for this subscription
     */
    public function isEligibleForMember(Member $member): bool
    {
        $age = $member->getAge();
        
        if ($age === null) {
            return false;
        }
        
        // Check age restrictions
        if ($this->min_age && $age < $this->min_age) {
            return false;
        }
        
        if ($this->max_age && $age > $this->max_age) {
            return false;
        }
        
        // Check capacity
        return $this->hasCapacity();
    }
    
    /**
     * Calculate prorata price for given date
     */
    public function calculateProrataPrice(\DateTime $startDate): float
    {
        if (!$this->prorata_enabled || !$this->fixed_start_day) {
            return $this->price;
        }
        
        $monthStart = new \DateTime($startDate->format('Y-m-') . sprintf('%02d', $this->fixed_start_day));
        
        if ($startDate <= $monthStart) {
            return $this->price;
        }
        
        // Calculate days remaining in month
        $monthEnd = clone $monthStart;
        $monthEnd->add(new \DateInterval('P1M'));
        
        $totalDays = $monthStart->diff($monthEnd)->days;
        $remainingDays = $startDate->diff($monthEnd)->days;
        
        $prorataAmount = ($this->prorata_price * $remainingDays / $totalDays) + $this->admin_fee;
        
        return round($prorataAmount, 2);
    }
    
    /**
     * Get next billing date for recurring subscription
     */
    public function getNextBillingDate(\DateTime $startDate): \DateTime
    {
        $billingDate = clone $startDate;
        
        switch ($this->term_unit) {
            case 'day':
                $billingDate->add(new \DateInterval('P' . $this->term_length . 'D'));
                break;
            case 'week':
                $billingDate->add(new \DateInterval('P' . ($this->term_length * 7) . 'D'));
                break;
            case 'month':
                $billingDate->add(new \DateInterval('P' . $this->term_length . 'M'));
                break;
            case 'year':
                $billingDate->add(new \DateInterval('P' . $this->term_length . 'Y'));
                break;
        }
        
        return $billingDate;
    }
    
    /**
     * Get next subscription for age progression
     */
    public function getNextSubscription(): ?self
    {
        if (!$this->next_subscription_id) {
            return null;
        }
        
        return self::find($this->next_subscription_id);
    }
    
    /**
     * Format price for display
     */
    public function getFormattedPrice(): string
    {
        return format_currency($this->price, $this->currency);
    }
    
    /**
     * Get subscription type label
     */
    public function getTypeLabel(): string
    {
        return match($this->type) {
            'fixed_length' => 'Fixed Length',
            'recurring' => 'Recurring',
            'session_based' => 'Session Based',
            default => ucfirst($this->type)
        };
    }

/**
     * Get an array of IDs for classes associated with this subscription.
     */
    public function getClassIds(): array
    {
        $sql = "SELECT class_id FROM class_subscriptions WHERE subscription_id = ?";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Sync the classes associated with this subscription.
     */
    public function syncClasses(array $classIds): void
    {
        $db = self::getConnection();
        
        // Delete existing associations
        $stmt = $db->prepare("DELETE FROM class_subscriptions WHERE subscription_id = ?");
        $stmt->execute([$this->id]);
        
        // Add new associations
        if (!empty($classIds)) {
            $stmt = $db->prepare("INSERT INTO class_subscriptions (subscription_id, class_id) VALUES (?, ?)");
            foreach ($classIds as $classId) {
                $stmt->execute([$this->id, $classId]);
            }
        }
    }


}
