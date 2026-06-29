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
	$password = $_POST["apass"];

       $sql = "INSERT into aregister(aname, aadd, apass) VALUES('" .$aname.  "', '" . $aadd . "', '" . $apassword. "')";
       if (mysqli_query($conn, $sql)) {
    echo '<script>alert("New record created successfully !")</script>';

  }
  else{  echo "Error: " . $sql . "" . mysqli_error($conn);}
  mysqli_close($conn);}
  ?>  
  
<!doctype html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
    integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">

  <title>Ecommerce</title>
  <style>
    #container {
      width: 100%;
      height: 100%;
      overflow: hidden;
    }

    #header{
    width:100%;
    height:70px;
    background:#6a1b9a;
    display:flex;
    justify-content:center;
    align-items:center;
}

#header h1{
    color:white;
    margin:0;
    font-size:36px;
    font-weight:bold;
    font-family:Arial, sans-serif;
}

    #menu{
background:hotpink;
min-height:60px;
width:100%;
}
#menu ul{
margin:0;
padding:0;
list-style:none;
}
#menu ul li{
float:left;
position:relative;
width:25%;
}
#menu ul li a{
color:blue;
display:block;
padding:15px 95px;
text-decoration:none;
}
#menu ul li a:hover{
background:purple;
color:white;
}
#menu ul li ul{
position:absolute;
    z-index: 99999;
left:9999px;
background:hotpink;
}
#menu ul li:hover ul{
left:0;
display:block;
}
#menu ul li ul li{
float:none;
width:250px;
}
#menu ul li ul li a{
background:hotpink;
color:blue;
padding:15px 85px;
}
    #content {
      background-image: linear-gradient(pink, cadetblue);
      padding-left: 0px;
      height: 850px;
    }
    #content pre {
      color: darkblue;
      padding-top: 10px;
    }

    #footer {
      background-color: purple;
width:100%;
      height: 45px;
margin:245px 0px 0px 0px;
bottom:0;
top:673px;
position:absolute;

}

    #footer p {
      padding-top: 10px;
      color: white;
    }
  </style>
</head>

<body>
  <div id="container">
    <div id="header">
      <h1 align="center">Ecommerce</h1>
    </div>
    <div id="menu">
      <ul>
      <li><a href="Home.html"><h5>Home</h5></a></li>
      <li><a href="Alogin.php"><h5>Admin</h5></a></li>
      <li><a href="Clogin.php"><h5>Customer</h5></a></li>
      <li><a href="Contact.html"><h5>Contact</h5></a></li>
      </ul>
      </div>
    <div id="content">
      <br><br><br><br><br>
   <div class="login-box">
  <div class="row">
      <div class="col mid-6">
            <h1>Login</h1>
   <form action="validation.php" method="POST">
           <div class="form-group">
            <h4> Aname</h4><input type="text" name="aname" class="form-control" required>
           </div>
             <div class="form-group">
     <h4>Apass</h4><input type="password" name="apass" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
         </form>
       </div>
       <div class="col mid-6">
        <h1>Register</h1>
        <form action="All.php" method="POST">
          <div class="form-group">
            <h4>Aname</h4><input type="text" name="aname" class="form-control" required>
          </div>
          <div class="form-group">
             <h4>Aadd</h4><input type="text" name="aadd" class="form-control" required>
           </div>
           <div class="form-group">
             <h4>Apass</h4><input type="password" name="apass" class="form-control" required>
           </div>
           <a href="Aadd.html"><button type="submit" class="btn btn-primary" >Register</button></a>
        </form>
      </div>
     </div>
      </div>
     

      
    <div id="footer">
      <p align="center">&#169;Copyrights Reserved</a></p>
    </div>
    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: jQuery and Bootstrap Bundle (includes Popper) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
      integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
      crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx"
      crossorigin="anonymous"></script>

    <!-- Option 2: jQuery, Popper.js, and Bootstrap JS
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.min.js" integrity="sha384-w1Q4orYjBQndcko6MimVbzY0tgp4pWB4lZ7lr30WKz0vr/aWKhXdBNmNb5D92v7s" crossorigin="anonymous"></script>
    -->
  </div>
</body>
</html>