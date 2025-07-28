<?php
// app/views/admin/members/profile.php
 require_once VIEW_PATH . '/admin/layouts/admin.php';  use App\functions as Helpers; ?>

<?php start_section('content'); ?>

<h1 class="text-2xl font-semibold text-gray-900 mb-6">Member Profile: <?= esc($member['first_name'] . ' ' . $member['last_name']) ?></h1>

<?php displayFlashMessages();  displayErrors(); ?>

<div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            Personal Information
            <?php if (\App\Core\Auth::hasPermission('edit_members')): ?>
                <button type="button" id="editMemberBtn" class="ml-4 inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Edit
                </button>
            <?php endif; ?>
        </h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">
            Detailed information about this member.
        </p>
    </div>
    <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
        <form id="memberProfileForm" action="/admin/members/<?= esc($member['id']) ?>/update" method="POST">
            <dl class="sm:divide-y sm:divide-gray-200">
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">First Name</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <input type="text" name="first_name" value="<?= esc($member['first_name']) ?>" required class="form-input" disabled>
                        <?php displayErrors('first_name'); ?>
                    </dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Last Name</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <input type="text" name="last_name" value="<?= esc($member['last_name']) ?>" required class="form-input" disabled>
                        <?php displayErrors('last_name'); ?>
                    </dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Email address</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <input type="email" name="email" value="<?= esc($member['email']) ?>" required class="form-input" disabled>
                        <?php displayErrors('email'); ?>
                    </dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Mobile Number</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <input type="tel" name="mobile" value="<?= esc($member['mobile']) ?>" required class="form-input" disabled>
                        <?php displayErrors('mobile'); ?>
                    </dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Date of Birth</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <input type="date" name="date_of_birth" value="<?= esc($member['date_of_birth']) ?>" required class="form-input" disabled>
                        (<?= calculateAge($member['date_of_birth']) ?> years old)
                        <?php displayErrors('date_of_birth'); ?>
                    </dd>
                </div>

                <div class="px-4 py-5 sm:px-6 bg-gray-50 border-t border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Emergency Contact</h3>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <input type="text" name="emergency_contact_name" value="<?= esc($member['emergency_contact_name']) ?>" required class="form-input" disabled>
                        <?php displayErrors('emergency_contact_name'); ?>
                    </dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Number</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <input type="tel" name="emergency_contact_number" value="<?= esc($member['emergency_contact_number']) ?>" required class="form-input" disabled>
                        <?php displayErrors('emergency_contact_number'); ?>
                    </dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Relationship</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <input type="text" name="emergency_contact_relationship" value="<?= esc($member['emergency_contact_relationship']) ?>" required class="form-input" disabled>
                        <?php displayErrors('emergency_contact_relationship'); ?>
                    </dd>
                </div>

                <?php if (calculateAge($member['date_of_birth']) < 18): ?>
                    <div class="px-4 py-5 sm:px-6 bg-gray-50 border-t border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Parent/Guardian Details</h3>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Parent/Guardian Email</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <input type="email" name="parent_guardian_email" value="<?= esc($member['parent_guardian_email']) ?>" class="form-input" disabled>
                            <?php displayErrors('parent_guardian_email'); ?>
                        </dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Parent/Guardian Mobile</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <input type="tel" name="parent_guardian_mobile" value="<?= esc($member['parent_guardian_mobile']) ?>" class="form-input" disabled>
                            <?php displayErrors('parent_guardian_mobile'); ?>
                        </dd>
                    </div>
                <?php endif; ?>

                <div class="px-4 py-5 sm:px-6 bg-gray-50 border-t border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Consents</h3>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Photography Consent</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <div class="flex items-center">
                            <input id="consent_photography" name="consent_photography" type="checkbox" value="1"
                                   class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                   <?= $member['consent_photography'] ? 'checked' : '' ?> disabled>
                            <label for="consent_photography" class="ml-2 block text-sm text-gray-900">
                                I consent to photography/videography.
                            </label>
                        </div>
                        <?php displayErrors('consent_photography'); ?>
                    </dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">First Aid Consent</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <div class="flex items-center">
                            <input id="consent_first_aid" name="consent_first_aid" type="checkbox" value="1"
                                   class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                   <?= $member['consent_first_aid'] ? 'checked' : '' ?> disabled>
                            <label for="consent_first_aid" class="ml-2 block text-sm text-gray-900">
                                I consent to first aid administration.
                            </label>
                        </div>
                        <?php displayErrors('consent_first_aid'); ?>
                    </dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Terms & Conditions</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <div class="flex items-center">
                            <input id="terms_conditions_acceptance" name="terms_conditions_acceptance" type="checkbox" value="1"
                                   class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                   <?= $member['terms_conditions_acceptance'] ? 'checked' : '' ?> disabled>
                            <label for="terms_conditions_acceptance" class="ml-2 block text-sm text-gray-900">
                                I accept the Terms & Conditions.
                            </label>
                        </div>
                        <?php displayErrors('terms_conditions_acceptance'); ?>
                    </dd>
                </div>
            </dl>
            <div class="px-4 py-3 bg-gray-50 text-right sm:px-6 hidden" id="saveChangesBtnContainer">
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('memberProfileForm');
        const inputs = form.querySelectorAll('.form-input, input[type="checkbox"]');
        const editBtn = document.getElementById('editMemberBtn');
        const saveChangesBtnContainer = document.getElementById('saveChangesBtnContainer');

        function toggleFormEdit(enable) {
            inputs.forEach(input => {
                input.disabled = !enable;
            });
            if (enable) {
                saveChangesBtnContainer.classList.remove('hidden');
                editBtn.classList.add('hidden');
            } else {
                saveChangesBtnContainer.classList.add('hidden');
                editBtn.classList.remove('hidden');
            }
        }

        // Initialize form as disabled
        toggleFormEdit(false);

        // Enable form on Edit button click
        editBtn.addEventListener('click', () => {
            toggleFormEdit(true);
        });

        // Add a class for tailwind styling of form inputs
        inputs.forEach(input => {
            if (input.type !== 'checkbox') {
                input.classList.add('block', 'w-full', 'border-gray-300', 'rounded-md', 'shadow-sm', 'focus:ring-indigo-500', 'focus:border-indigo-500', 'sm:text-sm');
            }
        });
    });
