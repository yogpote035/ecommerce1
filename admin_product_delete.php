<?php
require_once 'init.php';

// Check admin authentication
if (empty($_SESSION['admin_logged_in']) || empty($_SESSION['admin_id'])) {
    header('Location: auth.php?role=admin&mode=login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid delete request.'];
    header('Location: admin_products.php');
    exit;
}

if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid CSRF token.'];
    header('Location: admin_products.php');
    exit;
}

$productId = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($productId <= 0) {
    $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid product ID.'];
    header('Location: admin_products.php');
    exit;
}

// Delete product
$deleteStmt = mysqli_prepare($conn, "DELETE FROM apadd WHERE Apid = ?");
mysqli_stmt_bind_param($deleteStmt, 'i', $productId);

if (mysqli_stmt_execute($deleteStmt)) {
    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Product deleted successfully.'];
} else {
    $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Error deleting product.'];
}

mysqli_stmt_close($deleteStmt);
header('Location: admin_products.php');
exit;
