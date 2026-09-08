<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/mpesa.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$data = $_POST;
if (empty($data)) {
    $raw = file_get_contents('php://input');
    $data = strlen($raw) <= 65536 ? (json_decode($raw, true) ?: []) : [];
}

if (empty($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No callback payload received.']);
    exit;
}

$result = MpesaGateway::updatePaymentFromCallback($data);
if ($result) {
    echo json_encode(['success' => true, 'message' => 'Payment callback processed.']);
    exit;
}

http_response_code(404);
echo json_encode(['success' => false, 'message' => 'Payment reference not found.']);
