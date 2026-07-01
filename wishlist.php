<?php
require_once 'init.php';

$siteTitle = 'My Wishlist';
$customerId = $_SESSION['customer_id'] ?? $_SESSION['cid'] ?? 0;

if ($customerId <= 0) {
    $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Please login as a customer to view your wishlist.'];
    header('Location: auth.php?role=customer&mode=login&redirect=' . urlencode('wishlist.php'));
    exit;
}

$itemsPerPage = PaginationHelper::PER_PAGE;
$currentPage = PaginationHelper::currentPage();
$offset = PaginationHelper::offset($currentPage, $itemsPerPage);
$totalItems = WishlistHelper::countItems($conn, $customerId);
$totalPages = PaginationHelper::totalPages($totalItems, $itemsPerPage);
$currentPage = min($currentPage, $totalPages);
$offset = PaginationHelper::offset($currentPage, $itemsPerPage);
$items = WishlistHelper::getItems($conn, $customerId, $itemsPerPage, $offset);
include 'templates/header.php';
$breadcrumbs = [
    ['label' => 'Home', 'href' => 'Home.php'],
    ['label' => 'Wishlist'],
];
include 'templates/components/breadcrumb.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
  <div>
    <h1 class="h4 mb-1">My Wishlist</h1>
    <p class="text-secondary mb-0">Save products you want to revisit before checkout.</p>
  </div>
  <a href="index.php?page=1" class="btn btn-outline-primary mt-3 mt-md-0">Browse products</a>
</div>

<?php if (empty($items)): ?>
  <div class="card sidebar-card shadow-sm">
    <div class="card-body text-center py-5">
      <h2 class="h5">No wishlist items yet</h2>
      <p class="text-secondary">Use the heart button on products to save them here.</p>
      <a href="index.php?page=1" class="btn btn-primary">Start browsing</a>
    </div>
  </div>
<?php else: ?>
  <div class="row">
    <?php foreach ($items as $row): ?>
      <div class="col-sm-6 col-lg-4 col-xl-3 mb-4">
        <?php
          $product = [
              'id' => (int) $row['Apid'],
              'name' => $row['apname'],
              'price' => $row['apprice'],
              'image' => !empty($row['apimage']) ? $row['apimage'] : 'images/products/accessories.jpg',
              'rating' => 4.0,
              'brand' => $row['apbrand'] ?? '',
              'category' => $row['apcategory'] ?? '',
              'stock' => $row['apqty'] ?? '',
          ];
          include 'templates/components/product-card.php';
        ?>
      </div>
    <?php endforeach; ?>
  </div>
  <?php PaginationHelper::render($currentPage, $totalPages, $totalItems, $itemsPerPage, 'wishlist items'); ?>
<?php endif; ?>

<?php include 'templates/footer.php'; ?>
