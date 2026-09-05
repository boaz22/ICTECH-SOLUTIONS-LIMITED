<?php
/**
 * ICTECH Solutions - Google Sign-In callback
 * Only signs in accounts that already exist; never registers new ones.
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/google-auth.php';

if (!GoogleAuth::isConfigured()) {
    header('Location: ' . SITE_URL . 'login.php?google_error=unavailable');
    exit;
}

$code = getParam('code', '');
$state = getParam('state', '');

if (!empty($_GET['error']) || empty($code) || !GoogleAuth::verifyState($state)) {
    header('Location: ' . SITE_URL . 'login.php?google_error=failed');
    exit;
}

$email = GoogleAuth::getVerifiedEmail($code);
if (!$email) {
    header('Location: ' . SITE_URL . 'login.php?google_error=failed');
    exit;
}

$result = Auth::loginWithGoogleEmail($email);
if (!$result['success']) {
    header('Location: ' . SITE_URL . 'login.php?google_error=not_registered');
    exit;
}

$defaultRedirect = $result['role'] === 'trainer'
    ? SITE_URL . 'trainer/dashboard.php'
    : ($result['role'] === 'admin' ? SITE_URL . 'admin/index.php' : SITE_URL . 'student/dashboard.php');

header('Location: ' . $defaultRedirect);
exit;
