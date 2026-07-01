<?php
require_once 'init.php';
require_once 'helpers/CategoryHelper.php';

$categorySlug = isset($_GET['category']) ? trim($_GET['category']) : '';
$subcategorySlug = isset($_GET['subcategory']) ? trim($_GET['subcategory']) : '';
$priceMin = isset($_GET['price_min']) ? (float) $_GET['price_min'] : 0;
$priceMax = isset($_GET['price_max']) ? (float) $_GET['price_max'] : 0;
$sort = trim($_GET['sort'] ?? '');

// Pagination setup
$itemsPerPage = PaginationHelper::PER_PAGE;
$currentPage = PaginationHelper::currentPage();
$offset = PaginationHelper::offset($currentPage, $itemsPerPage);

$categoryHelper = new CategoryHelper($conn);
$categories = $categoryHelper->getCategoriesHierarchy();
$activeCategory = null;
$activeSubcategory = null;

foreach ($categories as $cat) {
    if ($cat['slug'] === $categorySlug) {
        $activeCategory = $cat;
        break;
    }
}

if ($subcategorySlug !== '') {
    foreach ($categories as $cat) {
        foreach ($cat['subcategories'] as $sub) {
            if ($sub['slug'] === $subcategorySlug) {
                $activeCategory = $cat;
                $activeSubcategory = $sub;
                break 2;
            }
        }
    }
}

$products = [];
$notFound = false;
$countSql = 'SELECT COUNT(*) as total FROM apadd';
$sql = 'SELECT * FROM apadd';
$conditions = [];
$params = [];
$types = '';

if ($activeSubcategory) {
    $conditions[] = 'LOWER(apcategory) = ?';
    $types .= 's';
    $params[] = strtolower($activeCategory['name'] . ' > ' . $activeSubcategory['name']);
} elseif ($activeCategory) {
    $conditions[] = '(LOWER(apcategory) = ? OR LOWER(apcategory) LIKE ?)';
    $types .= 'ss';
    $params[] = strtolower($activeCategory['name']);
    $params[] = strtolower($activeCategory['name'] . ' > %');
}

if ($priceMin > 0) {
    $conditions[] = 'apprice >= ?';
    $types .= 'd';
    $params[] = $priceMin;
}

if ($priceMax > 0) {
    $conditions[] = 'apprice <= ?';
    $types .= 'd';
    $params[] = $priceMax;
}

if (!empty($conditions)) {
    $whereClause = ' WHERE ' . implode(' AND ', $conditions);
    $countSql .= $whereClause;
    $sql .= $whereClause;
}

if ($sort === 'price_low_high') {
    $sql .= ' ORDER BY apprice ASC';
} elseif ($sort === 'price_high_low') {
    $sql .= ' ORDER BY apprice DESC';
} else {
    $sql .= ' ORDER BY Apid DESC';
}

// Get total count
$countStmt = mysqli_prepare($conn, $countSql);
$totalItems = 0;
$totalPages = 1;
if ($countStmt) {
    if (!empty($types)) {
        mysqli_stmt_bind_param($countStmt, $types, ...$params);
    }
    mysqli_stmt_execute($countStmt);
    $countResult = mysqli_stmt_get_result($countStmt);
    $countData = mysqli_fetch_assoc($countResult);
    $totalItems = $countData['total'];
    $totalPages = PaginationHelper::totalPages($totalItems, $itemsPerPage);
    $currentPage = min($currentPage, $totalPages);
    $offset = PaginationHelper::offset($currentPage, $itemsPerPage);
    mysqli_stmt_close($countStmt);
}

// Add pagination to main query
$sql .= ' LIMIT ? OFFSET ?';
$params[] = $itemsPerPage;
$params[] = $offset;
$types .= 'ii';

$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    if (!empty($types)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    mysqli_stmt_close($stmt);
} else {
    $notFound = $categorySlug !== '' && !$activeCategory;
}

