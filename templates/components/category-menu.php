<?php
// Category menu component.
$activeCategorySlug = $activeCategorySlug ?? '';
$activeSubcategorySlug = $activeSubcategorySlug ?? '';
?>
<div class="category-menu sidebar-card shadow-sm p-3 mb-4">
  <h2 class="h6 mb-3 text-secondary">Shop by Category</h2>
  <div class="list-group list-group-flush">
    <?php if (!empty($categories)): ?>
      <?php foreach ($categories as $menuCategory): ?>
        <div class="mb-2">
          <a href="category.php?slug=<?php echo urlencode($menuCategory['slug']); ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center<?php echo $activeCategorySlug === $menuCategory['slug'] && $activeSubcategorySlug === '' ? ' active' : ''; ?>">
            <span><?php echo htmlspecialchars($menuCategory['name']); ?></span>
            <span class="small">&rsaquo;</span>
          </a>
          <?php if (!empty($menuCategory['subcategories'])): ?>
            <div class="list-group list-group-flush ml-3 mt-2">
              <?php foreach ($menuCategory['subcategories'] as $menuSubcategory): ?>
                <a href="subcategory.php?category=<?php echo urlencode($menuCategory['slug']); ?>&slug=<?php echo urlencode($menuSubcategory['slug']); ?>" class="list-group-item list-group-item-action small<?php echo $activeCategorySlug === $menuCategory['slug'] && $activeSubcategorySlug === $menuSubcategory['slug'] ? ' active' : ''; ?>">
                  <?php echo htmlspecialchars($menuSubcategory['name']); ?>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="list-group-item text-muted">Categories not available.</div>
    <?php endif; ?>
  </div>
</div>
