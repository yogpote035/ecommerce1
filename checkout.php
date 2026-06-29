<?php
session_start();
if (empty($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
if (empty($_SESSION['cart_qty'])) {
    $_SESSION['cart_qty'] = [];
}

require_once 'db.php';

$cartItems = [];
$total = 0;
if (!empty($_SESSION['cart'])) {
    $ids = array_map('intval', $_SESSION['cart']);
    $idList = implode(',', $ids);
    $query = mysqli_query($conn, "SELECT * FROM apadd WHERE Apid IN ($idList)");
    if ($query) {
        while ($row = mysqli_fetch_assoc($query)) {
            $qty = isset($_SESSION['cart_qty'][$row['Apid']]) ? (int)$_SESSION['cart_qty'][$row['Apid']] : 1;
            $lineTotal = $qty * (float)$row['apprice'];
            $total += $lineTotal;
            $cartItems[] = [
                'id' => $row['Apid'],
                'name' => $row['apname'],
                'price' => (float)$row['apprice'],
                'qty' => $qty,
                'line_total' => $lineTotal,
            ];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerName = trim($_POST['customer_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $paymentMethod = trim($_POST['payment_method'] ?? 'Cash on Delivery');

    $firstProductId = !empty($cartItems) ? $cartItems[0]['id'] : 0;
    $source = 'Customer: ' . mysqli_real_escape_string($conn, $customerName) . ' | Address: ' . mysqli_real_escape_string($conn, $address) . ' | Contact: ' . mysqli_real_escape_string($conn, $contact);
    $destination = 'Payment: ' . mysqli_real_escape_string($conn, $paymentMethod);

    $sql = "INSERT INTO orders (pid, cid, cost, source, destination) VALUES ('" . (int)$firstProductId . "', '" . (int)($_SESSION['customer_id'] ?? 0) . "', '" . (float)$total . "', '" . $source . "', '" . $destination . "')";
    if (mysqli_query($conn, $sql)) {
        $orderId = mysqli_insert_id($conn);
        $_SESSION['last_order_id'] = $orderId;
        $_SESSION['message'] = 'Order placed successfully! Your tracking ID is #' . $orderId;
        $_SESSION['cart'] = [];
        $_SESSION['cart_qty'] = [];
        header('Location: Track.php?order=' . urlencode($orderId));
        exit();
    } else {
        $errorMessage = 'Unable to place order. Please try again later. Error: ' . mysqli_error($conn);
    }
}
mysqli_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Checkout</title>
    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css">
    <style>
        body { background:#f7f7f7; font-family:Arial, sans-serif; }
        #header { width:100%; height:70px; background:#6a1b9a; display:flex; justify-content:center; align-items:center; }
        #header h1 { color:white; margin:0; font-size:36px; font-weight:bold; }
        #menu { background:hotpink; min-height:60px; width:100%; }
        #menu ul { margin:0; padding:0; list-style:none; }
        #menu ul li { float:left; position:relative; width:25%; }
        #menu ul li a { color:blue; display:block; padding:15px 95px; text-decoration:none; }
        #menu ul li a:hover { background:purple; color:white; }
        #content { background: linear-gradient(pink, cadetblue); padding: 20px 25px 80px; min-height: 800px; }
        #footer { background-color: purple; color:white; text-align:center; padding:15px 10px; }
    </style>
</head>
<body>
<div id="header"><h1>Ecommerce</h1></div>
<div id="menu">
    <ul>
        <li><a href="Home.html"><h5>Home</h5></a></li>
        <li><a href="index.php"><h5>Product</h5></a></li>
        <li><a href="Track.php"><h5>Track</h5></a></li>
        <li><a href="Contact.html"><h5>Contact</h5></a></li>
    </ul>
</div>
<div id="content">
    <div class="container">
        <h2 class="text-center mb-4">Checkout</h2>
        <?php if (!empty($errorMessage)) { echo '<div class="alert alert-danger">' . $errorMessage . '</div>'; } ?>
        <?php if (empty($cartItems)) { ?>
            <div class="alert alert-warning">Your cart is empty. <a href="index.php">Browse products</a>.</div>
        <?php } else { ?>
            <div class="row">
                <div class="col-md-7">
                    <div class="card p-3">
                        <h4>Order Summary</h4>
                        <ul class="list-group">
                            <?php foreach ($cartItems as $item) { ?>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><?php echo htmlspecialchars($item['name']); ?> × <?php echo (int)$item['qty']; ?></span>
                                    <span>₹<?php echo number_format($item['line_total'], 2); ?></span>
                                </li>
                            <?php } ?>
                        </ul>
                        <h4 class="mt-3 text-right">Total: ₹<?php echo number_format($total, 2); ?></h4>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card p-3">
                        <h4>Payment & Shipping</h4>
                        <form method="post" action="checkout.php">
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="customer_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Address</label>
                                <textarea name="address" class="form-control" required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Contact Number</label>
                                <input type="tel" name="contact" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Payment Method</label>
                                <select name="payment_method" class="form-control">
                                    <option>Cash on Delivery</option>
                                    <option>Card</option>
                                    <option>UPI</option>
                                    <option>Wallet</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success btn-block">Pay Now & Place Order</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
<div id="footer">© Copyrights Reserved</div>
</body>
</html>