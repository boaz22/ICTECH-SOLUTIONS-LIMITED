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
</head>
<body class="bg-light">
<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-secondary mb-1">TRAINER PORTAL</p>
            <h1 class="mb-0">Course Delivery Dashboard</h1>
        </div>
        <a href="../logout.php" class="btn btn-outline-primary">Logout</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Assigned learners</div><div class="h3 mb-0"><?php echo (int) $stats['total']; ?></div></div></div></div></div>
        <div class="col-md-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Active programs</div><div class="h3 mb-0"><?php echo (int) $stats['active']; ?></div></div></div></div></div>
        <div class="col-md-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Awaiting review</div><div class="h3 mb-0"><?php echo (int) $stats['pending']; ?></div></div></div></div></div>
        <div class="col-md-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Completed</div><div class="h3 mb-0"><?php echo (int) $stats['completed']; ?></div></div></div></div></div>
    </div>

    <section class="card">
        <div class="card-body">
            <h2 class="h4 mb-3">Assigned learners</h2>
            <div class="table-responsive">
                <table class="table align-middle">
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
</body>
</html>
