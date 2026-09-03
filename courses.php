<?php
/**
 * ICTECH Solutions - Courses Listing Page
 */

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = 'Courses';

// Get filters
$categoryId = getParam('category', null, FILTER_VALIDATE_INT);
$search = getParam('search');
$page = getParam('page', 1, FILTER_VALIDATE_INT);
$page = max(1, $page);

// Pagination
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Get courses
$db = Database::getInstance();
$courses = getPublishedCourses($perPage, $offset, $categoryId, $search);
$totalCourses = $db->count('courses',
    $categoryId ? "status = 'published' AND category_id = $categoryId" : "status = 'published'");
$totalPages = ceil($totalCourses / $perPage);

// Get categories
$categories = getCategories();
?>

<!-- Header -->
<section class="bg-primary text-white py-5 courses-page-header">
    <div class="container">
        <h1>Our Professional Courses</h1>
        <p>Discover industry-relevant training programs designed for your success</p>
    </div>
</section>

<!-- Main Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4 courses-sidebar">
                <!-- Search -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Search Courses</h5>
                        <form method="GET" action="">
                            <input type="text" name="search" class="form-control mb-3"
                                   placeholder="Search courses..." value="<?php echo h($search ?? ''); ?>">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Categories Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Filter by Category</h5>
                        <div class="list-group">
                            <a href="courses.php" class="list-group-item list-group-item-action <?php echo !$categoryId ? 'active' : ''; ?>">
                                All Categories
                            </a>
                            <?php foreach ($categories as $cat): ?>
                                <a href="courses.php?category=<?php echo $cat['id']; ?>"
                                   class="list-group-item list-group-item-action <?php echo $categoryId === $cat['id'] ? 'active' : ''; ?>">
                                    <?php echo h($cat['name']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Filter Info -->
                <div class="card bg-light">
                    <div class="card-body">
                        <p class="mb-0">
                            <small>
                                <strong>Total Courses:</strong> <?php echo $totalCourses; ?>
                            </small>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Courses Grid -->
            <div class="col-lg-9">
                <?php if (!empty($courses)): ?>
                    <div class="row">
                        <?php foreach ($courses as $course): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="course-card">
                                    <div class="course-image">
                                        <?php if (courseImageUrl($course)): ?>
                                            <img src="<?php echo h(courseImageUrl($course)); ?>" alt="<?php echo h($course['title']); ?>">
                                        <?php else: ?>
                                            <i class="fas fa-book"></i>
                                        <?php endif; ?>
                                    </div>

                                    <div class="course-info">
                                        <div class="course-category"><?php echo h($course['category_name']); ?></div>
                                        <h5 class="course-title"><?php echo h($course['title']); ?></h5>
                                        <p class="course-description"><?php echo truncateText($course['description'], 80); ?></p>

                                        <div class="course-meta">
                                            <span class="course-duration">
                                                <i class="fas fa-clock"></i> <?php echo h($course['duration']); ?>
                                            </span>
                                            <span class="course-price"><?php echo formatCurrency($course['price']); ?></span>
                                        </div>

                                        <div class="course-footer">
                                            <a href="course-details.php?id=<?php echo $course['id']; ?>" class="btn btn-outline-primary btn-sm">
                                                Details
                                            </a>
                                            <?php if ($isLoggedIn && !isEnrolled($currentUser['id'], $course['id'])): ?>
                                                <a href="student/my-courses.php?action=enroll&course_id=<?php echo $course['id']; ?>" class="btn btn-secondary btn-sm">
                                                    Enroll
                                                </a>
                                            <?php elseif ($isLoggedIn && isEnrolled($currentUser['id'], $course['id'])): ?>
                                                <span class="badge bg-success w-100 text-center">Enrolled</span>
                                            <?php else: ?>
                                                <a href="register.php" class="btn btn-secondary btn-sm">
                                                    Enroll
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="courses.php?page=1<?php echo $categoryId ? '&category=' . $categoryId : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                            First
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="courses.php?page=<?php echo $page - 1; ?><?php echo $categoryId ? '&category=' . $categoryId : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                            Previous
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="courses.php?page=<?php echo $i; ?><?php echo $categoryId ? '&category=' . $categoryId : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="courses.php?page=<?php echo $page + 1; ?><?php echo $categoryId ? '&category=' . $categoryId : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                            Next
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="courses.php?page=<?php echo $totalPages; ?><?php echo $categoryId ? '&category=' . $categoryId : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                            Last
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No courses found. Try adjusting your filters.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="container">
        <h2>Ready to Get Started?</h2>
        <p>Choose a course and begin your learning journey today</p>
        <?php if (!$isLoggedIn): ?>
            <a href="register.php" class="btn btn-primary btn-lg">
                <i class="fas fa-user-plus"></i> Create Account & Enroll
            </a>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
