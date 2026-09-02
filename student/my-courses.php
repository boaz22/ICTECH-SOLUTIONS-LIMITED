<?php
/**
 * ICTECH Solutions - Student My Courses Page
 */

require_once __DIR__ . '/../includes/student-header.php';

$pageTitle = 'My Courses';
$userId = Auth::getCurrentUserId();
$db = Database::getInstance();

// Handle enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && postParam('action') === 'enroll') {
    $courseId = postParam('course_id', null, FILTER_VALIDATE_INT);
    
    if ($courseId) {
        $result = createEnrollment($userId, $courseId);
        if ($result['success']) {
            $enrollmentId = $result['enrollment_id'];
            // Redirect to payment
            header("Location: " . SITE_URL . "payment/initiate.php?enrollment_id=" . $enrollmentId);
            exit;
        }
    }
}

// Handle URL enrollment
if (getParam('action') === 'enroll') {
    $courseId = getParam('course_id', null, FILTER_VALIDATE_INT);
    if ($courseId) {
        $result = createEnrollment($userId, $courseId);
        if ($result['success']) {
            $enrollmentId = $result['enrollment_id'];
            header("Location: " . SITE_URL . "payment/initiate.php?enrollment_id=" . $enrollmentId);
            exit;
        }
    }
}

// Get all enrollments
$enrollments = getStudentEnrollments($userId);
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-1">
                <i class="fas fa-graduation-cap me-2"></i> My Courses
            </h1>
            <p class="text-muted">Manage your enrolled courses and track your progress</p>
        </div>
    </div>
    
    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" href="#active-courses" data-bs-toggle="tab">
                <i class="fas fa-play-circle me-2"></i> Active Courses
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#completed-courses" data-bs-toggle="tab">
                <i class="fas fa-check-circle me-2"></i> Completed
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#all-courses" data-bs-toggle="tab">
                <i class="fas fa-list me-2"></i> All Courses
            </a>
        </li>
    </ul>
    
    <!-- Tab Content -->
    <div class="tab-content">
        <!-- Active Courses -->
        <div class="tab-pane fade show active" id="active-courses">
            <div class="row">
                <?php 
                $activeCourses = array_filter($enrollments, fn($e) => $e['status'] === 'active');
                if (!empty($activeCourses)): 
                ?>
                    <?php foreach ($activeCourses as $course): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="portal-course-card">
                                <div style="height: 200px; background: linear-gradient(135deg, var(--primary) 0%, #003d99 100%); overflow: hidden;">
                                    <?php if ($course['image']): ?>
                                        <img src="<?php echo SITE_URL . 'uploads/' . h($course['image']); ?>" 
                                             alt="<?php echo h($course['course_title']); ?>" 
                                             style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                                            <i class="fas fa-book"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <strong><?php echo h($course['course_title']); ?></strong>
                                    </h5>
                                    <p class="text-muted mb-2">
                                        <small>
                                            <i class="fas fa-clock me-1"></i> <?php echo h($course['duration']); ?>
                                        </small>
                                    </p>
                                    <div class="course-progress mb-3">
                                        <div class="course-progress-bar" style="width: 45%;"></div>
                                    </div>
                                    <small class="text-muted">45% Complete</small>
                                    <div class="mt-3">
                                        <a href="<?php echo SITE_URL; ?>course-details.php?id=<?php echo $course['course_id']; ?>" 
                                           class="btn btn-primary btn-sm w-100">
                                            <i class="fas fa-arrow-right me-1"></i> Continue Learning
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="empty-state p-5">
                            <div class="empty-state-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <h4>No Active Courses</h4>
                            <p class="text-muted">You're not currently enrolled in any active courses.</p>
                            <a href="<?php echo SITE_URL; ?>courses.php" class="btn btn-primary mt-3">
                                <i class="fas fa-graduation-cap me-2"></i> Explore Courses
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Completed Courses -->
        <div class="tab-pane fade" id="completed-courses">
            <div class="row">
                <?php 
                $completedCourses = array_filter($enrollments, fn($e) => $e['status'] === 'completed');
                if (!empty($completedCourses)): 
                ?>
                    <?php foreach ($completedCourses as $course): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="portal-course-card">
                                <div style="height: 200px; background: linear-gradient(135deg, #4caf50 0%, #388e3c 100%); overflow: hidden;">
                                    <?php if ($course['image']): ?>
                                        <img src="<?php echo SITE_URL . 'uploads/' . h($course['image']); ?>" 
                                             alt="<?php echo h($course['course_title']); ?>" 
                                             style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <strong><?php echo h($course['course_title']); ?></strong>
                                    </h5>
                                    <p class="text-muted mb-2">
                                        <small>
                                            <i class="fas fa-clock me-1"></i> <?php echo h($course['duration']); ?>
                                        </small>
                                    </p>
                                    <div class="course-progress mb-3">
                                        <div class="course-progress-bar" style="width: 100%; background: #4caf50;"></div>
                                    </div>
                                    <small class="text-success">100% Complete</small>
                                    <div class="mt-3">
                                        <a href="#" class="btn btn-outline-primary btn-sm w-100">
                                            <i class="fas fa-certificate me-1"></i> Download Certificate
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="empty-state p-5">
                            <div class="empty-state-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h4>No Completed Courses</h4>
                            <p class="text-muted">You haven't completed any courses yet. Keep learning!</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- All Courses -->
        <div class="tab-pane fade" id="all-courses">
            <?php if (!empty($enrollments)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Course</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Enrolled Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($enrollments as $course): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo h($course['course_title']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo h($course['duration']); ?></small>
                                    </td>
                                    <td><?php echo formatCurrency($course['price']); ?></td>
                                    <td>
                                        <?php 
                                        $status = $course['status'];
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
                                    <td><?php echo formatDate($course['enrolled_at'], 'M d, Y'); ?></td>
                                    <td>
                                        <a href="<?php echo SITE_URL; ?>course-details.php?id=<?php echo $course['course_id']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/student-footer.php'; ?>
