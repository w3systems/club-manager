<?php
function get_nav_classes($uri, $path) {
    $isActive = $uri === $path || ($path !== '/admin' && strpos($uri, $path) === 0);
    if ($isActive) {
        return 'bg-gray-900 text-white'; // Active state
    } else {
        return 'text-gray-400 hover:bg-gray-700 hover:text-white';
    }
}
$currentUri = $_SERVER['REQUEST_URI'];
?>

<a href="<?= $this->url('admin') ?>" class="<?= get_nav_classes($currentUri, '/admin') ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
    <div class="h-6 flex items-center justify-center flex-shrink-0">
        <i class="fas fa-tachometer-alt fa-fw"></i>
    </div>
    <span x-show="!sidebarCollapsed" class="ml-2 flex-1">Dashboard</span>
</a>
<a href="<?= $this->url('admin/members') ?>" class="<?= get_nav_classes($currentUri, '/admin/members') ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
    <div class="h-6 flex items-center justify-center flex-shrink-0">
        <i class="fas fa-users fa-fw"></i>
    </div>
    <span x-show="!sidebarCollapsed" class="ml-2 flex-1">Members</span>
</a>
<a href="<?= $this->url('admin/subscriptions') ?>" class="<?= get_nav_classes($currentUri, '/admin/subscriptions') ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
    <div class="h-6 flex items-center justify-center flex-shrink-0">
        <i class="fas fa-credit-card fa-fw"></i>
    </div>
    <span x-show="!sidebarCollapsed" class="ml-2 flex-1">Subscriptions</span>
</a>
<a href="<?= $this->url('admin/classes') ?>" class="<?= get_nav_classes($currentUri, '/admin/classes') ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
    <div class="h-6 flex items-center justify-center flex-shrink-0">
        <i class="fas fa-calendar-alt fa-fw"></i>
    </div>
    <span x-show="!sidebarCollapsed" class="ml-2 flex-1">Classes</span>
</a>
<a href="<?= $this->url('admin/payments') ?>" class="<?= get_nav_classes($currentUri, '/admin/payments') ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
    <div class="h-6 flex items-center justify-center flex-shrink-0">
        <i class="fas fa-pound-sign fa-fw"></i>
    </div>
    <span x-show="!sidebarCollapsed" class="ml-2 flex-1">Payments</span>
</a>

<div class="pt-4 mt-4 border-t border-gray-700"></div>

<a href="<?= $this->url('admin/users') ?>" class="<?= get_nav_classes($currentUri, '/admin/users') ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
    <div class="h-6 flex items-center justify-center flex-shrink-0">
        <i class="fas fa-user-shield fa-fw"></i>
    </div>
    <span x-show="!sidebarCollapsed" class="ml-2 flex-1">Admin Users</span>
</a>
<a href="<?= $this->url('admin/roles') ?>" class="<?= get_nav_classes($currentUri, '/admin/roles') ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
    <div class="h-6 flex items-center justify-center flex-shrink-0">
        <i class="fas fa-key fa-fw"></i>
    </div>
    <span x-show="!sidebarCollapsed" class="ml-2 flex-1">Roles & Permissions</span>
</a>

<div class="pt-4 mt-4 border-t border-gray-700"></div>

<a href="<?= $this->url('admin/settings') ?>" class="<?= get_nav_classes($currentUri, '/admin/settings') ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
    <div class="h-6 flex items-center justify-center flex-shrink-0">
        <i class="fas fa-cog fa-fw"></i>
    </div>
    <span x-show="!sidebarCollapsed" class="ml-2 flex-1">Settings</span>
</a>