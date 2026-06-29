<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $Cid = trim($_POST['id'] ?? '');
  $Cname = trim($_POST['name'] ?? '');
  $Cemail = trim($_POST['c_email'] ?? '');
  $Cadd = trim($_POST['c_add'] ?? '');
  $Ccontact = trim($_POST['c_contact'] ?? '');
  $Cpass = trim($_POST['c_pass'] ?? '');

  $sql = "INSERT INTO cregister(Cid, Cname, Cemail, Cadd, Ccontact, Cpass) VALUES('$Cid', '$Cname', '$Cemail', '$Cadd', '$Ccontact', '$Cpass')";
  if (mysqli_query($conn, $sql)) {
    $_SESSION['customer_id'] = $Cid;
    $_SESSION['customer_logged_in'] = true;
    header('Location: index.php');
    exit();
  } else {
    echo 'Error: ' . mysqli_error($conn);
  }
}

mysqli_close($conn);
?>
  