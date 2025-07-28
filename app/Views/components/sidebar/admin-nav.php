<?php
// =====================================
// app/Views/components/sidebar/admin-nav.php
?>
<div class="space-y-1">
    <!-- Dashboard -->
    <a href="<?= $this->url('admin') ?>" 
       class="<?= $_SERVER['REQUEST_URI'] === '/admin' ? 'bg-red-100 text-red-900 border-r-2 border-red-500' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
        <i class="fas fa-tachometer-alt mr-3 h-5 w-5"></i>
        <span x-show="!sidebarCollapsed">Dashboard</span>
    </a>

    <!-- Members -->
    <?php if ($auth->hasPermission('view_members')): ?>
    <a href="<?= $this->url('admin/members') ?>" 
       class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/members') === 0 ? 'bg-red-100 text-red-900 border-r-2 border-red-500' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
        <i class="fas fa-users mr-3 h-5 w-5"></i>
        <span x-show="!sidebarCollapsed">Members</span>
    </a>
    <?php endif; ?>

    <!-- Subscriptions -->
    <?php if ($auth->hasPermission('view_subscriptions')): ?>
    <a href="<?= $this->url('admin/subscriptions') ?>" 
       class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/subscriptions') === 0 ? 'bg-red-100 text-red-900 border-r-2 border-red-500' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
        <i class="fas fa-credit-card mr-3 h-5 w-5"></i>
        <span x-show="!sidebarCollapsed">Subscriptions</span>
    </a>
    <?php endif; ?>

    <!-- Classes -->
    <?php if ($auth->hasPermission('view_classes')): ?>
    <a href="<?= $this->url('admin/classes') ?>" 
       class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/classes') === 0 ? 'bg-red-100 text-red-900 border-r-2 border-red-500' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
        <i class="fas fa-calendar-alt mr-3 h-5 w-5"></i>
        <span x-show="!sidebarCollapsed">Classes</span>
    </a>
    <?php endif; ?>

    <!-- Payments -->
    <?php if ($auth->hasPermission('view_payments')): ?>
    <a href="<?= $this->url('admin/payments') ?>" 
       class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/payments') === 0 ? 'bg-red-100 text-red-900 border-r-2 border-red-500' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
        <i class="fas fa-pound-sign mr-3 h-5 w-5"></i>
        <span x-show="!sidebarCollapsed">Payments</span>
    </a>
    <?php endif; ?>

    <!-- Messages -->
    <?php if ($auth->hasPermission('view_member_messages')): ?>
    <a href="<?= $this->url('admin/messages') ?>" 
       class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/messages') === 0 ? 'bg-red-100 text-red-900 border-r-2 border-red-500' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
        <i class="fas fa-envelope mr-3 h-5 w-5"></i>
        <span x-show="!sidebarCollapsed">Messages</span>
        <?php $unreadCount = count(App\Models\Message::getUnreadForAdmin()); ?>
        <?php if ($unreadCount > 0): ?>
            <span class="ml-auto bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                <?= $unreadCount ?>
            </span>
        <?php endif; ?>
    </a>
    <?php endif; ?>

    <!-- Admin Users -->
    <?php if ($auth->hasPermission('manage_users')): ?>
    <a href="<?= $this->url('admin/users') ?>" 
       class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/users') === 0 ? 'bg-red-100 text-red-900 border-r-2 border-red-500' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
        <i class="fas fa-user-shield mr-3 h-5 w-5"></i>
        <span x-show="!sidebarCollapsed">Admin Users</span>
    </a>
    <?php endif; ?>

    <!-- Roles -->
    <?php if ($auth->hasPermission('manage_roles')): ?>
    <a href="<?= $this->url('admin/roles') ?>" 
       class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/roles') === 0 ? 'bg-red-100 text-red-900 border-r-2 border-red-500' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
        <i class="fas fa-key mr-3 h-5 w-5"></i>
        <span x-show="!sidebarCollapsed">Roles & Permissions</span>
    </a>
    <?php endif; ?>

    <!-- Settings -->
    <?php if ($auth->hasPermission('manage_settings')): ?>
    <a href="<?= $this->url('admin/settings') ?>" 
       class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/settings') === 0 ? 'bg-red-100 text-red-900 border-r-2 border-red-500' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
        <i class="fas fa-cog mr-3 h-5 w-5"></i>
        <span x-show="!sidebarCollapsed">Settings</span>
    </a>
    <?php endif; ?>
</div>