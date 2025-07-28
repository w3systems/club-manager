<?php
// app/Views/auth/member-login.php
?>
<div class="sm:mx-auto sm:w-full sm:max-w-md">
    <div class="text-center">
        <h1 class="text-3xl font-bold" style="color: var(--primary-color)">
            <?= $this->e($_ENV['APP_NAME'] ?? 'Club Manager') ?>
        </h1>
        <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
            Sign in to your account
        </h2>
        <p class="mt-2 text-sm text-gray-600">
            Or
            <a href="<?= $this->url('register') ?>" class="font-medium hover:underline" style="color: var(--primary-color)">
                create a new account
            </a>
        </p>
    </div>
</div>

<div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
    <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <form class="space-y-6" action="<?= $this->url('login') ?>" method="POST">
            <?= $this->csrf() ?>
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">
                    Email address
                </label>
                <div class="mt-1">
                    <input id="email" name="email" type="email" autocomplete="email" required 
                           value="<?= $this->old('email') ?>"
                           class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-2 focus:border-transparent <?= $this->hasError('email') ? 'border-red-300 focus:ring-red-500' : 'focus:ring-red-500' ?>"
                           placeholder="Enter your email">
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

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember-me" name="remember" type="checkbox" 
                           class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                    <label for="remember-me" class="ml-2 block text-sm text-gray-900">
                        Remember me
                    </label>
                </div>

                <div class="text-sm">
                    <a href="#" class="font-medium hover:underline" style="color: var(--primary-color)">
                        Forgot your password?
                    </a>
                </div>
            </div>

            <div>
                <button type="submit" 
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 hover:opacity-90 transition-opacity"
                        style="background-color: var(--primary-color)">
                    Sign in
                </button>
            </div>
        </form>

        <div class="mt-6">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300" />
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">New to our club?</span>
                </div>
            </div>

            <div class="mt-6">
                <a href="<?= $this->url('free-trial') ?>" 
                   class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <i class="fas fa-calendar-check mr-2"></i>
                    Book a Free Trial Class
                </a>
            </div>
        </div>
    </div>
</div>