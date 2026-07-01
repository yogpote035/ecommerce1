<?php
class CategoryHelper {
    private $db;
    private $hasSchema;
    private $fallbackCategories = [
        [
            'id' => 1,
            'name' => 'Bags',
            'slug' => 'bags',
            'icon' => '',
            'image' => 'images/products/bags.jpg',
            'description' => 'Carry everything in style with our curated bag collections.',
            'subcategories' => [
                ['id' => 101, 'name' => 'Travel Bags', 'slug' => 'travel-bags'],
                ['id' => 102, 'name' => 'College Bags', 'slug' => 'college-bags'],
                ['id' => 103, 'name' => 'Backpacks', 'slug' => 'backpacks'],
                ['id' => 104, 'name' => 'Handbags', 'slug' => 'handbags'],
                ['id' => 105, 'name' => 'Laptop Bags', 'slug' => 'laptop-bags'],
                ['id' => 106, 'name' => 'Duffel Bags', 'slug' => 'duffel-bags'],
                ['id' => 107, 'name' => 'Sling Bags', 'slug' => 'sling-bags'],
                ['id' => 108, 'name' => 'Messenger Bags', 'slug' => 'messenger-bags'],
            ],
        ],
        [
            'id' => 2,
            'name' => 'Accessories',
            'slug' => 'accessories',
            'icon' => '',
            'image' => 'images/products/accessories.jpg',
            'description' => 'Find the finishing touches for any outfit or lifestyle.',
            'subcategories' => [
                ['id' => 201, 'name' => 'Jewelry', 'slug' => 'jewelry'],
                ['id' => 202, 'name' => 'Watches', 'slug' => 'watches'],
                ['id' => 203, 'name' => 'Sunglasses', 'slug' => 'sunglasses'],
                ['id' => 204, 'name' => 'Belts', 'slug' => 'belts'],
                ['id' => 205, 'name' => 'Wallets', 'slug' => 'wallets'],
                ['id' => 206, 'name' => 'Phone Cases', 'slug' => 'phone-cases'],
                ['id' => 207, 'name' => 'Hats', 'slug' => 'hats'],
                ['id' => 208, 'name' => 'Scarves', 'slug' => 'scarves'],
            ],
        ],
        [
            'id' => 3,
            'name' => 'Clothes',
            'slug' => 'clothes',
            'icon' => '',
            'image' => 'images/products/clothes.jpg',
            'description' => 'Explore curated looks for every season and occasion.',
            'subcategories' => [
                ['id' => 301, 'name' => 'Tops', 'slug' => 'tops'],
                ['id' => 302, 'name' => 'Bottoms', 'slug' => 'bottoms'],
                ['id' => 303, 'name' => 'Innerwear', 'slug' => 'innerwear'],
                ['id' => 304, 'name' => 'Outerwear', 'slug' => 'outerwear'],
                ['id' => 305, 'name' => 'Sportswear', 'slug' => 'sportswear'],
                ['id' => 306, 'name' => 'Ethnic Wear', 'slug' => 'ethnic-wear'],
                ['id' => 307, 'name' => 'Sleepwear', 'slug' => 'sleepwear'],
                ['id' => 308, 'name' => 'Formalwear', 'slug' => 'formalwear'],
            ],
        ],
        [
            'id' => 4,
            'name' => 'Footwear',
            'slug' => 'footwear',
            'icon' => '',
            'image' => 'images/products/footwear.jpg',
            'description' => 'Step into comfort and style with our footwear collections.',
            'subcategories' => [
                ['id' => 401, 'name' => 'Shoes', 'slug' => 'shoes'],
                ['id' => 402, 'name' => 'Slippers', 'slug' => 'slippers'],
                ['id' => 403, 'name' => 'Sports Shoes', 'slug' => 'sports-shoes'],
                ['id' => 404, 'name' => 'Sandals', 'slug' => 'sandals'],
                ['id' => 405, 'name' => 'Boots', 'slug' => 'boots'],
                ['id' => 406, 'name' => 'Casual Shoes', 'slug' => 'casual-shoes'],
                ['id' => 407, 'name' => 'Loafers', 'slug' => 'loafers'],
                ['id' => 408, 'name' => 'Flip Flops', 'slug' => 'flip-flops'],
            ],
        ],
        [
            'id' => 5,
            'name' => 'Appliances',
            'slug' => 'appliances',
            'icon' => '',
            'image' => 'images/products/appliances.jpg',
            'description' => 'Home and personal appliances for daily convenience.',
            'subcategories' => [
                ['id' => 501, 'name' => 'Kitchen Appliances', 'slug' => 'kitchen-appliances'],
                ['id' => 502, 'name' => 'Home Appliances', 'slug' => 'home-appliances'],
                ['id' => 503, 'name' => 'Personal Care', 'slug' => 'personal-care'],
                ['id' => 504, 'name' => 'Cleaning Appliances', 'slug' => 'cleaning-appliances'],
                ['id' => 505, 'name' => 'Air Conditioners', 'slug' => 'air-conditioners'],
                ['id' => 506, 'name' => 'Water Heaters', 'slug' => 'water-heaters'],
            ],
        ],
        [
            'id' => 6,
            'name' => 'Electronics',
            'slug' => 'electronics',
            'icon' => '',
            'image' => 'images/products/electronics.jpg',
            'description' => 'Gadgets and devices to keep you connected and productive.',
            'subcategories' => [
                ['id' => 601, 'name' => 'Mobile Phones', 'slug' => 'mobile-phones'],
                ['id' => 602, 'name' => 'Laptops', 'slug' => 'laptops'],
                ['id' => 603, 'name' => 'Audio', 'slug' => 'audio'],
                ['id' => 604, 'name' => 'Smart Home', 'slug' => 'smart-home'],
                ['id' => 605, 'name' => 'Cameras', 'slug' => 'cameras'],
                ['id' => 606, 'name' => 'Wearables', 'slug' => 'wearables'],
            ],
        ],
        [
            'id' => 7,
            'name' => 'Home & Living',
            'slug' => 'home-living',
            'icon' => '',
            'image' => 'images/products/home-living.jpg',
            'description' => 'Decor and furniture to make every room feel welcoming.',
            'subcategories' => [
                ['id' => 701, 'name' => 'Decor', 'slug' => 'decor'],
                ['id' => 702, 'name' => 'Furniture', 'slug' => 'furniture'],
                ['id' => 703, 'name' => 'Bedding', 'slug' => 'bedding'],
                ['id' => 704, 'name' => 'Lighting', 'slug' => 'lighting'],
                ['id' => 705, 'name' => 'Kitchenware', 'slug' => 'kitchenware'],
            ],
        ],
        [
            'id' => 8,
            'name' => 'Sports & Outdoors',
            'slug' => 'sports-outdoors',
            'icon' => '',
            'image' => 'images/products/sports.jpg',
            'description' => 'Gear, apparel, and accessories for active lifestyles.',
            'subcategories' => [
                ['id' => 801, 'name' => 'Fitness', 'slug' => 'fitness'],
                ['id' => 802, 'name' => 'Outdoor Gear', 'slug' => 'outdoor-gear'],
                ['id' => 803, 'name' => 'Cycling', 'slug' => 'cycling'],
                ['id' => 804, 'name' => 'Camping', 'slug' => 'camping'],
                ['id' => 805, 'name' => 'Yoga', 'slug' => 'yoga'],
            ],
        ],
        [
            'id' => 9,
            'name' => 'Beauty & Personal Care',
            'slug' => 'beauty-personal-care',
            'icon' => '',
            'image' => 'images/products/beauty.jpg',
            'description' => 'Beauty essentials and personal care items for daily self-care routines.',
            'subcategories' => [
                ['id' => 901, 'name' => 'Skincare', 'slug' => 'skincare'],
                ['id' => 902, 'name' => 'Hair Care', 'slug' => 'hair-care'],
                ['id' => 903, 'name' => 'Makeup', 'slug' => 'makeup'],
                ['id' => 904, 'name' => 'Fragrance', 'slug' => 'fragrance'],
            ],
        ],
        [
            'id' => 10,
            'name' => 'Kids & Babies',
            'slug' => 'kids-babies',
            'icon' => '',
            'image' => 'images/products/kids.jpg',
            'description' => 'Products for children and infants, from clothing to nursery essentials.',
            'subcategories' => [
                ['id' => 1001, 'name' => 'Toys', 'slug' => 'toys'],
                ['id' => 1002, 'name' => 'Baby Clothing', 'slug' => 'baby-clothing'],
                ['id' => 1003, 'name' => 'Feeding', 'slug' => 'feeding'],
                ['id' => 1004, 'name' => 'Nursery', 'slug' => 'nursery'],
            ],
        ],
        [
            'id' => 11,
            'name' => 'Books & Stationery',
            'slug' => 'books-stationery',
            'icon' => '',
            'image' => 'images/products/books.jpg',
            'description' => 'Books, notebooks, and stationery for work, school, and leisure.',
            'subcategories' => [
                ['id' => 1101, 'name' => 'Books', 'slug' => 'books'],
                ['id' => 1102, 'name' => 'Office Supplies', 'slug' => 'office-supplies'],
                ['id' => 1103, 'name' => 'Art Supplies', 'slug' => 'art-supplies'],
                ['id' => 1104, 'name' => 'Notebooks', 'slug' => 'notebooks'],
            ],
        ],
        [
            'id' => 12,
            'name' => 'Pets',
            'slug' => 'pets',
            'icon' => '',
            'image' => 'images/products/pets.jpg',
            'description' => 'Everything for pets, from food to toys and grooming accessories.',
            'subcategories' => [
                ['id' => 1201, 'name' => 'Pet Food', 'slug' => 'pet-food'],
                ['id' => 1202, 'name' => 'Grooming', 'slug' => 'grooming'],
                ['id' => 1203, 'name' => 'Toys', 'slug' => 'pet-toys'],
                ['id' => 1204, 'name' => 'Accessories', 'slug' => 'pet-accessories'],
            ],
        ],
        [
            'id' => 13,
            'name' => 'Automotive',
            'slug' => 'automotive',
            'icon' => '',
            'image' => 'images/products/automotive.jpg',
            'description' => 'Car and motorcycle accessories, tools, and maintenance essentials.',
            'subcategories' => [
                ['id' => 1301, 'name' => 'Car Accessories', 'slug' => 'car-accessories'],
                ['id' => 1302, 'name' => 'Motorcycle Gear', 'slug' => 'motorcycle-gear'],
                ['id' => 1303, 'name' => 'Tools', 'slug' => 'tools'],
                ['id' => 1304, 'name' => 'Maintenance', 'slug' => 'maintenance'],
            ],
        ],
    ];

