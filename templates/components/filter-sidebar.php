<?php
$filterAction = $filterAction ?? 'index.php';
$filterHidden = $filterHidden ?? [];
$priceMin = $priceMin ?? 0;
$priceMax = $priceMax ?? 0;
$sort = $sort ?? '';
$filterClearUrl = $filterClearUrl ?? $filterAction;
?>
<div class="sidebar-card sidebar-sticky shadow-sm mb-4">
  <div class="card-body">
    <h4 class="h5">Filters</h4>
    <form method="get" action="<?php echo htmlspecialchars($filterAction); ?>">
      <?php foreach ($filterHidden as $name => $value): ?>
        <input type="hidden" name="<?php echo htmlspecialchars($name); ?>" value="<?php echo htmlspecialchars($value); ?>">
      <?php endforeach; ?>
      <div class="form-group mb-3">
        <label for="price_min" class="small font-weight-bold">Min Price</label>
        <input id="price_min" name="price_min" type="number" step="0.01" min="0" class="form-control" value="<?php echo $priceMin > 0 ? htmlspecialchars($priceMin) : ''; ?>" placeholder="Rs 0">
      </div>
      <div class="form-group mb-3">
        <label for="price_max" class="small font-weight-bold">Max Price</label>
        <input id="price_max" name="price_max" type="number" step="0.01" min="0" class="form-control" value="<?php echo $priceMax > 0 ? htmlspecialchars($priceMax) : ''; ?>" placeholder="Rs 0">
      </div>
      <div class="form-group mb-3">
        <label for="sort" class="small font-weight-bold">Sort</label>
        <select id="sort" name="sort" class="form-control">
          <option value="">Default</option>
          <option value="price_low_high"<?php echo $sort === 'price_low_high' ? ' selected' : ''; ?>>Price: Low to High</option>
          <option value="price_high_low"<?php echo $sort === 'price_high_low' ? ' selected' : ''; ?>>Price: High to Low</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Apply filters</button>
      <a href="<?php echo htmlspecialchars($filterClearUrl); ?>" class="btn btn-outline-secondary btn-block mt-2">Clear filters</a>
    </form>
  </div>
</div>
