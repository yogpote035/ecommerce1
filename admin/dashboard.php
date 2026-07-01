<?php
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../config/Database.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: ../auth.php?role=admin&mode=login');
    exit;
}

function tableExists($conn, $table) {
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
}

function columnExists($conn, $table, $column) {
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
}

function scalarQuery($conn, $sql, $fallback = 0) {
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        return $fallback;
    }
    $row = mysqli_fetch_row($result);
    return $row ? ($row[0] ?? $fallback) : $fallback;
}

$hasPayments = tableExists($conn, 'payments');
$hasStatusLog = tableExists($conn, 'order_status_log');
$orderDateColumn = columnExists($conn, 'orders', 'created_at') ? 'created_at' : (columnExists($conn, 'orders', 'order_date') ? 'order_date' : null);

$stats = [
    'products' => (int)scalarQuery($conn, 'SELECT COUNT(*) FROM apadd'),
    'customers' => (int)scalarQuery($conn, 'SELECT COUNT(*) FROM cregister'),
    'orders' => (int)scalarQuery($conn, 'SELECT COUNT(*) FROM orders'),
    'revenue' => (float)scalarQuery($conn, 'SELECT COALESCE(SUM(cost), 0) FROM orders'),
    'low_stock' => (int)scalarQuery($conn, 'SELECT COUNT(*) FROM apadd WHERE apqty > 0 AND apqty <= 5'),
    'out_of_stock' => (int)scalarQuery($conn, 'SELECT COUNT(*) FROM apadd WHERE apqty <= 0'),
    'paid' => $hasPayments ? (int)scalarQuery($conn, "SELECT COUNT(*) FROM payments WHERE payment_status = 'Paid'") : 0,
    'pending_payment' => $hasPayments ? (int)scalarQuery($conn, "SELECT COUNT(*) FROM payments WHERE payment_status = 'Pending'") : 0,
];

