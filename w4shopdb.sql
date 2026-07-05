-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Jul 03, 2026 at 09:16 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `w4shopdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `address_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `label` varchar(50) DEFAULT NULL COMMENT 'Ví dụ: Nhà, Công ty',
  `full_address` text NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`address_id`, `user_id`, `label`, `full_address`, `city`, `is_default`) VALUES
(3, 3, 'Nhà', '789 Trần Hưng Đạo, Quận 5', 'Hồ Chí Minh', 1);

-- --------------------------------------------------------

--
-- Table structure for table `attributes`
--

CREATE TABLE `attributes` (
  `attribute_id` int(11) NOT NULL,
  `attribute_name` varchar(50) NOT NULL COMMENT 'Ví dụ: Màu sắc, Dung lượng, RAM'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attributes`
--

INSERT INTO `attributes` (`attribute_id`, `attribute_name`) VALUES
(1, 'Màu sắc'),
(2, 'Dung lượng'),
(3, 'RAM'),
(4, 'Kích cỡ'),
(6, 'Phiên bản');

-- --------------------------------------------------------

--
-- Table structure for table `attribute_values`
--

CREATE TABLE `attribute_values` (
  `value_id` int(11) NOT NULL,
  `attribute_id` int(11) NOT NULL,
  `value_name` varchar(100) NOT NULL COMMENT 'Ví dụ: Titan Tự Nhiên, 256GB, 8GB'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attribute_values`
--

INSERT INTO `attribute_values` (`value_id`, `attribute_id`, `value_name`) VALUES
(1, 1, 'Titan Tự Nhiên'),
(2, 1, 'Titan Đen'),
(3, 1, 'Titan Trắng'),
(4, 1, 'Titan Sa Mạc'),
(5, 1, 'Titan Xanh'),
(6, 1, 'Đen'),
(7, 1, 'Bạc'),
(8, 1, 'Vàng'),
(9, 2, '128GB'),
(10, 2, '256GB'),
(11, 2, '512GB'),
(12, 2, '1TB'),
(13, 2, '256GB SSD'),
(14, 2, '512GB SSD'),
(15, 2, '1TB SSD'),
(16, 3, '8GB'),
(17, 3, '16GB'),
(18, 3, '32GB'),
(19, 4, '1m'),
(20, 4, '2m'),
(21, 3, '64GB'),
(24, 1, 'Trắng'),
(25, 1, 'Kem'),
(27, 3, '24GB'),
(28, 6, '7800X3D (Tray)'),
(29, 6, '14900'),
(30, 6, '14900K'),
(31, 6, '14900KF');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `created_at`) VALUES
(2, 3, '2026-05-25 12:55:32'),
(3, 5, '2026-06-26 21:21:49'),
(5, 4, '2026-07-04 01:29:04');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `cart_item_id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price_snapshot` decimal(15,2) NOT NULL COMMENT 'Giá lúc thêm vào giỏ'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`cart_item_id`, `cart_id`, `product_id`, `variant_id`, `quantity`, `price_snapshot`) VALUES
(3, 2, 4, 13, 1, 28990000.00),
(27, 3, 4, 14, 1, 28990000.00);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL COMMENT 'NULL = danh mục gốc',
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `parent_id`, `category_name`, `description`) VALUES
(1, NULL, 'Điện thoại', 'Điện thoại di động các loại'),
(2, NULL, 'Laptop', 'Máy tính xách tay'),
(3, NULL, 'Phụ kiện', 'Phụ kiện điện tử'),
(4, 1, 'iPhone', 'Điện thoại Apple iPhone'),
(6, 3, 'Sạc & Cáp', 'Cáp sạc, củ sạc'),
(7, 3, 'Ốp lưng', 'Ốp lưng điện thoại'),
(8, NULL, 'Card đồ họa', 'VGA'),
(9, NULL, 'Bộ xử lý', 'CPU'),
(12, 2, 'Macbook', NULL),
(13, 1, 'Oneplus', 'cấu hình mạnh hơn tầm giá'),
(14, NULL, 'Ram', NULL),
(15, 1, 'Sam Sung', NULL),
(16, 2, 'Dell', NULL),
(17, 2, 'Lenovo', NULL),
(18, 9, 'Intel', NULL),
(19, 9, 'AMD', NULL),
(20, 2, 'ASUS', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `homepage_categories`
--

CREATE TABLE `homepage_categories` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT 'Thứ tự hiển thị, số nhỏ xếp trước (0-3)',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Danh mục được ghim/feature trên trang chủ';

--
-- Dumping data for table `homepage_categories`
--

INSERT INTO `homepage_categories` (`id`, `category_id`, `sort_order`, `created_at`, `updated_at`) VALUES
(254, 1, 0, '2026-07-03 08:52:21', '2026-07-03 08:52:21'),
(255, 8, 1, '2026-07-03 08:52:21', '2026-07-03 08:52:21'),
(256, 2, 2, '2026-07-03 08:52:21', '2026-07-03 08:52:21'),
(257, 3, 3, '2026-07-03 08:52:21', '2026-07-03 08:52:21'),
(258, 9, 4, '2026-07-03 08:52:21', '2026-07-03 08:52:21'),
(259, 14, 5, '2026-07-03 08:52:21', '2026-07-03 08:52:21');

-- --------------------------------------------------------

--
-- Table structure for table `homepage_products`
--

CREATE TABLE `homepage_products` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT 'Thứ tự hiển thị trên trang chủ (0-3)',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sản phẩm được ghim/feature trên trang chủ';

--
-- Dumping data for table `homepage_products`
--

INSERT INTO `homepage_products` (`id`, `product_id`, `category_id`, `sort_order`, `created_at`, `updated_at`) VALUES
(377, 1, 1, 0, '2026-07-03 08:52:21', '2026-07-03 08:52:21'),
(378, 3, 1, 1, '2026-07-03 08:52:21', '2026-07-03 08:52:21'),
(379, 2, 1, 2, '2026-07-03 08:52:21', '2026-07-03 08:52:21'),
(380, 17, 1, 3, '2026-07-03 08:52:21', '2026-07-03 08:52:21'),
(381, 19, 1, 4, '2026-07-03 08:52:21', '2026-07-03 08:52:21'),
(382, 18, 1, 5, '2026-07-03 08:52:21', '2026-07-03 08:52:21'),
(383, 8, 8, 1000, '2026-07-03 08:52:21', '2026-07-03 08:52:21'),
(384, 28, 8, 1001, '2026-07-03 08:52:21', '2026-07-03 08:52:21'),
(385, 29, 8, 1002, '2026-07-03 08:52:21', '2026-07-03 08:52:21'),
(386, 30, 8, 1003, '2026-07-03 08:52:21', '2026-07-03 08:52:21'),
(387, 4, 2, 2000, '2026-07-03 08:52:21', '2026-07-03 08:52:21'),
(388, 5, 2, 2001, '2026-07-03 08:52:21', '2026-07-03 08:52:21'),
(389, 32, 2, 2002, '2026-07-03 08:52:21', '2026-07-03 08:52:21'),
(390, 31, 2, 2003, '2026-07-03 08:52:21', '2026-07-03 08:52:21'),
(391, 6, 3, 3000, '2026-07-03 08:52:21', '2026-07-03 08:52:21'),
(392, 7, 3, 3001, '2026-07-03 08:52:21', '2026-07-03 08:52:21'),
(393, 33, 9, 4000, '2026-07-03 08:52:21', '2026-07-03 08:52:21'),
(394, 35, 9, 4001, '2026-07-03 08:52:21', '2026-07-03 08:52:21'),
(395, 34, 9, 4002, '2026-07-03 08:52:21', '2026-07-03 08:52:21'),
(396, 36, 9, 4003, '2026-07-03 08:52:21', '2026-07-03 08:52:21'),
(397, 37, 14, 5000, '2026-07-03 08:52:21', '2026-07-03 08:52:21'),
(398, 38, 14, 5001, '2026-07-03 08:52:21', '2026-07-03 08:52:21');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'NULL nếu là khách vãng lai',
  `address_id` int(11) DEFAULT NULL COMMENT 'NULL nếu địa chỉ đã bị xóa',
  `order_date` datetime NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','confirmed','shipping','completed','cancelled') NOT NULL DEFAULT 'pending',
  `note` text DEFAULT NULL COMMENT 'Ghi chú của khách',
  `guest_name` varchar(100) DEFAULT NULL COMMENT 'Tên khách vãng lai',
  `guest_phone` varchar(20) DEFAULT NULL COMMENT 'SĐT khách vãng lai',
  `guest_email` varchar(150) DEFAULT NULL COMMENT 'Email khách vãng lai',
  `guest_address` text DEFAULT NULL COMMENT 'Địa chỉ khách vãng lai',
  `guest_city` varchar(100) DEFAULT NULL COMMENT 'Thành phố KVL'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `address_id`, `order_date`, `total_amount`, `status`, `note`, `guest_name`, `guest_phone`, `guest_email`, `guest_address`, `guest_city`) VALUES
