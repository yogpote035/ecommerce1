<?php
require_once 'init.php';
$siteTitle = 'Order Tracking';
$orderId = (int)($_GET['order'] ?? $_GET['order_id'] ?? $_GET['oid'] ?? 0);
include 'templates/header.php';
?>
<div class="row">
  <div class="col-12">
    <section class="card sidebar-card shadow-sm mb-4">
      <div class="card-body">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
          <div>
            <h1 class="h4 mb-2">Order Tracking</h1>
            <p class="text-secondary mb-0">Enter your tracking ID to see the latest order status.</p>
          </div>
          <a href="index.php" class="btn btn-secondary mt-3 mt-md-0">Back to Shop</a>
        </div>

        <?php if (!empty($_SESSION['message'])): ?>
          <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['message']); ?></div>
          <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <form method="get" action="Track.php" class="mb-4">
          <div class="input-group">
            <input type="number" name="order" class="form-control" placeholder="Enter tracking ID" value="<?php echo $orderId; ?>" aria-label="Tracking ID">
            <button class="btn btn-primary" type="submit">Track</button>
          </div>
        </form>

        <?php include 'Tracks.php'; ?>
      </div>
    </section>
  </div>
</div>

<?php include 'templates/footer.php';
