<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $this->e($title) . ' - ' : '' ?><?= $this->e($_ENV['APP_NAME'] ?? 'Club Manager') ?> Admin</title>
    <meta name="csrf-token" content="<?= $csrfToken ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="/assets/css/admin.css">
    <style>:root { --primary-color: <?= App\Models\Setting::get('site_color_primary', '#971b1e') ?>; }</style>
</head>
<body class="h-full">
    <div x-data="{ sidebarCollapsed: false }" class="flex h-full">
        <div class="flex-shrink-0 transition-all duration-300 bg-gray-800" :class="sidebarCollapsed ? 'w-14' : 'w-64'">
            <div class="flex flex-col h-full">
                <div class="h-24 flex-shrink-0 flex items-center justify-between px-2">
                    <div x-show="!sidebarCollapsed" x-transition:enter="transition-opacity ease-in-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-in-out duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="flex-1 flex flex-col items-start pl-2 items-center">
                        <?php if (!empty($appLogoPath)): ?>
                            <img class="object-contain mb-1" src="<?= htmlspecialchars($appLogoPath) ?>" alt="Logo" style="max-height: 4rem;">
                        <?php else: ?>
                            <span class="text-white font-semibold text-xl justify-center"><?= $this->e($_ENV['APP_NAME'] ?? 'Club Manager') ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="/*w-12*/ h-full flex items-center justify-center">
                        <button @click="sidebarCollapsed = !sidebarCollapsed" class="text-gray-400 hover:text-white focus:outline-none p-2 rounded-md">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>
                </div>
                <nav class="flex-1 px-2 py-4 space-y-1">
                    <?php $this->component('sidebar.admin-nav') ?>
                </nav>
            </div>
        </div>

        <div class="flex flex-col w-0 flex-1 overflow-hidden">
            <div class="relative z-10 flex-shrink-0 flex h-16 bg-white shadow">
                 <div class="flex-1 px-4 flex justify-between items-center">
                    <div class="flex items-center">
                        <button type="button" class="bg-white p-1 rounded-full text-gray-400 hover:text-gray-500"><i class="far fa-bell h-6 w-6"></i></button>
                        <span class="ml-3 text-sm text-gray-600 hidden sm:block">Hi, <?= $this->e($auth->user()->first_name) ?>!</span>
                    </div>
                    <div class="ml-4 flex items-center md:ml-6">
                        <?php $this->component('sidebar.admin-user-menu') ?>
                    </div>
                </div>
            </div>

            <main class="flex-1 relative overflow-y-auto focus:outline-none">
                <div class="py-6">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                         <?= $content ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>