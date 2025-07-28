<?php
// app/models/Message.php
namespace App\Models;

use App\Core\Database;

class Message
{
    private static $table = 'messages';

    /**
     * Creates a new message entry.
     * @param int|null $senderMemberId ID of the sending member.
     * @param int|null $senderAdminId ID of the sending admin.
     * @param int|null $recipientMemberId ID of the receiving member.
     * @param int|null $recipientAdminId ID of the receiving admin.
     * @param string $content Message content.
     * @param string $type Type of message ('member_to_admin' or 'admin_to_member').
     * @return string Last inserted ID.
     * @throws \Exception If both sender types or both recipient types are provided/missing, or content/type missing.
     */
    public static function createMessage($memberId, $adminId, $content, $type)
    {
        $db = Database::getInstance()->getConnection();

        $senderMemberId = null;
        $senderAdminId = null;
        $recipientMemberId = null;
        $recipientAdminId = null;

        if ($type === 'member_to_admin') {
            $senderMemberId = $memberId;
            $recipientAdminId = $adminId; // If specific admin, else default admin
        } elseif ($type === 'admin_to_member') {
            $senderAdminId = $adminId;
            $recipientMemberId = $memberId;
        } else {
            throw new \Exception("Invalid message type.");
        }

        if (empty($content)) {
            throw new \Exception("Message content cannot be empty.");
        }

        $sql = "INSERT INTO " . self::$table . " (sender_member_id, sender_admin_id, recipient_member_id, recipient_admin_id, content, type, is_read_by_recipient)
                VALUES (:sender_member_id, :sender_admin_id, :recipient_member_id, :recipient_admin_id, :content, :type, 0)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'sender_member_id' => $senderMemberId,
            'sender_admin_id' => $senderAdminId,
            'recipient_member_id' => $recipientMemberId,
            'recipient_admin_id' => $recipientAdminId,
            'content' => $content,
            'type' => $type
        ]);
        return $db->lastInsertId();
    }

    /**
     * Gets all messages for a specific member (sent by them or to them).
     * @param int $memberId
     * @return array
     */
    public static function getMessagesForMember($memberId)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT m.*, sm.first_name as sender_member_first_name, sm.last_name as sender_member_last_name,
                       sa.first_name as sender_admin_first_name, sa.last_name as sender_admin_last_name
                FROM " . self::$table . " m
                LEFT JOIN members sm ON m.sender_member_id = sm.id
                LEFT JOIN admins sa ON m.sender_admin_id = sa.id
                WHERE m.sender_member_id = :member_id OR m.recipient_member_id = :member_id
                ORDER BY m.created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute(['member_id' => $memberId]);
        return $stmt->fetchAll();
    }

    /**
     * Gets all messages requiring admin attention (member-to-admin).
     * @return array
     */
    public static function getAllAdminMessages()
    {
        $db = Database::getInstance()->getConnection();
        // This query fetches messages where members sent to any admin, or admins sent to members for context
        $sql = "SELECT m.*,
                       sm.first_name as sender_member_first_name, sm.last_name as sender_member_last_name,
                       sm.email as sender_member_email,
                       sa.first_name as sender_admin_first_name, sa.last_name as sender_admin_last_name,
                       rm.first_name as recipient_member_first_name, rm.last_name as recipient_member_last_name
                FROM " . self::$table . " m
                LEFT JOIN members sm ON m.sender_member_id = sm.id
                LEFT JOIN admins sa ON m.sender_admin_id = sa.id
                LEFT JOIN members rm ON m.recipient_member_id = rm.id
                WHERE m.type = 'member_to_admin' OR m.type = 'admin_to_member' -- Show full threads
                ORDER BY m.created_at DESC";
        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Counts unread messages for admins (messages sent by members that admins haven't read).
     * @return int
     */
    public static function countUnreadAdminMessages()
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT COUNT(*) FROM " . self::$table . " m
                WHERE m.type = 'member_to_admin' AND m.is_read_by_recipient = 0";
        $stmt = $db->query($sql);
        return $stmt->fetchColumn();
    }

    /**
     * Marks a message as read by its intended recipient.
     * @param int $messageId
     * @return bool
     */
    public static function markAsRead($messageId)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "UPDATE " . self::$table . " SET is_read_by_recipient = 1 WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute(['id' => $messageId]);
    }
}
