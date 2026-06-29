<?php
	session_start();
	if (isset($_POST['save']) && !empty($_POST['qty'])) {
		foreach ($_POST['qty'] as $id => $qty) {
			$id = (int)$id;
			$qty = max(1, (int)$qty);
			$_SESSION['cart_qty'][$id] = $qty;
		}

		$_SESSION['message'] = 'Cart updated successfully';
		header('location: view_cart.php');
		exit();
	}
?>