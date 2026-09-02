<?php
/**
 * ICTECH Solutions - Logout
 */

require_once __DIR__ . '/includes/auth.php';

// Check if user is logged in
if (!Auth::isLoggedIn()) {
    header("Location: " . SITE_URL . "login.php");
    exit;
}

// Logout user
Auth::logout();

// Redirect to home
header("Location: " . SITE_URL . "?logout=1");
exit;

?>
