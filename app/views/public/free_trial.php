<?php
// app/views/public/free_trial.php
require_once VIEW_PATH . '/member/layouts/member.php';
use App\Helpers\functions as Helpers;
?>

<?php Helpers\start_section('content'); ?>

<h1 class="text-2xl font-semibold text-gray-900 mb-6">Book a Free Trial Class</h1>

<?php Helpers\displayFlashMessages(); ?>
<?php Helpers\displayErrors(); ?>

<?php if (empty($classes)): ?>
    <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4" role="alert">
        <p class="font-bold">No Free Trial Classes Available</p>
        <p>There are no free trial classes available at this time, or you may not be eligible.</p>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($classes as $class): ?>
            <div class="bg-white overflow-hidden shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900"><?= Helpers\esc($class['name']) ?></h3>
                <p class="mt-2 text-sm text-gray-500">
                    <?= Helpers\esc($class['description']) ?>
                </p>
                <p class="mt-3 text-sm font-medium text-gray-700">
                    Date: <?= (new DateTime($class['instance_date_time']))->format('D, M jS, Y H:i') ?>
                </p>
                <?php if ($class['capacity'] !== null): ?>
                    <p class="mt-1 text-sm text-gray-700">
                        Capacity: <?= Helpers\esc($class['capacity'] - \App\Models\ClassBooking::countBookingsForClass($class['id'])) ?> / <?= Helpers\esc($class['capacity']) ?> spots left
                    </p>
                <?php endif; ?>
                <?php if ($class['class_type'] === 'recurring_parent'): ?>
                    <p class="mt-1 text-sm text-gray-500">
                        (This is a recurring series, you will book a single instance)
                    </p>
                <?php endif; ?>

                <form action="/register" method="GET" class="mt-4">
                    <input type="hidden" name="free_trial_class_id" value="<?= Helpers\esc($class['id']) ?>">
                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Register & Book Free Trial
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php Helpers\end_section(); ?>
