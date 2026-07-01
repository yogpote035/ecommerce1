<?php
require_once 'init.php';
require_once 'helpers/CategoryHelper.php';

$siteTitle = 'Shop Subcategory';
$categoryHelper = new CategoryHelper($conn);
$slug = trim($_GET['slug'] ?? '');
$priceMin = isset($_GET['price_min']) ? max(0, (float) $_GET['price_min']) : 0;
$priceMax = isset($_GET['price_max']) ? max(0, (float) $_GET['price_max']) : 0;
$sort = trim($_GET['sort'] ?? '');
$subcategory = $categoryHelper->getSubcategoryBySlug($slug);
$products = [];
$notFound = false;

if ($subcategory) {
    $category = !empty($subcategory['category_slug'])
        ? $categoryHelper->getCategoryBySlug($subcategory['category_slug'])
        : $categoryHelper->getCategoryById($subcategory['category_id']);
    $categoryId = (int) ($category['id'] ?? $subcategory['category_id']);
    $products = $categoryHelper->getProductsByCategory($categoryId, $subcategory['id']);
} else {
    $notFound = true;
}

$products = array_values(array_filter($products, function ($product) use ($priceMin, $priceMax) {
    $price = (float) ($product['price'] ?? $product['apprice'] ?? 0);
    if ($priceMin > 0 && $price < $priceMin) {
        return false;
    }
    if ($priceMax > 0 && $price > $priceMax) {
        return false;
    }
    return true;
}));

if ($sort === 'price_low_high') {
    usort($products, function ($a, $b) {
        return (float) ($a['price'] ?? $a['apprice'] ?? 0) <=> (float) ($b['price'] ?? $b['apprice'] ?? 0);
    });
} elseif ($sort === 'price_high_low') {
    usort($products, function ($a, $b) {
        return (float) ($b['price'] ?? $b['apprice'] ?? 0) <=> (float) ($a['price'] ?? $a['apprice'] ?? 0);
    });
}

$itemsPerPage = PaginationHelper::PER_PAGE;
$currentPage = PaginationHelper::currentPage();
$totalItems = count($products);
$totalPages = PaginationHelper::totalPages($totalItems, $itemsPerPage);
$currentPage = min($currentPage, $totalPages);
$offset = PaginationHelper::offset($currentPage, $itemsPerPage);
$products = array_slice($products, $offset, $itemsPerPage);

include 'templates/header.php';
?>
<div class="container pt-1 pb-5">
  <div class="row">
    <div class="col-lg-3 mb-4">
      <?php $categories = $categoryHelper->getCategoriesHierarchy(); ?>
      <?php
        $filterAction = 'subcategory.php';
        $filterHidden = ['slug' => $slug];
        $filterClearUrl = 'subcategory.php?slug=' . urlencode($slug);
        include 'templates/components/filter-sidebar.php';
        $activeCategorySlug = $category['slug'] ?? ($subcategory['category_slug'] ?? '');
        $activeSubcategorySlug = $subcategory['slug'] ?? $slug;
      ?>
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
          <?php PaginationHelper::render($currentPage, $totalPages, $totalItems, $itemsPerPage, 'products'); ?>
        <?php else: ?>
          <div class="alert alert-info">No products are currently assigned to this subcategory.</div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include 'templates/footer.php';
