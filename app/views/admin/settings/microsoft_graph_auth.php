<?php
// app/views/admin/settings/microsoft_graph_auth.php
 require_once VIEW_PATH . '/admin/layouts/admin.php';  use App\Helpers\functions as Helpers; ?>

<?php Helpers\start_section('content'); ?>

<h1 class="text-2xl font-semibold text-gray-900 mb-6">Microsoft Graph API Authentication</h1>

<?php Helpers\displayFlashMessages();  Helpers\displayErrors(); ?>

<div class="bg-white shadow overflow-hidden sm:rounded-lg p-6 text-center">
    <h2 class="text-xl font-medium text-gray-900 mb-4">Grant Admin Consent</h2>
    <p class="text-gray-700 mb-6">
        To allow the application to send emails via Microsoft Graph API, you need to grant admin consent.
        This will redirect you to Microsoft's login page to authorize the application.
    </p>

    <?php if (isset($authUrl) && !empty($authUrl)): ?>
        <a href="<?= Helpers\esc($authUrl) ?>"
           class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <svg class="-ml-1 mr-3 h-5 w-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path d="M10 2a8 8 0 00-8 8c0 4.418 4.03 8 9 8a9.863 9.863 0 004.255-.949L17 18l1.395-3.72C18.512 13.042 19 11.574 19 10a8 8 0 00-8-8zM7 9a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1zm1 3a1 1 0 00-1 1v.01a1 1 0 001 1h4a1 1 0 001-1V12a1 1 0 00-1-1H8z" />
            </svg>
            Continue to Microsoft for Consent
        </a>
        <p class="mt-4 text-sm text-gray-500">You will be redirected back to this application after authorization.</p>
    <?php else: ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
            <p class="font-bold">Authentication URL Not Generated</p>
            <p>Please ensure `MSGRAPH_TENANT_ID`, `MSGRAPH_CLIENT_ID`, and `MSGRAPH_CLIENT_SECRET` are correctly configured in your `.env` file.</p>
        </div>
    <?php endif; ?>
</div>

<?php Helpers\end_section(); ?>
