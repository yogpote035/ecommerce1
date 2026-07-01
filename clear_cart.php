<?php
require_once 'init.php';
$customerId = $_SESSION['customer_id'] ?? $_SESSION['cid'] ?? 0;

if ($customerId > 0) {
    CartHelper::clearCustomerCart($conn, $customerId);
} else {
    unset($_SESSION['cart']);
    unset($_SESSION['cart_qty']);
}

$_SESSION['message'] = 'Cart cleared successfully';
header('location: index.php?page=1');
exit();
?>