<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

Auth::requireTrainer();
$db = Database::getInstance();
$trainerId = Auth::getCurrentUserId();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::verifyCSRFToken(postParam('csrf_token'))) {
    $enrollmentId = postParam('enrollment_id', null, FILTER_VALIDATE_INT);
    $action = postParam('action');

    if ($action === 'progress' && $enrollmentId) {
        $studentId = (int) $db->getValue('SELECT user_id FROM enrollments WHERE id = ? AND trainer_id = ?', [$enrollmentId, $trainerId]);
        if ($studentId) {
            updateEnrollmentProgress($enrollmentId, $studentId, postParam('progress', 0, FILTER_VALIDATE_INT));
        }
    }

    if ($action === 'approve' && $enrollmentId) {
        approveTrainerCompletion($enrollmentId, $trainerId);
    }
}

$stats = [
    'total' => $db->getValue('SELECT COUNT(*) FROM enrollments WHERE trainer_id = ?', [$trainerId]),
    'active' => $db->getValue("SELECT COUNT(*) FROM enrollments WHERE trainer_id = ? AND status = 'active'", [$trainerId]),
    'pending' => $db->getValue("SELECT COUNT(*) FROM enrollments WHERE trainer_id = ? AND status IN ('pending', 'active') AND progress < 100", [$trainerId]),
    'completed' => $db->getValue("SELECT COUNT(*) FROM enrollments WHERE trainer_id = ? AND status = 'completed'", [$trainerId])
];

$assignments = $db->getAll(
    "SELECT e.*, c.title AS course_title, u.name AS student_name, u.email AS student_email
     FROM enrollments e
     JOIN courses c ON c.id = e.course_id
     JOIN users u ON u.id = e.user_id
     WHERE e.trainer_id = ?
     ORDER BY e.updated_at DESC",
    [$trainerId]
);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Trainer Dashboard | ICTECH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=20260910b">
</head>
<body class="admin-shell">
<div class="admin-app">
    <aside class="admin-sidebar">
        <div class="admin-sidebar-inner">
            <div class="sidebar-brand"><img src="../assets/images/ictech-logo-transparent.png" alt="ICTECH Solutions"></div>
            <div class="admin-user-box">
                <p class="name"><?php echo h(Auth::getCurrentUser()['name'] ?? 'Trainer'); ?></p>
                <p class="email"><?php echo h(Auth::getCurrentUser()['email'] ?? ''); ?></p>
            </div>
            <nav>
                <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            </nav>
            <div class="sidebar-footer"><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
        </div>
    </aside>
    <div class="admin-content">
        <header class="admin-topbar">
            <div class="admin-topbar-inner">
                <div class="brand-mark"><i class="fas fa-chalkboard-teacher"></i> Trainer Portal</div>
                <div class="d-flex align-items-center gap-2">
                    <div class="admin-user-chip"><i class="fas fa-user-circle"></i> <?php echo h(Auth::getCurrentUser()['name'] ?? 'Trainer'); ?></div>
                    <a href="../logout.php" class="btn btn-sm btn-outline-primary"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </header>
        <div class="admin-content-body">
            <main class="container-fluid px-0">
                <div class="admin-page-header d-flex justify-content-between align-items-center gap-3 flex-wrap">
                    <div>
                        <p class="eyebrow mb-2">TRAINER PORTAL</p>
                        <h1>Course Delivery Dashboard</h1>
                        <p>Track learner progress and approve completed courses.</p>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3"><div class="card admin-stat-card h-100"><div class="card-body"><i class="fas fa-users text-primary mb-2"></i><div class="text-muted small">Assigned learners</div><div class="h3 mb-0"><?php echo (int) $stats['total']; ?></div></div></div></div>
                    <div class="col-6 col-md-3"><div class="card admin-stat-card h-100"><div class="card-body"><i class="fas fa-book-open text-success mb-2"></i><div class="text-muted small">Active programs</div><div class="h3 mb-0"><?php echo (int) $stats['active']; ?></div></div></div></div>
                    <div class="col-6 col-md-3"><div class="card admin-stat-card h-100"><div class="card-body"><i class="fas fa-hourglass-half text-warning mb-2"></i><div class="text-muted small">Awaiting review</div><div class="h3 mb-0"><?php echo (int) $stats['pending']; ?></div></div></div></div>
                    <div class="col-6 col-md-3"><div class="card admin-stat-card h-100"><div class="card-body"><i class="fas fa-certificate text-secondary mb-2"></i><div class="text-muted small">Completed</div><div class="h3 mb-0"><?php echo (int) $stats['completed']; ?></div></div></div></div>
                </div>

                <section class="card admin-panel-card">
                    <div class="card-body">
                        <h2 class="h4 mb-3">Assigned learners</h2>
                        <div class="table-responsive">
                            <table class="table align-middle admin-table">
                                <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Course</th>
                                    <th>Progress</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($assignments as $item): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo h($item['student_name']); ?></strong><br>
                                            <small class="text-muted"><?php echo h($item['student_email']); ?></small>
                                        </td>
                                        <td><?php echo h($item['course_title']); ?></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="text-muted small"><?php echo (int) $item['progress']; ?>%</span>
                                                <div class="progress flex-grow-1" style="height: 8px;">
                                                    <div class="progress-bar" role="progressbar" style="width: <?php echo (int) $item['progress']; ?>%;"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge text-bg-<?php echo $item['status'] === 'completed' ? 'success' : ($item['status'] === 'active' ? 'primary' : 'warning'); ?>"><?php echo h($item['status']); ?></span>
                                        </td>
                                        <td>
                                            <form method="post" class="d-flex flex-wrap gap-2 align-items-center">
                                                <input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>">
                                                <input type="hidden" name="enrollment_id" value="<?php echo (int) $item['id']; ?>">
                                                <input class="form-control form-control-sm" type="number" min="0" max="100" name="progress" value="<?php echo (int) $item['progress']; ?>" style="max-width: 90px;">
                                                <button class="btn btn-sm btn-outline-primary" name="action" value="progress">Save</button>
                                                <?php if ((int) $item['progress'] === 100 && $item['student_completed_at'] && !$item['trainer_approved_at']): ?>
                                                    <button class="btn btn-sm btn-success" name="action" value="approve">Approve</button>
                                                <?php endif; ?>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (!$assignments): ?>
                                    <tr><td colspan="5" class="text-center text-muted py-4">No assigned learners yet.</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>
</div>
</body>
</html>
