<?php
require_once 'init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: auth.php?role=admin&mode=signup');
    exit();
}

if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    die('Invalid CSRF token');
}

$aname = SecurityHelper::sanitize($_POST['aname'] ?? '');
$email = SecurityHelper::sanitizeEmail($_POST['email'] ?? '');
$aadd = SecurityHelper::sanitize($_POST['aadd'] ?? '');
$apass = trim($_POST['apass'] ?? '');

$redirectUrl = 'auth.php?role=admin&mode=signup';

if (!SecurityHelper::isValidEmail($email)) {
    $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Please enter a valid email address.'];
    header('Location: ' . $redirectUrl);
    exit();
}

$passwordValidation = SecurityHelper::validatePassword($apass);
if (!$passwordValidation['valid']) {
    $_SESSION['toast'] = ['type' => 'danger', 'message' => implode(' ', $passwordValidation['errors'])];
    header('Location: ' . $redirectUrl);
    exit();
}

$checkStmt = mysqli_prepare($conn, "SELECT aid FROM aregister WHERE email = ?");
mysqli_stmt_bind_param($checkStmt, 's', $email);
mysqli_stmt_execute($checkStmt);
mysqli_stmt_store_result($checkStmt);
if (mysqli_stmt_num_rows($checkStmt) > 0) {
    $_SESSION['toast'] = ['type' => 'danger', 'message' => 'This admin email is already registered. Please login instead.'];
    mysqli_stmt_close($checkStmt);
    header('Location: ' . $redirectUrl);
    exit();
}
mysqli_stmt_close($checkStmt);

$apassHash = SecurityHelper::hashPassword($apass);
$stmt = mysqli_prepare($conn, "INSERT INTO aregister (aname, aadd, email, apass) VALUES (?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, 'ssss', $aname, $aadd, $email, $apassHash);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Admin account created successfully. Please login.'];
    header('Location: auth.php?role=admin&mode=login');
    exit();
}

$_SESSION['toast'] = ['type' => 'danger', 'message' => 'Unable to register admin, please try again.'];
header('Location: ' . $redirectUrl);
exit();

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
  