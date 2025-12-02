<?php
// =====================================
// app/Views/components/sidebar/member-nav.php
?>
<div class="space-y-1">
    <!-- Dashboard -->
    <a href="<?= $this->url('/') ?>" 
       class="<?= $_SERVER['REQUEST_URI'] === '/' ? 'bg-red-100 text-red-900 border-r-2 border-red-500' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
        <i class="fas fa-home mr-3 h-5 w-5"></i>
        <span x-show="!sidebarCollapsed">Dashboard</span>
    </a>

    <!-- Profile -->
    <a href="<?= $this->url('profile') ?>" 
       class="<?= $_SERVER['REQUEST_URI'] === '/profile' ? 'bg-red-100 text-red-900 border-r-2 border-red-500' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
        <i class="fas fa-user mr-3 h-5 w-5"></i>
        <span x-show="!sidebarCollapsed">My Profile</span>
    </a>

    <!-- Subscriptions -->
    <a href="<?= $this->url('subscriptions') ?>" 
       class="<?= strpos($_SERVER['REQUEST_URI'], '/subscriptions') === 0 ? 'bg-red-100 text-red-900 border-r-2 border-red-500' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
        <i class="fas fa-credit-card mr-3 h-5 w-5"></i>
        <span x-show="!sidebarCollapsed">My Subscriptions</span>
    </a>

    <!-- Classes -->
    <a href="<?= $this->url('classes') ?>" 
       class="<?= strpos($_SERVER['REQUEST_URI'], '/classes') === 0 ? 'bg-red-100 text-red-900 border-r-2 border-red-500' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
        <i class="fas fa-calendar-alt mr-3 h-5 w-5"></i>
        <span x-show="!sidebarCollapsed">Classes</span>
    </a>

    <!-- Payments -->
    <a href="<?= $this->url('payments') ?>" 
       class="<?= strpos($_SERVER['REQUEST_URI'], '/payments') === 0 ? 'bg-red-100 text-red-900 border-r-2 border-red-500' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
        <i class="fas fa-pound-sign mr-3 h-5 w-5"></i>
        <span x-show="!sidebarCollapsed">Payments</span>
    </a>

    <!-- Payment Methods -->
    <a href="<?= $this->url('payment-methods') ?>" 
       class="<?= strpos($_SERVER['REQUEST_URI'], '/payment-methods') === 0 ? 'bg-red-100 text-red-900 border-r-2 border-red-500' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
        <i class="fas fa-wallet mr-3 h-5 w-5"></i>
        <span x-show="!sidebarCollapsed">Payment Methods</span>
    </a>

    <!-- Notifications -->
    <a href="<?= $this->url('notifications') ?>" 
       class="<?= strpos($_SERVER['REQUEST_URI'], '/notifications') === 0 ? 'bg-red-100 text-red-900 border-r-2 border-red-500' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
        <i class="fas fa-bell mr-3 h-5 w-5"></i>
        <span x-show="!sidebarCollapsed">Notifications</span>
        <?php $unreadCount = count(App\Models\Notification::getUnreadForMember($auth->id())); ?>
        <?php if ($unreadCount > 0): ?>
            <span class="ml-auto bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                <?= $unreadCount ?>
            </span>
        <?php endif; ?>
    </a>

    <!-- Messages -->
    <a href="<?= $this->url('messages') ?>" 
       class="<?= strpos($_SERVER['REQUEST_URI'], '/messages') === 0 ? 'bg-red-100 text-red-900 border-r-2 border-red-500' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
        <i class="fas fa-envelope mr-3 h-5 w-5"></i>
        <span x-show="!sidebarCollapsed">Messages</span>
    </a>
</div>