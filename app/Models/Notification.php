<?php
// app/Models/Notification.php

namespace App\Models;

use App\Core\Model;

/**
 * Notification Model
 * Represents notifications sent to members
 */
class Notification extends Model
{
    protected static string $table = 'notifications';
    protected static array $fillable = [
        'member_id', 'type', 'message', 'delivery_method_sent', 'is_read'
    ];
    protected static array $casts = [
        'member_id' => 'integer',
        'is_read' => 'boolean'
    ];
    
    /**
     * Get member for this notification
     */
    public function getMember(): ?Member
    {
        return Member::find($this->member_id);
    }
    
    /**
     * Get unread notifications for member
     */
    public static function getUnreadForMember(int $memberId): array
    {
        $sql = "SELECT * FROM notifications 
                WHERE member_id = ? AND is_read = 0 
                ORDER BY created_at DESC";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$memberId]);
        
        $notifications = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $notifications[] = self::createFromRow($row);
        }
        
        return $notifications;
    }
    
    /**
     * Get all notifications for member
     */
    public static function getAllForMember(int $memberId, int $limit = 50): array
    {
        $sql = "SELECT * FROM notifications 
                WHERE member_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$memberId, $limit]);
        
        $notifications = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $notifications[] = self::createFromRow($row);
        }
        
        return $notifications;
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead(): bool
    {
        $this->is_read = true;
        return $this->save();
    }
    
    /**
     * Mark all notifications as read for member
     */
    public static function markAllAsReadForMember(int $memberId): bool
    {
        $sql = "UPDATE notifications SET is_read = 1 WHERE member_id = ?";
        $stmt = self::getConnection()->prepare($sql);
        return $stmt->execute([$memberId]);
    }
    
    /**
     * Get type label
     */
    public function getTypeLabel(): string
    {
        return match($this->type) {
            'payment_received' => 'Payment Received',
            'payment_failed' => 'Payment Failed',
            'subscription_renewed' => 'Subscription Renewed',
            'subscription_cancelled' => 'Subscription Cancelled',
            'subscription_suspended' => 'Subscription Suspended',
            'class_booked' => 'Class Booked',
            'class_cancelled' => 'Class Cancelled',
            'class_reminder' => 'Class Reminder',
            'message_from_admin' => 'Message from Admin',
            'card_expired' => 'Card Expired',
            'trial_ending' => 'Trial Ending',
            default => ucwords(str_replace('_', ' ', $this->type))
        };
    }
    
    /**
     * Get icon class for notification type
     */
    public function getIconClass(): string
    {
        return match($this->type) {
            'payment_received' => 'text-green-500 fas fa-credit-card',
            'payment_failed' => 'text-red-500 fas fa-exclamation-triangle',
            'subscription_renewed' => 'text-blue-500 fas fa-sync',
            'subscription_cancelled' => 'text-red-500 fas fa-times-circle',
            'subscription_suspended' => 'text-yellow-500 fas fa-pause-circle',
            'class_booked' => 'text-green-500 fas fa-calendar-check',
            'class_cancelled' => 'text-red-500 fas fa-calendar-times',
            'class_reminder' => 'text-blue-500 fas fa-bell',
            'message_from_admin' => 'text-purple-500 fas fa-envelope',
            'card_expired' => 'text-orange-500 fas fa-credit-card',
            'trial_ending' => 'text-yellow-500 fas fa-hourglass-end',
            default => 'text-gray-500 fas fa-info-circle'
        };
    }
}
