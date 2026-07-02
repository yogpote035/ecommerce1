<?php
$siteTitle = $siteTitle ?? 'Ecommerce';
$assetBase = function_exists('app_url') ? app_url() : '';
$mainClass = !empty($_SESSION['admin_logged_in']) ? 'container-fluid admin-main' : 'container py-4';
$csrfToken = SecurityHelper::generateCSRFToken();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title><?php echo htmlspecialchars($siteTitle); ?></title>
  <script>
    (function() {
      var storedTheme = localStorage.getItem('ecommerce_theme');
      var systemTheme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
      var theme = storedTheme || systemTheme;
      document.documentElement.setAttribute('data-theme', theme);
    })();
  </script>
  <!-- Using current Bootstrap 4.5.3 for app UI modernization -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="<?php echo htmlspecialchars($assetBase); ?>public/assets/css/theme.css">
  <link rel="stylesheet" href="<?php echo htmlspecialchars($assetBase); ?>public/assets/css/main.css">
  <link rel="stylesheet" href="<?php echo htmlspecialchars($assetBase); ?>public/assets/css/components.css">
  <link rel="stylesheet" href="<?php echo htmlspecialchars($assetBase); ?>public/assets/css/animations.css">
  <link rel="stylesheet" href="<?php echo htmlspecialchars($assetBase); ?>public/assets/css/responsive.css">
  <style>
    body {
      background: var(--bg);
      color: var(--text-primary);
      font-family: var(--font-family);
      padding-top: 80px;
      transition: background var(--transition), color var(--transition);
    }
    .navbar-theme {
      background: var(--navbar-bg) !important;
      backdrop-filter: blur(20px);
      border-bottom: 1px solid var(--border);
      box-shadow: var(--shadow-soft);
    }
    .navbar-theme .container {
      max-width: 1480px;
    }
    .navbar-theme .navbar-collapse {
      min-width: 0;
    }
    .navbar-theme .navbar-nav {
      flex-wrap: wrap;
      row-gap: 0.35rem;
      justify-content: flex-end;
    }
    .navbar-theme .nav-link {
      padding-left: 0.55rem !important;
      padding-right: 0.55rem !important;
      white-space: nowrap;
      font-size: 0.94rem;
    }
    .navbar-search-item {
      flex: 1 1 260px;
      min-width: 220px;
      max-width: 360px;
    }
    .navbar-search-form .input-group {
      width: 100%;
    }
    .navbar-search-form .search-dropdown {
      position: fixed !important;
      top: 86px;
      left: 50%;
      right: auto;
      transform: translateX(-50%);
      width: min(640px, calc(100vw - 2rem)) !important;
      max-height: calc(100vh - 110px);
      overflow-y: auto;
      z-index: 1040 !important;
    }
    .category-mega-dropdown {
      position: fixed !important;
      top: 86px;
      right: 1rem;
      left: auto !important;
      transform: none !important;
      width: min(680px, calc(100vw - 2rem));
      max-height: calc(100vh - 110px);
      overflow-y: auto;
      z-index: 1040;
    }
    .navbar-theme .nav-link,
    .navbar-theme .navbar-brand,
    .navbar-theme .navbar-text,
    .navbar-theme .dropdown-item {
      color: var(--text-primary) !important;
    }
    .navbar-theme .nav-link:hover {
      color: var(--primary) !important;
    }
    .navbar-theme .navbar-toggler-icon {
      width: 1.5rem;
      height: 1.5rem;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: var(--text-primary);
    }
    .navbar-theme .navbar-toggler-icon::before {
      content: "\2630";
      font-size: 1.2rem;
      color: var(--text-primary);
    }
    .theme-button {
      min-width: 2.8rem;
      min-height: 2.8rem;
      padding: 0.5rem;
      background: var(--toolbar-bg) !important;
      border: 1px solid var(--border) !important;
      color: var(--text-primary) !important;
      border-radius: 999px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: background var(--transition), border-color var(--transition), color var(--transition);
    }
    .theme-button:hover {
      background: var(--toolbar-hover) !important;
    }
    .theme-icon {
      width: 1rem;
      height: 1rem;
      display: block;
      color: inherit;
      fill: currentColor;
    }
    .toast-container {
      position: fixed;
      top: 4rem;
      right: 1rem;
      z-index: 1080;
    }
    .app-toast {
      min-width: min(320px, calc(100vw - 2rem));
      border: 0;
      border-radius: 0.75rem;
      box-shadow: var(--shadow-soft);
      overflow: hidden;
    }
    .app-toast .toast-body {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      padding: 1rem 1.1rem;
      font-weight: 600;
      color: inherit;
    }
    .app-toast .close {
      color: inherit;
      opacity: 0.8;
      text-shadow: none;
    }
    .app-toast .close:hover,
    .app-toast .close:focus {
      opacity: 1;
    }
    .app-toast-success,
    .toast.app-toast-success {
      background: #16a34a !important;
      color: #fff !important;
    }
    .app-toast-danger,
    .toast.app-toast-danger {
      background: #dc2626 !important;
      color: #fff !important;
    }
    .app-toast-warning,
    .toast.app-toast-warning {
      background: #f59e0b !important;
      color: #111827 !important;
    }
    .app-toast-info,
    .toast.app-toast-info {
      background: #2563eb !important;
      color: #fff !important;
    }
    [data-theme="dark"] .toast.app-toast-success {
      background: #16a34a !important;
      color: #fff !important;
    }
    [data-theme="dark"] .toast.app-toast-danger {
      background: #dc2626 !important;
      color: #fff !important;
    }
    [data-theme="dark"] .toast.app-toast-warning {
      background: #f59e0b !important;
      color: #111827 !important;
    }
    [data-theme="dark"] .toast.app-toast-info {
      background: #2563eb !important;
      color: #fff !important;
    }
    @media (min-width: 992px) {
      body {
        padding-top: 96px;
      }
      .navbar-theme {
        min-height: 72px;
      }
    }
    body.admin-layout {
      padding-top: 74px;
    }
    body.admin-layout .navbar-theme {
      min-height: 64px;
      padding-top: 0.45rem !important;
      padding-bottom: 0.45rem !important;
    }
    body.admin-layout .navbar-theme .nav-link {
      padding-top: 0.45rem !important;
      padding-bottom: 0.45rem !important;
    }
    body.admin-layout .navbar-theme .btn,
    body.admin-layout .navbar-theme .theme-button {
      min-height: 40px;
      padding-top: 0.42rem;
      padding-bottom: 0.42rem;
    }
    body.admin-layout .admin-main {
      padding-top: 0.65rem;
      padding-bottom: 1.25rem;
    }
    body.admin-layout .dashboard-shell {
      padding-top: 0.65rem !important;
    }
    body.admin-layout .admin-main > .container-fluid:first-child,
    body.admin-layout .admin-main > .container:first-child {
      margin-top: 0 !important;
      padding-top: 0 !important;
    }
    body.admin-layout h1,
    body.admin-layout .h1,
    body.admin-layout .h2 {
      margin-top: 0;
    }
    @media (min-width: 992px) {
      body.admin-layout {
        padding-top: 72px;
      }
    }
    @media (max-width: 991px) {
      .navbar-search-item {
        width: 100%;
        max-width: none;
      }
      .navbar-search-form .search-dropdown {
        top: 76px;
      }
      .category-mega-dropdown {
        position: static !important;
        width: 100%;
        max-height: 60vh;
        overflow-y: auto;
      }
      .navbar-theme .navbar-nav {
        align-items: stretch !important;
      }
      .navbar-theme .nav-link,
      .navbar-theme .btn {
        width: 100%;
        justify-content: center;
      }
    }
  </style>
</head>
<body class="<?php echo !empty($_SESSION['admin_logged_in']) ? 'admin-layout' : ''; ?>">
<?php include __DIR__ . '/layouts/navbar.php'; ?>
<?php if (!empty($_SESSION['toast'])): ?>
<div class="toast-container">
  <?php
    $toastType = $_SESSION['toast']['type'] ?? 'info';
    $toastMessage = htmlspecialchars($_SESSION['toast']['message'] ?? '');
    $toastClass = in_array($toastType, ['success', 'danger', 'warning', 'info'], true) ? 'app-toast-' . $toastType : 'app-toast-info';
  ?>
  <div class="toast app-toast <?php echo htmlspecialchars($toastClass); ?> show" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000" data-autohide="true">
    <div class="toast-body">
      <span><?php echo $toastMessage; ?></span>
      <button type="button" class="close" data-dismiss="toast" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  </div>
</div>
<?php unset($_SESSION['toast']); endif; ?>
<main class="<?php echo htmlspecialchars($mainClass); ?>">
