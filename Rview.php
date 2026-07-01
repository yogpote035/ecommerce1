<?php
require_once 'init.php';
require_once 'helpers/CategoryHelper.php';
$siteTitle = 'Retail Product Catalog';

if (empty($_SESSION['retailer_logged_in']) && empty($_SESSION['rname'])) {
    $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Please login as retailer to view products.'];
    header('Location: auth.php?role=retailer&mode=login');
    exit;
}

$category = trim($_GET['category'] ?? '');
$subcategory = trim($_GET['subcategory'] ?? '');
$categoryHelper = new CategoryHelper($conn);
$categoryHierarchy = $categoryHelper->getCategoriesHierarchy();
$itemsPerPage = PaginationHelper::PER_PAGE;
$currentPage = PaginationHelper::currentPage();
$offset = PaginationHelper::offset($currentPage, $itemsPerPage);

$conditions = [];
$params = [];
$types = '';

if ($category !== '') {
    if ($subcategory !== '') {
        $conditions[] = 'apcategory = ?';
        $types .= 's';
        $params[] = $category . ' > ' . $subcategory;
    } else {
        $conditions[] = '(apcategory = ? OR apcategory LIKE ?)';
        $types .= 'ss';
        $params[] = $category;
        $params[] = $category . ' > %';
    }
}

$where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
$countSql = 'SELECT COUNT(*) AS total FROM apadd' . $where;
$totalItems = 0;
$countStmt = mysqli_prepare($conn, $countSql);
if ($countStmt) {
    if ($params) {
        mysqli_stmt_bind_param($countStmt, $types, ...$params);
    }
    mysqli_stmt_execute($countStmt);
    $countResult = mysqli_stmt_get_result($countStmt);
    $totalItems = (int)(mysqli_fetch_assoc($countResult)['total'] ?? 0);
    mysqli_stmt_close($countStmt);
}
$totalPages = PaginationHelper::totalPages($totalItems, $itemsPerPage);
$currentPage = min($currentPage, $totalPages);
$offset = PaginationHelper::offset($currentPage, $itemsPerPage);

$products = [];
$query = 'SELECT * FROM apadd' . $where . ' ORDER BY Apid DESC LIMIT ? OFFSET ?';
$stmt = mysqli_prepare($conn, $query);
if ($stmt) {
    $queryParams = array_merge($params, [$itemsPerPage, $offset]);
    $queryTypes = $types . 'ii';
    mysqli_stmt_bind_param($stmt, $queryTypes, ...$queryParams);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    mysqli_stmt_close($stmt);
}

include 'templates/header.php';
?>
<div class="row">
  <div class="col-lg-3 mb-4">
    <div class="sidebar-card shadow-sm mb-4 retailer-category-panel">
      <div class="card-body">
        <h4 class="h5">Categories</h4>
        <div class="list-group list-group-flush mt-3">
          <a href="Rview.php" class="list-group-item list-group-item-action<?php echo $category === '' ? ' active' : ''; ?>">All Products</a>
          <?php foreach ($categoryHierarchy as $cat): ?>
            <a href="Rview.php?category=<?php echo urlencode($cat['name']); ?>" class="list-group-item list-group-item-action<?php echo $category === $cat['name'] && $subcategory === '' ? ' active' : ''; ?>"><?php echo htmlspecialchars($cat['name']); ?></a>
            <?php if (!empty($cat['subcategories'])): ?>
              <div class="list-group list-group-flush ml-3 mb-2">
                <?php foreach ($cat['subcategories'] as $sub): ?>
                  <a href="Rview.php?category=<?php echo urlencode($cat['name']); ?>&subcategory=<?php echo urlencode($sub['name']); ?>" class="list-group-item list-group-item-action small<?php echo $category === $cat['name'] && $subcategory === $sub['name'] ? ' active' : ''; ?>">
                    <?php echo htmlspecialchars($sub['name']); ?>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-9">
    <div class="d-flex justify-content-between align-items-start mb-4 flex-column flex-md-row">
      <div>
        <h2 class="h4 mb-1">Retail Catalog</h2>
        <p class="text-muted mb-0">Review product inventory by category and subcategory.</p>
      </div>
      <div class="form-actions mt-3 mt-md-0">
        <a href="Radd.php" class="btn btn-primary">Add Product</a>
        <a href="Rmain.php" class="btn btn-outline-secondary">Dashboard</a>
      </div>
    </div>

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
              $productCardMode = 'retailer';
            ?>
            <?php include 'templates/components/product-card.php'; ?>
            <?php unset($productCardMode); ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="alert alert-warning">No products match the selected category.</div>
    <?php endif; ?>
    <?php PaginationHelper::render($currentPage, $totalPages, $totalItems, $itemsPerPage, 'products'); ?>
  </div>
</div>

<?php include 'templates/footer.php';
mysqli_close($conn);
?>
