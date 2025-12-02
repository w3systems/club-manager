<div x-data="{ open: false }" class="ml-3 relative">
    <div>
        <button @click="open = !open" type="button" class="max-w-xs bg-white flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
            <span class="sr-only">Open user menu</span>
             <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                <span class="text-sm font-medium text-gray-700">
                    <?= $auth->user()->getInitials() ?>
                </span>
            </div>
        </button>
    </div>

    <div 
        x-show="open" 
        @click.away="open = false"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none" 
        role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1">
        
        <a href="<?= $this->url('admin/profile') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">Your Profile</a>
        <a href="<?= $this->url('admin/settings') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">Settings</a>
        <a href="<?= $this->url('admin/logout') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">Sign out</a>
    </div>
</div>