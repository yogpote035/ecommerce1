-- Global ecommerce schema and sample data
-- Generated from backup file for one-step database setup

CREATE DATABASE IF NOT EXISTS `ecommerce` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `ecommerce`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `user_type` enum('customer','admin','retailer') DEFAULT 'customer',
  `action` varchar(255) DEFAULT NULL,
  `entity_type` varchar(100) DEFAULT NULL,
  `entity_id` int DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`created_at`),
  KEY `user_type` (`user_type`,`action`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `activity_logs` (`id`, `user_id`, `user_type`, `action`, `entity_type`, `entity_id`, `ip_address`, `user_agent`, `created_at`) VALUES ('1', NULL, 'admin', 'automatic_backup', 'backup', NULL, 'UNKNOWN', 'UNKNOWN', '2026-07-01 10:16:40');
INSERT INTO `activity_logs` (`id`, `user_id`, `user_type`, `action`, `entity_type`, `entity_id`, `ip_address`, `user_agent`, `created_at`) VALUES ('2', NULL, 'admin', 'automatic_backup', 'backup', NULL, 'UNKNOWN', 'UNKNOWN', '2026-07-01 10:17:19');
INSERT INTO `activity_logs` (`id`, `user_id`, `user_type`, `action`, `entity_type`, `entity_id`, `ip_address`, `user_agent`, `created_at`) VALUES ('3', '2', 'customer', 'login', 'auth', '2', 'UNKNOWN', 'UNKNOWN', '2026-07-01 10:43:55');
INSERT INTO `activity_logs` (`id`, `user_id`, `user_type`, `action`, `entity_type`, `entity_id`, `ip_address`, `user_agent`, `created_at`) VALUES ('4', '2', 'customer', 'wishlist_add', 'product', '33', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-01 11:17:16');

DROP TABLE IF EXISTS `apadd`;
CREATE TABLE `apadd` (
  `Apid` int NOT NULL AUTO_INCREMENT,
  `apname` varchar(100) NOT NULL,
  `apbrand` varchar(100) DEFAULT NULL,
  `apcategory` varchar(100) DEFAULT NULL,
  `apqty` int DEFAULT '0',
  `apprice` decimal(10,2) DEFAULT '0.00',
  `apdescription` text,
  `apimage` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`Apid`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('1', 'Classic Earrings', 'Luxora', 'Accessories', '30', '799.00', NULL, 'images/products/accessories.jpg');
INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('2', 'Silver Necklace', 'Glow', 'Accessories', '28', '899.00', NULL, 'images/products/accessories1.jpg');
INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('3', 'True Wireless Earbuds', 'AudioX', 'Accessories', '20', '1499.00', NULL, 'images/products/accessories2.jpg');
INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('4', 'Smartphone Case', 'CoverPro', 'Accessories', '40', '499.00', NULL, 'images/products/accessories3.jpg');
INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('5', 'Fitness Band', 'HealthFit', 'Accessories', '22', '1299.00', NULL, 'images/products/accessories4.jpg');
INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('6', 'Sun Glasses', 'ShadeX', 'Accessories', '26', '699.00', NULL, 'images/products/accessories5.jpg');
INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('7', 'Leather Wallet', 'Urban', 'Accessories', '34', '599.00', NULL, 'images/products/accessories6.jpg');
INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('8', 'Denim Jacket', 'Trendz', 'Clothes', '18', '2199.00', NULL, 'images/products/clothes.jpg');
INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('9', 'Summer Dress', 'Flora', 'Clothes', '20', '1799.00', NULL, 'images/products/clothes1.jpg');
INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('10', 'Formal Shirt', 'SuitUp', 'Clothes', '24', '1599.00', NULL, 'images/products/clothes2.jpg');
INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('11', 'Casual T-Shirt', 'Everyday', 'Clothes', '42', '799.00', NULL, 'images/products/clothes3.jpg');
INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('12', 'Jogger Pants', 'FlexFit', 'Clothes', '30', '1399.00', NULL, 'images/products/clothes4.jpg');
INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('13', 'Evening Gown', 'Elegante', 'Clothes', '12', '2599.00', NULL, 'images/products/clothes5.jpg');
INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('14', 'Hoodie Sweatshirt', 'CozyWear', 'Clothes', '28', '1299.00', NULL, 'images/products/clothes6.jpg');
INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('15', 'Running Sneakers', 'Sprint', 'Footwear', '22', '1999.00', NULL, 'images/products/footwear.jpg');
INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('16', 'Leather Boots', 'Outlander', 'Footwear', '16', '2499.00', NULL, 'images/products/footwear1.jpg');
INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('17', 'Canvas Slip-ons', 'EasyWalk', 'Footwear', '30', '1399.00', NULL, 'images/products/footwear2.jpg');
INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('18', 'Comfort Sandals', 'SunStep', 'Footwear', '26', '1199.00', NULL, 'images/products/footwear3.jpg');
INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('19', 'Trail Runners', 'Mountain', 'Footwear', '18', '2299.00', NULL, 'images/products/footwear4.jpg');
INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('20', 'Sports Shoes', 'Active', 'Footwear', '20', '1699.00', NULL, 'images/products/footwear5.jpg');
INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('21', 'Classic Loafers', 'Gentlemen', 'Footwear', '14', '1899.00', NULL, 'images/products/footwear6.jpg');
INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('22', 'Smart TV', 'VisionX', 'Appliances', '10', '45999.00', NULL, 'images/products/eappliance.jpg');
INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('23', 'Air Fryer', 'KitchenPro', 'Appliances', '16', '7999.00', NULL, 'images/products/eappliance1.jpg');
INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('24', 'Microwave Oven', 'HeatWave', 'Appliances', '12', '9999.00', NULL, 'images/products/eappliance2.jpg');
INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('25', 'Mixer Grinder', 'PowerChef', 'Appliances', '14', '5499.00', NULL, 'images/products/eappliance3.jpg');
INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('26', 'Coffee Maker', 'BrewMaster', 'Appliances', '15', '6499.00', NULL, 'images/products/eappliance4.jpg');
INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('27', 'Refrigerator', 'CoolHome', 'Appliances', '8', '29999.00', NULL, 'images/products/eappliance5.jpg');
INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('28', 'Washing Machine', 'CleanMax', 'Appliances', '9', '25999.00', NULL, 'images/products/eappliance6.jpg');
INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('29', 'Party Dress', 'Glamour', 'Clothes', '14', '2699.00', NULL, 'images/products/Dress1.jpg');
INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('30', 'College Bag', 'Sj Enterprises ', 'Bags', '10', '300.00', NULL, 'uploads/stylish-backpack-boys-girls-office-college-travel-sj-enterprises-original-imah9m4jpusugbhp.webp');
INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('31', 'College Bag Test', 'Sj Enterprises', 'Bags > College Bags', '20', '400.00', 'new', 'uploads/stylish-backpack-boys-girls-office-college-travel-sj-enterprises-original-imah9m4jpusugbhp.webp');
INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('32', 'Sample Test Multiple imgs', 'Tata', 'Appliances', '5', '100.00', NULL, 'uploads/products/1782803291_4_1.jpg');
INSERT INTO `apadd` (`Apid`, `apname`, `apbrand`, `apcategory`, `apqty`, `apprice`, `apdescription`, `apimage`) VALUES ('33', 'Test Description', 'Sj Enterprises', 'Bags > College Bags', '50', '300.00', 'test descrip after copiot edit', 'uploads/products/1782829682_wallpapersden.com_windows-12-concept_7680x4320.jpg');

DROP TABLE IF EXISTS `aregister`;
CREATE TABLE `aregister` (
  `aid` int NOT NULL AUTO_INCREMENT,
  `aname` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `aadd` varchar(255) DEFAULT NULL,
  `apass` varchar(255) NOT NULL,
  PRIMARY KEY (`aid`),
  UNIQUE KEY `email` (`email`),
  KEY `email_2` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `aregister` (`aid`, `aname`, `email`, `aadd`, `apass`) VALUES ('1', 'admin', 'admin@gmail.com', 'Main Office', 'admin123');
INSERT INTO `aregister` (`aid`, `aname`, `email`, `aadd`, `apass`) VALUES ('2', 'Yogesh Dattatray pote', 'yogpote035@gmail.com', 'Shree Balaji PG New / Amber Apartments', '$2y$12$yYd.RsIfHFyxWG20TltMb.zwj0AfNO0g0nBhehfOECQz2.f0ohGxi');

DROP TABLE IF EXISTS `backup_logs`;
CREATE TABLE `backup_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `backup_file` varchar(255) NOT NULL,
  `backup_size` bigint DEFAULT NULL,
  `backup_type` enum('manual','automatic','restore') DEFAULT 'manual',
  `status` enum('success','failed') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`id`),
  KEY `created_at` (`created_at`,`backup_type`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `backup_logs` (`id`, `backup_file`, `backup_size`, `backup_type`, `status`, `created_at`, `created_by`, `notes`) VALUES ('1', 'auto_backup_20260701_064640.sql', '68140', 'automatic', 'success', '2026-07-01 10:16:40', NULL, 'Created by auto-backup.php CLI runner');
INSERT INTO `backup_logs` (`id`, `backup_file`, `backup_size`, `backup_type`, `status`, `created_at`, `created_by`, `notes`) VALUES ('2', 'auto_backup_20260701_064719.sql', '68670', 'automatic', 'success', '2026-07-01 10:17:19', NULL, 'Created by auto-backup.php CLI runner');
INSERT INTO `backup_logs` (`id`, `backup_file`, `backup_size`, `backup_type`, `status`, `created_at`, `created_by`, `notes`) VALUES ('3', 'backup-ecommerce-20260701-092455.sql', '79929', 'manual', 'success', '2026-07-01 12:54:55', '2', 'Manual backup created.');

DROP TABLE IF EXISTS `cart_items`;
CREATE TABLE `cart_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_customer_product` (`customer_id`,`product_id`),
  KEY `idx_customer_id` (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`),
  KEY `slug_2` (`slug`,`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`, `image`, `is_active`, `created_at`, `updated_at`) VALUES ('1', 'Clothes', 'clothes', '­ƒæò', 'Clothing and apparel', NULL, '1', '2026-06-29 12:59:39', '2026-06-29 12:59:39');
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`, `image`, `is_active`, `created_at`, `updated_at`) VALUES ('2', 'Electronics', 'electronics', '­ƒô▒', 'Electronic devices and gadgets', NULL, '1', '2026-06-29 12:59:39', '2026-06-29 12:59:39');
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`, `image`, `is_active`, `created_at`, `updated_at`) VALUES ('3', 'Bags', 'bags', '­ƒæ£', 'Backpacks, handbags, and luggage', NULL, '1', '2026-06-29 12:59:39', '2026-06-29 12:59:39');
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`, `image`, `is_active`, `created_at`, `updated_at`) VALUES ('4', 'Accessories', 'accessories', 'ÔîÜ', 'Fashion and tech accessories', NULL, '1', '2026-06-29 12:59:39', '2026-06-29 12:59:39');
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`, `image`, `is_active`, `created_at`, `updated_at`) VALUES ('5', 'Footwear', 'footwear', '­ƒæƒ', 'Shoes, sneakers, and sandals', NULL, '1', '2026-06-29 12:59:39', '2026-06-29 12:59:39');

DROP TABLE IF EXISTS `child_categories`;
CREATE TABLE `child_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sub_category_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sub_category_id` (`sub_category_id`,`slug`),
  KEY `is_active` (`is_active`),
  CONSTRAINT `child_categories_ibfk_1` FOREIGN KEY (`sub_category_id`) REFERENCES `sub_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `child_categories` (`id`, `sub_category_id`, `name`, `slug`, `description`, `is_active`, `created_at`, `updated_at`) VALUES ('1', '1', 'Topwear', 'topwear', NULL, '1', '2026-06-29 12:59:39', '2026-06-29 12:59:39');
INSERT INTO `child_categories` (`id`, `sub_category_id`, `name`, `slug`, `description`, `is_active`, `created_at`, `updated_at`) VALUES ('2', '1', 'Bottomwear', 'bottomwear', NULL, '1', '2026-06-29 12:59:39', '2026-06-29 12:59:39');
INSERT INTO `child_categories` (`id`, `sub_category_id`, `name`, `slug`, `description`, `is_active`, `created_at`, `updated_at`) VALUES ('3', '1', 'Footwear', 'footwear', NULL, '1', '2026-06-29 12:59:39', '2026-06-29 12:59:39');
INSERT INTO `child_categories` (`id`, `sub_category_id`, `name`, `slug`, `description`, `is_active`, `created_at`, `updated_at`) VALUES ('4', '1', 'Accessories', 'accessories', NULL, '1', '2026-06-29 12:59:39', '2026-06-29 12:59:39');

DROP TABLE IF EXISTS `cregister`;
CREATE TABLE `cregister` (
  `Cid` int NOT NULL AUTO_INCREMENT,
  `Cname` varchar(100) NOT NULL,
  `Cemail` varchar(150) DEFAULT NULL,
  `Cadd` varchar(255) DEFAULT NULL,
  `Ccontact` varchar(20) DEFAULT NULL,
  `Cpass` varchar(255) NOT NULL,
  `Cconpass` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`Cid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `cregister` (`Cid`, `Cname`, `Cemail`, `Cadd`, `Ccontact`, `Cpass`, `Cconpass`) VALUES ('1', 'John Doe', 'john@example.com', 'Mumbai', '9876543210', 'pass123', 'pass123');
INSERT INTO `cregister` (`Cid`, `Cname`, `Cemail`, `Cadd`, `Ccontact`, `Cpass`, `Cconpass`) VALUES ('2', 'Yogesh Dattatray pote', 'yogpote035@gmail.com', 'Shree Balaji PG New / Amber Apartments', '8999390368', '$2y$12$AGno8lPiQlzPtlfbNFR56O1i1b43lT8.d2OeuJNLptw.I1Ezr/Tfa', '$2y$12$AGno8lPiQlzPtlfbNFR56O1i1b43lT8.d2OeuJNLptw.I1Ezr/Tfa');

DROP TABLE IF EXISTS `error_logs`;
CREATE TABLE `error_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `error_message` text,
  `error_code` varchar(50) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `line_number` int DEFAULT NULL,
  `trace` text,
  `severity` enum('info','warning','error','critical') DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `severity` (`severity`,`created_at`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `order_status_log`;
CREATE TABLE `order_status_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `status` enum('Pending','Confirmed','Packed','Shipped','Out For Delivery','Delivered','Cancelled') DEFAULT 'Pending',
  `notes` text,
  `updated_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`,`status`),
  CONSTRAINT `order_status_log_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`oid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('6', '10', 'Pending', 'Order created', NULL, '2026-06-30 09:40:35');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('7', '11', 'Pending', 'Order created', NULL, '2026-06-30 09:42:32');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('8', '12', 'Pending', 'Order created', NULL, '2026-06-30 09:43:03');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('9', '13', 'Pending', 'Order created', NULL, '2026-06-30 09:45:41');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('10', '14', 'Pending', 'Order created', NULL, '2026-06-30 09:47:04');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('11', '15', 'Pending', 'Order created', NULL, '2026-06-30 09:47:33');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('12', '16', 'Pending', 'Order created', NULL, '2026-06-30 10:06:23');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('13', '17', 'Pending', 'Order created', NULL, '2026-06-30 10:08:24');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('14', '18', 'Pending', 'Order created', NULL, '2026-06-30 10:09:14');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('15', '19', 'Pending', 'Order created', NULL, '2026-06-30 10:10:21');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('16', '20', 'Pending', 'Order created', NULL, '2026-06-30 10:16:38');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('17', '21', 'Pending', 'Order created', NULL, '2026-06-30 10:19:54');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('20', '24', 'Pending', 'Order created', NULL, '2026-06-30 10:33:14');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('23', '27', 'Pending', 'Order created', NULL, '2026-06-30 10:39:22');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('24', '28', 'Pending', 'Order created', NULL, '2026-06-30 11:02:58');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('25', '29', 'Pending', 'Order created', NULL, '2026-06-30 11:06:22');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('26', '30', 'Pending', 'Order created', NULL, '2026-06-30 11:10:50');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('27', '31', 'Pending', 'Order created', NULL, '2026-06-30 11:39:36');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('28', '32', 'Pending', 'Order created', NULL, '2026-06-30 11:44:19');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('29', '33', 'Pending', 'Order created', NULL, '2026-06-30 11:46:02');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('30', '34', 'Pending', 'Order created', NULL, '2026-06-30 11:46:15');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('31', '35', 'Pending', 'Order created', NULL, '2026-06-30 11:47:09');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('32', '36', 'Pending', 'Order created', NULL, '2026-06-30 12:25:05');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('33', '37', 'Confirmed', 'Order created', NULL, '2026-06-30 12:31:21');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('34', '38', 'Confirmed', 'Order created', NULL, '2026-06-30 14:23:39');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('35', '39', 'Confirmed', 'Order created', NULL, '2026-06-30 21:59:51');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('36', '40', 'Pending', 'Order created', NULL, '2026-06-30 22:50:05');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('37', '41', 'Pending', 'Order created', NULL, '2026-06-30 23:45:24');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('38', '41', 'Confirmed', 'Payment verified successfully', NULL, '2026-06-30 23:47:05');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('39', '42', 'Pending', 'Order created', NULL, '2026-07-01 08:43:31');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('40', '42', 'Confirmed', 'Payment verified successfully', NULL, '2026-07-01 08:44:32');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('41', '43', 'Pending', 'Order created', NULL, '2026-07-01 08:52:24');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('42', '44', 'Pending', 'Order created', NULL, '2026-07-01 10:50:25');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('43', '44', 'Confirmed', 'Payment verified successfully', NULL, '2026-07-01 10:51:01');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('44', '45', 'Pending', 'Order created', NULL, '2026-07-01 11:17:51');
