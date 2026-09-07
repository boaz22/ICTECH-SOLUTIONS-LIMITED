<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/mpesa.php';

Auth::requireLogin();

header('Content-Type: application/json');

$paymentId = getParam('payment_id', null, FILTER_VALIDATE_INT);
if (!$paymentId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Payment ID is required.']);
    exit;
}

$payment = Database::getInstance()->getRow(
    'SELECT * FROM payments WHERE id = ? AND user_id = ? LIMIT 1',
    [$paymentId, Auth::getCurrentUserId()]
);

if (!$payment) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Payment not found.']);
    exit;
}

$status = $payment['status'];
if ($status === 'pending') {
    $queriedStatus = MpesaGateway::queryPaymentStatus($payment);
    if ($queriedStatus === 'paid') {
        $status = 'paid';
    }
}

echo json_encode([
    'success' => true,
    'status' => $status,
    'message' => $status === 'paid'
        ? 'Payment confirmed.'
        : ($status === 'failed' ? 'Payment was not completed.' : null),
]);
