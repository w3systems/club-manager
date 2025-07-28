<?php
// app/views/admin/subscriptions/index.php
 require_once VIEW_PATH . '/admin/layouts/admin.php';  use App\Helpers\functions as Helpers; ?>

<?php start_section('content'); ?>

<h1 class="text-2xl font-semibold text-gray-900 mb-6">Subscription Types</h1>

<?php displayFlashMessages();  displayErrors(); ?>

<?php if (\App\Core\Auth::hasPermission('manage_subscriptions')): ?>
    <div class="mb-8 bg-white shadow overflow-hidden sm:rounded-lg p-6">
        <h2 class="text-xl font-medium text-gray-900 mb-4">Add New Subscription Type</h2>
        <form action="/admin/subscriptions/create" method="POST" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" id="name" required class="form-input" value="<?= old('name') ?>">
                    <?php displayErrors('name'); ?>
                </div>
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                    <select id="type" name="type" required class="form-select" onchange="toggleSubscriptionTypeFields()">
                        <option value="">Select Type</option>
                        <option value="recurring" <?= old('type') == 'recurring' ? 'selected' : '' ?>>Recurring</option>
                        <option value="fixed_length" <?= old('type') == 'fixed_length' ? 'selected' : '' ?>>Fixed Length</option>
                        <option value="session_based" <?= old('type') == 'session_based' ? 'selected' : '' ?>>Session Based</option>
                    </select>
                    <?php displayErrors('type'); ?>
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="3" class="form-textarea"><?= old('description') ?></textarea>
                <?php displayErrors('description'); ?>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700">Price (&pound;)</label>
                    <input type="number" step="0.01" name="price" id="price" required class="form-input" value="<?= old('price') ?>">
                    <?php displayErrors('price'); ?>
                </div>
                <div id="termLengthField">
                    <label for="term_length" class="block text-sm font-medium text-gray-700">Term Length / Sessions</label>
                    <input type="number" name="term_length" id="term_length" class="form-input" value="<?= old('term_length') ?>">
                    <?php displayErrors('term_length'); ?>
                </div>
                <div id="termUnitField">
                    <label for="term_unit" class="block text-sm font-medium text-gray-700">Term Unit / Session Unit</label>
                    <select id="term_unit" name="term_unit" class="form-select">
                        <option value="">Select Unit</option>
                        <option value="day" <?= old('term_unit') == 'day' ? 'selected' : '' ?>>Day(s)</option>
                        <option value="week" <?= old('term_unit') == 'week' ? 'selected' : '' ?>>Week(s)</option>
                        <option value="month" <?= old('term_unit') == 'month' ? 'selected' : '' ?>>Month(s)</option>
                        <option value="year" <?= old('term_unit') == 'year' ? 'selected' : '' ?>>Year(s)</option>
                        <option value="session" <?= old('term_unit') == 'session' ? 'selected' : '' ?>>Session(s)</option>
                    </select>
                    <?php displayErrors('term_unit'); ?>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="recurringSpecificFields">
                <div>
                    <label for="fixed_start_day" class="block text-sm font-medium text-gray-700">Fixed Start Day of Month (for recurring)</label>
                    <input type="number" name="fixed_start_day" id="fixed_start_day" min="1" max="31" class="form-input" value="<?= old('fixed_start_day') ?>">
                    <p class="mt-1 text-sm text-gray-500">e.g., 1 for 1st of month. Leave empty for join date.</p>
                    <?php displayErrors('fixed_start_day'); ?>
                </div>
                <div>
                    <label for="prorata_price" class="block text-sm font-medium text-gray-700">Pro-rata Price (First Month)</label>
                    <input type="number" step="0.01" name="prorata_price" id="prorata_price" class="form-input" value="<?= old('prorata_price') ?>">
                    <?php displayErrors('prorata_price'); ?>
                </div>
                <div>
                    <label for="admin_fee" class="block text-sm font-medium text-gray-700">Admin Fee (First Month)</label>
                    <input type="number" step="0.01" name="admin_fee" id="admin_fee" class="form-input" value="<?= old('admin_fee', '0.00') ?>">
                    <?php displayErrors('admin_fee'); ?>
                </div>
                <div class="col-span-2">
                    <label for="stripe_price_id" class="block text-sm font-medium text-gray-700">Stripe Price ID (for recurring subscriptions)</label>
                    <input type="text" name="stripe_price_id" id="stripe_price_id" class="form-input" value="<?= old('stripe_price_id') ?>">
                    <p class="mt-1 text-sm text-gray-500">Required for recurring subscriptions if processed via Stripe.</p>
                    <?php displayErrors('stripe_price_id'); ?>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="capacity" class="block text-sm font-medium text-gray-700">Capacity (Optional)</label>
                    <input type="number" name="capacity" id="capacity" class="form-input" value="<?= old('capacity') ?>">
                    <p class="mt-1 text-sm text-gray-500">Max number of active members for this subscription type.</p>
                    <?php displayErrors('capacity'); ?>
                </div>
                <div>
                    <label for="min_age" class="block text-sm font-medium text-gray-700">Min Age (Optional)</label>
                    <input type="number" name="min_age" id="min_age" class="form-input" value="<?= old('min_age') ?>">
                    <?php displayErrors('min_age'); ?>
                </div>
                <div>
                    <label for="max_age" class="block text-sm font-medium text-gray-700">Max Age (Optional)</label>
                    <input type="number" name="max_age" id="max_age" class="form-input" value="<?= old('max_age') ?>">
                    <?php displayErrors('max_age'); ?>
                </div>
                <div>
                    <label for="next_subscription_id" class="block text-sm font-medium text-gray-700">Next Subscription (for age-based upgrade)</label>
                    <select id="next_subscription_id" name="next_subscription_id" class="form-select">
                        <option value="">None</option>
                        <?php foreach ($subscriptions as $subOption): // Assuming $subscriptions contains all subs for this dropdown ?>
                            <option value="<?= esc($subOption['id']) ?>" <?= old('next_subscription_id') == $subOption['id'] ? 'selected' : '' ?>>
                                <?= esc($subOption['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php displayErrors('next_subscription_id'); ?>
                </div>
            </div>

            <div class="space-y-4">
                <div class="relative flex items-start">
                    <div class="flex items-center h-5">
                        <input id="free_trial_enabled" name="free_trial_enabled" type="checkbox" value="1"
                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                               <?= old('free_trial_enabled') == '1' ? 'checked' : '' ?>>
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="free_trial_enabled" class="font-medium text-gray-700">Enable Free Trial</label>
                        <p class="text-gray-500">Allow members to have a free trial period for this subscription.</p>
                    </div>
                </div>
                <div class="relative flex items-start">
                    <div class="flex items-center h-5">
                        <input id="charge_on_start_date" name="charge_on_start_date" type="checkbox" value="1"
                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                               <?= old('charge_on_start_date') == '1' ? 'checked' : '' ?>>
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="charge_on_start_date" class="font-medium text-gray-700">Charge on Subscription Start Date</label>
                        <p class="text-gray-500">If checked, payment is due on the actual subscription start date (e.g., for fixed day of month plans). If unchecked, charged immediately on signup.</p>
                    </div>
                </div>
            </div>

            <div class="pt-5">
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Add Subscription Type
                </button>
            </div>
        </form>
    </div>
<?php endif; ?>

<script>
    function toggleSubscriptionTypeFields() {
        const type = document.getElementById('type').value;
        const termLengthField = document.getElementById('termLengthField');
        const termUnitField = document.getElementById('termUnitField');
        const fixedStartDayField = document.getElementById('fixed_start_day');
        const prorataPriceField = document.getElementById('prorata_price');
        const adminFeeField = document.getElementById('admin_fee');
        const stripePriceIdField = document.getElementById('stripe_price_id');

        // Reset display
        termLengthField.style.display = 'block';
        termUnitField.style.display = 'block';
        document.getElementById('fixed_start_day').required = false;
        document.getElementById('prorata_price').required = false;
        document.getElementById('stripe_price_id').required = false;

        switch (type) {
            case 'recurring':
                fixedStartDayField.closest('div').style.display = 'block';
                prorataPriceField.closest('div').style.display = 'block';
                adminFeeField.closest('div').style.display = 'block';
                stripePriceIdField.closest('div').style.display = 'block';
                document.getElementById('term_unit').value = 'month'; // Default for recurring
                document.getElementById('term_unit').disabled = true; // Lock term unit
                document.getElementById('term_length').value = 1; // Default to 1 unit
                document.getElementById('term_length').disabled = true; // Lock term length
                document.getElementById('stripe_price_id').required = true;
                break;
            case 'fixed_length':
                fixedStartDayField.closest('div').style.display = 'none';
                prorataPriceField.closest('div').style.display = 'none';
                adminFeeField.closest('div').style.display = 'none';
                stripePriceIdField.closest('div').style.display = 'none';
                document.getElementById('term_unit').disabled = false;
                document.getElementById('term_length').disabled = false;
                break;
            case 'session_based':
                fixedStartDayField.closest('div').style.display = 'none';
                prorataPriceField.closest('div').style.display = 'none';
                adminFeeField.closest('div').style.display = 'none';
                stripePriceIdField.closest('div').style.display = 'none';
                document.getElementById('term_unit').value = 'session';
                document.getElementById('term_unit').disabled = true; // Lock term unit
                document.getElementById('term_length').disabled = false;
                document.getElementById('term_length').placeholder = 'Number of sessions';
                break;
            default:
                fixedStartDayField.closest('div').style.display = 'none';
                prorataPriceField.closest('div').style.display = 'none';
                adminFeeField.closest('div').style.display = 'none';
                stripePriceIdField.closest('div').style.display = 'none';
                document.getElementById('term_unit').disabled = false;
                document.getElementById('term_length').disabled = false;
                document.getElementById('term_length').placeholder = '';
                document.getElementById('term_unit').value = '';
                break;
        }
    }

    // Call on load to set initial state based on old input
    document.addEventListener('DOMContentLoaded', toggleSubscriptionTypeFields);
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
    <h2 class="text-xl font-medium text-gray-900 mb-4">Existing Subscription Types</h2>
    <?php if (empty($subscriptions)): ?>
        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4" role="alert">
            <p class="font-bold">No Subscription Types Defined</p>
            <p>Use the form above to add your first subscription type.</p>
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
                                Price
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Term
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Capacity
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Trial
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
					<?php if (!empty($subscriptions)): ?>
                        <?php foreach ($subscriptions as $sub): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= esc($sub['name']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= esc(ucfirst(str_replace('_', ' ', $sub['type']))) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    &pound;<?= esc(number_format($sub['price'], 2)) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php if ($sub['term_length'] && $sub['term_unit']): ?>
                                        <?= esc($sub['term_length']) ?> <?= esc($sub['term_unit'] . ($sub['term_length'] > 1 ? 's' : '')) ?>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= esc($sub['capacity'] ?? 'Unlimited') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= $sub['free_trial_enabled'] ? 'Yes' : 'No' ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <?php if (\App\Core\Auth::hasPermission('manage_subscriptions')): ?>
                                        <a href="/admin/subscriptions/edit/<?= esc($sub['id']) ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                        <button type="button" class="ml-4 text-red-600 hover:text-red-900" onclick="confirmDeleteSubscription(<?= esc($sub['id']) ?>)">Delete</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No subscription types defined.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

<script>
    function confirmDeleteSubscription(id) {
        if (confirm('Are you sure you want to delete this subscription type? This will affect existing member subscriptions and related data.')) {
            // Implement actual delete form submission or AJAX call
            // For now, a placeholder to show where the action would go
            alert('Delete functionality to be implemented for ID: ' + id);
            // Example: Create a hidden form and submit
            // const form = document.createElement('form');
            // form.method = 'POST';
            // form.action = '/admin/subscriptions/delete/' + id;
            // document.body.appendChild(form);
            // form.submit();
        }
    }
</script>

<?php end_section(); ?>
