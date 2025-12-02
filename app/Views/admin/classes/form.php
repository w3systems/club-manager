<?php
$isEdit = isset($class) && !empty($class->id);
$formAction = $isEdit ? $this->url('admin/classes/' . $class->id) : $this->url('admin/classes');
$pageTitle = $isEdit ? 'Edit Class' : 'Add New Class';
$submitText = $isEdit ? 'Update Class' : 'Create Class';
?>

<div class="space-y-6" x-data="{ type: '<?= htmlspecialchars($this->old('class_type', $class->class_type ?? 'single')) ?>', payg: <?= $this->old('allow_booking_outside_subscription', $class->allow_booking_outside_subscription ?? false) ? 'true' : 'false' ?> }">
    <div class="flex items-center gap-4">
        <a href="<?= $this->url('admin/classes') ?>" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left text-xl"></i></a>
        <div><h1 class="text-2xl font-bold text-gray-900"><?= $pageTitle ?></h1></div>
    </div>

    <div class="bg-white rounded-lg shadow-lg">
        <form method="POST" action="<?= $formAction ?>" class="p-6">
            <?= $this->csrf() ?>
            <?php $this->component('alerts'); ?>

            <div class="form-section">
                <h3 class="form-section-header">Core Details</h3>
                <div class="form-grid form-grid-cols-2">
                    <div class="form-col-span-2">
                        <label for="name" class="form-label form-label-required">Class Name</label>
                        <input type="text" id="name" name="name" value="<?= $this->old('name', $class->name ?? '') ?>" required class="form-input">
                    </div>
                    <div class="form-col-span-2">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" rows="3" class="form-input"><?= $this->old('description', $class->description ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-header">Schedule</h3>
                <div>
                    <label class="form-label form-label-required">Class Type</label>
                    <select id="class_type" name="class_type" class="form-input" x-model="type" required>
                        <option value="single" <?= $this->old('class_type', $class->class_type ?? '') === 'single' ? 'selected' : '' ?>>Single (One-off event)</option>
                        <option value="recurring_parent" <?= $this->old('class_type', $class->class_type ?? '') === 'recurring_parent' ? 'selected' : '' ?>>Recurring (Weekly, Monthly, etc.)</option>
                    </select>
                </div>
                <div class="form-grid form-grid-cols-2 mt-4">
                    <div x-show="type === 'single'">
                        <label for="start_date" class="form-label form-label-required">Date</label>
                        <input type="date" id="start_date" name="start_date" value="<?= $this->old('start_date', ($class->original_start_date ?? new DateTime())->format('Y-m-d')) ?>" class="form-input">
                    </div>
                    <div x-show="type === 'recurring_parent'">
                        <label for="original_start_date" class="form-label form-label-required">Series Start Date</label>
                        <input type="date" id="original_start_date" name="original_start_date" value="<?= $this->old('original_start_date', ($class->original_start_date ?? new DateTime())->format('Y-m-d')) ?>" class="form-input">
                    </div>
                    <div>
                        <label for="start_time" class="form-label form-label-required">Start Time</label>
                        <input type="time" id="start_time" name="start_time" value="<?= $this->old('start_time', $class->start_time ?? '18:00') ?>" class="form-input">
                    </div>
                    <div x-show="type === 'recurring_parent'">
                        <label for="day_of_week" class="form-label form-label-required">Day of Week</label>
                        <select id="day_of_week" name="day_of_week" class="form-input">
                            <option value="1" <?= $this->old('day_of_week', $class->day_of_week ?? '') == 1 ? 'selected' : '' ?>>Monday</option>
                            <option value="2" <?= $this->old('day_of_week', $class->day_of_week ?? '') == 2 ? 'selected' : '' ?>>Tuesday</option>
                            <option value="3" <?= $this->old('day_of_week', $class->day_of_week ?? '') == 3 ? 'selected' : '' ?>>Wednesday</option>
                            <option value="4" <?= $this->old('day_of_week', $class->day_of_week ?? '') == 4 ? 'selected' : '' ?>>Thursday</option>
                            <option value="5" <?= $this->old('day_of_week', $class->day_of_week ?? '') == 5 ? 'selected' : '' ?>>Friday</option>
                            <option value="6" <?= $this->old('day_of_week', $class->day_of_week ?? '') == 6 ? 'selected' : '' ?>>Saturday</option>
                            <option value="7" <?= $this->old('day_of_week', $class->day_of_week ?? '') == 7 ? 'selected' : '' ?>>Sunday</option>
                        </select>
                    </div>
                    <div x-show="type === 'recurring_parent'">
                        <label for="frequency" class="form-label form-label-required">Frequency</label>
                        <select id="frequency" name="frequency" class="form-input">
                            <option value="weekly" <?= $this->old('frequency', $class->frequency ?? 'weekly') === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                            <option value="fortnightly" <?= $this->old('frequency', $class->frequency ?? '') === 'fortnightly' ? 'selected' : '' ?>>Fortnightly</option>
                            <option value="monthly" <?= $this->old('frequency', $class->frequency ?? '') === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-header">Settings & Pricing</h3>
                <div class="form-grid form-grid-cols-2">
                    <div>
                        <label for="duration_minutes" class="form-label form-label-required">Duration (Minutes)</label>
                        <input type="number" id="duration_minutes" name="duration_minutes" value="<?= $this->old('duration_minutes', $class->duration_minutes ?? '60') ?>" required class="form-input" min="1">
                    </div>
                    <div>
                        <label for="capacity" class="form-label">Capacity</label>
                        <input type="number" id="capacity" name="capacity" value="<?= $this->old('capacity', $class->capacity ?? '') ?>" class="form-input" min="0" placeholder="Unlimited">
                    </div>
                </div>
                 <div class="mt-4 space-y-4">
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="allow_booking_outside_subscription" class="form-checkbox" <?= $this->old('allow_booking_outside_subscription', $class->allow_booking_outside_subscription ?? false) ? 'checked' : '' ?> x-model="payg">
                        <span>Allow Pay-As-You-Go bookings</span>
                    </label>
                    <div x-show="payg">
                        <label for="session_price" class="form-label">Single Session Price (£)</label>
                        <input type="number" id="session_price" name="session_price" value="<?= $this->old('session_price', $class->session_price ?? '') ?>" class="form-input" step="0.01" min="0" placeholder="e.g., 10.00">
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h3 class="form-section-header">Included In Subscriptions</h3>
                 <p class="form-help">To link this class to a subscription, please edit the desired <a href="<?= $this->url('admin/subscriptions') ?>" class="text-blue-600 hover:underline">Subscription Plan</a> and add it to the "Included Classes" section.</p>
                <?php if ($isEdit && !empty($selectedSubscriptions)): ?>
                    <p class="form-help mt-4">This class is currently included in the following subscription plans:</p>
                    <ul class="list-disc list-inside space-y-1 text-sm text-gray-700 mt-2">
                        <?php 
                        foreach($selectedSubscriptions as $subId) {
                            $sub = \App\Models\Subscription::find($subId);
                            //if ($sub) echo '<li>' . htmlspecialchars($sub->name) . '</li>';
							if ($sub)  {
								echo '<li><a href="' . $this->url('admin/subscriptions/' . $sub->id . '/edit') . '" class="text-indigo-600 hover:text-indigo-800 hover:underline">' . htmlspecialchars($sub->name) . '</a></li>';
                            }
                        }
                        ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <a href="<?= $this->url('admin/classes') ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><?= $submitText ?></button>
            </div>
        </form>
    </div>
</div>