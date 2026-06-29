
<?php
session_start();
if (empty($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
if (empty($_SESSION['cart_qty'])) {
    $_SESSION['cart_qty'] = [];
}

require_once 'db.php';

$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT * FROM apadd WHERE 1=1";
if ($category !== '') {
    $categorySafe = mysqli_real_escape_string($conn, $category);
    $sql .= " AND apcategory = '$categorySafe'";
}
if ($search !== '') {
    $searchSafe = mysqli_real_escape_string($conn, $search);
    $sql .= " AND (LOWER(apname) LIKE '%$searchSafe%' OR LOWER(apbrand) LIKE '%$searchSafe%' OR LOWER(apcategory) LIKE '%$searchSafe%')";
}
$sql .= " ORDER BY Apid DESC";

$result = mysqli_query($conn, $sql);
$products = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
}
mysqli_close($conn);

$categories = ['Bags','Accessories','Clothes','Footwear','Appliances'];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
  <title>Ecommerce</title>
  <style>
    body { background: #f7f7f7; font-family: Arial, sans-serif; }
    #header { width:100%; height:70px; background:#6a1b9a; display:flex; justify-content:center; align-items:center; }
    #header h1 { color:white; margin:0; font-size:36px; font-weight:bold; }
    #menu { background: hotpink; min-height:60px; width:100%; }
    #menu ul { margin:0; padding:0; list-style:none; }
    #menu ul li { float:left; position:relative; width:25%; }
    #menu ul li a { color:blue; display:block; padding:15px 95px; text-decoration:none; }
    #menu ul li a:hover { background:purple; color:white; }
    #content { background: linear-gradient(pink, cadetblue); padding: 20px 25px 80px; min-height: 800px; }
    .sidebar { background: #fff; border-radius: 8px; padding: 15px; margin-bottom: 20px; }
    .product-card { background:#fff; border-radius: 8px; padding: 15px; margin-bottom: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.12); }
    .product-card img { width: 100%; height: 140px; object-fit: cover; border-radius: 6px; }
    .product-meta { font-size: 14px; color: #555; }
    #footer { background-color: purple; color:white; text-align:center; padding: 15px 10px; }
  </style>
</head>
<body>
  <div id="header">
    <h1>Ecommerce</h1>
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
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-3">
          <div class="sidebar">
            <h4>Categories</h4>
            <ul class="nav flex-column">
              <li class="nav-item"><a class="nav-link" href="index.php">All Products</a></li>
              <?php foreach ($categories as $cat) { ?>
                <li class="nav-item"><a class="nav-link" href="index.php?category=<?php echo urlencode($cat); ?>"><?php echo htmlspecialchars($cat); ?></a></li>
              <?php } ?>
            </ul>
          </div>
          <div class="sidebar">
            <h4>Quick Search</h4>
            <form method="get" action="index.php" class="form-inline">
              <input type="text" name="search" class="form-control mb-2" placeholder="Search products" value="<?php echo htmlspecialchars($search); ?>">
              <button type="submit" class="btn btn-primary mb-2">Search</button>
            </form>
          </div>
        </div>
        <div class="col-md-9">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Products</h3>
            <a href="view_cart.php" class="btn btn-success">Cart <span class="badge badge-light"><?php echo count($_SESSION['cart']); ?></span></a>
          </div>
          <?php if (isset($_SESSION['message'])) { echo '<div class="alert alert-info">' . $_SESSION['message'] . '</div>'; unset($_SESSION['message']); } ?>
          <?php if (!empty($products)) { ?>
            <div class="row">
              <?php foreach ($products as $row) { ?>
                <div class="col-md-6 col-lg-4">
                  <div class="product-card">
                    <img src="<?php echo !empty($row['apimage']) ? htmlspecialchars($row['apimage']) : 'images/products/accessories.jpg'; ?>" alt="Product image">
                    <h5 class="mt-2"><?php echo htmlspecialchars($row['apname']); ?></h5>
                    <p class="product-meta">Brand: <?php echo htmlspecialchars($row['apbrand']); ?></p>
                    <p class="product-meta">Category: <?php echo htmlspecialchars($row['apcategory']); ?></p>
                    <p class="product-meta">Qty: <?php echo htmlspecialchars($row['apqty']); ?></p>
                    <h4 class="text-primary">₹<?php echo number_format($row['apprice'], 2); ?></h4>
                    <a href="add_cart.php?id=<?php echo (int)$row['Apid']; ?>" class="btn btn-primary btn-block">Add to Cart</a>
                  </div>
                </div>
              <?php } ?>
            </div>
          <?php } else { ?>
            <div class="alert alert-warning">No products found for this selection.</div>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>
  <div id="footer">© Copyrights Reserved</div>
</body>
</html>
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
