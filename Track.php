<?php
session_start();
$orderId = isset($_GET['order']) ? (int)$_GET['order'] : 0;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
  <title>Ecommerce - Order Tracking</title>
  <style>
    body { background:#f7f7f7; font-family:Arial, sans-serif; }
    #header { width:100%; height:70px; background:#6a1b9a; display:flex; justify-content:center; align-items:center; }
    #header h1 { color:white; margin:0; font-size:36px; font-weight:bold; }
    #menu { background:hotpink; min-height:60px; width:100%; }
    #menu ul { margin:0; padding:0; list-style:none; }
    #menu ul li { float:left; position:relative; width:25%; }
    #menu ul li a { color:blue; display:block; padding:15px 95px; text-decoration:none; }
    #menu ul li a:hover { background:purple; color:white; }
    #content { background:linear-gradient(pink, cadetblue); padding:20px 25px 80px; min-height:800px; }
    #footer { background-color: purple; color:white; text-align:center; padding:15px 10px; }
  </style>
</head>
<body>
  <div id="header"><h1>Ecommerce</h1></div>
  <div id="menu">
    <ul>
      <li><a href="Home.html"><h5>Home</h5></a></li>
      <li><a href="index.php"><h5>Product</h5></a></li>
      <li><a href="Track.php"><h5>Track Product</h5></a></li>
      <li><a href="Contact.html"><h5>Contact</h5></a></li>
    </ul>
  </div>
  <div id="content">
    <div class="container">
      <h2 class="text-center mb-4">Order Tracking</h2>
      <?php if (isset($_SESSION['message'])) { echo '<div class="alert alert-success">' . $_SESSION['message'] . '</div>'; unset($_SESSION['message']); } ?>
      <form method="get" action="Track.php" class="mb-4">
        <div class="input-group">
          <input type="number" name="order" class="form-control" placeholder="Enter tracking ID" value="<?php echo (int)$orderId; ?>">
          <div class="input-group-append"><button class="btn btn-primary" type="submit">Track</button></div>
        </div>
      </form>
      <?php include 'Tracks.php'; ?>
    </div>
  </div>
  <div id="footer">© Copyrights Reserved</div>
</body>
</html>