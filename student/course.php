<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

Auth::requireStudent();

$enrollmentId = getParam('enrollment', null, FILTER_VALIDATE_INT);

if (!$enrollmentId) {
    header('Location: my-courses.php');
    exit;
}

$db = Database::getInstance();
$enrollment = $db->getRow(
    "SELECT e.*, c.title AS course_title, c.description, c.course_outline, c.objectives, c.requirements,
            c.image, c.duration, cat.name AS category_name, u.name AS trainer_name
     FROM enrollments e
     JOIN courses c ON c.id = e.course_id
     LEFT JOIN categories cat ON cat.id = c.category_id
     LEFT JOIN users u ON u.id = e.trainer_id
     WHERE e.id = ? AND e.user_id = ?",
    [$enrollmentId, Auth::getCurrentUserId()]
);

if (!$enrollment) {
    header('Location: my-courses.php');
    exit;
}

$pageTitle = $enrollment['course_title'];
require_once __DIR__ . '/../includes/student-header.php';

$statusLabels = [
    'pending' => 'Pending',
    'active' => 'Active',
    'completed' => 'Completed',
    'cancelled' => 'Cancelled',
];
$statusClasses = [
    'pending' => 'status-pending',
    'active' => 'status-active',
    'completed' => 'status-completed',
    'cancelled' => 'status-cancelled',
];
$outlineItems = array_filter(array_map('trim', explode("\n", (string) $enrollment['course_outline'])));
$objectiveItems = array_filter(array_map('trim', explode("\n", (string) $enrollment['objectives'])));
$requirementItems = array_filter(array_map('trim', explode("\n", (string) $enrollment['requirements'])));
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h1 class="mb-1">
                <i class="fas fa-book-open me-2"></i><?php echo h($enrollment['course_title']); ?>
            </h1>
            <p class="text-muted mb-0">Review your course content, current progress, and completion updates.</p>
        </div>
        <a href="my-courses.php" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>Back to My Courses
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <section class="student-course-hero mb-4">
                <div class="student-course-hero-image">
                    <?php if (courseImageUrl($enrollment)): ?>
                        <img src="<?php echo h(courseImageUrl($enrollment)); ?>" alt="<?php echo h($enrollment['course_title']); ?>">
                    <?php else: ?>
                        <i class="fas fa-graduation-cap fa-4x"></i>
                    <?php endif; ?>
                </div>
                <div class="student-course-hero-body">
                    <span class="badge text-bg-primary"><?php echo h($statusLabels[$enrollment['status']] ?? ucfirst($enrollment['status'])); ?></span>
                    <div class="student-course-meta">
                        <div class="student-course-meta-item">
                            <i class="fas fa-clock"></i>
                            <span><?php echo h($enrollment['duration']); ?></span>
                        </div>
                        <div class="student-course-meta-item">
                            <i class="fas fa-folder-open"></i>
                            <span><?php echo h($enrollment['category_name']); ?></span>
                        </div>
                        <div class="student-course-meta-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Enrolled <?php echo formatDate($enrollment['enrolled_at'], 'M d, Y'); ?></span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="student-detail-card">
                <h2 class="h4"><i class="fas fa-book me-2 text-primary"></i>Course Overview</h2>
                <p class="mb-0"><?php echo nl2br(h($enrollment['description'])); ?></p>
            </section>

            <?php if (!empty($objectiveItems)): ?>
                <section class="student-detail-card">
                    <h2 class="h4"><i class="fas fa-check-circle me-2 text-primary"></i>Learning Objectives</h2>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($objectiveItems as $objective): ?>
                            <li class="list-group-item px-0">
                                <i class="fas fa-check text-success me-2"></i><?php echo h($objective); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>

            <?php if (!empty($outlineItems) || !empty($enrollment['course_outline'])): ?>
                <section class="student-detail-card">
                    <h2 class="h4"><i class="fas fa-list me-2 text-primary"></i>Course Outline</h2>
                    <?php if (!empty($outlineItems)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($outlineItems as $outlineItem): ?>
                                <li class="list-group-item px-0">
                                    <i class="fas fa-angle-right text-secondary me-2"></i><?php echo h($outlineItem); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="mb-0"><?php echo nl2br(h((string) $enrollment['course_outline'])); ?></p>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <?php if (!empty($requirementItems)): ?>
                <section class="student-detail-card">
                    <h2 class="h4"><i class="fas fa-exclamation-circle me-2 text-primary"></i>Prerequisites</h2>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($requirementItems as $requirement): ?>
                            <li class="list-group-item px-0">
                                <i class="fas fa-arrow-right text-secondary me-2"></i><?php echo h($requirement); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <section class="student-detail-card">
                <h2 class="h4"><i class="fas fa-chart-line me-2 text-primary"></i>Learning Progress</h2>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="<?php echo h($statusClasses[$enrollment['status']] ?? 'status-pending'); ?>">
                        <?php echo h($statusLabels[$enrollment['status']] ?? ucfirst($enrollment['status'])); ?>
                    </span>
                    <strong><?php echo (int) $enrollment['progress']; ?>%</strong>
                </div>

                <?php if (in_array($enrollment['status'], ['active', 'completed'], true)): ?>
                    <div class="course-progress mb-2">
                        <div class="course-progress-bar" style="width: <?php echo (int) $enrollment['progress']; ?>%;"></div>
                    </div>
                    <small class="text-muted d-block mb-3">Trainer updates your progress as you move through the course.</small>
                <?php endif; ?>

                <div class="border rounded-3 p-3 bg-light mb-3">
                    <div class="small text-muted mb-1">Assigned Trainer</div>
                    <div class="fw-semibold"><?php echo h($enrollment['trainer_name'] ?: 'Not assigned yet'); ?></div>
                </div>

                <?php if ($enrollment['status'] === 'pending'): ?>
                    <div class="alert alert-warning mb-0">Your enrollment is waiting for activation by ICTECH.</div>
                <?php elseif ($enrollment['status'] === 'active'): ?>
                    <?php if ($enrollment['student_completed_at'] && empty($enrollment['trainer_approved_at'])): ?>
                        <div class="alert alert-warning mb-0">Your work has reached 100% and is awaiting trainer approval.</div>
                    <?php elseif (!empty($enrollment['trainer_approved_at'])): ?>
                        <div class="alert alert-info mb-0">Trainer approval is complete. Final admin approval is pending.</div>
                    <?php else: ?>
                        <div class="alert alert-secondary mb-0">Keep following your trainer's guidance. Your progress is updated inside this portal.</div>
                    <?php endif; ?>
                <?php elseif ($enrollment['status'] === 'completed'): ?>
                    <div class="alert alert-success">This course has been fully completed and approved.</div>
                    <a href="certificate.php?id=<?php echo (int) $enrollment['id']; ?>" class="btn btn-primary w-100">
                        <i class="fas fa-certificate me-2"></i>View Certificate
                    </a>
                <?php else: ?>
                    <div class="alert alert-secondary mb-0">This enrollment has been cancelled. Contact ICTECH if you need clarification.</div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/student-footer.php'; ?>
