<?php
require_once 'db.php';

$orderId = isset($_GET['order']) ? (int)$_GET['order'] : 0;

if ($orderId > 0) {
    $sql = "SELECT * FROM orders WHERE oid = $orderId";
} else {
    $sql = "SELECT * FROM orders ORDER BY oid DESC LIMIT 10";
}

$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    echo '<div class="card p-3"><table class="table table-bordered bg-white"><thead><tr><th>Tracking ID</th><th>Product ID</th><th>Customer ID</th><th>Cost</th><th>Source</th><th>Destination</th><th>Status</th></tr></thead><tbody>';
    while ($row = mysqli_fetch_assoc($result)) {
        echo '<tr><td>#' . (int)$row['oid'] . '</td><td>' . (int)$row['pid'] . '</td><td>' . (int)$row['cid'] . '</td><td>₹' . number_format($row['cost'], 2) . '</td><td>' . htmlspecialchars($row['source']) . '</td><td>' . htmlspecialchars($row['destination']) . '</td><td><span class="badge badge-success">Confirmed</span></td></tr>';
    }
    echo '</tbody></table></div>';
} else {
    echo '<div class="alert alert-warning">No order found for that tracking ID.</div>';
}

mysqli_close($conn);
?>