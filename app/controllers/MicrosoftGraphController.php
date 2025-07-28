<?php
// app/controllers/MicrosoftGraphController.php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Services\MicrosoftGraphService;
use App\Models\Setting;

class MicrosoftGraphController extends Controller
{
    public function __construct()
    {
        // Only admin can setup API auth
        Auth::requireAdmin();
        Auth::checkPermission('manage_settings'); // Specific permission for this action
    }

    public function setupAuth()
    {
        try {
            $graphService = new MicrosoftGraphService();
            $authUrl = $graphService->getAuthorizationUrl();
            $_SESSION['microsoft_auth_url'] = $authUrl; // Store for redirect after consent
            $this->view('admin.settings.microsoft_graph_auth', ['authUrl' => $authUrl]);
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Error generating Microsoft Graph auth URL: ' . $e->getMessage();
            $this->redirect('/admin/settings');
        }
    }

    public function callback()
    {
        // This route is called by Azure AD after user consent
        // No permission check here as it's an external callback
        $code = $_GET['code'] ?? null;
        if (!$code) {
            $_SESSION['error_message'] = 'Microsoft Graph authentication failed: No authorization code received.';
            $this->redirect('/admin/settings');
        }

        try {
            $graphService = new MicrosoftGraphService();
            $tokens = $graphService->getTokensFromAuthCode($code);

            // Securely store tokens. For simplicity, storing in settings table.
            // In a production environment, ensure these are encrypted in the DB
            // or stored in a more secure vault solution.
            Setting::set('msgraph_access_token', $tokens['access_token']);
            Setting::set('msgraph_refresh_token', $tokens['refresh_token']);
            Setting::set('msgraph_token_expires_at', time() + $tokens['expires_in']);

            $_SESSION['success_message'] = 'Microsoft Graph API connected successfully!';
            $this->redirect('/admin/settings');
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Microsoft Graph API callback failed: ' . $e->getMessage();
            $this->redirect('/admin/settings');
        }
    }
}
