<?php
// include_once 'db.php';
$servername = "localhost";
$username = "root";
$password = "Yogeshpo7@";
$dbname = "ecommerce";
$conn = mysqli_connect($servername, $username, $password, $dbname);

if ($_POST) {
	$aname = $_POST["aname"];
	$aadd = $_POST["aadd"];
	$aassword = $_POST["apass"];

       $sql = "INSERT into aregister(aname, aadd, apass) VALUES('" .$aname.  "', '" . $aadd . "', '" . $password. "')";
       if (mysqli_query($conn, $sql)) {
    echo '<script>alert("Registered Successfully !")</script>';
   
  }
  else{  echo "Error: " . $sql . "" . mysqli_error($conn);}
  mysqli_close($conn);}
  ?>  