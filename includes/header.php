<?php
/**
 * ICTECH Solutions - Public Header Template
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

$isLoggedIn = Auth::isLoggedIn();
$currentUser = Auth::getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo SITE_DESCRIPTION; ?>">
    <meta name="keywords" content="ICT training, professional development, technology courses">
    <meta property="og:title" content="<?php echo SITE_NAME; ?>">
    <meta property="og:description" content="<?php echo SITE_DESCRIPTION; ?>">
    <meta property="og:type" content="website">
    
    <title><?php echo isset($pageTitle) ? h($pageTitle) . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?php echo SITE_URL; ?>">
                <i class="fas fa-laptop-code"></i> ICTECH
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>courses.php">Courses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>services.php">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>resources.php">Resources</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>contact.php">Contact</a>
                    </li>
                    
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo h($currentUser['name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>student/dashboard.php">Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>student/my-courses.php">My Courses</a></li>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>student/profile.php">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-warning btn-sm ms-2" href="<?php echo SITE_URL; ?>register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</body>
</html>
