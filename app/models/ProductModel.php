<?php
require_once __DIR__ . '/../../core/Model.php';

class ProductModel extends Model {

    // ==================== ADMIN CRUD ====================

    /** Lấy tất cả sản phẩm kể cả inactive cho trang admin */
    public function getAllAdmin(): array {
        return $this->fetchAll(
            "SELECT p.*,
                    pi.image_url,
                    c.category_name,
                    pc.category_name AS parent_cat_name,
                    MIN(pv.price)          AS min_price,
                    MAX(pv.price)          AS max_price,
                    SUM(pv.stock_quantity) AS total_stock,
                    COUNT(DISTINCT pv.variant_id) AS variant_count
             FROM products p
             LEFT JOIN product_images pi  ON p.product_id = pi.product_id AND pi.is_primary = 1
             LEFT JOIN categories c       ON p.category_id = c.category_id
             LEFT JOIN categories pc      ON c.parent_id = pc.category_id
             LEFT JOIN product_variants pv ON p.product_id = pv.product_id AND pv.is_active = 1
             GROUP BY p.product_id
             ORDER BY p.created_at DESC"
        );
    }

    /** Lấy chi tiết sản phẩm (bao gồm inactive) */
    public function getByIdAdmin(int $id): ?array {
        $row = $this->fetchOne(
            "SELECT p.*, c.category_name
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.category_id
             WHERE p.product_id = $id"
        );
        return $row ?: null;
    }

    /** Tạo sản phẩm, trả về ID */
    public function createProduct(array $data): int {
        $name        = $this->escape($data['product_name']);
        $slug        = $this->escape($data['slug']);
        $desc        = $this->escape($data['description'] ?? '');
        $categoryId  = isset($data['category_id']) && $data['category_id']
                        ? (int)$data['category_id'] : 'NULL';
        $isActive    = (int)($data['is_active'] ?? 1);

        $this->query(
            "INSERT INTO products (category_id, product_name, description, slug, is_active)
             VALUES ($categoryId, '$name', '$desc', '$slug', $isActive)"
        );
        return (int)$this->lastInsertId();
    }

    /** Cập nhật sản phẩm */
    public function updateProduct(int $id, array $data): void {
        $name       = $this->escape($data['product_name']);
        $desc       = $this->escape($data['description'] ?? '');
        $categoryId = isset($data['category_id']) && $data['category_id']
                        ? (int)$data['category_id'] : 'NULL';
        $isActive   = (int)($data['is_active'] ?? 1);

        $this->query(
            "UPDATE products
             SET product_name = '$name',
                 description  = '$desc',
                 category_id  = $categoryId,
                 is_active    = $isActive
             WHERE product_id = $id"
        );
    }

    /** Xóa mềm sản phẩm (đánh dấu is_active = 0) */
    public function softDeleteProduct(int $id): void {
        $this->query("UPDATE products SET is_active = 0 WHERE product_id = $id");
    }

    /** Kiểm tra slug đã tồn tại chưa */
    public function slugExists(string $slug): int {
        $slug = $this->escape($slug);
        $row  = $this->fetchOne("SELECT COUNT(*) as cnt FROM products WHERE slug = '$slug'");
        return (int)($row['cnt'] ?? 0);
    }

    // ==================== VARIANTS ====================

    public function createVariant(array $data): int {
        $productId = (int)$data['product_id'];
        $sku       = $data['sku'] ? "'" . $this->escape($data['sku']) . "'" : 'NULL';
        $price     = (float)$data['price'];
        $stock     = (int)$data['stock_quantity'];
        $active    = (int)($data['is_active'] ?? 1);

        $this->query(
            "INSERT INTO product_variants (product_id, sku, price, stock_quantity, is_active)
             VALUES ($productId, $sku, $price, $stock, $active)"
        );
        return (int)$this->lastInsertId();
    }

