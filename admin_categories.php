<?php
require_once 'init.php';

$siteTitle = 'Admin Categories';
$csrfToken = SecurityHelper::generateCSRFToken();

if (empty($_SESSION['admin_logged_in']) || empty($_SESSION['admin_id'])) {
    header('Location: auth.php?role=admin&mode=login');
    exit;
}

function ensureCategoryTables($conn) {
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        slug VARCHAR(100) NOT NULL UNIQUE,
        icon VARCHAR(255),
        description TEXT,
        image VARCHAR(255),
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS sub_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) NOT NULL,
        description TEXT,
        image VARCHAR(255),
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_category_slug (category_id, slug)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS child_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sub_category_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) NOT NULL,
        description TEXT,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_sub_slug (sub_category_id, slug)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function categorySlug($value) {
    $slug = strtolower(trim($value));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    return trim($slug, '-') ?: 'category-' . time();
}

ensureCategoryTables($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid form token.'];
        header('Location: admin_categories.php');
        exit;
    }

    $action = $_POST['action'] ?? '';
    if ($action === 'add_category') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $icon = trim($_POST['icon'] ?? '');
        $slug = categorySlug($_POST['slug'] ?? $name);
        $stmt = mysqli_prepare($conn, 'INSERT INTO categories (name, slug, icon, description) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE icon = VALUES(icon), description = VALUES(description), is_active = 1');
        mysqli_stmt_bind_param($stmt, 'ssss', $name, $slug, $icon, $description);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Category saved.'];
    } elseif ($action === 'add_subcategory') {
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $slug = categorySlug($_POST['slug'] ?? $name);
        $stmt = mysqli_prepare($conn, 'INSERT INTO sub_categories (category_id, name, slug) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name), is_active = 1');
        mysqli_stmt_bind_param($stmt, 'iss', $categoryId, $name, $slug);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Subcategory saved.'];
    } elseif ($action === 'add_child') {
        $subId = (int) ($_POST['sub_category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $slug = categorySlug($_POST['slug'] ?? $name);
        $stmt = mysqli_prepare($conn, 'INSERT INTO child_categories (sub_category_id, name, slug) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name), is_active = 1');
        mysqli_stmt_bind_param($stmt, 'iss', $subId, $name, $slug);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Child category saved.'];
    } elseif ($action === 'toggle') {
        $table = in_array($_POST['table'] ?? '', ['categories', 'sub_categories', 'child_categories'], true) ? $_POST['table'] : '';
        $id = (int) ($_POST['id'] ?? 0);
        if ($table && $id > 0) {
            mysqli_query($conn, "UPDATE $table SET is_active = IF(is_active = 1, 0, 1) WHERE id = $id");
        }
    }

    header('Location: admin_categories.php');
    exit;
}

$itemsPerPage = PaginationHelper::PER_PAGE;
$categoryPage = PaginationHelper::currentPage('category_page');
$subcategoryPage = PaginationHelper::currentPage('subcategory_page');
$childPage = PaginationHelper::currentPage('child_page');
$categoryOffset = PaginationHelper::offset($categoryPage, $itemsPerPage);
$subcategoryOffset = PaginationHelper::offset($subcategoryPage, $itemsPerPage);
$childOffset = PaginationHelper::offset($childPage, $itemsPerPage);
$categoryTotal = (int)(mysqli_fetch_assoc(mysqli_query($conn, 'SELECT COUNT(*) AS total FROM categories'))['total'] ?? 0);
$subcategoryTotal = (int)(mysqli_fetch_assoc(mysqli_query($conn, 'SELECT COUNT(*) AS total FROM sub_categories'))['total'] ?? 0);
$childTotal = (int)(mysqli_fetch_assoc(mysqli_query($conn, 'SELECT COUNT(*) AS total FROM child_categories'))['total'] ?? 0);
$categoryPages = PaginationHelper::totalPages($categoryTotal, $itemsPerPage);
$subcategoryPages = PaginationHelper::totalPages($subcategoryTotal, $itemsPerPage);
$childPages = PaginationHelper::totalPages($childTotal, $itemsPerPage);
$categoryPage = min($categoryPage, $categoryPages);
$subcategoryPage = min($subcategoryPage, $subcategoryPages);
$childPage = min($childPage, $childPages);
$categoryOffset = PaginationHelper::offset($categoryPage, $itemsPerPage);
$subcategoryOffset = PaginationHelper::offset($subcategoryPage, $itemsPerPage);
$childOffset = PaginationHelper::offset($childPage, $itemsPerPage);

$categoryOptions = mysqli_query($conn, 'SELECT * FROM categories ORDER BY name ASC');
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC LIMIT $itemsPerPage OFFSET $categoryOffset");
$subcategories = mysqli_query($conn, "SELECT s.*, c.name AS category_name FROM sub_categories s LEFT JOIN categories c ON c.id = s.category_id ORDER BY c.name ASC, s.name ASC LIMIT $itemsPerPage OFFSET $subcategoryOffset");
$children = mysqli_query($conn, "SELECT ch.*, s.name AS sub_name, c.name AS category_name FROM child_categories ch LEFT JOIN sub_categories s ON s.id = ch.sub_category_id LEFT JOIN categories c ON c.id = s.category_id ORDER BY c.name ASC, s.name ASC, ch.name ASC LIMIT $itemsPerPage OFFSET $childOffset");
$subOptions = mysqli_query($conn, 'SELECT id, name FROM sub_categories ORDER BY name ASC');

include 'templates/header.php';
?>
<div class="d-flex justify-content-between align-items-start mb-4">
  <div>
    <h1 class="h4 mb-1">Category Management</h1>
    <p class="text-secondary mb-0">Manage category hierarchy for storefront navigation and filtering.</p>
  </div>
  <a href="admin_products.php" class="btn btn-outline-secondary">Products</a>
</div>

<div class="row">
  <div class="col-lg-4 mb-4">
    <div class="card sidebar-card shadow-sm h-100"><div class="card-body">
      <h2 class="h6">Add Category</h2>
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        <input type="hidden" name="action" value="add_category">
        <input class="form-control mb-2" name="name" placeholder="Category name" required>
        <input class="form-control mb-2" name="slug" placeholder="Slug">
        <input class="form-control mb-2" name="icon" placeholder="Icon or label">
        <textarea class="form-control mb-3" name="description" rows="2" placeholder="Description"></textarea>
        <button class="btn btn-primary w-100">Save category</button>
      </form>
    </div></div>
  </div>
  <div class="col-lg-4 mb-4">
    <div class="card sidebar-card shadow-sm h-100"><div class="card-body">
      <h2 class="h6">Add Subcategory</h2>
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        <input type="hidden" name="action" value="add_subcategory">
        <select class="form-control mb-2" name="category_id" required>
          <option value="">Choose category</option>
          <?php while ($cat = mysqli_fetch_assoc($categoryOptions)): ?>
            <option value="<?php echo (int) $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
          <?php endwhile; ?>
        </select>
        <input class="form-control mb-2" name="name" placeholder="Subcategory name" required>
        <input class="form-control mb-3" name="slug" placeholder="Slug">
        <button class="btn btn-primary w-100">Save subcategory</button>
      </form>
    </div></div>
  </div>
  <div class="col-lg-4 mb-4">
    <div class="card sidebar-card shadow-sm h-100"><div class="card-body">
      <h2 class="h6">Add Child Category</h2>
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        <input type="hidden" name="action" value="add_child">
        <select class="form-control mb-2" name="sub_category_id" required>
          <option value="">Choose subcategory</option>
          <?php while ($sub = mysqli_fetch_assoc($subOptions)): ?>
            <option value="<?php echo (int) $sub['id']; ?>"><?php echo htmlspecialchars($sub['name']); ?></option>
          <?php endwhile; ?>
        </select>
        <input class="form-control mb-2" name="name" placeholder="Child category name" required>
        <input class="form-control mb-3" name="slug" placeholder="Slug">
        <button class="btn btn-primary w-100">Save child category</button>
      </form>
    </div></div>
  </div>
</div>

<div class="card sidebar-card shadow-sm mb-4"><div class="card-body">
  <h2 class="h5">Categories</h2>
  <div class="table-responsive"><table class="table table-sm">
    <thead><tr><th>Name</th><th>Slug</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
        <tr><td><?php echo htmlspecialchars($cat['name']); ?></td><td><?php echo htmlspecialchars($cat['slug']); ?></td><td><?php echo $cat['is_active'] ? 'Active' : 'Hidden'; ?></td><td>
          <form method="post"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="table" value="categories"><input type="hidden" name="id" value="<?php echo (int) $cat['id']; ?>"><button class="btn btn-sm btn-outline-secondary">Toggle</button></form>
        </td></tr>
      <?php endwhile; ?>
    </tbody>
  </table></div>
  <?php PaginationHelper::render($categoryPage, $categoryPages, $categoryTotal, $itemsPerPage, 'categories', ['pageKey' => 'category_page']); ?>
</div></div>

<div class="row">
  <div class="col-lg-6 mb-4"><div class="card sidebar-card shadow-sm"><div class="card-body">
    <h2 class="h5">Subcategories</h2>
    <div class="table-responsive"><table class="table table-sm"><thead><tr><th>Category</th><th>Name</th><th>Status</th><th></th></tr></thead><tbody>
      <?php while ($sub = mysqli_fetch_assoc($subcategories)): ?>
        <tr><td><?php echo htmlspecialchars($sub['category_name'] ?? ''); ?></td><td><?php echo htmlspecialchars($sub['name']); ?></td><td><?php echo $sub['is_active'] ? 'Active' : 'Hidden'; ?></td><td><form method="post"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="table" value="sub_categories"><input type="hidden" name="id" value="<?php echo (int) $sub['id']; ?>"><button class="btn btn-sm btn-outline-secondary">Toggle</button></form></td></tr>
      <?php endwhile; ?>
    </tbody></table></div>
    <?php PaginationHelper::render($subcategoryPage, $subcategoryPages, $subcategoryTotal, $itemsPerPage, 'subcategories', ['pageKey' => 'subcategory_page']); ?>
  </div></div></div>
  <div class="col-lg-6 mb-4"><div class="card sidebar-card shadow-sm"><div class="card-body">
    <h2 class="h5">Child Categories</h2>
    <div class="table-responsive"><table class="table table-sm"><thead><tr><th>Path</th><th>Name</th><th>Status</th><th></th></tr></thead><tbody>
      <?php while ($child = mysqli_fetch_assoc($children)): ?>
        <tr><td><?php echo htmlspecialchars(($child['category_name'] ?? '') . ' / ' . ($child['sub_name'] ?? '')); ?></td><td><?php echo htmlspecialchars($child['name']); ?></td><td><?php echo $child['is_active'] ? 'Active' : 'Hidden'; ?></td><td><form method="post"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="table" value="child_categories"><input type="hidden" name="id" value="<?php echo (int) $child['id']; ?>"><button class="btn btn-sm btn-outline-secondary">Toggle</button></form></td></tr>
      <?php endwhile; ?>
    </tbody></table></div>
    <?php PaginationHelper::render($childPage, $childPages, $childTotal, $itemsPerPage, 'child categories', ['pageKey' => 'child_page']); ?>
  </div></div></div>
</div>

<?php include 'templates/footer.php'; ?>
