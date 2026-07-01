<?php
require_once 'init.php';
require_once 'helpers/ProductImageHelper.php';

$siteTitle = 'Product Details';
$productId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$product = null;
$images = [];
$helper = new ProductImageHelper($conn);
$isRetailer = !empty($_SESSION['retailer_logged_in']) || !empty($_SESSION['rname']);

if ($productId > 0) {
    $stmt = mysqli_prepare($conn, 'SELECT Apid, apname, apbrand, apcategory, apqty, apprice, apdescription, apimage FROM apadd WHERE Apid = ? LIMIT 1');
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    }
}

$relatedProducts = [];
$customerIdForWishlist = $_SESSION['customer_id'] ?? $_SESSION['cid'] ?? 0;
$isWishlisted = false;

if ($product) {
    $isWishlisted = $customerIdForWishlist > 0 ? WishlistHelper::isWishlisted($conn, $customerIdForWishlist, $productId) : false;
    $images = $helper->getProductImages($productId);
    if (empty($images)) {
        $images[] = [
            'public_path' => !empty($product['apimage']) ? $product['apimage'] : 'images/products/accessories.jpg',
            'alt_text' => htmlspecialchars($product['apname'] ?? 'Product Image'),
            'is_primary' => 1,
        ];
    }
    
    // Fetch related products from the same category
    $categoryQuery = mysqli_prepare($conn, 'SELECT Apid, apname, apbrand, apcategory, apprice, apimage, apqty FROM apadd WHERE apcategory = ? AND Apid != ? LIMIT 6');
    if ($categoryQuery) {
        mysqli_stmt_bind_param($categoryQuery, 'si', $product['apcategory'], $productId);
        mysqli_stmt_execute($categoryQuery);
        $categoryResult = mysqli_stmt_get_result($categoryQuery);
        while ($row = mysqli_fetch_assoc($categoryResult)) {
            $relatedProducts[] = $row;
        }
        mysqli_stmt_close($categoryQuery);
    }
}

include 'templates/header.php';
?>

<style>
  .product-detail-hero {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    padding: 20px 0;
    margin-bottom: 40px;
  }

  .product-details-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: box-shadow 0.3s ease;
  }

  .product-details-card:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
  }

  .product-title {
    font-size: 28px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 16px;
    line-height: 1.3;
  }

  .product-brand {
    font-size: 14px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
    margin-bottom: 8px;
  }

  .product-rating {
    font-size: 13px;
    color: #ffc107;
    margin-bottom: 16px;
    display: none;
  }

  .product-price {
    font-size: 32px;
    font-weight: 700;
    color: #0d6efd;
    margin-bottom: 8px;
  }

  .product-category-badge {
    display: inline-block;
    background-color: #e7f3ff;
    color: #0d6efd;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 20px;
  }

  .product-stock {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    margin-left: 8px;
  }

  .product-stock.in-stock {
    background-color: #d4edda;
    color: #155724;
  }

  .product-stock.low-stock {
    background-color: #fff3cd;
    color: #856404;
  }

  .product-stock.out-of-stock {
    background-color: #f8d7da;
    color: #721c24;
  }

  .product-description {
    font-size: 15px;
    line-height: 1.6;
    color: #555;
    margin-bottom: 24px;
    padding: 16px;
    background-color: #f8f9fa;
    border-left: 4px solid #0d6efd;
    border-radius: 6px;
  }

  .action-buttons {
    display: flex;
    gap: 12px;
    margin-bottom: 24px;
  }

  .action-buttons .btn {
    border-radius: 8px;
    padding: 12px 28px;
    font-weight: 600;
    font-size: 15px;
    transition: all 0.3s ease;
  }

  .action-buttons .btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
  }

  .action-buttons .btn-primary:hover {
    background-color: #0b5ed7;
    border-color: #0b5ed7;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
  }

  .action-buttons .btn-outline-secondary:hover {
    background-color: #6c757d;
    border-color: #6c757d;
    color: white;
    transform: translateY(-2px);
  }

  .product-features {
    list-style: none;
    padding: 0;
    margin: 20px 0;
  }

  .product-features li {
    padding: 8px 0;
    font-size: 14px;
    color: #555;
  }


  .related-products-section {
    border-top: 2px solid #e9ecef;
    margin-top: 48px;
    padding-top: 40px;
  }

  .product-features li:before {
    content: none !important;
  }

  .related-products-header {
    font-size: 24px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 24px;
  }

  @media (max-width: 768px) {
    .product-title {
      font-size: 22px;
    }

    .product-price {
      font-size: 24px;
    }

    .action-buttons {
      flex-direction: column;
    }

    .action-buttons .btn {
      width: 100%;
    }
  }
