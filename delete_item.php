<?php
require_once 'init.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$customerId = $_SESSION['customer_id'] ?? $_SESSION['cid'] ?? 0;

if ($id > 0) {
    if ($customerId > 0) {
        CartHelper::removeCartItem($conn, $customerId, $id);
    } else {
        if (!empty($_SESSION['cart'])) {
            $key = array_search($id, $_SESSION['cart']);
            if ($key !== false) {
                unset($_SESSION['cart'][$key]);
                $_SESSION['cart'] = array_values($_SESSION['cart']);
            }
        }
        unset($_SESSION['cart_qty'][$id]);
    }
}

$_SESSION['message'] = 'Product deleted from cart';
header('location: view_cart.php');
exit();
?>