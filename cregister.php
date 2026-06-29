<?php
include_once("db.php");
session_start();
if(isset($_SESSION['cid'])) {
	header("Location: cmain.html");
}
$error = false;
if (isset($_POST['clogin'])) {
    $cid=$_POST['Cid'];
	$Cname = $_POST['Cname'];
	$Cadd = $_POST['Cadd'];
    $Ccontact=$_POST['Ccontact'];
	$Cpass = $_POST['Cpass'];
	$Cconpass =  $_POST['Cconpass'];	
	if (!preg_match("/^[a-zA-Z ]+$/",$Cname)) {
		$error = true;
		$uname_error = "Name must contain only alphabets and space";
	}
	
	if(strlen($Cpass) < 6) {
		$error = true;
		$rpass_error = "Password must be minimum of 6 characters";
	}
	if($Cpass != $Cconpass) {
		$error = true;
		$rconpass = "Password and Confirm Password doesn't match";
	}
	if (!$error) {
		if(mysqli_query($conn, "INSERT INTO Cregister(Cid,Cname, Cadd,Ccontact,Cpass,Cconpass) VALUES('" .$Cid.  "','" .$Cname.  "', '" . $Cadd . "','" .$Ccontact.  "', '" . $Cpass."' ,'".$Cconpass ."')")) {
			$success_message = "Successfully Registered! <a href='cmain.html'>Registered Successfully!</a>";
		} else {
			$error_message = "Error in registering...Please try again later!";
		}
	}
}
?>