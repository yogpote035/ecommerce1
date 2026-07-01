<?php
require_once 'init.php';
require_once 'helpers/CategoryHelper.php';
require_once 'helpers/ProductImageHelper.php';

$siteTitle = 'Edit Product';

// Check admin authentication
if (empty($_SESSION['admin_logged_in']) || empty($_SESSION['admin_id'])) {
    header('Location: auth.php?role=admin&mode=login');
    exit;
}

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = null;

$descriptionColumnExists = false;
$columnStmt = mysqli_prepare($conn, 'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
if ($columnStmt) {
    $tableName = 'apadd';
    $columnName = 'apdescription';
    mysqli_stmt_bind_param($columnStmt, 'ss', $tableName, $columnName);
    mysqli_stmt_execute($columnStmt);
    mysqli_stmt_bind_result($columnStmt, $columnCount);
    mysqli_stmt_fetch($columnStmt);
    mysqli_stmt_close($columnStmt);
    $descriptionColumnExists = (int)$columnCount > 0;
}

if (!$descriptionColumnExists) {
    mysqli_query($conn, 'ALTER TABLE apadd ADD COLUMN apdescription TEXT NULL');
}

if ($productId > 0) {
    $stmt = mysqli_prepare($conn, "SELECT Apid, apname, apbrand, apcategory, apqty, apprice, apdescription, Apimage FROM apadd WHERE Apid = ? LIMIT 1");
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    $apname = mysqli_real_escape_string($conn, trim($_POST['apname'] ?? ''));
    $apbrand = mysqli_real_escape_string($conn, trim($_POST['apbrand'] ?? ''));
    $apcategory = mysqli_real_escape_string($conn, trim($_POST['apcategory'] ?? ''));
    $apsubcategory = mysqli_real_escape_string($conn, trim($_POST['apsubcategory'] ?? ''));
    if ($apsubcategory !== '') {
        $apcategory = $apcategory . ' > ' . $apsubcategory;
    }
    $apqty = isset($_POST['apqty']) ? (int)$_POST['apqty'] : 0;
    $apprice = isset($_POST['apprice']) ? (float)$_POST['apprice'] : 0.0;
    $apdescription = mysqli_real_escape_string($conn, trim($_POST['apdescription'] ?? ''));
    $errors = [];

    if ($apname === '') {
        $errors[] = 'Product name is required.';
    }
    if ($apbrand === '') {
        $errors[] = 'Brand is required.';
    }
    if ($apcategory === '') {
        $errors[] = 'Category is required.';
    }
    if ($apqty < 0) {
        $errors[] = 'Quantity cannot be negative.';
    }
    if ($apprice <= 0) {
        $errors[] = 'Price must be greater than zero.';
    }
    if ($apdescription === '') {
        $errors[] = 'Description is required.';
    }

    if (!empty($errors)) {
        $_SESSION['toast'] = ['type' => 'danger', 'message' => implode(' ', $errors)];
        header('Location: admin_product_edit.php?id=' . $productId);
        exit;
    }

    $updateSql = "UPDATE apadd SET apname = ?, apbrand = ?, apcategory = ?, apqty = ?, apprice = ?, apdescription = ? WHERE Apid = ?";
    $updateStmt = mysqli_prepare($conn, $updateSql);
    mysqli_stmt_bind_param($updateStmt, 'sssidsi', $apname, $apbrand, $apcategory, $apqty, $apprice, $apdescription, $productId);
    
    if (mysqli_stmt_execute($updateStmt)) {
        $uploadedPaths = [];
        if (!empty($_FILES['apimage']['name']) && is_array($_FILES['apimage']['name'])) {
            $imageHelper = new ProductImageHelper($conn);
            foreach ($_FILES['apimage']['name'] as $index => $fileName) {
                if (empty($_FILES['apimage']['tmp_name'][$index]) || $_FILES['apimage']['error'][$index] !== UPLOAD_ERR_OK) {
                    continue;
                }
                $fileArray = [
                    'name' => $_FILES['apimage']['name'][$index],
                    'type' => $_FILES['apimage']['type'][$index],
                    'tmp_name' => $_FILES['apimage']['tmp_name'][$index],
                    'error' => $_FILES['apimage']['error'][$index],
                    'size' => $_FILES['apimage']['size'][$index],
                ];

                try {
                    $uploadedPaths[] = $imageHelper->uploadImageFile($productId, $fileArray, empty($product['Apimage']) && $index === 0, $apname);
                } catch (Exception $ex) {
                    $_SESSION['toast'] = ['type' => 'warning', 'message' => 'Product updated, but one or more images could not be uploaded: ' . $ex->getMessage()];
                    header('Location: admin_product_edit.php?id=' . $productId);
                    exit;
                }
            }
        }

        if (!empty($uploadedPaths) && empty($product['Apimage'])) {
            $mainImagePath = $uploadedPaths[0];
            $imageStmt = mysqli_prepare($conn, 'UPDATE apadd SET Apimage = ? WHERE Apid = ?');
            if ($imageStmt) {
                mysqli_stmt_bind_param($imageStmt, 'si', $mainImagePath, $productId);
                mysqli_stmt_execute($imageStmt);
                mysqli_stmt_close($imageStmt);
            }
        }

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Product updated successfully.'];
        header('Location: admin_products.php');
        exit;
    } else {
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Error updating product.'];
    }
    mysqli_stmt_close($updateStmt);
}

$categoryHelper = new CategoryHelper($conn);
$categories = $categoryHelper->getCategoriesHierarchy();

// Parse category and subcategory from product
$productCategory = '';
$productSubcategory = '';
if (strpos($product['apcategory'], ' > ') !== false) {
    list($productCategory, $productSubcategory) = explode(' > ', $product['apcategory'], 2);
} else {
    $productCategory = $product['apcategory'];
}

$csrf_token = SecurityHelper::generateCSRFToken();
include 'templates/header.php';
?>

<style>
  .edit-product-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px 0;
    margin-bottom: 30px;
    border-radius: 8px;
  }

  .edit-product-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  }

  .form-group label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 8px;
  }

  .form-control {
    border-radius: 6px;
    border: 1px solid #dee2e6;
    padding: 10px 14px;
  }

  .form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
  }

  .btn-update {
    background-color: #0d6efd;
    color: white;
    padding: 12px 28px;
    border-radius: 6px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .btn-update:hover {
    background-color: #0b5ed7;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
  }

  .btn-cancel {
    background-color: #6c757d;
    color: white;
    padding: 12px 28px;
    border-radius: 6px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
  }

  .btn-cancel:hover {
    background-color: #5a6268;
    color: white;
    transform: translateY(-2px);
    text-decoration: none;
  }
</style>

<div class="container py-4">
  <!-- Header -->
  <div class="edit-product-hero">
    <h1 class="h3 mb-2">Edit Product</h1>
    <p class="mb-0">Update product details and information</p>
  </div>

  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card edit-product-card">
        <div class="card-body p-5">
          <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="apname">Product Name *</label>
                <input type="text" id="apname" name="apname" class="form-control" value="<?php echo htmlspecialchars($product['apname']); ?>" required>
              </div>
              <div class="form-group col-md-6">
                <label for="apbrand">Brand *</label>
                <input type="text" id="apbrand" name="apbrand" class="form-control" value="<?php echo htmlspecialchars($product['apbrand']); ?>" required>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="apcategory">Category *</label>
                <select id="apcategory" name="apcategory" class="form-control" required>
                  <option value="">Choose category</option>
                  <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat['name']); ?>" <?php echo $cat['name'] === $productCategory ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group col-md-6">
                <label for="apsubcategory">Subcategory</label>
                <select id="apsubcategory" name="apsubcategory" class="form-control">
                  <option value="">Choose subcategory</option>
                </select>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="apqty">Quantity *</label>
                <input type="number" id="apqty" name="apqty" class="form-control" value="<?php echo $product['apqty']; ?>" min="0" required>
              </div>
              <div class="form-group col-md-6">
                <label for="apprice">Price *</label>
                <input type="number" id="apprice" name="apprice" class="form-control" value="<?php echo $product['apprice']; ?>" step="0.01" min="0" required>
              </div>
            </div>

            <div class="form-group">
              <label for="apdescription">Description *</label>
              <textarea id="apdescription" name="apdescription" class="form-control" rows="4" required><?php echo htmlspecialchars($product['apdescription']); ?></textarea>
            </div>

            <div class="form-group">
              <label for="apimage">Upload More Product Images</label>
              <input type="file" id="apimage" name="apimage[]" class="form-control-file" accept="image/*" multiple>
              <small class="form-text text-muted">Optional. New images will be added to the product gallery.</small>
            </div>

            <div id="editFilePreview" class="mb-4"></div>

            <div class="form-group mt-4 form-actions">
              <button type="submit" class="btn btn-update">Update Product</button>
              <a href="admin_products.php" class="btn btn-cancel ml-2">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var categorySubcategories = <?php echo json_encode(array_map(function($cat) {
    return [
      'name' => $cat['name'],
      'subcategories' => array_values(array_map(function($sub) {
        return $sub['name'];
      }, $cat['subcategories']))
    ];
  }, $categories)); ?>;

  var categorySelect = document.getElementById('apcategory');
  var subcategorySelect = document.getElementById('apsubcategory');
  var selectedSubcategory = '<?php echo htmlspecialchars($productSubcategory); ?>';
  var imageInput = document.getElementById('apimage');
  var filePreview = document.getElementById('editFilePreview');

  function renderSubcategories(categoryName) {
    subcategorySelect.innerHTML = '<option value="">Choose subcategory</option>';
    var category = categorySubcategories.find(function(item) {
      return item.name === categoryName;
    });
    if (!category || !category.subcategories.length) {
      return;
    }
    category.subcategories.forEach(function(subcategory) {
      var option = document.createElement('option');
      option.value = subcategory;
      option.textContent = subcategory;
      if (subcategory === selectedSubcategory) {
        option.selected = true;
      }
      subcategorySelect.appendChild(option);
    });
  }

  categorySelect.addEventListener('change', function() {
    renderSubcategories(categorySelect.value);
  });

  // Initialize on page load
  if (categorySelect.value) {
    renderSubcategories(categorySelect.value);
  }

  function renderFilePreview(files) {
    filePreview.innerHTML = '';
    if (!files.length) {
      return;
    }

    var title = document.createElement('div');
    title.className = 'mb-2 text-muted small font-weight-bold';
    title.textContent = 'Selected image preview';
    filePreview.appendChild(title);

    var list = document.createElement('div');
    list.className = 'image-preview-grid';

    Array.from(files).forEach(function(file) {
      var item = document.createElement('div');
      item.className = 'image-preview-item';

      var image = document.createElement('img');
      image.alt = file.name;
      image.src = URL.createObjectURL(file);
      image.onload = function() {
        URL.revokeObjectURL(image.src);
      };

      var meta = document.createElement('div');
      meta.className = 'image-preview-meta';
      meta.textContent = file.name + ' · ' + Math.round(file.size / 1024) + ' KB';

      item.appendChild(image);
      item.appendChild(meta);
      list.appendChild(item);
    });

    filePreview.appendChild(list);
  }

  imageInput.addEventListener('change', function() {
    renderFilePreview(imageInput.files);
  });
});
</script>

<?php include 'templates/footer.php';
