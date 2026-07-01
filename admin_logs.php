<?php
require_once 'init.php';

$siteTitle = 'Admin Logs';

if (empty($_SESSION['admin_logged_in']) || empty($_SESSION['admin_id'])) {
    header('Location: auth.php?role=admin&mode=login');
    exit;
}

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    user_type ENUM('customer', 'admin', 'retailer') DEFAULT 'customer',
    action VARCHAR(255),
    entity_type VARCHAR(100),
    entity_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_user_created (user_id, created_at),
    KEY idx_action (user_type, action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS error_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    error_message TEXT,
    error_code VARCHAR(50),
    file_path VARCHAR(255),
    line_number INT,
    trace TEXT,
    severity ENUM('info', 'warning', 'error', 'critical') DEFAULT 'error',
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_severity_created (severity, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$itemsPerPage = PaginationHelper::PER_PAGE;
$activityPage = PaginationHelper::currentPage('activity_page');
$errorPage = PaginationHelper::currentPage('error_page');
$activityOffset = PaginationHelper::offset($activityPage, $itemsPerPage);
$errorOffset = PaginationHelper::offset($errorPage, $itemsPerPage);
$activityTotal = 0;
$errorTotal = 0;
$activityCount = mysqli_query($conn, 'SELECT COUNT(*) AS total FROM activity_logs');
if ($activityCount) {
    $activityTotal = (int)(mysqli_fetch_assoc($activityCount)['total'] ?? 0);
}
$errorCount = mysqli_query($conn, 'SELECT COUNT(*) AS total FROM error_logs');
if ($errorCount) {
    $errorTotal = (int)(mysqli_fetch_assoc($errorCount)['total'] ?? 0);
}
$activityPages = PaginationHelper::totalPages($activityTotal, $itemsPerPage);
$errorPages = PaginationHelper::totalPages($errorTotal, $itemsPerPage);
$activityPage = min($activityPage, $activityPages);
$errorPage = min($errorPage, $errorPages);
$activityOffset = PaginationHelper::offset($activityPage, $itemsPerPage);
$errorOffset = PaginationHelper::offset($errorPage, $itemsPerPage);
$activity = mysqli_query($conn, "SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT $itemsPerPage OFFSET $activityOffset");
$errors = mysqli_query($conn, "SELECT * FROM error_logs ORDER BY created_at DESC LIMIT $itemsPerPage OFFSET $errorOffset");

include 'templates/header.php';
?>
<div class="d-flex justify-content-between align-items-start mb-4">
  <div>
    <h1 class="h4 mb-1">Activity & Error Logs</h1>
    <p class="text-secondary mb-0">Review recent application activity and captured errors.</p>
  </div>
  <a href="admin/dashboard.php" class="btn btn-outline-secondary">Dashboard</a>
</div>

<div class="row">
  <div class="col-xl-6 mb-4">
    <div class="card sidebar-card shadow-sm h-100">
      <div class="card-body">
        <h2 class="h5">Recent Activity</h2>
        <div class="table-responsive">
          <table class="table table-sm">
            <thead><tr><th>When</th><th>User</th><th>Action</th><th>Entity</th></tr></thead>
            <tbody>
              <?php if ($activity && mysqli_num_rows($activity) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($activity)): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    <td><?php echo htmlspecialchars(($row['user_type'] ?? '') . ' #' . ($row['user_id'] ?? '')); ?></td>
                    <td><?php echo htmlspecialchars($row['action'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars(($row['entity_type'] ?? '') . ' #' . ($row['entity_id'] ?? '')); ?></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="4" class="text-center text-secondary py-4">No activity logs yet.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <?php PaginationHelper::render($activityPage, $activityPages, $activityTotal, $itemsPerPage, 'activity logs', ['pageKey' => 'activity_page']); ?>
      </div>
    </div>
  </div>
  <div class="col-xl-6 mb-4">
    <div class="card sidebar-card shadow-sm h-100">
      <div class="card-body">
        <h2 class="h5">Recent Errors</h2>
        <div class="table-responsive">
          <table class="table table-sm">
            <thead><tr><th>When</th><th>Severity</th><th>Message</th><th>Location</th></tr></thead>
            <tbody>
              <?php if ($errors && mysqli_num_rows($errors) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($errors)): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    <td><span class="badge badge-secondary"><?php echo htmlspecialchars($row['severity'] ?? 'error'); ?></span></td>
                    <td><?php echo htmlspecialchars($row['error_message'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars(($row['file_path'] ?? '') . ':' . ($row['line_number'] ?? '')); ?></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="4" class="text-center text-secondary py-4">No error logs yet.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <?php PaginationHelper::render($errorPage, $errorPages, $errorTotal, $itemsPerPage, 'error logs', ['pageKey' => 'error_page']); ?>
      </div>
    </div>
  </div>
</div>

<?php include 'templates/footer.php'; ?>
