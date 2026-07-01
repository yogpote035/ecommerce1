<?php
/**
 * Shared page wrapper for modernized procedural pages.
 *
 * Usage:
 *   $siteTitle = 'Products';
 *   $contentFile = __DIR__ . '/templates/customer/products.php';
 *   include __DIR__ . '/templates/layouts/base.php';
 *
 * Or pass already-rendered markup through $pageContent / $content.
 */
if (!isset($conn)) {
    require_once __DIR__ . '/../../init.php';
}

$siteTitle = $siteTitle ?? 'Ecommerce';

include __DIR__ . '/../header.php';

if (!empty($contentFile) && file_exists($contentFile)) {
    include $contentFile;
} elseif (!empty($pageContent)) {
    echo $pageContent;
} else {
    echo $content ?? '';
}

include __DIR__ . '/../footer.php';