    public function updateVariant(int $id, array $data): void {
        $sku    = $data['sku'] ? "'" . $this->escape($data['sku']) . "'" : 'NULL';
        $price  = (float)$data['price'];
        $stock  = (int)$data['stock_quantity'];
        $active = (int)$data['is_active'];

        $this->query(
            "UPDATE product_variants
             SET sku = $sku, price = $price, stock_quantity = $stock, is_active = $active
             WHERE variant_id = $id"
        );
    }

    public function deleteVariant(int $id): void {
        $this->query("DELETE FROM product_variants WHERE variant_id = $id");
    }

    // ==================== IMAGES ====================

    public function addProductImage(int $productId, string $url, int $isPrimary = 0): void {
        $url      = $this->escape($url);
        $sortOrder = $isPrimary ? 1 : 99;
        $this->query(
            "INSERT INTO product_images (product_id, image_url, is_primary, sort_order)
             VALUES ($productId, '$url', $isPrimary, $sortOrder)"
        );
    }

    public function updatePrimaryImage(int $productId, string $url): void {
        $url = $this->escape($url);
        // Xóa is_primary cũ
        $this->query("UPDATE product_images SET is_primary = 0 WHERE product_id = $productId");
        // Kiểm tra xem url đã có chưa
        $existing = $this->fetchOne(
            "SELECT image_id FROM product_images WHERE product_id = $productId AND image_url = '$url'"
        );
        if ($existing) {
            $this->query(
                "UPDATE product_images SET is_primary = 1 WHERE image_id = {$existing['image_id']}"
            );
        } else {
            $this->addProductImage($productId, $url, 1);
        }
    }

    // ==================== CATEGORIES ====================

    public function getAllCategoriesFlat(): array {
        return $this->fetchAll(
            "SELECT c.category_id, c.category_name, c.parent_id,
                    pc.category_name AS parent_name
             FROM categories c
             LEFT JOIN categories pc ON c.parent_id = pc.category_id
             ORDER BY COALESCE(c.parent_id, c.category_id), c.category_id"
        );
    }

    // ==================== FRONTEND (giữ nguyên) ====================

    public function getAll($limit = null) {
        $sql = "SELECT p.*, pi.image_url,
                       MIN(pv.price) as min_price, MAX(pv.price) as max_price,
                       SUM(pv.stock_quantity) as total_stock
                FROM products p
                LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
                LEFT JOIN product_variants pv ON p.product_id = pv.product_id AND pv.is_active = 1
                WHERE p.is_active = 1
                GROUP BY p.product_id
                ORDER BY p.created_at DESC";
        if ($limit) $sql .= " LIMIT " . (int)$limit;
        return $this->fetchAll($sql);
    }

    public function getByCategory($categoryId, $limit = 5) {
        $categoryId = (int)$categoryId;
        $sql = "SELECT p.*, pi.image_url,
                       c.category_id as prod_cat_id, c.category_name as prod_cat_name, c.parent_id as prod_parent_id,
                       pc.category_name as parent_cat_name,
                       MIN(pv.price) as min_price, MAX(pv.price) as max_price,
                       SUM(pv.stock_quantity) as total_stock
                FROM products p
                LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
                LEFT JOIN product_variants pv ON p.product_id = pv.product_id AND pv.is_active = 1
                JOIN categories c ON p.category_id = c.category_id
                LEFT JOIN categories pc ON c.parent_id = pc.category_id
                WHERE p.is_active = 1 AND (
                    p.category_id = $categoryId
                    OR p.category_id IN (SELECT category_id FROM categories WHERE parent_id = $categoryId)
                )
                GROUP BY p.product_id
                ORDER BY p.created_at DESC";
        if ($limit) $sql .= " LIMIT " . (int)$limit;
        return $this->fetchAll($sql);
    }

    public function getCategoriesWithParent() {
        return $this->fetchAll(
            "SELECT c.category_id, c.category_name, c.parent_id
             FROM categories c WHERE c.parent_id IS NULL ORDER BY c.category_name"
        );
    }

    public function getSubCategories($parentId) {
        $parentId = (int)$parentId;
        return $this->fetchAll(
            "SELECT category_id, category_name FROM categories WHERE parent_id = $parentId ORDER BY category_name"
        );
    }

