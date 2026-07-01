<?php
require_once 'init.php';
$siteTitle = 'Payment Cancelled';
$orderId = isset($_GET['order']) ? (int)$_GET['order'] : 0;
include 'templates/header.php';
?>
<div class="row">
  <div class="col-12">
    <section class="card sidebar-card shadow-sm mb-4">
      <div class="card-body">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
          <div>
            <h1 class="h4 mb-2">Payment Cancelled</h1>
            <p class="text-secondary mb-0">Your payment was cancelled from the Razorpay checkout modal.</p>
          </div>
          <a href="Track.php?order=<?php echo $orderId; ?>" class="btn btn-secondary mt-3 mt-md-0">Track Order</a>
        </div>

        <div class="alert alert-warning">
          <h4 class="alert-heading">Payment not completed</h4>
          <p>Your order has been created, but payment is still pending. You can retry payment from your order tracking page.</p>
        </div>

        <?php if ($orderId): ?>
          <div class="mb-4">
            <p><strong>Order ID:</strong> <?php echo htmlspecialchars($orderId); ?></p>
          </div>
        <?php endif; ?>

        <div class="mb-4">
          <a href="Track.php?order=<?php echo $orderId; ?>" class="btn btn-primary">Go to Order Tracking</a>
          <a href="index.php" class="btn btn-outline-secondary ms-2">Continue Shopping</a>
        </div>

        <div class="card bg-light p-3">
          <h5 class="mb-3">What happens next?</h5>
          <ul>
            <li>Your order remains in <strong>Pending</strong> status until payment is completed.</li>
            <li>If you want to pay later, visit your order on the tracking page.</li>
            <li>If you want to place a new order, go back to the shop.</li>
          </ul>
        </div>
      </div>
    </section>
  </div>
</div>

<?php include 'templates/footer.php';
