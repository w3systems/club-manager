<?php
// app/views/member/profile.php
 require_once VIEW_PATH . '/member/layouts/member.php';  use App\Helpers\functions as Helpers; ?>

<?php Helpers\start_section('content'); ?>

<h1 class="text-2xl font-semibold text-gray-900 mb-6">Your Profile</h1>

<?php Helpers\displayFlashMessages();  Helpers\displayErrors(); ?>

<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            Personal Information
        </h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">
            Details about your account and personal information.
        </p>
    </div>
    <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
        <form action="/profile/update" method="POST" class="space-y-6">
            <div class="sm:divide-y sm:divide-gray-200">
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">First Name</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <input type="text" name="first_name" value="<?= Helpers\esc($member['first_name']) ?>" required
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <?php Helpers\displayErrors('first_name'); ?>
                    </dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Last Name</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <input type="text" name="last_name" value="<?= Helpers\esc($member['last_name']) ?>" required
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <?php Helpers\displayErrors('last_name'); ?>
                    </dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Email address</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <input type="email" name="email" value="<?= Helpers\esc($member['email']) ?>" required
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <?php Helpers\displayErrors('email'); ?>
                    </dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Mobile Number</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <input type="tel" name="mobile" value="<?= Helpers\esc($member['mobile']) ?>" required
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <?php Helpers\displayErrors('mobile'); ?>
                    </dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Date of Birth</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <input type="date" name="date_of_birth" value="<?= Helpers\esc($member['date_of_birth']) ?>" required
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <?php Helpers\displayErrors('date_of_birth'); ?>
                    </dd>
                </div>

                <div class="px-4 py-5 sm:px-6 bg-gray-50 border-t border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Emergency Contact</h3>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <input type="text" name="emergency_contact_name" value="<?= Helpers\esc($member['emergency_contact_name']) ?>" required
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <?php Helpers\displayErrors('emergency_contact_name'); ?>
                    </dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Number</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <input type="tel" name="emergency_contact_number" value="<?= Helpers\esc($member['emergency_contact_number']) ?>" required
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <?php Helpers\displayErrors('emergency_contact_number'); ?>
                    </dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Relationship</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <input type="text" name="emergency_contact_relationship" value="<?= Helpers\esc($member['emergency_contact_relationship']) ?>" required
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <?php Helpers\displayErrors('emergency_contact_relationship'); ?>
                    </dd>
                </div>

                <?php if (Helpers\calculateAge($member['date_of_birth']) < 18): ?>
                    <div class="px-4 py-5 sm:px-6 bg-gray-50 border-t border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Parent/Guardian Details</h3>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Parent/Guardian Email</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <input type="email" name="parent_guardian_email" value="<?= Helpers\esc($member['parent_guardian_email']) ?>"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <?php Helpers\displayErrors('parent_guardian_email'); ?>
                        </dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Parent/Guardian Mobile</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <input type="tel" name="parent_guardian_mobile" value="<?= Helpers\esc($member['parent_guardian_mobile']) ?>"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <?php Helpers\displayErrors('parent_guardian_mobile'); ?>
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
                                   <?= $member['consent_photography'] ? 'checked' : '' ?>>
                            <label for="consent_photography" class="ml-2 block text-sm text-gray-900">
                                I consent to photography/videography.
                            </label>
                        </div>
                        <?php Helpers\displayErrors('consent_photography'); ?>
                    </dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">First Aid Consent</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <div class="flex items-center">
                            <input id="consent_first_aid" name="consent_first_aid" type="checkbox" value="1"
                                   class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                   <?= $member['consent_first_aid'] ? 'checked' : '' ?>>
                            <label for="consent_first_aid" class="ml-2 block text-sm text-gray-900">
                                I consent to first aid administration.
                            </label>
                        </div>
                        <?php Helpers\displayErrors('consent_first_aid'); ?>
                    </dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Terms & Conditions</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <div class="flex items-center">
                            <input id="terms_conditions_acceptance" name="terms_conditions_acceptance" type="checkbox" value="1"
                                   class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                   <?= $member['terms_conditions_acceptance'] ? 'checked' : '' ?>>
                            <label for="terms_conditions_acceptance" class="ml-2 block text-sm text-gray-900">
                                I accept the Terms & Conditions.
                            </label>
                        </div>
                        <?php Helpers\displayErrors('terms_conditions_acceptance'); ?>
                    </dd>
                </div>
            </div>

            <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<?php Helpers\end_section(); ?>