    public function search($keyword, $limit = 6) {
        $keyword = $this->escape($keyword);
        return $this->fetchAll(
            "SELECT p.*, pi.image_url,
                    MIN(pv.price) as min_price, MAX(pv.price) as max_price,
                    SUM(pv.stock_quantity) as total_stock
             FROM products p
             LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
             LEFT JOIN product_variants pv ON p.product_id = pv.product_id AND pv.is_active = 1
             WHERE p.is_active = 1 AND (p.product_name LIKE '%$keyword%' OR p.description LIKE '%$keyword%')
             GROUP BY p.product_id
             ORDER BY CASE WHEN p.product_name LIKE '$keyword%' THEN 0 ELSE 1 END, p.product_name ASC
             LIMIT $limit"
        );
    }

    public function getFeatured($limit = 8) { return $this->getAll($limit); }

    public function getById($id) {
        $id = (int)$id;
        return $this->fetchOne(
            "SELECT p.*, pi.image_url,
                    MIN(pv.price) as min_price, MAX(pv.price) as max_price,
                    SUM(pv.stock_quantity) as total_stock
             FROM products p
             LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
             LEFT JOIN product_variants pv ON p.product_id = pv.product_id AND pv.is_active = 1
             WHERE p.product_id = $id AND p.is_active = 1 GROUP BY p.product_id"
        );
    }

    public function getImages($productId) {
        $productId = (int)$productId;
        return $this->fetchAll(
            "SELECT * FROM product_images WHERE product_id = $productId ORDER BY sort_order, is_primary DESC"
        );
    }

    public function getVariants($productId) {
        $productId = (int)$productId;
        return $this->fetchAll(
            "SELECT pv.*, GROUP_CONCAT(av.value_name ORDER BY a.attribute_name SEPARATOR ', ') as attributes
             FROM product_variants pv
             LEFT JOIN variant_attribute_values vav ON pv.variant_id = vav.variant_id
             LEFT JOIN attribute_values av ON vav.value_id = av.value_id
             LEFT JOIN attributes a ON av.attribute_id = a.attribute_id
             WHERE pv.product_id = $productId
             GROUP BY pv.variant_id ORDER BY pv.price ASC"
        );
    }

    public function getVariantById($variantId) {
        $variantId = (int)$variantId;
        return $this->fetchOne(
            "SELECT pv.*, GROUP_CONCAT(av.value_name ORDER BY a.attribute_name SEPARATOR ', ') as attributes
             FROM product_variants pv
             LEFT JOIN variant_attribute_values vav ON pv.variant_id = vav.variant_id
             LEFT JOIN attribute_values av ON vav.value_id = av.value_id
             LEFT JOIN attributes a ON av.attribute_id = a.attribute_id
             WHERE pv.variant_id = $variantId GROUP BY pv.variant_id"
        );
    }

    public function getCategoryName($categoryId) {
        $categoryId = (int)$categoryId;
        $result = $this->fetchOne("SELECT category_name FROM categories WHERE category_id = $categoryId");
        return $result ? $result['category_name'] : 'Uncategorized';
    }

    public function getAllCategories() {
        return $this->fetchAll(
            "SELECT category_id, category_name FROM categories WHERE parent_id IS NULL ORDER BY category_name"
        );
    }

    // ==================== HOMEPAGE FEATURED ====================

    public function getHomepageProducts() {
        return $this->fetchAll(
            "SELECT hp.id, hp.product_id, hp.category_id, hp.sort_order, hp.created_at,
                    p.product_name, pi.image_url,
                    MIN(pv.price) as min_price, MAX(pv.price) as max_price
             FROM homepage_products hp
             JOIN products p ON hp.product_id = p.product_id
             LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
             LEFT JOIN product_variants pv ON p.product_id = pv.product_id AND pv.is_active = 1
             GROUP BY hp.id ORDER BY hp.sort_order ASC"
        );
    }

