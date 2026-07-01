<?php
require_once 'init.php';
require_once 'helpers/CategoryHelper.php';

$siteTitle = 'Shop Categories';
$categoryHelper = new CategoryHelper($conn);
$categories = $categoryHelper->getCategoriesHierarchy();
$selectedSlug = trim($_GET['slug'] ?? '');
$selectedCategory = null;
$products = [];
$notFound = false;

if ($selectedSlug !== '') {
    $selectedCategory = $categoryHelper->getCategoryBySlug($selectedSlug);
    if ($selectedCategory) {
        $products = $categoryHelper->getProductsByCategory($selectedCategory['id']);
    } else {
        $notFound = true;
    }
} else {
    $products = $categoryHelper->getAllProducts();
}

include 'templates/header.php';
?>
<div class="container py-5">
  <div class="row">
    <div class="col-lg-3 mb-4">
      <?php include 'templates/components/category-menu.php'; ?>
    </div>
    <div class="col-lg-9">
      <div class="d-flex justify-content-between align-items-center mb-4 flex-column flex-md-row gap-3">
        <div>
          <h1 class="h3 mb-1"><?php echo $selectedCategory ? htmlspecialchars($selectedCategory['name']) : 'Browse categories'; ?></h1>
          <p class="text-secondary mb-0"><?php echo $selectedCategory ? htmlspecialchars($selectedCategory['description'] ?? 'Explore curated collections and trending products across categories.') : 'Explore curated collections and trending products across categories.'; ?></p>
        </div>
        <?php if ($selectedCategory): ?>
          <a href="category.php" class="btn btn-outline-primary">View all categories</a>
        <?php else: ?>
          <a href="index.php?page=1" class="btn btn-outline-primary">View all products</a>
        <?php endif; ?>
      </div>

      <?php if ($notFound): ?>
        <div class="alert alert-warning">The requested category was not found. Please choose another category.</div>
      <?php else: ?>
        <?php if (!empty($selectedCategory) && !empty($selectedCategory['subcategories'])): ?>
          <div class="mb-4">
            <h2 class="h5 mb-3">Subcategories</h2>
            <div class="d-flex flex-wrap gap-2">
              <?php foreach ($selectedCategory['subcategories'] as $subcategory): ?>
                <a href="subcategory.php?slug=<?php echo urlencode($subcategory['slug']); ?>" class="btn btn-outline-secondary btn-sm"><?php echo htmlspecialchars($subcategory['name']); ?></a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <?php if (!empty($products)): ?>
          <div class="row g-4">
            <?php foreach ($products as $product): ?>
              <div class="col-sm-6 col-xl-4">
                <?php include 'templates/components/product-card.php'; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="alert alert-info">No products are available for this category yet.</div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include 'templates/footer.php';
