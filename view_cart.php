<?php
require_once 'init.php';
$siteTitle = 'Shopping Cart';
include 'templates/header.php';
?>
<div class="row gx-4 gy-4">
  <div class="col-lg-8">
    <section class="card sidebar-card shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-4 flex-column flex-md-row">
          <div>
            <h1 class="h4 mb-1">Your shopping cart</h1>
            <p class="text-secondary mb-0">Review, update quantities, or continue shopping before checkout.</p>
          </div>
          <div class="d-flex align-items-center mt-3 mt-md-0" style="flex-wrap: nowrap; gap: 0.6rem;">
            <a href="index.php?page=1" class="btn btn-outline-secondary btn-sm px-2 py-1" style="white-space: nowrap; font-size: 0.8rem;">Continue shopping</a>
            <a href="checkout.php" class="btn btn-primary btn-sm px-2 py-1" style="white-space: nowrap; font-size: 0.8rem;">Proceed to Checkout</a>
          </div>
        </div>

        <?php if (!empty($_SESSION['message'])): ?>
          <div class="alert alert-info text-center"><?php echo htmlspecialchars($_SESSION['message']); ?></div>
          <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <?php
        $total = 0;
        $cartItems = [];
        if (!empty($_SESSION['cart'])) {
            $cartIds = array_map('intval', $_SESSION['cart']);
            $idList = implode(',', $cartIds);
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
        ?>

        <form method="POST" action="save_cart.php">
          <?php if (!empty($cartItems)): ?>
            <div class="table-responsive">
              <table class="table table-striped align-middle mb-4">
                <thead>
                  <tr>
                    <th>Action</th>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($cartItems as $item): ?>
                    <tr>
                      <td>
                        <a href="delete_item.php?id=<?php echo $item['id']; ?>" class="btn btn-danger btn-sm">Remove</a>
                      </td>
                      <td><?php echo htmlspecialchars($item['name']); ?></td>
                      <td>₹<?php echo number_format($item['price'], 2); ?></td>
                      <td>
                        <input type="number" class="form-control form-control-sm w-25" value="<?php echo $item['qty']; ?>" name="qty[<?php echo $item['id']; ?>]" min="1">
                      </td>
                      <td>₹<?php echo number_format($item['line_total'], 2); ?></td>
                    </tr>
                  <?php endforeach; ?>
                  <tr>
                    <td colspan="4" class="text-end fw-bold">Total</td>
                    <td class="fw-bold">₹<?php echo number_format($total, 2); ?></td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="d-flex flex-wrap gap-2">
              <button type="submit" class="btn btn-success mx-2" name="save">Update cart</button>
              <a href="clear_cart.php" class="btn btn-danger">Clear cart</a>
            </div>
          <?php else: ?>
            <div class="alert alert-warning text-center">
              Your cart is empty. <a href="index.php" class="font-weight-bold">Browse products</a> to get started.
            </div>
          <?php endif; ?>
        </form>
      </div>
    </section>
  </div>

  <div class="col-lg-4">
    <aside class="card sidebar-card shadow-sm h-100">
      <div class="card-body">
        <h2 class="h5 mb-3">Cart summary</h2>
        <p class="text-secondary mb-4">Keep your order details in view as you edit quantities and review costs.</p>
        <ul class="list-group mb-4">
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span>Items</span>
            <span><?php echo count($cartItems); ?></span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span>Subtotal</span>
            <span>₹<?php echo number_format($total, 2); ?></span>
          </li>
        </ul>
        <a href="checkout.php" class="btn btn-primary w-100<?php echo empty($cartItems) ? ' disabled' : ''; ?>">Proceed to Checkout</a>
      </div>
    </aside>
  </div>
</div>

<?php include 'templates/footer.php';
