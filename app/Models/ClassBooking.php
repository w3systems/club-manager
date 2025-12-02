<?php
// app/Models/ClassBooking.php

namespace App\Models;

use App\Core\Model;

/**
 * Class Booking Model
 * Represents member bookings for classes
 */
class ClassBooking extends Model
{
    protected static string $table = 'class_bookings';
    protected static array $fillable = [
        'member_id', 'class_instance_id', 'booking_date', 'status',
        'is_auto_booked', 'is_free_trial'
    ];
    protected static array $casts = [
        'member_id' => 'integer',
        'class_instance_id' => 'integer',
        'is_auto_booked' => 'boolean',
        'is_free_trial' => 'boolean',
        'booking_date' => 'datetime'
    ];
    
    /**
     * Get member for this booking
     */
    public function getMember(): ?Member
    {
        return Member::find($this->member_id);
    }
    
    /**
     * Get class for this booking
     */
    public function getClass(): ?ClassModel
    {
        return ClassModel::find($this->class_instance_id);
    }
    
    /**
     * Get upcoming bookings
     */
    public static function getUpcoming(int $limit = 50): array
    {
        $sql = "SELECT cb.*, m.first_name, m.last_name, c.name as class_name, c.instance_date_time
                FROM class_bookings cb
                INNER JOIN members m ON cb.member_id = m.id
                INNER JOIN classes c ON cb.class_instance_id = c.id
                WHERE cb.status = 'booked' 
                AND c.instance_date_time >= NOW()
                ORDER BY c.instance_date_time ASC
                LIMIT ?";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$limit]);
        
        $bookings = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $bookings[] = self::createFromRow($row);
        }
        
        return $bookings;
    }
    
    /**
     * Get bookings for member
     */
    public static function getForMember(int $memberId, string $status = null): array
    {
        $sql = "SELECT cb.*, c.name as class_name, c.instance_date_time
                FROM class_bookings cb
                INNER JOIN classes c ON cb.class_instance_id = c.id
                WHERE cb.member_id = ?";
        
        $params = [$memberId];
        
        if ($status) {
            $sql .= " AND cb.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY c.instance_date_time DESC";
        
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        
        $bookings = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $bookings[] = self::createFromRow($row);
        }
        
        return $bookings;
    }
    
    /**
     * Cancel booking
     */
    public function cancel(): bool
    {
        $this->status = 'cancelled';
        return $this->save();
    }
    
    /**
     * Mark as attended
     */
    public function markAttended(): bool
    {
        $this->status = 'attended';
        return $this->save();
    }
    
    /**
     * Mark as no show
     */
    public function markNoShow(): bool
    {
        $this->status = 'no_show';
        return $this->save();
    }
    
    /**
     * Check if booking is active
     */
    public function isActive(): bool
    {
        return $this->status === 'booked';
    }
    
    /**
     * Check if booking is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
    
    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'booked' => 'Booked',
            'cancelled' => 'Cancelled',
            'attended' => 'Attended',
            'no_show' => 'No Show',
            default => ucfirst($this->status)
        };
    }
    
    /**
     * Get status color class
     */
    public function getStatusColorClass(): string
    {
        return match($this->status) {
            'booked' => 'text-blue-600 bg-blue-100',
            'cancelled' => 'text-red-600 bg-red-100',
            'attended' => 'text-green-600 bg-green-100',
            'no_show' => 'text-orange-600 bg-orange-100',
            default => 'text-gray-600 bg-gray-100'
        };
    }
}
