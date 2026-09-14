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

// Remember the role so students are sent back to the student login view
$wasStudent = Auth::getCurrentUser()['role'] === 'student';

// Logout user
Auth::logout();

// Redirect to the login page (student logins go back to the student-facing form)
$loginUrl = SITE_URL . 'login.php?logout=1';
if ($wasStudent) {
    $loginUrl .= '&student_access=1';
}
header("Location: " . $loginUrl);
exit;

?>
