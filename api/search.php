<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../helpers/CategoryHelper.php';

$query = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');
$response = [
    'success' => false,
    'query' => $query,
    'results' => [],
    'suggestions' => [],
    'history' => []
];

$searchTerm = '%' . strtolower($query) . '%';
$customerId = $_SESSION['customer_id'] ?? $_SESSION['cid'] ?? 0;

function searchUrl($path) {
    return function_exists('app_url') ? app_url($path) : '../' . ltrim($path, '/');
}

function findCategoryUrlByName($conn, $name) {
    $name = trim((string) $name);
    if (strpos($name, '>') !== false) {
        $parts = array_map('trim', explode('>', $name));
        $childName = end($parts);
        if ($childName !== '') {
            $childUrl = findCategoryUrlByName($conn, $childName);
            if (strpos($childUrl, 'search.php') === false) {
                return $childUrl;
            }
        }
    }

    $sources = [
        ['table' => 'categories', 'target' => 'category.php'],
        ['table' => 'sub_categories', 'target' => 'subcategory.php'],
        ['table' => 'child_categories', 'target' => 'subcategory.php'],
    ];

    foreach ($sources as $source) {
        $stmt = mysqli_prepare($conn, "SELECT slug FROM {$source['table']} WHERE name = ? LIMIT 1");
        if (!$stmt) {
            continue;
        }
        mysqli_stmt_bind_param($stmt, 's', $name);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $slug);
        if (mysqli_stmt_fetch($stmt) && $slug !== '') {
            mysqli_stmt_close($stmt);
            return searchUrl($source['target'] . '?slug=' . urlencode($slug));
        }
        mysqli_stmt_close($stmt);
    }

    $categoryHelper = new CategoryHelper($conn);
    foreach ($categoryHelper->getCategoriesHierarchy() as $category) {
        if (strcasecmp($category['name'] ?? '', $name) === 0 && !empty($category['slug'])) {
            return searchUrl('category.php?slug=' . urlencode($category['slug']));
        }
        foreach ($category['subcategories'] ?? [] as $subcategory) {
            if (strcasecmp($subcategory['name'] ?? '', $name) === 0 && !empty($subcategory['slug'])) {
                return searchUrl('subcategory.php?slug=' . urlencode($subcategory['slug']));
            }
        }
    }

    return searchUrl('search.php?q=' . urlencode($name));
}

function ensureSearchHistoryTable($conn) {
    return mysqli_query($conn, "CREATE TABLE IF NOT EXISTS search_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT NOT NULL,
        search_query VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_customer_query (customer_id, search_query),
        KEY idx_customer_updated (customer_id, updated_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4") !== false;
}

if ($query !== '') {
    $sql = 'SELECT Apid, apname, apbrand, apcategory, apprice, apimage FROM apadd WHERE (LOWER(apname) LIKE ? OR LOWER(apbrand) LIKE ? OR LOWER(apcategory) LIKE ?)';
    $params = [$searchTerm, $searchTerm, $searchTerm];
    $types = 'sss';

    if ($category !== '') {
        $sql .= ' AND apcategory = ?';
        $params[] = $category;
        $types .= 's';
    }

    $sql .= ' ORDER BY Apid DESC LIMIT 8';
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $response['results'][] = [
                'type' => 'product',
                'id' => (int) $row['Apid'],
                'title' => $row['apname'],
                'brand' => $row['apbrand'],
                'category' => $row['apcategory'],
                'price' => number_format((float) $row['apprice'], 2),
                'image' => searchUrl(!empty($row['apimage']) ? $row['apimage'] : 'images/products/accessories.jpg'),
                'url' => searchUrl('product.php?id=' . urlencode($row['Apid'])),
            ];
        }
        mysqli_stmt_close($stmt);
    }
}

// Search suggestions by brand and category
$suggestionQueries = [
    'brand' => 'SELECT DISTINCT apbrand AS value FROM apadd WHERE LOWER(apbrand) LIKE ? LIMIT 5',
    'category' => 'SELECT DISTINCT apcategory AS value FROM apadd WHERE LOWER(apcategory) LIKE ? LIMIT 5'
];

foreach ($suggestionQueries as $type => $querySql) {
    $stmt = mysqli_prepare($conn, $querySql);
    if (!$stmt) {
        continue;
    }
    mysqli_stmt_bind_param($stmt, 's', $searchTerm);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        if (!empty($row['value'])) {
            $response['suggestions'][] = [
                'type' => $type,
                'text' => $row['value'],
                'query' => $row['value'],
                'url' => $type === 'category' ? findCategoryUrlByName($conn, $row['value']) : searchUrl('search.php?q=' . urlencode($row['value'])),
            ];
        }
    }
    mysqli_stmt_close($stmt);
}

// Session-based search history
if (!isset($_SESSION['search_history']) || !is_array($_SESSION['search_history'])) {
    $_SESSION['search_history'] = [];
}

$normalizedQuery = strtolower($query);
if ($normalizedQuery !== '' && !in_array($normalizedQuery, $_SESSION['search_history'], true)) {
    array_unshift($_SESSION['search_history'], $normalizedQuery);
    $_SESSION['search_history'] = array_slice($_SESSION['search_history'], 0, 5);
}

if ($customerId > 0 && $normalizedQuery !== '' && ensureSearchHistoryTable($conn)) {
    $stmt = mysqli_prepare($conn, 'INSERT INTO search_history (customer_id, search_query) VALUES (?, ?) ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP');
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'is', $customerId, $normalizedQuery);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

foreach ($_SESSION['search_history'] as $historyQuery) {
    if (stripos($historyQuery, $query) !== false) {
        $response['history'][] = [
            'type' => 'history',
            'text' => $historyQuery,
            'query' => $historyQuery,
            'url' => searchUrl('search.php?q=' . urlencode($historyQuery)),
        ];
    }
}

if ($customerId > 0 && ensureSearchHistoryTable($conn)) {
    $historySql = $query !== ''
        ? 'SELECT search_query FROM search_history WHERE customer_id = ? AND search_query LIKE ? ORDER BY updated_at DESC LIMIT 5'
        : 'SELECT search_query FROM search_history WHERE customer_id = ? ORDER BY updated_at DESC LIMIT 5';
    $stmt = mysqli_prepare($conn, $historySql);
    if ($stmt) {
        if ($query !== '') {
            $likeQuery = '%' . $normalizedQuery . '%';
            mysqli_stmt_bind_param($stmt, 'is', $customerId, $likeQuery);
        } else {
            mysqli_stmt_bind_param($stmt, 'i', $customerId);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $historyText = $row['search_query'];
            $response['history'][] = [
                'type' => 'history',
                'text' => $historyText,
                'query' => $historyText,
                'url' => searchUrl('search.php?q=' . urlencode($historyText)),
            ];
        }
        mysqli_stmt_close($stmt);
    }
}

$response['history'] = array_values(array_unique($response['history'], SORT_REGULAR));

$response['success'] = true;

echo json_encode($response);
exit;
