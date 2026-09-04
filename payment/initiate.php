<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/mpesa.php';

Auth::requireLogin();

$db = Database::getInstance();
$userId = Auth::getCurrentUserId();
$courseId = getParam('course_id', null, FILTER_VALIDATE_INT);
if (!$courseId) {
    header('Location: ' . SITE_URL . 'courses.php');
    exit;
}

$course = getCourse($courseId);
if (!$course) {
    header('Location: ' . SITE_URL . 'courses.php');
    exit;
}

$userProfile = getUserProfile($userId);
$phone = trim((string) ($userProfile['phone'] ?? ''));
if ($phone === '') {
    $phone = trim((string) postParam('phone', ''));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && postParam('action') === 'initiate_payment') {
    if (!Auth::verifyCSRFToken(postParam('csrf_token'))) {
        $error = 'Security validation failed.';
    } else {
        $phone = trim((string) postParam('phone', $phone));
        $result = createEnrollment($userId, $courseId);
        if (!$result['success']) {
            $error = $result['error'];
        } else {
            $enrollmentId = $result['enrollment_id'];
            $response = MpesaGateway::initiateSTKPush($userId, $enrollmentId, $course['price'], $phone, 'ICTECH-' . $courseId);
            if ($response['success']) {
                $paymentReference = $response['reference'];
                header('Location: ' . SITE_URL . 'student/payments.php?success=1&reference=' . urlencode($paymentReference));
                exit;
            }
            $error = $response['error'];
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pay with M-Pesa | ICTECH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <p class="text-secondary mb-1">SECURE PAYMENT</p>
                    <h1 class="h3 mb-3">Complete your course payment</h1>
                    <p class="text-muted">Course: <strong><?php echo h($course['title']); ?></strong></p>
                    <p class="display-6 mb-4"><?php echo formatCurrency((float) $course['price']); ?></p>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo h($error); ?></div>
                    <?php endif; ?>

                    <form method="post">
                        <input type="hidden" name="action" value="initiate_payment">
                        <input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>">
                        <div class="mb-3">
                            <label class="form-label">Phone number</label>
                            <input type="tel" class="form-control" name="phone" value="<?php echo h($phone); ?>" placeholder="e.g. 0712345678" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Pay with M-Pesa</button>
                        <a href="<?php echo SITE_URL; ?>student/my-courses.php" class="btn btn-link w-100">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
