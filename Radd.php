<?php
// include_once 'db.php';
$servername = "localhost";
$username = "root";
$password = "Yogeshpo7@";
$dbname = "retailler";
$conn = mysqli_connect($servername, $username, $password, $dbname);

if ($_POST) {
	$Rpid=$_POST["rpid"];
  $Rpname = $_POST["rpname"];
	$Rpbrand = $_POST["rpbrand"];
	$Rpqty = $_POST["rpqty"];
	$Rpprice =  $_POST["rpprice"];	
 
 
       $sql = "INSERT into rpadd(rpid,rpname, rpbrand, rpqty,rpprice) VALUES('" .$Rpid.  "','" .$Rpname.  "', '" . $Rpbrand . "', '" . $Rpqty. "' ,'".$Rpprice ."')";
       if (mysqli_query($conn, $sql)) {
    echo '<script>alert("Product added successfully !")</script>';

  }
  else {  echo "Error: " . $sql . "" . mysqli_error($conn);}
  mysqli_close($conn);}
  ?>  





