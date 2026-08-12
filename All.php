<?php
require_once 'init.php';

$_SESSION['toast'] = ['type' => 'danger', 'message' => 'Admin registration is disabled. Please use the admin login page.'];
header('Location: auth.php?role=admin&mode=login');
exit();
  