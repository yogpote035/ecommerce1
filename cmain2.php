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
      font:large;
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
   #content::after{
     clear:both;
   }
     #left{
       
       background-color:pink;
       height:812px;
       width:20%;
       float:left;
     }
    
     #left ul {
  list-style-type: none;
  margin: 0;
  padding: 20px;
  width: 200px;
}

#left ul li a {
  display: block;
  color:blue;
  padding: 40px 70px;
  text-decoration: none;
}

/* Change the link color on hover */
#left ul li a:hover {
  background-color: orange;
  color: blue;
  width:253px;
}
     #right{
     background-color:blue;
       height:812px;
       width:80%;
       float:right;
      
     }
     
     .product_image{
			height:150px;
      width:250px;
      padding-left:50px;
		}
    .product_id{
      padding-left:110px;
      padding-top:99px;
    }
		.product_name{
			height:80px; 
			padding-left:20px; 
			padding-right:20px;
		}
    .product_brand{
      padding-left:97px; 
			margin-top:-48px;
		}
    .product_qty{
      padding-left:97px; 
			margin-top:-4px;
		}
		.product_price{
      padding-left:95px; 
			margin-top:-4px;
		}
     .product_cart{
       padding-left:95px;
     }
    
    #footer {      background-color: purple;
    padding-right:15px;
      width:100%;
      height: 45px;
      margin: -41px;
      margin-left:-410px;
      position:absolute;
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
      <li><a href="Radd.html">Product</a></li>
      <li><a href="Track.php">Track</a></li>
      <li><a href="Contact.html">Contact</a>
      </ul>
      </div>  
      <div id="content">             
     <div id="left">
  <h2><b><font color="blue">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>Categories</u></font></b></h2>
  <ul>   
  <li><a href="cmain.php"><h4>Clothes</h4></a></li>
  <li><a href="#news"><h4>Accessories</h4></a></li>
  <li><a href="#contact"><h4>Eappliances</h4></a></li>
  <li><a href="#about"><h4>Footwear</h4></a></li>
</ul>
     </div>
     <div id="right">
     <?php
	session_start();
	//initialize cart if not set or is unset
	if(!isset($_SESSION['cart'])){
		$_SESSION['cart'] = array();
	}

	//unset qunatity
	unset($_SESSION['pqty']);
?>

	
<nav class="navbar navbar-default">
	  <div class="container-fluid">
	    <div class="navbar-header">
	      <a class="navbar-brand" href="#">Simple Shopping Cart</a>
	    </div>
	      <ul class="nav navbar-nav navbar-right">
	      	<li><a href="view_cart.php"><span class="badge"><?php echo count($_SESSION['cart']); ?></span> Cart <span class="glyphicon glyphicon-shopping-cart"></span></a></li>
	      </ul>
	  
	  </div>
	</nav>
	
	<?php
		//info message
		if(isset($_SESSION['message'])){
			?>
			<div class="row">
				<div class="col-sm-6 col-sm-offset-6">
					<div class="alert alert-info text-center">
						<?php echo $_SESSION['message']; ?>
					</div>
				</div>
			</div>
			<?php
			unset($_SESSION['message']);
		}
		//end info message
		//fetch our products	
		//connection
		$conn = new mysqli('localhost', 'root', '', 'retailler');

		$sql = "SELECT * FROM product";
		$query = $conn->query($sql);
		$inc = 20;
		while($row = $query->fetch_assoc()){
			$inc = ($inc == 20) ? 1 : $inc + 1; 
			if($inc == 1) echo "<div class='row text-center'>";  
			?>
   <div class="col-md-4">
				<div class="panel panel-default">
					<div class="panel-body">
						<div class="row product_image">
							<img src="<?php echo $row['pimage'] ?>" width="90px" height="auto">
						</div>
            <div class="row product_pid">
							<h4><?php echo $row['pid']; ?></h4>
						</div>
						<div class="row product_name">
							<h4><?php echo $row['pname']; ?></h4>
						</div>
            <div class="row product_brand">
							<h4><?php echo $row['pbrand']; ?></h4>
						</div>
            <div class="row product_qty">
							<h4><?php echo $row['pqty']; ?></h4>
						</div>
            <div class="row product_price">
							<h4><?php echo $row['pprice']; ?></h4>
						</div>
						<div class="row product_cart">	
						<span><a href="add_cart.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm"><span class="glyphicon glyphicon-plus"></span> Cart</a></span>
						</div>
            <br><br>
					</div>
				</div>
			</div>
      
     
      
      

     
			
           
						
    
			<?php
		}
		if($inc == 1) echo "<div></div><div></div><div></div></div>"; 
		if($inc == 2) echo "<div></div><div></div></div>"; 
		if($inc == 3) echo "<div></div></div>";
		
		//end product row 
	?>
</div>


    
    
    <div id="footer">
      <p align="center">Copyrights Reserved</a></p>
    </div>
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
  
</body>
</html>