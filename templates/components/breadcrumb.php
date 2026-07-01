<?php
/**
 * Expected input:
 * $breadcrumbs = [
 *   ['label' => 'Home', 'href' => 'Home.php'],
 *   ['label' => 'Products', 'href' => 'index.php?page=1'],
 *   ['label' => 'Current page'],
 * ];
 */
$breadcrumbs = $breadcrumbs ?? [];
if (empty($breadcrumbs)) {
    $breadcrumbs = [
        ['label' => 'Home', 'href' => 'Home.php'],
        ['label' => $siteTitle ?? 'Page'],
    ];
}
?>
<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb bg-transparent px-0 mb-0">
    <?php foreach ($breadcrumbs as $index => $crumb): ?>
      <?php $isLast = $index === count($breadcrumbs) - 1; ?>
      <li class="breadcrumb-item<?php echo $isLast ? ' active' : ''; ?>"<?php echo $isLast ? ' aria-current="page"' : ''; ?>>
        <?php if (!$isLast && !empty($crumb['href'])): ?>
          <a href="<?php echo htmlspecialchars($crumb['href']); ?>"><?php echo htmlspecialchars($crumb['label']); ?></a>
        <?php else: ?>
          <?php echo htmlspecialchars($crumb['label']); ?>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ol>
</nav>
