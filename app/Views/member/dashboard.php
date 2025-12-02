<?php
// =====================================
// app/Views/member/dashboard.php
?>
<div class="space-y-6">
    <!-- Welcome header -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Welcome back, <?= $this->e($member->first_name) ?>!
                </h1>
                <p class="text-gray-600 mt-1">
                    Here's what's happening with your membership
                </p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Member since</p>
                <p class="text-lg font-semibold text-gray-900">
                    <?= format_date($member->created_at, 'M Y') ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Quick stats -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Active Subscriptions -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-credit-card text-2xl text-green-500"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Active Plans</dt>
                            <dd class="text-3xl font-semibold text-gray-900"><?= $stats['active_subscriptions'] ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Classes Attended -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-calendar-check text-2xl text-blue-500"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Classes Attended</dt>
                            <dd class="text-3xl font-semibold text-gray-900"><?= $stats['attended_classes'] ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Paid -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-pound-sign text-2xl text-purple-500"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Paid</dt>
                            <dd class="text-3xl font-semibold text-gray-900"><?= format_currency($stats['total_paid']) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Outstanding Balance -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-2xl <?= $stats['outstanding_balance'] > 0 ? 'text-red-500' : 'text-gray-400' ?>"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Outstanding</dt>
                            <dd class="text-3xl font-semibold <?= $stats['outstanding_balance'] > 0 ? 'text-red-600' : 'text-gray-900' ?>">
                                <?= format_currency($stats['outstanding_balance']) ?>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Subscriptions -->
    <?php if (!empty($activeSubscriptions)): ?>
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Your Active Subscriptions</h3>
        </div>
        <div class="divide-y divide-gray-200">
            <?php foreach ($activeSubscriptions as $subscription): ?>
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">
                                <?= $this->e($subscription->subscription_name) ?>
                            </h4>
                            <p class="text-sm text-gray-500">
                                Started <?= format_date($subscription->start_date, 'd/m/Y') ?>
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900">
                                <?= $subscription->getFormattedEffectivePrice() ?>
                            </p>
                            <?php if ($subscription->next_renewal_date): ?>
                                <p class="text-sm text-gray-500">
                                    Next: <?= format_date($subscription->next_renewal_date, 'd/m/Y') ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="px-6 py-3 bg-gray-50 text-right">
            <a href="<?= $this->url('subscriptions') ?>" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                Manage subscriptions →
            </a>
        </div>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Upcoming Classes -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Your Upcoming Classes</h3>
            </div>
            <div class="divide-y divide-gray-200">
                <?php if (empty($upcomingClasses)): ?>
                    <div class="px-6 py-8 text-center">
                        <i class="fas fa-calendar-alt text-gray-400 text-3xl mb-4"></i>
                        <p class="text-gray-500">No upcoming classes booked.</p>
                        <a href="<?= $this->url('classes') ?>" 
                           class="mt-2 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            Browse Classes
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($upcomingClasses as $item): ?>
                        <div class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">
                                        <?= $this->e($item['class']->name) ?>
                                    </h4>
                                    <p class="text-sm text-gray-500">
                                        <?= $item['class']->getFormattedDateTime() ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Booked
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="px-6 py-3 bg-gray-50 text-right">
                <a href="<?= $this->url('classes') ?>" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    View all classes →
                </a>
            </div>
        </div>

        <!-- Available Classes -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Available to Book</h3>
            </div>
            <div class="divide-y divide-gray-200">
                <?php if (empty($availableClasses)): ?>
                    <div class="px-6 py-8 text-center">
                        <i class="fas fa-info-circle text-gray-400 text-3xl mb-4"></i>
                        <p class="text-gray-500">No classes available for booking.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($availableClasses as $class): ?>
                        <div class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">
                                        <?= $this->e($class->name) ?>
                                    </h4>
                                    <p class="text-sm text-gray-500">
                                        <?= $class->getFormattedDateTime() ?>
                                    </p>
                                </div>
                                <div>
                                    <form action="<?= $this->url('classes/' . $class->id . '/book') ?>" method="POST" class="inline">
                                        <?= $this->csrf() ?>
                                        <button type="submit" 
                                                class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200">
                                            Book Now
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <?php if (!empty($recentPayments)): ?>
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Recent Payments</h3>
        </div>
        <div class="divide-y divide-gray-200">
            <?php foreach (array_slice($recentPayments, 0, 5) as $payment): ?>
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                <?= $this->e($payment->description ?: 'Subscription Payment') ?>
                            </p>
                            <p class="text-sm text-gray-500">
                                <?= format_date($payment->payment_date, 'd/m/Y H:i') ?>
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900">
                                <?= $payment->getFormattedAmount() ?>
                            </p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $payment->getStatusColorClass() ?>">
                                <?= $payment->getStatusLabel() ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="px-6 py-3 bg-gray-50 text-right">
            <a href="<?= $this->url('payments') ?>" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                View all payments →
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>