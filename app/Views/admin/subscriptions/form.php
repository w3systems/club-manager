<?php
$isEdit = isset($subscription) && !empty($subscription->id);
$formAction = $isEdit ? $this->url('admin/subscriptions/' . $subscription->id) : $this->url('admin/subscriptions');
$pageTitle = $isEdit ? 'Edit Subscription Plan' : 'Add New Subscription Plan';
$pageDescription = $isEdit ? 'Update the details for the ' . htmlspecialchars($subscription->name ?? '') . ' plan.' : 'Create a new plan for members to subscribe to.';
$submitText = $isEdit ? 'Update Plan' : 'Create Plan';

// FIX: Determine which classes should be checked. Prioritize old input, then fall back to saved data.
$checkedClasses = $this->old('classes', $selectedClasses ?? []);

// NEW: Helper function to determine if a class is compatible with the current subscription's schedule.
function is_class_compatible($class, $subscriptionData) {
    $lock = $class->getScheduleLockType();
    if ($lock === null) {
        return true; // Class is not locked to any schedule yet.
    }

    // A class is compatible if its locked schedule matches the subscription's schedule properties.
    return $lock['type'] === $subscriptionData->type &&
           $lock['term_length'] == $subscriptionData->term_length &&
           $lock['term_unit'] === $subscriptionData->term_unit &&
           $lock['fixed_start_day'] == $subscriptionData->fixed_start_day;
}

// Get the current subscription's schedule "signature" for comparison, prioritizing old input.
$currentSubscriptionData = (object)[
    'type' => $this->old('type', $subscription->type ?? 'recurring'),
    'term_length' => $this->old('term_length', $subscription->term_length),
    'term_unit' => $this->old('term_unit', $subscription->term_unit),
    'fixed_start_day' => $this->old('fixed_start_day', $subscription->fixed_start_day)
];
?>

