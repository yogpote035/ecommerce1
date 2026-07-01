<?php
$rows = isset($rows) ? (int) $rows : 3;
$variant = $variant ?? 'list';
?>
<div class="loading-skeleton-grid row" aria-hidden="true">
  <?php for ($r = 0; $r < max(1, $rows); $r++): ?>
    <?php if ($variant === 'card'): ?>
      <div class="col-sm-6 col-lg-4 mb-4">
        <div class="card shimmer-card skeleton-card">
          <div class="skeleton-box skeleton-card-image"></div>
          <div class="card-body">
            <div class="skeleton-line mb-2" style="width: 70%;"></div>
            <div class="skeleton-line mb-2" style="width: 45%;"></div>
            <div class="skeleton-line" style="width: 90%;"></div>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div class="col-12 mb-3">
        <div class="card shimmer-card">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="skeleton-box skeleton-list-image mr-3"></div>
              <div class="flex-fill">
                <div class="skeleton-line mb-2" style="width: 60%;"></div>
                <div class="skeleton-line mb-2" style="width: 40%;"></div>
                <div class="skeleton-line" style="width: 80%;"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  <?php endfor; ?>
</div>