</script>

<div class="mb-8">
    <h2 class="text-xl font-medium text-gray-900 mb-4">Member Subscriptions</h2>
    <?php if (\App\Core\Auth::hasPermission('manage_subscriptions')): ?>
        <div class="bg-white shadow overflow-hidden sm:rounded-lg p-6 mb-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Add New Subscription for Member</h3>
            <form action="/admin/members/<?= esc($member['id']) ?>/subscription/add" method="POST" class="space-y-4">
                <div>
                    <label for="subscription_id" class="block text-sm font-medium text-gray-700">Subscription Type</label>
                    <select id="subscription_id" name="subscription_id" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">Select a Subscription</option>
                        <?php foreach ($availableSubscriptions as $sub): ?>
                            <option value="<?= esc($sub['id']) ?>">
                                <?= esc($sub['name']) ?> (&pound;<?= esc(number_format($sub['price'], 2)) ?> / <?= esc($sub['term_unit'] ?? 'one-time') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php displayErrors('subscription_id'); ?>
                </div>
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="<?= date('Y-m-d') ?>" required
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <?php displayErrors('start_date'); ?>
                </div>
                <div>
                    <label for="override_fee" class="block text-sm font-medium text-gray-700">Override Fee (Optional)</label>
                    <input type="number" step="0.01" name="override_fee" id="override_fee"
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           placeholder="e.g., 50.00">
                    <p class="mt-1 text-sm text-gray-500">If set, this amount will be recorded as a manual payment for the subscription's initial cost, overriding the default price.</p>
                    <?php displayErrors('override_fee'); ?>
                </div>
                <div>
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Add Subscription
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="min-w-full overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Subscription Name
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Type
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Start Date
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            End Date
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
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
                                    <?= esc($sub['subscription_name']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= esc(ucfirst(str_replace('_', ' ', $sub['type']))) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= (new DateTime($sub['start_date']))->format('M j, Y') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php if ($sub['end_date']): ?>
                                        <?= (new DateTime($sub['end_date']))->format('M j, Y') ?>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        <?php
                                            switch ($sub['status']) {
                                                case 'active': echo 'bg-green-100 text-green-800'; break;
                                                case 'suspended': echo 'bg-yellow-100 text-yellow-800'; break;
                                                case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                                case 'ended': echo 'bg-gray-100 text-gray-800'; break;
                                                case 'trial': echo 'bg-blue-100 text-blue-800'; break;
                                                default: echo 'bg-gray-100 text-gray-800'; break;
                                            }
                                        ?>">
                                        <?= esc(ucfirst($sub['status'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <?php if (\App\Core\Auth::hasPermission('manage_subscriptions')): ?>
                                        <button type="button" onclick="openSubscriptionActionsModal(<?= esc($sub['id']) ?>, '<?= esc($sub['subscription_name']) ?>', '<?= esc($sub['status']) ?>')"
                                                class="text-indigo-600 hover:text-indigo-900">Actions</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No subscriptions for this member.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="subscriptionActionsModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
  <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
      <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
        <div class="sm:flex sm:items-start">
          <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
              Subscription Actions for <span id="modalSubscriptionName"></span>
            </h3>
            <div class="mt-2">
              <p class="text-sm text-gray-500">
                Choose an action to perform on this subscription.
              </p>
              <form id="subscriptionActionForm" action="/admin/members/<?= esc($member['id']) ?>/subscription/update" method="POST" class="mt-4 space-y-4">
                <input type="hidden" name="member_subscription_id" id="modalMemberSubscriptionId">
                <div>
                    <label for="action_type" class="block text-sm font-medium text-gray-700">Action</label>
                    <select id="action_type" name="action" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">Select Action</option>
                        <option value="suspend">Suspend</option>
                        <option value="activate">Activate</option>
                        <option value="cancel">Cancel</option>
                        <option value="delete">Delete (Permanent)</option>
                        <option value="upgrade">Upgrade</option>
                        <option value="downgrade">Downgrade</option>
                    </select>
                </div>
                <div id="newSubscriptionSelect" class="hidden">
                    <label for="new_subscription_id" class="block text-sm font-medium text-gray-700">New Subscription Type</label>
                    <select id="new_subscription_id" name="new_subscription_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">Select New Subscription</option>
                        <?php foreach ($availableSubscriptions as $sub): ?>
                            <option value="<?= esc($sub['id']) ?>">
                                <?= esc($sub['name']) ?> (&pound;<?= esc(number_format($sub['price'], 2)) ?> / <?= esc($sub['term_unit'] ?? 'one-time') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="effective_date" class="block text-sm font-medium text-gray-700">Effective Date</label>
                    <input type="date" name="effective_date" id="effective_date" value="<?= date('Y-m-d') ?>" required
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
        <button type="button" id="confirmActionButton" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
          Confirm
        </button>
        <button type="button" id="cancelModalButton" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
          Cancel
        </button>
      </div>
    </div>
  </div>
</div>

<script>
    const modal = document.getElementById('subscriptionActionsModal');
    const modalSubscriptionName = document.getElementById('modalSubscriptionName');
    const modalMemberSubscriptionId = document.getElementById('modalMemberSubscriptionId');
    const actionTypeSelect = document.getElementById('action_type');
    const newSubscriptionSelectDiv = document.getElementById('newSubscriptionSelect');
    const confirmActionButton = document.getElementById('confirmActionButton');
    const cancelModalButton = document.getElementById('cancelModalButton');
    const subscriptionActionForm = document.getElementById('subscriptionActionForm');

    function openSubscriptionActionsModal(memberSubscriptionId, subscriptionName, currentStatus) {
        modalSubscriptionName.textContent = subscriptionName;
        modalMemberSubscriptionId.value = memberSubscriptionId;
        actionTypeSelect.value = ''; // Reset action
        newSubscriptionSelectDiv.classList.add('hidden'); // Hide new sub select by default
        modal.classList.remove('hidden');

        // Dynamically enable/disable options based on current status
        Array.from(actionTypeSelect.options).forEach(option => {
            option.disabled = false; // Enable all first
            if (currentStatus === 'active') {
                if (option.value === 'activate') option.disabled = true;
            } else if (currentStatus === 'suspended') {
                if (option.value === 'suspend') option.disabled = true;
            } else if (currentStatus === 'cancelled' || currentStatus === 'ended') {
                if (option.value === 'suspend' || option.value === 'cancel' || option.value === 'activate') option.disabled = true;
            }
        });
    }

    function closeSubscriptionActionsModal() {
        modal.classList.add('hidden');
    }

    actionTypeSelect.addEventListener('change', (event) => {
        if (event.target.value === 'upgrade' || event.target.value === 'downgrade') {
            newSubscriptionSelectDiv.classList.remove('hidden');
            document.getElementById('new_subscription_id').setAttribute('required', 'required');
        } else {
            newSubscriptionSelectDiv.classList.add('hidden');
            document.getElementById('new_subscription_id').removeAttribute('required');
        }
    });

    confirmActionButton.addEventListener('click', () => {
        // Simple client-side validation before submitting the form
        if (actionTypeSelect.value === '') {
            alert('Please select an action.');
            return;
        }
        if ((actionTypeSelect.value === 'upgrade' || actionTypeSelect.value === 'downgrade') && document.getElementById('new_subscription_id').value === '') {
            alert('Please select a new subscription type.');
            return;
        }
        if (actionTypeSelect.value === 'delete' && !confirm('Are you absolutely sure you want to PERMANENTLY DELETE this subscription record? This action cannot be undone.')) {
            return;
        }

        subscriptionActionForm.submit();
    });

    cancelModalButton.addEventListener('click', closeSubscriptionActionsModal);

    // Close modal if escape key is pressed
    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeSubscriptionActionsModal();
        }
    });
</script>

<style>
    /* Add this to your main CSS or style block for the modal overlay effect */
    .form-input {
        @apply block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm;
    }
</style>


<div class="mb-8">
    <h2 class="text-xl font-medium text-gray-900 mb-4">Collected Fees</h2>
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="min-w-full overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Amount
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Description
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Method
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($payments)): ?>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= (new DateTime($payment['payment_date']))->format('M j, Y H:i') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    &pound;<?= esc(number_format($payment['amount'], 2)) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?= esc($payment['description']) ?>
                                    <?php if ($payment['member_subscription_id']): ?>
                                        <span class="text-xs text-gray-400 block"> (Subscription ID: <?= esc($payment['member_subscription_id']) ?>)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        <?php
                                            switch ($payment['status']) {
                                                case 'succeeded': echo 'bg-green-100 text-green-800'; break;
                                                case 'failed': echo 'bg-red-100 text-red-800'; break;
                                                case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                                case 'refunded': echo 'bg-gray-100 text-gray-800'; break;
                                                default: echo 'bg-gray-100 text-gray-800'; break;
                                            }
                                        ?>">
                                        <?= esc(ucfirst($payment['status'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= esc(ucfirst(str_replace('_', ' ', $payment['payment_gateway']))) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No payment records for this member.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mb-8">
    <h2 class="text-xl font-medium text-gray-900 mb-4">Classes Booked</h2>
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="min-w-full overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Class Name
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date & Time
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Booking Date
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Type
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($classesBooked)): ?>
                        <?php foreach ($classesBooked as $booking): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= esc($booking['class_name']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= (new DateTime($booking['instance_date_time']))->format('M j, Y H:i') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= (new DateTime($booking['booking_date']))->format('M j, Y H:i') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        <?php
                                            switch ($booking['status']) {
                                                case 'booked': echo 'bg-green-100 text-green-800'; break;
                                                case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                                case 'attended': echo 'bg-blue-100 text-blue-800'; break;
                                                case 'no_show': echo 'bg-yellow-100 text-yellow-800'; break;
                                                default: echo 'bg-gray-100 text-gray-800'; break;
                                            }
                                        ?>">
                                        <?= esc(ucfirst($booking['status'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php if ($booking['is_free_trial']): ?>
                                        Free Trial
                                    <?php elseif ($booking['is_auto_booked']): ?>
                                        Auto
                                    <?php else: ?>
                                        Manual
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No classes booked for this member.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mb-8">
    <h2 class="text-xl font-medium text-gray-900 mb-4">Messages with Member</h2>
    <?php if (\App\Core\Auth::hasPermission('view_member_messages')): ?>
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="p-6">
                <div class="max-h-96 overflow-y-auto space-y-4 mb-4 border-b pb-4">
                    <?php if (!empty($messages)): ?>
                        <?php foreach ($messages as $message): ?>
                            <div class="p-3 rounded-lg <?= $message['type'] === 'admin_to_member' ? 'bg-indigo-50 text-right' : 'bg-gray-50 text-left' ?>">
                                <p class="text-xs text-gray-600 mb-1">
                                    <?php if ($message['type'] === 'admin_to_member'): ?>
                                        You:
                                    <?php else: ?>
                                        <?= esc($member['first_name']) ?>:
                                    <?php endif; ?>
                                    <span class="float-right text-gray-400">
                                        <?= (new DateTime($message['created_at']))->format('M j, Y H:i') ?>
                                    </span>
                                </p>
                                <p class="text-sm text-gray-800"><?= esc($message['content']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-500 text-center">No messages with this member yet.</p>
                    <?php endif; ?>
                </div>

                <?php if (\App\Core\Auth::hasPermission('send_member_messages')): ?>
                    <form action="/admin/messages" method="POST" class="space-y-4">
                        <input type="hidden" name="member_id" value="<?= esc($member['id']) ?>">
                        <div>
                            <label for="message_content" class="sr-only">Reply to Member</label>
                            <textarea id="message_content" name="message_content" rows="4" required
                                      class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md p-3"
                                      placeholder="Reply to <?= esc($member['first_name']) ?>..."></textarea>
                            <?php displayErrors('message_content'); ?>
                        </div>
                        <div>
                            <button type="submit"
                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Send Reply
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <p class="text-gray-500 text-center">You do not have permission to send messages.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>


<?php end_section(); ?>