<div class="space-y-6" x-data="{ type: '<?= htmlspecialchars($this->old('type', $subscription->type ?? 'recurring')) ?>', prorata: <?= $this->old('prorata_enabled', $subscription->prorata_enabled ?? false) ? 'true' : 'false' ?> }">
    <div class="flex items-center gap-4">
        <a href="<?= $this->url('admin/subscriptions') ?>" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left text-xl"></i></a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= $pageTitle ?></h1>
            <p class="text-gray-600"><?= $pageDescription ?></p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-lg">
        <form method="POST" action="<?= $formAction ?>" class="p-6">
            <?= $this->csrf() ?>
            <?php $this->component('alerts'); ?>

            <div class="form-section">
                <h3 class="form-section-header">Core Details</h3>
                <div class="form-grid form-grid-cols-2">
                    <div class="form-col-span-2">
                        <label for="name" class="form-label form-label-required">Plan Name</label>
                        <input type="text" id="name" name="name" value="<?= $this->old('name', $subscription->name ?? '') ?>" required class="form-input">
                    </div>
                    <div class="form-col-span-2">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" rows="3" class="form-input"><?= $this->old('description', $subscription->description ?? '') ?></textarea>
                    </div>
                     <div>
                        <label for="price" class="form-label form-label-required">Price (£)</label>
                        <input type="number" id="price" name="price" value="<?= $this->old('price', $subscription->price ?? '0.00') ?>" required class="form-input" step="0.01" min="0">
                    </div>
                    <div>
                        <label for="status" class="form-label form-label-required">Status</label>
                        <select id="status" name="status" class="form-input" required>
                            <option value="active" <?= $this->old('status', $subscription->status ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $this->old('status', $subscription->status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                        <p class="form-help">Inactive plans cannot be subscribed to by new members.</p>
                    </div>
                    <div class="form-col-span-2">
                        <label for="type" class="form-label form-label-required">Billing Type</label>
                        <select id="type" name="type" class="form-input" x-model="type" required>
                            <option value="recurring" <?= $this->old('type', $subscription->type ?? '') === 'recurring' ? 'selected' : '' ?>>Recurring</option>
                            <option value="fixed_length" <?= $this->old('type', $subscription->type ?? '') === 'fixed_length' ? 'selected' : '' ?>>Fixed-Length</option>
                            <option value="session_based" <?= $this->old('type', $subscription->type ?? '') === 'session_based' ? 'selected' : '' ?>>Session-Based</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div x-show="type === 'recurring'">
                    <h3 class="form-section-header">Recurring Settings</h3>
                    <div class="form-grid form-grid-cols-2">
                        <div>
                            <label for="term_length" class="form-label form-label-required">Term Length</label>
                            <input type="number" id="term_length" name="term_length" value="<?= $this->old('term_length', $subscription->term_length ?? '1') ?>" class="form-input" min="1">
                        </div>
                        <div>
                            <label for="term_unit" class="form-label form-label-required">Term Unit</label>
                            <select id="term_unit" name="term_unit" class="form-input">
                                <option value="week" <?= $this->old('term_unit', $subscription->term_unit ?? '') === 'week' ? 'selected' : '' ?>>Week(s)</option>
                                <option value="month" <?= $this->old('term_unit', $subscription->term_unit ?? 'month') === 'month' ? 'selected' : '' ?>>Month(s)</option>
                                <option value="year" <?= $this->old('term_unit', $subscription->term_unit ?? '') === 'year' ? 'selected' : '' ?>>Year(s)</option>
                            </select>
                        </div>
                        <div class="form-col-span-2">
                            <label for="fixed_start_day" class="form-label">Fixed Start Day (optional)</label>
                            <input type="number" id="fixed_start_day" name="fixed_start_day" value="<?= $this->old('fixed_start_day', $subscription->fixed_start_day ?? '') ?>" class="form-input" min="1" max="28" placeholder="e.g., 1 for 1st of the month">
                            <p class="form-help">If set, billing cycle starts on this day. Leave blank for a rolling start date.</p>
                        </div>
                        <div class="form-col-span-2">
                            <label class="flex items-center space-x-2"><input type="checkbox" name="prorata_enabled" class="form-checkbox" x-model="prorata" <?= $this->old('prorata_enabled', $subscription->prorata_enabled ?? false) ? 'checked' : '' ?>><span>Enable Pro-Rata Billing</span></label>
                            <p class="form-help">If a member joins mid-cycle, charge a pro-rata amount for the first period.</p>
                        </div>
                        <div x-show="prorata" class="form-col-span-2">
                            <label for="prorata_price" class="form-label">Pro-Rata Price (£)</label>
                            <input type="number" id="prorata_price" name="prorata_price" value="<?= $this->old('prorata_price', $subscription->prorata_price ?? '') ?>" class="form-input" step="0.01" min="0">
                            <p class="form-help">The amount to use for daily pro-rata calculation. If blank, the main price is used.</p>
                        </div>
                    </div>
                </div>
                <div x-show="type === 'fixed_length'">
                    <h3 class="form-section-header">Fixed-Length Settings</h3>
                     <div class="form-grid form-grid-cols-2">
                        <div>
                            <label for="fixed_term_length" class="form-label form-label-required">Term Length</label>
                            <input type="number" id="fixed_term_length" name="term_length" value="<?= $this->old('term_length', $subscription->term_length ?? '1') ?>" class="form-input" min="1">
                        </div>
                        <div>
                            <label for="fixed_term_unit" class="form-label form-label-required">Term Unit</label>
                            <select id="fixed_term_unit" name="term_unit" class="form-input">
                                <option value="week" <?= $this->old('term_unit', $subscription->term_unit ?? '') === 'week' ? 'selected' : '' ?>>Week(s)</option>
                                <option value="month" <?= $this->old('term_unit', $subscription->term_unit ?? 'month') === 'month' ? 'selected' : '' ?>>Month(s)</option>
                                <option value="year" <?= $this->old('term_unit', $subscription->term_unit ?? '') === 'year' ? 'selected' : '' ?>>Year(s)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div x-show="type === 'session_based'">
                    <h3 class="form-section-header">Session-Based Settings</h3>
                    <div>
                        <label for="sessions" class="form-label form-label-required">Number of Sessions</label>
                        <input type="number" id="sessions" name="sessions" value="<?= $this->old('sessions', $subscription->term_length ?? '10') ?>" class="form-input" min="1">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-header">Rules & Restrictions</h3>
                <div class="form-grid form-grid-cols-2">
                    <div>
                        <label for="capacity" class="form-label">Capacity (optional)</label>
                        <input type="number" id="capacity" name="capacity" value="<?= $this->old('capacity', $subscription->capacity ?? '') ?>" class="form-input" min="0">
                        <p class="form-help">Max members on this plan. Leave blank for unlimited.</p>
                    </div>
                    <div>
                         <label for="min_age" class="form-label">Age Range (optional)</label>
                         <div class="flex items-center gap-2">
                            <input type="number" name="min_age" value="<?= $this->old('min_age', $subscription->min_age ?? '') ?>" class="form-input" placeholder="Min">
                            <span class="text-gray-500">-</span>
                            <input type="number" name="max_age" value="<?= $this->old('max_age', $subscription->max_age ?? '') ?>" class="form-input" placeholder="Max">
                         </div>
                    </div>
                </div>
                <div class="mt-4 space-y-2">
                    <label class="flex items-center space-x-2"><input type="checkbox" name="free_trial_enabled" class="form-checkbox" <?= $this->old('free_trial_enabled', $subscription->free_trial_enabled ?? false) ? 'checked' : '' ?>><span>Eligible for one free trial session</span></label>
                    <label class="flex items-center space-x-2"><input type="checkbox" name="charge_on_start_date" class="form-checkbox" <?= $this->old('charge_on_start_date', $subscription->charge_on_start_date ?? false) ? 'checked' : '' ?>><span>Charge member on start date (not immediately)</span></label>
                </div>
            </div>

             <div class="form-section">
                <h3 class="form-section-header">Included Classes</h3>
                <p class="form-help mb-4">Select which classes this plan gives members access to. Classes already linked to a different schedule type will be disabled.</p>
                <label class="flex items-center space-x-2 mb-4">
                    <input type="checkbox" name="auto_book" class="form-checkbox" <?= $this->old('auto_book', $subscription->auto_book ?? false) ? 'checked' : '' ?>>
                    <span>Automatically book members with these subscriptions into new sessions</span>
                </label>
                <div class="h-64 overflow-y-auto border rounded-md p-2 space-y-1">
                     <?php foreach ($allClasses as $class): ?>
                        <?php
                            $isCompatible = is_class_compatible($class, (object)[
                                'type' => $this->old('type', $subscription->type ?? 'recurring'),
                                'term_length' => $this->old('term_length', $subscription->term_length),
                                'term_unit' => $this->old('term_unit', $subscription->term_unit),
                                'fixed_start_day' => $this->old('fixed_start_day', $subscription->fixed_start_day)
                            ]);
                            $isChecked = in_array($class->id, $checkedClasses);
                        ?>
                        <label class="flex items-center space-x-2 p-1 rounded <?= $isCompatible ? 'hover:bg-gray-50 cursor-pointer' : 'opacity-60 bg-gray-100 cursor-not-allowed' ?>">
                            <input type="checkbox" name="classes[]" value="<?= $class->id ?>" class="form-checkbox"
                                <?= $isChecked ? 'checked' : '' ?>
                                <?= !$isCompatible && !$isChecked ? 'disabled' : '' ?>>
                            <span class="<?= !$isCompatible ? 'text-gray-500' : '' ?>">
                                <?= htmlspecialchars($class->name) ?>
                                <?php if (!$isCompatible): ?>
                                    <span class="text-xs italic ml-2">(Incompatible Schedule)</span>
                                <?php endif; ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-actions">
                <a href="<?= $this->url('admin/subscriptions') ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><?= $submitText ?></button>
            </div>
        </form>
    </div>
</div>