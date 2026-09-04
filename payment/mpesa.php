<?php
require_once __DIR__ . '/../includes/helpers.php';

class MpesaGateway
{
    public static function isConfigured()
    {
        return defined('MPESA_CONSUMER_KEY')
            && defined('MPESA_CONSUMER_SECRET')
            && defined('MPESA_BUSINESS_SHORTCODE')
            && defined('MPESA_PASSKEY')
            && MPESA_CONSUMER_KEY !== 'your-mpesa-consumer-key'
            && MPESA_CONSUMER_SECRET !== 'your-mpesa-consumer-secret'
            && MPESA_BUSINESS_SHORTCODE !== 'your-business-shortcode'
            && MPESA_PASSKEY !== 'your-mpesa-passkey';
    }

    public static function validatePhone($phone)
    {
        return preg_match('/^(?:\+254|254|0)[1-9]\d{8}$/', trim((string) $phone)) === 1;
    }

    public static function generateReference($prefix = 'ICTECH')
    {
        return $prefix . '-' . date('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(4)));
    }

    public static function getAccessToken()
    {
        if (!self::isConfigured()) {
            return null;
        }

        $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
        $credentials = base64_encode(MPESA_CONSUMER_KEY . ':' . MPESA_CONSUMER_SECRET);
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Basic ' . $credentials, 'Content-Type: application/json'],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            return null;
        }

        $payload = json_decode($response, true);
        return $payload['access_token'] ?? null;
    }

    public static function initiateSTKPush($userId, $enrollmentId, $amount, $phone, $reference = '')
    {
        $db = Database::getInstance();
        $cleanPhone = preg_replace('/\s+/', '', trim((string) $phone));
        $cleanPhone = preg_replace('/^\+/', '', $cleanPhone);
        $cleanPhone = preg_replace('/^0/', '254', $cleanPhone);

        if (!self::validatePhone($cleanPhone)) {
            return ['success' => false, 'error' => 'Use a valid Kenyan phone number.'];
        }

        $amountValue = (float) $amount;
        if ($amountValue <= 0) {
            return ['success' => false, 'error' => 'Amount must be greater than zero.'];
        }

        $reference = $reference !== '' ? $reference : self::generateReference();
        $checkoutRequestId = 'ws_' . strtoupper(bin2hex(random_bytes(6)));
        $merchantRequestId = 'mr_' . strtoupper(bin2hex(random_bytes(6)));

        $paymentId = $db->insert('payments', [
            'user_id' => (int) $userId,
            'enrollment_id' => $enrollmentId ? (int) $enrollmentId : null,
            'amount' => $amountValue,
            'method' => 'mpesa',
            'status' => 'pending',
            'reference' => $reference,
            'checkout_request_id' => $checkoutRequestId,
            'merchant_request_id' => $merchantRequestId,
        ]);

        if (!self::isConfigured()) {
            return [
                'success' => true,
                'payment_id' => $paymentId,
                'status' => 'pending',
                'reference' => $reference,
                'checkout_request_id' => $checkoutRequestId,
                'merchant_request_id' => $merchantRequestId,
                'simulated' => true,
                'message' => 'M-Pesa credentials are not configured in this environment, so the payment request was created in pending state for local testing.'
            ];
        }

        $token = self::getAccessToken();
        if (!$token) {
            $db->update('payments', ['status' => 'failed'], 'id = ?', [$paymentId]);
            return ['success' => false, 'error' => 'Unable to generate an M-Pesa access token. Please check your credentials.'];
        }

        $payload = [
            'BusinessShortCode' => MPESA_BUSINESS_SHORTCODE,
            'Password' => base64_encode(MPESA_BUSINESS_SHORTCODE . MPESA_PASSKEY . date('YmdHis')),
            'Timestamp' => date('YmdHis'),
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => (string) round($amountValue, 2),
            'PartyA' => $cleanPhone,
            'PartyB' => MPESA_BUSINESS_SHORTCODE,
            'PhoneNumber' => $cleanPhone,
            'CallBackURL' => MPESA_CALLBACK_URL,
            'AccountReference' => $reference,
            'TransactionDesc' => 'ICTECH course payment',
        ];

        $response = self::sendJsonRequest('https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest', $token, $payload);

        if ($response && isset($response['ResponseCode']) && (string) $response['ResponseCode'] === '0') {
            $db->update('payments', [
                'checkout_request_id' => $checkoutRequestId,
                'merchant_request_id' => $merchantRequestId,
                'status' => 'pending',
            ], 'id = ?', [$paymentId]);

            return [
                'success' => true,
                'payment_id' => $paymentId,
                'status' => 'pending',
                'reference' => $reference,
                'checkout_request_id' => $checkoutRequestId,
                'merchant_request_id' => $merchantRequestId,
                'response' => $response,
            ];
        }

        $db->update('payments', ['status' => 'failed'], 'id = ?', [$paymentId]);

        return [
            'success' => false,
            'error' => $response['ResponseDescription'] ?? 'M-Pesa request was rejected.',
            'response' => $response,
        ];
    }

    public static function updatePaymentFromCallback($data)
    {
        $db = Database::getInstance();

        $checkoutId = $data['CheckoutRequestID'] ?? $data['checkoutRequestId'] ?? null;
        $merchantId = $data['MerchantRequestID'] ?? $data['merchantRequestId'] ?? null;
        $resultCode = $data['ResultCode'] ?? $data['resultCode'] ?? null;

        if (!$checkoutId && !$merchantId) {
            return false;
        }

        $payment = $db->getRow(
            'SELECT * FROM payments WHERE checkout_request_id = ? OR merchant_request_id = ? LIMIT 1',
            [$checkoutId, $merchantId]
        );

        if (!$payment) {
            return false;
        }

        if ((string) $resultCode === '0') {
            $db->update('payments', ['status' => 'paid'], 'id = ?', [$payment['id']]);
            if (!empty($payment['enrollment_id'])) {
                $db->update('enrollments', ['status' => 'active', 'approved_at' => date('Y-m-d H:i:s')], 'id = ?', [$payment['enrollment_id']]);
            }
            return true;
        }

        $db->update('payments', ['status' => 'failed'], 'id = ?', [$payment['id']]);
        return true;
    }

    private static function sendJsonRequest($url, $token, $payload)
    {
        if (!function_exists('curl_init')) {
            return null;
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300 || !$response) {
            return null;
        }

        return json_decode($response, true);
    }
}
