<?php
include_once("db.php");
session_start();
if(isset($_SESSION['rname'])) {
	header("Location: Rmain.php");
}
$error = false;
if (isset($_POST['Rlogin'])) {
	$rname = $_POST['rname'];
	$radd = $_POST['radd'];
	$password = $_POST['rpass'];
	$cpassword =  $_POST['rconpass'];	
	if (!preg_match("/^[a-zA-Z ]+$/",$rname)) {
		$error = true;
		$uname_error = "Name must contain only alphabets and space";
	}
	
	if(strlen($rpass) < 6) {
		$error = true;
		$rpass_error = "Password must be minimum of 6 characters";
	}
	if($rpass != $rconpass) {
		$error = true;
		$rconpass = "Password and Confirm Password doesn't match";
	}
	if (!$error) {
		if(mysqli_query($conn, "INSERT INTO rregister(rname, radd, rpass,rconpass) VALUES('" .$rname.  "', '" . $radd . "', '" . $rpass."' ,'".$rconpass ."')")) {
			$success_message = "Successfully Registered! <a href='Rmain.php'>Click here to Login</a>";
		} else {
			$error_message = "Error in registering...Please try again later!";
		}
	}
}
?>