<?php
require_once 'init.php';

$siteTitle = 'Admin Customers';

if (empty($_SESSION['admin_logged_in']) || empty($_SESSION['admin_id'])) {
    header('Location: auth.php?role=admin&mode=login');
    exit;
}

$search = trim($_GET['q'] ?? '');
$where = '';
$params = [];
$types = '';
if ($search !== '') {
    $where = 'WHERE c.Cname LIKE ? OR c.Cemail LIKE ? OR c.Ccontact LIKE ?';
    $like = '%' . $search . '%';
    $params = [$like, $like, $like];
    $types = 'sss';
}
$itemsPerPage = PaginationHelper::PER_PAGE;
$currentPage = PaginationHelper::currentPage();
$offset = PaginationHelper::offset($currentPage, $itemsPerPage);
$totalItems = 0;
$countSql = "SELECT COUNT(*) AS total FROM cregister c $where";
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

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_customer_product (customer_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$sql = "SELECT c.Cid, c.Cname, c.Cemail, c.Ccontact, c.Cadd,
        COUNT(DISTINCT o.oid) AS order_count,
        COALESCE(SUM(o.cost), 0) AS total_spend,
        COUNT(DISTINCT w.id) AS wishlist_count
    FROM cregister c
    LEFT JOIN orders o ON o.cid = c.Cid
    LEFT JOIN wishlist w ON w.customer_id = c.Cid
    $where
    GROUP BY c.Cid, c.Cname, c.Cemail, c.Ccontact, c.Cadd
    ORDER BY c.Cid DESC
    LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    $queryParams = array_merge($params, [$itemsPerPage, $offset]);
    $queryTypes = $types . 'ii';
    mysqli_stmt_bind_param($stmt, $queryTypes, ...$queryParams);
}
if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = false;
}

include 'templates/header.php';
?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
  <div>
    <h1 class="h4 mb-1">Customer Management</h1>
    <p class="text-secondary mb-0">Review customer accounts, order activity, and wishlist engagement.</p>
  </div>
  <a href="admin/dashboard.php" class="btn btn-outline-secondary mt-3 mt-md-0">Dashboard</a>
</div>

<div class="card sidebar-card shadow-sm mb-4">
  <div class="card-body">
    <form method="get" class="row">
      <div class="col-md-9 mb-2 mb-md-0">
        <input type="search" name="q" class="form-control" placeholder="Search by name, email, or phone" value="<?php echo htmlspecialchars($search); ?>">
      </div>
      <div class="col-md-3">
        <button class="btn btn-primary w-100">Search</button>
      </div>
    </form>
  </div>
</div>

<div class="card sidebar-card shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table mb-0">
        <thead>
          <tr><th>Customer</th><th>Contact</th><th>Address</th><th>Orders</th><th>Total Spend</th><th>Wishlist</th></tr>
        </thead>
        <tbody>
          <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while ($customer = mysqli_fetch_assoc($result)): ?>
              <tr>
                <td><strong>#<?php echo (int) $customer['Cid']; ?></strong><br><?php echo htmlspecialchars($customer['Cname']); ?></td>
                <td><?php echo htmlspecialchars($customer['Cemail'] ?? ''); ?><br><span class="text-secondary"><?php echo htmlspecialchars($customer['Ccontact'] ?? ''); ?></span></td>
                <td><?php echo htmlspecialchars($customer['Cadd'] ?? ''); ?></td>
                <td><?php echo number_format((int) $customer['order_count']); ?></td>
                <td>₹<?php echo number_format((float) $customer['total_spend'], 2); ?></td>
                <td><?php echo number_format((int) $customer['wishlist_count']); ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="6" class="text-center py-5 text-secondary">No customers found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php PaginationHelper::render($currentPage, $totalPages, $totalItems, $itemsPerPage, 'customers'); ?>

<?php
if ($stmt) {
    mysqli_stmt_close($stmt);
}
include 'templates/footer.php';
?>
