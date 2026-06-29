<?php
	session_start();
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Simple Shopping Cart using Session in PHP</title>
	<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css">
    <style>
      #container {
      width: 100%;
      height: 100%;
      overflow: hidden;
    }

    #header {
      width: 2050px;
      height: 50px;
      background-color: purple;
    
    }

    #header h1 {
      color: white;
      font: size 5;
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
      height: 1660px;
      padding-top: 10px;
    }

    #footer {
display:block;
background-color: purple;
width:100%;
height: 60px;
padding-top:60px;
position:absolute;
padding-top: 10px;
      color: white;
text-align:center;

}
#footer p {
      padding-top: 10px;
      color: white;
    }
    </style>
</head>
<body>

<div id="header">
      <h1 align="center">Ecommerce</h1>
    </div>
    <div id="menu">
      <ul>
      <li><a href="Home.html"><h5>Home</h5></a></li>
      <li><a href="index.php"><h5>Product</h5></a></li>
      <li><a href="Track.php"><h5>Track</h5></a></li>
      <li><a href="Contact.html"><h5>Contact</h5></a></li>
      </ul>
      </div>
    <div id="content">
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
	<h1 class="page-header text-center">Cart Details</h1>
	<div class="row">
		<div class="col-sm-8 col-sm-offset-2">
			<?php 
			if(isset($_SESSION['message'])){
				?>
				<div class="alert alert-info text-center">
					<?php echo $_SESSION['message']; ?>
				</div>
				<?php
				unset($_SESSION['message']);
			}

			?>
			<form method="POST" action="save_cart.php">
			<table class="table table-bordered table-striped">
				<thead>
					<th>Button</th>
					<th>Name</th>
					<th>Price</th>
					<th>Quantity</th>
					<th>Subtotal</th>
				</thead>
				<tbody>
					<?php
						require_once 'db.php';
						$total = 0;
						$cartItems = [];
						if (!empty($_SESSION['cart'])) {
							$cartIds = array_map('intval', $_SESSION['cart']);
							$idList = implode(',', $cartIds);
							if (!isset($_SESSION['cart_qty'])) {
								$_SESSION['cart_qty'] = [];
							}
							foreach ($cartIds as $cartId) {
								if (!isset($_SESSION['cart_qty'][$cartId]) || (int)$_SESSION['cart_qty'][$cartId] < 1) {
									$_SESSION['cart_qty'][$cartId] = 1;
								}
							}
							$sql = "SELECT * FROM apadd WHERE Apid IN ($idList)";
							if ($query = mysqli_query($conn, $sql)) {
								while ($row = mysqli_fetch_assoc($query)) {
									$qty = $_SESSION['cart_qty'][$row['Apid']];
									$lineTotal = $qty * (float)$row['apprice'];
									$cartItems[] = [
										'id' => $row['Apid'],
										'name' => $row['apname'],
										'price' => (float)$row['apprice'],
										'qty' => $qty,
										'line_total' => $lineTotal,
									];
									$total += $lineTotal;
								}
							}
						}
						if (!empty($cartItems)) {
							foreach ($cartItems as $item) {
								?>
								<tr>
									<td>
										<a href="delete_item.php?id=<?php echo $item['id']; ?>" class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-trash"></span></a>
									</td>
									<td><?php echo htmlspecialchars($item['name']); ?></td>
									<td><?php echo number_format($item['price'], 2); ?></td>
									<td><input type="number" class="form-control" value="<?php echo $item['qty']; ?>" name="qty[<?php echo $item['id']; ?>]" min="1"></td>
									<td><?php echo number_format($item['line_total'], 2); ?></td>
								</tr>
								<?php
							}
						} else {
							?>
							<tr>
								<td colspan="5" class="text-center">No items in cart</td>
							</tr>
							<?php
						}
					?>
				<tr>
					<td colspan='4' align='right'><b>Total</b></td>
					<td><b><?php echo "".number_format($total, 2).""; ?></b></td>
				</tr>;
			</tbody>
		</table>
		<a href="index.php" class="btn btn-primary"><span class="glyphicon glyphicon-arrow-left"></span> Back</a>
		<button type="submit" class="btn btn-success" name="save">Save Changes</button>
		<a href="clear_cart.php" class="btn btn-danger"><span class="glyphicon glyphicon-trash"></span> Clear Cart</a>
		<a href="checkout.php" class="btn btn-success"><span class="glyphicon glyphicon-check"></span> Checkout</a>
		</form>
	</div>					
                               
	</div><br><br>
<center>	<h1>Order</h1>
    <form method="post" action="ins.php">
    Oid:<input type="number" name="Oid" required><br><br><br>
    Cid:<input type="number" name="Cid" required><br><br><br>
    Pid:<input type="number" name="pid" required><br><br><br>
    Cost:<input type="number" name="cost"  max="1000000" required><br><br><br>
    Source:<textarea rows="4" name="Source" col="4" required>Enter Source Address</textarea><br><br>
    Destination:<textarea rows="4" cols="15" name="Destination" required>Enter Destination Address</textarea><br><br>
    <a href="Track.php" target="_parent"><button>Submit</button></a>
    </form>
	</center>
</div>
				
<div id="footer">
      &#169;Copyrights Reserved
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