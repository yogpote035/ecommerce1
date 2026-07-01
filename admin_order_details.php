<?php
require_once 'init.php';
$siteTitle = 'Admin - Order Details';
$csrfToken = SecurityHelper::generateCSRFToken();

// Check admin authentication
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: auth.php?role=admin&mode=login');
    exit();
}

$oid = isset($_GET['oid']) ? (int)$_GET['oid'] : 0;

if (!$oid) {
    header('Location: admin_orders.php');
    exit();
}

$tableExists = function ($table) use ($conn) {
    $stmt = mysqli_prepare($conn, 'SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, 's', $table);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $count);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    return (int)$count > 0;
};

$columnExists = function ($table, $column) use ($conn) {
    $stmt = mysqli_prepare($conn, 'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, 'ss', $table, $column);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $count);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    return (int)$count > 0;
};

$hasPayments = $tableExists('payments');
$hasStatusLog = $tableExists('order_status_log');
$hasPaymentLogs = $tableExists('payment_logs');
$orderDateColumn = $columnExists('orders', 'created_at') ? 'created_at' : ($columnExists('orders', 'order_date') ? 'order_date' : null);
$orderDateSelect = $orderDateColumn ? "o.$orderDateColumn AS order_created_at" : 'NULL AS order_created_at';
$paymentJoin = $hasPayments ? 'LEFT JOIN payments p ON o.oid = p.order_id' : '';
$paymentSelect = $hasPayments ? "p.payment_method, p.payment_status, p.transaction_reference, p.payment_id" : "'COD' AS payment_method, 'Pending' AS payment_status, NULL AS transaction_reference, NULL AS payment_id";
$statusJoin = $hasStatusLog ? "
    LEFT JOIN (
        SELECT l.order_id, l.status AS latest_status
        FROM order_status_log l
        INNER JOIN (
            SELECT order_id, MAX(id) AS max_id
            FROM order_status_log
            GROUP BY order_id
        ) latest ON latest.max_id = l.id
    ) osl ON o.oid = osl.order_id
" : '';
$statusSelect = $hasStatusLog ? "COALESCE(osl.latest_status, 'Pending') AS latest_status" : "'Pending' AS latest_status";

// Fetch order details
$orderQuery = "
    SELECT o.*, $paymentSelect, $statusSelect, $orderDateSelect
    FROM orders o
    $paymentJoin
    $statusJoin
    WHERE o.oid = ?
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $orderQuery);
mysqli_stmt_bind_param($stmt, 'i', $oid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$order) {
    $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Order not found'];
    header('Location: admin_orders.php');
    exit();
}

$statusLog = [];
if ($hasStatusLog) {
    $statusLogQuery = "SELECT * FROM order_status_log WHERE order_id = ? ORDER BY created_at DESC, id DESC";
    $statusStmt = mysqli_prepare($conn, $statusLogQuery);
    mysqli_stmt_bind_param($statusStmt, 'i', $oid);
    mysqli_stmt_execute($statusStmt);
    $statusResult = mysqli_stmt_get_result($statusStmt);
    $statusLog = mysqli_fetch_all($statusResult, MYSQLI_ASSOC);
    mysqli_stmt_close($statusStmt);
}

$paymentLogs = [];
if ($hasPaymentLogs) {
    $paymentLogQuery = "SELECT * FROM payment_logs WHERE order_id = ? ORDER BY created_at DESC";
    $paymentLogStmt = mysqli_prepare($conn, $paymentLogQuery);
    mysqli_stmt_bind_param($paymentLogStmt, 'i', $oid);
    mysqli_stmt_execute($paymentLogStmt);
    $paymentLogResult = mysqli_stmt_get_result($paymentLogStmt);
    $paymentLogs = mysqli_fetch_all($paymentLogResult, MYSQLI_ASSOC);
    mysqli_stmt_close($paymentLogStmt);
}

include 'templates/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h2">Order #<?php echo $order['oid']; ?></h1>
                <a href="admin_orders.php" class="btn btn-secondary">Back to Orders</a>
            </div>
        </div>
    </div>

    <!-- Order Summary -->
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Order Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Order ID:</strong> #<?php echo htmlspecialchars($order['oid']); ?></p>
                            <p><strong>Customer ID:</strong> <?php echo htmlspecialchars($order['cid']); ?></p>
                            <p><strong>Order Date:</strong> <?php echo !empty($order['order_created_at']) ? date('M d, Y H:i', strtotime($order['order_created_at'])) : 'N/A'; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Source (Pickup):</strong> <?php echo htmlspecialchars($order['source']); ?></p>
                            <p><strong>Destination (Drop):</strong> <?php echo htmlspecialchars($order['destination']); ?></p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Amount:</strong> <span class="h5 text-primary">₹<?php echo number_format($order['cost'], 2); ?></span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="card shadow-sm mb-4" id="status-update">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Payment Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Payment Method:</strong>
                                <?php
                                $method = $order['payment_method'] ?? 'COD';
                                if ($method === 'Razorpay') {
                                    echo '<span class="badge badge-info">Razorpay</span>';
                                } else {
                                    echo '<span class="badge badge-secondary">Cash on Delivery (COD)</span>';
                                }
                                ?>
                            </p>
                            <p><strong>Payment Status:</strong>
                                <?php
                                $status = $order['payment_status'] ?? 'Pending';
                                $statusClass = 'secondary';
                                if ($status === 'Paid') $statusClass = 'success';
                                elseif ($status === 'Failed') $statusClass = 'danger';
                                echo '<span class="badge badge-' . $statusClass . '">' . htmlspecialchars($status) . '</span>';
                                ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <?php if (!empty($order['transaction_reference'])): ?>
                            <p><strong>Razorpay Order ID:</strong> <code><?php echo htmlspecialchars($order['transaction_reference']); ?></code></p>
                            <?php endif; ?>
                            <?php if (!empty($order['payment_id'])): ?>
                            <p><strong>Razorpay Payment ID:</strong> <code><?php echo htmlspecialchars($order['payment_id']); ?></code></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Status Timeline -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Order Status Timeline</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($statusLog)): ?>
                    <div class="timeline">
                        <?php foreach ($statusLog as $index => $log): ?>
                        <div class="timeline-item" style="margin-bottom: 20px;">
                            <div class="timeline-marker" style="display: inline-block; width: 12px; height: 12px; background: #0066cc; border-radius: 50%; margin-right: 10px;"></div>
                            <strong><?php echo htmlspecialchars($log['status']); ?></strong>
                            <br>
                            <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($log['created_at'])); ?></small>
                            <?php if (!empty($log['notes'])): ?>
                            <br>
                            <small class="text-muted">Notes: <?php echo htmlspecialchars($log['notes']); ?></small>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p class="text-muted">No status updates yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Status Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Current Status</h5>
                </div>
                <div class="card-body">
                    <p class="mb-3">
                        <strong>Order Status:</strong><br>
                        <span class="badge badge-lg badge-info" style="font-size: 1em; padding: 8px 12px;">
                            <?php echo htmlspecialchars($order['latest_status'] ?? 'Pending'); ?>
                        </span>
                    </p>
                    <form method="post" action="admin_update_order_status.php">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                        <input type="hidden" name="oid" value="<?php echo $order['oid']; ?>">
                        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? ('admin_order_details.php?oid=' . $order['oid'])); ?>">
                        <div class="form-group">
                            <label for="status">Update Status:</label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="">Select new status</option>
                                <option value="Pending">Pending</option>
                                <option value="Confirmed">Confirmed</option>
                                <option value="Packed">Packed</option>
                                <option value="Shipped">Shipped</option>
                                <option value="Out For Delivery">Out For Delivery</option>
                                <option value="Delivered">Delivered</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="notes">Notes:</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Add notes about this status update..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Update Status</button>
                    </form>
                </div>
            </div>

            <!-- Payment Logs -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Payment Logs</h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <?php if (!empty($paymentLogs)): ?>
                    <?php foreach ($paymentLogs as $log): ?>
                    <div class="mb-3 pb-2 border-bottom">
                        <small>
                            <strong><?php echo htmlspecialchars($log['event_type']); ?></strong><br>
                            <span class="text-muted"><?php echo date('M d, H:i', strtotime($log['created_at'])); ?></span><br>
                            <span class="badge badge-<?php echo $log['status'] === 'success' ? 'success' : 'danger'; ?>">
                                <?php echo htmlspecialchars($log['status']); ?>
                            </span>
                            <?php if (!empty($log['error_message'])): ?>
                            <br><span class="text-danger text-small"><?php echo htmlspecialchars($log['error_message']); ?></span>
                            <?php endif; ?>
                        </small>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <p class="text-muted text-center">No payment logs</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
