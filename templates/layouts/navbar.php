<?php
// Modern reusable navbar for Phase 3
require_once __DIR__ . '/../../helpers/CategoryHelper.php';
$isAdmin = !empty($_SESSION['admin_logged_in']);
$isCustomer = !empty($_SESSION['customer_logged_in']);
$isRetailer = !empty($_SESSION['retailer_logged_in']) || !empty($_SESSION['rname']);
$isLoggedIn = $isAdmin || $isCustomer || $isRetailer;
$currentPage = basename($_SERVER['PHP_SELF']);
$categoryHelperForNav = new CategoryHelper($conn);
$navCategories = array_slice($categoryHelperForNav->getCategoriesHierarchy(), 0, 8);

if ($isAdmin) {
    $navItems = [
        ['href' => 'Home.php', 'label' => 'Home'],
        ['href' => 'admin/dashboard.php', 'label' => 'Dashboard'],
        ['href' => 'admin_products.php', 'label' => 'Products'],
        ['href' => 'admin_orders.php', 'label' => 'Orders'],
    ];
    $moreNavItems = [
        ['href' => 'admin_categories.php', 'label' => 'Categories'],
        ['href' => 'admin_customers.php', 'label' => 'Customers'],
        ['href' => 'validation.php', 'label' => 'Add Product'],
        ['href' => 'admin/backup-manager.php', 'label' => 'Backups'],
        ['href' => 'admin_logs.php', 'label' => 'Logs'],
        ['href' => 'Track.php', 'label' => 'Track Orders'],
        ['href' => 'Contact.php', 'label' => 'Contact'],
    ];
} elseif ($isCustomer) {
    $navItems = [
        ['href' => 'Home.php', 'label' => 'Home'],
        ['href' => 'index.php', 'label' => 'Products'],
    ];
    $moreNavItems = [
        ['href' => 'wishlist.php', 'label' => 'Wishlist'],
        ['href' => 'checkout.php', 'label' => 'Checkout'],
        ['href' => 'Track.php', 'label' => 'Track'],
        ['href' => 'Contact.php', 'label' => 'Contact'],
    ];
} elseif ($isRetailer) {
    $navItems = [
        ['href' => 'Rmain.php', 'label' => 'Dashboard'],
        ['href' => 'Rview.php', 'label' => 'Catalog'],
        ['href' => 'Radd.php', 'label' => 'Add Product'],
    ];
    $moreNavItems = [];
} else {
    $navItems = [
        ['href' => 'Home.php', 'label' => 'Home'],
        ['href' => 'index.php', 'label' => 'Products'],
        ['href' => 'Contact.php', 'label' => 'Contact'],
    ];
    $moreNavItems = [];
}

