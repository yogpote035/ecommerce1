<?php
/**
 * Payment Processing API
 * PHASE 6: Payment Integration
 * 
 * Handles:
 * - POST /api/payment.php - Create payment order
 * - POST /api/payment.php?action=verify - Verify payment
 */

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Enable error logging
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Include dependencies
require_once dirname(__DIR__) . '/init.php';
require_once dirname(__DIR__) . '/config/Razorpay.php';
require_once dirname(__DIR__) . '/helpers/SecurityHelper.php';

// ========================================
// AUTHENTICATION CHECK
// ========================================

if (!isset($_SESSION['cid']) && !isset($_SESSION['customer_id']) && !isset($_SESSION['aid'])) {
    http_response_code(401);
    die(json_encode([
        'success' => false,
        'error' => 'User not authenticated'
    ]));
}

$customerId = $_SESSION['cid'] ?? $_SESSION['customer_id'] ?? 0;
$adminId = $_SESSION['aid'] ?? 0;

ensurePaymentTrackingSchema();

/**
 * Map Razorpay error messages to more user-friendly descriptions.
 */
function mapRazorpayErrorMessage($message) {
    $lower = strtolower($message);

    if (strpos($lower, 'international_transaction_not_allowed') !== false
        || (strpos($lower, 'international') !== false && strpos($lower, 'not allowed') !== false)
        || strpos($lower, 'card not supported') !== false
        || strpos($lower, 'gateway rejected') !== false
        || strpos($lower, 'payment method not allowed') !== false
    ) {
        return 'Razorpay has blocked this card because international transactions are not enabled for the account. In test mode, use UPI (`test@razorpay`) or a supported local test card instead.';
    }

    if (strpos($lower, 'bad_request_error') !== false || strpos($lower, 'invalid key id') !== false || strpos($lower, 'invalid key secret') !== false) {
        return 'Razorpay credentials appear invalid. Please verify RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET in your .env file.';
    }

    if (strpos($lower, 'payment not captured') !== false) {
        return 'Payment was not captured by Razorpay. Please try again or contact support.';
    }

    return $message;
}

// ========================================
// ROUTE HANDLING
// ========================================

$action = isset($_GET['action']) ? $_GET['action'] : 'create';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        switch ($action) {
            case 'create':
                handlePaymentOrder();
                break;
            case 'verify':
                verifyPayment();
                break;
            default:
                throw new Exception('Invalid action');
        }
    } else {
        throw new Exception('Only POST requests allowed');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => mapRazorpayErrorMessage($e->getMessage())
    ]);
}

exit;

// ========================================
// HANDLER FUNCTIONS
// ========================================

/**
 * Handle payment order creation
 */
