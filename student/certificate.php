<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/certificate-render.php';

Auth::requireStudent();

$certificateId = getParam('id', null, FILTER_VALIDATE_INT);
$db = Database::getInstance();
$certificate = $db->getRow(
    "SELECT cert.*, e.user_id, e.admin_approved_at, c.title, c.duration, cat.name AS category_name, u.name
     FROM certificates cert
     JOIN enrollments e ON e.id = cert.enrollment_id
     JOIN courses c ON c.id = e.course_id
     LEFT JOIN categories cat ON cat.id = c.category_id
     JOIN users u ON u.id = e.user_id
     WHERE cert.enrollment_id = ? AND e.user_id = ? AND e.status = 'completed'",
    [$certificateId, Auth::getCurrentUserId()]
);

if (!$certificate) {
    http_response_code(404);
    exit('Certificate is not available until all approvals are complete.');
}

if (getParam('download') === 'pdf') {
    streamCertificatePdf($certificate);
}

$pageTitle = $certificate['certificate_number'];
require_once __DIR__ . '/../includes/student-header.php';
?>

<div class="student-certificate-actions">
    <a href="my-courses.php" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back to My Courses
    </a>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Print
        </button>
        <a href="?id=<?php echo (int) $certificateId; ?>&download=pdf" class="btn btn-primary btn-sm">
            <i class="fas fa-file-pdf me-1"></i> Download PDF
        </a>
    </div>
</div>

<div class="certificate-page-wrapper">
    <?php echo renderCertificateHtml($certificate, false); ?>
</div>

<?php require_once __DIR__ . '/../includes/student-footer.php'; ?>
