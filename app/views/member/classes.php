<?php
// app/views/member/classes.php
 require_once VIEW_PATH . '/member/layouts/member.php';  use App\Helpers\functions as Helpers; ?>

<?php Helpers\start_section('content'); ?>

<h1 class="text-2xl font-semibold text-gray-900 mb-6">Your Classes</h1>

<?php Helpers\displayFlashMessages(); ?>

<?php if (empty($classes)): ?>
    <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4" role="alert">
        <p class="font-bold">No Classes Booked</p>
        <p>You are not currently booked into any classes. Check your subscriptions or browse available classes.</p>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        <?php foreach ($classes as $class): ?>
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900"><?= Helpers\esc($class['class_name']) ?></h3>
                    <p class="mt-2 text-sm text-gray-500"><?= Helpers\esc($class['description']) ?></p>
                    <p class="mt-3 text-sm font-medium text-gray-700">
                        Date: <?= (new DateTime($class['instance_date_time']))->format('D, M jS, Y \a\t H:i') ?>
                    </p>
                    <?php if ($class['duration_minutes']): ?>
                        <p class="mt-1 text-sm text-gray-700">Duration: <?= Helpers\esc($class['duration_minutes']) ?> minutes</p>
                    <?php endif; ?>
                    <p class="mt-1 text-sm text-gray-500">
                        Status:
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                            <?php
                                switch ($class['status']) {
                                    case 'booked': echo 'bg-green-100 text-green-800'; break;
                                    case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                    case 'attended': echo 'bg-blue-100 text-blue-800'; break;
                                    case 'no_show': echo 'bg-yellow-100 text-yellow-800'; break;
                                    default: echo 'bg-gray-100 text-gray-800'; break;
                                }
                            ?>">
                            <?= Helpers\esc(ucfirst($class['status'])) ?>
                        </span>
                    </p>
                    <?php if ($class['is_free_trial']): ?>
                        <p class="mt-1 text-xs text-indigo-600 font-semibold">Free Trial Class</p>
                    <?php endif; ?>
                    <?php if ($class['is_auto_booked']): ?>
                        <p class="mt-1 text-xs text-blue-600 font-semibold">Auto-booked by subscription</p>
                    <?php endif; ?>
                </div>
                <div class="bg-gray-50 px-4 py-4 sm:px-6 text-right">
                    <?php if ($class['status'] === 'booked' && (new DateTime($class['instance_date_time'])) > (new DateTime('+1 day'))): ?>
                        <button class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Cancel Booking
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="mt-8">
    <h2 class="text-xl font-medium text-gray-900 mb-4">Explore More Classes</h2>
    <div class="bg-white shadow overflow-hidden sm:rounded-lg p-6">
        <p class="text-gray-500">Check out all available classes that your current subscriptions provide access to, or browse public classes.</p>
        <div class="mt-4">
            <a href="/classes/calendar" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">View Class Calendar &rarr;</a>
        </div>
    </div>
</div>

<?php Helpers\end_section(); ?>
