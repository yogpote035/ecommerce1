<?php
require_once 'init.php';
require_once 'helpers/ProductImageHelper.php';
require_once 'helpers/CategoryHelper.php';
$siteTitle = 'Retail Product Add';

$categoryHelper = new CategoryHelper($conn);
$categories = $categoryHelper->getCategoriesHierarchy();

if (empty($_SESSION['retailer_logged_in']) && empty($_SESSION['rname'])) {
    $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Please login as retailer to add products.'];
    header('Location: auth.php?role=retailer&mode=login');
    exit;
}

$csrf_token = SecurityHelper::generateCSRFToken();
$errors = [];
$productName = '';
$productBrand = '';
$productCategory = '';
$productSubcategory = '';
$productQty = 1;
$productPrice = '';
$productDescription = '';
$imageHelper = new ProductImageHelper($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $productName = trim($_POST['apname'] ?? '');
        $productBrand = trim($_POST['apbrand'] ?? '');
        $productCategory = trim($_POST['apcategory'] ?? '');
        $productSubcategory = trim($_POST['apsubcategory'] ?? '');
        $productQty = isset($_POST['apqty']) ? (int) $_POST['apqty'] : 0;
        $productPrice = isset($_POST['apprice']) ? trim($_POST['apprice']) : '';
        $productDescription = trim($_POST['apdescription'] ?? '');
        $storedCategory = $productCategory;
        if ($productSubcategory !== '') {
            $storedCategory .= ' > ' . $productSubcategory;
        }

        if ($productName === '') {
            $errors[] = 'Product name is required.';
        }
        if ($productDescription === '') {
            $errors[] = 'Product description is required.';
        }
        if ($productBrand === '') {
            $errors[] = 'Brand is required.';
        }
        if ($productCategory === '') {
            $errors[] = 'Category is required.';
        }
        if ($productQty <= 0) {
            $errors[] = 'Quantity must be at least 1.';
        }
        if (!is_numeric($productPrice) || (float) $productPrice <= 0) {
            $errors[] = 'Price must be a valid positive number.';
        }
        if (empty($errors)) {
            $stmt = mysqli_prepare($conn, "INSERT INTO apadd (apname, apbrand, apcategory, apqty, apprice, apdescription, apimage) VALUES (?, ?, ?, ?, ?, ?, ?)");
        } else {
            $stmt = false;
        }

        if ($stmt) {
            $priceValue = (float) $productPrice;
            $mainImagePath = '';
            mysqli_stmt_bind_param($stmt, 'sssidss', $productName, $productBrand, $storedCategory, $productQty, $priceValue, $productDescription, $mainImagePath);
            if (mysqli_stmt_execute($stmt)) {
                $productId = mysqli_insert_id($conn);
                $uploadedPaths = [];
                if (!empty($_FILES['apimage']['name'])) {
                    foreach ($_FILES['apimage']['name'] as $index => $fileName) {
                        if (empty($_FILES['apimage']['tmp_name'][$index]) || $_FILES['apimage']['error'][$index] !== UPLOAD_ERR_OK) {
                            continue;
                        }
                        try {
                            $fileArray = [
                                'name' => $_FILES['apimage']['name'][$index],
                                'type' => $_FILES['apimage']['type'][$index],
                                'tmp_name' => $_FILES['apimage']['tmp_name'][$index],
                                'error' => $_FILES['apimage']['error'][$index],
                                'size' => $_FILES['apimage']['size'][$index],
                            ];
                            $uploadedPaths[] = $imageHelper->uploadImageFile($productId, $fileArray, $index === 0, $productName);
                        } catch (Exception $ex) {
                            $errors[] = $ex->getMessage();
                            break;
                        }
                    }
                }

                if (!empty($uploadedPaths)) {
                    $mainImagePath = $uploadedPaths[0];
                    $updateStmt = mysqli_prepare($conn, 'UPDATE apadd SET apimage = ? WHERE Apid = ?');
                    if ($updateStmt) {
                        mysqli_stmt_bind_param($updateStmt, 'si', $mainImagePath, $productId);
                        mysqli_stmt_execute($updateStmt);
                        mysqli_stmt_close($updateStmt);
                    }
                }

                if (empty($errors)) {
                    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Product added successfully.'];
                    mysqli_stmt_close($stmt);
                    header('Location: Radd.php');
                    exit;
                }
            }
            mysqli_stmt_close($stmt);
        }

        if (empty($errors)) {
            $errors[] = 'Unable to save product. Please try again.';
        }
    }
}

