<?php
/**
 * ICTECH Solutions - Student Portal Header Template
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

// Require login
Auth::requireStudent();

$currentUser = Auth::getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF']);
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
    <title><?php echo isset($pageTitle) ? h($pageTitle) . ' - ' . SITE_NAME : SITE_NAME; ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css?v=20260918">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/student.css?v=20260918">
</head>
<body>
    <div class="d-flex" style="min-height: 100vh;">
        <!-- Sidebar -->
        <nav class="bg-primary p-3 text-white" style="width: 250px;">
            <div class="mb-4">
                <h5 class="mb-3">
                    <img src="<?php echo SITE_URL; ?>assets/images/ictech-logo-transparent.png" alt="ICTECH Solutions Limited" class="site-logo site-logo-sidebar">
                </h5>
                <div class="text-light" style="font-size: 0.9rem;">
                    <div class="fw-bold"><?php echo h($currentUser['name']); ?></div>
                    <small class="text-light-50"><?php echo h($currentUser['email']); ?></small>
                </div>
            </div>

            <hr class="bg-light-50">

            <ul class="list-unstyled">
                <li class="mb-2">
                    <a href="<?php echo SITE_URL; ?>student/dashboard.php"
                       class="text-light text-decoration-none <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                </li>
                <li class="mb-2">
                    <a href="<?php echo SITE_URL; ?>student/my-courses.php"
                       class="text-light text-decoration-none <?php echo $currentPage === 'my-courses.php' ? 'active' : ''; ?>">
                        <i class="fas fa-graduation-cap me-2"></i> My Courses
                    </a>
                </li>
                <li class="mb-2">
                    <a href="<?php echo SITE_URL; ?>student/payments.php"
                       class="text-light text-decoration-none <?php echo $currentPage === 'payments.php' ? 'active' : ''; ?>">
                        <i class="fas fa-credit-card me-2"></i> Payments
                    </a>
                </li>
                <li class="mb-2">
                    <a href="<?php echo SITE_URL; ?>student/profile.php"
                       class="text-light text-decoration-none <?php echo $currentPage === 'profile.php' ? 'active' : ''; ?>">
                        <i class="fas fa-user me-2"></i> Profile
                    </a>
                </li>

                <li class="mb-2">
                    <a href="<?php echo SITE_URL; ?>courses.php" class="text-light text-decoration-none">
                        <i class="fas fa-book me-2"></i> Browse Courses
                    </a>
                </li>
            </ul>

            <hr class="bg-light-50">

            <ul class="list-unstyled">
                <li class="mb-2">
                    <form method="post" action="<?php echo SITE_URL; ?>logout.php">
                        <input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>">
                        <button type="submit" class="btn btn-link text-light text-decoration-none p-0">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="flex-grow-1">
            <!-- Top Navigation -->
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
                <div class="container-fluid">
                    <span class="navbar-brand mb-0 h1">
                        <i class="fas fa-graduation-cap"></i> Student Portal
                    </span>
                    <div class="navbar-nav ms-auto">
                        <div class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userMenu" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i> <?php echo h($currentUser['name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="post" action="<?php echo SITE_URL; ?>logout.php">
                                        <input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>">
                                        <button type="submit" class="dropdown-item border-0 bg-transparent w-100 text-start">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main class="p-4">
