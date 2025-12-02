<?php
// app/Models/Member.php

namespace App\Models;

use App\Core\Model;
use App\Models\MemberSubscription;
use App\Models\ClassBooking;
use App\Models\Payment;

/**
 * Member Model
 * Represents club members
 */
class Member extends Model
{
    protected static string $table = 'members';
    protected static array $fillable = [
        'first_name', 'last_name', 'email', 'password_hash', 'mobile', 
        'date_of_birth', 'consent_photography', 'consent_first_aid', 
        'terms_conditions_acceptance', 'emergency_contact_name', 
        'emergency_contact_number', 'emergency_contact_relationship',
        'parent_guardian_email', 'parent_guardian_mobile', 'stripe_customer_id'
    ];
    protected static array $hidden = ['password_hash'];
    protected static array $casts = [
        'consent_photography' => 'boolean',
        'consent_first_aid' => 'boolean',
        'terms_conditions_acceptance' => 'boolean',
        'date_of_birth' => 'datetime'
    ];
    
    /**
     * Find member by email
     */
    public static function findByEmail(string $email): ?self
    {
        $sql = "SELECT * FROM " . self::$table . " WHERE email = ? LIMIT 1";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$email]);
        
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$row) {
            return null;
        }
        
        return self::createFromRow($row);
    }
    
    /**
     * Find member by Stripe customer ID
     */
    public static function findByStripeCustomerId(string $customerId): ?self
    {
        $sql = "SELECT * FROM " . self::$table . " WHERE stripe_customer_id = ? LIMIT 1";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$customerId]);
        
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$row) {
            return null;
        }
        
        return self::createFromRow($row);
    }
    
    /**
     * Get member's active subscriptions
     */
    public function getActiveSubscriptions(): array
    {
        $sql = "SELECT ms.*, s.name as subscription_name, s.description as subscription_description
                FROM member_subscriptions ms
                INNER JOIN subscriptions s ON ms.subscription_id = s.id
                WHERE ms.member_id = ? AND ms.status = 'active'
                ORDER BY ms.created_at DESC";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$this->id]);
        
        $subscriptions = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $subscriptions[] = MemberSubscription::createFromRow($row);
        }
        
        return $subscriptions;
    }
    
    /**
     * Get all member subscriptions
     */
    public function getSubscriptions(): array
    {
        $sql = "SELECT ms.*, s.name as subscription_name, s.description as subscription_description
                FROM member_subscriptions ms
                INNER JOIN subscriptions s ON ms.subscription_id = s.id
                WHERE ms.member_id = ?
                ORDER BY ms.created_at DESC";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$this->id]);
        
        $subscriptions = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $subscriptions[] = MemberSubscription::createFromRow($row);
        }
        
        return $subscriptions;
    }
    
    /**
     * Get member's class bookings
     */
    public function getClassBookings(string $status = null): array
    {
        $sql = "SELECT cb.*, c.name as class_name, c.instance_date_time
                FROM class_bookings cb
                INNER JOIN classes c ON cb.class_instance_id = c.id
                WHERE cb.member_id = ?";
        
        $params = [$this->id];
        
        if ($status) {
            $sql .= " AND cb.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY c.instance_date_time DESC";
        
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        
        $bookings = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $bookings[] = ClassBooking::createFromRow($row);
        }
        
        return $bookings;
    }
    
    /**
     * Get member's payments
     */
    public function getPayments(): array
    {
        $sql = "SELECT * FROM payments WHERE member_id = ? ORDER BY payment_date DESC";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$this->id]);
        
        $payments = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $payments[] = Payment::createFromRow($row);
        }
        
        return $payments;
    }
    
    /**
     * Get member's outstanding balance
     */
    public function getOutstandingBalance(): float
    {
        $sql = "SELECT COALESCE(SUM(amount), 0) FROM payments 
                WHERE member_id = ? AND status = 'failed'";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$this->id]);
        
        return (float) $stmt->fetchColumn();
    }
    
    /**
     * Check if member has active subscription
     */
    public function hasActiveSubscription(): bool
    {
        $sql = "SELECT COUNT(*) FROM member_subscriptions 
                WHERE member_id = ? AND status = 'active'";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$this->id]);
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Check if member can book free trial
     */
    public function canBookFreeTrial(): bool
    {
        $sql = "SELECT COUNT(*) FROM class_bookings 
                WHERE member_id = ? AND is_free_trial = 1";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$this->id]);
        
        return $stmt->fetchColumn() == 0;
    }
    
    /**
     * Get member's age
     */
    public function getAge(): ?int
    {
        if (!$this->date_of_birth) {
            return null;
        }
        
        $birthDate = new \DateTime($this->date_of_birth);
        $today = new \DateTime();
        
        return $today->diff($birthDate)->y;
    }
    
    /**
     * Check if member is under 18
     */
    public function isMinor(): bool
    {
        $age = $this->getAge();
        return $age !== null && $age < 18;
    }
    
    /**
     * Get full name
     */
    public function getFullName(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
    
    /**
     * Get initials
     */
    public function getInitials(): string
    {
        return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
    }
    
    /**
     * Get display name (with age if minor)
     */
    public function getDisplayName(): string
    {
        $name = $this->getFullName();
        
        if ($this->isMinor()) {
            $age = $this->getAge();
            $name .= " ({$age})";
        }
        
        return $name;
    }
}