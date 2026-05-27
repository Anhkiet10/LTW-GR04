-- ============================================================
--  E-COMMERCE DATABASE SCHEMA
--  Engine  : MySQL 8.0+
--  Charset : utf8mb4 / utf8mb4_unicode_ci
-- ============================================================

CREATE DATABASE IF NOT EXISTS W4SHOPDB
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE W4SHOPDB;

-- ============================================================
-- 1. USERS
-- ============================================================
CREATE TABLE users (
  user_id       INT            NOT NULL AUTO_INCREMENT,
  full_name     VARCHAR(100)   NOT NULL,
  email         VARCHAR(150)   NOT NULL,
  password_hash VARCHAR(255)   NOT NULL,
  phone         VARCHAR(20)        NULL,
  role          ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  created_at    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (user_id),
  UNIQUE  KEY uq_users_email (email)
) ENGINE=InnoDB;

-- ============================================================
-- 2. ADDRESSES
-- ============================================================
CREATE TABLE addresses (
  address_id   INT           NOT NULL AUTO_INCREMENT,
  user_id      INT           NOT NULL,
  label        VARCHAR(50)       NULL COMMENT 'Ví dụ: Nhà, Công ty',
  full_address TEXT          NOT NULL,
  city         VARCHAR(100)      NULL,
  is_default   TINYINT(1)    NOT NULL DEFAULT 0,

  PRIMARY KEY (address_id),
  KEY idx_addresses_user (user_id),
  CONSTRAINT fk_addresses_user
    FOREIGN KEY (user_id) REFERENCES users (user_id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 3. CATEGORIES  (hỗ trợ danh mục cha–con)
-- ============================================================
CREATE TABLE categories (
  category_id   INT          NOT NULL AUTO_INCREMENT,
  parent_id     INT              NULL COMMENT 'NULL = danh mục gốc',
  category_name VARCHAR(100) NOT NULL,
  description   TEXT             NULL,

  PRIMARY KEY (category_id),
  KEY idx_categories_parent (parent_id),
  CONSTRAINT fk_categories_parent
    FOREIGN KEY (parent_id) REFERENCES categories (category_id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 4. PRODUCTS
-- ============================================================
CREATE TABLE products (
  product_id     INT             NOT NULL AUTO_INCREMENT,
  category_id    INT                 NULL,
  product_name   VARCHAR(200)    NOT NULL,
  description    TEXT                NULL,
  price          DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
  stock_quantity INT             NOT NULL DEFAULT 0,
  slug           VARCHAR(220)    NOT NULL COMMENT 'URL thân thiện SEO',
  is_active      TINYINT(1)      NOT NULL DEFAULT 1,
  created_at     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (product_id),
  UNIQUE  KEY uq_products_slug (slug),
  KEY idx_products_category (category_id),
  KEY idx_products_active   (is_active),
  CONSTRAINT fk_products_category
    FOREIGN KEY (category_id) REFERENCES categories (category_id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 5. PRODUCT_IMAGES
-- ============================================================
CREATE TABLE product_images (
  image_id   INT          NOT NULL AUTO_INCREMENT,
  product_id INT          NOT NULL,
  image_url  VARCHAR(500) NOT NULL,
  is_primary TINYINT(1)   NOT NULL DEFAULT 0 COMMENT '1 = ảnh đại diện',
  sort_order INT          NOT NULL DEFAULT 0,

  PRIMARY KEY (image_id),
  KEY idx_pimages_product (product_id),
  CONSTRAINT fk_pimages_product
    FOREIGN KEY (product_id) REFERENCES products (product_id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 6. CART
-- ============================================================
CREATE TABLE cart (
  cart_id    INT      NOT NULL AUTO_INCREMENT,
  user_id    INT      NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (cart_id),
  UNIQUE  KEY uq_cart_user (user_id) COMMENT '1 user chỉ có 1 giỏ hàng',
  CONSTRAINT fk_cart_user
    FOREIGN KEY (user_id) REFERENCES users (user_id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 7. CART_ITEMS
-- ============================================================
CREATE TABLE cart_items (
  cart_item_id    INT           NOT NULL AUTO_INCREMENT,
  cart_id         INT           NOT NULL,
  product_id      INT           NOT NULL,
  quantity        INT           NOT NULL DEFAULT 1,
  price_snapshot  DECIMAL(15,2) NOT NULL COMMENT 'Giá lúc thêm vào giỏ',

  PRIMARY KEY (cart_item_id),
  UNIQUE  KEY uq_cart_product (cart_id, product_id),
  KEY idx_citems_product (product_id),
  CONSTRAINT fk_citems_cart
    FOREIGN KEY (cart_id) REFERENCES cart (cart_id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_citems_product
    FOREIGN KEY (product_id) REFERENCES products (product_id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 8. ORDERS
-- ============================================================
CREATE TABLE orders (
  order_id     INT           NOT NULL AUTO_INCREMENT,
  user_id      INT           NOT NULL,
  address_id   INT               NULL COMMENT 'NULL nếu địa chỉ đã bị xóa',
  order_date   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  total_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  status       ENUM(
                 'pending',
                 'paid',
                 'shipping',
                 'completed',
                 'cancelled'
               ) NOT NULL DEFAULT 'pending',
  note         TEXT              NULL COMMENT 'Ghi chú của khách',

  PRIMARY KEY (order_id),
  KEY idx_orders_user    (user_id),
  KEY idx_orders_status  (status),
  KEY idx_orders_date    (order_date),
  CONSTRAINT fk_orders_user
    FOREIGN KEY (user_id) REFERENCES users (user_id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_orders_address
    FOREIGN KEY (address_id) REFERENCES addresses (address_id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 9. ORDER_ITEMS
-- ============================================================
CREATE TABLE order_items (
  order_item_id INT           NOT NULL AUTO_INCREMENT,
  order_id      INT           NOT NULL,
  product_id    INT               NULL COMMENT 'NULL nếu SP đã bị xóa',
  quantity      INT           NOT NULL,
  unit_price    DECIMAL(15,2) NOT NULL COMMENT 'Giá tại thời điểm mua',

  PRIMARY KEY (order_item_id),
  KEY idx_oitems_order   (order_id),
  KEY idx_oitems_product (product_id),
  CONSTRAINT fk_oitems_order
    FOREIGN KEY (order_id) REFERENCES orders (order_id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_oitems_product
    FOREIGN KEY (product_id) REFERENCES products (product_id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 10. PAYMENTS
-- ============================================================
CREATE TABLE payments (
  payment_id     INT      NOT NULL AUTO_INCREMENT,
  order_id       INT      NOT NULL,
  payment_method ENUM(
                   'cod',
                   'bank_transfer',
                   'momo',
                   'vnpay',
                   'zalopay',
                   'credit_card'
                 ) NOT NULL,
  payment_status ENUM(
                   'pending',
                   'completed',
                   'failed',
                   'refunded'
                 ) NOT NULL DEFAULT 'pending',
  transaction_id VARCHAR(100) NULL COMMENT 'Mã GD từ cổng thanh toán',
  paid_at        DATETIME     NULL,

  PRIMARY KEY (payment_id),
  UNIQUE  KEY uq_payments_order (order_id) COMMENT '1 đơn = 1 bản ghi thanh toán',
  CONSTRAINT fk_payments_order
    FOREIGN KEY (order_id) REFERENCES orders (order_id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 11. REVIEWS  (tùy chọn – mở rộng sau)
-- ============================================================
CREATE TABLE reviews (
  review_id  INT     NOT NULL AUTO_INCREMENT,
  product_id INT     NOT NULL,
  user_id    INT     NOT NULL,
  rating     TINYINT NOT NULL COMMENT '1–5 sao',
  comment    TEXT        NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (review_id),
  UNIQUE  KEY uq_review_user_product (user_id, product_id)
    COMMENT 'Mỗi user chỉ review 1 lần / sản phẩm',
  KEY idx_reviews_product (product_id),
  CONSTRAINT chk_reviews_rating
    CHECK (rating BETWEEN 1 AND 5),
  CONSTRAINT fk_reviews_product
    FOREIGN KEY (product_id) REFERENCES products (product_id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_reviews_user
    FOREIGN KEY (user_id) REFERENCES users (user_id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- demo ghim sản phẩm của admin
-- ============================================================

CREATE TABLE homepage_categories (
  id          INT NOT NULL AUTO_INCREMENT,
  category_id INT NOT NULL,
  sort_order  INT NOT NULL DEFAULT 0 COMMENT 'Thứ tự hiển thị, số nhỏ xếp trước',
  PRIMARY KEY (id),
  UNIQUE KEY uq_home_cat (category_id), -- Để một danh mục không bị ghim trùng 2 lần
  CONSTRAINT fk_home_cat_categories
    FOREIGN KEY (category_id) REFERENCES categories (category_id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE homepage_products (
  id          INT NOT NULL AUTO_INCREMENT,
  product_id  INT NOT NULL,
  sort_order  INT NOT NULL DEFAULT 0 COMMENT 'Thứ tự hiển thị trên trang chủ',
  PRIMARY KEY (id),
  UNIQUE KEY uq_home_prod (product_id), -- Để một sản phẩm không bị ghim trùng
  CONSTRAINT fk_home_prod_products
    FOREIGN KEY (product_id) REFERENCES products (product_id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- SAMPLE DATA
-- ============================================================



-- Admin + 2 khách hàng
INSERT INTO users (full_name, email, password_hash, phone, role) VALUES
  ('Admin Shop',   'admin@shop.vn',   '$2y$10$XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', '0900000001', 'admin'),
  ('Nguyễn Văn A', 'nguyenvana@gmail.com', '$2y$10$YYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY', '0900000002', 'customer'),
  ('Trần Thị B',   'tranthib@gmail.com',   '$2y$10$ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ', '0900000003', 'customer');

-- Địa chỉ
INSERT INTO addresses (user_id, label, full_address, city, is_default) VALUES
  (2, 'Nhà',     '123 Lê Lợi, Phường 1, Quận 1', 'Hồ Chí Minh', 1),
  (2, 'Công ty', '456 Nguyễn Huệ, Phường 2, Quận 1', 'Hồ Chí Minh', 0),
  (3, 'Nhà',     '789 Trần Hưng Đạo, Quận 5',    'Hồ Chí Minh', 1);

-- Danh mục (có cha–con)
INSERT INTO categories (parent_id, category_name, description) VALUES
  (NULL, 'Điện thoại', 'Điện thoại di động các loại'),
  (NULL, 'Laptop',     'Máy tính xách tay'),
  (NULL, 'Phụ kiện',   'Phụ kiện điện tử'),
  (1,    'iPhone',     'Điện thoại Apple iPhone'),
  (1,    'Android',    'Điện thoại hệ điều hành Android'),
  (3,    'Sạc & Cáp',  'Cáp sạc, củ sạc'),
  (3,    'Ốp lưng',    'Ốp lưng điện thoại');

-- Sản phẩm
INSERT INTO products (category_id, product_name, description, price, stock_quantity, slug) VALUES
  (4, 'iPhone 15 Pro Max 256GB',   'Chip A17 Pro, camera 48MP', 34990000, 50, 'iphone-15-pro-max-256gb'),
  (4, 'iPhone 15 128GB',           'Chip A16 Bionic, Dynamic Island', 22990000, 80, 'iphone-15-128gb'),
  (5, 'Samsung Galaxy S24 Ultra',  'Snapdragon 8 Gen 3, S Pen', 31990000, 40, 'samsung-galaxy-s24-ultra'),
  (2, 'MacBook Air M3 13 inch',    'Apple M3, 8GB RAM, 256GB SSD', 28990000, 30, 'macbook-air-m3-13-inch'),
  (2, 'Dell XPS 15 9530',          'Intel Core i7, RTX 4060', 42990000, 20, 'dell-xps-15-9530'),
  (6, 'Cáp USB-C Apple 1m',        'Cáp sạc nhanh chính hãng', 490000, 200, 'cap-usb-c-apple-1m'),
  (7, 'Ốp lưng iPhone 15 Pro Max', 'Chất liệu silicon cao cấp', 290000, 150, 'op-lung-iphone-15-pro-max');

-- Ảnh sản phẩm
INSERT INTO product_images (product_id, image_url, is_primary, sort_order) VALUES
  (1, '/images/iphone15promax_1.jpg', 1, 1),
  (1, '/images/iphone15promax_2.jpg', 0, 2),
  (2, '/images/iphone15_1.jpg',       1, 1),
  (3, '/images/s24ultra_1.jpg',        1, 1),
  (4, '/images/macbookairm3_1.jpg',    1, 1),
  (5, '/images/dellxps15_1.jpg',       1, 1);

-- Giỏ hàng
INSERT INTO cart (user_id) VALUES (2), (3);

INSERT INTO cart_items (cart_id, product_id, quantity, price_snapshot) VALUES
  (1, 1, 1, 34990000),
  (1, 6, 2,   490000),
  (2, 4, 1, 28990000);

-- Đơn hàng
INSERT INTO orders (user_id, address_id, total_amount, status) VALUES
  (2, 1, 35970000, 'completed'),
  (3, 3, 28990000, 'shipping');

INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES
  (1, 1, 1, 34990000),
  (1, 6, 2,   490000),
  (2, 4, 1, 28990000);

-- Thanh toán
INSERT INTO payments (order_id, payment_method, payment_status, transaction_id, paid_at) VALUES
  (1, 'momo',    'completed', 'MOMO20240601001', '2024-06-01 10:30:00'),
  (2, 'cod',     'pending',   NULL,               NULL);

-- Review
INSERT INTO reviews (product_id, user_id, rating, comment) VALUES
  (1, 2, 5, 'Sản phẩm tuyệt vời, giao hàng nhanh!');