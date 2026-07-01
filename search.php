<?php
require_once 'init.php';
require_once 'helpers/CategoryHelper.php';

$q = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');
$categoryHelper = new CategoryHelper($conn);
$categories = array_column($categoryHelper->getCategoriesHierarchy(), 'name');
$products = [];
$itemsPerPage = PaginationHelper::PER_PAGE;
$currentPage = PaginationHelper::currentPage();
$offset = PaginationHelper::offset($currentPage, $itemsPerPage);
$totalItems = 0;
$totalPages = 1;

if ($q !== '') {
    $query = '%' . strtolower($q) . '%';
    $sql = 'SELECT * FROM apadd WHERE (LOWER(apname) LIKE ? OR LOWER(apbrand) LIKE ? OR LOWER(apcategory) LIKE ?)';
    $countSql = 'SELECT COUNT(*) AS total FROM apadd WHERE (LOWER(apname) LIKE ? OR LOWER(apbrand) LIKE ? OR LOWER(apcategory) LIKE ?)';
    $types = 'sss';
    $params = [$query, $query, $query];

    if ($category !== '') {
        $sql .= ' AND apcategory = ?';
        $countSql .= ' AND apcategory = ?';
        $types .= 's';
        $params[] = $category;
    }

    $countStmt = mysqli_prepare($conn, $countSql);
    if ($countStmt) {
        mysqli_stmt_bind_param($countStmt, $types, ...$params);
        mysqli_stmt_execute($countStmt);
        $countResult = mysqli_stmt_get_result($countStmt);
        $totalItems = (int)(mysqli_fetch_assoc($countResult)['total'] ?? 0);
        $totalPages = PaginationHelper::totalPages($totalItems, $itemsPerPage);
        $currentPage = min($currentPage, $totalPages);
        $offset = PaginationHelper::offset($currentPage, $itemsPerPage);
        mysqli_stmt_close($countStmt);
    }

    $sql .= ' ORDER BY Apid DESC LIMIT ? OFFSET ?';
    $queryParams = array_merge($params, [$itemsPerPage, $offset]);
    $queryTypes = $types . 'ii';
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, $queryTypes, ...$queryParams);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
}

$siteTitle = 'Search Results';
include 'templates/header.php';
?>
<div class="row">
  <div class="col-md-3 mb-4">
    <div class="sidebar-card shadow-sm mb-4">
      <div class="card-body">
        <h4 class="h5">Categories</h4>
        <div class="list-group list-group-flush mt-3">
          <a href="search.php?q=<?php echo urlencode($q); ?>" class="list-group-item list-group-item-action<?php echo $category === '' ? ' active' : ''; ?>">All</a>
          <?php foreach ($categories as $cat): ?>
            <a href="search.php?q=<?php echo urlencode($q); ?>&category=<?php echo urlencode($cat); ?>" class="list-group-item list-group-item-action<?php echo $category === $cat ? ' active' : ''; ?>"><?php echo htmlspecialchars($cat); ?></a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-9">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-column flex-md-row">
      <div>
        <h2 class="h4 mb-1">Search results for "<?php echo htmlspecialchars($q); ?>"</h2>
        <p class="text-muted mb-0"><?php echo $q === '' ? 'Enter a product name, brand or category to start searching.' : 'We found ' . number_format($totalItems) . ' matching products.'; ?></p>
      </div>
      <a href="view_cart.php" class="btn btn-success mt-3 mt-md-0">Cart <span class="badge bg-light text-dark"><?php echo count($_SESSION['cart']); ?></span></a>
    </div>

    <?php if ($q === ''): ?>
      <div class="alert alert-info">Use the search box in the navbar to find products instantly.</div>
    <?php elseif (!empty($products)): ?>
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
    <?php else: ?>
      <div class="alert alert-warning">No products match your search. Try another keyword or category.</div>
    <?php endif; ?>
  </div>
</div>

<?php include 'templates/footer.php';
?>
