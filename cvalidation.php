<?php
session_start();
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cid = trim($_POST["Cid"] ?? '');
    $Cpass = trim($_POST["Cpass"] ?? '');

    $sql = "SELECT * FROM cregister WHERE Cid='$cid' AND Cpass='$Cpass'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $_SESSION['customer_id'] = $cid;
        $_SESSION['customer_logged_in'] = true;
        header('Location: index.php');
        exit();
    } else {
        echo "<script>alert('Invalid Customer ID or Password'); window.location='Clogin.php';</script>";
    }
}

mysqli_close($conn);
?>