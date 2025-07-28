<?php
// app/services/MicrosoftGraphService.php
namespace App\Services;

use League\OAuth2\Client\Provider\GenericProvider;
use App\Models\Setting;
use Exception;

class MicrosoftGraphService
{
    private $provider;

    public function __construct()
    {
        $this->provider = new GenericProvider([
            'clientId'                => MSGRAPH_CLIENT_ID,
            'clientSecret'            => MSGRAPH_CLIENT_SECRET,
            'redirectUri'             => MSGRAPH_REDIRECT_URI,
            'urlAuthorize'            => '[https://login.microsoftonline.com/](https://login.microsoftonline.com/)' . MSGRAPH_TENANT_ID . '/oauth2/v2.0/authorize',
            'urlAccessToken'          => '[https://login.microsoftonline.com/](https://login.microsoftonline.com/)' . MSGRAPH_TENANT_ID . '/oauth2/v2.0/token',
            'urlResourceOwnerDetails' => '', // Not needed for sending emails directly
            'scopes'                  => ['[https://graph.microsoft.com/.default](https://graph.microsoft.com/.default)', 'offline_access'], // .default for app permissions (client credentials flow if no user context, or admin consent)
        ]);
    }

    public function getAuthorizationUrl()
    {
        // This is for the admin to grant consent.
        // It requires an admin user to sign in to Microsoft and grant permissions to the app.
        $options = [
            'scope' => '[https://graph.microsoft.com/.default](https://graph.microsoft.com/.default) offline_access'
        ];
        return $this->provider->getAuthorizationUrl($options);
    }

    public function getTokensFromAuthCode($code)
    {
        try {
            $accessToken = $this->provider->getAccessToken('authorization_code', [
                'code' => $code,
            ]);

            return [
                'access_token' => $accessToken->getToken(),
                'refresh_token' => $accessToken->getRefreshToken(),
                'expires_in' => $accessToken->getExpires() - time(), // Time until expiry in seconds
            ];
        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            throw new Exception("Microsoft Graph Token Error: " . $e->getMessage());
        }
    }

    public function refreshAccessToken()
    {
        try {
            $refreshToken = Setting::get('msgraph_refresh_token');
            if (!$refreshToken) {
                throw new Exception("No Microsoft Graph refresh token found. Admin consent may be required.");
            }

            $newAccessToken = $this->provider->getAccessToken('refresh_token', [
                'refresh_token' => $refreshToken,
            ]);

            // Update stored tokens
            Setting::set('msgraph_access_token', $newAccessToken->getToken());
            Setting::set('msgraph_refresh_token', $newAccessToken->getRefreshToken()); // Refresh tokens can also rotate
            Setting::set('msgraph_token_expires_at', $newAccessToken->getExpires());

            return $newAccessToken->getToken();
        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            // Log full error for debugging
            error_log("Microsoft Graph Token Refresh Error: " . $e->getMessage() . " - " . $e->getResponseBody());
            throw new Exception("Microsoft Graph Token Refresh Error: " . $e->getMessage() . ". Please re-authenticate the Microsoft Graph API in settings.");
        }
    }

    private function getValidAccessToken()
    {
        $accessToken = Setting::get('msgraph_access_token');
        $expiresAt = Setting::get('msgraph_token_expires_at');

        // Check if token exists and is not expired (or expires soon, e.g., within 5 minutes)
        if (!$accessToken || time() >= ($expiresAt - 300)) { // Refresh if less than 5 minutes to expiry
            return $this->refreshAccessToken();
        }
        return $accessToken;
    }

    public function sendEmail($toEmail, $subject, $bodyHtml, $senderEmail = MSGRAPH_MAIL_USER_PRINCIPAL_NAME)
    {
        try {
            if (empty($senderEmail)) {
                throw new Exception("Microsoft Graph sender email (MSGRAPH_MAIL_USER_PRINCIPAL_NAME) is not configured in .env.");
            }

            $accessToken = $this->getValidAccessToken();

            // Use 'users/{id|userPrincipalName}' for sending on behalf of a user/shared mailbox
            $graphUrl = "[https://graph.microsoft.com/v1.0/users/](https://graph.microsoft.com/v1.0/users/){$senderEmail}/sendMail";

            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
            ];

            $message = [
                'message' => [
                    'subject' => $subject,
                    'body' => [
                        'contentType' => 'HTML',
                        'content' => $bodyHtml,
                    ],
                    'toRecipients' => [
                        [
                            'emailAddress' => [
                                'address' => $toEmail,
                            ],
                        ],
                    ],
                ],
                'saveToSentItems' => true, // Save a copy in sender's Sent Items
            ];

            $ch = curl_init($graphUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Important for production

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_errno($ch)) {
                throw new Exception("cURL Error: " . curl_error($ch));
            }

            curl_close($ch);

            if ($httpCode >= 400) {
                $responseBody = json_decode($response, true);
                $errorMessage = $responseBody['error']['message'] ?? 'Unknown error';
                error_log("Microsoft Graph API Email Send Error ($httpCode): " . $errorMessage);
                throw new Exception("Failed to send email via Microsoft Graph API: " . $errorMessage);
            }

            return true;
        } catch (Exception $e) {
            error_log("Failed to send email via Microsoft Graph: " . $e->getMessage());
            throw $e;
        }
    }
}
