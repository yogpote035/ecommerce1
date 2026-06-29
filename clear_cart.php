<?php
	session_start();
	unset($_SESSION['cart']);
	unset($_SESSION['cart_qty']);
	$_SESSION['message'] = 'Cart cleared successfully';
	header('location: index.php');
	exit();
?>