$categoryRows = [];
$categoryResult = mysqli_query($conn, "
    SELECT
        TRIM(SUBSTRING_INDEX(COALESCE(apcategory, 'Uncategorized'), '>', 1)) AS category,
        COUNT(*) AS total
    FROM apadd
    GROUP BY category
    ORDER BY total DESC, category ASC
    LIMIT 8
");
if ($categoryResult) {
    while ($row = mysqli_fetch_assoc($categoryResult)) {
        $categoryRows[] = $row;
    }
}
$maxCategoryTotal = max(array_map(function ($row) {
    return (int)$row['total'];
}, $categoryRows ?: [['total' => 1]]));

$salesRows = [];
if ($orderDateColumn) {
    $salesResult = mysqli_query($conn, "
        SELECT DATE($orderDateColumn) AS order_day, COALESCE(SUM(cost), 0) AS total
        FROM orders
        WHERE $orderDateColumn >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        GROUP BY DATE($orderDateColumn)
        ORDER BY order_day ASC
    ");
    $salesMap = [];
    if ($salesResult) {
        while ($row = mysqli_fetch_assoc($salesResult)) {
            $salesMap[$row['order_day']] = (float)$row['total'];
        }
    }
    for ($i = 6; $i >= 0; $i--) {
        $day = date('Y-m-d', strtotime("-$i days"));
        $salesRows[] = [
            'label' => date('M d', strtotime($day)),
            'total' => $salesMap[$day] ?? 0,
        ];
    }
}
$maxSalesTotal = max(array_map(function ($row) {
    return (float)$row['total'];
}, $salesRows ?: [['total' => 1]]));

$dateSelect = $orderDateColumn ? "o.$orderDateColumn AS order_created_at" : 'NULL AS order_created_at';
$paymentJoin = $hasPayments ? 'LEFT JOIN payments p ON p.order_id = o.oid' : '';
$paymentSelect = $hasPayments ? "COALESCE(p.payment_method, 'COD') AS payment_method, COALESCE(p.payment_status, 'Pending') AS payment_status" : "'COD' AS payment_method, 'Pending' AS payment_status";
$statusJoin = $hasStatusLog ? "
    LEFT JOIN (
        SELECT l.order_id, l.status AS latest_status
        FROM order_status_log l
        INNER JOIN (
            SELECT order_id, MAX(id) AS max_id
            FROM order_status_log
            GROUP BY order_id
        ) latest ON latest.max_id = l.id
    ) osl ON osl.order_id = o.oid
" : '';
$statusSelect = $hasStatusLog ? "COALESCE(osl.latest_status, 'Pending') AS latest_status" : "'Pending' AS latest_status";

$recentOrders = [];
$recentResult = mysqli_query($conn, "
    SELECT o.oid, o.cid, o.cost, $dateSelect, $paymentSelect, $statusSelect
    FROM orders o
    $paymentJoin
    $statusJoin
    ORDER BY o.oid DESC
    LIMIT 8
");
if ($recentResult) {
    while ($row = mysqli_fetch_assoc($recentResult)) {
        $recentOrders[] = $row;
    }
}

$lowStockProducts = [];
$lowStockResult = mysqli_query($conn, 'SELECT Apid, apname, apbrand, apqty FROM apadd WHERE apqty <= 5 ORDER BY apqty ASC, apname ASC LIMIT 8');
if ($lowStockResult) {
    while ($row = mysqli_fetch_assoc($lowStockResult)) {
        $lowStockProducts[] = $row;
    }
}
    $siteTitle = 'Admin Dashboard';
    include __DIR__ . '/../templates/header.php';
?>
    <style>
        .dashboard-shell { max-width: 1320px; margin: 0 auto; padding: .75rem 1rem 1.5rem; }
        .metric-card { border: 1px solid #e5e7eb; border-radius: 8px; background: #fff; padding: 1.25rem; height: 100%; }
        .metric-label { color: #64748b; font-weight: 700; text-transform: uppercase; font-size: .75rem; letter-spacing: .04em; }
        .metric-value { font-size: 2rem; font-weight: 800; color: #0f172a; line-height: 1.1; margin-top: .4rem; }
        .report-card { border: 1px solid #e5e7eb; border-radius: 8px; background: #fff; box-shadow: 0 8px 28px rgba(15, 23, 42, .05); }
        .bar-row { display: grid; grid-template-columns: minmax(110px, 160px) 1fr 80px; gap: .75rem; align-items: center; margin-bottom: .75rem; }
        .bar-track { height: 12px; border-radius: 999px; background: #e2e8f0; overflow: hidden; }
        .bar-fill { height: 100%; border-radius: 999px; background: #2563eb; min-width: 4px; }
        .sales-bars { display: grid; grid-template-columns: repeat(7, 1fr); gap: .75rem; min-height: 220px; align-items: end; }
        .sales-bar-wrap { display: flex; flex-direction: column; align-items: center; gap: .5rem; height: 220px; justify-content: flex-end; }
        .sales-bar { width: 100%; max-width: 42px; min-height: 4px; border-radius: 6px 6px 0 0; background: #16a34a; }
        .sales-label { font-size: .75rem; color: #64748b; text-align: center; }
        .quick-actions .btn { min-width: 150px; }
    </style>
<div class="dashboard-shell">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div>
            <h1 class="h2 mb-1">Admin Dashboard</h1>
            <p class="text-muted mb-0">Operational snapshot for products, customers, orders, payments, and stock.</p>
        </div>
        <div class="quick-actions mt-3 mt-md-0">
            <a href="<?php echo htmlspecialchars(app_url('admin_products.php')); ?>" class="btn btn-primary">Products</a>
            <a href="<?php echo htmlspecialchars(app_url('admin_categories.php')); ?>" class="btn btn-outline-primary">Categories</a>
            <a href="<?php echo htmlspecialchars(app_url('admin_customers.php')); ?>" class="btn btn-outline-primary">Customers</a>
            <a href="<?php echo htmlspecialchars(app_url('admin_orders.php')); ?>" class="btn btn-outline-primary">Orders</a>
            <a href="<?php echo htmlspecialchars(app_url('admin/backup-manager.php')); ?>" class="btn btn-outline-secondary">Backups</a>
            <a href="<?php echo htmlspecialchars(app_url('admin_logs.php')); ?>" class="btn btn-outline-secondary">Logs</a>
            <a href="<?php echo htmlspecialchars(app_url('Home.php')); ?>" class="btn btn-outline-secondary">Storefront</a>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6 col-xl-3 mb-3"><div class="metric-card"><div class="metric-label">Products</div><div class="metric-value"><?php echo number_format($stats['products']); ?></div></div></div>
        <div class="col-sm-6 col-xl-3 mb-3"><div class="metric-card"><div class="metric-label">Customers</div><div class="metric-value"><?php echo number_format($stats['customers']); ?></div></div></div>
        <div class="col-sm-6 col-xl-3 mb-3"><div class="metric-card"><div class="metric-label">Orders</div><div class="metric-value"><?php echo number_format($stats['orders']); ?></div></div></div>
        <div class="col-sm-6 col-xl-3 mb-3"><div class="metric-card"><div class="metric-label">Revenue</div><div class="metric-value">Rs <?php echo number_format($stats['revenue'], 2); ?></div></div></div>
        <div class="col-sm-6 col-xl-3 mb-3"><div class="metric-card"><div class="metric-label">Paid Payments</div><div class="metric-value"><?php echo number_format($stats['paid']); ?></div></div></div>
        <div class="col-sm-6 col-xl-3 mb-3"><div class="metric-card"><div class="metric-label">Pending Payments</div><div class="metric-value"><?php echo number_format($stats['pending_payment']); ?></div></div></div>
        <div class="col-sm-6 col-xl-3 mb-3"><div class="metric-card"><div class="metric-label">Low Stock</div><div class="metric-value"><?php echo number_format($stats['low_stock']); ?></div></div></div>
        <div class="col-sm-6 col-xl-3 mb-3"><div class="metric-card"><div class="metric-label">Out of Stock</div><div class="metric-value"><?php echo number_format($stats['out_of_stock']); ?></div></div></div>
    </div>

    <div class="row mt-2">
        <div class="col-lg-7 mb-4">
            <div class="report-card p-4 h-100">
                <h2 class="h5 mb-3">Sales This Week</h2>
                <?php if (empty($salesRows)): ?>
                    <p class="text-muted mb-0">Add `created_at` or `order_date` to orders to show daily sales trends.</p>
                <?php else: ?>
                    <div class="sales-bars">
                        <?php foreach ($salesRows as $row): ?>
                            <?php $height = $maxSalesTotal > 0 ? max(4, (int)(($row['total'] / $maxSalesTotal) * 170)) : 4; ?>
                            <div class="sales-bar-wrap">
                                <div class="small font-weight-bold">Rs <?php echo number_format($row['total'], 0); ?></div>
                                <div class="sales-bar" style="height: <?php echo $height; ?>px;"></div>
                                <div class="sales-label"><?php echo htmlspecialchars($row['label']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-lg-5 mb-4">
            <div class="report-card p-4 h-100">
                <h2 class="h5 mb-3">Top Categories</h2>
                <?php if (empty($categoryRows)): ?>
                    <p class="text-muted mb-0">No product categories found.</p>
                <?php else: ?>
                    <?php foreach ($categoryRows as $row): ?>
                        <?php $width = $maxCategoryTotal > 0 ? (int)(((int)$row['total'] / $maxCategoryTotal) * 100) : 0; ?>
                        <div class="bar-row">
                            <div class="text-truncate"><?php echo htmlspecialchars($row['category'] ?: 'Uncategorized'); ?></div>
                            <div class="bar-track"><div class="bar-fill" style="width: <?php echo $width; ?>%;"></div></div>
                            <div class="text-right font-weight-bold"><?php echo (int)$row['total']; ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="report-card p-4">
                <h2 class="h5 mb-3">Recent Orders</h2>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr><th>Order</th><th>Customer</th><th>Amount</th><th>Payment</th><th>Status</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentOrders)): ?>
                                <tr><td colspan="6" class="text-muted">No orders found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td><a href="../admin_order_details.php?oid=<?php echo (int)$order['oid']; ?>">#<?php echo (int)$order['oid']; ?></a></td>
                                        <td><?php echo htmlspecialchars($order['cid'] ?? 'N/A'); ?></td>
                                        <td>Rs <?php echo number_format((float)$order['cost'], 2); ?></td>
                                        <td><?php echo htmlspecialchars(($order['payment_method'] ?? 'COD') . ' / ' . ($order['payment_status'] ?? 'Pending')); ?></td>
                                        <td><?php echo htmlspecialchars($order['latest_status'] ?? 'Pending'); ?></td>
                                        <td><?php echo !empty($order['order_created_at']) ? htmlspecialchars(date('M d, Y', strtotime($order['order_created_at']))) : 'N/A'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="report-card p-4">
                <h2 class="h5 mb-3">Stock Watch</h2>
                <?php if (empty($lowStockProducts)): ?>
                    <p class="text-muted mb-0">No low stock products.</p>
                <?php else: ?>
                    <?php foreach ($lowStockProducts as $product): ?>
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <a href="../admin_product_edit.php?id=<?php echo (int)$product['Apid']; ?>" class="font-weight-bold"><?php echo htmlspecialchars($product['apname']); ?></a>
                                <div class="small text-muted"><?php echo htmlspecialchars($product['apbrand'] ?? ''); ?></div>
                            </div>
                            <span class="badge badge-<?php echo (int)$product['apqty'] <= 0 ? 'danger' : 'warning'; ?>"><?php echo (int)$product['apqty']; ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../templates/footer.php'; ?>
