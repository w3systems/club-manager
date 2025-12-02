<?php
// app/Controllers/Member/DashboardController.php

namespace App\Controllers\Member;

use App\Core\Controller;
use App\Models\Member;
use App\Models\MemberSubscription;
use App\Models\ClassBooking;
use App\Models\ClassModel;
use App\Models\Payment;
use App\Models\Notification;

/**
 * Member Dashboard Controller
 * Displays member dashboard with personal information and activities
 */
class DashboardController extends Controller
{
    /**
     * Display member dashboard
     */
    public function index(): void
    {
        $this->requireMember();
        
        $member = $this->auth->user();
        
        $data = [
            'member' => $member,
            'activeSubscriptions' => $this->getActiveSubscriptions($member),
            'upcomingClasses' => $this->getUpcomingClasses($member),
            'recentPayments' => $this->getRecentPayments($member),
            'unreadNotifications' => $this->getUnreadNotifications($member),
            'stats' => $this->getMemberStats($member),
            'availableClasses' => $this->getAvailableClasses($member),
        ];
        
        $this->render('member/dashboard', $data, 'member');
    }
    
    /**
     * Get member's active subscriptions
     */
    private function getActiveSubscriptions(Member $member): array
    {
        try {
            return $member->getActiveSubscriptions();
        } catch (\Exception $e) {
            logger('Active subscriptions error: ' . $e->getMessage(), 'error');
            return [];
        }
    }
    
    /**
     * Get member's upcoming classes
     */
    private function getUpcomingClasses(Member $member): array
    {
        try {
            $bookings = $member->getClassBookings('booked');
            
            // Filter for future classes only
            $upcomingBookings = [];
            foreach ($bookings as $booking) {
                $class = $booking->getClass();
                if ($class && $class->instance_date_time > new \DateTime()) {
                    $upcomingBookings[] = [
                        'booking' => $booking,
                        'class' => $class
                    ];
                }
            }
            
            // Sort by date
            usort($upcomingBookings, function($a, $b) {
                return $a['class']->instance_date_time <=> $b['class']->instance_date_time;
            });
            
            return array_slice($upcomingBookings, 0, 5);
            
        } catch (\Exception $e) {
            logger('Upcoming classes error: ' . $e->getMessage(), 'error');
            return [];
        }
    }
    
    /**
     * Get member's recent payments
     */
    private function getRecentPayments(Member $member): array
    {
        try {
            $payments = $member->getPayments();
            return array_slice($payments, 0, 5);
        } catch (\Exception $e) {
            logger('Recent payments error: ' . $e->getMessage(), 'error');
            return [];
        }
    }
    
    /**
     * Get member's unread notifications
     */
    private function getUnreadNotifications(Member $member): array
    {
        try {
            return Notification::getUnreadForMember($member->id);
        } catch (\Exception $e) {
            logger('Unread notifications error: ' . $e->getMessage(), 'error');
            return [];
        }
    }
    
    /**
     * Get member statistics
     */
    private function getMemberStats(Member $member): array
    {
        try {
            // Total classes attended
            $attendedClasses = $this->getAttendedClassesCount($member);
            
            // Total amount paid
            $totalPaid = $this->getTotalAmountPaid($member);
            
            // Active subscriptions count
            $activeSubscriptions = count($member->getActiveSubscriptions());
            
            // Outstanding balance
            $outstandingBalance = $member->getOutstandingBalance();
            
            return [
                'attended_classes' => $attendedClasses,
                'total_paid' => $totalPaid,
                'active_subscriptions' => $activeSubscriptions,
                'outstanding_balance' => $outstandingBalance,
            ];
            
        } catch (\Exception $e) {
            logger('Member stats error: ' . $e->getMessage(), 'error');
            return [
                'attended_classes' => 0,
                'total_paid' => 0.0,
                'active_subscriptions' => 0,
                'outstanding_balance' => 0.0,
            ];
        }
    }
    
    /**
     * Get attended classes count
     */
    private function getAttendedClassesCount(Member $member): int
    {
        $sql = "SELECT COUNT(*) FROM class_bookings 
                WHERE member_id = ? AND status = 'attended'";
        $stmt = $this->container->get('db')->prepare($sql);
        $stmt->execute([$member->id]);
        
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Get total amount paid by member
     */
    private function getTotalAmountPaid(Member $member): float
    {
        $sql = "SELECT COALESCE(SUM(amount), 0) FROM payments 
                WHERE member_id = ? AND status = 'succeeded'";
        $stmt = $this->container->get('db')->prepare($sql);
        $stmt->execute([$member->id]);
        
        return (float) $stmt->fetchColumn();
    }
    
    /**
     * Get available classes for booking
     */
    private function getAvailableClasses(Member $member): array
    {
        try {
            $upcomingClasses = ClassModel::getUpcoming(10);
            $availableClasses = [];
            
            foreach ($upcomingClasses as $class) {
                if ($class->canMemberBook($member)) {
                    $availableClasses[] = $class;
                }
            }
            
            return array_slice($availableClasses, 0, 5);
            
        } catch (\Exception $e) {
            logger('Available classes error: ' . $e->getMessage(), 'error');
            return [];
        }
    }
}
