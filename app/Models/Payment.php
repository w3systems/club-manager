<?php
// app/Models/Payment.php

namespace App\Models;

use App\Core\Model;

/**
 * Payment Model
 * Represents member payments
 */
class Payment extends Model
{
    protected static string $table = 'payments';
    protected static array $fillable = [
        'member_id', 'member_subscription_id', 'amount', 'currency', 
        'payment_date', 'status', 'payment_gateway', 'transaction_id', 
        'invoice_id', 'description'
    ];
    protected static array $casts = [
        'member_id' => 'integer',
        'member_subscription_id' => 'integer',
        'amount' => 'float',
        'payment_date' => 'datetime'
    ];
    
    /**
     * Get member for this payment
     */
    public function getMember(): ?Member
    {
        return Member::find($this->member_id);
    }
    
    /**
     * Get subscription for this payment
     */
    public function getSubscription(): ?MemberSubscription
    {
        if (!$this->member_subscription_id) {
            return null;
        }
        
        return MemberSubscription::find($this->member_subscription_id);
    }
    
    /**
     * Get recent failed payments
     */
    public static function getRecentFailed(int $days = 7): array
    {
        $sql = "SELECT p.*, m.first_name, m.last_name, m.email 
                FROM payments p
                INNER JOIN members m ON p.member_id = m.id
                WHERE p.status = 'failed' 
                AND p.payment_date >= DATE_SUB(NOW(), INTERVAL ? DAY)
                ORDER BY p.payment_date DESC";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$days]);
        
        $payments = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $payments[] = self::createFromRow($row);
        }
        
        return $payments;
    }
    
    /**
     * Get payments for date range
     */
    public static function getForDateRange(\DateTime $startDate, \DateTime $endDate): array
    {
        $sql = "SELECT p.*, m.first_name, m.last_name, m.email 
                FROM payments p
                INNER JOIN members m ON p.member_id = m.id
                WHERE p.payment_date BETWEEN ? AND ?
                ORDER BY p.payment_date DESC";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([
            $startDate->format('Y-m-d H:i:s'),
            $endDate->format('Y-m-d H:i:s')
        ]);
        
        $payments = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $payments[] = self::createFromRow($row);
        }
        
        return $payments;
    }
    
    /**
     * Get total revenue for period
     */
    public static function getTotalRevenue(\DateTime $startDate, \DateTime $endDate): float
    {
        $sql = "SELECT COALESCE(SUM(amount), 0) FROM payments 
                WHERE status = 'succeeded' 
                AND payment_date BETWEEN ? AND ?";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([
            $startDate->format('Y-m-d H:i:s'),
            $endDate->format('Y-m-d H:i:s')
        ]);
        
        return (float) $stmt->fetchColumn();
    }
    
    /**
     * Mark payment as succeeded
     */
    public function markAsSucceeded(string $transactionId = null): bool
    {
        $this->status = 'succeeded';
        if ($transactionId) {
            $this->transaction_id = $transactionId;
        }
        
        return $this->save();
    }
    
    /**
     * Mark payment as failed
     */
    public function markAsFailed(): bool
    {
        $this->status = 'failed';
        return $this->save();
    }
    
    /**
     * Mark payment as refunded
     */
    public function markAsRefunded(): bool
    {
        $this->status = 'refunded';
        return $this->save();
    }
    
    /**
     * Check if payment is successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'succeeded';
    }
    
    /**
     * Check if payment is failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
    
    /**
     * Check if payment is refunded
     */
    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }
    
    /**
     * Get formatted amount
     */
    public function getFormattedAmount(): string
    {
        return format_currency($this->amount, $this->currency);
    }
    
    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'succeeded' => 'Succeeded',
            'failed' => 'Failed',
            'refunded' => 'Refunded',
            default => ucfirst($this->status)
        };
    }
    
    /**
     * Get status color class
     */
    public function getStatusColorClass(): string
    {
        return match($this->status) {
            'pending' => 'text-yellow-600 bg-yellow-100',
            'succeeded' => 'text-green-600 bg-green-100',
            'failed' => 'text-red-600 bg-red-100',
            'refunded' => 'text-gray-600 bg-gray-100',
            default => 'text-gray-600 bg-gray-100'
        };
    }
    
    /**
     * Get gateway label
     */
    public function getGatewayLabel(): string
    {
        return match($this->payment_gateway) {
            'stripe' => 'Stripe',
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'manual' => 'Manual',
            default => ucfirst($this->payment_gateway)
        };
    }
}