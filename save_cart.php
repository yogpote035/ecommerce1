<?php
require_once 'init.php';
$customerId = $_SESSION['customer_id'] ?? $_SESSION['cid'] ?? 0;

if (isset($_POST['save']) && !empty($_POST['qty'])) {
    foreach ($_POST['qty'] as $id => $qty) {
        $id = (int)$id;
        $qty = max(1, (int)$qty);

        if ($customerId > 0) {
            CartHelper::updateCartItemQty($conn, $customerId, $id, $qty);
        } else {
            $_SESSION['cart_qty'][$id] = $qty;
        }
    }

    $_SESSION['message'] = 'Cart updated successfully';
    header('location: view_cart.php');
    exit();
}
?>