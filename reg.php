<?php
require_once 'init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    $Cname = SecurityHelper::sanitize($_POST['name'] ?? '');
    $Cemail = SecurityHelper::sanitizeEmail($_POST['c_email'] ?? '');
    $Cadd = SecurityHelper::sanitize($_POST['c_add'] ?? '');
    $Ccontact = SecurityHelper::sanitize($_POST['c_contact'] ?? '');
    $Cpass = trim($_POST['c_pass'] ?? '');

    if (!SecurityHelper::isValidEmail($Cemail)) {
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Please enter a valid email address.'];
        header('Location: auth.php');
        exit();
    }

    $hashedPassword = SecurityHelper::hashPassword($Cpass);

    $checkStmt = mysqli_prepare($conn, "SELECT Cid FROM cregister WHERE Cemail = ?");
    mysqli_stmt_bind_param($checkStmt, 's', $Cemail);
    mysqli_stmt_execute($checkStmt);
    mysqli_stmt_store_result($checkStmt);
    if (mysqli_stmt_num_rows($checkStmt) > 0) {
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Email is already registered. Please login instead.'];
        mysqli_stmt_close($checkStmt);
        header('Location: auth.php');
        exit();
    }
    mysqli_stmt_close($checkStmt);

    $stmt = mysqli_prepare($conn, "INSERT INTO cregister (Cname, Cemail, Cadd, Ccontact, Cpass) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sssss', $Cname, $Cemail, $Cadd, $Ccontact, $hashedPassword);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['customer_id'] = mysqli_insert_id($conn);
        $_SESSION['customer_email'] = $Cemail;
        $_SESSION['customer_logged_in'] = true;
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Customer account created successfully.'];
        header('Location: index.php?page=1');
        exit();
    } else {
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Unable to create account. Please try again.'];
        header('Location: auth.php');
        exit();
    }

    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>
  