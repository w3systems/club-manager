<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="<?= $this->url('admin/classes') ?>" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left text-xl"></i></a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manage Sessions: <?= htmlspecialchars($class->name) ?></h1>
            <p class="text-gray-600">Delete individual upcoming sessions for holidays or one-off cancellations.</p>
        </div>
    </div>

    <?php $this->component('alerts'); ?>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Session Date & Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bookings</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($instances)): ?>
                        <tr><td colspan="3" class="px-6 py-8 text-center text-gray-500">No upcoming sessions found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($instances as $instance): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= $instance->instance_date_time->format('l, F jS, Y \a\t g:i A') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    0 / <?= htmlspecialchars($class->capacity ?? '∞') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <form action="<?= $this->url('admin/classes/' . $class->id . '/instances/' . $instance->id . '/delete') ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this session?');" class="inline">
                                        <?= $this->csrf() ?>
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Delete Session"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>