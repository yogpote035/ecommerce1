<?php
/**
 * Razorpay Configuration & Handler
 * PHASE 6: Payment Integration
 */

// ========================================
// RAZORPAY TEST MODE CREDENTIALS
// ========================================
// Get these from: https://dashboard.razorpay.com/app/keys
// Make sure to use TEST credentials first!

if (!defined('RAZORPAY_KEY_ID')) {
    define('RAZORPAY_KEY_ID', trim((string)(getenv('RAZORPAY_KEY_ID') ?: '')));
}

if (!defined('RAZORPAY_KEY_SECRET')) {
    define('RAZORPAY_KEY_SECRET', trim((string)(getenv('RAZORPAY_KEY_SECRET') ?: '')));
}

if (!defined('RAZORPAY_WEBHOOK_SECRET')) {
    define('RAZORPAY_WEBHOOK_SECRET', trim((string)(getenv('RAZORPAY_WEBHOOK_SECRET') ?: '')));
}

if (!defined('RAZORPAY_LOG_FILE')) {
    define('RAZORPAY_LOG_FILE', __DIR__ . '/../logs/razorpay.log');
}

if (!defined('RAZORPAY_MODE')) {
    $mode = 'unknown';
    if (stripos(RAZORPAY_KEY_ID, 'rzp_test_') === 0) {
        $mode = 'test';
    } elseif (stripos(RAZORPAY_KEY_ID, 'rzp_live_') === 0) {
        $mode = 'live';
    }
    define('RAZORPAY_MODE', $mode);
}

if (!defined('RAZORPAY_ENABLED')) {
    define('RAZORPAY_ENABLED', in_array(RAZORPAY_MODE, ['test', 'live'], true));
}

if (!is_dir(dirname(RAZORPAY_LOG_FILE))) {
    @mkdir(dirname(RAZORPAY_LOG_FILE), 0755, true);
}

$modeLabel = strtoupper(RAZORPAY_MODE === 'live' ? 'LIVE' : (RAZORPAY_MODE === 'test' ? 'TEST' : 'UNKNOWN'));
$startupMessage = sprintf("[%s] Payment Mode: %s | Razorpay Key: %s\n", date('Y-m-d H:i:s'), $modeLabel, RAZORPAY_KEY_ID);
@file_put_contents(RAZORPAY_LOG_FILE, $startupMessage, FILE_APPEND | LOCK_EX);

// ========================================
// RAZORPAY HANDLER CLASS
// ========================================

class RazorpayHandler {
    private $db;
    private $keyId;
    private $keySecret;
    private $webhookSecret;
    private $mode;
    
    public function __construct($db) {
        $this->db = $db;
        $this->keyId = RAZORPAY_KEY_ID;
        $this->keySecret = RAZORPAY_KEY_SECRET;
        $this->webhookSecret = RAZORPAY_WEBHOOK_SECRET;

        if (empty($this->keyId) || empty($this->keySecret)) {
            throw new Exception('Razorpay credentials are not configured properly. Please set RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET in your .env file.');
        }

        if (stripos($this->keyId, 'rzp_test_') === 0) {
            $this->mode = 'test';
        } elseif (stripos($this->keyId, 'rzp_live_') === 0) {
            $this->mode = 'live';
        } else {
            throw new Exception('Invalid Razorpay key format. Use rzp_test_* for TEST or rzp_live_* for LIVE.');
        }
    }
    
    public function getMode() {
        return $this->mode;
    }
    
    /**
     * Create Razorpay order
     * @param int $amount - Amount in INR
     * @param int $orderId - Our order ID
     * @param string $customerEmail - Customer email
     * @param string $customerPhone - Customer phone (optional)
     * @param string $customerName - Customer name (optional)
     * @return array - Razorpay order data
     */
    public function createOrder($amount, $orderId, $customerEmail, $customerPhone = '', $customerName = '') {
        try {
            // Prepare order data
            $orderData = [
                'amount' => (int)($amount * 100),  // Convert to paise (smallest unit)
                'currency' => 'INR',
                'receipt' => 'ORDER-' . $orderId . '-' . time(),
                'payment_capture' => 1,            // Auto capture payment
                'notes' => [
                    'order_id' => $orderId,
                    'customer_email' => $customerEmail,
                    'customer_phone' => $customerPhone
                ]
            ];
            
            // Create cURL request to Razorpay API
            $jsonData = json_encode($orderData);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/orders');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $this->keyId . ':' . $this->keySecret);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData)
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            
            $response = curl_exec($ch);
            $curlError = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($curlError) {
                $this->logOrderEvent($orderId, $orderData, [], 'failed', 'Razorpay cURL error: ' . $curlError);
                throw new Exception('Razorpay cURL error: ' . $curlError);
            }
            
            $decodedResponse = json_decode($response, true);
            $this->logOrderEvent($orderId, $orderData, $decodedResponse, 'pending');
            
            if ($httpCode !== 200) {
                $message = 'Razorpay API error: ' . $response;
                if (isset($decodedResponse['error']['description'])) {
                    $message = 'Razorpay API error: ' . $decodedResponse['error']['description'];
                }
                if (isset($decodedResponse['error']['code']) && $decodedResponse['error']['code'] === 'BAD_REQUEST_ERROR') {
                    $message .= ' Please verify RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET in your .env file.';
                }
                throw new Exception($message);
            }
            
            if (!isset($decodedResponse['id'])) {
                throw new Exception('Invalid Razorpay response: ' . $response);
            }
            
            $razorpayOrder = $decodedResponse;
            
            // Log the order creation
            $this->logPaymentEvent($orderId, $razorpayOrder['id'], 'order_created', [], $razorpayOrder, 'success');
            
            return $razorpayOrder;
            
        } catch (Exception $e) {
            $this->logPaymentEvent($orderId, '', 'order_created', [], [], 'failed', $e->getMessage());
            throw new Exception('Failed to create Razorpay order: ' . $e->getMessage());
        }
    }
    
