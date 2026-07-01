<?php
// Product card component - Phase 3 starter
// Expected input: $product = [
//   'id' => 1,
//   'name' => 'Product',
//   'price' => 49.99,
//   'image' => 'path',
//   'rating' => 4.5,
//   'brand' => 'Brand Name',
//   'category' => 'Category',
//   'stock' => 10,
// ];
$productId = isset($product['id']) ? (int) $product['id'] : 0;
$productName = htmlspecialchars($product['name'] ?? 'Product');
$productImage = htmlspecialchars($product['image'] ?? 'images/products/accessories.jpg');
$productPrice = number_format($product['price'] ?? 0, 2);
$productRating = number_format($product['rating'] ?? 0, 1);
$productBrand = htmlspecialchars($product['brand'] ?? '');
$productCategory = htmlspecialchars($product['category'] ?? '');
$productStock = htmlspecialchars($product['stock'] ?? '');
$productCardMode = $productCardMode ?? '';
$isRetailerCard = $productCardMode === 'retailer' || !empty($_SESSION['retailer_logged_in']) || !empty($_SESSION['rname']);

// Check if user is logged in
$isLoggedIn = !empty($_SESSION['admin_logged_in']) || !empty($_SESSION['customer_logged_in']) || !empty($_SESSION['retailer_logged_in']) || !empty($_SESSION['rname']);
$cartUrl = $isLoggedIn ? 'add_cart.php?id=' . $productId : 'auth.php?redirect=' . urlencode('product.php?id=' . $productId);
$detailsUrl = 'product.php?id=' . $productId;
$customerIdForWishlist = $_SESSION['customer_id'] ?? $_SESSION['cid'] ?? 0;
$isWishlisted = (!$isRetailerCard && $customerIdForWishlist > 0) ? WishlistHelper::isWishlisted($conn, $customerIdForWishlist, $productId) : false;
$wishlistToken = !$isRetailerCard ? SecurityHelper::generateCSRFToken() : '';
?>
<div class="card product-card h-100 shadow-soft overflow-hidden">
  <div class="product-image-frame">
    <a href="<?php echo htmlspecialchars($detailsUrl); ?>">
      <img src="<?php echo $productImage; ?>" alt="<?php echo $productName; ?>" class="card-img-top">
    </a>
  </div>
  <div class="card-body d-flex flex-column">
    <?php if ($productCategory): ?>
      <p class="text-secondary small mb-2"><?php echo $productCategory; ?></p>
    <?php endif; ?>
    <h3 class="h6 mb-2 text-primary"><a href="<?php echo htmlspecialchars($detailsUrl); ?>" class="text-primary text-decoration-none"><?php echo $productName; ?></a></h3>
    <?php if ($productBrand): ?>
      <p class="text-muted small mb-3">Brand: <?php echo $productBrand; ?></p>
    <?php endif; ?>

    <div class="mt-auto">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="h5 mb-0">&#8377;<?php echo $productPrice; ?></span>
        <?php if ($productStock !== ''): ?>
          <span class="badge bg-secondary">Stock: <?php echo $productStock; ?></span>
        <?php endif; ?>
      </div>
      <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="badge rounded-pill bg-secondary py-2 px-3"><?php echo $productRating; ?> &#9733;</span>
        <?php if ($isRetailerCard): ?>
          <a href="<?php echo htmlspecialchars($detailsUrl); ?>" class="btn btn-outline-primary">View</a>
        <?php else: ?>
          <a href="<?php echo htmlspecialchars($cartUrl); ?>" class="btn btn-primary">Add to cart</a>
        <?php endif; ?>
      </div>
      <?php if (!$isRetailerCard): ?>
        <form method="post" action="wishlist_action.php" class="mb-0">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($wishlistToken); ?>">
          <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
          <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'index.php?page=1'); ?>">
          <button type="submit" class="btn btn-outline-primary btn-sm w-100">
            <?php echo $isWishlisted ? 'Saved to wishlist' : 'Save to wishlist'; ?>
          </button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>
