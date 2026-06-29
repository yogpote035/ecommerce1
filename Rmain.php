<!doctype html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
    integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">

  <title>Furniture Store</title>
  <style>
    #container {
      width: 100%;
      height: 100%;
      overflow: hidden;
    }

    #header {
      width: 15350px;
      height: 50px;
      background-color: purple;
      margin: 0px;
    }

    #header h1 {
      color: white;
      font: size 5;
    }

    #menu{
background:hotpink;
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
      background-color:cadetblue;
      padding-left: 0px;
      height: 600px;
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
      <h1 align="center"> Retail</h1>
    </div>
    <div id="menu">
      <ul>
      <li><a href="Home.html">Home</a></li>
      <li><a href="Radd.php">Add Product</a></li>
      <li><a href="Rview.php">View Product</a></li>
      <li><a href="Contact.html">Contact</a>
      </ul>
      </div>
    <div id="content">
    <?php 
    echo "<b><marquee>Hello"." ".$rname." "."Welcome to this page</marquee></b><br>";
    ?>
    <form  method="post" align="center>
     </form> 
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