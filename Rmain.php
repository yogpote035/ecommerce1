<?php
require_once 'init.php';
$siteTitle = 'Retail Dashboard';
if (empty($_SESSION['rname'])) {
    header('Location: Rlogin.php');
    exit;
}
$profileName = htmlspecialchars($_SESSION['rname']);
include 'templates/header.php';
?>
<div class="row justify-content-center">
  <div class="col-xl-10">
    <section class="card sidebar-card shadow-sm mb-4">
      <div class="card-body">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
          <div>
            <h1 class="h4 mb-2">Welcome back, <?php echo $profileName; ?>!</h1>
            <p class="text-secondary mb-0">This is your retailer portal. Use the links below to manage products and track orders.</p>
          </div>
          <a href="Rview.php" class="btn btn-outline-primary">Go to product catalog</a>
        </div>
      </div>
    </section>

    <div class="row row-cols-1 row-cols-md-3 g-4">
      <div class="col">
        <article class="card feature-card h-100 shadow-sm">
          <div class="card-body d-flex flex-column">
            <h3 class="h5 mb-3">Add Product</h3>
            <p class="text-secondary mb-4">Open the product entry form and add new inventory to your catalog.</p>
            <a href="Radd.php" class="btn btn-primary mt-auto">Open Add Product</a>
          </div>
        </article>
      </div>
      <div class="col">
        <article class="card feature-card h-100 shadow-sm">
          <div class="card-body d-flex flex-column">
            <h3 class="h5 mb-3">View Products</h3>
            <p class="text-secondary mb-4">Browse the current product list and manage stock quickly.</p>
            <a href="Rview.php" class="btn btn-secondary mt-auto">View Catalog</a>
          </div>
        </article>
      </div>
      <div class="col">
        <article class="card feature-card h-100 shadow-sm">
          <div class="card-body d-flex flex-column">
            <h3 class="h5 mb-3">Track Orders</h3>
            <p class="text-secondary mb-4">Check order status, delivery progress, and recent shipments.</p>
            <a href="Track.php" class="btn btn-info mt-auto">Track Orders</a>
          </div>
        </article>
      </div>
    </div>
  </div>
</div>
<?php include 'templates/footer.php';
