<?php
$searchQuery = htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8');
$searchPlaceholder = $searchPlaceholder ?? 'Search products, brands...';
$searchClass = $searchClass ?? '';
?>
<div class="search-box-wrapper position-relative <?php echo htmlspecialchars($searchClass, ENT_QUOTES, 'UTF-8'); ?>">
  <form class="search-box-form" action="search.php" method="get" onsubmit="return this.querySelector('[name=q]').value.trim() !== '';">
    <div class="input-group">
      <input
        type="search"
        name="q"
        class="form-control"
        placeholder="<?php echo htmlspecialchars($searchPlaceholder, ENT_QUOTES, 'UTF-8'); ?>"
        autocomplete="off"
        value="<?php echo $searchQuery; ?>"
        data-search-input
        aria-label="Search products"
      >
      <div class="input-group-append">
        <button class="btn btn-primary" type="submit">Search</button>
      </div>
    </div>
    <div class="search-dropdown d-none position-absolute w-100 mt-1" style="z-index:1050;">
      <div data-search-results class="list-group list-group-flush bg-white rounded border"></div>
      <div data-search-suggestions class="list-group list-group-flush bg-white rounded border border-top-0"></div>
      <div data-search-history class="list-group list-group-flush bg-white rounded border border-top-0"></div>
    </div>
  </form>
</div>