    /**
     * Verify payment signature
     * @param string $paymentId - Razorpay payment ID
     * @param string $razorpayOrderId - Razorpay order ID
     * @param string $signature - Razorpay signature from webhook/response
     * @param int $orderId - Our order ID (for logging)
     * @return bool - True if valid, throws exception if invalid
     */
    public function verifyPaymentSignature($paymentId, $razorpayOrderId, $signature, $orderId = null) {
        try {
            // Create verification string
            $verifyString = $razorpayOrderId . '|' . $paymentId;
            
            // Generate expected signature
            $expectedSignature = hash_hmac('sha256', $verifyString, $this->keySecret);
            
            // Compare signatures (use hash_equals to prevent timing attacks)
            if (!hash_equals($expectedSignature, $signature)) {
                throw new Exception('Invalid payment signature');
            }
            
            if ($orderId) {
                $this->logPaymentEvent($orderId, $paymentId, 'payment_verified', [], ['status' => 'verified'], 'success');
            }
            
            return true;
            
        } catch (Exception $e) {
            if ($orderId) {
                $this->logPaymentEvent($orderId, $paymentId, 'payment_verified', [], [], 'failed', $e->getMessage());
            }
            throw new Exception('Payment verification failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Get payment details from Razorpay
     * @param string $paymentId - Razorpay payment ID
     * @return array - Payment details
     */
    public function getPaymentDetails($paymentId) {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/payments/' . $paymentId);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $this->keyId . ':' . $this->keySecret);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                throw new Exception('Failed to fetch payment details');
            }
            
            return json_decode($response, true);
            
        } catch (Exception $e) {
            throw new Exception('Error fetching payment: ' . $e->getMessage());
        }
    }
    
    /**
     * Save payment to database
     * @param int $orderId - Our order ID
     * @param string $razorpayPaymentId - Razorpay payment ID
     * @param string $razorpayOrderId - Razorpay order ID
     * @param float $amount - Payment amount
     * @param string $status - Payment status (Paid, Failed, etc)
     * @return bool - Success status
     */
    public function savePayment($orderId, $razorpayPaymentId, $razorpayOrderId, $amount, $status = 'Paid') {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO payments (order_id, payment_method, transaction_reference, payment_id, payment_status, amount)
                VALUES (?, 'Razorpay', ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    payment_id = ?,
                    payment_status = ?,
                    updated_at = NOW()
            ");
            
            if (!$stmt) {
                throw new Exception('DB prepare failed: ' . $this->db->error);
            }
            
            $stmt->bind_param(
                'isssdss',
                $orderId,
                $razorpayOrderId,
                $razorpayPaymentId,
                $status,
                $amount,
                $razorpayPaymentId,
                $status
            );
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            throw new Exception('Failed to save payment: ' . $e->getMessage());
        }
    }
    
    /**
     * Log payment event for audit trail
     * @param int $orderId - Our order ID
     * @param string $paymentId - Payment ID
     * @param string $eventType - Event type
     * @param array $requestData - Request data
     * @param array $responseData - Response data
     * @param string $status - Status
     * @param string $errorMessage - Error message if any
     */
    private function logPaymentEvent($orderId, $paymentId, $eventType, $requestData = [], $responseData = [], $status = 'pending', $errorMessage = '') {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO payment_logs 
                (order_id, payment_id, event_type, request_data, response_data, ip_address, status, error_message)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $requestJson = json_encode($requestData);
            $responseJson = json_encode($responseData);
            
            $stmt->bind_param(
                'isssssss',
                $orderId,
                $paymentId,
                $eventType,
                $requestJson,
                $responseJson,
                $ipAddress,
                $status,
                $errorMessage
            );
            
            $stmt->execute();
        } catch (Exception $e) {
            // Log error silently to avoid breaking the payment flow
            error_log('Payment log error: ' . $e->getMessage());
        }
    }

    private function logOrderEvent($orderId, $requestData, $responseData, $status = 'pending', $errorMessage = '') {
        $mode = strtoupper($this->mode ?? 'UNKNOWN');
        $eventData = [
            'mode' => $mode,
            'key' => $this->keyId,
            'request' => $requestData,
            'response' => $responseData,
            'error' => $errorMessage
        ];
        $message = sprintf(
            "[%s] order_id=%s mode=%s amount=%s currency=%s status=%s error=%s request=%s response=%s\n",
            date('Y-m-d H:i:s'),
            $orderId,
            $mode,
            $requestData['amount'] ?? 'n/a',
            $requestData['currency'] ?? 'n/a',
            $status,
            $errorMessage ?: 'none',
            json_encode($requestData),
            json_encode($responseData)
        );
        @file_put_contents(RAZORPAY_LOG_FILE, $message, FILE_APPEND | LOCK_EX);
    }
}

?>
