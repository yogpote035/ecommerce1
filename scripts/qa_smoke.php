<?php
/**
 * Non-destructive smoke checks for the ecommerce modernization project.
 *
 * Run from project root:
 *   php scripts/qa_smoke.php
 */

$root = dirname(__DIR__);
chdir($root);

$sessionPath = $root . '/logs/sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0775, true);
}
if (is_dir($sessionPath) && is_writable($sessionPath)) {
    session_save_path($sessionPath);
}

require_once $root . '/init.php';

$checks = [];
$failures = 0;
$warnings = 0;

function addCheck(&$checks, &$failures, &$warnings, $name, $passed, $detail = '', $required = true) {
    $checks[] = [
        'name' => $name,
        'passed' => (bool)$passed,
        'detail' => $detail,
        'required' => (bool)$required,
    ];

    if (!$passed && $required) {
        $failures++;
    } elseif (!$passed) {
        $warnings++;
    }
}

function tableExistsForQa($conn, $table) {
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

function columnExistsForQa($conn, $table, $column) {
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

addCheck($checks, $failures, $warnings, 'Database connection', isset($conn) && $conn instanceof mysqli, DB_NAME);

$requiredTables = [
    'apadd',
    'aregister',
    'cregister',
    'orders',
];

foreach ($requiredTables as $table) {
    addCheck($checks, $failures, $warnings, "Required table: $table", tableExistsForQa($conn, $table));
}

$modernTables = [
    'payments',
    'order_status_log',
    'otp_codes',
    'backup_logs',
    'cart_items',
    'wishlist',
    'search_history',
    'activity_logs',
    'error_logs',
    'remember_tokens',
    'password_resets',
    'categories',
    'sub_categories',
    'child_categories',
    'product_images',
];

foreach ($modernTables as $table) {
    addCheck($checks, $failures, $warnings, "Modern table available: $table", tableExistsForQa($conn, $table), 'Created automatically by app flows if missing.', false);
}

$requiredFiles = [
    'Home.php',
    'index.php',
    'auth.php',
    'checkout.php',
    'Track.php',
    'admin/dashboard.php',
    'admin/backup-manager.php',
    'admin_categories.php',
    'admin_customers.php',
    'admin_logs.php',
    'admin_product_images.php',
    'wishlist.php',
    'wishlist_action.php',
    'forgot_password.php',
    'auto-backup.php',
    'api/payment.php',
    'api/search.php',
    'api/webhook.php',
    'templates/header.php',
    'templates/footer.php',
    'templates/layouts/navbar.php',
    'templates/components/breadcrumb.php',
    'public/assets/css/theme.css',
    'public/assets/js/search.js',
    'public/assets/js/cart.js',
];

foreach ($requiredFiles as $file) {
    addCheck($checks, $failures, $warnings, "Required file: $file", file_exists($root . '/' . $file));
}

$writableDirs = [
    'uploads',
    'logs',
    'backups',
];

foreach ($writableDirs as $dir) {
    $path = $root . '/' . $dir;
    addCheck($checks, $failures, $warnings, "Writable directory: $dir", is_dir($path) && is_writable($path));
}

addCheck($checks, $failures, $warnings, 'apadd.apdescription column', columnExistsForQa($conn, 'apadd', 'apdescription'), 'Admin edit can self-add this column.', false);
addCheck($checks, $failures, $warnings, 'orders date column', columnExistsForQa($conn, 'orders', 'created_at') || columnExistsForQa($conn, 'orders', 'order_date'), 'Dashboard still works without it, but charts need a date column.', false);
addCheck($checks, $failures, $warnings, 'Razorpay mode known or safely disabled', defined('RAZORPAY_MODE') ? in_array(RAZORPAY_MODE, ['test', 'live', 'unknown'], true) : true, defined('RAZORPAY_MODE') ? RAZORPAY_MODE : 'not loaded', false);

foreach ($checks as $check) {
    $label = '[PASS] ';
    if (!$check['passed']) {
        $label = $check['required'] ? '[FAIL] ' : '[WARN] ';
    }
    echo $label . $check['name'];
    if ($check['detail'] !== '') {
        echo ' - ' . $check['detail'];
    }
    echo PHP_EOL;
}

echo PHP_EOL;
if ($failures > 0) {
    echo "Smoke checks failed: $failures" . PHP_EOL;
    exit(1);
}

echo 'Smoke checks passed: ' . count($checks) . ' checks, ' . $warnings . ' warnings' . PHP_EOL;