function handlePaymentOrder() {
    global $conn, $customerId;
    
    // Validate request
    $paymentMethod = $_POST['payment_method'] ?? 'COD';
    $shippingAddress = $_POST['shipping_address'] ?? '';
    $cartItems = json_decode($_POST['cart_items'] ?? '[]', true);
    
    if (empty($cartItems)) {
        throw new Exception('Cart is empty');
    }
    
    if (!in_array($paymentMethod, ['COD', 'Razorpay'])) {
        throw new Exception('Invalid payment method');
    }
    
    // Calculate total from database prices, never from client-submitted prices.
    $total = 0;
    $orderItems = [];
    
    foreach ($cartItems as $item) {
        if (!isset($item['id'], $item['qty'])) {
            throw new Exception('Invalid cart item format');
        }
        
        $productId = (int)$item['id'];
        $qty = (int)$item['qty'];
        
        if ($productId <= 0 || $qty <= 0) {
            throw new Exception('Invalid item quantity');
        }

        $productStmt = $conn->prepare('SELECT Apid, apprice, apqty FROM apadd WHERE Apid = ? LIMIT 1');
        if (!$productStmt) {
            throw new Exception('Unable to validate cart item');
        }
        $productStmt->bind_param('i', $productId);
        $productStmt->execute();
        $product = $productStmt->get_result()->fetch_assoc();
        $productStmt->close();

        if (!$product) {
            throw new Exception('One or more cart products no longer exist');
        }

        if (isset($product['apqty']) && (int)$product['apqty'] > 0 && $qty > (int)$product['apqty']) {
            throw new Exception('Requested quantity exceeds available stock');
        }

        $price = (float)$product['apprice'];
        
        $itemTotal = $price * $qty;
        $total += $itemTotal;
        
        $orderItems[] = [
            'product_id' => $productId,
            'quantity' => $qty,
            'price' => $price,
            'subtotal' => $itemTotal
        ];
    }
    
    // Validate total
    if ($total <= 0 || $total > 100000) { // Max 1 lakh
        throw new Exception('Invalid order total');
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Create order
        $primaryProductId = (int)($orderItems[0]['product_id'] ?? 0);
        $stmt = $conn->prepare("
            INSERT INTO orders (pid, cid, cost, source, destination)
            VALUES (?, ?, ?, 'Warehouse', ?)
        ");
        
        $stmt->bind_param('iids', $primaryProductId, $customerId, $total, $shippingAddress);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create order');
        }
        
        $orderId = $conn->insert_id;
        
        // Add initial status log
        $status = 'Pending';
        $stmt = $conn->prepare("
            INSERT INTO order_status_log (order_id, status, notes)
            VALUES (?, ?, 'Order created')
        ");
        $stmt->bind_param('is', $orderId, $status);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create status log');
        }
        
        // Handle payment
        if ($paymentMethod === 'Razorpay') {
            if (RAZORPAY_MODE === 'unknown') {
                throw new Exception('Razorpay is not configured correctly. Please set valid Razorpay API credentials in your .env file.');
            }

            $razorpay = new RazorpayHandler($conn);
            
            // Get customer email from session or database
            $customerEmail = $_SESSION['cemail'] ?? '';
            $customerPhone = $_SESSION['cphone'] ?? '';
            
            if (!$customerEmail) {
            $stmt = $conn->prepare("SELECT Cemail, Ccontact FROM cregister WHERE Cid = ?");
                $stmt->bind_param('i', $customerId);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                
                if ($result) {
                    $customerEmail = $result['Cemail'];
                    $customerPhone = $result['Ccontact'];
                }
            }
            
            // Create Razorpay order
            $razorpayOrder = $razorpay->createOrder(
                $total,
                $orderId,
                $customerEmail,
                $customerPhone,
                $_SESSION['cname'] ?? 'Customer'
            );
            
            // Save payment record
            $razorpay->savePayment(
                $orderId,
                '',
                $razorpayOrder['id'],
                $total,
                'Pending'
            );
            
            $conn->commit();
            $_SESSION['last_order_id'] = $orderId;
            
            // Return Razorpay order details
            echo json_encode([
                'success' => true,
                'order_id' => $orderId,
                'razorpay_order_id' => $razorpayOrder['id'],
                'razorpay_key' => RAZORPAY_KEY_ID,
                'razorpay_mode' => RAZORPAY_MODE,
                'amount' => $total,
                'customer_email' => $customerEmail,
                'customer_phone' => $customerPhone,
                'customer_name' => $_SESSION['cname'] ?? 'Customer'
            ]);
            
        } else {
            // COD Payment
            $codStatus = 'Pending';
            $stmt = $conn->prepare("
                INSERT INTO payments (order_id, payment_method, payment_status, amount)
                VALUES (?, 'COD', ?, ?)
            ");
            $stmt->bind_param('isd', $orderId, $codStatus, $total);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to create payment record');
            }
            
            if ($customerId > 0) {
                CartHelper::clearCustomerCart($conn, $customerId);
            } else {
                unset($_SESSION['cart'], $_SESSION['cart_qty']);
            }
            
            $conn->commit();
            $_SESSION['last_order_id'] = $orderId;
            
            echo json_encode([
                'success' => true,
                'order_id' => $orderId,
                'payment_method' => 'COD',
                'amount' => $total,
                'message' => 'Order created with Cash on Delivery'
            ]);
        }
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

/**
 * Verify payment and update order status
 */
function verifyPayment() {
    global $conn, $customerId;
    
    // Get verification data
    $paymentId = $_POST['razorpay_payment_id'] ?? '';
    $orderId = $_POST['razorpay_order_id'] ?? '';
    $signature = $_POST['razorpay_signature'] ?? '';
    $ourOrderId = (int)($_POST['order_id'] ?? 0);
    
    if (!$paymentId || !$orderId || !$signature || !$ourOrderId) {
        throw new Exception('Missing payment verification data');
    }
    
    try {
        $razorpay = new RazorpayHandler($conn);
        
        // Verify signature
        $razorpay->verifyPaymentSignature($paymentId, $orderId, $signature, $ourOrderId);
        
        // Get payment details from Razorpay
        $paymentDetails = $razorpay->getPaymentDetails($paymentId);
        
        if (!$paymentDetails || $paymentDetails['status'] !== 'captured') {
            throw new Exception('Payment not captured');
        }
        
        // Update payment in database
        $stmt = $conn->prepare("
            UPDATE payments 
            SET payment_id = ?, payment_status = 'Paid', updated_at = NOW()
            WHERE order_id = ? AND transaction_reference = ?
        ");
        $stmt->bind_param('sis', $paymentId, $ourOrderId, $orderId);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update payment');
        }

        if ($customerId > 0) {
            CartHelper::clearCustomerCart($conn, $customerId);
        } else {
            unset($_SESSION['cart'], $_SESSION['cart_qty']);
        }
        
        // Add confirmed order status while preserving the original pending event.
        $stmt = $conn->prepare("
            INSERT INTO order_status_log (order_id, status, notes)
            SELECT ?, 'Confirmed', 'Payment verified successfully'
            WHERE NOT EXISTS (
                SELECT 1 FROM order_status_log WHERE order_id = ? AND status = 'Confirmed'
            )
        ");
        $stmt->bind_param('ii', $ourOrderId, $ourOrderId);
        $stmt->execute();
        
        $_SESSION['last_order_id'] = $ourOrderId;
        echo json_encode([
            'success' => true,
            'order_id' => $ourOrderId,
            'payment_id' => $paymentId,
            'status' => 'verified',
            'message' => 'Payment verified successfully'
        ]);
        
    } catch (Exception $e) {
        throw new Exception(mapRazorpayErrorMessage('Payment verification failed: ' . $e->getMessage()));
    }
}

function ensurePaymentTrackingSchema() {
    global $conn;

    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS payments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        order_id INT NOT NULL,
        payment_method ENUM('COD', 'Razorpay', 'Other') DEFAULT 'COD',
        payment_id VARCHAR(255),
        transaction_reference VARCHAR(255),
        payment_status ENUM('Pending', 'Paid', 'Failed', 'Refunded') DEFAULT 'Pending',
        amount DECIMAL(10,2) NOT NULL,
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
        event_type VARCHAR(80) DEFAULT 'payment_initiated',
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

    ensureColumnExists('payments', 'payment_id', "ALTER TABLE payments ADD COLUMN payment_id VARCHAR(255) NULL");
    ensureColumnExists('payments', 'transaction_reference', "ALTER TABLE payments ADD COLUMN transaction_reference VARCHAR(255) NULL");
    ensureColumnExists('payments', 'notes', "ALTER TABLE payments ADD COLUMN notes TEXT NULL");
    ensureColumnExists('payments', 'updated_at', "ALTER TABLE payments ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    ensureColumnExists('payment_logs', 'error_message', "ALTER TABLE payment_logs ADD COLUMN error_message TEXT NULL");
}

function ensureColumnExists($table, $column, $alterSql) {
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
