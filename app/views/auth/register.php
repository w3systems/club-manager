<?php
// app/views/auth/register.php
 require_once VIEW_PATH . '/layouts/master.php';  use App\Helpers\functions as Helpers; ?>

<?php Helpers\start_section('content'); ?>

<div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Create your account
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Or
                <a href="/login" class="font-medium text-indigo-600 hover:text-indigo-500">
                    sign in to an existing account
                </a>
            </p>
        </div>

        <?php Helpers\displayFlashMessages(); ?>
        <?php Helpers\displayErrors(); ?>

        <form class="mt-8 space-y-6 bg-white p-8 rounded-lg shadow" action="/register" method="POST">
            <input type="hidden" name="free_trial_class_id" value="<?= Helpers\old('free_trial_class_id', $_GET['free_trial_class_id'] ?? '') ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                    <input type="text" name="first_name" id="first_name" required
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           value="<?= Helpers\old('first_name') ?>">
                    <?php Helpers\displayErrors('first_name'); ?>
                </div>
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input type="text" name="last_name" id="last_name" required
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           value="<?= Helpers\old('last_name') ?>">
                    <?php Helpers\displayErrors('last_name'); ?>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input type="email" name="email" id="email" autocomplete="email" required
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           value="<?= Helpers\old('email') ?>">
                    <?php Helpers\displayErrors('email'); ?>
                </div>
                <div>
                    <label for="mobile" class="block text-sm font-medium text-gray-700">Mobile Number</label>
                    <input type="tel" name="mobile" id="mobile" required
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           value="<?= Helpers\old('mobile') ?>">
                    <?php Helpers\displayErrors('mobile'); ?>
                </div>
            </div>

            <div>
                <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Date of Birth</label>
                <input type="date" name="date_of_birth" id="date_of_birth" required
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                       value="<?= Helpers\old('date_of_birth') ?>">
                <?php Helpers\displayErrors('date_of_birth'); ?>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" id="password" autocomplete="new-password" required
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <?php Helpers\displayErrors('password'); ?>
                </div>
                <div>
                    <label for="password_confirm" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <input type="password" name="password_confirm" id="password_confirm" autocomplete="new-password" required
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <?php Helpers\displayErrors('password_confirm'); ?>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Emergency Contact</h3>
                <p class="mt-1 text-sm text-gray-600">Details of someone we can contact in an emergency.</p>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="emergency_contact_name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" name="emergency_contact_name" id="emergency_contact_name" required
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               value="<?= Helpers\old('emergency_contact_name') ?>">
                        <?php Helpers\displayErrors('emergency_contact_name'); ?>
                    </div>
                    <div>
                        <label for="emergency_contact_number" class="block text-sm font-medium text-gray-700">Number</label>
                        <input type="tel" name="emergency_contact_number" id="emergency_contact_number" required
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               value="<?= Helpers\old('emergency_contact_number') ?>">
                        <?php Helpers\displayErrors('emergency_contact_number'); ?>
                    </div>
                    <div>
                        <label for="emergency_contact_relationship" class="block text-sm font-medium text-gray-700">Relationship</label>
                        <input type="text" name="emergency_contact_relationship" id="emergency_contact_relationship" required
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               value="<?= Helpers\old('emergency_contact_relationship') ?>">
                        <?php Helpers\displayErrors('emergency_contact_relationship'); ?>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Parent/Guardian Details (if under 18)</h3>
                <p class="mt-1 text-sm text-gray-600">Please provide details for a parent or legal guardian if the member is under 18 years old.</p>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="parent_guardian_email" class="block text-sm font-medium text-gray-700">Parent/Guardian Email</label>
                        <input type="email" name="parent_guardian_email" id="parent_guardian_email"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               value="<?= Helpers\old('parent_guardian_email') ?>">
                        <?php Helpers\displayErrors('parent_guardian_email'); ?>
                    </div>
                    <div>
                        <label for="parent_guardian_mobile" class="block text-sm font-medium text-gray-700">Parent/Guardian Mobile</label>
                        <input type="tel" name="parent_guardian_mobile" id="parent_guardian_mobile"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               value="<?= Helpers\old('parent_guardian_mobile') ?>">
                        <?php Helpers\displayErrors('parent_guardian_mobile'); ?>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6 space-y-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Consents & Terms</h3>
                <div class="relative flex items-start">
                    <div class="flex items-center h-5">
                        <input id="consent_photography" name="consent_photography" type="checkbox" value="1" required
                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                <?= Helpers\old('consent_photography') == '1' ? 'checked' : '' ?>>
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="consent_photography" class="font-medium text-gray-700">Consent for Photography/Videography</label>
                        <p class="text-gray-500">I consent to the club taking and using photographs/videos of me for promotional purposes.</p>
                        <?php Helpers\displayErrors('consent_photography'); ?>
                    </div>
                </div>
                <div class="relative flex items-start">
                    <div class="flex items-center h-5">
                        <input id="consent_first_aid" name="consent_first_aid" type="checkbox" value="1" required
                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                <?= Helpers\old('consent_first_aid') == '1' ? 'checked' : '' ?>>
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="consent_first_aid" class="font-medium text-gray-700">Consent to Administer First Aid</label>
                        <p class="text-gray-500">I consent to appropriate first aid being administered in case of emergency.</p>
                        <?php Helpers\displayErrors('consent_first_aid'); ?>
                    </div>
                </div>
                <div class="relative flex items-start">
                    <div class="flex items-center h-5">
                        <input id="terms_conditions_acceptance" name="terms_conditions_acceptance" type="checkbox" value="1" required
                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                <?= Helpers\old('terms_conditions_acceptance') == '1' ? 'checked' : '' ?>>
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="terms_conditions_acceptance" class="font-medium text-gray-700">Acceptance of Terms & Conditions</label>
                        <p class="text-gray-500">I accept the <a href="#" class="text-indigo-600 hover:text-indigo-500">Terms and Conditions</a> of the club.</p>
                        <?php Helpers\displayErrors('terms_conditions_acceptance'); ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($freeTrialClasses)): ?>
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Free Trial Class (Optional)</h3>
                <p class="mt-1 text-sm text-gray-600">Select a free trial class if you wish to try one before committing.</p>
                <div class="mt-4">
                    <label for="free_trial_class_select" class="sr-only">Choose a Free Trial Class</label>
                    <select id="free_trial_class_select" name="free_trial_class_id"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">No Free Trial Class</option>
                        <?php foreach ($freeTrialClasses as $class): ?>
                            <option value="<?= Helpers\esc($class['id']) ?>"
                                <?= Helpers\old('free_trial_class_id', $_GET['free_trial_class_id'] ?? '') == $class['id'] ? 'selected' : '' ?>>
                                <?= Helpers\esc($class['name']) ?> (<?= (new DateTime($class['instance_date_time']))->format('D, M jS, Y H:i') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <?php endif; ?>

            <div>
                <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Register Account
                </button>
            </div>
        </form>
    </div>
</div>

<?php Helpers\end_section(); ?>