    public function __construct($conn) {
        $this->db = $conn;
        $this->hasSchema = $this->db && $this->tableExists('categories') && $this->tableExists('sub_categories');
    }

    public function getCategoriesHierarchy() {
        if (!$this->hasSchema) {
            return $this->fallbackCategories;
        }

        $sql = "
            SELECT
                c.id AS category_id,
                c.name AS category_name,
                c.slug AS category_slug,
                c.icon AS category_icon,
                c.image AS category_image,
                c.description AS category_description,
                s.id AS sub_id,
                s.name AS sub_name,
                s.slug AS sub_slug
            FROM categories c
            LEFT JOIN sub_categories s ON s.category_id = c.id AND s.is_active = 1
            WHERE c.is_active = 1
            ORDER BY c.name ASC, s.name ASC
        ";

        $stmt = $this->db->prepare($sql);
        if (!$stmt || !mysqli_stmt_execute($stmt)) {
            return $this->fallbackCategories;
        }

        $result = mysqli_stmt_get_result($stmt);
        $categories = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $categoryId = (int) $row['category_id'];
            if (!isset($categories[$categoryId])) {
                $categories[$categoryId] = [
                    'id' => $categoryId,
                    'name' => $row['category_name'],
                    'slug' => $row['category_slug'],
                    'icon' => $row['category_icon'],
                    'image' => $row['category_image'],
                    'description' => $row['category_description'],
                    'subcategories' => [],
                ];
            }

            if (!empty($row['sub_id'])) {
                $categories[$categoryId]['subcategories'][] = [
                    'id' => (int) $row['sub_id'],
                    'name' => $row['sub_name'],
                    'slug' => $row['sub_slug'],
                ];
            }
        }

