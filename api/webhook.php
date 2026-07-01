<?php
/**
 * Razorpay Webhook Handler
 * PHASE 6: Payment Integration
 * 
 * Receives and processes Razorpay payment events
 */

header('Content-Type: application/json');

// Enable error logging
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Include dependencies
require_once dirname(__DIR__) . '/init.php';
require_once dirname(__DIR__) . '/config/Razorpay.php';

ensureWebhookPaymentSchema();

// Log webhook request
$requestBody = file_get_contents('php://input');
$webhookSignature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

error_log('Webhook received: ' . $requestBody);
error_log('Webhook signature: ' . $webhookSignature);

try {
    // Verify webhook signature
    if (!verifyWebhookSignature($requestBody, $webhookSignature)) {
        http_response_code(401);
        throw new Exception('Invalid webhook signature');
    }
    
    // Parse webhook data
    $data = json_decode($requestBody, true);
    
    if (!$data || !isset($data['event'])) {
        throw new Exception('Invalid webhook data');
    }
    
    // Handle different webhook events
    switch ($data['event']) {
        case 'payment.authorized':
            handlePaymentAuthorized($data);
            break;
            
        case 'payment.failed':
            handlePaymentFailed($data);
            break;
            
        case 'payment.captured':
            handlePaymentCaptured($data);
            break;
            
        case 'refund.created':
            handleRefundCreated($data);
            break;
            
        default:
            // Log unhandled events
            error_log('Unhandled webhook event: ' . $data['event']);
            break;
    }
    
    // Return success response
    http_response_code(200);
    echo json_encode(['status' => 'ok']);
    
} catch (Exception $e) {
    error_log('Webhook error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

exit;

// ========================================
// WEBHOOK HANDLERS
// ========================================

/**
 * Verify webhook signature
 */
function verifyWebhookSignature($body, $signature) {
    $expectedSignature = hash_hmac('sha256', $body, RAZORPAY_WEBHOOK_SECRET);
    return hash_equals($expectedSignature, $signature);
}

/**
 * Handle payment authorized event
 */
function handlePaymentAuthorized($data) {
    global $conn;
    
    $payment = $data['payload']['payment']['entity'] ?? [];
    $paymentId = $payment['id'] ?? '';
    $orderId = $payment['notes']['order_id'] ?? 0;
    $amount = ($payment['amount'] ?? 0) / 100; // Convert from paise to INR
    $status = $payment['status'] ?? 'unknown';
    
    if (!$paymentId || !$orderId) {
        throw new Exception('Missing payment or order ID');
    }
    
    error_log("Payment authorized: $paymentId for order $orderId");
    
    // Log webhook event
    logWebhookEvent($orderId, $paymentId, 'payment.authorized', $data, 'success');
}

/**
 * Handle payment captured event
 */
function handlePaymentCaptured($data) {
    global $conn;
    
    $payment = $data['payload']['payment']['entity'] ?? [];
    $paymentId = $payment['id'] ?? '';
    $orderId = $payment['notes']['order_id'] ?? 0;
    $amount = ($payment['amount'] ?? 0) / 100;
    $status = $payment['status'] ?? 'unknown';
    
    if (!$paymentId || !$orderId) {
        throw new Exception('Missing payment or order ID');
    }
    
    error_log("Payment captured: $paymentId for order $orderId");
    
    // Update payment status
    $stmt = $conn->prepare("
        UPDATE payments 
        SET payment_id = ?, payment_status = 'Paid', updated_at = NOW()
        WHERE order_id = ?
    ");
    $stmt->bind_param('si', $paymentId, $orderId);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update payment status');
    }
    
    // Update order status to Confirmed if still Pending
    $stmt = $conn->prepare("
        INSERT INTO order_status_log (order_id, status, notes)
        SELECT ?, 'Confirmed', 'Payment captured via webhook'
        WHERE NOT EXISTS (
            SELECT 1 FROM order_status_log 
            WHERE order_id = ? AND status IN ('Confirmed', 'Packed', 'Shipped', 'Delivered')
        )
    ");
    $stmt->bind_param('ii', $orderId, $orderId);
    $stmt->execute();
    
    // Log webhook event
    logWebhookEvent($orderId, $paymentId, 'payment.captured', $data, 'success');
}

/**
 * Handle payment failed event
 */
function handlePaymentFailed($data) {
    global $conn;
    
    $payment = $data['payload']['payment']['entity'] ?? [];
    $paymentId = $payment['id'] ?? '';
    $orderId = $payment['notes']['order_id'] ?? 0;
    $errorReason = $payment['error_reason'] ?? 'Unknown error';
    $errorDescription = $payment['error_description'] ?? '';
    
    if (!$orderId) {
        throw new Exception('Missing order ID');
    }
    
    error_log("Payment failed for order $orderId: $errorDescription");
    
    // Update payment status
    $failedStatus = 'Failed';
    $stmt = $conn->prepare("
        INSERT INTO payments (order_id, payment_method, transaction_reference, payment_id, payment_status, notes)
        VALUES (?, 'Razorpay', ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            payment_status = ?,
            notes = CONCAT(notes, '\n', ?),
            updated_at = NOW()
    ");
    
    $notes = "Failed: $errorReason - $errorDescription";
    $stmt->bind_param(
        'issssss',
        $orderId,
        $orderId,
        $paymentId,
        $failedStatus,
        $notes,
        $failedStatus,
        $notes
    );
    $stmt->execute();
    
    // Log webhook event
    logWebhookEvent($orderId, $paymentId, 'payment.failed', $data, 'success', $errorDescription);
}

/**
 * Handle refund created event
 */
function handleRefundCreated($data) {
    global $conn;
    
    $refund = $data['payload']['refund']['entity'] ?? [];
    $refundId = $refund['id'] ?? '';
    $paymentId = $refund['payment_id'] ?? '';
    $orderId = $refund['notes']['order_id'] ?? 0;
    $amount = ($refund['amount'] ?? 0) / 100;
    
    if (!$paymentId) {
        throw new Exception('Missing payment ID');
    }
    
    if ($orderId) {
        error_log("Refund created: $refundId for order $orderId");
        
        // Update payment status
        $refundedStatus = 'Refunded';
        $stmt = $conn->prepare("
            UPDATE payments 
            SET payment_status = ?, notes = CONCAT(IFNULL(notes, ''), '\nRefund: $refundId')
            WHERE order_id = ?
        ");
        $stmt->bind_param('si', $refundedStatus, $orderId);
        $stmt->execute();
        
        // Log webhook event
        logWebhookEvent($orderId, $paymentId, 'refund.created', $data, 'success');
    }
}

/**
 * Log webhook event for audit trail
 */
function logWebhookEvent($orderId, $paymentId, $eventType, $eventData, $status = 'success', $errorMessage = '') {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO payment_logs 
            (order_id, payment_id, event_type, request_data, response_data, ip_address, status, error_message)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'webhook';
        $eventJson = json_encode($eventData);
        
        $stmt->bind_param(
            'isssssss',
            $orderId,
            $paymentId,
            $eventType,
            $eventJson,
            $eventJson,
            $ipAddress,
            $status,
            $errorMessage
        );
        
        $stmt->execute();
    } catch (Exception $e) {
        error_log('Failed to log webhook event: ' . $e->getMessage());
    }
}

function ensureWebhookPaymentSchema() {
    global $conn;

    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS payments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        order_id INT NOT NULL,
        payment_method ENUM('COD', 'Razorpay', 'Other') DEFAULT 'COD',
        payment_id VARCHAR(255),
        transaction_reference VARCHAR(255),
        payment_status ENUM('Pending', 'Paid', 'Failed', 'Refunded') DEFAULT 'Pending',
        amount DECIMAL(10,2) DEFAULT 0.00,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (order_id, payment_status),
        INDEX (payment_id),
        INDEX (transaction_reference)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS order_status_log (
        id INT PRIMARY KEY AUTO_INCREMENT,
        order_id INT NOT NULL,
        status ENUM('Pending', 'Confirmed', 'Packed', 'Shipped', 'Out For Delivery', 'Delivered', 'Cancelled') DEFAULT 'Pending',
        notes TEXT,
        updated_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (order_id, status),
        INDEX (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS payment_logs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        order_id INT,
        payment_id VARCHAR(255),
        event_type VARCHAR(80) DEFAULT 'webhook_received',
        request_data JSON,
        response_data JSON,
        ip_address VARCHAR(45),
        status ENUM('success', 'failed', 'pending') DEFAULT 'pending',
        error_message TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (order_id),
        INDEX (payment_id),
        INDEX (event_type),
        INDEX (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    ensureWebhookColumnExists('payments', 'notes', "ALTER TABLE payments ADD COLUMN notes TEXT NULL");
    ensureWebhookColumnExists('payments', 'updated_at', "ALTER TABLE payments ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    ensureWebhookColumnExists('payment_logs', 'error_message', "ALTER TABLE payment_logs ADD COLUMN error_message TEXT NULL");
}

function ensureWebhookColumnExists($table, $column, $alterSql) {
    global $conn;

    $stmt = mysqli_prepare($conn, 'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, 'ss', $table, $column);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $count);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if ((int)$count === 0) {
        return mysqli_query($conn, $alterSql) !== false;
    }

    return true;
}

?>
