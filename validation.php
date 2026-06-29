<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "Yogeshpo7@";
$dbname = "Ecommerce";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $aname = $_POST["aname"];
    $apass = $_POST["apass"];

    $sql = "SELECT * FROM aregister
            WHERE aname='$aname'
            AND apass='$apass'";

    $result = mysqli_query($conn, $sql);

    if ($result) {

        if (mysqli_num_rows($result) > 0) {

            $_SESSION['aname'] = $aname;
            echo "<script>alert('Logged in Successfully');</script>";

        } else {

            echo "<script>alert('Invalid Username or Password');</script>";
            exit();

        }

    } else {

        echo "SQL Error : " . mysqli_error($conn);

    }
}

mysqli_close($conn);
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
    html, body {
      min-height: 100%;
      margin: 0;
      padding: 0;
      overflow-x: hidden;
    }

    body {
      overflow-y: auto;
    }

    #container {
      width: 100%;
      min-height: 100vh;
      overflow: visible;
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
      background:#ff69b4;
      min-height:54px;
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
      color:#1a1a66;
      display:block;
      padding:15px 95px;
      text-decoration:none;
      font-weight:700;
    }
    #menu ul li a:hover{
      background:#6a1b9a;
      color:white;
    }
    #menu ul li ul{
      position:absolute;
      z-index: 99999;
      left:9999px;
      background:#ff99d4;
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
      background:#ff99d4;
      color:#1a1a66;
      padding:15px 85px;
    }
    #content {
      background-image: linear-gradient(135deg, #ffd5ea, #d4c4ff);
      padding: 30px 20px 40px;
      min-height: calc(100vh - 130px);
      overflow: visible;
    }

    .add-box {
      max-width: 760px;
      margin: 0 auto;
      padding: 24px;
      background: rgba(255,255,255,0.96);
      border-radius: 18px;
      box-shadow: 0 10px 28px rgba(106, 27, 154, 0.18);
      overflow: auto;
      max-height: calc(100vh - 180px);
      border: 1px solid rgba(255,255,255,0.9);
      border: 1px solid rgba(255,255,255,0.9);
    }

    .add-box form {
      display: block;
    }
    #content pre {
      color: darkblue;
      padding-top: 10px;
    }

    #footer {
      background-color: purple;
      height: 45px;
      margin-top: 0px;
    }

    #footer p {
      padding-top: 15px;
      color: white;
    }
  </style>
</head>

<body>
  <div id="container">
    <div id="header">
      <h1 style="align: center;">Ecommerce</h1>
    </div>
    <div id="menu">
      <ul>
      <li><a href="Home.html">Home</a></li>
      <li><a href="validation.php">Product</a></li>
      <li><a href="Aview.php">View Product</a></li>
      <li><a href="Contact.html">Contact</a>
      </ul>
      </div>
    <div id="content">
    <?php
if(isset($_SESSION['aname']))
{
    echo "<h4><b><marquee>Hello ".$_SESSION['aname']." Welcome to this page</marquee></b></h4>";
}
?>
     <div class="add-box">                             
  <div class="row">                                  
      <div class="col-12">                         
            <h1>Product</h1>                    
   <form method="post" action="Aadd.php" enctype="multipart/form-data">
           <div class="form-group">
             <h4>Apid:</h4><input type="number" name="Apid" class="form-control" required>
           </div>
             <div class="form-group">
     <h4>Apname</h4><input type="text" name="apname" class="form-control" required>
            </div>
            <div class="form-group">
            <h4>Abrand</h4><input type="text" name="apbrand" class="form-control" required>
      </div>
              <div class="form-group">
    <h4>Category</h4>

    <select name="apcategory" class="form-control">

        <option>Bags</option>
        <option>Clothes</option>
        <option>Appliances</option>
        <option>Footwear</option>
        <option>Accessories</option>

    </select>

</div>
          <div class="form-group">
             <h4>Apqty</h4><input type="number" name="apqty" class="form-control" required>
           </div>
           <div class="form-group">
             <h4>Apprice</h4><input type="number" name="apprice" class="form-control" required>
           </div>
<div class="form-group">

<h4>Product Image</h4>

<input
type="file"
name="apimage"
class="form-control"
required>

</div>
           <div>
             <button type="submit" class="btn btn-primary"><h4>Add</h4></button>
             <button type="submit" class="btn btn-primary" formaction="Adel.php"><h4>Delete</h4></button>
             <button type="submit" class="btn btn-primary" formaction="Aupdate.php"><h4>Update</h4></button>
            </div>
          </form>
       </div>
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