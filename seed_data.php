<?php
mysqli_report(MYSQLI_REPORT_OFF);

function connect($dbname) {
    $configs = [
        ['root', '', $dbname],
        ['root', 'Yogeshpo7@', $dbname],
    ];

    foreach ($configs as $config) {
        $conn = @mysqli_connect('localhost', $config[0], $config[1], $config[2]);
        if ($conn) {
            mysqli_set_charset($conn, 'utf8');
            return $conn;
        }
    }

    return false;
}

$connEcommerce = connect('ecommerce');
$connRetailer = connect('retailler');

if (!$connEcommerce || !$connRetailer) {
    die("Unable to connect to the databases. Please verify MySQL is running.\n");
}

$sampleProducts = [
    ['Classic Earrings', 'Luxora', 'Accessories', 30, 799.00, 'images/products/accessories.jpg'],
    ['Silver Necklace', 'Glow', 'Accessories', 28, 899.00, 'images/products/accessories1.jpg'],
    ['True Wireless Earbuds', 'AudioX', 'Accessories', 20, 1499.00, 'images/products/accessories2.jpg'],
    ['Smartphone Case', 'CoverPro', 'Accessories', 40, 499.00, 'images/products/accessories3.jpg'],
    ['Fitness Band', 'HealthFit', 'Accessories', 22, 1299.00, 'images/products/accessories4.jpg'],
    ['Sun Glasses', 'ShadeX', 'Accessories', 26, 699.00, 'images/products/accessories5.jpg'],
    ['Leather Wallet', 'Urban', 'Accessories', 34, 599.00, 'images/products/accessories6.jpg'],
    ['Denim Jacket', 'Trendz', 'Clothes', 18, 2199.00, 'images/products/clothes.jpg'],
    ['Summer Dress', 'Flora', 'Clothes', 20, 1799.00, 'images/products/clothes1.jpg'],
    ['Formal Shirt', 'SuitUp', 'Clothes', 24, 1599.00, 'images/products/clothes2.jpg'],
    ['Casual T-Shirt', 'Everyday', 'Clothes', 42, 799.00, 'images/products/clothes3.jpg'],
    ['Jogger Pants', 'FlexFit', 'Clothes', 30, 1399.00, 'images/products/clothes4.jpg'],
    ['Evening Gown', 'Elegante', 'Clothes', 12, 2599.00, 'images/products/clothes5.jpg'],
    ['Hoodie Sweatshirt', 'CozyWear', 'Clothes', 28, 1299.00, 'images/products/clothes6.jpg'],
    ['Running Sneakers', 'Sprint', 'Footwear', 22, 1999.00, 'images/products/footwear.jpg'],
    ['Leather Boots', 'Outlander', 'Footwear', 16, 2499.00, 'images/products/footwear1.jpg'],
    ['Canvas Slip-ons', 'EasyWalk', 'Footwear', 30, 1399.00, 'images/products/footwear2.jpg'],
    ['Comfort Sandals', 'SunStep', 'Footwear', 26, 1199.00, 'images/products/footwear3.jpg'],
    ['Trail Runners', 'Mountain', 'Footwear', 18, 2299.00, 'images/products/footwear4.jpg'],
    ['Sports Shoes', 'Active', 'Footwear', 20, 1699.00, 'images/products/footwear5.jpg'],
    ['Classic Loafers', 'Gentlemen', 'Footwear', 14, 1899.00, 'images/products/footwear6.jpg'],
    ['Smart TV', 'VisionX', 'Appliances', 10, 45999.00, 'images/products/eappliance.jpg'],
    ['Air Fryer', 'KitchenPro', 'Appliances', 16, 7999.00, 'images/products/eappliance1.jpg'],
    ['Microwave Oven', 'HeatWave', 'Appliances', 12, 9999.00, 'images/products/eappliance2.jpg'],
    ['Mixer Grinder', 'PowerChef', 'Appliances', 14, 5499.00, 'images/products/eappliance3.jpg'],
    ['Coffee Maker', 'BrewMaster', 'Appliances', 15, 6499.00, 'images/products/eappliance4.jpg'],
    ['Refrigerator', 'CoolHome', 'Appliances', 8, 29999.00, 'images/products/eappliance5.jpg'],
    ['Washing Machine', 'CleanMax', 'Appliances', 9, 25999.00, 'images/products/eappliance6.jpg'],
    ['Party Dress', 'Glamour', 'Clothes', 14, 2699.00, 'images/products/Dress1.jpg'],
];

