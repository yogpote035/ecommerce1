<?php
// include_once 'db.php';
$servername = "localhost";
$username = "root";
$password = "Yogeshpo7@";
$dbname = "retailler";
$conn = mysqli_connect($servername, $username, $password, $dbname);

if ($_POST) {
	$rname = $_POST["rname"];
	$radd = $_POST["radd"];
	$password = $_POST["rpass"];

       $sql = "INSERT into rregister(rname, radd, rpass) VALUES('" .$rname.  "', '" . $radd . "', '" . $password. "')";
       if (mysqli_query($conn, $sql)) {
    echo '<script>alert("Registered Successfully !")</script>';
   
  }
  else{  echo "Error: " . $sql . "" . mysqli_error($conn);}
  mysqli_close($conn);}
  ?>  