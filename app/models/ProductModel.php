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
        return $this->fetchAll(
            "SELECT p.*, pi.image_url
             FROM products p
             LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
             WHERE p.category_id = $categoryId AND p.is_active = 1
             ORDER BY p.created_at DESC
             LIMIT $limit"
        );
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
}
?>