mysqli_query($connEcommerce, 'DELETE FROM apadd');
foreach ($sampleProducts as $product) {
    $name = mysqli_real_escape_string($connEcommerce, $product[0]);
    $brand = mysqli_real_escape_string($connEcommerce, $product[1]);
    $category = mysqli_real_escape_string($connEcommerce, $product[2]);
    $qty = (int)$product[3];
    $price = (float)$product[4];
    $image = mysqli_real_escape_string($connEcommerce, $product[5]);
    mysqli_query($connEcommerce, "INSERT INTO apadd (apname, apbrand, apcategory, apqty, apprice, apimage) VALUES ('$name', '$brand', '$category', $qty, $price, '$image')");
}

$adminCheck = mysqli_query($connEcommerce, 'SELECT COUNT(*) AS count_rows FROM aregister');
if ($adminCheck) {
    $row = mysqli_fetch_assoc($adminCheck);
    if ((int)$row['count_rows'] === 0) {
        mysqli_query($connEcommerce, "INSERT INTO aregister (aname, aadd, apass) VALUES ('admin', 'Main Office', 'admin123')");
    }
}

$customerCheck = mysqli_query($connEcommerce, 'SELECT COUNT(*) AS count_rows FROM cregister');
if ($customerCheck) {
    $row = mysqli_fetch_assoc($customerCheck);
    if ((int)$row['count_rows'] === 0) {
        mysqli_query($connEcommerce, "INSERT INTO cregister (Cname, Cemail, Cadd, Ccontact, Cpass, Cconpass) VALUES ('John Doe', 'john@example.com', 'Mumbai', '9876543210', 'pass123', 'pass123')");
    }
}

$retailerCheck = mysqli_query($connRetailer, 'SELECT COUNT(*) AS count_rows FROM rpadd');
if ($retailerCheck) {
    $row = mysqli_fetch_assoc($retailerCheck);
    if ((int)$row['count_rows'] === 0) {
        mysqli_query($connRetailer, "INSERT INTO rpadd (Rpname, Rpbrand, Rpqty, Rpprice) VALUES ('Travel Backpack', 'BackPack Co.', 12, 1599.00)");
        mysqli_query($connRetailer, "INSERT INTO rpadd (Rpname, Rpbrand, Rpqty, Rpprice) VALUES ('Smart Watch', 'TimeTech', 8, 2899.00)");
    }
}

$retailerUserCheck = mysqli_query($connRetailer, 'SELECT COUNT(*) AS count_rows FROM rregister');
if ($retailerUserCheck) {
    $row = mysqli_fetch_assoc($retailerUserCheck);
    if ((int)$row['count_rows'] === 0) {
        mysqli_query($connRetailer, "INSERT INTO rregister (rname, radd, rpass, rconpass) VALUES ('retailer', 'Delhi', 'retail123', 'retail123')");
    }
}

$orderCheck = mysqli_query($connEcommerce, 'SELECT COUNT(*) AS count_rows FROM orders');
if ($orderCheck) {
    $row = mysqli_fetch_assoc($orderCheck);
    if ((int)$row['count_rows'] === 0) {
        mysqli_query($connEcommerce, "INSERT INTO orders (pid, cid, cost, source, destination) VALUES (1, 1, 2499.00, 'Mumbai', 'Pune')");
    }
}

mysqli_close($connEcommerce);
mysqli_close($connRetailer);

echo "Sample data inserted successfully.\n";
