<?php
/**
 * ICTECH Solutions - Student Dashboard
 */

$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/student-header.php';

$userId = Auth::getCurrentUserId();
$db = Database::getInstance();

// Get dashboard statistics
$totalEnrollments = $db->count('enrollments', 'user_id = ?', [$userId]);
$activeEnrollments = $db->count('enrollments', "user_id = ? AND status IN ('active', 'pending')", [$userId]);
$completedEnrollments = $db->count('enrollments', "user_id = ? AND status = 'completed'", [$userId]);
$certificatesEarned = (int) $db->getValue(
    "SELECT COUNT(*) FROM certificates cert
     JOIN enrollments e ON e.id = cert.enrollment_id
     WHERE e.user_id = ?",
    [$userId]
);

// Get recent enrollments
$recentEnrollments = $db->getAll(
    "SELECT e.*, c.title as course_title, c.image, c.duration
     FROM enrollments e
     JOIN courses c ON e.course_id = c.id
     WHERE e.user_id = ?
     ORDER BY e.enrolled_at DESC
     LIMIT 5",
    [$userId]
);

// Get user profile
$userProfile = getUserProfile($userId);
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-1">
                <i class="fas fa-chart-line me-2"></i> Welcome, <?php echo h($currentUser['name']); ?>!
            </h1>
            <p class="text-muted">Here's your learning dashboard</p>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="quick-stat">
                <div class="quick-stat-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="quick-stat-content">
                    <h6>Active Courses</h6>
                    <p class="value"><?php echo $activeEnrollments; ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="quick-stat">
                <div class="quick-stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="quick-stat-content">
                    <h6>Completed</h6>
                    <p class="value"><?php echo $completedEnrollments; ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="quick-stat">
                <div class="quick-stat-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="quick-stat-content">
                    <h6>Total Enrollments</h6>
                    <p class="value"><?php echo $totalEnrollments; ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="quick-stat">
                <div class="quick-stat-icon">
                    <i class="fas fa-certificate"></i>
                </div>
                <div class="quick-stat-content">
                    <h6>Certificates Earned</h6>
                    <p class="value"><?php echo $certificatesEarned; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Enrollments -->
    <div class="row align-items-start">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i> My Courses</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($recentEnrollments)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Course</th>
                                        <th>Status</th>
                                        <th>Enrolled Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentEnrollments as $enrollment): ?>
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong><?php echo h($enrollment['course_title']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo h(formatCourseDurationHours($enrollment['duration'])); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <?php
                                                $status = $enrollment['status'];
                                                $statusClass = 'status-pending';
                                                $statusLabel = 'Pending';

                                                if ($status === 'active') {
                                                    $statusClass = 'status-active';
                                                    $statusLabel = 'Active';
                                                } elseif ($status === 'completed') {
                                                    $statusClass = 'status-completed';
                                                    $statusLabel = 'Completed';
                                                }
                                                ?>
                                                <span class="<?php echo $statusClass; ?>">
                                                    <?php echo $statusLabel; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small><?php echo formatDate($enrollment['enrolled_at'], 'M d, Y'); ?></small>
                                            </td>
                                            <td>
                                                <a href="course.php?enrollment=<?php echo (int) $enrollment['id']; ?>"
                                                   class="btn btn-sm btn-outline-primary">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state p-5">
                            <div class="empty-state-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <h4>No Courses Yet</h4>
                            <p class="text-muted">Your administrator will add agreed courses to your portal once enrollment is confirmed.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Profile Card -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i> Profile</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">Name</label>
                        <p class="text-dark fw-bold"><?php echo h($userProfile['name']); ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Email</label>
                        <p class="text-dark fw-bold"><?php echo h($userProfile['email']); ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Phone</label>
                        <p class="text-dark fw-bold"><?php echo h($userProfile['phone'] ?? 'Not set'); ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Member Since</label>
                        <p class="text-dark fw-bold"><?php echo formatDate($userProfile['created_at']); ?></p>
                    </div>
                    <a href="profile.php" class="btn btn-outline-primary w-100">
                        <i class="fas fa-edit me-2"></i> Edit Profile
                    </a>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i> Quick Actions</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="my-courses.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-book text-secondary me-2"></i> My Courses
                    </a>
                    <a href="profile.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-cog text-secondary me-2"></i> Account Settings
                    </a>
                    <a href="mailto:<?php echo h(MAIL_REPLY_TO); ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-life-ring text-secondary me-2"></i> Contact Support
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/student-footer.php'; ?>