(1, 5, NULL, '2026-07-03 23:45:10', 5000000.00, 'confirmed', NULL, NULL, NULL, NULL, NULL, NULL),
(2, 5, NULL, '2026-07-03 23:45:37', 25990000.00, 'pending', NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL COMMENT 'NULL nếu SP đã bị xóa',
  `variant_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL COMMENT 'Giá tại thời điểm mua'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `variant_id`, `quantity`, `unit_price`) VALUES
(1, 1, 38, NULL, 1, 5000000.00),
(2, 2, 2, 8, 1, 25990000.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_method` enum('cod','bank_transfer','momo','vnpay','zalopay','credit_card') NOT NULL,
  `payment_status` enum('pending','processing','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `transaction_id` varchar(100) DEFAULT NULL COMMENT 'Mã GD từ cổng thanh toán',
  `paid_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `order_id`, `payment_method`, `payment_status`, `transaction_id`, `paid_at`) VALUES
(1, 1, 'bank_transfer', 'paid', NULL, '2026-07-03 23:45:53'),
(2, 2, 'cod', 'pending', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `product_name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `slug` varchar(220) NOT NULL COMMENT 'URL thân thiện SEO',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `description`, `slug`, `is_active`, `created_at`) VALUES
(1, 4, 'iPhone 15 Pro Max', 'Chip A17 Pro, camera 48MP', 'iphone-15-pro-max', 1, '2026-05-25 12:55:32'),
(2, 4, 'iPhone 15 ', 'Chip A16 Bionic, Dynamic Island', 'iphone-15', 1, '2026-05-25 12:55:32'),
(3, 15, 'Samsung Galaxy S24 Ultra', 'Snapdragon 8 Gen 3, S Pen', 'samsung-galaxy-s24-ultra', 1, '2026-05-25 12:55:32'),
(4, 12, 'MacBook Air M3 13 inch', 'Apple M3, 8GB RAM, 256GB SSD', 'macbook-air-m3-13-inch', 1, '2026-05-25 12:55:32'),
(5, 16, 'Dell XPS 15 9530', 'Intel Core i7, RTX 4060', 'dell-xps-15-9530', 1, '2026-05-25 12:55:32'),
(6, 6, 'Cáp USB-C Apple ', 'Cáp sạc nhanh chính hãng', 'cap-usb-c-apple', 1, '2026-05-25 12:55:32'),
(7, 7, 'Ốp lưng iPhone 15 Pro Max', 'Chất liệu silicon cao cấp', 'op-lng-iphone-15-pro-max', 1, '2026-05-25 12:55:32'),
(8, 8, 'RTX3060 Ti', 'xử lý đồ họa một cách mượt mà', 'rtx3060-ti', 1, '2026-05-28 14:30:24'),
(17, 13, 'Oneplus ACE 6', 'Snap 8 elite\r\n', 'oneplus-ace-6', 1, '2026-07-03 08:03:33'),
(18, 13, 'Oneplus ACE 5', '', 'oneplus-ace-5', 1, '2026-07-03 08:05:54'),
(19, 4, 'iPhone 16 Pro Max', '', 'iphone-16-pro-max', 1, '2026-07-03 08:14:15'),
(28, 8, 'RTX 5060Ti', '', 'rtx-5060ti', 1, '2026-07-03 08:21:43'),
(29, 8, 'RTX 5070 OC 12G', '', 'rtx-5070-oc-12g', 1, '2026-07-03 08:23:56'),
(30, 8, 'RTX 5090 32G Gaming Trio OC', '', 'rtx-5090-32g-gaming-trio-oc', 1, '2026-07-03 08:25:53'),
(31, 17, 'Lenovo thinkbook 14g7+', 'LƯU Ý BẢN 24GB KHÔNG THỂ NÂNG RAM', 'lenovo-thinkbook-14g7', 1, '2026-07-03 08:30:59'),
(32, 20, 'ASUS VivoBook 14 X1407CA-LY008W', '', 'asus-vivobook-14-x1407ca-ly008w', 1, '2026-07-03 08:33:42'),
(33, 19, 'CPU AMD Ryzen 7', '', 'cpu-amd-ryzen-7', 1, '2026-07-03 08:36:24'),
(34, 18, 'CPU Intel Core i9', '', 'cpu-intel-core-i9', 1, '2026-07-03 08:41:42'),
(35, 18, 'CPU Intel Core i5 14600K (Tray)', '', 'cpu-intel-core-i5-14600k-tray', 1, '2026-07-03 08:45:00'),
(36, 18, 'CPU Intel Core Ultra 7 265K', '', 'cpu-intel-core-ultra-7-265k', 1, '2026-07-03 08:46:17'),
(37, 14, 'RAM Laptop Kingston Sodimm 1.2V', '', 'ram-laptop-kingston-sodimm-12v', 1, '2026-07-03 08:47:53'),
(38, 14, 'RAM PC ADATA XPG D50 RGB  3200MHz DDR4', '', 'ram-pc-adata-xpg-d50-rgb-3200mhz-ddr4', 1, '2026-07-03 08:51:21');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `image_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `image_url` varchar(500) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = ảnh đại diện',
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`image_id`, `product_id`, `variant_id`, `image_url`, `is_primary`, `sort_order`) VALUES
(1, 1, NULL, '/assets/upload/img-product/ip15prm.webp', 1, 1),
(2, 1, NULL, '/images/iphone15promax_2.jpg', 0, 2),
(3, 2, NULL, '/images/iphone15_1.jpg', 0, 1),
(4, 3, NULL, '/images/s24ultra_1.jpg', 0, 1),
(5, 4, NULL, '/assets/upload/img-product/macbookm313inh.webp', 1, 1),
(6, 5, NULL, '/images/dellxps15_1.jpg', 0, 1),
(7, 1, 5, '/assets/upload/img-product/product_1_v5_1782923209.webp', 1, 0),
(8, 30, NULL, '/assets/upload/img-product/product_30_1783092856.webp', 1, 0),
(9, 29, NULL, '/assets/upload/img-product/product_29_1783092890.webp', 1, 0),
(10, 28, NULL, '/assets/upload/img-product/product_28_1783092914.webp', 1, 0),
(11, 19, NULL, '/assets/upload/img-product/product_19_1783092953.webp', 1, 0),
(12, 19, 41, '/assets/upload/img-product/product_19_v41_1783092973.webp', 1, 0),
(13, 19, 39, '/assets/upload/img-product/product_19_v39_1783092994.webp', 1, 0),
(14, 18, 37, '/assets/upload/img-product/product_18_v37_1783093087.jpg', 1, 0),
(15, 18, 38, '/assets/upload/img-product/product_18_v38_1783093104.jpg', 1, 0),
(16, 17, 34, '/assets/upload/img-product/product_17_v34_1783093139.jpg', 1, 0),
(17, 17, 35, '/assets/upload/img-product/product_17_v35_1783093202.webp', 1, 0),
(18, 17, 36, '/assets/upload/img-product/product_17_v36_1783093202.webp', 1, 0),
(19, 8, NULL, '/assets/upload/img-product/product_8_1783093243.webp', 1, 0),
(20, 38, NULL, '/assets/upload/img-product/product_38_1783093394.jpg', 1, 0),
(21, 37, NULL, '/assets/upload/img-product/product_37_1783093418.jpeg', 1, 0),
(22, 36, NULL, '/assets/upload/img-product/product_36_1783093465.jpg', 1, 0),
(23, 35, NULL, '/assets/upload/img-product/product_35_1783093499.webp', 1, 0),
(24, 34, NULL, '/assets/upload/img-product/product_34_1783093528.jpg', 1, 0),
(25, 33, NULL, '/assets/upload/img-product/product_33_1783093627.png', 1, 0),
(26, 32, NULL, '/assets/upload/img-product/product_32_1783093650.jpg', 1, 0),
(27, 31, NULL, '/assets/upload/img-product/product_31_1783093657.png', 1, 0),
(28, 5, NULL, '/assets/upload/img-product/product_5_1783093889.webp', 1, 0),
(29, 3, NULL, '/assets/upload/img-product/product_3_1783093913.webp', 1, 0),
(30, 7, NULL, '/assets/upload/img-product/product_7_1783093936.jpg', 1, 0),
(31, 2, NULL, '/assets/upload/img-product/product_2_1783093997.webp', 1, 0),
(32, 6, NULL, '/assets/upload/img-product/product_6_1783094038.webp', 1, 0),
(34, 38, NULL, '/assets/upload/img-product/product_38_v57_1783105983.webp', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `variant_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_key` varchar(255) NOT NULL COMMENT 'Chuỗi tổ hợp các value_id, sắp xếp tăng dần, cách nhau bằng dấu _ (vd: 1_10, 7_13_16)',
  `sku` varchar(100) DEFAULT NULL COMMENT 'Mã SKU quản lý kho, có thể NULL',
  `price` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Giá riêng của variant này',
  `stock_quantity` int(11) NOT NULL DEFAULT 0 COMMENT 'Tồn kho riêng của variant này',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=đang bán, 0=ngừng bán'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`variant_id`, `product_id`, `variant_key`, `sku`, `price`, `stock_quantity`, `is_active`) VALUES
(1, 1, '1_10', 'IP15PM-256-TN', 34990000.00, 22, 1),
(2, 1, '2_10', 'IP15PM-256-TD', 34990000.00, 20, 1),
(4, 1, '2_11', 'IP15PM-512-TD', 38990000.00, 7, 1),
(5, 1, '8_11', 'IP15PM-1TB-YL', 54990000.00, 5, 1),
(6, 2, '6_9', 'IP15-128-DEN', 22990000.00, 30, 1),
(7, 2, '7_9', 'IP15-128-BAC', 22990000.00, 25, 1),
(8, 2, '6_10', 'IP15-256-DEN', 25990000.00, 18, 1),
(9, 2, '7_10', 'IP15-256-BAC', 25990000.00, 15, 1),
(10, 3, '6_10', 'S24U-256-DEN', 31990000.00, 20, 1),
(11, 3, '5_10', 'S24U-256-TIM', 31990000.00, 14, 1),
(12, 3, '6_11', 'S24U-512-DEN', 36990000.00, 10, 1),
(13, 4, '7_13_16', 'MBA-M3-8-256-BAC', 28990000.00, 14, 1),
(14, 4, '8_13_16', 'MBA-M3-8-256-VAN', 28990000.00, 8, 1),
(15, 4, '7_13_17', 'MBA-M3-16-256-BAC', 33990000.00, 5, 1),
(16, 4, '7_14_17', 'MBA-M3-16-512-BAC', 37990000.00, 4, 1),
(17, 5, '14_17', 'DXPS15-16-512', 42990000.00, 10, 1),
(18, 5, '15_18', 'DXPS15-32-1TB', 52990000.00, 5, 1),
(19, 6, '19', 'CABLE-USBC-1M', 490000.00, 117, 1),
(20, 6, '20', 'CABLE-USBC-2M', 690000.00, 78, 1),
(22, 7, '3', 'CASE-IP15PM-TRG', 290000.00, 50, 1),
(23, 7, '7', 'CASE-IP15PM-BAC', 290000.00, 40, 1),
(24, 8, '18', 'RTX3060-32G', 2500000.00, 0, 1),
(26, 2, 'default', 'đỏ', 30000000.00, 5, 1),
(27, 8, '17', 'RTX3060-16G', 20000000.00, 0, 1),
(34, 17, '7', 'oplus-A6-BAC', 9800000.00, 5, 1),
(35, 17, '6', 'oplus-A6-DEN', 9800000.00, 12, 1),
(36, 17, '24', 'oplus-A6-KEM', 9800000.00, 20, 1),
(37, 18, '24', 'oplus-A5-TRANG', 8200000.00, 5, 1),
(38, 18, '6', 'oplus-A5-DEN', 8200000.00, 2, 1),
(39, 19, '2', 'IP16PM-256-TTDEN', 39900000.00, 4, 1),
(41, 19, '3', 'IP16PM-256-TTTRANG', 39900000.00, 6, 1),
(42, 28, 'default', 'RTX-5060TI-16g', 20000000.00, 5, 1),
(43, 29, 'default', 'RTX-5070OC-12g', 26990000.00, 20, 1),
(44, 30, 'default', 'RTX-5090-32G', 119000000.00, 4, 1),
(45, 31, '27', 'LNV-TB14G7+-24G', 21000000.00, 20, 1),
(47, 31, '17', 'LNV-TB14G7+-16G', 19000000.00, 50, 1),
(48, 32, '7', 'ASUS-VivoBook-14-X1407CA-LY008W-BAC', 22000000.00, 34, 1),
(49, 33, '28', 'ADM-R7-7800X3D (Tray)', 9200000.00, 50, 1),
(50, 34, '29', 'intel-i9-14900', 19000000.00, 5, 1),
(51, 34, '30', 'intel-i9-14900K', 16450000.00, 35, 1),
(52, 34, '31', 'intel-i9-14900KF', 15990000.00, 32, 1),
(53, 35, 'default', 'CPU-Intel-Core-i5-14600K-(Tray)', 8000000.00, 7, 1),
(54, 36, 'default', 'CPU-Intel-Core-Ultra-7-265K', 11790000.00, 6, 1),
(55, 37, '17', 'RAM Laptop Kingston Sodimm 1.2V 16G', 3990000.00, 5, 1),
(56, 37, '16', 'RAM Laptop Kingston Sodimm 1.2V 8G', 2490000.00, 12, 1),
(58, 38, '6_16', 'RAM PC ADATA XPG D50 RGB 8GB (1x8GB) 3200MHz DDR4', 2600000.00, 5, 1),
(60, 38, '17_24', 'RAM PC ADATA XPG D50 RGB 16GB (1x16GB) 3200MHz DDR4-TRANG', 5000000.00, 6, 1);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL COMMENT '1–5 sao',
  `comment` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('customer','admin') NOT NULL DEFAULT 'customer',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password_hash`, `phone`, `role`, `created_at`) VALUES
