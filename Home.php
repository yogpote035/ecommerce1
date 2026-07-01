<?php
require_once 'init.php';
require_once 'helpers/CategoryHelper.php';

$siteTitle = 'Ecommerce';
$isLoggedIn = !empty($_SESSION['customer_logged_in']) || !empty($_SESSION['admin_logged_in']);
$ctaHref = $isLoggedIn ? 'index.php?page=1' : 'auth.php';
$ctaLabel = $isLoggedIn ? 'Browse Products' : 'Start Shopping';

$categoryHelper = new CategoryHelper($conn);
$categories = array_slice($categoryHelper->getCategoriesHierarchy(), 0, 6);

$featuredProducts = [];
$featuredStmt = mysqli_prepare($conn, 'SELECT * FROM apadd ORDER BY Apid DESC LIMIT 6');
if ($featuredStmt) {
    mysqli_stmt_execute($featuredStmt);
    $featuredResult = mysqli_stmt_get_result($featuredStmt);
    while ($row = mysqli_fetch_assoc($featuredResult)) {
        $featuredProducts[] = $row;
    }
    mysqli_stmt_close($featuredStmt);
}

include 'templates/header.php';
?>
<section class="hero store-hero py-5 px-4">
  <div class="row align-items-center">
    <div class="col-lg-6">
      <p class="text-uppercase font-weight-bold mb-3 hero-kicker">Fresh picks, simple checkout</p>
      <h1 class="display-4 font-weight-bold">Shop daily essentials and standout finds in one place.</h1>
      <p class="lead mt-4">Browse curated categories, preview trending products, and keep your cart ready across the modernized customer flow.</p>
      <div class="hero-buttons">
        <a href="<?php echo htmlspecialchars($ctaHref); ?>" class="btn btn-light btn-lg"><?php echo htmlspecialchars($ctaLabel); ?></a>
        <a href="index.php?page=1" class="btn btn-outline-light btn-lg">View Catalog</a>
      </div>
      <div class="mt-4 hero-search">
        <?php
          $searchPlaceholder = 'Search clothes, bags, appliances...';
          include 'templates/components/search-box.php';
        ?>
      </div>
    </div>
    <div class="col-lg-6 mt-5 mt-lg-0">
      <div class="hero-visual rounded-lg overflow-hidden shadow-soft">
        <img src="images/products/accessories.jpg" alt="Featured shopping collection" class="img-fluid" onerror="this.onerror=null;this.src='images/products/clothes.jpg';">
      </div>
      <div class="row hero-gallery mt-4">
        <div class="col-4">
          <div class="gallery-item rounded-lg overflow-hidden shadow-soft">
            <img src="images/products/clothes.jpg" alt="Clothes" class="img-fluid" onerror="this.onerror=null;this.src='images/products/accessories.jpg';">
          </div>
        </div>
        <div class="col-4">
          <div class="gallery-item rounded-lg overflow-hidden shadow-soft">
            <img src="images/products/eappliance.jpg" alt="Appliances" class="img-fluid" onerror="this.onerror=null;this.src='images/products/clothes.jpg';">
          </div>
        </div>
        <div class="col-4">
          <div class="gallery-item rounded-lg overflow-hidden shadow-soft">
            <img src="images/products/footwear.jpg" alt="Footwear" class="img-fluid" onerror="this.onerror=null;this.src='images/products/accessories.jpg';">
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php if (!empty($categories)): ?>
<section class="py-5">
  <div class="d-flex justify-content-between align-items-end flex-wrap mb-4">
    <div>
      <h2 class="h3 font-weight-bold mb-1">Shop by category</h2>
      <p class="text-muted mb-0">Quick paths into the catalog sections customers use most.</p>
    </div>
    <a href="index.php?page=1" class="btn btn-outline-primary mt-3 mt-md-0">All Products</a>
  </div>
  <div class="row">
    <?php foreach ($categories as $category): ?>
      <div class="col-sm-6 col-lg-4 mb-4">
        <a class="category-tile d-block h-100" href="index.php?category=<?php echo urlencode($category['slug']); ?>&page=1">
          <span class="category-tile-name"><?php echo htmlspecialchars($category['name']); ?></span>
          <span class="category-tile-copy"><?php echo htmlspecialchars($category['description'] ?? 'Explore products in this collection.'); ?></span>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section class="py-5">
  <div class="d-flex justify-content-between align-items-end flex-wrap mb-4">
    <div>
      <h2 class="h3 font-weight-bold mb-1">New arrivals</h2>
      <p class="text-muted mb-0">Recently added products, using the shared product card component.</p>
    </div>
    <a href="index.php?page=1" class="btn btn-primary mt-3 mt-md-0">Browse More</a>
  </div>

  <?php if (!empty($featuredProducts)): ?>
    <div class="row">
      <?php foreach ($featuredProducts as $row): ?>
        <div class="col-sm-6 col-lg-3 mb-4">
          <?php
            $product = [
              'id' => (int) ($row['Apid'] ?? 0),
              'name' => $row['apname'] ?? 'Product',
              'price' => $row['apprice'] ?? 0,
              'image' => !empty($row['apimage']) ? $row['apimage'] : 'images/products/accessories.jpg',
              'rating' => $row['aprating'] ?? 4.0,
              'brand' => $row['apbrand'] ?? '',
              'category' => $row['apcategory'] ?? '',
              'stock' => $row['apqty'] ?? '',
            ];
          ?>
          <?php include 'templates/components/product-card.php'; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <?php $rows = 3; $variant = 'card'; include 'templates/components/loading-skeleton.php'; ?>
  <?php endif; ?>
</section>

<section class="py-5">
  <div class="row">
    <div class="col-md-4 mb-4">
      <div class="card feature-card h-100 p-4">
        <h3 class="h5">Persistent cart</h3>
        <p class="mb-0">Logged-in customers keep cart items synced between browsing and checkout.</p>
      </div>
    </div>
    <div class="col-md-4 mb-4">
      <div class="card feature-card h-100 p-4">
        <h3 class="h5">Live search</h3>
        <p class="mb-0">Search suggestions and recent searches help customers move through the catalog faster.</p>
      </div>
    </div>
    <div class="col-md-4 mb-4">
      <div class="card feature-card h-100 p-4">
        <h3 class="h5">Modern checkout</h3>
        <p class="mb-0">COD and Razorpay paths are prepared for a cleaner payment experience.</p>
      </div>
    </div>
  </div>
</section>

<?php include 'templates/footer.php';
