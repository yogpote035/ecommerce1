<?php
require_once 'init.php';

$siteTitle = 'Admin Products Management';
$csrfToken = SecurityHelper::generateCSRFToken();

// Check admin authentication
if (empty($_SESSION['admin_logged_in']) || empty($_SESSION['admin_id'])) {
    header('Location: auth.php?role=admin&mode=login');
    exit;
}

// Pagination setup
$itemsPerPage = PaginationHelper::PER_PAGE;
$currentPage = PaginationHelper::currentPage();
$offset = PaginationHelper::offset($currentPage, $itemsPerPage);

// Get total count
$countStmt = mysqli_query($conn, "SELECT COUNT(*) as total FROM apadd");
$countResult = mysqli_fetch_assoc($countStmt);
$totalItems = $countResult['total'];
$totalPages = PaginationHelper::totalPages($totalItems, $itemsPerPage);
$currentPage = min($currentPage, $totalPages);
$offset = PaginationHelper::offset($currentPage, $itemsPerPage);

// Fetch products for current page
$sql = "SELECT Apid, apname, apbrand, apcategory, apqty, apprice FROM apadd ORDER BY Apid DESC LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'ii', $itemsPerPage, $offset);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}
mysqli_stmt_close($stmt);

include 'templates/header.php';
?>

<style>
  .admin-products-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px 0;
    margin-bottom: 30px;
    border-radius: 8px;
  }

  .admin-products-hero h1 {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 8px;
  }

  .admin-products-hero p {
    font-size: 16px;
    opacity: 0.9;
  }

  .products-table-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    margin-bottom: 30px;
  }

  .products-table-card thead {
    background-color: #f8f9fa;
    border-bottom: 2px solid #e9ecef;
  }

  .products-table-card thead th {
    font-weight: 600;
    color: #495057;
    padding: 16px;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .products-table-card tbody td {
    padding: 14px 16px;
    vertical-align: middle;
    border-bottom: 1px solid #e9ecef;
  }

  .products-table-card tbody tr:hover {
    background-color: #f8f9fa;
  }

  .product-name-cell {
    font-weight: 600;
    color: #1a1a1a;
  }

  .product-brand-cell {
    color: #6c757d;
    font-size: 14px;
  }

  .product-category-badge {
    display: inline-block;
    background-color: #e7f3ff;
    color: #0d6efd;
    padding: 4px 10px;
    border-radius: 16px;
    font-size: 12px;
    font-weight: 600;
  }

  .product-price {
    font-weight: 700;
    color: #0d6efd;
    font-size: 16px;
  }

  .stock-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
  }

  .stock-badge.in-stock {
    background-color: #d4edda;
    color: #155724;
  }

  .stock-badge.low-stock {
    background-color: #fff3cd;
    color: #856404;
  }

  .stock-badge.out-of-stock {
    background-color: #f8d7da;
    color: #721c24;
  }

  .action-buttons {
    display: flex;
    gap: 8px;
  }

  .action-buttons .btn-sm {
    padding: 6px 12px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 6px;
  }

  .action-buttons .btn-edit {
    background-color: #0d6efd;
    color: white;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .action-buttons .btn-edit:hover {
    background-color: #0b5ed7;
    transform: translateY(-2px);
    box-shadow: 0 2px 6px rgba(13, 110, 253, 0.3);
  }

  .action-buttons .btn-delete {
    background-color: #dc3545;
    color: white;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .action-buttons .btn-delete:hover {
    background-color: #c82333;
    transform: translateY(-2px);
    box-shadow: 0 2px 6px rgba(220, 53, 69, 0.3);
  }

  .pagination-wrapper {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 30px;
    flex-wrap: wrap;
  }

  .pagination-item {
    display: inline-block;
    padding: 8px 12px;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    background-color: white;
    color: #0d6efd;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
    font-size: 14px;
  }

  .pagination-item:hover {
    background-color: #0d6efd;
    color: white;
    border-color: #0d6efd;
  }

  .pagination-item.active {
    background-color: #0d6efd;
    color: white;
    border-color: #0d6efd;
  }

  .pagination-item.disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  .pagination-item.disabled:hover {
    background-color: white;
    color: #6c757d;
    border-color: #dee2e6;
  }

  /* New Pagination Styles */
  .pagination-section {
    border-radius: 0 0 12px 12px;
  }

  .pagination-text {
    white-space: nowrap;
  }

  .pagination-controls {
    flex-wrap: wrap;
    justify-content: center;
  }

  .pagination-nav-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    background-color: white;
    color: #0d6efd;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
  }

  .pagination-nav-btn:hover {
    background-color: #0d6efd;
    color: white;
    border-color: #0d6efd;
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(13, 110, 253, 0.2);
  }

  .pagination-page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 0 12px;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    background-color: white;
    color: #0d6efd;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
  }

  .pagination-page-btn:hover:not(.active) {
    background-color: #0d6efd;
    color: white;
    border-color: #0d6efd;
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(13, 110, 253, 0.2);
  }

  .pagination-page-btn.active {
    background-color: #0d6efd;
    color: white;
    border-color: #0d6efd;
    cursor: default;
  }

  .pagination-dots {
    color: #6c757d;
    font-weight: 600;
    padding: 0 0.25rem;
  }

  .pagination-numbers {
    flex-wrap: wrap;
    justify-content: center;
  }

  .pagination-page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 0 8px;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    background-color: white;
    color: #0d6efd;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
    font-size: 14px;
  }

  .pagination-page-btn:hover {
    background-color: #0d6efd;
    color: white;
    border-color: #0d6efd;
  }

  .pagination-page-btn.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: #667eea;
    font-weight: 700;
  }

  @media (max-width: 768px) {
    .pagination-section {
      flex-direction: column;
    }

    .pagination-controls {
      width: 100%;
      justify-content: center;
    }

    .pagination-text {
      text-align: center;
      width: 100%;
    }
  }

  .empty-state {
    text-align: center;
    padding: 60px 20px;
  }

  .empty-state-icon {
    font-size: 48px;
    color: #ccc;
    margin-bottom: 16px;
  }

  .empty-state-text {
    color: #6c757d;
    font-size: 18px;
    margin-bottom: 20px;
  }

  .stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
  }

  .stat-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    border-left: 4px solid #0d6efd;
  }

  .stat-label {
    color: #6c757d;
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 8px;
  }

  .stat-value {
    font-size: 28px;
    font-weight: 700;
    color: #1a1a1a;
  }