    public function getHomepageCategories() {
        return $this->fetchAll(
            "SELECT hc.id, hc.category_id, hc.sort_order, hc.created_at, c.category_name, c.description
             FROM homepage_categories hc
             JOIN categories c ON hc.category_id = c.category_id
             WHERE c.parent_id IS NULL ORDER BY hc.sort_order ASC"
        );
    }

    public function addHomepageProduct($productId, $sortOrder = 0) {
        $productId = (int)$productId; $sortOrder = (int)$sortOrder;
        $this->query("INSERT INTO homepage_products (product_id, sort_order) VALUES ($productId, $sortOrder) ON DUPLICATE KEY UPDATE sort_order = $sortOrder");
        return true;
    }

    public function addHomepageCategory($categoryId, $sortOrder = 0) {
        $categoryId = (int)$categoryId; $sortOrder = (int)$sortOrder;
        $this->query("INSERT INTO homepage_categories (category_id, sort_order) VALUES ($categoryId, $sortOrder) ON DUPLICATE KEY UPDATE sort_order = $sortOrder");
        return true;
    }

    public function updateHomepageProductOrder($id, $sortOrder) {
        $this->query("UPDATE homepage_products SET sort_order = " . (int)$sortOrder . " WHERE id = " . (int)$id);
        return true;
    }

    public function updateHomepageCategoryOrder($id, $sortOrder) {
        $this->query("UPDATE homepage_categories SET sort_order = " . (int)$sortOrder . " WHERE id = " . (int)$id);
        return true;
    }

    public function removeHomepageProduct($id) {
        $this->query("DELETE FROM homepage_products WHERE id = " . (int)$id);
        return true;
    }

    public function removeHomepageCategory($id) {
        $this->query("DELETE FROM homepage_categories WHERE id = " . (int)$id);
        return true;
    }

    public function getAvailableProducts() {
        return $this->fetchAll(
            "SELECT p.product_id, p.product_name, pi.image_url,
                    MIN(pv.price) as min_price, MAX(pv.price) as max_price
             FROM products p
             LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
             LEFT JOIN product_variants pv ON p.product_id = pv.product_id AND pv.is_active = 1
             WHERE p.is_active = 1 AND p.product_id NOT IN (SELECT product_id FROM homepage_products)
             GROUP BY p.product_id ORDER BY p.product_name ASC"
        );
    }

    public function getAvailableCategories() {
        return $this->fetchAll(
            "SELECT c.category_id, c.category_name FROM categories c
             WHERE c.parent_id IS NULL AND c.category_id NOT IN (SELECT category_id FROM homepage_categories)
             ORDER BY c.category_name ASC"
        );
    }

    public function getHomepageCategoriesWithProducts() {
        $categories = $this->fetchAll(
            "SELECT hc.id, hc.category_id, hc.sort_order, c.category_name, c.description
             FROM homepage_categories hc
             JOIN categories c ON hc.category_id = c.category_id
             WHERE c.parent_id IS NULL ORDER BY hc.sort_order ASC"
        );
        foreach ($categories as &$cat) {
            $catId = (int)$cat['category_id'];
            $cat['products'] = $this->fetchAll(
                "SELECT hp.id as hp_id, hp.product_id, hp.sort_order,
                        p.product_name, p.category_id, pi.image_url,
                        pc.category_id as prod_cat_id, pc.category_name as prod_cat_name, pc.parent_id as prod_parent_id,
                        ppc.category_name as parent_cat_name,
                        MIN(pv.price) as min_price, MAX(pv.price) as max_price,
                        SUM(pv.stock_quantity) as total_stock
                 FROM homepage_products hp
                 JOIN products p ON hp.product_id = p.product_id
                 LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
                 LEFT JOIN product_variants pv ON p.product_id = pv.product_id AND pv.is_active = 1
                 JOIN categories pc ON p.category_id = pc.category_id
                 LEFT JOIN categories ppc ON pc.parent_id = ppc.category_id
                 WHERE p.is_active = 1 AND hp.category_id = $catId
                 GROUP BY hp.id ORDER BY hp.sort_order ASC"
            );
        }
        return $categories;
    }
}