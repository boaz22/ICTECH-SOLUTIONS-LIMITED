<?php
/**
 * ICTECH Solutions - Student Payments Page
 */

require_once __DIR__ . '/../includes/student-header.php';

$pageTitle = 'Payment History';
$userId = Auth::getCurrentUserId();
$db = Database::getInstance();

// Get payment history
$payments = $db->getAll(
    "SELECT p.*, c.title as course_title, e.user_id
     FROM payments p
     LEFT JOIN enrollments e ON p.enrollment_id = e.id
     LEFT JOIN courses c ON e.course_id = c.id
     WHERE p.user_id = ?
     ORDER BY p.created_at DESC",
    [$userId]
);

// Get statistics
$totalPaid = $db->getValue(
    "SELECT SUM(amount) FROM payments WHERE user_id = ? AND status = 'paid'",
    [$userId]
) ?? 0;

$pendingPayments = $db->count('payments', "user_id = ? AND status = 'pending'", [$userId]);
$failedPayments = $db->count('payments', "user_id = ? AND status = 'failed'", [$userId]);
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-1">
                <i class="fas fa-credit-card me-2"></i> Payment History
            </h1>
            <p class="text-muted">View and manage your payments</p>
        </div>
    </div>

    <?php if (getParam('success')): ?>
        <div class="alert alert-success mb-4">
            Payment request created successfully. Reference: <code><?php echo h(getParam('reference', '')); ?></code>
        </div>
    <?php endif; ?>
    
    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="quick-stat">
                <div class="quick-stat-icon">
                    <i class="fas fa-check-circle text-success"></i>
                </div>
                <div class="quick-stat-content">
                    <h6>Total Paid</h6>
                    <p class="value"><?php echo formatCurrency($totalPaid); ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="quick-stat">
                <div class="quick-stat-icon">
                    <i class="fas fa-clock text-warning"></i>
                </div>
                <div class="quick-stat-content">
                    <h6>Pending</h6>
                    <p class="value"><?php echo $pendingPayments; ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="quick-stat">
                <div class="quick-stat-icon">
                    <i class="fas fa-times-circle text-danger"></i>
                </div>
                <div class="quick-stat-content">
                    <h6>Failed</h6>
                    <p class="value"><?php echo $failedPayments; ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="quick-stat">
                <div class="quick-stat-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="quick-stat-content">
                    <h6>Transactions</h6>
                    <p class="value"><?php echo count($payments); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Payment History Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i> Transaction History</h5>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($payments)): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Reference</th>
                                <th>Course</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td>
                                        <code><?php echo h($payment['reference'] ?? 'N/A'); ?></code>
                                    </td>
                                    <td>
                                        <small><?php echo h($payment['course_title'] ?? 'Course'); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo formatCurrency($payment['amount']); ?></strong>
                                    </td>
                                    <td>
                                        <small>
                                            <?php if ($payment['method'] === 'mpesa'): ?>
                                                <i class="fas fa-mobile-alt"></i> M-Pesa
                                            <?php elseif ($payment['method'] === 'card'): ?>
                                                <i class="fas fa-credit-card"></i> Card
                                            <?php else: ?>
                                                <i class="fas fa-university"></i> Bank Transfer
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php 
                                        $status = $payment['status'];
                                        $statusClass = 'status-pending';
                                        $statusLabel = 'Pending';
                                        
                                        if ($status === 'paid') {
                                            $statusClass = 'status-active';
                                            $statusLabel = 'Paid';
                                        } elseif ($status === 'failed') {
                                            $statusClass = 'bg-danger text-white px-2 py-1 rounded';
                                            $statusLabel = 'Failed';
                                        }
                                        ?>
                                        <span class="<?php echo $statusClass; ?>" style="<?php echo $status === 'failed' ? 'background: #f44336 !important; color: white !important; padding: 0.5rem 1rem !important; border-radius: 20px; font-size: 0.85rem; font-weight: 600;' : ''; ?>">
                                            <?php echo $statusLabel; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small><?php echo formatDate($payment['created_at'], 'M d, Y'); ?></small>
                                    </td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
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
                        <i class="fas fa-receipt"></i>
                    </div>
                    <h4>No Payments Yet</h4>
                    <p class="text-muted">You haven't made any payments yet.</p>
                    <a href="<?php echo SITE_URL; ?>courses.php" class="btn btn-primary mt-3">
                        <i class="fas fa-graduation-cap me-2"></i> Browse Courses
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/student-footer.php'; ?>
