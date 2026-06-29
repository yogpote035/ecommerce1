<?php
$servername = "localhost";
$username = "root";
$password = "Yogeshpo7@";
$dbname = "ecommerce";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

/* REGISTER */
if (isset($_POST['register'])) {

    $aname = $_POST["aname"];
    $aadd = $_POST["aadd"];
    $apass = $_POST["apass"];

    $sql = "INSERT INTO aregister (aname, aadd, apass)
            VALUES ('$aname', '$aadd', '$apass')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Admin Registered Successfully!');</script>";
    } else {
        echo "<script>alert('Registration Failed');</script>";
    }
}

/* LOGIN */
if (isset($_POST['login'])) {

    $aname = $_POST["aname"];
    $apass = $_POST["apass"];

    $sql = "SELECT * FROM aregister 
            WHERE aname='$aname' AND apass='$apass'";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        echo "<script>alert('Login Successful');</script>";
        // header("Location: adminpanel.php"); // optional
    } else {
        echo "<script>alert('Invalid Username or Password');</script>";
    }
}
?>

<!doctype html>
<html lang="en">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Ecommerce - Admin</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">

<style>

/* HEADER */
#header {
    width: 100%;
    height: 70px;
    background: #6a1b9a;
    display: flex;
    justify-content: center;
    align-items: center;
}

#header h1 {
    color: white;
    margin: 0;
    font-size: 36px;
    font-weight: bold;
}

/* MENU */
#menu {
    background: hotpink;
    min-height: 60px;
    width: 100%;
}

#menu ul {
    margin: 0;
    padding: 0;
    list-style: none;
    display: flex;
}

#menu ul li {
    width: 25%;
    text-align: center;
}

#menu ul li a {
    color: blue;
    display: block;
    padding: 15px;
    text-decoration: none;
    font-weight: bold;
}

#menu ul li a:hover {
    background: purple;
    color: white;
}

/* CONTENT */
#content {
    background: linear-gradient(pink, cadetblue);
    min-height: 600px;
    padding: 40px;
}

/* FOOTER */
#footer {
    background: purple;
    height: 45px;
    text-align: center;
    color: white;
    padding-top: 10px;
}

</style>

</head>

<body>

<!-- HEADER -->
<div id="header">
    <h1>Ecommerce Admin</h1>
</div>

<!-- MENU -->
<div id="menu">
    <ul>
        <li><a href="Home.html">Home</a></li>
        <li><a href="Alogin1.php">Admin</a></li>
        <li><a href="Clogin.php">Customer</a></li>
        <li><a href="Contact.html">Contact</a></li>
    </ul>
</div>

<!-- CONTENT -->
<div id="content">

<div class="container">

<div class="row">

<!-- LOGIN -->
<div class="col-md-6">
<h2 class="text-center mb-3">Admin Login</h2>

<form method="POST">
    <input type="text" name="aname" class="form-control mb-2" placeholder="Admin Name" required>
    <input type="password" name="apass" class="form-control mb-2" placeholder="Password" required>

    <button type="submit" name="login" class="btn btn-primary btn-block">
        Login
    </button>
</form>
</div>

<!-- REGISTER -->
<div class="col-md-6">
<h2 class="text-center mb-3">Admin Register</h2>

<form method="POST">
    <input type="text" name="aname" class="form-control mb-2" placeholder="Admin Name" required>
    <input type="text" name="aadd" class="form-control mb-2" placeholder="Address" required>
    <input type="password" name="apass" class="form-control mb-2" placeholder="Password" required>

    <button type="submit" name="register" class="btn btn-success btn-block">
        Register
    </button>
</form>
</div>

</div>

</div>

</div>

<!-- FOOTER -->
<div id="footer">
    © Copyrights Reserved
</div>

</body>
</html>