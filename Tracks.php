<?php
$orderId = (int)($_GET['order'] ?? $_GET['order_id'] ?? $_GET['oid'] ?? 0);
$customerId = (int)($_SESSION['customer_id'] ?? $_SESSION['cid'] ?? 0);
$isAdminTracking = !empty($_SESSION['admin_logged_in']);
$orders = [];
$itemsPerPage = PaginationHelper::PER_PAGE;
$currentPage = PaginationHelper::currentPage();
$offset = PaginationHelper::offset($currentPage, $itemsPerPage);
$totalItems = 0;
$totalPages = 1;

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
    return (int) $count > 0;
};

$hasPayments = $tableExists('payments');
$hasStatusLog = $tableExists('order_status_log');
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
$orderDateColumn = $columnExists('orders', 'created_at') ? 'created_at' : ($columnExists('orders', 'order_date') ? 'order_date' : null);
$orderDateSelect = $orderDateColumn ? "o.$orderDateColumn AS order_date" : 'NULL AS order_date';
$paymentSelect = $hasPayments ? "COALESCE(p.payment_method, 'COD') AS payment_method, COALESCE(p.payment_status, 'Pending') AS payment_status" : "'COD' AS payment_method, 'Pending' AS payment_status";
$paymentJoin = $hasPayments ? 'LEFT JOIN payments p ON o.oid = p.order_id' : '';

$baseSql = "
    SELECT
        o.oid,
        o.pid,
        o.cid,
        o.cost,
        o.source,
        o.destination,
        $orderDateSelect,
        a.apname,
        a.apimage,
        $paymentSelect
    FROM orders o
    LEFT JOIN apadd a ON a.Apid = o.pid
    $paymentJoin
";

if ($orderId > 0) {
    $sql = $baseSql . ' WHERE o.oid = ?';
    $types = 'i';
    $params = [$orderId];
    if ($customerId > 0 && !$isAdminTracking) {
        $sql .= ' AND o.cid = ?';
        $types .= 'i';
        $params[] = $customerId;
    }
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);
    } else {
        $result = false;
    }
} else {
    if ($customerId > 0 && !$isAdminTracking) {
        $countStmt = mysqli_prepare($conn, 'SELECT COUNT(*) AS total FROM orders o WHERE o.cid = ?');
        if ($countStmt) {
            mysqli_stmt_bind_param($countStmt, 'i', $customerId);
            mysqli_stmt_execute($countStmt);
            $countResult = mysqli_stmt_get_result($countStmt);
            $totalItems = (int)(mysqli_fetch_assoc($countResult)['total'] ?? 0);
            mysqli_stmt_close($countStmt);
        }
        $totalPages = PaginationHelper::totalPages($totalItems, $itemsPerPage);
        $currentPage = min($currentPage, $totalPages);
        $offset = PaginationHelper::offset($currentPage, $itemsPerPage);
        $stmt = mysqli_prepare($conn, $baseSql . ' WHERE o.cid = ? ORDER BY o.oid DESC LIMIT ? OFFSET ?');
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'iii', $customerId, $itemsPerPage, $offset);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            mysqli_stmt_close($stmt);
        } else {
            $result = false;
        }
    } else {
        $countResult = mysqli_query($conn, 'SELECT COUNT(*) AS total FROM orders');
        if ($countResult) {
            $totalItems = (int)(mysqli_fetch_assoc($countResult)['total'] ?? 0);
        }
        $totalPages = PaginationHelper::totalPages($totalItems, $itemsPerPage);
        $currentPage = min($currentPage, $totalPages);
        $offset = PaginationHelper::offset($currentPage, $itemsPerPage);
        $result = mysqli_query($conn, $baseSql . " ORDER BY o.oid DESC LIMIT $itemsPerPage OFFSET $offset");
    }
}

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }
}

$statusSteps = ['Pending', 'Confirmed', 'Packed', 'Shipped', 'Out For Delivery', 'Delivered'];

$getOrderTimeline = function ($trackedOrderId, $paymentStatus = 'Pending') use ($conn, $hasStatusLog, $statusSteps) {
    $logs = [];
    if ($hasStatusLog) {
        $stmt = mysqli_prepare($conn, 'SELECT status, notes, created_at FROM order_status_log WHERE order_id = ? ORDER BY created_at ASC, id ASC');
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'i', $trackedOrderId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) {
                $logs[$row['status']] = $row;
            }
            mysqli_stmt_close($stmt);
        }
    }

    if (empty($logs)) {
        $fallbackStatus = strtolower((string) $paymentStatus) === 'paid' ? 'Confirmed' : 'Pending';
        $logs[$fallbackStatus] = [
            'status' => $fallbackStatus,
            'notes' => $fallbackStatus === 'Confirmed' ? 'Payment received and order confirmed.' : 'Order received and waiting for confirmation.',
            'created_at' => null,
        ];
    }

    $latestStatus = array_key_last($logs);
    $latestIndex = array_search($latestStatus, $statusSteps, true);
    if ($latestIndex === false) {
        $latestIndex = 0;
    }

    return [$logs, $latestStatus, $latestIndex];
};

