<?php
/**
 * ICTECH Solutions - Student Account Information
 *
 * Student accounts are provisioned only by an administrator after a training
 * agreement has been reached.
 */

$pageTitle = 'Student Accounts';
$pageDescription = 'Learn how to arrange a student account and enrol in professional ICT training with ICTECH Solutions Limited.';
$pageRobots = 'noindex, follow';
require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-page-header">
    <div class="container">
        <div class="auth-page-header-content">
            <div class="section-subtitle">Student accounts</div>
            <h1>Accounts are created by ICTECH</h1>
            <p>After agreeing your course, schedule, and fees with ICTECH, an administrator will create your account and send your access details by email.</p>
        </div>
    </div>
</section>

<section class="py-5 auth-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card auth-card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h2 class="h4">Ready to begin?</h2>
                        <p class="text-muted mb-4">Browse our courses or contact ICTECH to arrange your enrollment. Student portal access details are sent after your account has been created.</p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="courses.php" class="btn btn-primary"><i class="fas fa-graduation-cap"></i> Browse Courses</a>
                            <a href="contact.php" class="btn btn-outline-primary"><i class="fas fa-envelope"></i> Contact ICTECH</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
