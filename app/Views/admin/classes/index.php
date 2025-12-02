<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Classes</h1>
            <p class="text-gray-600">Manage all classes, sessions, and events.</p>
        </div>
        <a href="<?= $this->url('admin/classes/create') ?>" class="btn btn-primary"><i class="fas fa-plus mr-2"></i>Add Class</a>
    </div>

    <?php $this->component('alerts'); ?>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Schedule</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($classes)): ?>
                        <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">No classes found.</td></tr>
                    <?php else: ?>
                        <?php $daysOfWeek = [1 => 'Mondays', 2 => 'Tuesdays', 3 => 'Wednesdays', 4 => 'Thursdays', 5 => 'Fridays', 6 => 'Saturdays', 7 => 'Sundays']; ?>
                        <?php foreach ($classes as $class): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4"><div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($class->name) ?></div></td>
                                <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars(ucfirst(str_replace('_parent', '', $class->class_type))) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?php if($class->class_type === 'recurring_parent'): ?>
                                        <?= htmlspecialchars($daysOfWeek[$class->day_of_week] ?? ucfirst($class->frequency)) . ' at ' . date('g:ia', strtotime($class->start_time)) ?>
                                    <?php else: ?>
                                        <?= date('D, M j, Y @ g:ia', strtotime($class->original_start_date->format('Y-m-d') . ' ' . $class->start_time)) ?>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-3">
                                        <?php if($class->class_type === 'recurring_parent'): ?>
                                            <a href="<?= $this->url('admin/classes/' . $class->id . '/instances') ?>" class="btn btn-secondary btn-sm" title="Manage Sessions">Manage Sessions</a>
                                        <?php endif; ?>
                                        <a href="<?= $this->url('admin/classes/' . $class->id . '/edit') ?>" class="text-indigo-600 hover:text-indigo-900" title="Edit Class"><i class="fas fa-edit"></i></a>
                                        <form action="<?= $this->url('admin/classes/' . $class->id . '/delete') ?>" method="POST" onsubmit="return confirm('Are you sure?');" class="inline">
                                            <?= $this->csrf() ?>
                                            <button type="submit" class="text-red-600 hover:text-red-900" title="Delete Class"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>