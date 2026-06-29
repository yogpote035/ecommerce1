<?php
// include_once 'db.php';
$servername = "localhost";
$username = "root";
$password = "Yogeshpo7@";
$dbname = "retailler";
$conn = mysqli_connect($servername, $username, $password, $dbname);

if ($_POST) {
	$orid=$_POST["Oid"];
  $pid = $_POST["pid"];
	$cid = $_POST["Cid"];
	$cost = $_POST["cost"];
	$source =  $_POST["Source"];	
    $destination =  $_POST["Destination"];
 
       $sql = "INSERT into orders(oid,pid,cid,cost,source,destination) VALUES('" .$orid.  "','" .$pid.  "', '" . $cid . "', '" . $cost. "' ,'".$source ."','".$destination ."')";
       if (mysqli_query($conn, $sql)) {
    echo "<script>alert('Product added successfully !');</script>";
    header("Location: Track.php");
       }
       else { 
            echo "Error: " . $sql . " " . mysqli_error($conn);
        }
  mysqli_close($conn);
    }
    else{
        echo("Error");
    }
  
  
  ?>  


