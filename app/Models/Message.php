<?php
// app/Models/Message.php

namespace App\Models;

use App\Core\Model;

/**
 * Message Model
 * Represents messages between members and admins
 */
class Message extends Model
{
    protected static string $table = 'messages';
    protected static array $fillable = [
        'sender_member_id', 'sender_admin_id', 'recipient_member_id', 
        'recipient_admin_id', 'content', 'type', 'is_read_by_recipient'
    ];
    protected static array $casts = [
        'sender_member_id' => 'integer',
        'sender_admin_id' => 'integer',
        'recipient_member_id' => 'integer',
        'recipient_admin_id' => 'integer',
        'is_read_by_recipient' => 'boolean'
    ];
    
    /**
     * Get sender (member or admin)
     */
    public function getSender()
    {
        if ($this->type === 'member_to_admin') {
            return Member::find($this->sender_member_id);
        } else {
            return Admin::find($this->sender_admin_id);
        }
    }
    
    /**
     * Get recipient (member or admin)
     */
    public function getRecipient()
    {
        if ($this->type === 'member_to_admin') {
            return Admin::find($this->recipient_admin_id);
        } else {
            return Member::find($this->recipient_member_id);
        }
    }
    
    /**
     * Get unread messages for admin
     */
    public static function getUnreadForAdmin(): array
    {
        $sql = "SELECT m.*, mem.first_name, mem.last_name, mem.email
                FROM messages m
                INNER JOIN members mem ON m.sender_member_id = mem.id
                WHERE m.type = 'member_to_admin' 
                AND m.is_read_by_recipient = 0
                ORDER BY m.created_at DESC";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute();
        
        $messages = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $messages[] = self::createFromRow($row);
        }
        
        return $messages;
    }
    
    /**
     * Get conversation between member and admin
     */
    public static function getConversation(int $memberId, int $adminId = null): array
    {
        $sql = "SELECT m.*, 
                   mem.first_name as member_first_name, mem.last_name as member_last_name,
                   a.first_name as admin_first_name, a.last_name as admin_last_name
                FROM messages m
                LEFT JOIN members mem ON m.sender_member_id = mem.id OR m.recipient_member_id = mem.id
                LEFT JOIN admins a ON m.sender_admin_id = a.id OR m.recipient_admin_id = a.id
                WHERE (m.sender_member_id = ? OR m.recipient_member_id = ?)";
        
        $params = [$memberId, $memberId];
        
        if ($adminId) {
            $sql .= " AND (m.sender_admin_id = ? OR m.recipient_admin_id = ?)";
            $params[] = $adminId;
            $params[] = $adminId;
        }
        
        $sql .= " ORDER BY m.created_at ASC";
        
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        
        $messages = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $messages[] = self::createFromRow($row);
        }
        
        return $messages;
    }
    
    /**
     * Mark message as read
     */
    public function markAsRead(): bool
    {
        $this->is_read_by_recipient = true;
        return $this->save();
    }
    
    /**
     * Check if message is from member
     */
    public function isFromMember(): bool
    {
        return $this->type === 'member_to_admin';
    }
    
    /**
     * Check if message is from admin
     */
    public function isFromAdmin(): bool
    {
        return $this->type === 'admin_to_member';
    }
    
    /**
     * Get sender name
     */
    public function getSenderName(): string
    {
        $sender = $this->getSender();
        return $sender ? $sender->getFullName() : 'Unknown';
    }
}