</style>

<div class="product-detail-hero">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item"><a href="category.php"><?php echo htmlspecialchars($product['apcategory'] ?? 'Products'); ?></a></li>
        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['apname'] ?? 'Product'); ?></li>
      </ol>
    </nav>
  </div>
</div>

<div class="container py-5">
  <?php if (!$product): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
      <strong>Not Found</strong> The product you're looking for doesn't exist.
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <a href="index.php" class="btn btn-outline-primary">← Back to Products</a>
  <?php else: ?>
    <div class="row mb-4">
      <div class="col-lg-5 mb-4">
        <?php include 'templates/components/product-gallery.php'; ?>
      </div>

      <div class="col-lg-7">
        <div class="product-details-card card">
          <div class="card-body p-4 p-lg-5">
            <!-- Brand & Rating -->
            <p class="product-brand"><?php echo htmlspecialchars($product['apbrand'] ?? 'Brand'); ?></p>
            <!-- Title -->
            <h1 class="product-title"><?php echo htmlspecialchars($product['apname']); ?></h1>

            <!-- Category & Stock Badge -->
            <div class="mb-3">
              <span class="product-category-badge"><?php echo htmlspecialchars($product['apcategory'] ?? 'General'); ?></span>
              <?php 
                $stockClass = 'in-stock';
                $stockText = 'In Stock';
                if ($product['apqty'] <= 0) {
                  $stockClass = 'out-of-stock';
                  $stockText = 'Out of Stock';
                } elseif ($product['apqty'] <= 5) {
                  $stockClass = 'low-stock';
                  $stockText = 'Only ' . $product['apqty'] . ' left';
                }
              ?>
              <span class="product-stock <?php echo $stockClass; ?>"><?php echo $stockText; ?></span>
            </div>

            <!-- Price -->
            <div class="mb-4">
              <span class="product-price">₹<?php echo number_format($product['apprice'], 2); ?></span>
              <span class="text-muted" style="font-size: 14px;">incl. taxes</span>
            </div>

            <!-- Description -->
            <div class="product-description">
              <?php echo htmlspecialchars($product['apdescription'] ?? 'No description available.'); ?>
            </div>

            <?php if (!$isRetailer): ?>
            <!-- Action Buttons -->
            <div class="action-buttons">
              <a href="add_cart.php?id=<?php echo urlencode($productId); ?>" class="btn btn-primary btn-lg">
                <i class="fas fa-shopping-cart"></i> Add to Cart
              </a>
              <form method="post" action="wishlist_action.php" class="m-0">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(SecurityHelper::generateCSRFToken()); ?>">
                <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'product.php?id=' . $productId); ?>">
                <button type="submit" class="btn btn-outline-primary btn-lg">
                  <?php echo $isWishlisted ? 'Saved to Wishlist' : 'Save to Wishlist'; ?>
                </button>
              </form>
              <a href="index.php" class="btn btn-outline-secondary btn-lg">
                ← Continue Shopping
              </a>
            </div>
            <?php endif; ?>

            <!-- Product Info -->
            <div style="border-top: 1px solid #e9ecef; padding-top: 20px;">
              <h6 class="text-muted mb-3" style="font-weight: 600; font-size: 13px; text-transform: uppercase;">Product Information</h6>
              <ul class="product-features">
                <li><strong>Category:</strong> <?php echo htmlspecialchars($product['apcategory']); ?></li>
                <li><strong>Brand:</strong> <?php echo htmlspecialchars($product['apbrand']); ?></li>
                <li><strong>Stock Available:</strong> <?php echo $product['apqty']; ?> units</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Related Products Section -->

    <?php if (!empty($relatedProducts)): ?>
    <div class="related-products-section">
      <h2 class="related-products-header">More Products in <?php echo htmlspecialchars($product['apcategory']); ?></h2>
      <div class="row g-4">
        <?php foreach ($relatedProducts as $relatedProduct): ?>
          <div class="col-6 col-md-4 col-lg-3">
            <?php 
              $product = [
                'id' => $relatedProduct['Apid'],
                'name' => $relatedProduct['apname'],
                'price' => $relatedProduct['apprice'],
                'image' => $relatedProduct['apimage'] ?? 'images/products/accessories.jpg',
                'brand' => $relatedProduct['apbrand'],
                'category' => $relatedProduct['apcategory'],
                'stock' => $relatedProduct['apqty'],
                'rating' => 0,
              ];
              $productCardMode = $isRetailer ? 'retailer' : '';
              include 'templates/components/product-card.php'; 
              unset($productCardMode);
            ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php include 'templates/footer.php';