$siteTitle = 'Ecommerce';
include 'templates/header.php';
?>
<div class="row">
  <div class="col-md-3 mb-4">
    <div class="sidebar-card sidebar-sticky shadow-sm h-fit mb-4">
      <div class="card-body">
        <h4 class="h5">Filters</h4>
        <form method="get" action="index.php">
          <input type="hidden" name="category" value="<?php echo htmlspecialchars($categorySlug); ?>">
          <input type="hidden" name="subcategory" value="<?php echo htmlspecialchars($subcategorySlug); ?>">
          <div class="form-group mb-3">
            <label for="price_min" class="small font-weight-bold">Min Price</label>
            <input id="price_min" name="price_min" type="number" step="0.01" min="0" class="form-control" value="<?php echo $priceMin > 0 ? htmlspecialchars($priceMin) : ''; ?>" placeholder="₹0">
          </div>
          <div class="form-group mb-3">
            <label for="price_max" class="small font-weight-bold">Max Price</label>
            <input id="price_max" name="price_max" type="number" step="0.01" min="0" class="form-control" value="<?php echo $priceMax > 0 ? htmlspecialchars($priceMax) : ''; ?>" placeholder="₹0">
          </div>
          <div class="form-group mb-3">
            <label for="sort" class="small font-weight-bold">Sort</label>
            <select id="sort" name="sort" class="form-control">
              <option value="">Default</option>
              <option value="price_low_high"<?php echo $sort === 'price_low_high' ? ' selected' : ''; ?>>Price: Low to High</option>
              <option value="price_high_low"<?php echo $sort === 'price_high_low' ? ' selected' : ''; ?>>Price: High to Low</option>
            </select>
          </div>
          <button type="submit" class="btn btn-primary btn-block mb-4">Apply filters</button>
        </form>

        <h4 class="h5">Categories</h4>
        <div class="list-group list-group-flush mt-3">
          <a href="index.php" class="list-group-item list-group-item-action<?php echo $categorySlug === '' ? ' active' : ''; ?>">All Products</a>
          <?php foreach ($categories as $cat): ?>
            <div class="mb-2">
              <a href="index.php?category=<?php echo urlencode($cat['slug']); ?>" class="list-group-item list-group-item-action<?php echo $categorySlug === $cat['slug'] ? ' active' : ''; ?>">
                <?php echo htmlspecialchars($cat['name']); ?>
              </a>
              <?php if (!empty($cat['subcategories'])): ?>
                <div class="list-group list-group-flush ml-3">
                  <?php foreach ($cat['subcategories'] as $sub): ?>
                    <a href="index.php?category=<?php echo urlencode($cat['slug']); ?>&subcategory=<?php echo urlencode($sub['slug']); ?>" class="list-group-item list-group-item-action small<?php echo $subcategorySlug === $sub['slug'] ? ' active' : ''; ?>">
                      <?php echo htmlspecialchars($sub['name']); ?>
                    </a>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-9 product-area-scroll">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-column flex-md-row">
      <div>
        <h2 class="h4 mb-1"><?php echo $activeSubcategory ? htmlspecialchars($activeCategory['name'] . ' / ' . $activeSubcategory['name']) : ($activeCategory ? htmlspecialchars($activeCategory['name']) : 'Products'); ?></h2>
        <p class="text-muted mb-0"><?php echo $activeSubcategory ? 'Showing products for the selected subcategory.' : ($activeCategory ? 'Showing products for the selected category.' : 'Responsive product cards for customers.'); ?></p>
        <?php if ($activeCategory && !$activeSubcategory && !empty($activeCategory['subcategories'])): ?>
          <div class="mt-3">
            <span class="small text-secondary">Subcategories:</span>
            <div class="d-flex flex-wrap mt-2 ml-3">
              <?php foreach ($activeCategory['subcategories'] as $sub): ?>
                <a href="index.php?category=<?php echo urlencode($activeCategory['slug']); ?>&subcategory=<?php echo urlencode($sub['slug']); ?>" class="btn ml-1 btn-outline-secondary btn-sm<?php echo $subcategorySlug === $sub['slug'] ? ' active' : ''; ?>"><?php echo htmlspecialchars($sub['name']); ?></a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
      <a href="view_cart.php" class="btn btn-success mt-3 mt-md-0">Cart <span class="badge bg-light text-dark"><?php echo count($_SESSION['cart']); ?></span></a>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
      <div class="alert alert-info"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
    <?php endif; ?>

      <?php if (!empty($products)): ?>
        <div class="row">
          <?php foreach ($products as $row): ?>
            <div class="col-sm-6 col-lg-4 mb-4">
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

        <?php PaginationHelper::render($currentPage, $totalPages, $totalItems, $itemsPerPage, 'products'); ?>
        <!-- Legacy pagination retained unreachable while shared pagination renders above. -->
        <?php if (false && $totalPages > 1): 
          $showFrom = $offset + 1;
          $showTo = min($offset + $itemsPerPage, $totalItems);
          
          // Build pagination URL helper
          $buildPaginationUrl = function($pageNum) use ($categorySlug, $subcategorySlug, $priceMin, $priceMax, $sort) {
            $pageNum = (int)$pageNum;
            if ($pageNum < 1) {
              $pageNum = 1;
            }
            $url = '?page=' . $pageNum;
            if ($categorySlug) $url .= '&category=' . urlencode($categorySlug);
            if ($subcategorySlug) $url .= '&subcategory=' . urlencode($subcategorySlug);
            if ($priceMin > 0) $url .= '&price_min=' . $priceMin;
            if ($priceMax > 0) $url .= '&price_max=' . $priceMax;
            if ($sort) $url .= '&sort=' . urlencode($sort);
            return $url;
          };
        ?>
          <div class="pagination-section">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
              <!-- Pagination Info -->
              <div class="pagination-info">
                <span class="pagination-text">
                  SHOWING <?php echo $showFrom; ?> TO <?php echo $showTo; ?> OF <?php echo $totalItems; ?> PRODUCTS
                </span>
              </div>

              <!-- Pagination Controls -->
              <div class="pagination-controls d-flex align-items-center gap-2">
                <!-- Previous Button -->
                <?php if ((int)$currentPage > 1): ?>
                  <a href="<?php echo $buildPaginationUrl((int)$currentPage - 1); ?>" class="pagination-nav-btn" title="Previous Page">
                    <span aria-hidden="true">‹</span>
                  </a>
                <?php else: ?>
                  <span class="pagination-nav-btn disabled" aria-hidden="true">
                    <span>‹</span>
                  </span>
                <?php endif; ?>

                <!-- Page Numbers -->
                <div class="pagination-numbers">
                  <?php 
                    $start = max(1, (int)$currentPage - 2);
                    $end = min($totalPages, (int)$currentPage + 2);
                    
                    if ($start > 1): ?>
                      <a href="<?php echo $buildPaginationUrl(1); ?>" class="pagination-page-btn">1</a>
                      <span class="pagination-dots">...</span>
                    <?php endif;
                    
                    for ($i = $start; $i <= $end; $i++): 
                      $pageClass = 'pagination-page-btn';
                      if ($i === (int) $currentPage) {
                        $pageClass .= ' active';
                      }                    ?>
                      <a href="<?php echo $buildPaginationUrl($i); ?>" class="<?php echo $pageClass; ?>"<?php echo $i === (int) $currentPage ? ' aria-current="page"' : ''; ?>>
                        <?php echo $i; ?>
                      </a>
                    <?php endfor;
                    
                    if ($end < $totalPages): ?>
                      <span class="pagination-dots">...</span>
                      <a href="<?php echo $buildPaginationUrl($totalPages); ?>" class="pagination-page-btn"><?php echo $totalPages; ?></a>
                    <?php endif; ?>
                </div>

                <!-- Next Button -->
                <?php if ((int)$currentPage < $totalPages): ?>
                  <a href="<?php echo $buildPaginationUrl((int)$currentPage + 1); ?>" class="pagination-nav-btn" title="Next Page">
                    <span aria-hidden="true">›</span>
                  </a>
                <?php else: ?>
                  <span class="pagination-nav-btn disabled" aria-hidden="true">
                    <span>›</span>
                  </span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endif; ?>
      <?php else: ?>
        <div class="alert alert-warning">No products found for this selection.</div>
      <?php endif; ?>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
