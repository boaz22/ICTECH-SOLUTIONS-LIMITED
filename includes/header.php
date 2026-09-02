<?php
/**
 * ICTECH Solutions - Public Header Template
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

$isLoggedIn = Auth::isLoggedIn();
$currentUser = Auth::getCurrentUser();
$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
$seoTitle = isset($pageTitle) ? $pageTitle : SITE_NAME;
$seoDescription = $pageDescription ?? SITE_DESCRIPTION;
$canonicalUrl = $currentPage === 'index.php' ? SITE_URL : SITE_URL . $currentPage;
$socialImage = SITE_URL . 'assets/images/hero-tech.jpg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo h($seoDescription); ?>">
    <meta name="keywords" content="ICT training Kenya, technology courses, professional certification, corporate training, IT solutions">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?php echo h($canonicalUrl); ?>">
    <meta property="og:locale" content="en_KE">
    <meta property="og:site_name" content="<?php echo h(SITE_NAME); ?>">
    <meta property="og:title" content="<?php echo h($seoTitle); ?>">
    <meta property="og:description" content="<?php echo h($seoDescription); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo h($canonicalUrl); ?>">
    <meta property="og:image" content="<?php echo h($socialImage); ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo h($seoTitle); ?>">
    <meta name="twitter:description" content="<?php echo h($seoDescription); ?>">
    <meta name="twitter:image" content="<?php echo h($socialImage); ?>">

    <title><?php echo h($seoTitle); ?> - <?php echo h(SITE_NAME); ?></title>

    <script type="application/ld+json">
    <?php echo json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'EducationalOrganization',
        'name' => SITE_NAME,
        'url' => SITE_URL,
        'logo' => SITE_URL . 'assets/images/ictech-logo-transparent.png',
        'description' => $seoDescription,
        'telephone' => '+254 20 200 4000',
        'email' => 'info@ictechsolutions.co.ke',
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => 'Nairobi',
            'addressCountry' => 'KE'
        ]
    ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP); ?>
    </script>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css?v=20260902">
</head>
<body>
    <header class="site-header">
        <div class="header-topbar">
            <div class="container header-topbar-inner">
                <div class="header-contact">
                    <span><i class="fas fa-phone"></i> +254 712 345 678</span>
                    <span><i class="fas fa-envelope"></i> info@ictechsolutions.co.ke</span>
                </div>
                <div class="header-socials" aria-label="Social media links">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
                    <a href="<?php echo SITE_URL; ?>contact.php" aria-label="Search"><i class="fas fa-search"></i></a>
                </div>
            </div>
        </div>
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand fw-bold" href="<?php echo SITE_URL; ?>">
                    <img src="<?php echo SITE_URL; ?>assets/images/ictech-logo-transparent.png" alt="ICTECH Solutions Limited" class="site-logo">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Toggle navigation">
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
                                <li><a class="dropdown-item" href="<?php echo SITE_URL . ($currentUser['role'] === 'admin' ? 'admin/index.php' : ($currentUser['role'] === 'trainer' ? 'trainer/dashboard.php' : 'student/dashboard.php')); ?>">Dashboard</a></li>
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
    </header>
