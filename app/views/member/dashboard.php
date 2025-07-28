<?php
// app/views/member/dashboard.php
 require_once VIEW_PATH . '/member/layouts/member.php';  use App\Helpers\functions as Helpers; ?>

<?php Helpers\start_section('content'); ?>

<h1 class="text-2xl font-semibold text-gray-900 mb-6">Welcome, <?= Helpers\esc($member['first_name']) ?>!</h1>

<?php Helpers\displayFlashMessages(); ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white overflow-hidden shadow rounded-lg lg:col-span-2">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Your Upcoming Classes</h3>
            <?php if (!empty($upcomingClasses)): ?>
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($upcomingClasses as $class): ?>
                        <li class="py-4 flex flex-col sm:flex-row justify-between items-start sm:items-center">
                            <div>
                                <p class="text-sm font-medium text-indigo-600"><?= Helpers\esc($class['class_name']) ?></p>
                                <p class="text-sm text-gray-500">
                                    <time datetime="<?= (new DateTime($class['instance_date_time']))->format('Y-m-d H:i') ?>">
                                        <?= (new DateTime($class['instance_date_time']))->format('D, M jS, Y \a\t H:i') ?>
                                    </time>
                                </p>
                            </div>
                            <div class="mt-2 sm:mt-0">
                                <a href="/classes" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">View Class Details &rarr;</a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-gray-500">You don't have any upcoming classes booked at the moment.</p>
                <div class="mt-4">
                    <a href="/classes" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">Browse Classes &rarr;</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Latest Notifications</h3>
            <?php if (!empty($notifications)): ?>
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($notifications as $notification): ?>
                        <li class="py-4">
                            <p class="text-sm text-gray-800 <?= $notification['is_read'] ? 'text-gray-500' : 'font-semibold' ?>">
                                <?= Helpers\esc($notification['message']) ?>
                            </p>
                            <p class="text-xs text-gray-400 mt-1">
                                <?= (new DateTime($notification['created_at']))->format('M j, Y \a\t H:i') ?>
                            </p>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="mt-4 text-right">
                    <a href="/notifications" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">View All Notifications &rarr;</a>
                </div>
            <?php else: ?>
                <p class="text-gray-500">No new notifications.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Your Subscriptions</h3>
            <p class="text-gray-500">View your active and past subscriptions, or sign up for new ones.</p>
            <div class="mt-4">
                <a href="/subscriptions" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">Manage Subscriptions &rarr;</a>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Payment Methods & History</h3>
            <p class="text-gray-500">Manage your payment methods and view your payment history.</p>
            <div class="mt-4">
                <a href="/payment-methods" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">Manage Payments &rarr;</a>
            </div>
        </div>
    </div>
</div>

<?php Helpers\end_section(); ?>

