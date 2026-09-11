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
    if (!$errors && $enrollmentId && in_array($status, ['pending', 'active', 'completed', 'cancelled'], true)) {
        $db->update('enrollments', ['status' => $status], 'id = ?', [$enrollmentId]);
        // Ensure a certificate is issued whenever an enrollment is marked completed,
        // even when the admin sets status directly instead of going through the
        // student -> trainer -> admin approval chain.
        if ($status === 'completed') {
            createCertificateForEnrollment($enrollmentId);
        }
        header('Location: enrollments.php?updated=1');
        exit;
    }

    if (!$errors && $enrollmentId && postParam('action') === 'assign_trainer') {
        $trainerId = postParam('trainer_id', null, FILTER_VALIDATE_INT);
        if ($trainerId) {
            if (assignTrainer($enrollmentId, $trainerId)) {
                header('Location: enrollments.php?assigned=1');
                exit;
            }
            $errors[] = 'Unable to assign trainer. The enrollment must be active first.';
        } else {
            $errors[] = 'Please select a trainer to assign.';
        }
    }
}

$trainers = $db->getAll("SELECT id, name FROM users WHERE role = 'trainer' AND status = 'active' ORDER BY name ASC");

$statusFilter = getParam('status', 'all');
$sql = 'SELECT e.*, u.name AS student_name, c.title AS course_title, t.name AS trainer_name
        FROM enrollments e
        JOIN users u ON u.id = e.user_id
        JOIN courses c ON c.id = e.course_id
        LEFT JOIN users t ON t.id = e.trainer_id
        WHERE 1 = 1';
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
    <link rel="icon" type="image/png" href="../assets/images/favicon-32.png">
    <link rel="apple-touch-icon" href="../assets/images/apple-touch-icon.png">
    <title>Enrollment Management | ICTECH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=20260919">
</head>
<body class="admin-shell">
<div class="admin-app">
    <aside class="admin-sidebar">
        <div class="admin-sidebar-inner">
            <div class="sidebar-brand"><img src="../assets/images/ictech-logo-transparent.png" alt="ICTECH Solutions"></div>
            <div class="admin-user-box"><p class="name"><?php echo h(Auth::getCurrentUser()['name'] ?? 'Administrator'); ?></p><p class="email"><?php echo h(Auth::getCurrentUser()['email'] ?? ''); ?></p></div>
            <nav>
                <?php $page = basename($_SERVER['PHP_SELF']); ?>
                <a href="index.php" class="<?php echo $page === 'index.php' ? 'active' : ''; ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="users.php" class="<?php echo $page === 'users.php' ? 'active' : ''; ?>"><i class="fas fa-users"></i> Users</a>
                <a href="enrollments.php" class="<?php echo $page === 'enrollments.php' ? 'active' : ''; ?>"><i class="fas fa-clipboard-list"></i> Enrollments</a>
                <a href="courses.php" class="<?php echo $page === 'courses.php' ? 'active' : ''; ?>"><i class="fas fa-book-open"></i> Courses</a>
                <a href="categories.php" class="<?php echo $page === 'categories.php' ? 'active' : ''; ?>"><i class="fas fa-tags"></i> Categories</a>
                <a href="certificates.php" class="<?php echo $page === 'certificates.php' ? 'active' : ''; ?>"><i class="fas fa-certificate"></i> Certificates</a>
                <a href="payments.php" class="<?php echo $page === 'payments.php' ? 'active' : ''; ?>"><i class="fas fa-credit-card"></i> Payments</a>
                <a href="reports.php" class="<?php echo $page === 'reports.php' ? 'active' : ''; ?>"><i class="fas fa-chart-bar"></i> Reports</a>
                <a href="testimonials.php" class="<?php echo $page === 'testimonials.php' ? 'active' : ''; ?>"><i class="fas fa-comments"></i> Testimonials</a>
                <a href="partners.php" class="<?php echo $page === 'partners.php' ? 'active' : ''; ?>"><i class="fas fa-handshake"></i> Partners</a>
                <a href="contact-messages.php" class="<?php echo $page === 'contact-messages.php' ? 'active' : ''; ?>"><i class="fas fa-envelope"></i> Messages</a>
                <a href="settings.php" class="<?php echo $page === 'settings.php' ? 'active' : ''; ?>"><i class="fas fa-sliders-h"></i> Settings</a>
            </nav>
            <div class="sidebar-footer"><form method="post" action="../logout.php"><input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>"><button type="submit" class="sidebar-logout"><i class="fas fa-sign-out-alt"></i> Logout</button></form></div>
        </div>
    </aside>
    <div class="admin-content">
        <header class="admin-topbar"><div class="admin-topbar-inner"><div class="brand-mark"><i class="fas fa-shield-alt"></i> Admin Console</div><div class="d-flex align-items-center gap-2"><div class="admin-user-chip"><i class="fas fa-user-circle"></i> <?php echo h(Auth::getCurrentUser()['name'] ?? 'Admin'); ?></div><form method="post" action="../logout.php" class="d-inline"><input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>"><button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-sign-out-alt"></i> Logout</button></form></div></div></header>
        <div class="admin-content-body">
            <main class="container-fluid px-0">
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

    <?php if (getParam('assigned')): ?>
        <div class="alert alert-success">Trainer assigned successfully.</div>
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
                        <th>Trainer</th>
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
                            <td>
                                <?php if ($enrollment['trainer_name']): ?>
                                    <?php echo h($enrollment['trainer_name']); ?>
                                <?php else: ?>
                                    <span class="text-muted">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo h(date('M d, Y', strtotime($enrollment['enrolled_at']))); ?></td>
                            <td>
                                <form method="post" class="d-flex gap-2 align-items-center mb-2">
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
                                <?php if ($enrollment['status'] === 'active'): ?>
                                    <form method="post" class="d-flex gap-2 align-items-center">
                                        <input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>">
                                        <input type="hidden" name="enrollment_id" value="<?php echo (int) $enrollment['id']; ?>">
                                        <input type="hidden" name="action" value="assign_trainer">
                                        <select name="trainer_id" class="form-select form-select-sm">
                                            <option value="">Select trainer...</option>
                                            <?php foreach ($trainers as $trainer): ?>
                                                <option value="<?php echo (int) $trainer['id']; ?>" <?php echo (int) $enrollment['trainer_id'] === (int) $trainer['id'] ? 'selected' : ''; ?>><?php echo h($trainer['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">Assign</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
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
