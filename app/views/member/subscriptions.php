<?php
// app/views/member/subscriptions.php
 require_once VIEW_PATH . '/member/layouts/member.php';  use App\Helpers\functions as Helpers; ?>

<?php Helpers\start_section('content'); ?>

<h1 class="text-2xl font-semibold text-gray-900 mb-6">Your Subscriptions</h1>

<?php Helpers\displayFlashMessages(); ?>

<div class="mb-8">
    <h2 class="text-xl font-medium text-gray-900 mb-4">Active Subscriptions</h2>
    <?php if (!empty($activeSubscriptions)): ?>
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <ul role="list" class="divide-y divide-gray-200">
                <?php foreach ($activeSubscriptions as $sub): ?>
                    <li class="px-4 py-5 sm:px-6">
                        <div class="flex items-center justify-between">
                            <p class="text-lg font-medium text-indigo-600"><?= Helpers\esc($sub['subscription_name']) ?></p>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Active
                            </span>
                        </div>
                        <div class="mt-2 text-sm text-gray-500">
                            <p><?= Helpers\esc($sub['description']) ?></p>
                            <p class="mt-1">Starts: <?= (new DateTime($sub['start_date']))->format('M j, Y') ?></p>
                            <?php if ($sub['end_date']): ?>
                                <p>Ends: <?= (new DateTime($sub['end_date']))->format('M j, Y') ?></p>
                            <?php endif; ?>
                            <?php if ($sub['type'] === 'recurring' && $sub['next_renewal_date']): ?>
                                <p>Next Renewal: <?= (new DateTime($sub['next_renewal_date']))->format('M j, Y') ?></p>
                            <?php endif; ?>
                            <p class="font-medium text-gray-700 mt-2">Price: &pound;<?= Helpers\esc(number_format($sub['price'], 2)) ?> / <?= Helpers\esc($sub['term_unit'] ? $sub['term_unit'] : 'n/a') ?></p>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else: ?>
        <p class="text-gray-500">You do not have any active subscriptions.</p>
    <?php endif; ?>
</div>

<div class="mb-8">
    <h2 class="text-xl font-medium text-gray-900 mb-4">Available Subscriptions</h2>
    <?php if (!empty($availableSubscriptions)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($availableSubscriptions as $sub): ?>
                <div class="bg-white overflow-hidden shadow rounded-lg p-6 flex flex-col justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900"><?= Helpers\esc($sub['name']) ?></h3>
                        <p class="mt-2 text-sm text-gray-500"><?= Helpers\esc($sub['description']) ?></p>
                        <p class="mt-3 text-sm font-medium text-gray-700">Price: &pound;<?= Helpers\esc(number_format($sub['price'], 2)) ?> / <?= Helpers\esc($sub['term_unit'] ? $sub['term_unit'] : 'one-time') ?></p>
                        <?php if ($sub['type'] === 'recurring' && $sub['prorata_enabled']): ?>
                            <p class="text-xs text-gray-500">
                                (Pro-rata first month available for &pound;<?= Helpers\esc(number_format($sub['prorata_price'] + $sub['admin_fee'], 2)) ?>)
                            </p>
                        <?php endif; ?>
                        <?php if ($sub['free_trial_enabled']): ?>
                            <p class="text-xs text-indigo-600 font-semibold mt-1">Free Trial Available!</p>
                        <?php endif; ?>
                        <?php if ($sub['capacity'] !== null): ?>
                            <p class="mt-1 text-sm text-gray-700">Capacity: Limited to <?= Helpers\esc($sub['capacity']) ?> members</p>
                        <?php endif; ?>
                        <?php if ($sub['min_age'] || $sub['max_age']): ?>
                            <p class="mt-1 text-sm text-gray-700">
                                Age Range:
                                <?php if ($sub['min_age'] && $sub['max_age']): ?>
                                    <?= Helpers\esc($sub['min_age']) ?> - <?= Helpers\esc($sub['max_age']) ?> years
                                <?php elseif ($sub['min_age']): ?>
                                    <?= Helpers\esc($sub['min_age']) ?>+ years
                                <?php else: ?>
                                    Up to <?= Helpers\esc($sub['max_age']) ?> years
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="mt-4">
                        <a href="/subscriptions/new?id=<?= Helpers\esc($sub['id']) ?>" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Sign Up
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-gray-500">No new subscriptions are currently available for you.</p>
    <?php endif; ?>
</div>

<div class="mb-8">
    <h2 class="text-xl font-medium text-gray-900 mb-4">Past Subscriptions</h2>
    <?php if (!empty($pastSubscriptions)): ?>
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <ul role="list" class="divide-y divide-gray-200">
                <?php foreach ($pastSubscriptions as $sub): ?>
                    <li class="px-4 py-5 sm:px-6">
                        <div class="flex items-center justify-between">
                            <p class="text-lg font-medium text-gray-800"><?= Helpers\esc($sub['subscription_name']) ?></p>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                <?= Helpers\esc(ucfirst($sub['status'])) ?>
                            </span>
                        </div>
                        <div class="mt-2 text-sm text-gray-500">
                            <p><?= Helpers\esc($sub['description']) ?></p>
                            <p class="mt-1">Starts: <?= (new DateTime($sub['start_date']))->format('M j, Y') ?></p>
                            <?php if ($sub['end_date']): ?>
                                <p>Ended: <?= (new DateTime($sub['end_date']))->format('M j, Y') ?></p>
                            <?php elseif ($sub['cancellation_date']): ?>
                                <p>Cancelled: <?= (new DateTime($sub['cancellation_date']))->format('M j, Y') ?></p>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else: ?>
        <p class="text-gray-500">You have no past subscriptions.</p>
    <?php endif; ?>
</div>

<?php Helpers\end_section(); ?>
