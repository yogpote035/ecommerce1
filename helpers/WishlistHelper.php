<?php

class WishlistHelper {
    private static function ensureTable($conn) {
        $sql = "CREATE TABLE IF NOT EXISTS wishlist (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT NOT NULL,
            product_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_customer_product (customer_id, product_id),
            KEY idx_customer_id (customer_id),
            KEY idx_product_id (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        return mysqli_query($conn, $sql) !== false;
    }

    public static function isWishlisted($conn, $customerId, $productId) {
        if ($customerId <= 0 || $productId <= 0 || !self::ensureTable($conn)) {
            return false;
        }

        $stmt = mysqli_prepare($conn, 'SELECT id FROM wishlist WHERE customer_id = ? AND product_id = ? LIMIT 1');
        if (!$stmt) {
            return false;
        }
        mysqli_stmt_bind_param($stmt, 'ii', $customerId, $productId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $exists = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);
        return $exists;
    }

    public static function toggle($conn, $customerId, $productId) {
        if ($customerId <= 0 || $productId <= 0 || !self::ensureTable($conn)) {
            return ['ok' => false, 'wishlisted' => false];
        }

        if (self::isWishlisted($conn, $customerId, $productId)) {
            $stmt = mysqli_prepare($conn, 'DELETE FROM wishlist WHERE customer_id = ? AND product_id = ?');
            mysqli_stmt_bind_param($stmt, 'ii', $customerId, $productId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return ['ok' => true, 'wishlisted' => false];
        }

        $stmt = mysqli_prepare($conn, 'INSERT IGNORE INTO wishlist (customer_id, product_id) VALUES (?, ?)');
        mysqli_stmt_bind_param($stmt, 'ii', $customerId, $productId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return ['ok' => true, 'wishlisted' => true];
    }

    public static function countItems($conn, $customerId) {
        if ($customerId <= 0 || !self::ensureTable($conn)) {
            return 0;
        }
        $stmt = mysqli_prepare($conn, 'SELECT COUNT(*) AS total FROM wishlist WHERE customer_id = ?');
        if (!$stmt) {
            return 0;
        }
        mysqli_stmt_bind_param($stmt, 'i', $customerId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = $result ? mysqli_fetch_assoc($result) : [];
        mysqli_stmt_close($stmt);
        return (int)($row['total'] ?? 0);
    }

    public static function getItems($conn, $customerId, $limit = null, $offset = 0) {
        if ($customerId <= 0 || !self::ensureTable($conn)) {
            return [];
        }

        $sql = "SELECT p.Apid, p.apname, p.apbrand, p.apcategory, p.apprice, p.apqty, p.apimage, w.created_at
            FROM wishlist w
            INNER JOIN apadd p ON p.Apid = w.product_id
            WHERE w.customer_id = ?
            ORDER BY w.created_at DESC";
        if ($limit !== null) {
            $sql .= ' LIMIT ? OFFSET ?';
        }
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            return [];
        }

        if ($limit !== null) {
            $limit = max(1, (int)$limit);
            $offset = max(0, (int)$offset);
            mysqli_stmt_bind_param($stmt, 'iii', $customerId, $limit, $offset);
        } else {
            mysqli_stmt_bind_param($stmt, 'i', $customerId);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $items = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);
        return $items;
    }
}
