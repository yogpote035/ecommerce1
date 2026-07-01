<?php
// Shared initialization for modernized app pages
require_once __DIR__ . '/config/Security.php';
require_once __DIR__ . '/helpers/SecurityHelper.php';
require_once __DIR__ . '/helpers/CartHelper.php';
require_once __DIR__ . '/helpers/WishlistHelper.php';
require_once __DIR__ . '/helpers/MailHelper.php';
require_once __DIR__ . '/helpers/PaginationHelper.php';

// Load environment variables from .env if available
$dotenvFile = __DIR__ . '/.env';
if (file_exists($dotenvFile)) {
    $lines = file($dotenvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') === false) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        $value = trim($value, " \t\n\r\0\x0B\"");

        if ($name === '') {
            continue;
        }

        if (getenv($name) === false) {
            putenv("$name=$value");
        }
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
SecurityHelper::isSessionValid(SESSION_TIMEOUT);

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (!isset($_SESSION['cart_qty'])) {
    $_SESSION['cart_qty'] = [];
}

require_once __DIR__ . '/db.php';

if (!function_exists('app_base_path')) {
    function app_base_path() {
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $projectDir = basename(__DIR__);
        $needle = '/' . $projectDir . '/';
        $position = strpos($scriptName, $needle);
        if ($position !== false) {
            return substr($scriptName, 0, $position + strlen($needle));
        }
        return '/';
    }
}

if (!function_exists('app_url')) {
    function app_url($path = '') {
        $path = ltrim((string)$path, '/');
        return app_base_path() . $path;
    }
}

if (!function_exists('app_absolute_url')) {
    function app_absolute_url($path = '') {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host . app_url($path);
    }
}

function ensureRememberTokenTable($conn) {
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS remember_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        user_type ENUM('customer', 'admin') NOT NULL,
        selector VARCHAR(32) NOT NULL UNIQUE,
        token_hash VARCHAR(255) NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY idx_user (user_id, user_type),
        KEY idx_expires (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $requiredColumns = [
        'user_id' => "ALTER TABLE remember_tokens ADD COLUMN user_id INT NOT NULL AFTER id",
        'user_type' => "ALTER TABLE remember_tokens ADD COLUMN user_type ENUM('customer', 'admin') NOT NULL AFTER user_id",
        'selector' => "ALTER TABLE remember_tokens ADD COLUMN selector VARCHAR(32) NOT NULL AFTER user_type",
        'token_hash' => "ALTER TABLE remember_tokens ADD COLUMN token_hash VARCHAR(255) NOT NULL AFTER selector",
        'expires_at' => "ALTER TABLE remember_tokens ADD COLUMN expires_at DATETIME NOT NULL AFTER token_hash",
        'created_at' => "ALTER TABLE remember_tokens ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER expires_at",
    ];

    foreach ($requiredColumns as $column => $alterSql) {
        $stmt = mysqli_prepare($conn, 'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
        if (!$stmt) {
            continue;
        }
        $table = 'remember_tokens';
        mysqli_stmt_bind_param($stmt, 'ss', $table, $column);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $count);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        if ((int) $count === 0) {
            mysqli_query($conn, $alterSql);
        }
    }

    mysqli_query($conn, 'ALTER TABLE remember_tokens MODIFY selector VARCHAR(32) NOT NULL');
    mysqli_query($conn, 'ALTER TABLE remember_tokens MODIFY token_hash VARCHAR(255) NOT NULL');
    mysqli_query($conn, 'CREATE UNIQUE INDEX remember_selector_unique ON remember_tokens (selector)');
}

function ensurePasswordResetTable($conn) {
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_type ENUM('customer', 'admin') NOT NULL,
        email VARCHAR(255) NOT NULL,
        token_hash VARCHAR(255) NOT NULL,
        is_used TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at DATETIME NOT NULL,
        KEY idx_email_type (email, user_type),
        KEY idx_expires (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

ensureRememberTokenTable($conn);
ensurePasswordResetTable($conn);

function issueRememberToken($conn, $userId, $userType) {
    ensureRememberTokenTable($conn);
    if (!in_array($userType, ['customer', 'admin'], true) || (int) $userId <= 0) {
        return false;
    }

    $selector = bin2hex(random_bytes(8));
    $token = bin2hex(random_bytes(32));
    $hash = hash('sha256', $token);
    $expires = date('Y-m-d H:i:s', time() + (86400 * 30));
    $stmt = mysqli_prepare($conn, 'INSERT INTO remember_tokens (user_id, user_type, selector, token_hash, expires_at) VALUES (?, ?, ?, ?, ?)');
    if (!$stmt) {
        error_log('Remember token prepare failed: ' . mysqli_error($conn));
        return false;
    }

    mysqli_stmt_bind_param($stmt, 'issss', $userId, $userType, $selector, $hash, $expires);
    if (!mysqli_stmt_execute($stmt)) {
        error_log('Remember token insert failed: ' . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }
    mysqli_stmt_close($stmt);
    setcookie('remember_login', $selector . ':' . $token, time() + (86400 * 30), '/', '', false, true);
    return true;
}

if (empty($_SESSION['customer_logged_in']) && empty($_SESSION['admin_logged_in']) && !empty($_COOKIE['remember_login'])) {
    ensureRememberTokenTable($conn);
    $parts = explode(':', $_COOKIE['remember_login'], 2);
    if (count($parts) === 2) {
        [$selector, $token] = $parts;
        $stmt = mysqli_prepare($conn, 'SELECT user_id, user_type, token_hash FROM remember_tokens WHERE selector = ? AND expires_at > NOW() LIMIT 1');
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $selector);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $rememberUserId, $rememberUserType, $rememberHash);
            if (mysqli_stmt_fetch($stmt) && hash_equals($rememberHash, hash('sha256', $token))) {
                if ($rememberUserType === 'customer') {
                    $_SESSION['customer_id'] = (int) $rememberUserId;
                    $_SESSION['cid'] = (int) $rememberUserId;
                    $_SESSION['customer_logged_in'] = true;
                } elseif ($rememberUserType === 'admin') {
                    $_SESSION['admin_id'] = (int) $rememberUserId;
                    $_SESSION['admin_logged_in'] = true;
                }
            }
            mysqli_stmt_close($stmt);
        } else {
            setcookie('remember_login', '', time() - 3600, '/', '', false, true);
        }
    }
}

$customerId = $_SESSION['customer_id'] ?? $_SESSION['cid'] ?? 0;
if ($customerId > 0 && empty($_SESSION['cart'])) {
    CartHelper::loadCustomerCartToSession($conn, $customerId);
}
