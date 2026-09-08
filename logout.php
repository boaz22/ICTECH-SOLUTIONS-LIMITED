<?php
/**
 * ICTECH Solutions - Logout
 */

require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed.');
}

if (!Auth::isLoggedIn() || !Auth::verifyCSRFToken(postParam('csrf_token'))) {
    header("Location: " . SITE_URL . "login.php");
    exit;
}

// Logout user
Auth::logout();

// Redirect to home
header("Location: " . SITE_URL . "?logout=1");
exit;

?>
