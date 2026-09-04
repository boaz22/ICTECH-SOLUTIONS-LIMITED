<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

Auth::requireAdmin();
$db = Database::getInstance();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCSRFToken(postParam('csrf_token'))) {
        $errors[] = 'Security validation failed.';
    }

    $enrollmentId = postParam('enrollment_id', null, FILTER_VALIDATE_INT);
    $status = postParam('status');
    if ($enrollmentId && in_array($status, ['pending', 'active', 'completed', 'cancelled'], true)) {
        $db->update('enrollments', ['status' => $status], 'id = ?', [$enrollmentId]);
        header('Location: enrollments.php?updated=1');
        exit;
    }
}

$statusFilter = getParam('status', 'all');
$sql = 'SELECT e.*, u.name AS student_name, c.title AS course_title FROM enrollments e JOIN users u ON u.id = e.user_id JOIN courses c ON c.id = e.course_id WHERE 1 = 1';
$params = [];
if ($statusFilter !== 'all') {
    $sql .= ' AND e.status = ?';
    $params[] = $statusFilter;
}
$sql .= ' ORDER BY e.enrolled_at DESC';
$enrollments = $db->getAll($sql, $params);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enrollment Management | ICTECH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=20260902">
</head>
<body class="admin-shell">
<main class="container py-5">
    <div class="admin-page-header d-flex justify-content-between align-items-center gap-3 flex-wrap">
        <div>
            <p class="eyebrow mb-2">ADMIN CONSOLE</p>
            <h1>Enrollment Management</h1>
        </div>
        <a href="index.php" class="btn btn-outline-primary">Back to dashboard</a>
    </div>

    <?php if (getParam('updated')): ?>
        <div class="alert alert-success">Enrollment status updated.</div>
    <?php endif; ?>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger"><?php echo h($error); ?></div>
    <?php endforeach; ?>

    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All statuses</option>
                        <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-8 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="enrollments.php" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <section class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                    <tr>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Enrolled</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($enrollments as $enrollment): ?>
                        <tr>
                            <td><?php echo h($enrollment['student_name']); ?></td>
                            <td><?php echo h($enrollment['course_title']); ?></td>
                            <td><span class="badge text-bg-<?php echo $enrollment['status'] === 'completed' ? 'success' : ($enrollment['status'] === 'active' ? 'primary' : 'warning'); ?>"><?php echo h($enrollment['status']); ?></span></td>
                            <td><?php echo (int) $enrollment['progress']; ?>%</td>
                            <td><?php echo h(date('M d, Y', strtotime($enrollment['enrolled_at']))); ?></td>
                            <td>
                                <form method="post" class="d-flex gap-2 align-items-center">
                                    <input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>">
                                    <input type="hidden" name="enrollment_id" value="<?php echo (int) $enrollment['id']; ?>">
                                    <select name="status" class="form-select form-select-sm">
                                        <option value="pending" <?php echo $enrollment['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="active" <?php echo $enrollment['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="completed" <?php echo $enrollment['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo $enrollment['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</main>
</body>
</html>
