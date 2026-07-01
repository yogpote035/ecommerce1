<?php
// Category menu component - Phase 3 starter
// Expected input: $categories = [ ['id' => 1, 'name' => 'Category', 'slug' => 'category', 'subcategories' => [...]], ... ];
?>
<div class="category-menu bg-surface border rounded-lg shadow-soft p-3 mb-4">
  <h2 class="h6 mb-3 text-secondary">Shop by Category</h2>
  <ul class="list-unstyled mb-0">
    <?php foreach ($categories as $category): ?>
      <li class="mb-2">
        <a href="category.php?slug=<?php echo urlencode($category['slug']); ?>" class="d-flex justify-content-between align-items-center text-body text-decoration-none py-2 px-2 rounded hover-bg-light">
          <span><?php echo htmlspecialchars($category['name']); ?></span>
          <span class="text-muted small">›</span>
        </a>
        <?php if (!empty($category['subcategories'])): ?>
          <ul class="list-unstyled ps-3 mt-2">
            <?php foreach ($category['subcategories'] as $subcategory): ?>
              <li class="mb-1">
                <a href="subcategory.php?slug=<?php echo urlencode($subcategory['slug']); ?>" class="text-secondary text-decoration-none small">
                  <?php echo htmlspecialchars($subcategory['name']); ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
