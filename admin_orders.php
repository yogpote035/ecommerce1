<?php
require_once 'init.php';
$siteTitle = 'Admin - Order Management';
$csrfToken = SecurityHelper::generateCSRFToken();

// Check admin authentication
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: auth.php?role=admin&mode=login');
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
$orderDateColumn = $columnExists('orders', 'created_at') ? 'created_at' : ($columnExists('orders', 'order_date') ? 'order_date' : null);
$orderDateSelect = $orderDateColumn ? "o.$orderDateColumn AS order_created_at" : 'NULL AS order_created_at';
$paymentJoin = $hasPayments ? 'LEFT JOIN payments p ON o.oid = p.order_id' : '';
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
    ) osl ON o.oid = osl.order_id
" : '';
$statusSelect = $hasStatusLog ? "COALESCE(osl.latest_status, 'Pending') AS latest_status" : "'Pending' AS latest_status";
$itemsPerPage = PaginationHelper::PER_PAGE;
$currentPage = PaginationHelper::currentPage();
$offset = PaginationHelper::offset($currentPage, $itemsPerPage);
$totalItems = 0;
$countResult = mysqli_query($conn, 'SELECT COUNT(*) AS total FROM orders');
if ($countResult) {
    $totalItems = (int)(mysqli_fetch_assoc($countResult)['total'] ?? 0);
}
$totalPages = PaginationHelper::totalPages($totalItems, $itemsPerPage);
$currentPage = min($currentPage, $totalPages);
$offset = PaginationHelper::offset($currentPage, $itemsPerPage);

$totalCount = $totalItems;
$paidCount = 0;
$pendingCount = 0;
$totalRevenueValue = 0.0;
if ($hasPayments) {
    $paidOrders = mysqli_query($conn, "SELECT COUNT(*) AS count FROM payments WHERE payment_status = 'Paid'");
    $paidCount = $paidOrders ? (int)(mysqli_fetch_assoc($paidOrders)['count'] ?? 0) : 0;

    $pendingPayments = mysqli_query($conn, "SELECT COUNT(*) AS count FROM payments WHERE payment_status = 'Pending'");
    $pendingCount = $pendingPayments ? (int)(mysqli_fetch_assoc($pendingPayments)['count'] ?? 0) : 0;
}
$totalRevenue = mysqli_query($conn, "SELECT SUM(cost) AS total FROM orders");
if ($totalRevenue) {
    $revenueRow = mysqli_fetch_assoc($totalRevenue);
    $totalRevenueValue = (float)($revenueRow['total'] ?? 0);
}

