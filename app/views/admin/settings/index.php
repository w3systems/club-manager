<?php
// app/views/admin/settings/index.php
 require_once VIEW_PATH . '/admin/layouts/admin.php';  use App\Helpers\functions as Helpers; ?>

<?php start_section('content'); ?>

<h1 class="text-2xl font-semibold text-gray-900 mb-6">Application Settings</h1>

<?php displayFlashMessages();  displayErrors(); ?>

<?php if (\App\Core\Auth::hasPermission('manage_settings')): ?>
    <div class="bg-white shadow overflow-hidden sm:rounded-lg p-6">
        <h2 class="text-xl font-medium text-gray-900 mb-4">General Settings</h2>
        <form action="/admin/settings/save" method="POST" class="space-y-4">
            <div>
                <label for="site_color_primary" class="block text-sm font-medium text-gray-700">Primary Site Color</label>
                <input type="color" name="site_color_primary" id="site_color_primary"
                       value="<?= old('site_color_primary', \App\Models\Setting::get('site_color_primary')) ?>"
                       class="mt-1 block w-24 h-10 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <?php displayErrors('site_color_primary'); ?>
            </div>
            <div>
                <label for="site_color_secondary" class="block text-sm font-medium text-gray-700">Secondary Site Color</label>
                <input type="color" name="site_color_secondary" id="site_color_secondary"
                       value="<?= old('site_color_secondary', \App\Models\Setting::get('site_color_secondary')) ?>"
                       class="mt-1 block w-24 h-10 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <?php displayErrors('site_color_secondary'); ?>
            </div>

            <h2 class="text-xl font-medium text-gray-900 mt-8 mb-4">Payment Gateway Settings</h2>
            <div class="space-y-4">
                <div class="relative flex items-start">
                    <div class="flex items-center h-5">
                        <input id="payment_type_stripe_enabled" name="payment_type_stripe_enabled" type="checkbox" value="1"
                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                               <?= \App\Models\Setting::get('payment_type_stripe_enabled') == '1' ? 'checked' : '' ?>>
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="payment_type_stripe_enabled" class="font-medium text-gray-700">Enable Stripe Payments</label>
                        <p class="text-gray-500">Allow members to pay via Stripe (credit/debit cards).</p>
                    </div>
                </div>
                <div class="relative flex items-start">
                    <div class="flex items-center h-5">
                        <input id="payment_type_cash_enabled" name="payment_type_cash_enabled" type="checkbox" value="1"
                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                               <?= \App\Models\Setting::get('payment_type_cash_enabled') == '1' ? 'checked' : '' ?>>
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="payment_type_cash_enabled" class="font-medium text-gray-700">Enable Cash Payments (Admin Only)</label>
                        <p class="text-gray-500">Allow admins to record cash payments for members.</p>
                    </div>
                </div>
                <div class="relative flex items-start">
                    <div class="flex items-center h-5">
                        <input id="payment_type_bank_transfer_enabled" name="payment_type_bank_transfer_enabled" type="checkbox" value="1"
                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                               <?= \App\Models\Setting::get('payment_type_bank_transfer_enabled') == '1' ? 'checked' : '' ?>>
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="payment_type_bank_transfer_enabled" class="font-medium text-gray-700">Enable Bank Transfer Payments (Admin Only)</label>
                        <p class="text-gray-500">Allow admins to record bank transfer payments for members.</p>
                    </div>
                </div>
            </div>

            <div class="pt-5">
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Save Settings
                </button>
            </div>
        </form>
    </div>

    <div class="mt-8 bg-white shadow overflow-hidden sm:rounded-lg p-6">
        <h2 class="text-xl font-medium text-gray-900 mb-4">Microsoft Graph API Integration</h2>
        <?php if (empty(MSGRAPH_CLIENT_ID) || empty(MSGRAPH_CLIENT_SECRET) || empty(MSGRAPH_TENANT_ID)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                <p class="font-bold">Missing API Credentials</p>
                <p>Please configure `MSGRAPH_TENANT_ID`, `MSGRAPH_CLIENT_ID`, and `MSGRAPH_CLIENT_SECRET` in your `.env` file to enable Microsoft Graph API integration for emails.</p>
            </div>
        <?php else: ?>
            <p class="mb-4 text-gray-500">Integrate with Microsoft Graph API to send emails via your Microsoft 365 account or shared mailbox.</p>
            <?php
            $accessToken = \App\Models\Setting::get('msgraph_access_token');
            $expiresAt = \App\Models\Setting::get('msgraph_token_expires_at');
            $tokenExpired = ($accessToken && $expiresAt && time() >= $expiresAt) ? true : false;
            ?>

            <?php if ($accessToken && !$tokenExpired): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                    <p class="font-bold">Connected!</p>
                    <p>Microsoft Graph API is connected. Access token expires: <?= (new DateTime('@' . $expiresAt))->format('M j, Y H:i:s') ?>.</p>
                </div>
                <button type="button" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" onclick="alert('Revoke functionality to be implemented. You might need to revoke consent in Azure AD.')">
                    Revoke Consent
                </button>
            <?php elseif ($accessToken && $tokenExpired): ?>
                 <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">
                    <p class="font-bold">Token Expired</p>
                    <p>Your Microsoft Graph API access token has expired. Please re-authenticate.</p>
                </div>
                <a href="/auth/microsoft/setup" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Re-authenticate
                </a>
            <?php else: ?>
                <p class="mb-4 text-gray-500">Click the button below to initiate the authentication process and grant consent for this application to send emails on your behalf.</p>
                <a href="/auth/microsoft/setup" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Connect Microsoft Graph API
                </a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php end_section(); ?>
