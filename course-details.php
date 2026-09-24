<?php
/**
 * ICTECH Solutions - Course Details Page
 */

// Buffer output so redirects (invalid/unpublished course) still work.
ob_start();

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

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

$pageTitle = $course['title'];
$pageDescription = trim(preg_replace('/\s+/', ' ', strip_tags((string) $course['description'])));
$pageDescription = $pageDescription !== '' ? substr($pageDescription, 0, 155) : 'Explore this professional ICT training course from ICTECH Solutions Limited in Kenya.';
$socialImage = courseImageUrl($course) ?: SITE_URL . 'assets/images/hero-tech.jpg';
$courseSchema = [
    '@type' => 'Course',
    'name' => $course['title'],
    'description' => $pageDescription,
    'provider' => [
        '@type' => 'Organization',
        'name' => SITE_NAME,
        'sameAs' => SITE_URL
    ],
    'url' => SITE_URL . 'course-details.php?id=' . (int) $courseId
];

require_once __DIR__ . '/includes/header.php';

// If the logged-in student is enrolled in this course, show their
// enrollment status/progress instead of the public enquiry call-to-action.
$studentEnrollment = null;
if (Auth::isStudent()) {
    $studentEnrollment = $db->getRow(
        "SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?",
        [Auth::getCurrentUserId(), $courseId]
    );
}

// Get related courses
$relatedCourses = $db->getAll(
    "SELECT c.*, cat.name as category_name
     FROM courses c
     LEFT JOIN categories cat ON c.category_id = cat.id
     WHERE c.status = 'published' AND c.category_id = ? AND c.id != ?
     LIMIT 3",
    [$course['category_id'], $courseId]
);

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
                    <div class="text-light" style="font-size: 1rem; font-weight: 600;">
                        Course Enquiries Open
                    </div>
                    <small class="text-light">Contact us for intake dates and pricing guidance.</small>
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

                <!-- Course Outline -->
                <?php if (!empty($course['course_outline'])): ?>
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-list"></i> Full Course Outline</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $outlineItems = array_filter(array_map('trim', explode("\n", (string) $course['course_outline'])));
                            ?>
                            <?php if (!empty($outlineItems)): ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($outlineItems as $outlineItem): ?>
                                        <li class="list-group-item">
                                            <i class="fas fa-angle-right me-2 text-secondary"></i> <?php echo h($outlineItem); ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p><?php echo nl2br(h((string) $course['course_outline'])); ?></p>
                            <?php endif; ?>
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
                <?php if ($studentEnrollment): ?>
                    <!-- Learning Progress Card (enrolled students) -->
                    <div class="card" id="learning-progress">
                        <div class="card-body">
                            <h5 class="card-title mb-3"><i class="fas fa-chart-line text-primary"></i> Your Learning Progress</h5>

                            <?php
                            $statusLabels = ['pending' => 'Pending', 'active' => 'Active', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
                            $statusClasses = ['pending' => 'text-warning', 'active' => 'text-primary', 'completed' => 'text-success', 'cancelled' => 'text-muted'];
                            $enrollmentStatus = $studentEnrollment['status'];
                            ?>

                            <div class="d-flex justify-content-between mb-2">
                                <span><i class="fas fa-info-circle text-secondary"></i> Status</span>
                                <strong class="<?php echo h($statusClasses[$enrollmentStatus] ?? ''); ?>"><?php echo h($statusLabels[$enrollmentStatus] ?? ucfirst($enrollmentStatus)); ?></strong>
                            </div>

                            <?php if (in_array($enrollmentStatus, ['active', 'completed'], true)): ?>
                                <div class="course-progress mb-1">
                                    <div class="course-progress-bar" style="width: <?php echo (int) $studentEnrollment['progress']; ?>%;"></div>
                                </div>
                                <small class="text-muted d-block mb-3"><?php echo (int) $studentEnrollment['progress']; ?>% complete</small>
                            <?php endif; ?>

                            <hr>

                            <?php if ($enrollmentStatus === 'pending'): ?>
                                <p class="text-muted mb-0">Your enrollment is awaiting activation by ICTECH. You'll be notified once it's active.</p>
                            <?php elseif ($enrollmentStatus === 'active'): ?>
                                <?php if ($studentEnrollment['student_completed_at'] && empty($studentEnrollment['trainer_approved_at'])): ?>
                                    <p class="text-warning mb-0"><i class="fas fa-hourglass-half"></i> Awaiting trainer approval.</p>
                                <?php elseif ($studentEnrollment['trainer_approved_at']): ?>
                                    <p class="text-info mb-0"><i class="fas fa-hourglass-half"></i> Awaiting final admin approval.</p>
                                <?php else: ?>
                                    <p class="text-muted mb-0">Your trainer updates your progress as you advance through the course.</p>
                                <?php endif; ?>
                            <?php elseif ($enrollmentStatus === 'completed'): ?>
                                <p class="text-success mb-2"><i class="fas fa-certificate"></i> Course completed!</p>
                                <a href="<?php echo SITE_URL; ?>student/certificate.php?id=<?php echo (int) $studentEnrollment['id']; ?>" class="btn btn-primary w-100">
                                    <i class="fas fa-download"></i> View Certificate
                                </a>
                            <?php elseif ($enrollmentStatus === 'cancelled'): ?>
                                <p class="text-muted mb-0">This enrollment was cancelled. Contact ICTECH if you believe this is a mistake.</p>
                            <?php endif; ?>

                            <a href="<?php echo SITE_URL; ?>student/my-courses.php" class="btn btn-outline-primary w-100 mt-3">
                                <i class="fas fa-arrow-left"></i> Back to My Courses
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Enquiry Card -->
                    <div class="card">
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

                            <p class="text-muted">Interested in this course? Send us an enquiry and our team will assist you with availability and training options.</p>
                            <a href="course-enquiry.php?course_id=<?php echo (int) $courseId; ?>" class="btn btn-secondary w-100">
                                <i class="fas fa-envelope"></i> Enquire About This Course
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
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
                                <?php if (courseImageUrl($related)): ?>
                                    <img src="<?php echo h(courseImageUrl($related)); ?>" alt="<?php echo h($related['title']); ?>">
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
                                </div>

                                <div class="course-footer">
                                    <a href="course-details.php?id=<?php echo $related['id']; ?>" class="btn btn-outline-primary btn-sm">
                                        View
                                    </a>
                                    <a href="course-enquiry.php?course_id=<?php echo (int) $related['id']; ?>" class="btn btn-secondary btn-sm">
                                        Enquire
                                    </a>
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
