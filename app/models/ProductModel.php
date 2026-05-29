<?php
require_once __DIR__ . '/../../core/Model.php';

class ProductModel extends Model {

    public function getAll($limit = null) {
        $sql = "SELECT p.*, pi.image_url
                FROM products p
                LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
                WHERE p.is_active = 1
                ORDER BY p.created_at DESC";
        if ($limit) $sql .= " LIMIT " . (int)$limit;
        return $this->fetchAll($sql);
    }

public function getByCategory($categoryId, $limit = 5) {
    $categoryId = (int)$categoryId;

    $sql = "SELECT p.*, pi.image_url,
                   c.category_id as prod_cat_id, c.category_name as prod_cat_name, c.parent_id as prod_parent_id,
                   pc.category_name as parent_cat_name
            FROM products p
            LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
            JOIN categories c ON p.category_id = c.category_id
            LEFT JOIN categories pc ON c.parent_id = pc.category_id
            WHERE p.is_active = 1 AND (
                p.category_id = $categoryId
                OR p.category_id IN (SELECT category_id FROM categories WHERE parent_id = $categoryId)
            )
            ORDER BY p.created_at DESC";

    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }

    return $this->fetchAll($sql);
}

    public function getCategoriesWithParent() {
        return $this->fetchAll(
            "SELECT c.category_id, c.category_name, c.parent_id
             FROM categories c
             WHERE c.parent_id IS NULL
             ORDER BY c.category_name"
        );
    }

    public function getSubCategories($parentId) {
        $parentId = (int)$parentId;
        return $this->fetchAll(
            "SELECT category_id, category_name FROM categories
             WHERE parent_id = $parentId
             ORDER BY category_name"
        );
    }

    public function search($keyword, $limit = 6) {
        $keyword = $this->escape($keyword);
        return $this->fetchAll(
            "SELECT p.*, pi.image_url
             FROM products p
             LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
             WHERE p.is_active = 1 AND
                   (p.product_name LIKE '%$keyword%' OR p.description LIKE '%$keyword%')
             ORDER BY
                CASE WHEN p.product_name LIKE '$keyword%' THEN 0 ELSE 1 END,
                p.product_name ASC
             LIMIT $limit"
        );
    }

    public function getFeatured($limit = 8) {
        return $this->getAll($limit);
    }

    public function getById($id) {
        $id = (int)$id;
        return $this->fetchOne(
            "SELECT p.*, pi.image_url
             FROM products p
             LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
             WHERE p.product_id = $id AND p.is_active = 1"
        );
    }

    public function getImages($productId) {
        $productId = (int)$productId;
        return $this->fetchAll(
            "SELECT * FROM product_images
             WHERE product_id = $productId
             ORDER BY sort_order, is_primary DESC"
        );
    }

    public function getCategoryName($categoryId) {
        $categoryId = (int)$categoryId;
        $result = $this->fetchOne(
            "SELECT category_name FROM categories WHERE category_id = $categoryId"
        );
        return $result ? $result['category_name'] : 'Uncategorized';
    }

    public function getAllCategories() {
        return $this->fetchAll(
            "SELECT category_id, category_name FROM categories
             WHERE parent_id IS NULL
             ORDER BY category_name"
        );
    }

    // ==================== HOMEPAGE FEATURED ITEMS ====================

    public function getHomepageProducts() {
        return $this->fetchAll(
            "SELECT hp.id, hp.product_id, hp.category_id, hp.sort_order, hp.created_at,
                    p.product_name, p.price, pi.image_url
             FROM homepage_products hp
             JOIN products p ON hp.product_id = p.product_id
             LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
             ORDER BY hp.sort_order ASC"
        );
    }

    public function getHomepageCategories() {
        return $this->fetchAll(
            "SELECT hc.id, hc.category_id, hc.sort_order, hc.created_at,
                    c.category_name, c.description
             FROM homepage_categories hc
             JOIN categories c ON hc.category_id = c.category_id
             WHERE c.parent_id IS NULL
             ORDER BY hc.sort_order ASC"
        );
    }

    public function addHomepageProduct($productId, $sortOrder = 0) {
        $productId = (int)$productId;
        $sortOrder = (int)$sortOrder;

        $sql = "INSERT INTO homepage_products (product_id, sort_order)
                VALUES ($productId, $sortOrder)
                ON DUPLICATE KEY UPDATE sort_order = $sortOrder";

        $this->query($sql);
        return true;
    }

    public function addHomepageCategory($categoryId, $sortOrder = 0) {
        $categoryId = (int)$categoryId;
        $sortOrder = (int)$sortOrder;

        $sql = "INSERT INTO homepage_categories (category_id, sort_order)
                VALUES ($categoryId, $sortOrder)
                ON DUPLICATE KEY UPDATE sort_order = $sortOrder";

        $this->query($sql);
        return true;
    }

    public function updateHomepageProductOrder($id, $sortOrder) {
        $id = (int)$id;
        $sortOrder = (int)$sortOrder;

        $this->query(
            "UPDATE homepage_products SET sort_order = $sortOrder WHERE id = $id"
        );
        return true;
    }

    public function updateHomepageCategoryOrder($id, $sortOrder) {
        $id = (int)$id;
        $sortOrder = (int)$sortOrder;

        $this->query(
            "UPDATE homepage_categories SET sort_order = $sortOrder WHERE id = $id"
        );
        return true;
    }

    public function removeHomepageProduct($id) {
        $id = (int)$id;
        $this->query("DELETE FROM homepage_products WHERE id = $id");
        return true;
    }

    public function removeHomepageCategory($id) {
        $id = (int)$id;
        $this->query("DELETE FROM homepage_categories WHERE id = $id");
        return true;
    }

    public function getAvailableProducts() {
        return $this->fetchAll(
            "SELECT p.product_id, p.product_name, p.price, p.category_id, pi.image_url
             FROM products p
             LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
             WHERE p.is_active = 1 AND p.product_id NOT IN (SELECT product_id FROM homepage_products)
             ORDER BY p.product_name ASC"
        );
    }

    public function getAvailableCategories() {
        return $this->fetchAll(
            "SELECT c.category_id, c.category_name
             FROM categories c
             WHERE c.parent_id IS NULL AND c.category_id NOT IN (SELECT category_id FROM homepage_categories)
             ORDER BY c.category_name ASC"
        );
    }

    public function getHomepageCategoriesWithProducts() {
        $categories = $this->fetchAll(
            "SELECT hc.id, hc.category_id, hc.sort_order,
                    c.category_name, c.description
             FROM homepage_categories hc
             JOIN categories c ON hc.category_id = c.category_id
             WHERE c.parent_id IS NULL
             ORDER BY hc.sort_order ASC"
        );

        foreach ($categories as &$cat) {
            $catId = (int)$cat['category_id'];
            $cat['products'] = $this->fetchAll(
                "SELECT hp.id as hp_id, hp.product_id, hp.sort_order,
                        p.product_name, p.price, p.category_id, p.stock_quantity, pi.image_url,
                        pc.category_id as prod_cat_id, pc.category_name as prod_cat_name, pc.parent_id as prod_parent_id,
                        ppc.category_name as parent_cat_name
                 FROM homepage_products hp
                 JOIN products p ON hp.product_id = p.product_id
                 LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
                 JOIN categories pc ON p.category_id = pc.category_id
                 LEFT JOIN categories ppc ON pc.parent_id = ppc.category_id
                 WHERE p.is_active = 1 AND hp.category_id = $catId
                 ORDER BY hp.sort_order ASC"
            );
        }

        return $categories;
    }
}
?>