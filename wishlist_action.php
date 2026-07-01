<?php
require_once 'init.php';

$customerId = $_SESSION['customer_id'] ?? $_SESSION['cid'] ?? 0;
$productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : (int) ($_GET['product_id'] ?? 0);
$redirect = $_POST['redirect'] ?? $_SERVER['HTTP_REFERER'] ?? 'wishlist.php';

if ($customerId <= 0) {
    $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Please login as a customer to use your wishlist.'];
    header('Location: auth.php?role=customer&mode=login&redirect=' . urlencode($redirect));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid wishlist request.'];
    header('Location: ' . $redirect);
    exit;
}

$result = WishlistHelper::toggle($conn, $customerId, $productId);
if ($result['ok']) {
    SecurityHelper::logActivity($conn, $result['wishlisted'] ? 'wishlist_add' : 'wishlist_remove', 'product', $productId, $customerId, 'customer');
    $_SESSION['toast'] = [
        'type' => 'success',
        'message' => $result['wishlisted'] ? 'Added to wishlist.' : 'Removed from wishlist.',
    ];
} else {
    $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Unable to update wishlist.'];
}

header('Location: ' . $redirect);
exit;
