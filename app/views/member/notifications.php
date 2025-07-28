<?php
// app/views/member/notifications.php
 require_once VIEW_PATH . '/member/layouts/member.php';  use App\Helpers\functions as Helpers; ?>

<?php Helpers\start_section('content'); ?>

<h1 class="text-2xl font-semibold text-gray-900 mb-6">Your Notifications</h1>

<?php Helpers\displayFlashMessages(); ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white shadow overflow-hidden sm:rounded-lg lg:col-span-2">
        <div class="px-4 py-5 sm:p-6">
            <h2 class="text-xl font-medium text-gray-900 mb-4">Latest Notifications</h2>
            <?php if (!empty($notifications)): ?>
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($notifications as $notification): ?>
                        <li class="py-4 px-2 hover:bg-gray-50 flex items-start justify-between">
                            <div>
                                <p class="text-sm <?= $notification['is_read'] ? 'text-gray-500' : 'font-semibold text-gray-900' ?>">
                                    <?= Helpers\esc($notification['message']) ?>
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    Type: <?= Helpers\esc(ucwords(str_replace('_', ' ', $notification['type']))) ?>
                                    <span class="mx-1">•</span>
                                    Sent via: <?= Helpers\esc(str_replace(',', ', ', $notification['delivery_method_sent'])) ?>
                                    <span class="mx-1">•</span>
                                    <?= (new DateTime($notification['created_at']))->format('M j, Y \a\t H:i') ?>
                                </p>
                            </div>
                            <?php if (!$notification['is_read']): ?>
                                <form action="/notifications/mark-read" method="POST" class="ml-4 flex-shrink-0">
                                    <input type="hidden" name="notification_id" value="<?= Helpers\esc($notification['id']) ?>">
                                    <button type="submit" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">Mark as Read</button>
                                </form>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-gray-500">No notifications to display.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h2 class="text-xl font-medium text-gray-900 mb-4">Notification Preferences</h2>
            <p class="text-sm text-gray-500 mb-4">Choose how you wish to receive notifications.</p>

            <form action="/notifications/settings/update" method="POST" class="space-y-4">
                <?php
                $notificationTypes = [
                    'subscription_renewed' => 'Subscription Renewed',
                    'payment_received' => 'Payment Received',
                    'payment_failed' => 'Payment Failed',
                    'saved_card_expired' => 'Saved Card Expiring',
                    'message_from_admin' => 'Message from Admin',
                    'upcoming_class_details' => 'Upcoming Class Details',
                    'subscription_changed' => 'Subscription Changed',
                ];
                $deliveryOptions = [
                    'in_app' => 'In-App',
                    'email' => 'Email'
                ];
                ?>

                <?php foreach ($notificationTypes as $typeKey => $typeName): ?>
                    <fieldset>
                        <legend class="text-base font-medium text-gray-900"><?= Helpers\esc($typeName) ?></legend>
                        <div class="mt-2 space-y-2">
                            <?php foreach ($deliveryOptions as $optionValue => $optionLabel): ?>
                                <div class="relative flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="setting_<?= Helpers\esc($typeKey) ?>_<?= Helpers\esc($optionValue) ?>"
                                               name="settings[<?= Helpers\esc($typeKey) ?>][]"
                                               type="checkbox"
                                               value="<?= Helpers\esc($optionValue) ?>"
                                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                               <?php
                                                    $currentPref = $notificationSettings[$typeKey] ?? 'in_app';
                                                    if (in_array($optionValue, explode(',', $currentPref))) {
                                                        echo 'checked';
                                                    }
                                               ?>>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="setting_<?= Helpers\esc($typeKey) ?>_<?= Helpers\esc($optionValue) ?>" class="font-medium text-gray-700">
                                            <?= Helpers\esc($optionLabel) ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>
                <?php endforeach; ?>

                <div class="pt-5">
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Save Preferences
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php Helpers\end_section(); ?>
