<?php
require_once 'init.php';

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$customerId = $_SESSION['customer_id'] ?? $_SESSION['cid'] ?? 0;

if ($productId > 0) {
    if ($customerId > 0) {
        CartHelper::addCartItem($conn, $customerId, $productId, 1);
        $_SESSION['message'] = 'Product added to cart successfully.';
    } else {
        if (empty($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        if (empty($_SESSION['cart_qty'])) {
            $_SESSION['cart_qty'] = [];
        }

        if (!in_array($productId, $_SESSION['cart'])) {
            $_SESSION['cart'][] = $productId;
            $_SESSION['cart_qty'][$productId] = 1;
            $_SESSION['message'] = 'Product added to cart successfully.';
        } else {
            $_SESSION['cart_qty'][$productId] = isset($_SESSION['cart_qty'][$productId]) ? $_SESSION['cart_qty'][$productId] + 1 : 1;
            $_SESSION['message'] = 'Product quantity updated in cart.';
        }
    }
} else {
    $_SESSION['message'] = 'Invalid product selection.';
}

header('Location: index.php?page=1');
exit();
?>
