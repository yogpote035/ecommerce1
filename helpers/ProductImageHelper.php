<?php
class ProductImageHelper {
    private $db;
    private $uploadDir = __DIR__ . '/../uploads/products/';
    private $publicDir = 'uploads/products/';

    public function __construct($conn) {
        $this->db = $conn;
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function getProductImages($productId, $limit = null, $offset = 0) {
        if (!$this->tableExists('product_images')) {
            return [];
        }

        $limit = $limit === null ? null : max(1, (int)$limit);
        $offset = max(0, (int)$offset);
        $stmt = $this->db->prepare(
            'SELECT id, image_path, alt_text, is_primary, sort_order FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC' .
            ($limit !== null ? ' LIMIT ? OFFSET ?' : '')
        );
        if (!$stmt) {
            return [];
        }

        if ($limit !== null) {
            mysqli_stmt_bind_param($stmt, 'iii', $productId, $limit, $offset);
        } else {
            mysqli_stmt_bind_param($stmt, 'i', $productId);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $images = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);

        foreach ($images as &$image) {
            $image['public_path'] = $this->publicDir . basename($image['image_path']);
        }

        return $images;
    }

    private function tableExists($tableName) {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) AS count FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
        );
        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param($stmt, 's', $tableName);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $count);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        return (int) $count > 0;
    }

    public function uploadImageFile($productId, array $file, $isPrimary = false, $altText = '') {
        if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Invalid file upload.');
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes, true)) {
            throw new Exception('Invalid file type. Only JPG, PNG, and WEBP are allowed.');
        }
        if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
            throw new Exception('Image size must be 5 MB or smaller.');
        }

        $imageName = basename($file['name']);
        $safeName = preg_replace('/[^A-Za-z0-9_.-]/', '_', $imageName);
        $targetPath = $this->uploadDir . time() . '_' . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception('Failed to move uploaded file.');
        }

        if (!$this->tableExists('product_images')) {
            return $this->publicDir . basename($targetPath);
        }

        $stmt = $this->db->prepare(
            'INSERT INTO product_images (product_id, image_path, alt_text, is_primary, sort_order) VALUES (?, ?, ?, ?, ?)' 
        );
        if (!$stmt) {
            throw new Exception('Unable to prepare image insert statement.');
        }

        $sortOrder = 0;
        mysqli_stmt_bind_param($stmt, 'issii', $productId, $targetPath, $altText, $isPrimary, $sortOrder);
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            throw new Exception('Unable to save product image metadata.');
        }

        mysqli_stmt_close($stmt);
        return $this->publicDir . basename($targetPath);
    }
}
