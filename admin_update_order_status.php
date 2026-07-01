<?php
require_once 'init.php';

// Check admin authentication
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: auth.php?role=admin&mode=login');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin_orders.php');
    exit();
}

if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid form token. Please try again.'];
    header('Location: admin_orders.php');
    exit();
}

$oid = isset($_POST['oid']) ? (int)$_POST['oid'] : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
$redirect = trim($_POST['redirect'] ?? '');
$redirect = preg_match('/^[A-Za-z0-9_\/?.=&%-]+$/', $redirect) ? $redirect : '';
$fallbackRedirect = $oid > 0 ? 'admin_order_details.php?oid=' . $oid : 'admin_orders.php';
$redirectUrl = $redirect !== '' ? $redirect : $fallbackRedirect;

if (!$oid || !$status) {
    $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid order or status'];
    header('Location: ' . $redirectUrl);
    exit();
}

// Validate status
$validStatuses = ['Pending', 'Confirmed', 'Packed', 'Shipped', 'Out For Delivery', 'Delivered', 'Cancelled'];
if (!in_array($status, $validStatuses)) {
    $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid status value'];
    header('Location: ' . $redirectUrl);
    exit();
}

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

// Update order status log
$stmt = mysqli_prepare($conn, "INSERT INTO order_status_log (order_id, status, notes, updated_by, created_at) VALUES (?, ?, ?, ?, NOW())");
$updatedBy = $_SESSION['admin_id'] ?? 0;
mysqli_stmt_bind_param($stmt, 'issi', $oid, $status, $notes, $updatedBy);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Order status updated successfully'];
    
    // Log the update
    error_log("Admin " . $_SESSION['aname'] . " updated order $oid status to $status");
} else {
    $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Failed to update order status'];
}

mysqli_stmt_close($stmt);
header('Location: ' . $redirectUrl);
exit();
?>