(1, 'Admin Shop', 'admin@shop.vn', '$2y$10$7uaxlvHs6jhYWZxoRMpxxuDr9kwy9FM9lT8t7o9CcDzUdwWqepOYK', '0900000001', 'admin', '2026-05-25 12:55:32'),
(3, 'Trần Thị B', 'tranthib@gmail.com', '$2y$10$P8haYPHZih8lcrP6x4KB3uy5Jwf65GJuuQLO9c3.tC/K74IT4ndKq', '0900000004', 'customer', '2026-05-25 12:55:32'),
(4, 'tt', 'tt@gmail.com', '$2y$10$iPvEcQazcwgkoVuFumpeeOP2rrzJAfZ2PETjq0Ao8uIJb0BdjDtDG', NULL, 'admin', '2026-06-13 21:54:32'),
(5, 'uu', 'uu@gmail.com', '$2y$10$s9GDLCriSIsp1E5WT6hPfeVXu.vwSFdLOkhnOpL3sP/8F6AZ0P42K', '', 'customer', '2026-06-26 21:21:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `idx_addresses_user` (`user_id`);

--
-- Indexes for table `attributes`
--
ALTER TABLE `attributes`
  ADD PRIMARY KEY (`attribute_id`);

--
-- Indexes for table `attribute_values`
--
ALTER TABLE `attribute_values`
  ADD PRIMARY KEY (`value_id`),
  ADD KEY `idx_attrval_attribute` (`attribute_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD UNIQUE KEY `uq_cart_user` (`user_id`) COMMENT '1 user chỉ có 1 giỏ hàng';

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`cart_item_id`),
  ADD UNIQUE KEY `uq_cart_variant` (`cart_id`,`variant_id`),
  ADD KEY `idx_citems_product` (`product_id`),
  ADD KEY `fk_citems_variant` (`variant_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD KEY `idx_categories_parent` (`parent_id`);

--
-- Indexes for table `homepage_categories`
--
ALTER TABLE `homepage_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_home_cat` (`category_id`),
  ADD KEY `idx_sort_order` (`sort_order`);

--
-- Indexes for table `homepage_products`
--
ALTER TABLE `homepage_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_home_prod` (`product_id`),
  ADD KEY `idx_sort_order` (`sort_order`),
  ADD KEY `idx_category_id` (`category_id`),
  ADD KEY `idx_category_sort` (`category_id`,`sort_order`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `idx_orders_user` (`user_id`),
  ADD KEY `idx_orders_status` (`status`),
  ADD KEY `idx_orders_date` (`order_date`),
  ADD KEY `fk_orders_address` (`address_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `idx_oitems_order` (`order_id`),
  ADD KEY `idx_oitems_product` (`product_id`),
  ADD KEY `fk_oitems_variant` (`variant_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `uq_payments_order` (`order_id`) COMMENT '1 đơn = 1 bản ghi thanh toán';

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `uq_products_slug` (`slug`),
  ADD KEY `idx_products_category` (`category_id`),
  ADD KEY `idx_products_active` (`is_active`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `idx_pimages_product` (`product_id`),
  ADD KEY `fk_pimages_variant` (`variant_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`variant_id`),
  ADD UNIQUE KEY `uq_product_variant_key` (`product_id`,`variant_key`(191)),
  ADD UNIQUE KEY `uq_variants_sku` (`sku`),
  ADD KEY `idx_variants_product` (`product_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `uq_review_user_product` (`user_id`,`product_id`) COMMENT 'Mỗi user chỉ review 1 lần / sản phẩm',
  ADD KEY `idx_reviews_product` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `uq_users_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `attributes`
--
ALTER TABLE `attributes`
  MODIFY `attribute_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `attribute_values`
--
ALTER TABLE `attribute_values`
  MODIFY `value_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `cart_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `homepage_categories`
--
ALTER TABLE `homepage_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=260;

--
-- AUTO_INCREMENT for table `homepage_products`
--
ALTER TABLE `homepage_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=399;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `variant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `fk_addresses_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `attribute_values`
--
ALTER TABLE `attribute_values`
  ADD CONSTRAINT `fk_attrval_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `fk_citems_cart` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`cart_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_citems_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_citems_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`variant_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `homepage_categories`
--
ALTER TABLE `homepage_categories`
  ADD CONSTRAINT `fk_home_cat_categories` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `homepage_products`
--
ALTER TABLE `homepage_products`
  ADD CONSTRAINT `fk_home_prod_categories` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_home_prod_products` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_address` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`address_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_oitems_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_oitems_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_oitems_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`variant_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `fk_pimages_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pimages_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`variant_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `fk_variants_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
