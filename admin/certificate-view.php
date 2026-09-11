<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/certificate-render.php';

Auth::requireAdmin();

$certificateId = getParam('id', null, FILTER_VALIDATE_INT);
$db = Database::getInstance();
$certificate = $db->getRow(
    "SELECT cert.*, e.user_id, e.admin_approved_at, c.title, c.duration, cat.name AS category_name, u.name
     FROM certificates cert
     JOIN enrollments e ON e.id = cert.enrollment_id
     JOIN courses c ON c.id = e.course_id
     LEFT JOIN categories cat ON cat.id = c.category_id
     JOIN users u ON u.id = e.user_id
     WHERE cert.enrollment_id = ? AND e.status = 'completed'",
    [$certificateId]
);

if (!$certificate) {
    http_response_code(404);
    exit('Certificate not found.');
}

if (getParam('download') === 'pdf') {
    streamCertificatePdf($certificate);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="../assets/images/favicon-32.png">
    <link rel="apple-touch-icon" href="../assets/images/apple-touch-icon.png">
    <title><?php echo h($certificate['certificate_number']); ?> - ICTECH Solutions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        <?php echo certificateStyles(); ?>
        .page-actions { max-width: 850px; margin: 0 auto 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        @media print {
            .page-actions { display: none; }
            body { background: #fff; padding: 0; }
            .cert-border { border-width: 8px; }
        }
    </style>
</head>
<body>
    <div class="page-actions">
        <a href="certificates.php" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Certificates
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

    <?php echo renderCertificateHtml($certificate, false); ?>
</body>
</html>
