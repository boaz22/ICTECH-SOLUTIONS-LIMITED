<?php
/**
 * ICTECH Solutions - Student Portal Header Template
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

Auth::requireStudent();

$currentUser = Auth::getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF']);
$portalPageTitle = isset($pageTitle) && $pageTitle ? h($pageTitle) . ' - Student Portal' : 'Student Portal';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo SITE_DESCRIPTION; ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo SITE_URL; ?>assets/images/favicon-32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo SITE_URL; ?>assets/images/favicon-16.png">
    <link rel="apple-touch-icon" href="<?php echo SITE_URL; ?>assets/images/apple-touch-icon.png">
    <title><?php echo $portalPageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/student.css?v=20260919">
</head>
<body class="student-portal-body">
    <div class="student-portal-shell">
        <aside class="student-sidebar">
            <div class="student-sidebar-brand">
                <img src="<?php echo SITE_URL; ?>assets/images/ictech-logo-transparent.png" alt="ICTECH Solutions Limited" class="student-sidebar-logo">
                <div class="student-sidebar-title">Student Portal</div>
                <div class="student-sidebar-subtitle">ICTECH learning access</div>
            </div>

            <div class="student-sidebar-user">
                <div class="student-sidebar-user-name"><?php echo h($currentUser['name']); ?></div>
                <small class="student-sidebar-user-email"><?php echo h($currentUser['email']); ?></small>
            </div>

            <nav class="student-sidebar-nav" aria-label="Student portal navigation">
                <a href="<?php echo SITE_URL; ?>student/dashboard.php"
                   class="student-sidebar-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line me-2"></i> Dashboard
                </a>
                <a href="<?php echo SITE_URL; ?>student/my-courses.php"
                   class="student-sidebar-link <?php echo in_array($currentPage, ['my-courses.php', 'course.php', 'certificate.php'], true) ? 'active' : ''; ?>">
                    <i class="fas fa-graduation-cap me-2"></i> My Courses
                </a>
                <a href="<?php echo SITE_URL; ?>student/profile.php"
                   class="student-sidebar-link <?php echo $currentPage === 'profile.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user me-2"></i> Profile
                </a>
            </nav>

            <div class="student-sidebar-support">
                <div class="student-sidebar-support-title">
                    <i class="fas fa-life-ring me-2"></i>Need help?
                </div>
                <a href="mailto:<?php echo h(MAIL_REPLY_TO); ?>" class="student-sidebar-support-link"><?php echo h(MAIL_REPLY_TO); ?></a>
            </div>

            <form method="post" action="<?php echo SITE_URL; ?>logout.php" class="student-logout-form">
                <input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>">
                <button type="submit" class="student-sidebar-logout">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </button>
            </form>
        </aside>

        <div class="student-portal-main">
            <header class="student-topbar">
                <div>
                    <div class="student-topbar-eyebrow">ICTECH</div>
                    <div class="student-topbar-title"><?php echo isset($pageTitle) ? h($pageTitle) : 'Student Portal'; ?></div>
                </div>
                <div class="student-topbar-user">
                    <i class="fas fa-user-circle me-2"></i><?php echo h($currentUser['name']); ?>
                </div>
            </header>

            <main class="student-portal-content">
