<?php
// include_once 'db.php';
$servername = "localhost";
$username = "root";
$password = "Yogeshpo7@";
$dbname = "Ecommerce";
$conn = mysqli_connect($servername, $username, $password, $dbname);

if ($_POST) {
	$Apid=$_POST["apid"];
  $Apname = $_POST["apname"];
	$Apbrand = $_POST["apbrand"];
	$Apqty = $_POST["apqty"];
	$Apprice =  $_POST["apprice"];	
 
 echo "<tr>";
       $sql = "SELECT from apadd(apid,apname, apbrand, apqty,apprice) VALUES('" .$Apid.  "','" .$Apname.  "', '" . $Apbrand . "', '" . $Apqty. "' ,'".$Apprice ."')";
       if (mysqli_query($conn, $sql)) {
    echo '<script>alert("Product added successfully !")</script>';

  } echo "</tr>";
  else {  echo "Error: " . $sql . "" . mysqli_error($conn);}
  mysqli_close($conn);}
  ?>  