INSERT INTO `order_status_log` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES ('45', '45', 'Confirmed', 'Payment verified successfully', NULL, '2026-07-01 11:18:22');

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `oid` int NOT NULL AUTO_INCREMENT,
  `pid` int DEFAULT NULL,
  `cid` int DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT '0.00',
  `source` varchar(255) DEFAULT NULL,
  `destination` varchar(255) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`oid`),
  KEY `created_at` (`created_at`),
  KEY `idx_orders_customer` (`cid`),
  KEY `idx_orders_created` (`created_at`),
  CONSTRAINT `orders_fk_cid` FOREIGN KEY (`cid`) REFERENCES `cregister` (`Cid`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('1', '1', '1', '2499.00', 'Mumbai', 'Pune', 'Pending', '2026-06-29 12:59:39', '2026-06-29 12:59:39');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('2', '30', '1', '3500.00', 'Customer: Yogesh Dattatray pote | Address: Shree Balaji PG New / Amber Apartments\r\nlane no 11, veerbhadra nagar, Baner | Contact: 08999390368', 'Payment: Cash on Delivery', 'Pending', '2026-06-29 12:59:39', '2026-06-29 12:59:39');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('3', '30', '1', '2800.00', 'Customer: Yogesh Dattatray pote | Address: Shree Balaji PG New / Amber Apartments\r\nlane no 11, veerbhadra nagar, Baner | Contact: 08999390368', 'Payment: Cash on Delivery', 'Pending', '2026-06-29 12:59:39', '2026-06-29 12:59:39');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('4', '29', '2', '13495.00', 'Customer: Yogesh Dattatray pote | Address: Shree Balaji PG New / Amber Apartments\r\nlane no 11, veerbhadra nagar, Baner | Contact: 08999390368', 'Payment: UPI', 'Pending', '2026-06-29 22:15:18', '2026-06-29 22:15:18');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('10', NULL, '2', '400.00', 'Warehouse', 'Kharadi Pune', 'Pending', '2026-06-30 09:40:35', '2026-06-30 09:40:35');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('11', NULL, '2', '400.00', 'Warehouse', 'Kharadi Pune', 'Pending', '2026-06-30 09:42:32', '2026-06-30 09:42:32');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('12', NULL, '2', '400.00', 'Warehouse', 'Shree Balaji PG New / Amber Apartments', 'Pending', '2026-06-30 09:43:03', '2026-06-30 09:43:03');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('13', NULL, '2', '400.00', 'Warehouse', 'Kharadi Pune', 'Pending', '2026-06-30 09:45:41', '2026-06-30 09:45:41');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('14', NULL, '2', '400.00', 'Warehouse', 'greawr', 'Pending', '2026-06-30 09:47:04', '2026-06-30 09:47:04');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('15', NULL, '2', '400.00', 'Warehouse', 'Kharadi Pune', 'Pending', '2026-06-30 09:47:33', '2026-06-30 09:47:33');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('16', NULL, '2', '400.00', 'Warehouse', 'Kharadi Pune', 'Pending', '2026-06-30 10:06:23', '2026-06-30 10:06:23');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('17', NULL, '2', '400.00', 'Warehouse', 'kjhgfds', 'Pending', '2026-06-30 10:08:24', '2026-06-30 10:08:24');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('18', NULL, '2', '400.00', 'Warehouse', 'Kharadi Pune', 'Pending', '2026-06-30 10:09:14', '2026-06-30 10:09:14');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('19', NULL, '2', '400.00', 'Warehouse', 'Kharadi Pune', 'Pending', '2026-06-30 10:10:21', '2026-06-30 10:10:21');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('20', NULL, '2', '400.00', 'Warehouse', 'Kharadi Pune', 'Pending', '2026-06-30 10:16:38', '2026-06-30 10:16:38');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('21', NULL, '2', '400.00', 'Warehouse', 'Kharadi Pune', 'Pending', '2026-06-30 10:19:54', '2026-06-30 10:19:54');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('24', NULL, '2', '400.00', 'Warehouse', 'Kharadi Pune', 'Pending', '2026-06-30 10:33:14', '2026-06-30 10:33:14');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('27', NULL, '2', '400.00', 'Warehouse', 'Kharadi Pune', 'Pending', '2026-06-30 10:39:22', '2026-06-30 10:39:22');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('28', NULL, '2', '800.00', 'Warehouse', 'Kharadi Pune', 'Pending', '2026-06-30 11:02:58', '2026-06-30 11:02:58');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('29', NULL, '2', '800.00', 'Warehouse', 'Kharadi Pune', 'Pending', '2026-06-30 11:06:22', '2026-06-30 11:06:22');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('30', NULL, '2', '300.00', 'Warehouse', 'Kharadi Pune', 'Pending', '2026-06-30 11:10:50', '2026-06-30 11:10:50');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('31', NULL, '2', '400.00', 'Warehouse', 'Kharadi Pune', 'Pending', '2026-06-30 11:39:36', '2026-06-30 11:39:36');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('32', NULL, '2', '400.00', 'Warehouse', 'Kharadi Pune', 'Pending', '2026-06-30 11:44:19', '2026-06-30 11:44:19');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('33', NULL, '2', '400.00', 'Warehouse', 'Kharadi Pune', 'Pending', '2026-06-30 11:46:02', '2026-06-30 11:46:02');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('34', NULL, '2', '400.00', 'Warehouse', 'Kharadi Pune', 'Pending', '2026-06-30 11:46:15', '2026-06-30 11:46:15');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('35', NULL, '2', '400.00', 'Warehouse', 'Kharadi Pune', 'Pending', '2026-06-30 11:47:09', '2026-06-30 11:47:09');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('36', NULL, '2', '400.00', 'Warehouse', 'Kharadi Pune', 'Pending', '2026-06-30 12:25:05', '2026-06-30 12:25:05');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('37', NULL, '2', '13495.00', 'Warehouse', 'Kharadi Pune', 'Pending', '2026-06-30 12:31:21', '2026-06-30 12:31:21');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('38', NULL, '2', '1600.00', 'Warehouse', 'Shree Balaji PG New / Amber Apartments', 'Pending', '2026-06-30 14:23:39', '2026-06-30 14:23:39');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('39', NULL, '2', '1899.00', 'Warehouse', 'Kharadi Pune', 'Pending', '2026-06-30 21:59:51', '2026-06-30 21:59:51');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('40', NULL, '2', '300.00', 'Warehouse', 'Kharadi Pune', 'Pending', '2026-06-30 22:50:05', '2026-06-30 22:50:05');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('41', '33', '2', '300.00', 'Warehouse', 'Baner Gaon', 'Pending', '2026-06-30 23:45:24', '2026-06-30 23:45:24');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('42', '33', '2', '300.00', 'Warehouse', 'Baner Gaon', 'Pending', '2026-07-01 08:43:31', '2026-07-01 08:43:31');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('43', '33', '2', '300.00', 'Warehouse', 'Baner Gaon', 'Pending', '2026-07-01 08:52:24', '2026-07-01 08:52:24');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('44', '29', '2', '2699.00', 'Warehouse', 'Baner Gaon', 'Pending', '2026-07-01 10:50:25', '2026-07-01 10:50:25');
INSERT INTO `orders` (`oid`, `pid`, `cid`, `cost`, `source`, `destination`, `payment_status`, `created_at`, `updated_at`) VALUES ('45', '33', '2', '300.00', 'Warehouse', 'Baner Gaon', 'Pending', '2026-07-01 11:17:51', '2026-07-01 11:17:51');

DROP TABLE IF EXISTS `otp_codes`;
CREATE TABLE `otp_codes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `user_type` enum('customer','admin') DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `attempts` int DEFAULT '0',
  `is_used` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email`,`expires_at`),
  KEY `user_id` (`user_id`,`user_type`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `otp_codes` (`id`, `user_id`, `user_type`, `email`, `otp_code`, `attempts`, `is_used`, `created_at`, `expires_at`) VALUES ('1', NULL, 'customer', 'yogpote035@gmail.com', '869477', '1', '0', '2026-06-30 14:08:00', '2026-06-30 10:43:00');
INSERT INTO `otp_codes` (`id`, `user_id`, `user_type`, `email`, `otp_code`, `attempts`, `is_used`, `created_at`, `expires_at`) VALUES ('2', NULL, 'customer', 'yogpote035@gmail.com', '208497', '1', '0', '2026-06-30 14:09:16', '2026-06-30 10:44:16');
INSERT INTO `otp_codes` (`id`, `user_id`, `user_type`, `email`, `otp_code`, `attempts`, `is_used`, `created_at`, `expires_at`) VALUES ('3', NULL, 'customer', 'yogpote035@gmail.com', '095466', '1', '0', '2026-06-30 14:11:28', '2026-06-30 10:46:28');
INSERT INTO `otp_codes` (`id`, `user_id`, `user_type`, `email`, `otp_code`, `attempts`, `is_used`, `created_at`, `expires_at`) VALUES ('4', NULL, 'customer', 'yogpote035@gmail.com', '622904', '1', '0', '2026-06-30 14:12:46', '2026-06-30 10:47:46');
INSERT INTO `otp_codes` (`id`, `user_id`, `user_type`, `email`, `otp_code`, `attempts`, `is_used`, `created_at`, `expires_at`) VALUES ('5', NULL, 'customer', 'yogpote035@gmail.com', '050880', '1', '0', '2026-06-30 14:13:06', '2026-06-30 10:48:06');
INSERT INTO `otp_codes` (`id`, `user_id`, `user_type`, `email`, `otp_code`, `attempts`, `is_used`, `created_at`, `expires_at`) VALUES ('6', NULL, 'customer', 'yogpote035@gmail.com', '049838', '1', '1', '2026-06-30 14:16:13', '2026-06-30 10:51:13');
INSERT INTO `otp_codes` (`id`, `user_id`, `user_type`, `email`, `otp_code`, `attempts`, `is_used`, `created_at`, `expires_at`) VALUES ('7', NULL, 'customer', 'yogpote035@gmail.com', '827707', '1', '1', '2026-06-30 14:25:27', '2026-06-30 11:00:27');
INSERT INTO `otp_codes` (`id`, `user_id`, `user_type`, `email`, `otp_code`, `attempts`, `is_used`, `created_at`, `expires_at`) VALUES ('8', NULL, 'customer', 'yogpote035@gmail.com', '145248', '0', '1', '2026-07-01 08:30:53', '2026-07-01 05:05:53');

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_type` enum('customer','admin') NOT NULL,
  `email` varchar(255) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `is_used` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_email_type` (`email`,`user_type`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `password_resets` (`id`, `user_type`, `email`, `token_hash`, `is_used`, `created_at`, `expires_at`) VALUES ('1', 'customer', 'yogpote035@gmail.com', '5f1c1b75ccb0ded3f45c9245203f53430629457da9aa9be2892559d81ab7a3c0', '0', '2026-07-01 10:26:11', '2026-07-01 07:26:11');
INSERT INTO `password_resets` (`id`, `user_type`, `email`, `token_hash`, `is_used`, `created_at`, `expires_at`) VALUES ('2', 'customer', 'yogpote035@gmail.com', '99a74941472800c29692557d9d397c8231b367d07f7e2cec2c8dde2596ae0452', '0', '2026-07-01 10:27:10', '2026-07-01 07:27:10');
INSERT INTO `password_resets` (`id`, `user_type`, `email`, `token_hash`, `is_used`, `created_at`, `expires_at`) VALUES ('3', 'customer', 'yogpote035@gmail.com', 'b5a0199a1a2098bd823e2c3878907aa0c80dce88aa52dd2277620e1a86fa43e3', '0', '2026-07-01 12:01:24', '2026-07-01 09:01:24');
INSERT INTO `password_resets` (`id`, `user_type`, `email`, `token_hash`, `is_used`, `created_at`, `expires_at`) VALUES ('4', 'customer', 'yogpote035@gmail.com', 'a2099fc24dc32fedd390718f979340a7621fc5dffcf9177d0b701b70a3962e1d', '0', '2026-07-01 12:03:10', '2026-07-01 09:03:10');
INSERT INTO `password_resets` (`id`, `user_type`, `email`, `token_hash`, `is_used`, `created_at`, `expires_at`) VALUES ('5', 'customer', 'yogpote035@gmail.com', 'fa38345f893c3442a448f7f152023eb116e0097f39345d754eef18caf829b651', '0', '2026-07-01 12:05:31', '2026-07-01 09:05:31');
INSERT INTO `password_resets` (`id`, `user_type`, `email`, `token_hash`, `is_used`, `created_at`, `expires_at`) VALUES ('6', 'customer', 'yogpote035@gmail.com', 'a41b135fd0844d7354cd10fa8e6e05b390e7adbc53dec968e36fe730e03192b9', '1', '2026-07-01 12:12:35', '2026-07-01 09:12:35');

DROP TABLE IF EXISTS `payment_logs`;
CREATE TABLE `payment_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int DEFAULT NULL,
  `payment_id` varchar(255) DEFAULT NULL,
  `event_type` enum('order_created','payment_initiated','payment_verified','webhook_received','payment_failed','refund_initiated') DEFAULT 'payment_initiated',
  `request_data` json DEFAULT NULL,
  `response_data` json DEFAULT NULL,
  `razorpay_response` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `status` enum('success','failed','pending') DEFAULT 'pending',
  `error_message` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `payment_id` (`payment_id`),
  KEY `event_type` (`event_type`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `payment_logs` (`id`, `order_id`, `payment_id`, `event_type`, `request_data`, `response_data`, `razorpay_response`, `ip_address`, `status`, `error_message`, `created_at`) VALUES ('6', '10', 'order_T7hpSmPCERaL5I', 'order_created', '[]', '{\"id\": \"order_T7hpSmPCERaL5I\", \"notes\": {\"order_id\": \"10\", \"customer_email\": \"yogpote035@gmail.com\", \"customer_phone\": \"8999390368\"}, \"amount\": 40000, \"entity\": \"order\", \"status\": \"created\", \"receipt\": \"ORDER-10-1782792635\", \"attempts\": 0, \"currency\": \"INR\", \"offer_id\": null, \"amount_due\": 40000, \"created_at\": 1782792643, \"amount_paid\": 0}', NULL, '::1', 'success', '', '2026-06-30 09:40:36');
INSERT INTO `payment_logs` (`id`, `order_id`, `payment_id`, `event_type`, `request_data`, `response_data`, `razorpay_response`, `ip_address`, `status`, `error_message`, `created_at`) VALUES ('7', '11', 'order_T7hrWFwkAd26yD', 'order_created', '[]', '{\"id\": \"order_T7hrWFwkAd26yD\", \"notes\": {\"order_id\": \"11\", \"customer_email\": \"yogpote035@gmail.com\", \"customer_phone\": \"8999390368\"}, \"amount\": 40000, \"entity\": \"order\", \"status\": \"created\", \"receipt\": \"ORDER-11-1782792752\", \"attempts\": 0, \"currency\": \"INR\", \"offer_id\": null, \"amount_due\": 40000, \"created_at\": 1782792759, \"amount_paid\": 0}', NULL, '::1', 'success', '', '2026-06-30 09:42:32');
INSERT INTO `payment_logs` (`id`, `order_id`, `payment_id`, `event_type`, `request_data`, `response_data`, `razorpay_response`, `ip_address`, `status`, `error_message`, `created_at`) VALUES ('8', '12', 'order_T7hs4SvfZbViYe', 'order_created', '[]', '{\"id\": \"order_T7hs4SvfZbViYe\", \"notes\": {\"order_id\": \"12\", \"customer_email\": \"yogpote035@gmail.com\", \"customer_phone\": \"8999390368\"}, \"amount\": 40000, \"entity\": \"order\", \"status\": \"created\", \"receipt\": \"ORDER-12-1782792783\", \"attempts\": 0, \"currency\": \"INR\", \"offer_id\": null, \"amount_due\": 40000, \"created_at\": 1782792791, \"amount_paid\": 0}', NULL, '::1', 'success', '', '2026-06-30 09:43:04');
INSERT INTO `payment_logs` (`id`, `order_id`, `payment_id`, `event_type`, `request_data`, `response_data`, `razorpay_response`, `ip_address`, `status`, `error_message`, `created_at`) VALUES ('9', '13', 'order_T7huqb53rmiWSq', 'order_created', '[]', '{\"id\": \"order_T7huqb53rmiWSq\", \"notes\": {\"order_id\": \"13\", \"customer_email\": \"yogpote035@gmail.com\", \"customer_phone\": \"8999390368\"}, \"amount\": 40000, \"entity\": \"order\", \"status\": \"created\", \"receipt\": \"ORDER-13-1782792941\", \"attempts\": 0, \"currency\": \"INR\", \"offer_id\": null, \"amount_due\": 40000, \"created_at\": 1782792949, \"amount_paid\": 0}', NULL, '::1', 'success', '', '2026-06-30 09:45:41');
INSERT INTO `payment_logs` (`id`, `order_id`, `payment_id`, `event_type`, `request_data`, `response_data`, `razorpay_response`, `ip_address`, `status`, `error_message`, `created_at`) VALUES ('10', '15', 'order_T7hwpCniXIEQWa', 'order_created', '[]', '{\"id\": \"order_T7hwpCniXIEQWa\", \"notes\": {\"order_id\": \"15\", \"customer_email\": \"yogpote035@gmail.com\", \"customer_phone\": \"8999390368\"}, \"amount\": 40000, \"entity\": \"order\", \"status\": \"created\", \"receipt\": \"ORDER-15-1782793053\", \"attempts\": 0, \"currency\": \"INR\", \"offer_id\": null, \"amount_due\": 40000, \"created_at\": 1782793061, \"amount_paid\": 0}', NULL, '::1', 'success', '', '2026-06-30 09:47:34');
INSERT INTO `payment_logs` (`id`, `order_id`, `payment_id`, `event_type`, `request_data`, `response_data`, `razorpay_response`, `ip_address`, `status`, `error_message`, `created_at`) VALUES ('11', '16', 'order_T7iGi3oJqyQcxw', 'order_created', '[]', '{\"id\": \"order_T7iGi3oJqyQcxw\", \"notes\": {\"order_id\": \"16\", \"customer_email\": \"yogpote035@gmail.com\", \"customer_phone\": \"8999390368\"}, \"amount\": 40000, \"entity\": \"order\", \"status\": \"created\", \"receipt\": \"ORDER-16-1782794183\", \"attempts\": 0, \"currency\": \"INR\", \"offer_id\": null, \"amount_due\": 40000, \"created_at\": 1782794190, \"amount_paid\": 0}', NULL, '::1', 'success', '', '2026-06-30 10:06:23');
INSERT INTO `payment_logs` (`id`, `order_id`, `payment_id`, `event_type`, `request_data`, `response_data`, `razorpay_response`, `ip_address`, `status`, `error_message`, `created_at`) VALUES ('12', '17', 'order_T7iIrC8UAYFJIr', 'order_created', '[]', '{\"id\": \"order_T7iIrC8UAYFJIr\", \"notes\": {\"order_id\": \"17\", \"customer_email\": \"yogpote035@gmail.com\", \"customer_phone\": \"8999390368\"}, \"amount\": 40000, \"entity\": \"order\", \"status\": \"created\", \"receipt\": \"ORDER-17-1782794304\", \"attempts\": 0, \"currency\": \"INR\", \"offer_id\": null, \"amount_due\": 40000, \"created_at\": 1782794312, \"amount_paid\": 0}', NULL, '::1', 'success', '', '2026-06-30 10:08:25');
INSERT INTO `payment_logs` (`id`, `order_id`, `payment_id`, `event_type`, `request_data`, `response_data`, `razorpay_response`, `ip_address`, `status`, `error_message`, `created_at`) VALUES ('13', '18', 'order_T7iJjlk7GOw3iz', 'order_created', '[]', '{\"id\": \"order_T7iJjlk7GOw3iz\", \"notes\": {\"order_id\": \"18\", \"customer_email\": \"yogpote035@gmail.com\", \"customer_phone\": \"8999390368\"}, \"amount\": 40000, \"entity\": \"order\", \"status\": \"created\", \"receipt\": \"ORDER-18-1782794354\", \"attempts\": 0, \"currency\": \"INR\", \"offer_id\": null, \"amount_due\": 40000, \"created_at\": 1782794362, \"amount_paid\": 0}', NULL, '::1', 'success', '', '2026-06-30 10:09:15');
INSERT INTO `payment_logs` (`id`, `order_id`, `payment_id`, `event_type`, `request_data`, `response_data`, `razorpay_response`, `ip_address`, `status`, `error_message`, `created_at`) VALUES ('14', '19', 'order_T7iKun2GscGmRT', 'order_created', '[]', '{\"id\": \"order_T7iKun2GscGmRT\", \"notes\": {\"order_id\": \"19\", \"customer_email\": \"yogpote035@gmail.com\", \"customer_phone\": \"8999390368\"}, \"amount\": 40000, \"entity\": \"order\", \"status\": \"created\", \"receipt\": \"ORDER-19-1782794421\", \"attempts\": 0, \"currency\": \"INR\", \"offer_id\": null, \"amount_due\": 40000, \"created_at\": 1782794429, \"amount_paid\": 0}', NULL, '::1', 'success', '', '2026-06-30 10:10:22');
INSERT INTO `payment_logs` (`id`, `order_id`, `payment_id`, `event_type`, `request_data`, `response_data`, `razorpay_response`, `ip_address`, `status`, `error_message`, `created_at`) VALUES ('15', '20', 'order_T7iRXTl7UIZsgt', 'order_created', '[]', '{\"id\": \"order_T7iRXTl7UIZsgt\", \"notes\": {\"order_id\": \"20\", \"customer_email\": \"yogpote035@gmail.com\", \"customer_phone\": \"8999390368\"}, \"amount\": 40000, \"entity\": \"order\", \"status\": \"created\", \"receipt\": \"ORDER-20-1782794798\", \"attempts\": 0, \"currency\": \"INR\", \"offer_id\": null, \"amount_due\": 40000, \"created_at\": 1782794805, \"amount_paid\": 0}', NULL, '::1', 'success', '', '2026-06-30 10:16:39');
INSERT INTO `payment_logs` (`id`, `order_id`, `payment_id`, `event_type`, `request_data`, `response_data`, `razorpay_response`, `ip_address`, `status`, `error_message`, `created_at`) VALUES ('16', '21', 'order_T7iUzmk4MEAeF2', 'order_created', '[]', '{\"id\": \"order_T7iUzmk4MEAeF2\", \"notes\": {\"order_id\": \"21\", \"customer_email\": \"yogpote035@gmail.com\", \"customer_phone\": \"8999390368\"}, \"amount\": 40000, \"entity\": \"order\", \"status\": \"created\", \"receipt\": \"ORDER-21-1782794994\", \"attempts\": 0, \"currency\": \"INR\", \"offer_id\": null, \"amount_due\": 40000, \"created_at\": 1782795002, \"amount_paid\": 0}', NULL, '::1', 'success', '', '2026-06-30 10:19:55');
INSERT INTO `payment_logs` (`id`, `order_id`, `payment_id`, `event_type`, `request_data`, `response_data`, `razorpay_response`, `ip_address`, `status`, `error_message`, `created_at`) VALUES ('19', '24', 'order_T7ij4i8c64XPsB', 'order_created', '[]', '{\"id\": \"order_T7ij4i8c64XPsB\", \"notes\": {\"order_id\": 24, \"customer_email\": \"yogpote035@gmail.com\", \"customer_phone\": \"8999390368\"}, \"amount\": 40000, \"entity\": \"order\", \"status\": \"created\", \"receipt\": \"ORDER-24-1782795794\", \"attempts\": 0, \"currency\": \"INR\", \"offer_id\": null, \"amount_due\": 40000, \"created_at\": 1782795801, \"amount_paid\": 0}', NULL, '::1', 'success', '', '2026-06-30 10:33:15');
INSERT INTO `payment_logs` (`id`, `order_id`, `payment_id`, `event_type`, `request_data`, `response_data`, `razorpay_response`, `ip_address`, `status`, `error_message`, `created_at`) VALUES ('22', '27', 'order_T7ipZA6KvkPOsQ', 'order_created', '[]', '{\"id\": \"order_T7ipZA6KvkPOsQ\", \"notes\": {\"order_id\": 27, \"customer_email\": \"yogpote035@gmail.com\", \"customer_phone\": \"8999390368\"}, \"amount\": 40000, \"entity\": \"order\", \"status\": \"created\", \"receipt\": \"ORDER-27-1782796162\", \"attempts\": 0, \"currency\": \"INR\", \"offer_id\": null, \"amount_due\": 40000, \"created_at\": 1782796170, \"amount_paid\": 0}', NULL, '::1', 'success', '', '2026-06-30 10:39:23');
INSERT INTO `payment_logs` (`id`, `order_id`, `payment_id`, `event_type`, `request_data`, `response_data`, `razorpay_response`, `ip_address`, `status`, `error_message`, `created_at`) VALUES ('23', '28', 'order_T7jETq4RyESatI', 'order_created', '[]', '{\"id\": \"order_T7jETq4RyESatI\", \"notes\": {\"order_id\": 28, \"customer_email\": \"yogpote035@gmail.com\", \"customer_phone\": \"8999390368\"}, \"amount\": 80000, \"entity\": \"order\", \"status\": \"created\", \"receipt\": \"ORDER-28-1782797578\", \"attempts\": 0, \"currency\": \"INR\", \"offer_id\": null, \"amount_due\": 80000, \"created_at\": 1782797585, \"amount_paid\": 0}', NULL, '::1', 'success', '', '2026-06-30 11:02:58');
INSERT INTO `payment_logs` (`id`, `order_id`, `payment_id`, `event_type`, `request_data`, `response_data`, `razorpay_response`, `ip_address`, `status`, `error_message`, `created_at`) VALUES ('24', '29', 'order_T7jI4Ye1m4GtvT', 'order_created', '[]', '{\"id\": \"order_T7jI4Ye1m4GtvT\", \"notes\": {\"order_id\": 29, \"customer_email\": \"yogpote035@gmail.com\", \"customer_phone\": \"8999390368\"}, \"amount\": 80000, \"entity\": \"order\", \"status\": \"created\", \"receipt\": \"ORDER-29-1782797782\", \"attempts\": 0, \"currency\": \"INR\", \"offer_id\": null, \"amount_due\": 80000, \"created_at\": 1782797789, \"amount_paid\": 0}', NULL, '::1', 'success', '', '2026-06-30 11:06:22');
INSERT INTO `payment_logs` (`id`, `order_id`, `payment_id`, `event_type`, `request_data`, `response_data`, `razorpay_response`, `ip_address`, `status`, `error_message`, `created_at`) VALUES ('25', '30', 'order_T7jMn73IWDTKs6', 'order_created', '[]', '{\"id\": \"order_T7jMn73IWDTKs6\", \"notes\": {\"order_id\": 30, \"customer_email\": \"yogpote035@gmail.com\", \"customer_phone\": \"8999390368\"}, \"amount\": 30000, \"entity\": \"order\", \"status\": \"created\", \"receipt\": \"ORDER-30-1782798050\", \"attempts\": 0, \"currency\": \"INR\", \"offer_id\": null, \"amount_due\": 30000, \"created_at\": 1782798057, \"amount_paid\": 0}', NULL, '::1', 'success', '', '2026-06-30 11:10:50');
INSERT INTO `payment_logs` (`id`, `order_id`, `payment_id`, `event_type`, `request_data`, `response_data`, `razorpay_response`, `ip_address`, `status`, `error_message`, `created_at`) VALUES ('26', '31', 'order_T7jrBAsrjpwM9R', 'order_created', '[]', '{\"id\": \"order_T7jrBAsrjpwM9R\", \"notes\": {\"order_id\": 31, \"customer_email\": \"yogpote035@gmail.com\", \"customer_phone\": \"8999390368\"}, \"amount\": 40000, \"entity\": \"order\", \"status\": \"created\", \"receipt\": \"ORDER-31-1782799776\", \"attempts\": 0, \"currency\": \"INR\", \"offer_id\": null, \"amount_due\": 40000, \"created_at\": 1782799783, \"amount_paid\": 0}', NULL, '::1', 'success', '', '2026-06-30 11:39:36');
INSERT INTO `payment_logs` (`id`, `order_id`, `payment_id`, `event_type`, `request_data`, `response_data`, `razorpay_response`, `ip_address`, `status`, `error_message`, `created_at`) VALUES ('27', '32', 'order_T7jw9sQNVqhFgZ', 'order_created', '[]', '{\"id\": \"order_T7jw9sQNVqhFgZ\", \"notes\": {\"order_id\": 32, \"customer_email\": \"yogpote035@gmail.com\", \"customer_phone\": \"8999390368\"}, \"amount\": 40000, \"entity\": \"order\", \"status\": \"created\", \"receipt\": \"ORDER-32-1782800059\", \"attempts\": 0, \"currency\": \"INR\", \"offer_id\": null, \"amount_due\": 40000, \"created_at\": 1782800066, \"amount_paid\": 0}', NULL, '::1', 'success', '', '2026-06-30 11:44:19');
INSERT INTO `payment_logs` (`id`, `order_id`, `payment_id`, `event_type`, `request_data`, `response_data`, `razorpay_response`, `ip_address`, `status`, `error_message`, `created_at`) VALUES ('28', '37', 'order_T7kjqd93h5I1GY', 'order_created', '[]', '{\"id\": \"order_T7kjqd93h5I1GY\", \"notes\": {\"order_id\": 37, \"customer_email\": \"yogpote035@gmail.com\", \"customer_phone\": \"8999390368\"}, \"amount\": 1349500, \"entity\": \"order\", \"status\": \"created\", \"receipt\": \"ORDER-37-1782802881\", \"attempts\": 0, \"currency\": \"INR\", \"offer_id\": null, \"amount_due\": 1349500, \"created_at\": 1782802889, \"amount_paid\": 0}', NULL, '::1', 'success', '', '2026-06-30 12:31:22');
INSERT INTO `payment_logs` (`id`, `order_id`, `payment_id`, `event_type`, `request_data`, `response_data`, `razorpay_response`, `ip_address`, `status`, `error_message`, `created_at`) VALUES ('29', '37', 'pay_T7kkYA4OtwT3Uy', 'payment_verified', '[]', '{\"status\": \"verified\"}', NULL, '::1', 'success', '', '2026-06-30 12:32:24');
INSERT INTO `payment_logs` (`id`, `order_id`, `payment_id`, `event_type`, `request_data`, `response_data`, `razorpay_response`, `ip_address`, `status`, `error_message`, `created_at`) VALUES ('30', '38', 'order_T7meTykfBxkoBn', 'order_created', '[]', '{\"id\": \"order_T7meTykfBxkoBn\", \"notes\": {\"order_id\": 38, \"customer_email\": \"yogpote035@gmail.com\", \"customer_phone\": \"8999390368\"}, \"amount\": 160000, \"entity\": \"order\", \"status\": \"created\", \"receipt\": \"ORDER-38-1782809619\", \"attempts\": 0, \"currency\": \"INR\", \"offer_id\": null, \"amount_due\": 160000, \"created_at\": 1782809627, \"amount_paid\": 0}', NULL, '::1', 'success', '', '2026-06-30 14:23:40');
INSERT INTO `payment_logs` (`id`, `order_id`, `payment_id`, `event_type`, `request_data`, `response_data`, `razorpay_response`, `ip_address`, `status`, `error_message`, `created_at`) VALUES ('31', '38', 'pay_T7mfENAOD2IByq', 'payment_verified', '[]', '{\"status\": \"verified\"}', NULL, '::1', 'success', '', '2026-06-30 14:24:43');
INSERT INTO `payment_logs` (`id`, `order_id`, `payment_id`, `event_type`, `request_data`, `response_data`, `razorpay_response`, `ip_address`, `status`, `error_message`, `created_at`) VALUES ('32', '39', 'order_T7uQOrVq2keya1', 'order_created', '[]', '{\"id\": \"order_T7uQOrVq2keya1\", \"notes\": {\"order_id\": 39, \"customer_email\": \"yogpote035@gmail.com\", \"customer_phone\": \"8999390368\"}, \"amount\": 189900, \"entity\": \"order\", \"status\": \"created\", \"receipt\": \"ORDER-39-1782836991\", \"attempts\": 0, \"currency\": \"INR\", \"offer_id\": null, \"amount_due\": 189900, \"created_at\": 1782837000, \"amount_paid\": 0}', NULL, '::1', 'success', '', '2026-06-30 21:59:52');
INSERT INTO `payment_logs` (`id`, `order_id`, `payment_id`, `event_type`, `request_data`, `response_data`, `razorpay_response`, `ip_address`, `status`, `error_message`, `created_at`) VALUES ('33', '39', 'pay_T7uQaJaAowJa99', 'payment_verified', '[]', '{\"status\": \"verified\"}', NULL, '::1', 'success', '', '2026-06-30 22:00:21');
INSERT INTO `payment_logs` (`id`, `order_id`, `payment_id`, `event_type`, `request_data`, `response_data`, `razorpay_response`, `ip_address`, `status`, `error_message`, `created_at`) VALUES ('34', '41', 'order_T7wE2Xxz8gf5y9', 'order_created', '[]', '{\"id\": \"order_T7wE2Xxz8gf5y9\", \"notes\": {\"order_id\": 41, \"customer_email\": \"yogpote035@gmail.com\", \"customer_phone\": \"8999390368\"}, \"amount\": 30000, \"entity\": \"order\", \"status\": \"created\", \"receipt\": \"ORDER-41-1782843324\", \"attempts\": 0, \"currency\": \"INR\", \"offer_id\": null, \"amount_due\": 30000, \"created_at\": 1782843341, \"amount_paid\": 0}', NULL, '::1', 'success', '', '2026-06-30 23:45:33');
INSERT INTO `payment_logs` (`id`, `order_id`, `payment_id`, `event_type`, `request_data`, `response_data`, `razorpay_response`, `ip_address`, `status`, `error_message`, `created_at`) VALUES ('35', '41', 'pay_T7wFCpTmFCwe2W', 'payment_verified', '[]', '{\"status\": \"verified\"}', NULL, '::1', 'success', '', '2026-06-30 23:46:59');
INSERT INTO `payment_logs` (`id`, `order_id`, `payment_id`, `event_type`, `request_data`, `response_data`, `razorpay_response`, `ip_address`, `status`, `error_message`, `created_at`) VALUES ('36', '42', 'order_T85OL8Fgw9NiMS', 'order_created', '[]', '{\"id\": \"order_T85OL8Fgw9NiMS\", \"notes\": {\"order_id\": 42, \"customer_email\": \"yogpote035@gmail.com\", \"customer_phone\": \"8999390368\"}, \"amount\": 30000, \"entity\": \"order\", \"status\": \"created\", \"receipt\": \"ORDER-42-1782875611\", \"attempts\": 0, \"currency\": \"INR\", \"offer_id\": null, \"amount_due\": 30000, \"created_at\": 1782875621, \"amount_paid\": 0}', NULL, '::1', 'success', '', '2026-07-01 08:43:31');
INSERT INTO `payment_logs` (`id`, `order_id`, `payment_id`, `event_type`, `request_data`, `response_data`, `razorpay_response`, `ip_address`, `status`, `error_message`, `created_at`) VALUES ('37', '42', 'pay_T85P1XM7woCOjY', 'payment_verified', '[]', '{\"status\": \"verified\"}', NULL, '::1', 'success', '', '2026-07-01 08:44:30');
INSERT INTO `payment_logs` (`id`, `order_id`, `payment_id`, `event_type`, `request_data`, `response_data`, `razorpay_response`, `ip_address`, `status`, `error_message`, `created_at`) VALUES ('38', '44', 'order_T87YQRZnWdICuh', 'order_created', '[]', '{\"id\": \"order_T87YQRZnWdICuh\", \"notes\": {\"order_id\": 44, \"customer_email\": \"yogpote035@gmail.com\", \"customer_phone\": \"8999390368\"}, \"amount\": 269900, \"entity\": \"order\", \"status\": \"created\", \"receipt\": \"ORDER-44-1782883225\", \"attempts\": 0, \"currency\": \"INR\", \"offer_id\": null, \"amount_due\": 269900, \"created_at\": 1782883237, \"amount_paid\": 0}', NULL, '::1', 'success', '', '2026-07-01 10:50:27');
INSERT INTO `payment_logs` (`id`, `order_id`, `payment_id`, `event_type`, `request_data`, `response_data`, `razorpay_response`, `ip_address`, `status`, `error_message`, `created_at`) VALUES ('39', '44', 'pay_T87YbgOfMt6bv0', 'payment_verified', '[]', '{\"status\": \"verified\"}', NULL, '::1', 'success', '', '2026-07-01 10:50:59');
INSERT INTO `payment_logs` (`id`, `order_id`, `payment_id`, `event_type`, `request_data`, `response_data`, `razorpay_response`, `ip_address`, `status`, `error_message`, `created_at`) VALUES ('40', '45', 'order_T881Mw4nDwYej3', 'order_created', '[]', '{\"id\": \"order_T881Mw4nDwYej3\", \"notes\": {\"order_id\": 45, \"customer_email\": \"yogpote035@gmail.com\", \"customer_phone\": \"8999390368\"}, \"amount\": 30000, \"entity\": \"order\", \"status\": \"created\", \"receipt\": \"ORDER-45-1782884871\", \"attempts\": 0, \"currency\": \"INR\", \"offer_id\": null, \"amount_due\": 30000, \"created_at\": 1782884881, \"amount_paid\": 0}', NULL, '::1', 'success', '', '2026-07-01 11:17:51');
INSERT INTO `payment_logs` (`id`, `order_id`, `payment_id`, `event_type`, `request_data`, `response_data`, `razorpay_response`, `ip_address`, `status`, `error_message`, `created_at`) VALUES ('41', '45', 'pay_T881aXGC5LaxYA', 'payment_verified', '[]', '{\"status\": \"verified\"}', NULL, '::1', 'success', '', '2026-07-01 11:18:20');

DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `payment_method` enum('COD','Razorpay','Other') DEFAULT 'COD',
  `payment_id` varchar(255) DEFAULT NULL,
  `transaction_reference` varchar(255) DEFAULT NULL,
  `payment_status` enum('Pending','Paid','Failed','Refunded') DEFAULT 'Pending',
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `notes` text,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`,`payment_status`),
  KEY `payment_id` (`payment_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`oid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_id`, `transaction_reference`, `payment_status`, `amount`, `created_at`, `updated_at`, `notes`) VALUES ('1', '10', 'Razorpay', '', 'order_T7hpSmPCERaL5I', 'Pending', '400.00', '2026-06-30 09:40:36', '2026-06-30 09:40:36', NULL);
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_id`, `transaction_reference`, `payment_status`, `amount`, `created_at`, `updated_at`, `notes`) VALUES ('2', '11', 'Razorpay', '', 'order_T7hrWFwkAd26yD', 'Pending', '400.00', '2026-06-30 09:42:32', '2026-06-30 09:42:32', NULL);
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_id`, `transaction_reference`, `payment_status`, `amount`, `created_at`, `updated_at`, `notes`) VALUES ('3', '12', 'Razorpay', '', 'order_T7hs4SvfZbViYe', 'Pending', '400.00', '2026-06-30 09:43:04', '2026-06-30 09:43:04', NULL);
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_id`, `transaction_reference`, `payment_status`, `amount`, `created_at`, `updated_at`, `notes`) VALUES ('4', '13', 'Razorpay', '', 'order_T7huqb53rmiWSq', 'Pending', '400.00', '2026-06-30 09:45:41', '2026-06-30 09:45:41', NULL);
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_id`, `transaction_reference`, `payment_status`, `amount`, `created_at`, `updated_at`, `notes`) VALUES ('5', '14', 'COD', NULL, NULL, 'Pending', '400.00', '2026-06-30 09:47:04', '2026-06-30 09:47:04', NULL);
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_id`, `transaction_reference`, `payment_status`, `amount`, `created_at`, `updated_at`, `notes`) VALUES ('6', '15', 'Razorpay', '', 'order_T7hwpCniXIEQWa', 'Pending', '400.00', '2026-06-30 09:47:34', '2026-06-30 09:47:34', NULL);
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_id`, `transaction_reference`, `payment_status`, `amount`, `created_at`, `updated_at`, `notes`) VALUES ('7', '16', 'Razorpay', '', 'order_T7iGi3oJqyQcxw', 'Pending', '400.00', '2026-06-30 10:06:24', '2026-06-30 10:06:24', NULL);
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_id`, `transaction_reference`, `payment_status`, `amount`, `created_at`, `updated_at`, `notes`) VALUES ('8', '17', 'Razorpay', '', 'order_T7iIrC8UAYFJIr', 'Pending', '400.00', '2026-06-30 10:08:25', '2026-06-30 10:08:25', NULL);
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_id`, `transaction_reference`, `payment_status`, `amount`, `created_at`, `updated_at`, `notes`) VALUES ('9', '18', 'Razorpay', '', 'order_T7iJjlk7GOw3iz', 'Pending', '400.00', '2026-06-30 10:09:15', '2026-06-30 10:09:15', NULL);
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_id`, `transaction_reference`, `payment_status`, `amount`, `created_at`, `updated_at`, `notes`) VALUES ('10', '19', 'Razorpay', '', 'order_T7iKun2GscGmRT', 'Pending', '400.00', '2026-06-30 10:10:22', '2026-06-30 10:10:22', NULL);
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_id`, `transaction_reference`, `payment_status`, `amount`, `created_at`, `updated_at`, `notes`) VALUES ('11', '20', 'Razorpay', '', 'order_T7iRXTl7UIZsgt', 'Pending', '400.00', '2026-06-30 10:16:39', '2026-06-30 10:16:39', NULL);
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_id`, `transaction_reference`, `payment_status`, `amount`, `created_at`, `updated_at`, `notes`) VALUES ('12', '21', 'Razorpay', '', 'order_T7iUzmk4MEAeF2', 'Pending', '400.00', '2026-06-30 10:19:55', '2026-06-30 10:19:55', NULL);
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_id`, `transaction_reference`, `payment_status`, `amount`, `created_at`, `updated_at`, `notes`) VALUES ('13', '24', 'Razorpay', '', 'order_T7ij4i8c64XPsB', 'Pending', '400.00', '2026-06-30 10:33:15', '2026-06-30 10:33:15', NULL);
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_id`, `transaction_reference`, `payment_status`, `amount`, `created_at`, `updated_at`, `notes`) VALUES ('14', '27', 'Razorpay', '', 'order_T7ipZA6KvkPOsQ', 'Pending', '400.00', '2026-06-30 10:39:23', '2026-06-30 10:39:23', NULL);
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_id`, `transaction_reference`, `payment_status`, `amount`, `created_at`, `updated_at`, `notes`) VALUES ('15', '28', 'Razorpay', '', 'order_T7jETq4RyESatI', 'Pending', '800.00', '2026-06-30 11:02:58', '2026-06-30 11:02:58', NULL);
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_id`, `transaction_reference`, `payment_status`, `amount`, `created_at`, `updated_at`, `notes`) VALUES ('16', '29', 'Razorpay', '', 'order_T7jI4Ye1m4GtvT', 'Pending', '800.00', '2026-06-30 11:06:22', '2026-06-30 11:06:22', NULL);
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_id`, `transaction_reference`, `payment_status`, `amount`, `created_at`, `updated_at`, `notes`) VALUES ('17', '30', 'Razorpay', '', 'order_T7jMn73IWDTKs6', 'Pending', '300.00', '2026-06-30 11:10:50', '2026-06-30 11:10:50', NULL);
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_id`, `transaction_reference`, `payment_status`, `amount`, `created_at`, `updated_at`, `notes`) VALUES ('18', '31', 'Razorpay', '', 'order_T7jrBAsrjpwM9R', 'Pending', '400.00', '2026-06-30 11:39:36', '2026-06-30 11:39:36', NULL);
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_id`, `transaction_reference`, `payment_status`, `amount`, `created_at`, `updated_at`, `notes`) VALUES ('19', '32', 'Razorpay', '', 'order_T7jw9sQNVqhFgZ', 'Pending', '400.00', '2026-06-30 11:44:19', '2026-06-30 11:44:19', NULL);
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_id`, `transaction_reference`, `payment_status`, `amount`, `created_at`, `updated_at`, `notes`) VALUES ('20', '33', 'COD', NULL, NULL, 'Pending', '400.00', '2026-06-30 11:46:02', '2026-06-30 11:46:02', NULL);
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_id`, `transaction_reference`, `payment_status`, `amount`, `created_at`, `updated_at`, `notes`) VALUES ('21', '34', 'COD', NULL, NULL, 'Pending', '400.00', '2026-06-30 11:46:15', '2026-06-30 11:46:15', NULL);
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_id`, `transaction_reference`, `payment_status`, `amount`, `created_at`, `updated_at`, `notes`) VALUES ('22', '35', 'COD', NULL, NULL, 'Pending', '400.00', '2026-06-30 11:47:09', '2026-06-30 11:47:09', NULL);
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_id`, `transaction_reference`, `payment_status`, `amount`, `created_at`, `updated_at`, `notes`) VALUES ('23', '36', 'COD', NULL, NULL, 'Pending', '400.00', '2026-06-30 12:25:05', '2026-06-30 12:25:05', NULL);
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_id`, `transaction_reference`, `payment_status`, `amount`, `created_at`, `updated_at`, `notes`) VALUES ('24', '37', 'Razorpay', 'pay_T7kkYA4OtwT3Uy', 'order_T7kjqd93h5I1GY', 'Paid', '13495.00', '2026-06-30 12:31:22', '2026-06-30 12:32:25', NULL);
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_id`, `transaction_reference`, `payment_status`, `amount`, `created_at`, `updated_at`, `notes`) VALUES ('25', '38', 'Razorpay', 'pay_T7mfENAOD2IByq', 'order_T7meTykfBxkoBn', 'Paid', '1600.00', '2026-06-30 14:23:40', '2026-06-30 14:24:44', NULL);
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_id`, `transaction_reference`, `payment_status`, `amount`, `created_at`, `updated_at`, `notes`) VALUES ('26', '39', 'Razorpay', 'pay_T7uQaJaAowJa99', 'order_T7uQOrVq2keya1', 'Paid', '1899.00', '2026-06-30 21:59:52', '2026-06-30 22:00:22', NULL);
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_id`, `transaction_reference`, `payment_status`, `amount`, `created_at`, `updated_at`, `notes`) VALUES ('27', '40', 'COD', NULL, NULL, 'Pending', '300.00', '2026-06-30 22:50:05', '2026-06-30 22:50:05', NULL);
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_id`, `transaction_reference`, `payment_status`, `amount`, `created_at`, `updated_at`, `notes`) VALUES ('28', '41', 'Razorpay', 'pay_T7wFCpTmFCwe2W', 'order_T7wE2Xxz8gf5y9', 'Paid', '300.00', '2026-06-30 23:45:33', '2026-06-30 23:47:05', NULL);
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_id`, `transaction_reference`, `payment_status`, `amount`, `created_at`, `updated_at`, `notes`) VALUES ('29', '42', 'Razorpay', 'pay_T85P1XM7woCOjY', 'order_T85OL8Fgw9NiMS', 'Paid', '300.00', '2026-07-01 08:43:31', '2026-07-01 08:44:32', NULL);
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_id`, `transaction_reference`, `payment_status`, `amount`, `created_at`, `updated_at`, `notes`) VALUES ('30', '43', 'COD', NULL, NULL, 'Pending', '300.00', '2026-07-01 08:52:24', '2026-07-01 08:52:24', NULL);
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_id`, `transaction_reference`, `payment_status`, `amount`, `created_at`, `updated_at`, `notes`) VALUES ('31', '44', 'Razorpay', 'pay_T87YbgOfMt6bv0', 'order_T87YQRZnWdICuh', 'Paid', '2699.00', '2026-07-01 10:50:27', '2026-07-01 10:51:01', NULL);
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_id`, `transaction_reference`, `payment_status`, `amount`, `created_at`, `updated_at`, `notes`) VALUES ('32', '45', 'Razorpay', 'pay_T881aXGC5LaxYA', 'order_T881Mw4nDwYej3', 'Paid', '300.00', '2026-07-01 11:17:51', '2026-07-01 11:18:22', NULL);

