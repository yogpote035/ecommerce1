<?php

class PaginationHelper {
    public const PER_PAGE = 20;

    public static function currentPage($key = 'page') {
        $value = isset($_GET[$key]) ? $_GET[$key] : 1;
        if (is_array($value)) {
            return 1;
        }
        $value = trim((string)$value);
        return $value !== '' && ctype_digit($value) ? max(1, (int)$value) : 1;
    }

    public static function totalPages($totalItems, $perPage) {
        $perPage = max(1, min(self::PER_PAGE, (int)$perPage));
        return max(1, (int)ceil(((int)$totalItems) / $perPage));
    }

    public static function offset($currentPage, $perPage) {
        $perPage = max(1, min(self::PER_PAGE, (int)$perPage));
        return max(0, ((int)$currentPage - 1) * $perPage);
    }

    public static function url($page, array $overrides = [], $pageKey = 'page') {
        $query = array_merge($_GET, $overrides);
        $query[$pageKey] = max(1, (int)$page);
        foreach ($query as $key => $value) {
            if ($value === '' || $value === null || is_array($value)) {
                unset($query[$key]);
            }
        }
        return '?' . http_build_query($query);
    }

    public static function render($currentPage, $totalPages, $totalItems, $perPage, $label = 'records', array $options = []) {
        $currentPage = max(1, (int)$currentPage);
        $totalPages = max(1, (int)$totalPages);
        $totalItems = max(0, (int)$totalItems);
        $perPage = max(1, min(self::PER_PAGE, (int)$perPage));

        $pageKey = $options['pageKey'] ?? 'page';
        $showFrom = $totalItems === 0 ? 0 : (($currentPage - 1) * $perPage) + 1;
        $showTo = min($totalItems, $currentPage * $perPage);
        $start = max(1, $currentPage - 2);
        $end = min($totalPages, $currentPage + 2);
        ?>
        <nav class="pagination-section" aria-label="<?php echo htmlspecialchars(ucfirst($label)); ?> pagination">
          <div class="pagination-info">
            <span class="pagination-text">
              SHOWING <?php echo number_format($showFrom); ?> TO <?php echo number_format($showTo); ?> OF <?php echo number_format($totalItems); ?> <?php echo htmlspecialchars(strtoupper($label)); ?>
            </span>
          </div>
          <div class="pagination-controls">
            <?php if ($currentPage > 1): ?>
              <a href="<?php echo htmlspecialchars(self::url($currentPage - 1, [], $pageKey)); ?>" class="pagination-nav-btn" aria-label="Previous page">&lsaquo;</a>
            <?php else: ?>
              <span class="pagination-nav-btn disabled" aria-hidden="true">&lsaquo;</span>
            <?php endif; ?>

            <div class="pagination-numbers">
              <?php if ($start > 1): ?>
                <a href="<?php echo htmlspecialchars(self::url(1, [], $pageKey)); ?>" class="pagination-page-btn">1</a>
                <span class="pagination-dots">...</span>
              <?php endif; ?>

              <?php for ($i = $start; $i <= $end; $i++): ?>
                <a href="<?php echo htmlspecialchars(self::url($i, [], $pageKey)); ?>" class="pagination-page-btn<?php echo $i === $currentPage ? ' active' : ''; ?>"<?php echo $i === $currentPage ? ' aria-current="page"' : ''; ?>>
                  <?php echo $i; ?>
                </a>
              <?php endfor; ?>

              <?php if ($end < $totalPages): ?>
                <span class="pagination-dots">...</span>
                <a href="<?php echo htmlspecialchars(self::url($totalPages, [], $pageKey)); ?>" class="pagination-page-btn"><?php echo $totalPages; ?></a>
              <?php endif; ?>
            </div>

            <?php if ($currentPage < $totalPages): ?>
              <a href="<?php echo htmlspecialchars(self::url($currentPage + 1, [], $pageKey)); ?>" class="pagination-nav-btn" aria-label="Next page">&rsaquo;</a>
            <?php else: ?>
              <span class="pagination-nav-btn disabled" aria-hidden="true">&rsaquo;</span>
            <?php endif; ?>
          </div>
        </nav>
        <?php
    }
}
