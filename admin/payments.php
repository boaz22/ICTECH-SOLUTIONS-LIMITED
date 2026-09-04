<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

Auth::requireAdmin();
$db = Database::getInstance();
$filter = getParam('status', 'all');

$sql = 'SELECT p.*, u.name AS student_name, c.title AS course_title FROM payments p JOIN users u ON u.id = p.user_id LEFT JOIN enrollments e ON e.id = p.enrollment_id LEFT JOIN courses c ON c.id = e.course_id WHERE 1 = 1';
$params = [];
if ($filter !== 'all') {
    $sql .= ' AND p.status = ?';
    $params[] = $filter;
}
$sql .= ' ORDER BY p.created_at DESC';
$payments = $db->getAll($sql, $params);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Management | ICTECH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-secondary mb-1">ADMIN CONSOLE</p>
            <h1 class="mb-0">Payment Management</h1>
        </div>
        <a href="index.php" class="btn btn-outline-primary">Back to dashboard</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All payments</option>
                        <option value="pending" <?php echo $filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="paid" <?php echo $filter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                        <option value="failed" <?php echo $filter === 'failed' ? 'selected' : ''; ?>>Failed</option>
                        <option value="cancelled" <?php echo $filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-8 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="payments.php" class="btn btn-outline-secondary">Reset</a>
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
                            <th>Reference</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?php echo h($payment['student_name']); ?></td>
                            <td><?php echo h($payment['course_title'] ?? '—'); ?></td>
                            <td><code><?php echo h($payment['reference']); ?></code></td>
                            <td><?php echo formatCurrency((float) $payment['amount']); ?></td>
                            <td><?php echo h(strtoupper($payment['method'])); ?></td>
                            <td><span class="badge text-bg-<?php echo $payment['status'] === 'paid' ? 'success' : ($payment['status'] === 'pending' ? 'warning' : 'danger'); ?>"><?php echo h($payment['status']); ?></span></td>
                            <td><?php echo h(date('M d, Y', strtotime($payment['created_at']))); ?></td>
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
