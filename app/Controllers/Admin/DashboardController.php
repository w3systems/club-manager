<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Member;
use App\Models\ClassModel;
use App\Models\Payment;
use App\Models\Message;

/**
 * Admin Dashboard Controller
 * Displays admin dashboard with key metrics and information
 */
class DashboardController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function index(): void
    {
        $this->requireAdmin();
        $this->requirePermission('view_dashboard');
        
        // Get dashboard data
        $data = [
            'stats' => $this->getDashboardStats(),
            'recentMembers' => $this->getRecentMembers(),
            'upcomingClasses' => $this->getUpcomingClasses(),
            'recentPayments' => $this->getRecentPayments(),
            'failedPayments' => $this->getFailedPayments(),
            'unreadMessages' => $this->getUnreadMessages(),
            'expiringSubscriptions' => $this->getExpiringSubscriptions(),
        ];
        
        $this->render('admin/dashboard', $data, 'admin');
    }
    
    /**
     * Get dashboard statistics
     */
    private function getDashboardStats(): array
    {
        try {
            $db = $this->container->get('db');
            
            // Total members
            $stmt = $db->query("SELECT COUNT(*) FROM members");
            $totalMembers = $stmt->fetchColumn();
            
            // Active subscriptions
            $stmt = $db->query("SELECT COUNT(*) FROM member_subscriptions WHERE status = 'active'");
            $activeSubscriptions = $stmt->fetchColumn();
            
            // Today's revenue
            $stmt = $db->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'succeeded' AND DATE(payment_date) = CURDATE()");
            $todayRevenue = $stmt->fetchColumn();
            
            // This month's revenue
            $stmt = $db->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'succeeded' AND YEAR(payment_date) = YEAR(CURDATE()) AND MONTH(payment_date) = MONTH(CURDATE())");
            $monthRevenue = $stmt->fetchColumn();
            
            // Today's classes
            $stmt = $db->query("SELECT COUNT(*) FROM classes WHERE class_type IN ('single', 'recurring_instance') AND DATE(instance_date_time) = CURDATE()");
            $todayClasses = $stmt->fetchColumn();
            
            // Failed payments this week
            $stmt = $db->query("SELECT COUNT(*) FROM payments WHERE status = 'failed' AND payment_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
            $failedPayments = $stmt->fetchColumn();
            
            return [
                'total_members' => (int) $totalMembers,
                'active_subscriptions' => (int) $activeSubscriptions,
                'today_revenue' => (float) $todayRevenue,
                'month_revenue' => (float) $monthRevenue,
                'today_classes' => (int) $todayClasses,
                'failed_payments' => (int) $failedPayments,
            ];
            
        } catch (\Exception $e) {
            logger('Dashboard stats error: ' . $e->getMessage(), 'error');
            return [
                'total_members' => 0,
                'active_subscriptions' => 0,
                'today_revenue' => 0.0,
                'month_revenue' => 0.0,
                'today_classes' => 0,
                'failed_payments' => 0,
            ];
        }
    }
    
    /**
     * Get recent members (last 30 days)
     */
    private function getRecentMembers(): array
    {
        try {
            $db = $this->container->get('db');
            $stmt = $db->prepare("
                SELECT * FROM members 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
                ORDER BY created_at DESC 
                LIMIT 5
            ");
            $stmt->execute();
            
            $members = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $members[] = $this->createMemberFromRow($row);
            }
            
            return $members;
            
        } catch (\Exception $e) {
            logger('Recent members error: ' . $e->getMessage(), 'error');
            return [];
        }
    }
    
    /**
     * Get upcoming classes
     */
    private function getUpcomingClasses(): array
    {
        try {
            $db = $this->container->get('db');
            $stmt = $db->prepare("
                SELECT * FROM classes 
                WHERE class_type IN ('single', 'recurring_instance') 
                AND instance_date_time >= NOW() 
                ORDER BY instance_date_time ASC 
                LIMIT 5
            ");
            $stmt->execute();
            
            $classes = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $classes[] = $this->createClassFromRow($row);
            }
            
            return $classes;
            
        } catch (\Exception $e) {
            logger('Upcoming classes error: ' . $e->getMessage(), 'error');
            return [];
        }
    }
    
    /**
     * Get recent payments (last 7 days)
     */
    private function getRecentPayments(): array
    {
        try {
            $db = $this->container->get('db');
            $stmt = $db->prepare("
                SELECT p.*, m.first_name, m.last_name, m.email 
                FROM payments p
                INNER JOIN members m ON p.member_id = m.id
                WHERE p.payment_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY p.payment_date DESC
                LIMIT 10
            ");
            $stmt->execute();
            
            $payments = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $payments[] = $this->createPaymentFromRow($row);
            }
            
            return $payments;
            
        } catch (\Exception $e) {
            logger('Recent payments error: ' . $e->getMessage(), 'error');
            return [];
        }
    }
    
    /**
     * Get failed payments (last 7 days)
     */
    private function getFailedPayments(): array
    {
        try {
            $db = $this->container->get('db');
            $stmt = $db->prepare("
                SELECT p.*, m.first_name, m.last_name, m.email 
                FROM payments p
                INNER JOIN members m ON p.member_id = m.id
                WHERE p.status = 'failed' 
                AND p.payment_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY p.payment_date DESC
                LIMIT 5
            ");
            $stmt->execute();
            
            $payments = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $payments[] = $this->createPaymentFromRow($row);
            }
            
            return $payments;
            
        } catch (\Exception $e) {
            logger('Failed payments error: ' . $e->getMessage(), 'error');
            return [];
        }
    }
    
    /**
     * Get unread messages from members
     */
    private function getUnreadMessages(): array
    {
        try {
            $db = $this->container->get('db');
            $stmt = $db->prepare("
                SELECT m.*, mem.first_name, mem.last_name, mem.email
                FROM messages m
                INNER JOIN members mem ON m.sender_member_id = mem.id
                WHERE m.type = 'member_to_admin' 
                AND m.is_read_by_recipient = 0
                ORDER BY m.created_at DESC
                LIMIT 5
            ");
            $stmt->execute();
            
            $messages = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $messages[] = $this->createMessageFromRow($row);
            }
            
            return $messages;
            
        } catch (\Exception $e) {
            logger('Unread messages error: ' . $e->getMessage(), 'error');
            return [];
        }
    }
    
    /**
     * Get expiring subscriptions (next 7 days)
     */
    private function getExpiringSubscriptions(): array
    {
        try {
            $db = $this->container->get('db');
            $stmt = $db->prepare("
                SELECT ms.*, m.first_name, m.last_name, m.email, s.name as subscription_name
                FROM member_subscriptions ms
                INNER JOIN members m ON ms.member_id = m.id
                INNER JOIN subscriptions s ON ms.subscription_id = s.id
                WHERE ms.status = 'active'
                AND ms.next_renewal_date IS NOT NULL
                AND ms.next_renewal_date <= DATE_ADD(NOW(), INTERVAL 7 DAY)
                ORDER BY ms.next_renewal_date ASC
                LIMIT 5
            ");
            $stmt->execute();
            
            $subscriptions = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $subscriptions[] = $this->createSubscriptionFromRow($row);
            }
            
            return $subscriptions;
            
        } catch (\Exception $e) {
            logger('Expiring subscriptions error: ' . $e->getMessage(), 'error');
            return [];
        }
    }
    
    /**
     * Create member object from database row
     */
    private function createMemberFromRowold(array $row): object
    {
        return (object) [
            'id' => $row['id'],
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'email' => $row['email'],
            'created_at' => $row['created_at'],
            'getFullName' => function() use ($row) {
                return trim($row['first_name'] . ' ' . $row['last_name']);
            },
            'getInitials' => function() use ($row) {
                return strtoupper(substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1));
            }
        ];
    }


	/**
	 * Create member object from database row - FIXED VERSION
	 */
	private function createMemberFromRow(array $row): object
    {
        return (object) [
            'id' => $row['id'],
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'email' => $row['email'],
            'created_at' => $row['created_at'],
            'full_name' => trim($row['first_name'] . ' ' . $row['last_name']),
            'initials' => strtoupper(substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1))
        ];
    }

    
    /**
     * Create class object from database row
     */
    private function createClassFromRowold(array $row): object
    {
        return (object) [
            'id' => $row['id'],
            'name' => $row['name'],
            'instance_date_time' => new \DateTime($row['instance_date_time']),
            'capacity' => $row['capacity'],
            'getCurrentBookingCount' => function() use ($row) {
                $db = $this->container->get('db');
                $stmt = $db->prepare("SELECT COUNT(*) FROM class_bookings WHERE class_instance_id = ? AND status = 'booked'");
                $stmt->execute([$row['id']]);
                return (int) $stmt->fetchColumn();
            },
            'getFormattedDateTime' => function() use ($row) {
                return date('d/m/Y H:i', strtotime($row['instance_date_time']));
            }
        ];
    }
    
    /**
     * Create payment object from database row
     */
    private function createPaymentFromRowold(array $row): object
    {
        return (object) [
            'id' => $row['id'],
            'amount' => $row['amount'],
            'currency' => $row['currency'],
            'status' => $row['status'],
            'payment_date' => $row['payment_date'],
            'description' => $row['description'] ?? null,
            'first_name' => $row['first_name'] ?? '',
            'last_name' => $row['last_name'] ?? '',
            'email' => $row['email'] ?? '',
            'getFormattedAmount' => function() use ($row) {
                return format_currency($row['amount'], $row['currency']);
            },
            'getStatusLabel' => function() use ($row) {
                return ucfirst($row['status']);
            },
            'getStatusColorClass' => function() use ($row) {
                return match($row['status']) {
                    'pending' => 'text-yellow-600 bg-yellow-100',
                    'succeeded' => 'text-green-600 bg-green-100',
                    'failed' => 'text-red-600 bg-red-100',
                    'refunded' => 'text-gray-600 bg-gray-100',
                    default => 'text-gray-600 bg-gray-100'
                };
            }
        ];
    }
    
    /**
     * Create message object from database row
     */
    private function createMessageFromRowold(array $row): object
    {
        return (object) [
            'id' => $row['id'],
            'content' => $row['content'],
            'created_at' => $row['created_at'],
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'email' => $row['email']
        ];
    }
    
    /**
     * Create subscription object from database row
     */
    private function createSubscriptionFromRowold(array $row): object
    {
        return (object) [
            'id' => $row['id'],
            'next_renewal_date' => $row['next_renewal_date'],
            'subscription_name' => $row['subscription_name'],
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'email' => $row['email']
        ];
    }


	/**
	 * Create class object from database row - FIXED VERSION
	 */
	private function createClassFromRow(array $row): object
    {
        $db = $this->container->get('db');
        $stmt = $db->prepare("SELECT COUNT(*) FROM class_bookings WHERE class_instance_id = ? AND status = 'booked'");
        $stmt->execute([$row['id']]);
        $currentBookings = (int) $stmt->fetchColumn();
        
        return (object) [
            'id' => $row['id'],
            'name' => $row['name'],
            'instance_date_time' => new \DateTime($row['instance_date_time']),
            'capacity' => $row['capacity'],
            'current_booking_count' => $currentBookings,
            'formatted_date_time' => date('d/m/Y H:i', strtotime($row['instance_date_time']))
        ];
    }

	/**
	 * Create payment object from database row - FIXED VERSION
	 */
	private function createPaymentFromRow(array $row): object
	{
		$statusClasses = match($row['status']) {
			'pending' => 'text-yellow-600 bg-yellow-100',
			'succeeded' => 'text-green-600 bg-green-100',
			'failed' => 'text-red-600 bg-red-100',
			'refunded' => 'text-gray-600 bg-gray-100',
			default => 'text-gray-600 bg-gray-100'
		};
		
		return (object) [
			'id' => $row['id'],
			'amount' => $row['amount'],
			'currency' => $row['currency'],
			'status' => $row['status'],
			'payment_date' => $row['payment_date'],
			'description' => $row['description'] ?? null,
			'first_name' => $row['first_name'] ?? '',
			'last_name' => $row['last_name'] ?? '',
			'email' => $row['email'] ?? '',
			'formatted_amount' => number_format($row['amount'], 2),
			'status_label' => ucfirst($row['status']),
			'status_color_class' => $statusClasses,
			'formatted_date' => date('M j, Y', strtotime($row['payment_date']))
		];
	}

	/**
	 * Create message object from database row - FIXED VERSION
	 */
	private function createMessageFromRow(array $row): object
	{
		return (object) [
			'id' => $row['id'],
			'content' => $row['content'],
			'created_at' => $row['created_at'],
			'first_name' => $row['first_name'],
			'last_name' => $row['last_name'],
			'email' => $row['email'],
			'formatted_date' => date('M j, Y', strtotime($row['created_at'])),
			'preview' => substr(strip_tags($row['content']), 0, 100) . '...'
		];
	}

	/**
	 * Create subscription object from database row - FIXED VERSION
	 */
	private function createSubscriptionFromRow(array $row): object
	{
		return (object) [
			'id' => $row['id'],
			'next_renewal_date' => $row['next_renewal_date'],
			'subscription_name' => $row['subscription_name'],
			'first_name' => $row['first_name'],
			'last_name' => $row['last_name'],
			'email' => $row['email'],
			'member_full_name' => trim($row['first_name'] . ' ' . $row['last_name']),
			'formatted_renewal_date' => date('M j, Y', strtotime($row['next_renewal_date'])),
			'days_until_renewal' => ceil((strtotime($row['next_renewal_date']) - time()) / 86400)
		];
	}



}