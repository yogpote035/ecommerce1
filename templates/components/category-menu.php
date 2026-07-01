<?php
// Category menu component.
$activeCategorySlug = $activeCategorySlug ?? '';
$activeSubcategorySlug = $activeSubcategorySlug ?? '';
?>
<div class="category-menu sidebar-card shadow-sm p-3 mb-4">
  <h2 class="h6 mb-3 text-secondary">Shop by Category</h2>
  <div class="list-group list-group-flush">
    <?php foreach ($categories as $category): ?>
      <div class="mb-2">
        <a href="category.php?slug=<?php echo urlencode($category['slug']); ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center<?php echo $activeCategorySlug === $category['slug'] && $activeSubcategorySlug === '' ? ' active' : ''; ?>">
          <span><?php echo htmlspecialchars($category['name']); ?></span>
          <span class="small">&rsaquo;</span>
        </a>
        <?php if (!empty($category['subcategories'])): ?>
          <div class="list-group list-group-flush ml-3 mt-2">
            <?php foreach ($category['subcategories'] as $subcategory): ?>
              <a href="subcategory.php?slug=<?php echo urlencode($subcategory['slug']); ?>" class="list-group-item list-group-item-action small<?php echo $activeSubcategorySlug === $subcategory['slug'] ? ' active' : ''; ?>">
                <?php echo htmlspecialchars($subcategory['name']); ?>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>
