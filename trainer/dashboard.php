<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
Auth::requireTrainer();
$db = Database::getInstance();
$trainerId = Auth::getCurrentUserId();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::verifyCSRFToken(postParam('csrf_token'))) {
    $enrollmentId = postParam('enrollment_id', null, FILTER_VALIDATE_INT);
    if (postParam('action') === 'progress') updateEnrollmentProgress($enrollmentId, (int) $db->getValue('SELECT user_id FROM enrollments WHERE id = ? AND trainer_id = ?', [$enrollmentId, $trainerId]), postParam('progress', 0, FILTER_VALIDATE_INT));
    if (postParam('action') === 'approve') approveTrainerCompletion($enrollmentId, $trainerId);
}
$assignments = $db->getAll("SELECT e.*, c.title, u.name AS student_name FROM enrollments e JOIN courses c ON c.id=e.course_id JOIN users u ON u.id=e.user_id WHERE e.trainer_id = ? ORDER BY e.updated_at DESC", [$trainerId]);
?><!doctype html><html lang="en"><head><meta charset="utf-8"><title>Trainer Dashboard | ICTECH</title><link rel="stylesheet" href="../assets/css/style.css"></head><body><main class="container py-5"><div class="d-flex justify-content-between align-items-center mb-4"><h1>Trainer Dashboard</h1><a href="../logout.php" class="btn btn-outline-primary">Logout</a></div><p class="text-muted">Track assigned students and approve completion after they reach 100%.</p><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Student</th><th>Course</th><th>Progress</th><th>Action</th></tr></thead><tbody><?php foreach ($assignments as $item): ?><tr><td><?php echo h($item['student_name']); ?></td><td><?php echo h($item['title']); ?></td><td><?php echo (int) $item['progress']; ?>%</td><td><form method="post" class="d-flex gap-2"><input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>"><input type="hidden" name="enrollment_id" value="<?php echo (int) $item['id']; ?>"><input class="form-control form-control-sm" type="number" min="0" max="100" name="progress" value="<?php echo (int) $item['progress']; ?>"><button class="btn btn-sm btn-outline-primary" name="action" value="progress">Save</button><?php if ((int) $item['progress'] === 100 && $item['student_completed_at'] && !$item['trainer_approved_at']): ?><button class="btn btn-sm btn-success" name="action" value="approve">Approve</button><?php endif; ?></form></td></tr><?php endforeach; ?></tbody></table></div></main></body></html>
