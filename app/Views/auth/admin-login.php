<?php
// =====================================
// app/Views/auth/admin-login.php
?>
<div class="sm:mx-auto sm:w-full sm:max-w-md">
    <div class="text-center">
        <h1 class="text-3xl font-bold" style="color: var(--primary-color)">
            <?= $this->e($_ENV['APP_NAME'] ?? 'Club Manager') ?>
        </h1>
        <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
            Admin Sign In
        </h2>
        <p class="mt-2 text-sm text-gray-600">
            Access the administrative panel
        </p>
    </div>
</div>

<div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
    <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <form class="space-y-6" action="<?= $this->url('admin/login') ?>" method="POST">
            <?= $this->csrf() ?>
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">
                    Email address
                </label>
                <div class="mt-1">
                    <input id="email" name="email" type="email" autocomplete="email" required 
                           value="<?= $this->old('email') ?>"
                           class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-2 focus:border-transparent <?= $this->hasError('email') ? 'border-red-300 focus:ring-red-500' : 'focus:ring-red-500' ?>"
                           placeholder="Enter your admin email">
                </div>
                <?php if ($this->hasError('email')): ?>
                    <p class="mt-2 text-sm text-red-600"><?= $this->error('email') ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">
                    Password
                </label>
                <div class="mt-1">
                    <input id="password" name="password" type="password" autocomplete="current-password" required 
                           class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-2 focus:border-transparent <?= $this->hasError('password') ? 'border-red-300 focus:ring-red-500' : 'focus:ring-red-500' ?>"
                           placeholder="Enter your password">
                </div>
                <?php if ($this->hasError('password')): ?>
                    <p class="mt-2 text-sm text-red-600"><?= $this->error('password') ?></p>
                <?php endif; ?>
            </div>

            <div>
                <button type="submit" 
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 hover:opacity-90 transition-opacity"
                        style="background-color: var(--primary-color)">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Sign in to Admin Panel
                </button>
            </div>
        </form>

        <div class="mt-6 text-center">
            <a href="<?= $this->url('/') ?>" class="text-sm font-medium hover:underline" style="color: var(--primary-color)">
                <i class="fas fa-arrow-left mr-1"></i>
                Back to Member Portal
            </a>
        </div>
    </div>
</div>