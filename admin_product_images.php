<?php
require_once 'init.php';
require_once 'helpers/ProductImageHelper.php';

$siteTitle = 'Product Images';
$csrfToken = SecurityHelper::generateCSRFToken();

if (empty($_SESSION['admin_logged_in']) || empty($_SESSION['admin_id'])) {
    header('Location: auth.php?role=admin&mode=login');
    exit;
}

$productId = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($_POST['product_id'] ?? 0);
$product = null;
if ($productId > 0) {
    $stmt = mysqli_prepare($conn, 'SELECT Apid, apname, apimage FROM apadd WHERE Apid = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'i', $productId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

if (!$product) {
    $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Product not found.'];
    header('Location: admin_products.php');
    exit;
}

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255),
    is_primary TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_product_primary (product_id, is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$helper = new ProductImageHelper($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid form token.'];
        header('Location: admin_product_images.php?id=' . $productId);
        exit;
    }

    $action = $_POST['action'] ?? '';
    if ($action === 'upload' && !empty($_FILES['images']['name'])) {
        $uploaded = 0;
        foreach ($_FILES['images']['name'] as $index => $name) {
            if (empty($_FILES['images']['tmp_name'][$index]) || $_FILES['images']['error'][$index] !== UPLOAD_ERR_OK) {
                continue;
            }
            $file = [
                'name' => $_FILES['images']['name'][$index],
                'type' => $_FILES['images']['type'][$index],
                'tmp_name' => $_FILES['images']['tmp_name'][$index],
                'error' => $_FILES['images']['error'][$index],
                'size' => $_FILES['images']['size'][$index],
            ];
            try {
                $helper->uploadImageFile($productId, $file, $index === 0 && empty($product['apimage']), $product['apname']);
                $uploaded++;
            } catch (Exception $ex) {
                $_SESSION['toast'] = ['type' => 'warning', 'message' => $ex->getMessage()];
                break;
            }
        }
        if ($uploaded > 0) {
            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Images uploaded.'];
        }
    } elseif ($action === 'primary') {
        $imageId = (int) ($_POST['image_id'] ?? 0);
        $pathStmt = mysqli_prepare($conn, 'SELECT image_path FROM product_images WHERE id = ? AND product_id = ? LIMIT 1');
        if ($pathStmt) {
            mysqli_stmt_bind_param($pathStmt, 'ii', $imageId, $productId);
            mysqli_stmt_execute($pathStmt);
            mysqli_stmt_bind_result($pathStmt, $imagePath);
            $imageFound = mysqli_stmt_fetch($pathStmt);
            mysqli_stmt_close($pathStmt);
        } else {
            $imageFound = false;
            $imagePath = '';
        }

        if ($imageFound) {
            $publicPath = 'uploads/products/' . basename($imagePath);

            mysqli_begin_transaction($conn);
            $primaryUpdated = false;
            $productUpdated = false;

            $clearStmt = mysqli_prepare($conn, 'UPDATE product_images SET is_primary = 0 WHERE product_id = ?');
            if ($clearStmt) {
                mysqli_stmt_bind_param($clearStmt, 'i', $productId);
                $primaryUpdated = mysqli_stmt_execute($clearStmt);
                mysqli_stmt_close($clearStmt);
            }

            if ($primaryUpdated) {
                $stmt = mysqli_prepare($conn, 'UPDATE product_images SET is_primary = 1 WHERE id = ? AND product_id = ?');
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, 'ii', $imageId, $productId);
                    $primaryUpdated = mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                } else {
                    $primaryUpdated = false;
                }
            }

            if ($primaryUpdated) {
                $updateStmt = mysqli_prepare($conn, 'UPDATE apadd SET apimage = ? WHERE Apid = ?');
                if ($updateStmt) {
                    mysqli_stmt_bind_param($updateStmt, 'si', $publicPath, $productId);
                    $productUpdated = mysqli_stmt_execute($updateStmt);
                    mysqli_stmt_close($updateStmt);
                }
            }

            if ($primaryUpdated && $productUpdated) {
                mysqli_commit($conn);
                $_SESSION['toast'] = ['type' => 'success', 'message' => 'Primary image updated.'];
            } else {
                mysqli_rollback($conn);
                $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Unable to update product primary image.'];
            }
        } else {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Selected image was not found.'];
        }
    } elseif ($action === 'delete') {
        $imageId = (int) ($_POST['image_id'] ?? 0);
        $stmt = mysqli_prepare($conn, 'DELETE FROM product_images WHERE id = ? AND product_id = ?');
        mysqli_stmt_bind_param($stmt, 'ii', $imageId, $productId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Image metadata removed.'];
    }

    header('Location: admin_product_images.php?id=' . $productId);
    exit;
}

$itemsPerPage = PaginationHelper::PER_PAGE;
$currentPage = PaginationHelper::currentPage();
$offset = PaginationHelper::offset($currentPage, $itemsPerPage);
$imageCountResult = mysqli_query($conn, 'SELECT COUNT(*) AS total FROM product_images WHERE product_id = ' . (int)$productId);
$totalItems = $imageCountResult ? (int)(mysqli_fetch_assoc($imageCountResult)['total'] ?? 0) : 0;
$totalPages = PaginationHelper::totalPages($totalItems, $itemsPerPage);
$currentPage = min($currentPage, $totalPages);
$offset = PaginationHelper::offset($currentPage, $itemsPerPage);
$images = $helper->getProductImages($productId, $itemsPerPage, $offset);
include 'templates/header.php';
?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
  <div>
    <h1 class="h4 mb-1">Product Images</h1>
    <p class="text-secondary mb-0"><?php echo htmlspecialchars($product['apname']); ?></p>
  </div>
  <a href="admin_products.php" class="btn btn-outline-secondary mt-3 mt-md-0">Back to products</a>
</div>

<div class="card sidebar-card shadow-sm mb-4">
  <div class="card-body">
    <h2 class="h6">Upload Images</h2>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
      <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
      <input type="hidden" name="action" value="upload">
      <input type="file" name="images[]" class="form-control-file mb-3" accept="image/*" multiple required>
      <button class="btn btn-primary">Upload</button>
    </form>
  </div>
</div>

<div class="row">
  <?php if (empty($images)): ?>
    <div class="col-12"><div class="alert alert-info">No gallery images yet. The product still uses its main image fallback.</div></div>
  <?php else: ?>
    <?php foreach ($images as $image): ?>
      <div class="col-sm-6 col-lg-4 mb-4">
        <div class="card sidebar-card shadow-sm h-100">
          <img src="<?php echo htmlspecialchars($image['public_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($image['alt_text'] ?? $product['apname']); ?>" style="height:220px;object-fit:cover;">
          <div class="card-body">
            <p class="small text-secondary mb-3"><?php echo !empty($image['is_primary']) ? 'Primary image' : 'Gallery image'; ?></p>
            <div class="d-flex flex-wrap" style="gap: .5rem;">
              <form method="post">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                <input type="hidden" name="image_id" value="<?php echo (int) $image['id']; ?>">
                <input type="hidden" name="action" value="primary">
                <button class="btn btn-sm btn-outline-primary">Make primary</button>
              </form>
              <form method="post" onsubmit="return confirm('Remove this image metadata?');">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                <input type="hidden" name="image_id" value="<?php echo (int) $image['id']; ?>">
                <input type="hidden" name="action" value="delete">
                <button class="btn btn-sm btn-outline-danger">Remove</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
<?php PaginationHelper::render($currentPage, $totalPages, $totalItems, $itemsPerPage, 'images'); ?>

<?php include 'templates/footer.php'; ?>
