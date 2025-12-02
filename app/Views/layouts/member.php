<?php
// app/Views/layouts/member.php - UPDATED FOR CDN
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $this->e($title) . ' - ' : '' ?><?= $this->e($_ENV['APP_NAME'] ?? 'Club Manager') ?></title>
    <meta name="csrf-token" content="<?= $csrfToken ?>">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#fef2f2', 100: '#fee2e2', 200: '#fecaca', 300: '#fca5a5', 400: '#f87171',
                            500: '#ef4444', 600: '#dc2626', 700: '#b91c1c', 800: '#991b1b', 900: '#7f1d1d'
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Alpine.js CDN -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="<?= App\Models\Setting::get('site_color_primary', '#971b1e') ?>">
    <link rel="manifest" href="<?= $this->url('manifest.json') ?>">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="<?= $this->e($_ENV['APP_NAME'] ?? 'Club Manager') ?>">
    
    <style>
        :root {
            --primary-color: <?= App\Models\Setting::get('site_color_primary', '#971b1e') ?>;
            --secondary-color: <?= App\Models\Setting::get('site_color_secondary', '#cda22d') ?>;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        .btn-primary:hover {
            filter: brightness(0.9);
        }
        
        .text-primary { color: var(--primary-color); }
        .bg-primary { background-color: var(--primary-color); }
        .border-primary { border-color: var(--primary-color); }
    </style>
</head>
<body class="h-full">
    <!-- Rest of the body content remains the same -->
    <div x-data="{ sidebarOpen: false, sidebarCollapsed: false }" class="flex h-full">
        <!-- Sidebar content unchanged -->
        <div class="relative">
            <div x-show="sidebarOpen" @click="sidebarOpen = false" 
                 x-transition.opacity class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden"></div>
            
            <div :class="sidebarCollapsed ? 'w-16' : 'w-64'" 
                 class="fixed inset-y-0 left-0 z-50 flex flex-col transition-all duration-300 bg-white border-r border-gray-200 lg:relative lg:translate-x-0"
                 :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
                
                <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200">
                    <div x-show="!sidebarCollapsed" class="flex items-center">
                        <h1 class="text-xl font-bold text-primary">
                            <?= $this->e($_ENV['APP_NAME'] ?? 'Club Manager') ?>
                        </h1>
                    </div>
                    <button @click="sidebarCollapsed = !sidebarCollapsed" 
                            class="p-1 text-gray-500 hover:text-gray-700 hidden lg:block">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
                
                <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
                    <?php $this->component('sidebar.member-nav', ['collapsed' => false]) ?>
                </nav>
                
                <div class="flex-shrink-0 p-4 border-t border-gray-200">
                    <?php $this->component('sidebar.member-user-menu', ['collapsed' => false]) ?>
                </div>
            </div>
        </div>
        
        <div class="flex flex-col flex-1 min-w-0">
            <div class="flex items-center justify-between h-16 px-4 bg-white border-b border-gray-200 lg:px-6">
                <button @click="sidebarOpen = !sidebarOpen" class="p-1 text-gray-500 hover:text-gray-700 lg:hidden">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="flex items-center space-x-4">
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="p-2 text-gray-500 hover:text-gray-700 relative">
                            <i class="fas fa-bell"></i>
                            <?php if (isset($unreadNotifications) && count($unreadNotifications) > 0): ?>
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                    <?= count($unreadNotifications) ?>
                                </span>
                            <?php endif; ?>
                        </button>
                    </div>
                    
                    <span class="text-sm text-gray-500">Hi, <?= $this->e($auth->user()->first_name) ?>!</span>
                </div>
            </div>
            
            <main class="flex-1 p-4 lg:p-6 overflow-auto">
                <?php $this->component('alerts') ?>
                <?= $content ?>
            </main>
        </div>
    </div>
    
    <!-- Include the simple JavaScript -->
    <script src="<?= $this->url('assets/js/app.js') ?>"></script>
</body>
</html>