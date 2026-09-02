<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
Auth::requireStudent();
$certificateId = getParam('id', null, FILTER_VALIDATE_INT);
$db = Database::getInstance();
$certificate = $db->getRow("SELECT cert.*, e.user_id, e.admin_approved_at, c.title, u.name FROM certificates cert JOIN enrollments e ON e.id=cert.enrollment_id JOIN courses c ON c.id=e.course_id JOIN users u ON u.id=e.user_id WHERE cert.enrollment_id=? AND e.user_id=? AND e.status='completed'", [$certificateId, Auth::getCurrentUserId()]);
if (!$certificate) { http_response_code(404); exit('Certificate is not available until all approvals are complete.'); }
$download = getParam('download', '0') === '1';
if ($download) header('Content-Disposition: attachment; filename="' . $certificate['certificate_number'] . '.html"');
?><!doctype html><html lang="en"><head><meta charset="utf-8"><title><?php echo h($certificate['certificate_number']); ?></title><style>body{font-family:Georgia,serif;text-align:center;padding:70px;color:#001a4d}.certificate{border:12px solid #001a4d;padding:55px;max-width:800px;margin:auto}.accent{color:#ff9800}h1{font-size:42px}h2{font-size:28px}</style></head><body><main class="certificate"><p class="accent">ICTECH SOLUTIONS LIMITED</p><h1>Certificate of Completion</h1><p>This certificate is proudly presented to</p><h2><?php echo h($certificate['name']); ?></h2><p>for successfully completing</p><h2 class="accent"><?php echo h($certificate['title']); ?></h2><p>Certificate No: <?php echo h($certificate['certificate_number']); ?><br>Issued: <?php echo h($certificate['issued_at']); ?></p></main></body></html>