        mysqli_stmt_close($stmt);
        return $this->mergeFallbackCategories(array_values($categories));
    }

    private function mergeFallbackCategories(array $dbCategories) {
        $categoryMap = [];
        foreach ($dbCategories as $category) {
            $categoryMap[$category['slug']] = $category;
        }

        foreach ($this->fallbackCategories as $fallbackCategory) {
            if (!isset($categoryMap[$fallbackCategory['slug']])) {
                $categoryMap[$fallbackCategory['slug']] = $fallbackCategory;
                continue;
            }

            $existingCategory = &$categoryMap[$fallbackCategory['slug']];
            $existingSubcategories = [];
            foreach ($existingCategory['subcategories'] as $subcategory) {
                $existingSubcategories[$subcategory['slug']] = true;
            }

            foreach ($fallbackCategory['subcategories'] as $fallbackSubcategory) {
                if (!isset($existingSubcategories[$fallbackSubcategory['slug']])) {
                    $existingCategory['subcategories'][] = $fallbackSubcategory;
                }
            }
            unset($existingCategory);
        }

        $merged = array_values($categoryMap);
        usort($merged, function ($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        foreach ($merged as &$category) {
            if (!empty($category['subcategories'])) {
                usort($category['subcategories'], function ($a, $b) {
                    return strcasecmp($a['name'], $b['name']);
                });
            }
        }
        unset($category);

        return $merged;
    }

    public function getCategoryBySlug($slug) {
        if (!$this->hasSchema || $slug === '') {
            return $this->findCategoryBySlug($slug);
        }

        $stmt = $this->db->prepare('SELECT id, name, slug, description, icon, image FROM categories WHERE slug = ? AND is_active = 1 LIMIT 1');
        if (!$stmt) {
            return $this->findCategoryBySlug($slug);
        }

        mysqli_stmt_bind_param($stmt, 's', $slug);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $category = mysqli_fetch_assoc($result) ?: null;
        mysqli_stmt_close($stmt);
        return $category ?: $this->findCategoryBySlug($slug);
    }

    public function getSubcategoryBySlug($slug) {
        if ($slug === '') {
            return null;
        }

        if (!$this->hasSchema) {
            return $this->findSubcategoryBySlug($slug);
        }

        $stmt = $this->db->prepare(
            'SELECT s.id, s.name, s.slug, s.description, s.sub_category_id, s.category_id, c.name AS category_name, c.slug AS category_slug FROM sub_categories s INNER JOIN categories c ON c.id = s.category_id WHERE s.slug = ? AND s.is_active = 1 AND c.is_active = 1 LIMIT 1'
        );
        if (!$stmt) {
            return $this->findSubcategoryBySlug($slug);
        }

        mysqli_stmt_bind_param($stmt, 's', $slug);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $subcategory = mysqli_fetch_assoc($result) ?: null;
        mysqli_stmt_close($stmt);
        return $subcategory ?: $this->findSubcategoryBySlug($slug);
    }

    public function getChildCategories($subcategoryId) {
        if (!$this->hasSchema || empty($subcategoryId)) {
            return [];
        }

        $stmt = $this->db->prepare('SELECT id, name, slug FROM child_categories WHERE sub_category_id = ? AND is_active = 1 ORDER BY name ASC');
        if (!$stmt) {
            return [];
        }

        mysqli_stmt_bind_param($stmt, 'i', $subcategoryId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $children = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);
        return $children;
    }

    public function getProductsByCategory($categoryId = null, $subId = null, $childId = null) {
        if (empty($categoryId)) {
            return $this->getAllProducts();
        }

        if ($this->hasSchema && $this->hasColumn('apadd', 'category_id')) {
            $sql = 'SELECT p.* FROM apadd p WHERE p.category_id = ?';
            $types = 'i';
            $params = [$categoryId];

            if (!empty($subId)) {
                $sql .= ' AND p.sub_category_id = ?';
                $types .= 'i';
                $params[] = $subId;
            }

            if (!empty($childId)) {
                $sql .= ' AND p.child_category_id = ?';
                $types .= 'i';
                $params[] = $childId;
            }

            $sql .= ' ORDER BY p.Apid DESC';
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                return [];
            }

            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $products = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);
            return $products;
        }

        $category = $this->getCategoryById($categoryId);
        if (!$category || empty($category['name'])) {
            return [];
        }

        $sql = 'SELECT * FROM apadd WHERE apcategory = ? ORDER BY Apid DESC';
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }

        mysqli_stmt_bind_param($stmt, 's', $category['name']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $products = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);
        return $products;
    }

    public function getAllProducts() {
        $stmt = $this->db->prepare('SELECT * FROM apadd ORDER BY Apid DESC LIMIT 10');
        if (!$stmt) {
            return [];
        }

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $products = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);
        return $products;
    }

    public function getCategoryById($categoryId) {
        if (!$this->hasSchema || empty($categoryId)) {
            foreach ($this->fallbackCategories as $category) {
                if ($category['id'] === $categoryId) {
                    return $category;
                }
            }
            return null;
        }

        $stmt = $this->db->prepare('SELECT id, name, slug, description, icon, image FROM categories WHERE id = ? LIMIT 1');
        if (!$stmt) {
            return null;
        }

        mysqli_stmt_bind_param($stmt, 'i', $categoryId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $category = mysqli_fetch_assoc($result) ?: null;
        mysqli_stmt_close($stmt);
        return $category;
    }

    private function hasColumn($table, $column) {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
        );
        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param($stmt, 'ss', $table, $column);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $count);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        return $count > 0;
    }

    private function tableExists($table) {
        if (!$this->db) {
            return false;
        }

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param($stmt, 's', $table);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $count);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        return $count > 0;
    }

    private function findCategoryBySlug($slug) {
        if ($slug === '') {
            return null;
        }

        foreach ($this->fallbackCategories as $category) {
            if ($category['slug'] === $slug) {
                return $category;
            }
        }

        return null;
    }

    private function findSubcategoryBySlug($slug) {
        if ($slug === '') {
            return null;
        }

        foreach ($this->fallbackCategories as $category) {
            foreach ($category['subcategories'] as $subcategory) {
                if ($subcategory['slug'] === $slug) {
                    return array_merge($subcategory, [
                        'category_id' => $category['id'],
                        'category_name' => $category['name'],
                        'category_slug' => $category['slug'],
                    ]);
                }
            }
        }

        return null;
    }
}