$accountLabel = $isLoggedIn ? 'Logout' : 'Login';
$accountHref = $isLoggedIn ? 'logout.php' : 'auth.php';
$cartCount = 0;
$customerId = $_SESSION['customer_id'] ?? $_SESSION['cid'] ?? 0;
if ($customerId > 0) {
    $cartCount = CartHelper::countCustomerCartItems($conn, $customerId);
} elseif (!empty($_SESSION['cart'])) {
    $cartCount = count($_SESSION['cart']);
}
?>
<nav class="navbar navbar-expand-lg navbar-theme fixed-top py-3">
  <div class="container">
    <a class="navbar-brand font-weight-bold" href="<?php echo htmlspecialchars(app_url('Home.php')); ?>">Ecommerce</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ml-auto align-items-center">
        <?php if (!$isAdmin && !$isRetailer): ?>
        <li class="nav-item mr-lg-3 navbar-search-item">
          <form class="form-inline my-3 my-lg-0 position-relative navbar-search-form" action="<?php echo htmlspecialchars(app_url('search.php')); ?>" method="get" onsubmit="return this.querySelector('[name=q]').value.trim() !== '';">
            <div class="input-group">
              <input type="search" name="q" class="form-control navbar-search-input" placeholder="Search products" aria-label="Search" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" data-search-input autocomplete="off">
              <div class="input-group-append">
                <button class="btn btn-primary" type="submit">Search</button>
              </div>
            </div>
            <div class="search-dropdown position-absolute w-100 mt-1 d-none" style="z-index: 1050;">
              <div data-search-results class="list-group list-group-flush bg-white rounded border"></div>
              <div data-search-suggestions class="list-group list-group-flush bg-white rounded border border-top-0"></div>
              <div data-search-history class="list-group list-group-flush bg-white rounded border border-top-0"></div>
            </div>
          </form>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="<?php echo htmlspecialchars(app_url('category.php')); ?>" id="categoryMegaMenu" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Categories</a>
          <div class="dropdown-menu dropdown-menu-right category-mega-dropdown p-3" aria-labelledby="categoryMegaMenu">
            <div class="row">
              <?php foreach ($navCategories as $navCategory): ?>
                <div class="col-md-6 col-lg-4 mb-3">
                  <a class="font-weight-bold d-block mb-2" href="<?php echo htmlspecialchars(app_url('category.php?slug=' . urlencode($navCategory['slug'] ?? ''))); ?>">
                    <?php echo htmlspecialchars($navCategory['name'] ?? 'Category'); ?>
                  </a>
                  <?php foreach (array_slice($navCategory['subcategories'] ?? [], 0, 5) as $navSubcategory): ?>
                    <a class="dropdown-item px-0 py-1 small" href="<?php echo htmlspecialchars(app_url('subcategory.php?slug=' . urlencode($navSubcategory['slug'] ?? ''))); ?>">
                      <?php echo htmlspecialchars($navSubcategory['name'] ?? 'Subcategory'); ?>
                    </a>
                    <?php
                      $children = !empty($navSubcategory['id']) ? array_slice($categoryHelperForNav->getChildCategories((int) $navSubcategory['id']), 0, 3) : [];
                    ?>
                    <?php if (!empty($children)): ?>
                      <div class="small text-secondary pl-2 mb-1">
                        <?php echo htmlspecialchars(implode(' · ', array_column($children, 'name'))); ?>
                      </div>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </li>
        <?php endif; ?>
        <?php foreach ($navItems as $item): ?>
          <li class="nav-item">
            <a class="nav-link<?php echo $currentPage === basename($item['href']) ? ' active' : ''; ?>" href="<?php echo htmlspecialchars(app_url($item['href'])); ?>"><?php echo htmlspecialchars($item['label']); ?></a>
          </li>
        <?php endforeach; ?>
        <?php if (!empty($moreNavItems)): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="mainMoreMenu" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">More</a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="mainMoreMenu">
              <?php foreach ($moreNavItems as $item): ?>
                <a class="dropdown-item" href="<?php echo htmlspecialchars(app_url($item['href'])); ?>"><?php echo htmlspecialchars($item['label']); ?></a>
              <?php endforeach; ?>
            </div>
          </li>
        <?php endif; ?>
        <li class="nav-item ml-lg-3">
          <button id="theme-toggle" type="button" class="btn btn-sm theme-button" aria-label="Toggle theme">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="theme-icon" viewBox="0 0 16 16" aria-hidden="true">
              <path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278"/>
            </svg>
          </button>
        </li>
        <?php if (!$isAdmin && !$isRetailer): ?>
          <li class="nav-item ml-lg-3 mt-2 mt-lg-0">
            <a class="btn btn-primary position-relative d-flex align-items-center justify-content-center" href="<?php echo htmlspecialchars(app_url('view_cart.php')); ?>" aria-label="View cart">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cart-plus-fill" viewBox="0 0 16 16" aria-hidden="true">
                <path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0m7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0M9 5.5V7h1.5a.5.5 0 0 1 0 1H9v1.5a.5.5 0 0 1-1 0V8H6.5a.5.5 0 0 1 0-1H8V5.5a.5.5 0 0 1 1 0"/>
              </svg>
              <?php if ($cartCount > 0): ?>
                <span class="badge bg-light text-dark position-absolute top-0 start-100 translate-middle rounded-pill" style="font-size:0.7rem;">
                  <?php echo $cartCount; ?>
                </span>
              <?php endif; ?>
            </a>
          </li>
        <?php endif; ?>
        <li class="nav-item ml-lg-3 mt-2 mt-lg-0">
          <a class="btn btn-outline-primary" href="<?php echo htmlspecialchars(app_url($accountHref)); ?>"><?php echo htmlspecialchars($accountLabel); ?></a>
        </li>
      </ul>
    </div>
  </div>
</nav>
