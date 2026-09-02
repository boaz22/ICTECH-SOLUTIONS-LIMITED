<?php
/**
 * ICTECH Solutions - Course Details Page
 */

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/helpers.php';

$courseId = getParam('id', null, FILTER_VALIDATE_INT);

if (!$courseId) {
    header("Location: courses.php");
    exit;
}

$db = Database::getInstance();
$course = getCourse($courseId);

if (!$course || $course['status'] !== 'published') {
    header("Location: courses.php");
    exit;
}

$pageTitle = h($course['title']);

// Get related courses
$relatedCourses = $db->getAll(
    "SELECT c.*, cat.name as category_name
     FROM courses c
     LEFT JOIN categories cat ON c.category_id = cat.id
     WHERE c.status = 'published' AND c.category_id = ? AND c.id != ?
     LIMIT 3",
    [$course['category_id'], $courseId]
);

// Check if user is already enrolled
$isEnrolledInCourse = $isLoggedIn ? isEnrolled($currentUser['id'], $courseId) : false;
?>

<!-- Breadcrumb -->
<nav class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="courses.php">Courses</a></li>
            <li class="breadcrumb-item"><a href="courses.php?category=<?php echo $course['category_id']; ?>"><?php echo h($course['category_name']); ?></a></li>
            <li class="breadcrumb-item active"><?php echo h($course['title']); ?></li>
        </ol>
    </div>
</nav>

<!-- Course Header -->
<section class="bg-primary text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="course-category mb-2" style="color: var(--secondary);">
                    <?php echo h($course['category_name']); ?>
                </div>
                <h1 style="color: white;"><?php echo h($course['title']); ?></h1>
                <p class="lead">Master professional skills with our comprehensive training program</p>
            </div>
            <div class="col-lg-4 text-end">
                <div class="bg-dark p-3 rounded" style="background: rgba(0,0,0,0.3) !important;">
                    <div class="text-warning" style="font-size: 2.5rem; font-weight: bold;">
                        <?php echo formatCurrency($course['price']); ?>
                    </div>
                    <small class="text-light">Investment in your future</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Course Details -->
            <div class="col-lg-8">
                <!-- Course Overview -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-book"></i> Course Overview</h5>
                    </div>
                    <div class="card-body">
                        <p><?php echo nl2br(h($course['description'])); ?></p>
                    </div>
                </div>

                <!-- Learning Objectives -->
                <?php if ($course['objectives']): ?>
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-check-circle"></i> Learning Objectives</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <?php
                                $objectives = array_filter(array_map('trim', explode("\n", $course['objectives'])));
                                foreach ($objectives as $objective):
                                ?>
                                    <li class="list-group-item">
                                        <i class="fas fa-check text-success me-2"></i> <?php echo h($objective); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Requirements -->
                <?php if ($course['requirements']): ?>
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-exclamation-circle"></i> Prerequisites</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <?php
                                $requirements = array_filter(array_map('trim', explode("\n", $course['requirements'])));
                                foreach ($requirements as $requirement):
                                ?>
                                    <li class="list-group-item">
                                        <i class="fas fa-arrow-right me-2 text-secondary"></i> <?php echo h($requirement); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Enrollment Card -->
                <div class="card sticky-top" style="top: 80px;">
                    <div class="card-body">
                        <!-- Course Meta -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-3">
                                <span><i class="fas fa-clock text-secondary"></i> Duration</span>
                                <strong><?php echo h($course['duration']); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span><i class="fas fa-folder text-secondary"></i> Category</span>
                                <strong><?php echo h($course['category_name']); ?></strong>
                            </div>
                        </div>

                        <hr>

                        <!-- Enrollment Button -->
                        <?php if ($isEnrolledInCourse): ?>
                            <div class="alert alert-success mb-3">
                                <i class="fas fa-check-circle"></i> You are enrolled in this course
                            </div>
                            <a href="student/my-courses.php" class="btn btn-primary w-100">
                                <i class="fas fa-graduation-cap"></i> Go to Dashboard
                            </a>
                        <?php else: ?>
                            <div class="mb-3">
                                <div class="mb-2">
                                    <strong class="d-block mb-2">Price: <?php echo formatCurrency($course['price']); ?></strong>
                                </div>
                            </div>

                            <?php if ($isLoggedIn): ?>
                                <form method="POST" action="student/my-courses.php">
                                    <input type="hidden" name="action" value="enroll">
                                    <input type="hidden" name="course_id" value="<?php echo $courseId; ?>">
                                    <button type="submit" class="btn btn-secondary w-100">
                                        <i class="fas fa-credit-card"></i> Proceed to Enrollment
                                    </button>
                                </form>
                                <p class="text-center mt-2"><small>Secure payment via M-Pesa</small></p>
                            <?php else: ?>
                                <a href="register.php" class="btn btn-secondary w-100 mb-2">
                                    <i class="fas fa-user-plus"></i> Create Account
                                </a>
                                <a href="login.php" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-sign-in-alt"></i> Login to Enroll
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Courses -->
<?php if (!empty($relatedCourses)): ?>
    <section class="bg-light py-5">
        <div class="container">
            <div class="section-header">
                <h3>Related Courses</h3>
            </div>

            <div class="row">
                <?php foreach ($relatedCourses as $related): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="course-card">
                            <div class="course-image">
                                <?php if ($related['image']): ?>
                                    <img src="<?php echo SITE_URL . 'assets/images/' . h($related['image']); ?>" alt="<?php echo h($related['title']); ?>">
                                <?php else: ?>
                                    <i class="fas fa-book"></i>
                                <?php endif; ?>
                            </div>

                            <div class="course-info">
                                <div class="course-category"><?php echo h($related['category_name']); ?></div>
                                <h5 class="course-title"><?php echo h($related['title']); ?></h5>
                                <p class="course-description"><?php echo truncateText($related['description'], 80); ?></p>

                                <div class="course-meta">
                                    <span class="course-duration"><?php echo h($related['duration']); ?></span>
                                    <span class="course-price"><?php echo formatCurrency($related['price']); ?></span>
                                </div>

                                <div class="course-footer">
                                    <a href="course-details.php?id=<?php echo $related['id']; ?>" class="btn btn-outline-primary btn-sm">
                                        View
                                    </a>
                                    <?php if ($isLoggedIn && !isEnrolled($currentUser['id'], $related['id'])): ?>
                                        <a href="student/my-courses.php?action=enroll&course_id=<?php echo $related['id']; ?>" class="btn btn-secondary btn-sm">
                                            Enroll
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
