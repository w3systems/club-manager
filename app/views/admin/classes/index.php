<?php
// app/views/admin/classes/index.php
 require_once VIEW_PATH . '/admin/layouts/admin.php';  use App\functions as Helpers; ?>

<?php start_section('content'); ?>

<h1 class="text-2xl font-semibold text-gray-900 mb-6">Class Management</h1>

<?php displayFlashMessages();  displayErrors(); ?>

<?php if (\App\Core\Auth::hasPermission('manage_classes')): ?>
    <div class="mb-8 bg-white shadow overflow-hidden sm:rounded-lg p-6">
        <h2 class="text-xl font-medium text-gray-900 mb-4">Add New Class or Series</h2>
        <form action="/admin/classes/create" method="POST" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Class Name</label>
                    <input type="text" name="name" id="name" required class="form-input" value="<?= old('name') ?>">
                    <?php displayErrors('name'); ?>
                </div>
                <div>
                    <label for="class_type" class="block text-sm font-medium text-gray-700">Class Type</label>
                    <select id="class_type" name="class_type" required class="form-select" onchange="toggleClassTypeFields()">
                        <option value="">Select Type</option>
                        <option value="single" <?= old('class_type') == 'single' ? 'selected' : '' ?>>Single Event</option>
                        <option value="recurring_parent" <?= old('class_type') == 'recurring_parent' ? 'selected' : '' ?>>Recurring Series</option>
                    </select>
                    <?php displayErrors('class_type'); ?>
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="3" class="form-textarea"><?= old('description') ?></textarea>
                <?php displayErrors('description'); ?>
            </div>

            <div id="singleClassFields" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="instance_date_time_single" class="block text-sm font-medium text-gray-700">Date & Time</label>
                    <input type="datetime-local" name="instance_date_time" id="instance_date_time_single" class="form-input" value="<?= old('instance_date_time') ?>">
                    <?php displayErrors('instance_date_time'); ?>
                </div>
                <div>
                    <label for="duration_minutes_single" class="block text-sm font-medium text-gray-700">Duration (minutes)</label>
                    <input type="number" name="duration_minutes" id="duration_minutes_single" min="1" class="form-input" value="<?= old('duration_minutes') ?>">
                    <?php displayErrors('duration_minutes'); ?>
                </div>
            </div>

            <div id="recurringClassFields" class="grid grid-cols-1 md:grid-cols-2 gap-4 hidden">
                <div>
                    <label for="original_start_date" class="block text-sm font-medium text-gray-700">Series Start Date</label>
                    <input type="date" name="original_start_date" id="original_start_date" class="form-input" value="<?= old('original_start_date') ?>">
                    <?php displayErrors('original_start_date'); ?>
                </div>
                <div>
                    <label for="original_end_date" class="block text-sm font-medium text-gray-700">Series End Date (Optional)</label>
                    <input type="date" name="original_end_date" id="original_end_date" class="form-input" value="<?= old('original_end_date') ?>">
                    <?php displayErrors('original_end_date'); ?>
                </div>
                <div>
                    <label for="frequency" class="block text-sm font-medium text-gray-700">Frequency</label>
                    <select id="frequency" name="frequency" class="form-select">
                        <option value="">Select Frequency</option>
                        <option value="daily" <?= old('frequency') == 'daily' ? 'selected' : '' ?>>Daily</option>
                        <option value="weekly" <?= old('frequency') == 'weekly' ? 'selected' : '' ?>>Weekly</option>
                        <option value="fortnightly" <?= old('frequency') == 'fortnightly' ? 'selected' : '' ?>>Fortnightly</option>
                        <option value="4_weekly" <?= old('frequency') == '4_weekly' ? 'selected' : '' ?>>4 Weekly</option>
                        <option value="monthly" <?= old('frequency') == 'monthly' ? 'selected' : '' ?>>Monthly</option>
                    </select>
                    <?php displayErrors('frequency'); ?>
                </div>
                <div>
                    <label for="instance_time_recurring" class="block text-sm font-medium text-gray-700">Time of Day (for instances)</label>
                    <input type="time" name="instance_time_recurring" id="instance_time_recurring" class="form-input" value="<?= old('instance_time_recurring') ?>">
                    <?php displayErrors('instance_time_recurring'); ?>
                </div>
                <div>
                    <label for="duration_minutes_recurring" class="block text-sm font-medium text-gray-700">Duration (minutes)</label>
                    <input type="number" name="duration_minutes_recurring" id="duration_minutes_recurring" min="1" class="form-input" value="<?= old('duration_minutes_recurring') ?>">
                    <?php displayErrors('duration_minutes_recurring'); ?>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="capacity" class="block text-sm font-medium text-gray-700">Capacity (Optional)</label>
                    <input type="number" name="capacity" id="capacity" class="form-input" value="<?= old('capacity') ?>">
                    <p class="mt-1 text-sm text-gray-500">Max number of attendees for each class instance.</p>
                    <?php displayErrors('capacity'); ?>
                </div>
                <div>
                    <label for="auto_book" class="block text-sm font-medium text-gray-700">Auto-Book for Subscriptions</label>
                    <select id="auto_book" name="auto_book" class="form-select">
                        <option value="1" <?= old('auto_book', '1') == '1' ? 'selected' : '' ?>>Yes, auto-book</option>
                        <option value="0" <?= old('auto_book', '1') == '0' ? 'selected' : '' ?>>No, manual booking required</option>
                    </select>
                    <?php displayErrors('auto_book'); ?>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Associated Subscriptions</label>
                <p class="mt-1 text-sm text-gray-500">Select which subscription types grant access to this class/series.</p>
                <div class="mt-2 grid grid-cols-1 md:grid-cols-3 gap-2">
                    <?php foreach (\App\Models\Subscription::getAllSubscriptions() as $sub): ?>
                        <div class="relative flex items-start">
                            <div class="flex items-center h-5">
                                <input id="sub_<?= esc($sub['id']) ?>" name="subscription_ids[]" type="checkbox" value="<?= esc($sub['id']) ?>"
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                       <?= in_array($sub['id'], old('subscription_ids', [])) ? 'checked' : '' ?>>
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="sub_<?= esc($sub['id']) ?>" class="font-medium text-gray-700"><?= esc($sub['name']) ?></label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php displayErrors('subscription_ids'); ?>
            </div>

            <div class="pt-5">
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Add Class
                </button>
            </div>
        </form>
    </div>