DROP TABLE IF EXISTS `product_images`;
CREATE TABLE `product_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`,`is_primary`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `apadd` (`Apid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `alt_text`, `is_primary`, `sort_order`, `created_at`) VALUES ('1', '32', 'C:\\xampp\\htdocs\\ecommerce1\\helpers/../uploads/products/1782803291_4_1.jpg', 'Sample Test Multiple imgs', '1', '0', '2026-06-30 12:38:11');
INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `alt_text`, `is_primary`, `sort_order`, `created_at`) VALUES ('2', '32', 'C:\\xampp\\htdocs\\ecommerce1\\helpers/../uploads/products/1782803291_98434e2300ecd15a39f1ca5dab8f2518.jpeg', 'Sample Test Multiple imgs', '0', '0', '2026-06-30 12:38:11');
INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `alt_text`, `is_primary`, `sort_order`, `created_at`) VALUES ('3', '32', 'C:\\xampp\\htdocs\\ecommerce1\\helpers/../uploads/products/1782803291_338792.png', 'Sample Test Multiple imgs', '0', '0', '2026-06-30 12:38:11');
INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `alt_text`, `is_primary`, `sort_order`, `created_at`) VALUES ('4', '32', 'C:\\xampp\\htdocs\\ecommerce1\\helpers/../uploads/products/1782803291_minimalist-coding-wallpapers-1920x1080-v0-xkxree6yg51c1.png', 'Sample Test Multiple imgs', '0', '0', '2026-06-30 12:38:11');
INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `alt_text`, `is_primary`, `sort_order`, `created_at`) VALUES ('5', '32', 'C:\\xampp\\htdocs\\ecommerce1\\helpers/../uploads/products/1782803291_wallpapersden.com_windows-12-concept_7680x4320.jpg', 'Sample Test Multiple imgs', '0', '0', '2026-06-30 12:38:11');
INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `alt_text`, `is_primary`, `sort_order`, `created_at`) VALUES ('6', '32', 'C:\\xampp\\htdocs\\ecommerce1\\helpers/../uploads/products/1782803291_wp10243464-talk-is-cheap-show-me-the-code-wallpapers.jpg', 'Sample Test Multiple imgs', '0', '0', '2026-06-30 12:38:11');
INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `alt_text`, `is_primary`, `sort_order`, `created_at`) VALUES ('7', '33', 'C:\\xampp\\htdocs\\ecommerce1\\helpers/../uploads/products/1782829682_wallpapersden.com_windows-12-concept_7680x4320.jpg', 'Test Description', '1', '0', '2026-06-30 19:58:02');
INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `alt_text`, `is_primary`, `sort_order`, `created_at`) VALUES ('8', '33', 'C:\\xampp\\htdocs\\ecommerce1\\helpers/../uploads/products/1782829682_wp10243464-talk-is-cheap-show-me-the-code-wallpapers.jpg', 'Test Description', '0', '0', '2026-06-30 19:58:02');
INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `alt_text`, `is_primary`, `sort_order`, `created_at`) VALUES ('9', '33', 'C:\\xampp\\htdocs\\ecommerce1\\helpers/../uploads/products/1782829682_wp10243478-talk-is-cheap-show-me-the-code-wallpapers.jpg', 'Test Description', '0', '0', '2026-06-30 19:58:02');
INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `alt_text`, `is_primary`, `sort_order`, `created_at`) VALUES ('10', '33', 'C:\\xampp\\htdocs\\ecommerce1\\helpers/../uploads/products/1782829682_wp10243534-talk-is-cheap-show-me-the-code-wallpapers.jpg', 'Test Description', '0', '0', '2026-06-30 19:58:02');
INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `alt_text`, `is_primary`, `sort_order`, `created_at`) VALUES ('11', '33', 'C:\\xampp\\htdocs\\ecommerce1\\helpers/../uploads/products/1782829682_wp10243563-talk-is-cheap-show-me-the-code-wallpapers.jpg', 'Test Description', '0', '0', '2026-06-30 19:58:02');

DROP TABLE IF EXISTS `remember_tokens`;
CREATE TABLE `remember_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `user_type` enum('customer','admin') NOT NULL,
  `selector` varchar(32) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `selector` (`selector`),
  UNIQUE KEY `remember_selector_unique` (`selector`),
  KEY `idx_user` (`user_id`,`user_type`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `remember_tokens` (`id`, `user_id`, `user_type`, `selector`, `token_hash`, `expires_at`, `created_at`) VALUES ('1', '2', 'customer', '484339a9fd3c88c7', '9174eabbdcc6032842525c1dae76f9a796d5a9356dc45028decb464c38e7e5d3', '2026-07-31 07:10:45', '2026-07-01 10:40:45');

DROP TABLE IF EXISTS `search_history`;
CREATE TABLE `search_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `search_query` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_id` (`customer_id`,`search_query`),
  KEY `customer_id_2` (`customer_id`,`created_at`),
  CONSTRAINT `search_history_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `cregister` (`Cid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `search_history` (`id`, `customer_id`, `search_query`, `created_at`, `updated_at`) VALUES ('1', '2', 'coll', '2026-07-01 11:28:31', '2026-07-01 11:45:20');
INSERT INTO `search_history` (`id`, `customer_id`, `search_query`, `created_at`, `updated_at`) VALUES ('3', '2', 'colle', '2026-07-01 11:28:40', '2026-07-01 11:28:40');
INSERT INTO `search_history` (`id`, `customer_id`, `search_query`, `created_at`, `updated_at`) VALUES ('4', '2', 'college', '2026-07-01 11:28:41', '2026-07-01 11:53:03');
INSERT INTO `search_history` (`id`, `customer_id`, `search_query`, `created_at`, `updated_at`) VALUES ('9', '2', 'c', '2026-07-01 11:31:56', '2026-07-01 11:45:19');
INSERT INTO `search_history` (`id`, `customer_id`, `search_query`, `created_at`, `updated_at`) VALUES ('10', '2', 'co', '2026-07-01 11:31:56', '2026-07-01 11:31:56');
INSERT INTO `search_history` (`id`, `customer_id`, `search_query`, `created_at`, `updated_at`) VALUES ('19', '2', 'colleg', '2026-07-01 11:53:04', '2026-07-01 11:53:04');

DROP TABLE IF EXISTS `sub_categories`;
CREATE TABLE `sub_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_id` (`category_id`,`slug`),
  KEY `is_active` (`is_active`),
  CONSTRAINT `sub_categories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `slug`, `description`, `image`, `is_active`, `created_at`, `updated_at`) VALUES ('1', '1', 'Men', 'men', 'Mens clothing', NULL, '1', '2026-06-29 12:59:39', '2026-06-29 12:59:39');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `slug`, `description`, `image`, `is_active`, `created_at`, `updated_at`) VALUES ('2', '1', 'Women', 'women', 'Womens clothing', NULL, '1', '2026-06-29 12:59:39', '2026-06-29 12:59:39');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `slug`, `description`, `image`, `is_active`, `created_at`, `updated_at`) VALUES ('3', '1', 'Kids', 'kids', 'Childrens clothing', NULL, '1', '2026-06-29 12:59:39', '2026-06-29 12:59:39');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `slug`, `description`, `image`, `is_active`, `created_at`, `updated_at`) VALUES ('4', '2', 'Mobiles', 'mobiles', 'Mobile phones', NULL, '1', '2026-06-29 12:59:39', '2026-06-29 12:59:39');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `slug`, `description`, `image`, `is_active`, `created_at`, `updated_at`) VALUES ('5', '2', 'Laptops', 'laptops', 'Laptops and computers', NULL, '1', '2026-06-29 12:59:39', '2026-06-29 12:59:39');
INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `slug`, `description`, `image`, `is_active`, `created_at`, `updated_at`) VALUES ('6', '2', 'Accessories', 'tech-accessories', 'Tech accessories', NULL, '1', '2026-06-29 12:59:39', '2026-06-29 12:59:39');

DROP TABLE IF EXISTS `wishlist`;
CREATE TABLE `wishlist` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `product_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_id` (`customer_id`,`product_id`),
  KEY `product_id` (`product_id`),
  KEY `customer_id_2` (`customer_id`),
  CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `cregister` (`Cid`) ON DELETE CASCADE,
  CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `apadd` (`Apid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `wishlist` (`id`, `customer_id`, `product_id`, `created_at`) VALUES ('1', '2', '33', '2026-07-01 11:17:16');

CREATE DATABASE IF NOT EXISTS `retailler` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `retailler`;

DROP TABLE IF EXISTS `rregister`;
CREATE TABLE `rregister` (
  `rid` int NOT NULL AUTO_INCREMENT,
  `rname` varchar(100) NOT NULL,
  `radd` varchar(255) DEFAULT NULL,
  `rpass` varchar(255) NOT NULL,
  `rconpass` varchar(255) NOT NULL,
  PRIMARY KEY (`rid`),
  UNIQUE KEY `rname` (`rname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `rregister` (`rid`, `rname`, `radd`, `rpass`, `rconpass`) VALUES
  (1, 'demo_retailer', 'Demo Address', '$2y$10$WPpLeMFeK.vsVQ4ciHFQyOkJbDdb2V8Qj4QaKg/aZLjKiAHKmR03W', '$2y$10$WPpLeMFeK.vsVQ4ciHFQyOkJbDdb2V8Qj4QaKg/aZLjKiAHKmR03W');

USE `ecommerce`;

SET FOREIGN_KEY_CHECKS=1;
 