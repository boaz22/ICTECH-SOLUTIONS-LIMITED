<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

Auth::requireAdmin();
$db = Database::getInstance();

$period = getParam('period', 'all');
$fromDate = trim((string) getParam('from_date', ''));
$toDate = trim((string) getParam('to_date', ''));

$whereSql = '';
$params = [];

if ($period === 'last_30') {
    $whereSql = " WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) ";
} elseif ($period === 'last_90') {
    $whereSql = " WHERE created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY) ";
} elseif ($period === 'custom' && $fromDate !== '' && $toDate !== '') {
    $whereSql = " WHERE created_at >= ? AND created_at <= ? ";
    $params = [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'];
} elseif ($period === 'custom' && $fromDate !== '') {
    $whereSql = " WHERE created_at >= ? ";
    $params = [$fromDate . ' 00:00:00'];
} elseif ($period === 'custom' && $toDate !== '') {
    $whereSql = " WHERE created_at <= ? ";
    $params = [$toDate . ' 23:59:59'];
}

$stats = [
    'students' => $db->getValue("SELECT COUNT(*) FROM users WHERE role = 'student' AND status = 'active'"),
    'trainers' => $db->getValue("SELECT COUNT(*) FROM users WHERE role = 'trainer' AND status = 'active'"),
    'courses' => $db->getValue("SELECT COUNT(*) FROM courses WHERE status = 'published'"),
    'active_enrollments' => $db->getValue("SELECT COUNT(*) FROM enrollments WHERE status = 'active'"),
    'pending_enrollments' => $db->getValue("SELECT COUNT(*) FROM enrollments WHERE status = 'pending'"),
    'completed_enrollments' => $db->getValue("SELECT COUNT(*) FROM enrollments WHERE status = 'completed'"),
    'revenue' => (float) $db->getValue("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'paid'"),
    'certificates' => $db->getValue('SELECT COUNT(*) FROM certificates'),
];

$dateFilterSql = '';
$dateFilterParams = [];

if ($period === 'last_30') {
    $dateFilterSql = ' WHERE p.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
} elseif ($period === 'last_90') {
    $dateFilterSql = ' WHERE p.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)';
} elseif ($period === 'custom' && $fromDate !== '') {
    $dateFilterSql = ' WHERE p.created_at >= ?';
    $dateFilterParams[] = $fromDate . ' 00:00:00';
}

if ($period === 'custom' && $toDate !== '') {
    $dateFilterSql .= ($dateFilterSql === '' ? ' WHERE ' : ' AND ') . 'p.created_at <= ?';
    $dateFilterParams[] = $toDate . ' 23:59:59';
}

$courseReport = $db->getAll(
    "SELECT c.title, COUNT(e.id) AS enrollments, AVG(e.progress) AS avg_progress,
        SUM(CASE WHEN e.status = 'completed' THEN 1 ELSE 0 END) AS completed,
        COALESCE(SUM(p.amount), 0) AS revenue
     FROM courses c
     LEFT JOIN enrollments e ON e.course_id = c.id
     LEFT JOIN payments p ON p.enrollment_id = e.id AND p.status = 'paid'
     GROUP BY c.id, c.title
     ORDER BY enrollments DESC, revenue DESC
     LIMIT 10"
);

$recentPayments = $db->getAll(
    "SELECT p.id, p.amount, p.status, p.created_at, u.name AS student_name, c.title AS course_title
     FROM payments p
     JOIN users u ON u.id = p.user_id
     LEFT JOIN enrollments e ON e.id = p.enrollment_id
     LEFT JOIN courses c ON c.id = e.course_id
     " . ($dateFilterSql !== '' ? str_replace('p.', '', $dateFilterSql) : '') . "
     ORDER BY p.created_at DESC
     LIMIT 8",
    $dateFilterParams
);

if (getParam('export', '0') === '1') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="ictech-course-report.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Course', 'Enrollments', 'Average Progress', 'Completed', 'Revenue']);
    foreach ($courseReport as $row) {
        fputcsv($out, [
            $row['title'],
            (int) $row['enrollments'],
            round((float) ($row['avg_progress'] ?? 0), 1),
            (int) ($row['completed'] ?? 0),
            number_format((float) ($row['revenue'] ?? 0), 2, '.', '')
        ]);
    }
    fclose($out);
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Reports | ICTECH</title>
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
        <header class="admin-topbar"><div class="admin-topbar-inner"><div class="brand-mark"><i class="fas fa-shield-alt"></i> Admin Console</div><div class="d-flex align-items-center gap-2"><div class="admin-user-chip"><i class="fas fa-user-circle"></i> <?php echo h(Auth::getCurrentUser()['name'] ?? 'Admin'); ?></div><a href="../logout.php" class="btn btn-sm btn-outline-primary"><i class="fas fa-sign-out-alt"></i> Logout</a></div></div></header>
        <div class="admin-content-body">
            <main class="container-fluid px-0">
                <div class="admin-page-header d-flex justify-content-between align-items-center gap-3 flex-wrap">
                    <div>
                        <p class="eyebrow mb-2">OPERATIONS REPORTS</p>
                        <h1>Business Overview</h1>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="index.php" class="btn btn-outline-primary">Back to dashboard</a>
                        <a href="reports.php?export=1" class="btn btn-primary">Export CSV</a>
                    </div>
                </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Time range</label>
                    <select name="period" class="form-select">
                        <option value="all" <?php echo $period === 'all' ? 'selected' : ''; ?>>All time</option>
                        <option value="last_30" <?php echo $period === 'last_30' ? 'selected' : ''; ?>>Last 30 days</option>
                        <option value="last_90" <?php echo $period === 'last_90' ? 'selected' : ''; ?>>Last 90 days</option>
                        <option value="custom" <?php echo $period === 'custom' ? 'selected' : ''; ?>>Custom range</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">From</label>
                    <input type="date" name="from_date" value="<?php echo h($fromDate); ?>" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To</label>
                    <input type="date" name="to_date" value="<?php echo h($toDate); ?>" class="form-control">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">Apply</button>
                    <a href="reports.php" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <section class="row g-3 mb-4">
        <div class="col-md-3"><div class="card h-100"><div class="card-body"><div class="text-muted small mb-2">Active students</div><div class="h3 mb-0"><?php echo (int) $stats['students']; ?></div></div></div></div>
        <div class="col-md-3"><div class="card h-100"><div class="card-body"><div class="text-muted small mb-2">Active trainers</div><div class="h3 mb-0"><?php echo (int) $stats['trainers']; ?></div></div></div></div>
        <div class="col-md-3"><div class="card h-100"><div class="card-body"><div class="text-muted small mb-2">Published courses</div><div class="h3 mb-0"><?php echo (int) $stats['courses']; ?></div></div></div></div>
        <div class="col-md-3"><div class="card h-100"><div class="card-body"><div class="text-muted small mb-2">Certificates issued</div><div class="h3 mb-0"><?php echo (int) $stats['certificates']; ?></div></div></div></div>
        <div class="col-md-3"><div class="card h-100"><div class="card-body"><div class="text-muted small mb-2">Active enrollments</div><div class="h3 mb-0"><?php echo (int) $stats['active_enrollments']; ?></div></div></div></div>
        <div class="col-md-3"><div class="card h-100"><div class="card-body"><div class="text-muted small mb-2">Pending</div><div class="h3 mb-0"><?php echo (int) $stats['pending_enrollments']; ?></div></div></div></div>
        <div class="col-md-3"><div class="card h-100"><div class="card-body"><div class="text-muted small mb-2">Completed</div><div class="h3 mb-0"><?php echo (int) $stats['completed_enrollments']; ?></div></div></div></div>
        <div class="col-md-3"><div class="card h-100"><div class="card-body"><div class="text-muted small mb-2">Paid revenue</div><div class="h3 mb-0"><?php echo formatCurrency((float) $stats['revenue']); ?></div></div></div></div>
    </section>

    <div class="row g-4">
        <div class="col-lg-8">
            <section class="card h-100">
                <div class="card-body">
                    <h2 class="h4 mb-3">Top performing courses</h2>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                            <tr>
                                <th>Course</th>
                                <th>Enrollments</th>
                                <th>Avg. Progress</th>
                                <th>Completed</th>
                                <th>Revenue</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($courseReport as $row): ?>
                                <tr>
                                    <td><?php echo h($row['title']); ?></td>
                                    <td><?php echo (int) $row['enrollments']; ?></td>
                                    <td><?php echo round((float) ($row['avg_progress'] ?? 0), 1); ?>%</td>
                                    <td><?php echo (int) ($row['completed'] ?? 0); ?></td>
                                    <td><?php echo formatCurrency((float) ($row['revenue'] ?? 0)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($courseReport)): ?>
                                <tr><td colspan="5" class="text-center text-muted py-4">No course activity available for the selected range.</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>

        <div class="col-lg-4">
            <section class="card h-100">
                <div class="card-body">
                    <h2 class="h4 mb-3">Recent payments</h2>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentPayments as $payment): ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong><?php echo h($payment['student_name'] ?? 'Unknown student'); ?></strong>
                                    <span class="badge text-bg-<?php echo $payment['status'] === 'paid' ? 'success' : ($payment['status'] === 'pending' ? 'warning' : 'danger'); ?>"><?php echo h($payment['status']); ?></span>
                                </div>
                                <small class="text-muted d-block"><?php echo h($payment['course_title'] ?? 'General payment'); ?></small>
                                <small class="text-muted"><?php echo h(date('M d, Y', strtotime($payment['created_at']))); ?></small>
                                <div class="mt-1 fw-semibold"><?php echo formatCurrency((float) $payment['amount']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        </div>
    </div>
                            </main>
                        </div>
                    </div>
                </div>
            </body>
            </html>
