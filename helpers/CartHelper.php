<?php

class CartHelper {
    private static function validateConnection($conn) {
        if (!($conn instanceof mysqli)) {
            error_log('CartHelper: invalid mysqli connection passed');
            return false;
        }
        return true;
    }

    public static function ensureCartTableExists($conn) {
        if (!self::validateConnection($conn)) {
            return false;
        }

        $sql = "CREATE TABLE IF NOT EXISTS cart_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_customer_product (customer_id, product_id),
            KEY idx_customer_id (customer_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        if (mysqli_query($conn, $sql) === false) {
            error_log('CartHelper: failed to create or verify cart_items table: ' . mysqli_error($conn));
            return false;
        }

        return true;
    }

    public static function getCustomerCartItems($conn, $customerId) {
        if (!self::ensureCartTableExists($conn)) {
            return [];
        }

        $stmt = mysqli_prepare($conn, "SELECT product_id, quantity FROM cart_items WHERE customer_id = ?");
        if ($stmt === false) {
            error_log('CartHelper: getCustomerCartItems prepare failed: ' . mysqli_error($conn));
            return [];
        }
        mysqli_stmt_bind_param($stmt, 'i', $customerId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $items = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = [
                'product_id' => (int)$row['product_id'],
                'quantity' => (int)$row['quantity']
            ];
        }

        mysqli_stmt_close($stmt);
        return $items;
    }

    public static function countCustomerCartItems($conn, $customerId) {
        if (!self::ensureCartTableExists($conn)) {
            return 0;
        }

        $stmt = mysqli_prepare($conn, "SELECT SUM(quantity) as total_quantity FROM cart_items WHERE customer_id = ?");
        if ($stmt === false) {
            error_log('CartHelper: countCustomerCartItems prepare failed: ' . mysqli_error($conn));
            return 0;
        }
        mysqli_stmt_bind_param($stmt, 'i', $customerId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $totalQuantity);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        return max(0, (int)$totalQuantity);
    }

    public static function loadCustomerCartToSession($conn, $customerId) {
        $items = self::getCustomerCartItems($conn, $customerId);

        $_SESSION['cart'] = [];
        $_SESSION['cart_qty'] = [];

        foreach ($items as $item) {
            $_SESSION['cart'][] = $item['product_id'];
            $_SESSION['cart_qty'][$item['product_id']] = $item['quantity'];
        }
    }

    public static function syncSessionCartToDb($conn, $customerId) {
        self::ensureCartTableExists($conn);

        $sessionCart = $_SESSION['cart'] ?? [];
        $sessionQty = $_SESSION['cart_qty'] ?? [];
        $dbItems = self::getCustomerCartItems($conn, $customerId);

        $existing = [];
        foreach ($dbItems as $item) {
            $existing[$item['product_id']] = $item['quantity'];
        }

        foreach ($sessionCart as $productId) {
            $productId = (int)$productId;
            $quantity = max(1, (int)($sessionQty[$productId] ?? 1));

            if (isset($existing[$productId])) {
                $quantity = $existing[$productId] + $quantity;
                unset($existing[$productId]);
            }

            self::upsertCartItem($conn, $customerId, $productId, $quantity);
        }

        // Keep existing cart items that were already present in DB.
        foreach ($existing as $productId => $quantity) {
            self::upsertCartItem($conn, $customerId, $productId, $quantity);
        }
    }

    public static function addCartItem($conn, $customerId, $productId, $quantity = 1) {
        self::ensureCartTableExists($conn);

        $productId = (int)$productId;
        $quantity = max(1, (int)$quantity);

        $existingQty = self::getCartItemQuantity($conn, $customerId, $productId);
        $newQuantity = $existingQty > 0 ? $existingQty + $quantity : $quantity;

        self::upsertCartItem($conn, $customerId, $productId, $newQuantity);
        self::loadCustomerCartToSession($conn, $customerId);
    }

    public static function updateCartItemQty($conn, $customerId, $productId, $quantity) {
        self::ensureCartTableExists($conn);

        $productId = (int)$productId;
        $quantity = max(1, (int)$quantity);

        self::upsertCartItem($conn, $customerId, $productId, $quantity);
        self::loadCustomerCartToSession($conn, $customerId);
    }

    public static function removeCartItem($conn, $customerId, $productId) {
        self::ensureCartTableExists($conn);

        $productId = (int)$productId;
        $stmt = mysqli_prepare($conn, "DELETE FROM cart_items WHERE customer_id = ? AND product_id = ?");
        if ($stmt !== false) {
            mysqli_stmt_bind_param($stmt, 'ii', $customerId, $productId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            error_log('CartHelper: removeCartItem prepare failed: ' . mysqli_error($conn));
        }

        self::loadCustomerCartToSession($conn, $customerId);
    }

    public static function clearCustomerCart($conn, $customerId) {
        self::ensureCartTableExists($conn);

        $stmt = mysqli_prepare($conn, "DELETE FROM cart_items WHERE customer_id = ?");
        if ($stmt !== false) {
            mysqli_stmt_bind_param($stmt, 'i', $customerId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            error_log('CartHelper: clearCustomerCart prepare failed: ' . mysqli_error($conn));
        }

        $_SESSION['cart'] = [];
        $_SESSION['cart_qty'] = [];
    }

    private static function getCartItemQuantity($conn, $customerId, $productId) {
        $stmt = mysqli_prepare($conn, "SELECT quantity FROM cart_items WHERE customer_id = ? AND product_id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'ii', $customerId, $productId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $quantity);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        return (int)$quantity;
    }

    private static function upsertCartItem($conn, $customerId, $productId, $quantity) {
        $stmt = mysqli_prepare($conn, "INSERT INTO cart_items (customer_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = VALUES(quantity), updated_at = CURRENT_TIMESTAMP");
        if ($stmt === false) {
            error_log('CartHelper: upsertCartItem prepare failed: ' . mysqli_error($conn));
            return false;
        }
        mysqli_stmt_bind_param($stmt, 'iii', $customerId, $productId, $quantity);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }
}