</style>
<style>
  .admin-products-hero,
  .products-table-card,
  .stat-card {
    border: 1px solid var(--border) !important;
    box-shadow: var(--shadow-soft) !important;
  }

  .products-table-card,
  .stat-card,
  .products-table-card thead,
  .products-table-card tbody tr:hover {
    background: var(--surface) !important;
    color: var(--text-primary) !important;
  }

  .products-table-card thead th,
  .products-table-card tbody td,
  .product-name-cell,
  .stat-value {
    color: var(--text-primary) !important;
    border-color: var(--border) !important;
  }

  .product-brand-cell,
  .stat-label {
    color: var(--text-secondary) !important;
  }

  .product-category-badge,
  .stock-badge {
    border: 1px solid var(--border);
  }

  .action-buttons {
    gap: 0.5rem;
    flex-wrap: wrap;
  }

  .action-buttons .btn-edit,
  .action-buttons .btn-delete {
    color: #fff !important;
  }

  .pagination-section {
    background: var(--surface) !important;
    border-color: var(--border) !important;
  }

  .pagination-text {
    color: var(--text-secondary) !important;
  }
</style>

<div class="container-fluid py-4">
  <!-- Header Section -->
  <div class="admin-products-hero">
    <div class="row align-items-center px-4">
      <div class="col-md-8">
        <h1>Product Management</h1>
        <p>Manage your product catalog - add, edit, and delete items</p>
      </div>
      <div class="col-md-4 text-md-right">
        <a href="validation.php" class="btn btn-light btn-lg">+ Add New Product</a>
      </div>
    </div>
  </div>

  <!-- Stats Row -->
  <div class="stats-row">
    <div class="stat-card">
      <div class="stat-label">Total Products</div>
      <div class="stat-value"><?php echo $totalItems; ?></div>
    </div>
    <div class="stat-card" style="border-left-color: #28a745;">
      <div class="stat-label">In Stock</div>
      <div class="stat-value">
        <?php 
          $inStockResult = mysqli_query($conn, "SELECT COUNT(*) as count FROM apadd WHERE apqty > 0");
          $inStockData = mysqli_fetch_assoc($inStockResult);
          echo $inStockData['count'];
        ?>
      </div>
    </div>
    <div class="stat-card" style="border-left-color: #ffc107;">
      <div class="stat-label">Low Stock</div>
      <div class="stat-value">
        <?php 
          $lowStockResult = mysqli_query($conn, "SELECT COUNT(*) as count FROM apadd WHERE apqty > 0 AND apqty <= 5");
          $lowStockData = mysqli_fetch_assoc($lowStockResult);
          echo $lowStockData['count'];
        ?>
      </div>
    </div>
  </div>

  <!-- Products Table -->
  <div class="card products-table-card">
    <div class="card-body p-0">
      <?php if (empty($products)): ?>
        <div class="empty-state">
          <div class="empty-state-icon">📦</div>
          <div class="empty-state-text">No products found</div>
          <a href="validation.php" class="btn btn-primary">Add Your First Product</a>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table products-table-card mb-0">
            <thead>
              <tr>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Brand</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($products as $product): 
                $stockClass = 'in-stock';
                $stockText = $product['apqty'] . ' units';
                if ($product['apqty'] <= 0) {
                  $stockClass = 'out-of-stock';
                  $stockText = 'Out of Stock';
                } elseif ($product['apqty'] <= 5) {
                  $stockClass = 'low-stock';
                }
              ?>
                <tr>
                  <td><strong>#<?php echo $product['Apid']; ?></strong></td>
                  <td>
                    <div class="product-name-cell"><?php echo htmlspecialchars($product['apname']); ?></div>
                    <div class="product-brand-cell"><?php echo htmlspecialchars($product['apbrand']); ?></div>
                  </td>
                  <td><?php echo htmlspecialchars($product['apbrand']); ?></td>
                  <td><span class="product-category-badge"><?php echo htmlspecialchars(explode('>', $product['apcategory'])[0]); ?></span></td>
                  <td><span class="product-price">₹<?php echo number_format($product['apprice'], 2); ?></span></td>
                  <td><span class="stock-badge <?php echo $stockClass; ?>"><?php echo $stockText; ?></span></td>
                  <td>
                    <div class="action-buttons">
                      <a href="admin_product_edit.php?id=<?php echo $product['Apid']; ?>" class="btn btn-sm btn-edit">Edit</a>
                      <a href="admin_product_images.php?id=<?php echo $product['Apid']; ?>" class="btn btn-sm btn-outline-secondary">Images</a>
                      <form method="post" action="admin_product_delete.php" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product? This action cannot be undone.');">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                        <input type="hidden" name="id" value="<?php echo (int)$product['Apid']; ?>">
                        <button type="submit" class="btn btn-sm btn-delete">Delete</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <?php PaginationHelper::render($currentPage, $totalPages, $totalItems, $itemsPerPage, 'products'); ?>
        <!-- Legacy pagination retained unreachable while shared pagination renders above. -->
        <?php if (false && $totalPages > 1): 
          $showFrom = $offset + 1;
          $showTo = min($offset + $itemsPerPage, $totalItems);
        ?>
          <div class="pagination-section p-4" style="border-top: 1px solid #e9ecef; background-color: #f8f9fa;">
            <!-- Pagination Info -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
              <div class="pagination-info">
                <span class="pagination-text" style="font-weight: 600; color: #495057; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">
                  SHOWING <?php echo $showFrom; ?> TO <?php echo $showTo; ?> OF <?php echo $totalItems; ?> PRODUCTS
                </span>
              </div>

              <!-- Pagination Controls -->
              <div class="pagination-controls d-flex align-items-center gap-2">
                <!-- Previous Button -->
                <?php if ($currentPage > 1): ?>
                  <a href="?page=<?php echo $currentPage - 1; ?>" class="pagination-nav-btn" title="Previous Page">
                    <span style="font-size: 20px;">‹</span>
                  </a>
                <?php else: ?>
                  <span class="pagination-nav-btn disabled" style="opacity: 0.5; cursor: not-allowed;">
                    <span style="font-size: 20px;">‹</span>
                  </span>
                <?php endif; ?>

                <!-- Page Numbers -->
                <div class="pagination-numbers" style="display: flex; gap: 6px; align-items: center;">
                  <?php 
                    $start = max(1, $currentPage - 2);
                    $end = min($totalPages, $currentPage + 2);
                    
                    if ($start > 1): ?>
                      <a href="?page=1" class="pagination-page-btn">1</a>
                      <span style="color: #999;">...</span>
                    <?php endif;
                    
                    for ($i = $start; $i <= $end; $i++): ?>
                      <a href="?page=<?php echo $i; ?>" class="pagination-page-btn <?php echo $i === $currentPage ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                      </a>
                    <?php endfor;
                    
                    if ($end < $totalPages): ?>
                      <span style="color: #999;">...</span>
                      <a href="?page=<?php echo $totalPages; ?>" class="pagination-page-btn"><?php echo $totalPages; ?></a>
                    <?php endif; ?>
                </div>

                <!-- Next Button -->
                <?php if ($currentPage < $totalPages): ?>
                  <a href="?page=<?php echo $currentPage + 1; ?>" class="pagination-nav-btn" title="Next Page">
                    <span style="font-size: 20px;">›</span>
                  </a>
                <?php else: ?>
                  <span class="pagination-nav-btn disabled" style="opacity: 0.5; cursor: not-allowed;">
                    <span style="font-size: 20px;">›</span>
                  </span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include 'templates/footer.php';
