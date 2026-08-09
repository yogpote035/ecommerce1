<?php
require_once 'init.php';
$siteTitle = 'Access Restricted';

$_SESSION['toast'] = ['type' => 'danger', 'message' => 'Retailer access is no longer available. Please use customer or admin accounts.'];
header('Location: auth.php');
exit;
