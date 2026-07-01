<?php
require_once 'init.php';
require_once 'helpers/CategoryHelper.php';

$siteTitle = 'Shop Subcategory';
$categoryHelper = new CategoryHelper($conn);
$slug = trim($_GET['slug'] ?? '');
$subcategory = $categoryHelper->getSubcategoryBySlug($slug);
$products = [];
$notFound = false;

if ($subcategory) {
    $category = $categoryHelper->getCategoryById($subcategory['category_id']);
    $products = $categoryHelper->getProductsByCategory($subcategory['category_id'], $subcategory['id']);
} else {
    $notFound = true;
}

include 'templates/header.php';
?>
<div class="container py-5">
  <div class="row">
    <div class="col-lg-3 mb-4">
      <?php $categories = $categoryHelper->getCategoriesHierarchy(); ?>
      <?php include 'templates/components/category-menu.php'; ?>
    </div>
    <div class="col-lg-9">
      <?php if ($notFound): ?>
        <div class="alert alert-warning">The requested subcategory was not found.</div>
      <?php else: ?>
        <div class="d-flex justify-content-between align-items-center mb-4 flex-column flex-md-row gap-3">
          <div>
            <h1 class="h3 mb-1"><?php echo htmlspecialchars($subcategory['name']); ?></h1>
            <p class="text-secondary mb-0">Products inside <?php echo htmlspecialchars($subcategory['name']); ?> under <?php echo htmlspecialchars($category['name'] ?? 'Category'); ?>.</p>
          </div>
          <div class="btn-group">
            <a href="category.php?slug=<?php echo urlencode($category['slug'] ?? ''); ?>" class="btn btn-outline-secondary">Back to <?php echo htmlspecialchars($category['name'] ?? 'Category'); ?></a>
            <a href="category.php" class="btn btn-outline-primary">All categories</a>
          </div>
        </div>

        <?php if (!empty($products)): ?>
          <div class="row g-4">
            <?php foreach ($products as $product): ?>
              <div class="col-sm-6 col-xl-4">
                <?php include 'templates/components/product-card.php'; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="alert alert-info">No products are currently assigned to this subcategory.</div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include 'templates/footer.php';
