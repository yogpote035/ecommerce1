<?php
	session_start();
	$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
	if ($id > 0 && !empty($_SESSION['cart'])) {
		$key = array_search($id, $_SESSION['cart']);
		if ($key !== false) {
			unset($_SESSION['cart'][$key]);
			$_SESSION['cart'] = array_values($_SESSION['cart']);
		}
		unset($_SESSION['cart_qty'][$id]);
	}

	$_SESSION['message'] = "Product deleted from cart";
	header('location: view_cart.php');
	exit();
?>