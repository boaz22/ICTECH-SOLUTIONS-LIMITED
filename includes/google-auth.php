<?php
/**
 * ICTECH Solutions - Google Sign-In (OAuth 2.0 authorization code flow)
 * Verifies the Google account only; account creation is never performed here.
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

class GoogleAuth
{
    public static function isConfigured()
    {
        return defined('GOOGLE_CLIENT_ID')
            && defined('GOOGLE_CLIENT_SECRET')
            && GOOGLE_CLIENT_ID !== 'your-google-client-id'
            && GOOGLE_CLIENT_SECRET !== 'your-google-client-secret';
    }

    /**
     * Build the Google consent screen URL and store an anti-CSRF state token.
     */
    public static function getAuthUrl()
    {
        Auth::startSession();

        $state = bin2hex(random_bytes(16));
        $_SESSION['google_oauth_state'] = $state;

        $params = [
            'client_id' => GOOGLE_CLIENT_ID,
            'redirect_uri' => GOOGLE_REDIRECT_URI,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'prompt' => 'select_account',
        ];

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }

    /**
     * Verify the state token returned by Google.
     */
    public static function verifyState($state)
    {
        Auth::startSession();

        if (empty($_SESSION['google_oauth_state']) || empty($state)) {
            return false;
        }

        $valid = hash_equals($_SESSION['google_oauth_state'], $state);
        unset($_SESSION['google_oauth_state']);

        return $valid;
    }

    /**
     * Exchange the authorization code for a verified Google email address.
     * Returns null on any failure.
     */
    public static function getVerifiedEmail($code)
    {
        $token = self::exchangeCodeForToken($code);
        if (!$token || empty($token['access_token'])) {
            return null;
        }

        $userInfo = self::fetchUserInfo($token['access_token']);
        if (!$userInfo || empty($userInfo['email']) || empty($userInfo['email_verified'])) {
            return null;
        }

        return $userInfo['email'];
    }

    private static function exchangeCodeForToken($code)
    {
        if (!function_exists('curl_init')) {
            return null;
        }

        $payload = http_build_query([
            'code' => $code,
            'client_id' => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'redirect_uri' => GOOGLE_REDIRECT_URI,
            'grant_type' => 'authorization_code',
        ]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://oauth2.googleapis.com/token',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            return null;
        }

        return json_decode($response, true);
    }

    private static function fetchUserInfo($accessToken)
    {
        if (!function_exists('curl_init')) {
            return null;
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://www.googleapis.com/oauth2/v3/userinfo',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $accessToken],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            return null;
        }

        return json_decode($response, true);
    }
}
