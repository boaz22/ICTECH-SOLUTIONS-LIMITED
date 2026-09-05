<?php
/**
 * ICTECH Solutions - Google Sign-In entry point
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/google-auth.php';

if (!GoogleAuth::isConfigured()) {
    header('Location: ' . SITE_URL . 'login.php?google_error=unavailable');
    exit;
}

header('Location: ' . GoogleAuth::getAuthUrl());
exit;
