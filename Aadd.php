<?php
require_once 'init.php';
require_once 'helpers/ProductImageHelper.php';

$redirectUrl = 'validation.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $redirectUrl);
    exit;
}

if (empty($_SESSION['admin_logged_in']) || empty($_SESSION['admin_id'])) {
    $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Please login as admin to add products.'];
    header('Location: auth.php?role=admin&mode=login');
    exit;
}

if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid form token. Please try again.'];
    header('Location: ' . $redirectUrl);
    exit;
}

$apname = trim($_POST['apname'] ?? '');
$apbrand = trim($_POST['apbrand'] ?? '');
$apcategory = trim($_POST['apcategory'] ?? '');
$apsubcategory = trim($_POST['apsubcategory'] ?? '');
$apqty = isset($_POST['apqty']) ? (int) $_POST['apqty'] : 0;
$apprice = isset($_POST['apprice']) ? (float) $_POST['apprice'] : 0.0;
$apdescription = trim($_POST['apdescription'] ?? '');
$errors = [];

if ($apsubcategory !== '') {
    $apcategory .= ' > ' . $apsubcategory;
}

if ($apname === '') {
    $errors[] = 'Product name is required.';
}
if ($apbrand === '') {
    $errors[] = 'Brand is required.';
}
if ($apcategory === '') {
    $errors[] = 'Category is required.';
}
if ($apqty < 0) {
    $errors[] = 'Quantity cannot be negative.';
}
if ($apprice <= 0) {
    $errors[] = 'Price must be greater than zero.';
}
if ($apdescription === '') {
    $errors[] = 'Product description is required.';
}
$hasUploadedImage = !empty($_FILES['apimage']['name']) && is_array($_FILES['apimage']['name']) && count(array_filter($_FILES['apimage']['name'])) > 0;
if (!$hasUploadedImage) {
    $errors[] = 'At least one product image is required.';
}

if (!empty($errors)) {
    $_SESSION['toast'] = ['type' => 'danger', 'message' => implode(' ', $errors)];
    header('Location: ' . $redirectUrl);
    exit;
}

$descriptionColumnExists = false;
$columnStmt = mysqli_prepare($conn, 'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
if ($columnStmt) {
    $tableName = 'apadd';
    $columnName = 'apdescription';
    mysqli_stmt_bind_param($columnStmt, 'ss', $tableName, $columnName);
    mysqli_stmt_execute($columnStmt);
    mysqli_stmt_bind_result($columnStmt, $columnCount);
    mysqli_stmt_fetch($columnStmt);
    mysqli_stmt_close($columnStmt);
    $descriptionColumnExists = (int) $columnCount > 0;
}

if (!$descriptionColumnExists) {
    mysqli_query($conn, 'ALTER TABLE apadd ADD COLUMN apdescription TEXT NULL');
}

$mainImagePath = '';
$stmt = mysqli_prepare($conn, 'INSERT INTO apadd (apname, apbrand, apcategory, apqty, apprice, apdescription, apimage) VALUES (?, ?, ?, ?, ?, ?, ?)');
if (!$stmt) {
    $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Unable to prepare product save.'];
    header('Location: ' . $redirectUrl);
    exit;
}

mysqli_stmt_bind_param($stmt, 'sssidss', $apname, $apbrand, $apcategory, $apqty, $apprice, $apdescription, $mainImagePath);

if (!mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Unable to save product. Please try again.'];
    header('Location: ' . $redirectUrl);
    exit;
}

$productId = mysqli_insert_id($conn);
mysqli_stmt_close($stmt);

$uploadedPaths = [];
$imageHelper = new ProductImageHelper($conn);
$hasImageWarning = false;

if (!empty($_FILES['apimage']['name']) && is_array($_FILES['apimage']['name'])) {
    foreach ($_FILES['apimage']['name'] as $index => $fileName) {
        if (empty($_FILES['apimage']['tmp_name'][$index]) || $_FILES['apimage']['error'][$index] !== UPLOAD_ERR_OK) {
            continue;
        }

        $fileArray = [
            'name' => $_FILES['apimage']['name'][$index],
            'type' => $_FILES['apimage']['type'][$index],
            'tmp_name' => $_FILES['apimage']['tmp_name'][$index],
            'error' => $_FILES['apimage']['error'][$index],
            'size' => $_FILES['apimage']['size'][$index],
        ];

        try {
            $uploadedPaths[] = $imageHelper->uploadImageFile($productId, $fileArray, $index === 0, $apname);
        } catch (Exception $ex) {
            $_SESSION['toast'] = ['type' => 'warning', 'message' => 'Product was saved, but one or more images could not be uploaded: ' . $ex->getMessage()];
            $hasImageWarning = true;
            break;
        }
    }
}

if (!empty($uploadedPaths)) {
    $mainImagePath = $uploadedPaths[0];
    $updateStmt = mysqli_prepare($conn, 'UPDATE apadd SET apimage = ? WHERE Apid = ?');
    if ($updateStmt) {
        mysqli_stmt_bind_param($updateStmt, 'si', $mainImagePath, $productId);
        mysqli_stmt_execute($updateStmt);
        mysqli_stmt_close($updateStmt);
    }
}

if (!$hasImageWarning) {
    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Product added successfully.'];
}

SecurityHelper::logActivity($conn, 'create', 'product', $productId, $_SESSION['admin_id'] ?? null, 'admin');

header('Location: admin_products.php');
exit;
