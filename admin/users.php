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

    $userId = postParam('user_id', null, FILTER_VALIDATE_INT);
    $status = postParam('status');
    if ($userId && in_array($status, ['active', 'inactive'], true)) {
        $db->update('users', ['status' => $status], 'id = ?', [$userId]);
        header('Location: users.php?updated=1');
        exit;
    }
}

$search = trim((string) getParam('q', ''));
$sql = 'SELECT * FROM users WHERE 1 = 1';
$params = [];
if ($search !== '') {
    $sql .= ' AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)';
    $term = '%' . $search . '%';
    $params = [$term, $term, $term];
}
$sql .= ' ORDER BY created_at DESC';
$users = $db->getAll($sql, $params);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Management | ICTECH</title>
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
                <a href="payments.php" class="<?php echo $page === 'payments.php' ? 'active' : ''; ?>"><i class="fas fa-credit-card"></i> Payments</a>
                <a href="reports.php" class="<?php echo $page === 'reports.php' ? 'active' : ''; ?>"><i class="fas fa-chart-bar"></i> Reports</a>
                <a href="testimonials.php" class="<?php echo $page === 'testimonials.php' ? 'active' : ''; ?>"><i class="fas fa-comments"></i> Testimonials</a>
                <a href="partners.php" class="<?php echo $page === 'partners.php' ? 'active' : ''; ?>"><i class="fas fa-handshake"></i> Partners</a>
                <a href="contact-messages.php" class="<?php echo $page === 'contact-messages.php' ? 'active' : ''; ?>"><i class="fas fa-envelope"></i> Messages</a>
                <a href="settings.php" class="<?php echo $page === 'settings.php' ? 'active' : ''; ?>"><i class="fas fa-sliders-h"></i> Settings</a>
            </nav>
            <div class="sidebar-footer"><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
        </div>
    </aside>
    <div class="admin-content">
        <header class="admin-topbar"><div class="admin-topbar-inner"><div class="brand-mark"><i class="fas fa-shield-alt"></i> Admin Console</div><div class="admin-user-chip"><i class="fas fa-user-circle"></i> <?php echo h(Auth::getCurrentUser()['name'] ?? 'Admin'); ?></div></div></header>
        <div class="admin-content-body">
            <main class="container-fluid px-0">
                <div class="admin-page-header d-flex justify-content-between align-items-center gap-3 flex-wrap">
        <div>
            <p class="eyebrow mb-2">ADMIN CONSOLE</p>
            <h1>User Management</h1>
        </div>
        <a href="index.php" class="btn btn-outline-primary">Back to dashboard</a>
    </div>

    <?php if (getParam('updated')): ?>
        <div class="alert alert-success">User status updated.</div>
    <?php endif; ?>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger"><?php echo h($error); ?></div>
    <?php endforeach; ?>

    <div class="card admin-panel-card mb-4">
        <div class="card-body">
            <form method="get" class="row g-2 align-items-center">
                <div class="col-md-8">
                    <input type="text" class="form-control" name="q" value="<?php echo h($search); ?>" placeholder="Search by name, email, or phone">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="users.php" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <section class="card admin-panel-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle admin-table">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo h($user['name']); ?></td>
                            <td><?php echo h($user['email']); ?></td>
                            <td><?php echo h($user['phone'] ?? '—'); ?></td>
                            <td><span class="badge text-bg-primary"><?php echo h($user['role']); ?></span></td>
                            <td><span class="badge text-bg-<?php echo $user['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo h($user['status']); ?></span></td>
                            <td><?php echo h(date('M d, Y', strtotime($user['created_at']))); ?></td>
                            <td>
                                <form method="post" class="d-inline-block">
                                    <input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>">
                                    <input type="hidden" name="user_id" value="<?php echo (int) $user['id']; ?>">
                                    <input type="hidden" name="status" value="<?php echo $user['status'] === 'active' ? 'inactive' : 'active'; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-<?php echo $user['status'] === 'active' ? 'warning' : 'success'; ?>">
                                        <?php echo $user['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                    </button>
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
        </div>
    </div>
</div>
</body>
</html>