if (empty($orders)) {
    $message = $orderId > 0 ? 'No order found for that tracking ID.' : 'No recent orders available.';
    echo '<div class="alert alert-warning">' . htmlspecialchars($message) . '</div>';
    return;
}

if ($orderId > 0) {
    $order = $orders[0];
    [$logs, $latestStatus, $latestIndex] = $getOrderTimeline((int) $order['oid'], $order['payment_status'] ?? 'Pending');
    ?>
    <div class="tracking-summary mb-4">
      <div class="row align-items-center">
        <div class="col-md-8">
          <p class="text-muted mb-1">Tracking result</p>
          <h2 class="h5 mb-2">Order #<?php echo (int) $order['oid']; ?></h2>
          <p class="mb-0">
            <?php echo htmlspecialchars($order['apname'] ?? ('Product #' . (int) $order['pid'])); ?>
            <span class="text-muted">·</span>
            <?php echo htmlspecialchars($order['payment_method'] ?? 'COD'); ?>
            <span class="text-muted">·</span>
            Rs <?php echo number_format((float) $order['cost'], 2); ?>
          </p>
        </div>
        <div class="col-md-4 text-md-right mt-3 mt-md-0">
          <span class="badge badge-pill badge-primary tracking-status-badge"><?php echo htmlspecialchars($latestStatus); ?></span>
        </div>
      </div>
    </div>

    <div class="tracking-timeline mb-4">
      <?php foreach ($statusSteps as $index => $step): ?>
        <?php
          $isComplete = $index <= $latestIndex;
          $log = $logs[$step] ?? null;
        ?>
        <div class="tracking-step <?php echo $isComplete ? 'is-complete' : ''; ?>">
          <div class="tracking-dot"></div>
          <div class="tracking-content">
            <h3 class="h6 mb-1"><?php echo htmlspecialchars($step); ?></h3>
            <p class="text-muted small mb-0">
              <?php
                if ($log) {
                    echo htmlspecialchars($log['notes'] ?: 'Status updated.');
                    if (!empty($log['created_at'])) {
                        echo ' · ' . htmlspecialchars(date('M d, Y h:i A', strtotime($log['created_at'])));
                    }
                } else {
                    echo 'Awaiting update.';
                }
              ?>
            </p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <h3 class="h6 font-weight-bold">Delivery details</h3>
        <p class="mb-1"><strong>From:</strong> <?php echo htmlspecialchars($order['source'] ?? 'N/A'); ?></p>
        <p class="mb-0"><strong>To:</strong> <?php echo htmlspecialchars($order['destination'] ?? 'N/A'); ?></p>
      </div>
    </div>
    <?php
    return;
}

echo '<div class="mb-3"><p class="text-muted mb-2">Recent orders for quick tracking.</p></div>';
echo '<div class="table-responsive"><table class="table table-borderless align-middle">';
echo '<thead class="text-secondary"><tr><th>Tracking ID</th><th>Product</th><th>Cost</th><th>Payment</th><th>Delivery Address</th><th>Status</th><th></th></tr></thead><tbody>';

foreach ($orders as $row) {
    [$logs, $latestStatus] = $getOrderTimeline((int) $row['oid'], $row['payment_status'] ?? 'Pending');
    echo '<tr>';
    echo '<td>#' . (int) $row['oid'] . '</td>';
    echo '<td>' . htmlspecialchars($row['apname'] ?? ('Product #' . (int) $row['pid'])) . '</td>';
    echo '<td>Rs ' . number_format((float) $row['cost'], 2) . '</td>';
    echo '<td>' . htmlspecialchars($row['payment_method'] ?? 'COD') . '</td>';
    echo '<td>' . htmlspecialchars($row['destination'] ?? 'N/A') . '</td>';
    echo '<td><span class="badge badge-pill badge-primary">' . htmlspecialchars($latestStatus) . '</span></td>';
    echo '<td><a class="btn btn-sm btn-outline-primary" href="Track.php?order=' . (int) $row['oid'] . '">View</a></td>';
    echo '</tr>';
}

echo '</tbody></table></div>';
PaginationHelper::render($currentPage, $totalPages, $totalItems, $itemsPerPage, 'orders');
