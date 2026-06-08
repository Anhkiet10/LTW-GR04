-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: May 28, 2026 at 04:27 PM
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
(1, 2, 'Nhà', '123 Lê Lợi, Phường 1, Quận 1', 'Hồ Chí Minh', 1),
(2, 2, 'Công ty', '456 Nguyễn Huệ, Phường 2, Quận 1', 'Hồ Chí Minh', 0),
(3, 3, 'Nhà', '789 Trần Hưng Đạo, Quận 5', 'Hồ Chí Minh', 1);

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
(1, 2, '2026-05-25 12:55:32'),
(2, 3, '2026-05-25 12:55:32');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `cart_item_id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price_snapshot` decimal(15,2) NOT NULL COMMENT 'Giá lúc thêm vào giỏ'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`cart_item_id`, `cart_id`, `product_id`, `quantity`, `price_snapshot`) VALUES
(1, 1, 1, 1, 34990000.00),
(2, 1, 6, 2, 490000.00),
(3, 2, 4, 1, 28990000.00);

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
(5, 1, 'Android', 'Điện thoại hệ điều hành Android'),
(6, 3, 'Sạc & Cáp', 'Cáp sạc, củ sạc'),
(7, 3, 'Ốp lưng', 'Ốp lưng điện thoại'),
(8, NULL, 'card đồ họa', 'VGA'),
(9, NULL, 'bộ xử lý ', 'CPU'),
(10, NULL, 'Ram', 'Phụ kiện máy tính');

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
(44, 8, 0, '2026-05-28 21:21:16', '2026-05-28 21:21:16'),
(45, 2, 1, '2026-05-28 21:21:16', '2026-05-28 21:21:16'),
(46, 1, 2, '2026-05-28 21:21:16', '2026-05-28 21:21:16');

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
(33, 8, 1, 0, '2026-05-28 21:21:16', '2026-05-28 21:21:16'),
(34, 4, 1, 1, '2026-05-28 21:21:16', '2026-05-28 21:21:16'),
(35, 1, 1, 2, '2026-05-28 21:21:16', '2026-05-28 21:21:16');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address_id` int(11) DEFAULT NULL COMMENT 'NULL nếu địa chỉ đã bị xóa',
  `order_date` datetime NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','paid','shipping','completed','cancelled') NOT NULL DEFAULT 'pending',
  `note` text DEFAULT NULL COMMENT 'Ghi chú của khách'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `address_id`, `order_date`, `total_amount`, `status`, `note`) VALUES
(1, 2, 1, '2026-05-25 12:55:32', 35970000.00, 'completed', NULL),
(2, 3, 3, '2026-05-25 12:55:32', 28990000.00, 'shipping', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL COMMENT 'NULL nếu SP đã bị xóa',
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL COMMENT 'Giá tại thời điểm mua'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `unit_price`) VALUES
(1, 1, 1, 1, 34990000.00),
(2, 1, 6, 2, 490000.00),
(3, 2, 4, 1, 28990000.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_method` enum('cod','bank_transfer','momo','vnpay','zalopay','credit_card') NOT NULL,
  `payment_status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `transaction_id` varchar(100) DEFAULT NULL COMMENT 'Mã GD từ cổng thanh toán',
  `paid_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `order_id`, `payment_method`, `payment_status`, `transaction_id`, `paid_at`) VALUES
(1, 1, 'momo', 'completed', 'MOMO20240601001', '2024-06-01 10:30:00'),
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
  `price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `slug` varchar(220) NOT NULL COMMENT 'URL thân thiện SEO',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `description`, `price`, `stock_quantity`, `slug`, `is_active`, `created_at`) VALUES
(1, 4, 'iPhone 15 Pro Max 256GB', 'Chip A17 Pro, camera 48MP', 34990000.00, 50, 'iphone-15-pro-max-256gb', 1, '2026-05-25 12:55:32'),
(2, 4, 'iPhone 15 128GB', 'Chip A16 Bionic, Dynamic Island', 22990000.00, 80, 'iphone-15-128gb', 1, '2026-05-25 12:55:32'),
(3, 5, 'Samsung Galaxy S24 Ultra', 'Snapdragon 8 Gen 3, S Pen', 31990000.00, 40, 'samsung-galaxy-s24-ultra', 1, '2026-05-25 12:55:32'),
(4, 2, 'MacBook Air M3 13 inch', 'Apple M3, 8GB RAM, 256GB SSD', 28990000.00, 30, 'macbook-air-m3-13-inch', 1, '2026-05-25 12:55:32'),
(5, 2, 'Dell XPS 15 9530', 'Intel Core i7, RTX 4060', 42990000.00, 20, 'dell-xps-15-9530', 1, '2026-05-25 12:55:32'),
(6, 6, 'Cáp USB-C Apple 1m', 'Cáp sạc nhanh chính hãng', 490000.00, 200, 'cap-usb-c-apple-1m', 1, '2026-05-25 12:55:32'),
(7, 7, 'Ốp lưng iPhone 15 Pro Max', 'Chất liệu silicon cao cấp', 290000.00, 150, 'op-lung-iphone-15-pro-max', 1, '2026-05-25 12:55:32'),
(8, 8, 'RTX3060', 'xử lý đồ họa một cách mượt mà', 2000000.00, 20, 'card-do-hoa-rtx3060', 1, '2026-05-28 14:30:24');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `image_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = ảnh đại diện',
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`image_id`, `product_id`, `image_url`, `is_primary`, `sort_order`) VALUES
(1, 1, '/assets/upload/ip15prm.webp', 1, 1),
(2, 1, '/images/iphone15promax_2.jpg', 0, 2),
(3, 2, '/images/iphone15_1.jpg', 1, 1),
(4, 3, '/images/s24ultra_1.jpg', 1, 1),
(5, 4, '/assets/upload/macbookm313inh.webp', 1, 1),
(6, 5, '/images/dellxps15_1.jpg', 1, 1);

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
) ;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `product_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 2, 5, 'Sản phẩm tuyệt vời, giao hàng nhanh!', '2026-05-25 12:55:32');

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
(1, 'Admin Shop', 'admin@shop.vn', '$2y$10$XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', '0900000001', 'admin', '2026-05-25 12:55:32'),
(2, 'Nguyễn Văn A', 'nguyenvana@gmail.com', '$2y$10$YYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY', '0900000002', 'customer', '2026-05-25 12:55:32'),
(3, 'Trần Thị B', 'tranthib@gmail.com', '$2y$10$ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ', '0900000003', 'customer', '2026-05-25 12:55:32');

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
  ADD UNIQUE KEY `uq_cart_product` (`cart_id`,`product_id`),
  ADD KEY `idx_citems_product` (`product_id`);

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
  ADD KEY `idx_oitems_product` (`product_id`);

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
  ADD KEY `idx_pimages_product` (`product_id`);

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
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `cart_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `homepage_categories`
--
ALTER TABLE `homepage_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `homepage_products`
--
ALTER TABLE `homepage_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `fk_addresses_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `fk_citems_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_oitems_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_oitems_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE SET NULL ON UPDATE CASCADE;

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
  ADD CONSTRAINT `fk_pimages_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
