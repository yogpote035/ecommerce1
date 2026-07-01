<?php
require_once 'init.php';
require_once 'helpers/CategoryHelper.php';
$siteTitle = 'Admin Product Management';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    $email = SecurityHelper::sanitizeEmail($_POST['email'] ?? '');
    $apass = trim($_POST['apass'] ?? '');

    if (!SecurityHelper::isValidEmail($email)) {
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Please enter a valid email address.'];
        header('Location: auth.php');
        exit();
    }

    $stmt = mysqli_prepare($conn, "SELECT aid, aname, apass FROM aregister WHERE email = ?");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $aid, $aname, $storedHash);
    if (mysqli_stmt_fetch($stmt)) {
        $legacyPassword = !SecurityHelper::isPasswordHash($storedHash) && $storedHash === $apass;
        if (SecurityHelper::verifyPassword($apass, $storedHash) || $legacyPassword) {
            if ($legacyPassword) {
                $newHash = SecurityHelper::hashPassword($apass);
                $updateStmt = mysqli_prepare($conn, "UPDATE aregister SET apass = ? WHERE aid = ?");
                mysqli_stmt_bind_param($updateStmt, 'si', $newHash, $aid);
                mysqli_stmt_execute($updateStmt);
                mysqli_stmt_close($updateStmt);
            }

            $_SESSION['admin_id'] = $aid;
            $_SESSION['aname'] = $aname;
            $_SESSION['admin_email'] = $email;
            $_SESSION['admin_logged_in'] = true;
            if (!empty($_POST['remember_me'])) {
                issueRememberToken($conn, $aid, 'admin');
            }
            SecurityHelper::logActivity($conn, 'login', 'auth', $aid, $aid, 'admin');
            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Logged in successfully.'];
            mysqli_stmt_close($stmt);
            header('Location: admin_products.php');
            exit();
        } else {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid email or password.'];
            header('Location: auth.php?role=admin&mode=login');
            exit();
        }
    } else {
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid email or password.'];
        header('Location: auth.php?role=admin&mode=login');
        exit();
    }

    mysqli_stmt_close($stmt);
}

if (empty($_SESSION['admin_logged_in']) || empty($_SESSION['admin_id'])) {
    header('Location: auth.php?role=admin&mode=login');
    exit;
}

$categoryHelper = new CategoryHelper($conn);
$categories = $categoryHelper->getCategoriesHierarchy();
$csrfToken = SecurityHelper::generateCSRFToken();

include 'templates/header.php';
?>
<div class="container-fluid mb-4">
  <div class="alert alert-info d-flex justify-content-between align-items-center">
    <div>
      Hello <?php echo htmlspecialchars($_SESSION['aname'] ?? 'Admin'); ?>, welcome to the admin dashboard.
    </div>
    <div class="btn-group" role="group">
      <a href="validation.php" class="btn btn-sm btn-primary active">Products</a>
    </div>
  </div>
</div>

<div class="row justify-content-center">
  <div class="col-xl-8 col-lg-10">

        <div class="card shadow-sm border-0 rounded-lg overflow-hidden">
          <div class="card-header bg-white border-bottom py-4">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h1 class="h3 mb-1">Product Management</h1>
                <p class="text-muted mb-0">Add or update catalogue items with category mapping and image previews.</p>
              </div>
              <span class="badge badge-pill badge-primary py-2 px-3">Admin</span>
            </div>
          </div>
          <div class="card-body">
            <form method="post" action="Aadd.php" enctype="multipart/form-data">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="apname">Product Name</label>
                  <input type="text" id="apname" name="apname" class="form-control" placeholder="Enter product title" required>
                </div>
                <div class="form-group col-md-6">
                  <label for="apbrand">Brand</label>
                  <input type="text" id="apbrand" name="apbrand" class="form-control" placeholder="Enter brand name" required>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="apcategory">Category</label>
                  <select id="apcategory" name="apcategory" class="form-control" required>
                    <option value="">Choose category</option>
                    <?php foreach ($categories as $cat): ?>
                      <option value="<?php echo htmlspecialchars($cat['name']); ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
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
                  <input type="number" id="apqty" name="apqty" class="form-control" placeholder="0" min="0" required>
                </div>
                <div class="form-group col-md-4">
                  <label for="apprice">Price</label>
                  <input type="number" id="apprice" name="apprice" class="form-control" placeholder="0.00" step="0.01" min="0" required>
                </div>
                <div class="form-group col-md-4">
                  <label for="apimage">Product Images</label>
                  <input type="file" id="apimage" name="apimage[]" class="form-control-file" accept="image/*" multiple required>
                  <small class="form-text text-muted">Choose one or more product images for the product gallery.</small>
                </div>
              </div>

              <div class="form-group">
                <label for="apdescription">Product Description</label>
                <textarea id="apdescription" name="apdescription" class="form-control" rows="4" placeholder="Describe the product features, materials, and benefits" required></textarea>
              </div>

              <div id="filePreview" class="mb-4"></div>

              <div class="d-flex flex-wrap form-actions">
                <button type="submit" class="btn btn-primary btn-lg">Add Product</button>
                <a href="admin_products.php" class="btn btn-outline-secondary btn-lg">View Products</a>
              </div>
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

    var categorySelect = document.getElementById('apcategory');
    var subcategorySelect = document.getElementById('apsubcategory');
    var imageInput = document.getElementById('apimage');
    var filePreview = document.getElementById('filePreview');

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
        subcategorySelect.appendChild(option);
      });
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

      Array.from(files).forEach(function (file) {
        var item = document.createElement('div');
        item.className = 'image-preview-item';

        var image = document.createElement('img');
        image.alt = file.name;
        image.src = URL.createObjectURL(file);
        image.onload = function () {
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

    categorySelect.addEventListener('change', function () {
      renderSubcategories(categorySelect.value);
    });

    imageInput.addEventListener('change', function () {
      renderFilePreview(imageInput.files);
    });
  });
</script>

<?php include 'templates/footer.php';
