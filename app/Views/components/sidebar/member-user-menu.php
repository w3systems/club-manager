<?php
// =====================================
// app/Views/components/sidebar/member-user-menu.php
?>
<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" class="flex items-center w-full p-2 text-sm font-medium text-gray-600 rounded-md hover:bg-gray-50 hover:text-gray-900">
        <div class="flex items-center justify-center w-8 h-8 bg-gray-200 rounded-full mr-3">
            <span class="text-sm font-medium text-gray-700">
                <?= $auth->user()->getInitials() ?>
            </span>
        </div>
        <div x-show="!sidebarCollapsed" class="flex-1 text-left">
            <p class="text-sm font-medium"><?= $this->e($auth->user()->getFullName()) ?></p>
            <p class="text-xs text-gray-500"><?= $this->e($auth->user()->email) ?></p>
        </div>
        <i x-show="!sidebarCollapsed" class="fas fa-chevron-down ml-2"></i>
    </button>
    
    <div x-show="open" @click.away="open = false" 
         x-transition class="absolute bottom-full left-0 w-full mb-2 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5">
        <div class="py-1">
            <a href="<?= $this->url('profile') ?>" 
               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                <i class="fas fa-user mr-2"></i>
                Profile Settings
            </a>
            <a href="<?= $this->url('logout') ?>" 
               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                <i class="fas fa-sign-out-alt mr-2"></i>
                Sign out
            </a>
        </div>
    </div>
</div>