<?php
// app/views/admin/dashboard.php
 //require_once VIEW_PATH . '/admin/layouts/admin.php';
 //use App\Helpers\functions as Helpers; 
 ?>

<?php start_section('content'); ?>

<h1 class="text-2xl font-semibold text-gray-900 mb-6">Admin Dashboard</h1>

<?php displayFlashMessages(); ?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Total Members</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900"><?= esc($totalMembers) ?></dd>
            </dl>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Active Subscriptions</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900"><?= esc($activeSubscriptionsCount) ?></dd>
            </dl>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Pending Payments</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900"><?= esc($pendingPayments) ?></dd>
            </dl>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Failed Payments</dt>
                <dd class="mt-1 text-3xl font-semibold text-red-600"><?= esc($failedPayments) ?></dd>
            </dl>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Unread Member Messages</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900"><?= esc($unreadMessages) ?></dd>
            </dl>
        </div>
    </div>
</div>

<div class="mt-8 bg-white overflow-hidden shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Upcoming Classes</h3>
        <?php if (!empty($upcomingClasses)): ?>
            <ul class="divide-y divide-gray-200">
                <?php foreach ($upcomingClasses as $class): ?>
                    <li class="py-4 flex flex-col sm:flex-row justify-between items-start sm:items-center">
                        <div>
                            <p class="text-sm font-medium text-indigo-600"><?= esc($class['name']) ?></p>
                            <p class="text-sm text-gray-500">
                                <time datetime="<?= (new DateTime($class['instance_date_time']))->format('Y-m-d H:i') ?>">
                                    <?= (new DateTime($class['instance_date_time']))->format('D, M jS, Y \a\t H:i') ?>
                                </time>
                            </p>
                            <?php if ($class['capacity']): ?>
                                <p class="text-xs text-gray-400">Capacity: <?= esc($class['capacity']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="mt-2 sm:mt-0">
                            <a href="/admin/classes/calendar" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">View Calendar &rarr;</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-gray-500">No upcoming classes scheduled.</p>
        <?php endif; ?>
    </div>
</div>

<?php end_section(); ?>
