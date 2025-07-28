<?php
// app/Models/MemberSubscription.php

namespace App\Models;

use App\Core\Model;

/**
 * Member Subscription Model
 * Represents a member's subscription to a plan
 */
class MemberSubscription extends Model
{
    protected static string $table = 'member_subscriptions';
    protected static array $fillable = [
        'member_id', 'subscription_id', 'start_date', 'end_date', 'status',
        'payment_method_id', 'stripe_subscription_id', 'last_renewal_date',
        'next_renewal_date', 'admin_override_fee', 'suspension_date', 'cancellation_date'
    ];
    protected static array $casts = [
        'member_id' => 'integer',
        'subscription_id' => 'integer',
        'payment_method_id' => 'integer',
        'admin_override_fee' => 'float',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'last_renewal_date' => 'datetime',
        'next_renewal_date' => 'datetime',
        'suspension_date' => 'datetime',
        'cancellation_date' => 'datetime'
    ];
    
    /**
     * Get member for this subscription
     */
    public function getMember(): ?Member
    {
        return Member::find($this->member_id);
    }
    
    /**
     * Get subscription plan
     */
    public function getSubscription(): ?Subscription
    {
        return Subscription::find($this->subscription_id);
    }
    
    /**
     * Get active subscriptions
     */
    public static function getActive(): array
    {
        $sql = "SELECT ms.*, m.first_name, m.last_name, m.email, s.name as subscription_name
                FROM member_subscriptions ms
                INNER JOIN members m ON ms.member_id = m.id
                INNER JOIN subscriptions s ON ms.subscription_id = s.id
                WHERE ms.status = 'active'
                ORDER BY ms.next_renewal_date ASC";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute();
        
        $subscriptions = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $subscriptions[] = self::createFromRow($row);
        }
        
        return $subscriptions;
    }
    
    /**
     * Get subscriptions expiring soon
     */
    public static function getExpiringSoon(int $days = 7): array
    {
        $sql = "SELECT ms.*, m.first_name, m.last_name, m.email, s.name as subscription_name
                FROM member_subscriptions ms
                INNER JOIN members m ON ms.member_id = m.id
                INNER JOIN subscriptions s ON ms.subscription_id = s.id
                WHERE ms.status = 'active'
                AND ms.next_renewal_date IS NOT NULL
                AND ms.next_renewal_date <= DATE_ADD(NOW(), INTERVAL ? DAY)
                ORDER BY ms.next_renewal_date ASC";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$days]);
        
        $subscriptions = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $subscriptions[] = self::createFromRow($row);
        }
        
        return $subscriptions;
    }
    
    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
    
    /**
     * Check if subscription is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
    
    /**
     * Check if subscription is suspended
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }
    
    /**
     * Cancel subscription
     */
    public function cancel(\DateTime $effectiveDate = null): bool
    {
        $this->status = 'cancelled';
        $this->cancellation_date = $effectiveDate ?: new \DateTime();
        
        return $this->save();
    }
    
    /**
     * Suspend subscription
     */
    public function suspend(\DateTime $effectiveDate = null): bool
    {
        $this->status = 'suspended';
        $this->suspension_date = $effectiveDate ?: new \DateTime();
        
        return $this->save();
    }
    
    /**
     * Resume subscription
     */
    public function resume(): bool
    {
        $this->status = 'active';
        $this->suspension_date = null;
        
        return $this->save();
    }
    
    /**
     * Get effective price (with admin override if set)
     */
    public function getEffectivePrice(): float
    {
        if ($this->admin_override_fee !== null) {
            return $this->admin_override_fee;
        }
        
        $subscription = $this->getSubscription();
        return $subscription ? $subscription->price : 0.0;
    }
    
    /**
     * Get formatted effective price
     */
    public function getFormattedEffectivePrice(): string
    {
        $subscription = $this->getSubscription();
        $currency = $subscription ? $subscription->currency : 'GBP';
        
        return format_currency($this->getEffectivePrice(), $currency);
    }
    
    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'active' => 'Active',
            'suspended' => 'Suspended',
            'cancelled' => 'Cancelled',
            'ended' => 'Ended',
            'trial' => 'Trial',
            default => ucfirst($this->status)
        };
    }
    
    /**
     * Get status color class
     */
    public function getStatusColorClass(): string
    {
        return match($this->status) {
            'active' => 'text-green-600 bg-green-100',
            'suspended' => 'text-yellow-600 bg-yellow-100',
            'cancelled' => 'text-red-600 bg-red-100',
            'ended' => 'text-gray-600 bg-gray-100',
            'trial' => 'text-blue-600 bg-blue-100',
            default => 'text-gray-600 bg-gray-100'
        };
    }
}