include 'templates/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h2">Order Management</h1>
            <p class="text-muted">View and manage all customer orders</p>
        </div>
    </div>

    <!-- Payment Statistics -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Total Orders</h5>
                    <p class="h3 text-primary mb-0"><?php echo number_format($totalCount); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Paid Orders</h5>
                    <p class="h3 text-success mb-0"><?php echo number_format($paidCount); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Pending Payments</h5>
                    <p class="h3 text-warning mb-0"><?php echo number_format($pendingCount); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Total Revenue</h5>
                    <p class="h3 text-info mb-0">Rs. <?php echo number_format($totalRevenueValue, 2); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>OID</th>
                                    <th>CID</th>
                                    <th>Source</th>
                                    <th>Destination</th>
                                    <th>Price</th>
                                    <th>Method</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Fetch all orders with payment information and latest order status
                                $query = "
                                    SELECT
                                        o.oid,
                                        o.cid,
                                        o.source,
                                        o.destination,
                                        o.cost,
                                        $paymentSelect,
                                        $statusSelect,
                                        $orderDateSelect
                                    FROM orders o
                                    $paymentJoin
                                    $statusJoin
                                    ORDER BY o.oid DESC
                                    LIMIT $itemsPerPage OFFSET $offset
                                ";
                                
                                $result = mysqli_query($conn, $query);
                                
                                if (!$result) {
                                    echo '<tr><td colspan="10" class="text-danger text-center">Error fetching orders: ' . htmlspecialchars(mysqli_error($conn)) . '</td></tr>';
                                } elseif (mysqli_num_rows($result) === 0) {
                                    echo '<tr><td colspan="10" class="text-center text-muted">No orders found</td></tr>';
                                } else {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $paymentMethod = $row['payment_method'] ?? 'COD';
                                        $paymentStatus = $row['payment_status'] ?? 'Pending';
                                        $orderStatus = $row['latest_status'] ?? 'Pending';
                                        
                                        // Color coding for payment status
                                        $paymentStatusClass = 'secondary';
                                        if ($paymentStatus === 'Paid') {
                                            $paymentStatusClass = 'success';
                                        } elseif ($paymentStatus === 'Failed') {
                                            $paymentStatusClass = 'danger';
                                        }
                                        
                                        // Color coding for order status
                                        $orderStatusClass = 'warning';
                                        if ($orderStatus === 'Delivered') {
                                            $orderStatusClass = 'success';
                                        } elseif ($orderStatus === 'Cancelled') {
                                            $orderStatusClass = 'danger';
                                        } elseif ($orderStatus === 'Confirmed' || $orderStatus === 'Packed' || $orderStatus === 'Shipped' || $orderStatus === 'Out For Delivery') {
                                            $orderStatusClass = 'info';
                                        }
                                        
                                        echo '<tr>';
                                        echo '<td><strong>#' . htmlspecialchars($row['oid']) . '</strong></td>';
                                        echo '<td>' . htmlspecialchars($row['cid']) . '</td>';
                                        echo '<td>' . htmlspecialchars(substr($row['source'], 0, 25)) . '...</td>';
                                        echo '<td>' . htmlspecialchars(substr($row['destination'], 0, 25)) . '...</td>';
                                        echo '<td>₹' . number_format($row['cost'], 2) . '</td>';
                                        echo '<td>';
                                        if ($paymentMethod === 'Razorpay') {
                                            echo '<span class="badge badge-info">Razorpay</span>';
                                        } else {
                                            echo '<span class="badge badge-secondary">COD</span>';
                                        }
                                        echo '</td>';
                                        echo '<td><span class="badge badge-' . $paymentStatusClass . '">' . htmlspecialchars($paymentStatus) . '</span></td>';
                                        echo '<td><span class="badge badge-' . $orderStatusClass . '">' . htmlspecialchars($orderStatus) . '</span></td>';
                                        $createdAt = $row['order_created_at'] ?? '';
                                        echo '<td>' . ($createdAt ? date('M d, Y', strtotime($createdAt)) : 'N/A') . '</td>';
                                        echo '<td>';
                                        echo '<div class="action-buttons mb-2">';
                                        echo '<a href="admin_order_details.php?oid=' . $row['oid'] . '" class="btn btn-sm btn-info">View</a>';
                                        echo '<a href="admin_order_details.php?oid=' . $row['oid'] . '#status-update" class="btn btn-sm btn-outline-secondary">Details</a>';
                                        echo '</div>';
                                        echo '<form method="post" action="admin_update_order_status.php" class="order-status-inline-form">';
                                        echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrfToken) . '">';
                                        echo '<input type="hidden" name="oid" value="' . (int)$row['oid'] . '">';
                                        echo '<input type="hidden" name="redirect" value="' . htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'admin_orders.php') . '">';
                                        echo '<div class="input-group input-group-sm">';
                                        echo '<select name="status" class="custom-select" required>';
                                        $statuses = ['Pending', 'Confirmed', 'Packed', 'Shipped', 'Out For Delivery', 'Delivered', 'Cancelled'];
                                        foreach ($statuses as $statusOption) {
                                            $selected = $statusOption === $orderStatus ? ' selected' : '';
                                            echo '<option value="' . htmlspecialchars($statusOption) . '"' . $selected . '>' . htmlspecialchars($statusOption) . '</option>';
                                        }
                                        echo '</select>';
                                        echo '<div class="input-group-append"><button type="submit" class="btn btn-warning">Update</button></div>';
                                        echo '</div>';
                                        echo '</form>';
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <?php PaginationHelper::render($currentPage, $totalPages, $totalItems, $itemsPerPage, 'orders'); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Statistics moved above the table. -->
    <?php if (false): ?>
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Orders</h5>
                    <p class="h3 text-primary">
                        <?php
                        $totalOrders = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders");
                        $totalCount = mysqli_fetch_assoc($totalOrders)['count'];
                        echo $totalCount;
                        ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Paid Orders</h5>
                    <p class="h3 text-success">
                        <?php
                        $paidCount = 0;
                        if ($hasPayments) {
                            $paidOrders = mysqli_query($conn, "SELECT COUNT(*) as count FROM payments WHERE payment_status = 'Paid'");
                            $paidCount = mysqli_fetch_assoc($paidOrders)['count'] ?? 0;
                        }
                        echo $paidCount;
                        ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Pending Payments</h5>
                    <p class="h3 text-warning">
                        <?php
                        $pendingCount = 0;
                        if ($hasPayments) {
                            $pendingPayments = mysqli_query($conn, "SELECT COUNT(*) as count FROM payments WHERE payment_status = 'Pending'");
                            $pendingCount = mysqli_fetch_assoc($pendingPayments)['count'] ?? 0;
                        }
                        echo $pendingCount;
                        ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Revenue</h5>
                    <p class="h3 text-info">
                        ₹<?php
                        $totalRevenue = mysqli_query($conn, "SELECT SUM(cost) as total FROM orders");
                        $revenueRow = mysqli_fetch_assoc($totalRevenue);
                        echo number_format($revenueRow['total'] ?? 0, 2);
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'templates/footer.php'; ?>
