<?php
require_once 'init.php';
$_SESSION['toast'] = ['type' => 'danger', 'message' => 'Retailer functionality is no longer available. Please use customer or admin access.'];
header('Location: auth.php');
exit;