include 'templates/header.php';
?>
<div class="row justify-content-center">
  <div class="col-xl-9">
    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
          <div>
            <h1 class="h4 mb-1">Add Retail Product</h1>
            <p class="text-secondary mb-0">Use this form to add a new product to the catalog.</p>
          </div>
          <div class="btn-group">
            <a href="Rview.php" class="btn btn-outline-secondary">View Catalog</a>
            <a href="Rmain.php" class="btn btn-outline-primary">Dashboard</a>
          </div>
        </div>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="post" action="Radd.php" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="apname">Product Name</label>
              <input type="text" class="form-control" id="apname" name="apname" value="<?php echo htmlspecialchars($productName); ?>" required>
            </div>
            <div class="form-group col-md-6">
              <label for="apbrand">Brand</label>
              <input type="text" class="form-control" id="apbrand" name="apbrand" value="<?php echo htmlspecialchars($productBrand); ?>" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="apcategory">Category</label>
              <select id="apcategory" name="apcategory" class="form-control" required>
                <option value="">Choose category</option>
                <?php foreach ($categories as $category): ?>
                  <option value="<?php echo htmlspecialchars($category['name']); ?>" <?php echo $productCategory === $category['name'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option>
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
            <div class="form-group col-md-4">
              <label for="apqty">Quantity</label>
              <input type="number" class="form-control" id="apqty" name="apqty" min="1" value="<?php echo htmlspecialchars($productQty); ?>" required>
            </div>
            <div class="form-group col-md-4">
              <label for="apprice">Price</label>
              <input type="number" step="0.01" class="form-control" id="apprice" name="apprice" value="<?php echo htmlspecialchars($productPrice); ?>" required>
            </div>
          </div>

          <div class="form-group">
            <label for="apdescription">Product Description</label>
            <textarea id="apdescription" name="apdescription" class="form-control" rows="4" required><?php echo htmlspecialchars($productDescription); ?></textarea>
          </div>

          <div class="form-group">
            <label for="apimage">Product Images</label>
            <input type="file" class="form-control-file" id="apimage" name="apimage[]" multiple accept="image/*">
            <small class="form-text text-muted">Optional. Upload one or more product photos to display with the catalog.</small>
          </div>

          <button type="submit" class="btn btn-primary">Save Product</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var categorySubcategories = <?php echo json_encode(array_map(function ($cat) {
      return [
        'name' => $cat['name'],
        'subcategories' => array_values(array_map(function ($sub) {
          return $sub['name'];
        }, $cat['subcategories']))
      ];
    }, $categories)); ?>;

    var selectedSubcategory = <?php echo json_encode($productSubcategory); ?>;
    var categorySelect = document.getElementById('apcategory');
    var subcategorySelect = document.getElementById('apsubcategory');

    function renderSubcategories(categoryName) {
      subcategorySelect.innerHTML = '<option value="">Choose subcategory</option>';
      var category = categorySubcategories.find(function (item) {
        return item.name === categoryName;
      });
      if (!category || !category.subcategories.length) {
        return;
      }
      category.subcategories.forEach(function (subcategory) {
        var option = document.createElement('option');
        option.value = subcategory;
        option.textContent = subcategory;
        option.selected = subcategory === selectedSubcategory;
        subcategorySelect.appendChild(option);
      });
    }

    categorySelect.addEventListener('change', function () {
      selectedSubcategory = '';
      renderSubcategories(categorySelect.value);
    });

    renderSubcategories(categorySelect.value);
  });
</script>

<?php include 'templates/footer.php';
mysqli_close($conn);