<?php endif; ?>

<script>
    function toggleClassTypeFields() {
        const classType = document.getElementById('class_type').value;
        const singleClassFields = document.getElementById('singleClassFields');
        const recurringClassFields = document.getElementById('recurringClassFields');

        // Reset display and required attributes
        singleClassFields.classList.add('hidden');
        recurringClassFields.classList.add('hidden');
        document.getElementById('instance_date_time_single').removeAttribute('required');
        document.getElementById('duration_minutes_single').removeAttribute('required');
        document.getElementById('original_start_date').removeAttribute('required');
        document.getElementById('frequency').removeAttribute('required');
        document.getElementById('instance_time_recurring').removeAttribute('required');
        document.getElementById('duration_minutes_recurring').removeAttribute('required');

        if (classType === 'single') {
            singleClassFields.classList.remove('hidden');
            document.getElementById('instance_date_time_single').setAttribute('required', 'required');
            document.getElementById('duration_minutes_single').setAttribute('required', 'required');
        } else if (classType === 'recurring_parent') {
            recurringClassFields.classList.remove('hidden');
            document.getElementById('original_start_date').setAttribute('required', 'required');
            document.getElementById('frequency').setAttribute('required', 'required');
            document.getElementById('instance_time_recurring').setAttribute('required', 'required');
            document.getElementById('duration_minutes_recurring').setAttribute('required', 'required');
        }
    }

    // Call on load to set initial state based on old input
    document.addEventListener('DOMContentLoaded', toggleClassTypeFields);
</script>

<style>
    .form-input {
        @apply mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm;
    }
    .form-select {
        @apply mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md;
    }
    .form-textarea {
        @apply mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm;
    }
</style>

<div class="mt-8">
    <h2 class="text-xl font-medium text-gray-900 mb-4">Existing Classes & Series</h2>
    <?php if (empty($classes)): ?>
        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4" role="alert">
            <p class="font-bold">No Classes Defined</p>
            <p>Use the form above to add your first class or recurring series.</p>
        </div>
    <?php else: ?>
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="min-w-full overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Name
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Schedule
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Capacity
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Auto-Book
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Subscriptions
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
					<?php if (!empty($classes)): ?>
                        <?php foreach ($classes as $class): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= esc($class['name']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= esc(ucfirst(str_replace('_', ' ', $class['class_type']))) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?php if ($class['class_type'] === 'single' || $class['class_type'] === 'recurring_instance'): ?>
                                        <?= (new DateTime($class['instance_date_time']))->format('M j, Y H:i') ?> (<?= esc($class['duration_minutes']) ?> min)
                                    <?php elseif ($class['class_type'] === 'recurring_parent'): ?>
                                        <?= esc(ucfirst($class['frequency'])) ?> at <?= (new DateTime($class['instance_date_time']))->format('H:i') ?>
                                        <?php if ($class['original_start_date']): ?>
                                            from <?= (new DateTime($class['original_start_date']))->format('M j, Y') ?>
                                        <?php endif; ?>
                                        <?php if ($class['original_end_date']): ?>
                                            to <?= (new DateTime($class['original_end_date']))->format('M j, Y') ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= esc($class['capacity'] ?? 'Unlimited') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= $class['auto_book'] ? 'Yes' : 'No' ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= esc($class['associated_subscriptions'] ?? 'None') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <?php if (\App\Core\Auth::hasPermission('manage_classes')): ?>
                                        <a href="/admin/classes/edit/<?= esc($class['id']) ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                        <button type="button" class="ml-4 text-red-600 hover:text-red-900" onclick="confirmDeleteClass(<?= esc($class['id']) ?>)">Delete</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No classes defined.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

<script>
    function confirmDeleteClass(id) {
        if (confirm('Are you sure you want to delete this class? This will also delete any associated instances or bookings.')) {
            // Implement actual delete form submission or AJAX call
            alert('Delete functionality for class ID: ' + id + ' to be implemented.');
            // Example:
            // const form = document.createElement('form');
            // form.method = 'POST';
            // form.action = '/admin/classes/delete/' + id;
            // document.body.appendChild(form);
            // form.submit();
        }
    }
</script>

<?php end_section(); ?>
