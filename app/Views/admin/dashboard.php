<?php
// app/Views/admin/dashboard.php
?>
<div class="space-y-6">
    <!-- Page header -->
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Admin Dashboard</h1>
        <p class="text-gray-600">Welcome back, <?= $this->e($auth->user()->getFullName()) ?>!</p>
    </div>

    <!-- Stats overview -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <!-- Total Members -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-users text-2xl text-blue-500"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Members</dt>
                            <dd class="text-3xl font-semibold text-gray-900"><?= number_format($stats['total_members']) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Subscriptions -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-credit-card text-2xl text-green-500"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Active Subscriptions</dt>
                            <dd class="text-3xl font-semibold text-gray-900"><?= number_format($stats['active_subscriptions']) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Revenue -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-pound-sign text-2xl text-yellow-500"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Today's Revenue</dt>
                            <dd class="text-3xl font-semibold text-gray-900"><?= format_currency($stats['today_revenue']) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- This Month's Revenue -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-chart-line text-2xl text-purple-500"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Monthly Revenue</dt>
                            <dd class="text-3xl font-semibold text-gray-900"><?= format_currency($stats['month_revenue']) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Classes -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-calendar-alt text-2xl text-indigo-500"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Today's Classes</dt>
                            <dd class="text-3xl font-semibold text-gray-900"><?= number_format($stats['today_classes']) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Failed Payments -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-2xl text-red-500"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Failed Payments</dt>
                            <dd class="text-3xl font-semibold text-gray-900"><?= number_format($stats['failed_payments']) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Recent Members -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Recent Members</h3>
            </div>
            <div class="divide-y divide-gray-200">
                <?php if (empty($recentMembers)): ?>
                    <div class="px-6 py-4 text-center text-gray-500">
                        No recent members found.
                    </div>
                <?php else: ?>
                    <?php foreach ($recentMembers as $member): ?>
                        <div class="px-6 py-4 flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8">
                                    <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                        <span class="text-sm font-medium text-gray-700">
                                            <?= $member->initials ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">
                                        <?= $this->e($member->full_name) ?>
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        <?= $this->e($member->email) ?>
                                    </p>
                                </div>
                            </div>
                            <div class="text-sm text-gray-500">
                                <?= time_ago($member->created_at) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="px-6 py-3 bg-gray-50 text-right">
                <a href="<?= $this->url('admin/members') ?>" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    View all members →
                </a>
            </div>
        </div>

        <!-- Upcoming Classes -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Upcoming Classes</h3>
            </div>
            <div class="divide-y divide-gray-200">
                <?php if (empty($upcomingClasses)): ?>
                    <div class="px-6 py-4 text-center text-gray-500">
                        No upcoming classes found.
                    </div>
                <?php else: ?>
                    <?php foreach ($upcomingClasses as $class): ?>
                        <div class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        <?= $this->e($class->name) ?>
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        <?= $class->formatted_date_time ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-900">
                                        <?= $class->current_booking_count ?>/<?= $class->capacity ?: '∞' ?> booked
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="px-6 py-3 bg-gray-50 text-right">
                <a href="<?= $this->url('admin/classes') ?>" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    View all classes →
                </a>
            </div>
        </div>
    </div>

    <!-- Failed Payments & Messages -->
    <?php if (!empty($failedPayments) || !empty($unreadMessages)): ?>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Failed Payments -->
        <?php if (!empty($failedPayments)): ?>
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 text-red-600">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Failed Payments
                </h3>
            </div>
            <div class="divide-y divide-gray-200">
                <?php foreach (array_slice($failedPayments, 0, 5) as $payment): ?>
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    <?= $this->e($payment->first_name . ' ' . $payment->last_name) ?>
                                </p>
                                <p class="text-sm text-gray-500">
                                    <?= $this->e($payment->email) ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-red-600">
                                    <?= format_currency($payment->amount, $payment->currency) ?>
                                </p>
                                <p class="text-sm text-gray-500">
                                    <?= format_date($payment->payment_date, 'd/m/Y') ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="px-6 py-3 bg-gray-50 text-right">
                <a href="<?= $this->url('admin/payments') ?>" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    View all payments →
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Unread Messages -->
        <?php if (!empty($unreadMessages)): ?>
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-envelope mr-2"></i>
                    New Messages
                </h3>
            </div>
            <div class="divide-y divide-gray-200">
                <?php foreach (array_slice($unreadMessages, 0, 5) as $message): ?>
                    <div class="px-6 py-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    <?= $this->e($message->first_name . ' ' . $message->last_name) ?>
                                </p>
                                <p class="text-sm text-gray-600 mt-1">
                                    <?= $this->truncate($message->content, 80) ?>
                                </p>
                            </div>
                            <div class="text-sm text-gray-500 ml-4">
                                <?= time_ago($message->created_at) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="px-6 py-3 bg-gray-50 text-right">
                <a href="<?= $this->url('admin/messages') ?>" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    View all messages →
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>