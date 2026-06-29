<?php
// include_once 'db.php';
$servername = "localhost";
$username = "root";
$password = "Yogeshpo7@";
$dbname = "ecommerce";
$conn = mysqli_connect($servername, $username, $password, $dbname);

if ($_POST) {
	$Apid=$_POST["Apid"];
  $apname = $_POST["apname"];
	$apbrand = $_POST["apbrand"];
	$apqty = $_POST["apqty"];
	$apprice =  $_POST["apprice"];	
 
 
       $sql = "Delete from apadd where Apid=" .$Apid;  
       if (mysqli_query($conn, $sql)) {
    echo '<script>alert("Product deleted successfully !")</script>';

  }
  else {  echo "Error: " . $sql . "" . mysqli_error($conn);}
  mysqli_close($conn);}
  ?>  