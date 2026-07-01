<?php
require_once 'init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    $email = SecurityHelper::sanitizeEmail($_POST['Cemail'] ?? '');
    $Cpass = trim($_POST['Cpass'] ?? '');

    if (!SecurityHelper::isValidEmail($email)) {
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Please enter a valid email address.'];
        header('Location: auth.php');
        exit();
    }

    $stmt = mysqli_prepare($conn, "SELECT Cid, Cpass FROM cregister WHERE Cemail = ?");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $cid, $storedHash);

    if (mysqli_stmt_fetch($stmt)) {
        $legacyPassword = !SecurityHelper::isPasswordHash($storedHash) && $storedHash === $Cpass;
        if (SecurityHelper::verifyPassword($Cpass, $storedHash) || $legacyPassword) {
            if ($legacyPassword) {
                $newHash = SecurityHelper::hashPassword($Cpass);
                $updateStmt = mysqli_prepare($conn, "UPDATE cregister SET Cpass = ? WHERE Cid = ?");
                mysqli_stmt_bind_param($updateStmt, 'si', $newHash, $cid);
                mysqli_stmt_execute($updateStmt);
                mysqli_stmt_close($updateStmt);
            }

            $_SESSION['customer_id'] = $cid;
            $_SESSION['cid'] = $cid;
            $_SESSION['customer_email'] = $email;
            $_SESSION['customer_logged_in'] = true;
            if (!empty($_POST['remember_me'])) {
                issueRememberToken($conn, $cid, 'customer');
            }
            
            if (!empty($_SESSION['cart'])) {
                CartHelper::syncSessionCartToDb($conn, $cid);
            }
            CartHelper::loadCustomerCartToSession($conn, $cid);
            SecurityHelper::logActivity($conn, 'login', 'auth', $cid, $cid, 'customer');

            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Logged in successfully.'];
            header('Location: index.php?page=1');
            exit();
        }
    }

    mysqli_stmt_close($stmt);
    $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid email or password.'];
    header('Location: auth.php?role=customer&mode=login');
    exit();
}

mysqli_close($conn);
?>
