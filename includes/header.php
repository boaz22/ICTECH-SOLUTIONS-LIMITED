<?php
/**
 * ICTECH Solutions - Public Header Template
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
$isHomePage = in_array($currentPage, ['', 'index.php'], true);
$seoTitle = isset($pageTitle) ? $pageTitle : SITE_NAME;
$seoDescription = $pageDescription ?? SITE_DESCRIPTION;
$canonicalUrl = $currentPage === 'index.php' ? SITE_URL : SITE_URL . $currentPage;
$socialImage = SITE_URL . 'assets/images/hero-tech.jpg';
$publicCourseCategories = getCategories();
$publicProgramGroups = getProgramGroupMenu();
$hasProgramGroupItems = false;
foreach ($publicProgramGroups as $group) {
    if (!empty($group['categories']) || !empty($group['courses'])) {
        $hasProgramGroupItems = true;
        break;
    }
}
$headerCourseSearch = trim((string) getParam('search', '', FILTER_UNSAFE_RAW));
$headerCourseSuggestions = getPublishedCourses(50);
$activeCategoryId = getParam('category', null, FILTER_VALIDATE_INT);
$isCoursesActive = $currentPage === 'courses.php' || $currentPage === 'course-details.php' || $currentPage === 'course-enquiry.php';
$isScheduleActive = $currentPage === 'schedule.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo h($seoDescription); ?>">
    <meta name="keywords" content="ICT training Kenya, technology courses, professional certification, corporate training, IT solutions">
    <meta name="robots" content="index, follow">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo SITE_URL; ?>assets/images/favicon-32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo SITE_URL; ?>assets/images/favicon-16.png">
    <link rel="apple-touch-icon" href="<?php echo SITE_URL; ?>assets/images/apple-touch-icon.png">
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
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css?v=20260924c">
</head>
<body>
    <header class="site-header">
        <div class="header-topbar">
            <div class="container header-topbar-inner">
                <div class="header-contact">
                    <span><i class="fas fa-phone"></i> +254 733 600 326</span>
                    <span><i class="fas fa-envelope"></i> info@ictechsolutions.co.ke</span>
                </div>
                <div class="header-socials">
                    <a href="<?php echo SITE_URL; ?>contact.php" aria-label="Contact ICTECH"><i class="fas fa-envelope"></i><span class="visually-hidden">Contact ICTECH</span></a>
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
                            <a class="nav-link <?php echo $isHomePage ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>"<?php echo $isHomePage ? ' aria-current="page"' : ''; ?>>Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'about.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>about.php"<?php echo $currentPage === 'about.php' ? ' aria-current="page"' : ''; ?>>About</a>
                        </li>
                        <li class="nav-item nav-course-dropdown <?php echo $isCoursesActive ? 'active' : ''; ?>">
                            <a class="nav-link nav-course-toggle <?php echo $isCoursesActive ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>courses.php" data-course-menu-toggle aria-expanded="false"<?php echo $isCoursesActive ? ' aria-current="page"' : ''; ?>>
                                Courses
                            </a>
                            <?php if ($hasProgramGroupItems): ?>
                            <div class="dropdown-menu nav-course-menu nav-course-mega">
                                <div class="nav-course-mega-header">
                                    <a class="dropdown-item fw-bold" href="<?php echo SITE_URL; ?>courses.php">All Courses</a>
                                </div>
                                <div class="nav-course-mega-columns">
                                    <?php foreach ($publicProgramGroups as $group): ?>
                                        <div class="nav-course-mega-col">
                                            <p class="nav-course-mega-title"><?php echo h($group['label']); ?></p>
                                            <?php if (empty($group['categories']) && empty($group['courses'])): ?>
                                                <p class="nav-course-mega-empty">Coming soon</p>
                                            <?php endif; ?>
                                            <?php foreach ($group['categories'] as $category): ?>
                                                <a class="dropdown-item" href="<?php echo SITE_URL; ?>courses.php?category=<?php echo (int) $category['id']; ?>"><?php echo h($category['name']); ?></a>
                                            <?php endforeach; ?>
                                            <?php foreach ($group['courses'] as $groupCourse): ?>
                                                <a class="dropdown-item" href="<?php echo SITE_URL; ?>course-details.php?id=<?php echo (int) $groupCourse['id']; ?>"><?php echo h($groupCourse['title']); ?></a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php else: ?>
                            <ul class="dropdown-menu nav-course-menu">
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>courses.php">All Courses</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <?php foreach ($publicCourseCategories as $category): ?>
                                    <li>
                                        <a class="dropdown-item <?php echo $currentPage === 'courses.php' && (int) $activeCategoryId === (int) $category['id'] ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>courses.php?category=<?php echo (int) $category['id']; ?>">
                                            <?php echo h($category['name']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $isScheduleActive ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>schedule.php"<?php echo $isScheduleActive ? ' aria-current="page"' : ''; ?>>Schedule</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'services.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>services.php"<?php echo $currentPage === 'services.php' ? ' aria-current="page"' : ''; ?>>Services</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'students.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>students.php"<?php echo $currentPage === 'students.php' ? ' aria-current="page"' : ''; ?>>Learner Support</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'resources.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>resources.php"<?php echo $currentPage === 'resources.php' ? ' aria-current="page"' : ''; ?>>Resources</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'contact.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>contact.php"<?php echo $currentPage === 'contact.php' ? ' aria-current="page"' : ''; ?>>Contact</a>
                        </li>

                        <li class="nav-item nav-search-item">
                            <form method="get" action="<?php echo SITE_URL; ?>courses.php" class="header-course-search" role="search" aria-label="Search courses"<?php echo $currentPage === 'courses.php' && $headerCourseSearch !== '' ? ' data-clear-search-on-load="true"' : ''; ?>>
                                <label class="visually-hidden" for="header-course-search">Search courses</label>
                                <i class="fas fa-search" aria-hidden="true"></i>
                                <input id="header-course-search" type="search" name="search" value="<?php echo h($headerCourseSearch); ?>" placeholder="Search courses..." maxlength="120" autocomplete="off" aria-controls="header-course-suggestion-list" aria-autocomplete="list">
                                <datalist id="header-course-suggestions">
                                    <?php foreach ($headerCourseSuggestions as $suggestion): ?>
                                        <option value="<?php echo h($suggestion['title']); ?>" label="<?php echo h($suggestion['course_code'] ?? ($suggestion['category_name'] ?? '')); ?>" data-course-url="<?php echo h(SITE_URL . 'course-details.php?id=' . (int) $suggestion['id']); ?>"></option>
                                    <?php endforeach; ?>
                                </datalist>
                                <div id="header-course-suggestion-list" class="header-course-suggestion-list" role="listbox" hidden></div>
                                <button type="submit">Search</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
