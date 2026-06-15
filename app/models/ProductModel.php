<?php
require_once __DIR__ . '/../../core/Model.php';

/**
 * ProductModel — Frontend + Admin CRUD
 *
 * Thay đổi so với phiên bản cũ (schema v2):
 *  - Bỏ toàn bộ JOIN với variant_attribute_values (bảng đã bị DROP)
 *  - getVariants() / getVariantById() đọc variant_key rồi parse ra value_id
 *    để JOIN attribute_values, thay vì qua bảng trung gian
 *  - createVariant() / updateVariant() nhận thêm mảng value_ids để tự
 *    tạo variant_key (sort tăng dần, join bằng '_')
 *  - Thêm getVariantByKey() — lookup nhanh bằng (product_id, variant_key)
 *  - getAttributesForProduct() — trả về cấu trúc nhóm attribute cho trang detail
 */
class ProductModel extends Model {

    // =========================================================================
    // HELPER: tạo variant_key từ mảng value_id
    // =========================================================================

    /**
     * Nhận vào mảng value_id bất kỳ thứ tự, trả về chuỗi đã sort tăng dần.
     * Ví dụ: [16, 7, 13] → '7_13_16'
     */
    private function buildVariantKey(array $valueIds): string {
        if (empty($valueIds)) return 'default';
        $ids = array_map('intval', $valueIds);
        sort($ids, SORT_NUMERIC);
        return implode('_', $ids);
    }

    // =========================================================================
    // FRONTEND
    // =========================================================================

    /** Tất cả sản phẩm active, kèm giá và ảnh đại diện */
    public function getAll(?int $limit = null): array {
        $sql = "
            SELECT p.*,
                   pi.image_url,
                   MIN(pv.price)          AS min_price,
                   MAX(pv.price)          AS max_price,
                   SUM(pv.stock_quantity) AS total_stock
            FROM   products p
            LEFT JOIN product_images pi
                   ON pi.product_id = p.product_id AND pi.is_primary = 1
            LEFT JOIN product_variants pv
                   ON pv.product_id = p.product_id AND pv.is_active = 1
            WHERE  p.is_active = 1
            GROUP  BY p.product_id
            ORDER  BY p.created_at DESC";
        if ($limit) $sql .= ' LIMIT ' . (int)$limit;
        return $this->fetchAll($sql);
    }

    /** Sản phẩm theo danh mục (hỗ trợ cả danh mục cha) */
    public function getByCategory(int $categoryId, ?int $limit = null): array {
        $cid = (int)$categoryId;
        $sql = "
            SELECT p.*,
                   pi.image_url,
                   c.category_name        AS prod_cat_name,
                   c.parent_id            AS prod_parent_id,
                   pc.category_name       AS parent_cat_name,
                   MIN(pv.price)          AS min_price,
                   MAX(pv.price)          AS max_price,
                   SUM(pv.stock_quantity) AS total_stock
            FROM   products p
            LEFT JOIN product_images pi
                   ON pi.product_id = p.product_id AND pi.is_primary = 1
            LEFT JOIN product_variants pv
                   ON pv.product_id = p.product_id AND pv.is_active = 1
            JOIN   categories c  ON c.category_id = p.category_id
            LEFT JOIN categories pc ON pc.category_id = c.parent_id
            WHERE  p.is_active = 1
              AND  (
                       p.category_id = $cid
                    OR p.category_id IN (
                           SELECT category_id FROM categories WHERE parent_id = $cid
                       )
                   )
            GROUP  BY p.product_id
            ORDER  BY p.created_at DESC";
        if ($limit) $sql .= ' LIMIT ' . (int)$limit;
        return $this->fetchAll($sql);
    }

    /** Chi tiết sản phẩm (frontend — chỉ lấy active) */
    public function getById(int $id): ?array {
        $row = $this->fetchOne("
            SELECT p.*,
                   pi.image_url,
                   c.category_name,
                   MIN(pv.price)          AS min_price,
                   MAX(pv.price)          AS max_price,
                   SUM(pv.stock_quantity) AS total_stock
            FROM   products p
            LEFT JOIN product_images pi
                   ON pi.product_id = p.product_id AND pi.is_primary = 1
            LEFT JOIN product_variants pv
                   ON pv.product_id = p.product_id AND pv.is_active = 1
            LEFT JOIN categories c ON c.category_id = p.category_id
            WHERE  p.product_id = $id AND p.is_active = 1
            GROUP  BY p.product_id
        ");
        return $row ?: null;
    }

    /** Tìm kiếm sản phẩm */
    public function search(string $keyword, int $limit = 6): array {
        $kw = $this->escape($keyword);
        return $this->fetchAll("
            SELECT p.*,
                   pi.image_url,
                   MIN(pv.price)          AS min_price,
                   MAX(pv.price)          AS max_price,
                   SUM(pv.stock_quantity) AS total_stock
            FROM   products p
            LEFT JOIN product_images pi
                   ON pi.product_id = p.product_id AND pi.is_primary = 1
            LEFT JOIN product_variants pv
                   ON pv.product_id = p.product_id AND pv.is_active = 1
            WHERE  p.is_active = 1
              AND  (p.product_name LIKE '%$kw%' OR p.description LIKE '%$kw%')
            GROUP  BY p.product_id
            ORDER  BY CASE WHEN p.product_name LIKE '$kw%' THEN 0 ELSE 1 END,
                      p.product_name ASC
            LIMIT  $limit
        ");
    }

    public function getFeatured(int $limit = 8): array {
        return $this->getAll($limit);
    }

    // ─── Ảnh ──────────────────────────────────────────────────────────────────

    public function getImages(int $productId): array {
        return $this->fetchAll("
            SELECT * FROM product_images
            WHERE  product_id = $productId
            ORDER  BY is_primary DESC, sort_order
        ");
    }

    // ─── Variants (frontend) ──────────────────────────────────────────────────

    /**
     * Lấy tất cả variants của sản phẩm, kèm tên attribute đọc được.
     *
     * Cách hoạt động với schema v2 (không có variant_attribute_values):
     *   1. Lấy hàng từ product_variants → có cột variant_key (vd '7_13_16')
     *   2. Parse variant_key thành danh sách value_id
     *   3. JOIN attribute_values để lấy tên (GROUP_CONCAT trong subquery)
     *
     * Vì MariaDB không thể join động trên chuỗi variant_key một cách gọn,
     * ta dùng FIND_IN_SET với separator thay bằng dấu phẩy — nên ta dùng
     * REPLACE để đổi '_' → ',' rồi dùng FIND_IN_SET.
     */
    public function getVariants(int $productId): array {
        $pid = (int)$productId;
        // Lấy raw variants trước
        $variants = $this->fetchAll("
            SELECT * FROM product_variants
            WHERE  product_id = $pid AND is_active = 1
            ORDER  BY price ASC
        ");

        if (empty($variants)) return [];

        // Với mỗi variant, parse variant_key rồi lấy tên attribute
        foreach ($variants as &$v) {
            $v['attribute_label'] = $this->resolveVariantLabel($v['variant_key']);
        }
        return $variants;
    }

    /** Lấy 1 variant theo ID */
    public function getVariantById(int $variantId): ?array {
        $vid = (int)$variantId;
        $row = $this->fetchOne("
            SELECT * FROM product_variants WHERE variant_id = $vid
        ");
        if (!$row) return null;
        $row['attribute_label'] = $this->resolveVariantLabel($row['variant_key']);
        return $row;
    }

    /**
     * Lookup nhanh variant bằng (product_id + mảng value_id được chọn).
     * Dùng trên trang detail khi user click chọn option: build key → query.
     *
     * @param int   $productId
     * @param int[] $selectedValueIds  Mảng value_id user đã chọn (bất kỳ thứ tự)
     */
    public function getVariantByKey(int $productId, array $selectedValueIds): ?array {
        $key = $this->escape($this->buildVariantKey($selectedValueIds));
        $pid = (int)$productId;
        $row = $this->fetchOne("
            SELECT * FROM product_variants
            WHERE  product_id = $pid AND variant_key = '$key' AND is_active = 1
        ");
        if (!$row) return null;
        $row['attribute_label'] = $this->resolveVariantLabel($row['variant_key']);
        return $row;
    }

    /**
     * Trả về attributes + values thực sự xuất hiện trong variant_key của sản phẩm.
     * Đọc tất cả variant_key từ DB → parse value_id → nhóm theo loại attribute.
     *
     * Output:
     * [
     *   ['attribute_id' => 1, 'attribute_name' => 'Màu sắc', 'values' => [
     *       ['value_id' => 6, 'value_name' => 'Đen'],
     *       ['value_id' => 7, 'value_name' => 'Bạc'],
     *   ]],
     *   ...
     * ]
     */
    public function getAttributesForProduct(int $productId): array {
        $pid = (int)$productId;

        $variants = $this->fetchAll("
            SELECT variant_key FROM product_variants
            WHERE  product_id = $pid AND is_active = 1
        ");
        if (empty($variants)) return [];

        // Thu thập value_id duy nhất từ mọi variant_key (bỏ qua 'default')
        $valueIdsByAttribute = [];
        foreach ($variants as $v) {
            if ($v['variant_key'] === 'default' || $v['variant_key'] === '') continue;
            foreach (explode('_', $v['variant_key']) as $vid) {
                $vid = (int)$vid;
                if ($vid > 0) {
                    $valueIdsByAttribute[$vid] = true;
                }
            }
        }
        if (empty($valueIdsByAttribute)) return [];

        $idList = implode(',', array_keys($valueIdsByAttribute));

        $rows = $this->fetchAll("
            SELECT a.attribute_id, a.attribute_name,
                   av.value_id,    av.value_name
            FROM   attribute_values av
            JOIN   attributes a ON a.attribute_id = av.attribute_id
            WHERE  av.value_id IN ($idList)
            ORDER  BY a.attribute_id, av.value_id
        ");

        $grouped = [];
        foreach ($rows as $row) {
            $aid = (int)$row['attribute_id'];
            if (!isset($grouped[$aid])) {
                $grouped[$aid] = [
                    'attribute_id'   => $aid,
                    'attribute_name' => $row['attribute_name'],
                    'values'         => [],
                ];
            }
            $grouped[$aid]['values'][] = [
                'value_id'   => (int)$row['value_id'],
                'value_name' => $row['value_name'],
            ];
        }
        return array_values($grouped);
    }

    /**
     * Chuyển variant_key ('7_13_16') thành label đọc được ('Bạc, 256GB SSD, 8GB').
     * Dùng nội bộ cho getVariants() và getVariantById().
     */
    private function resolveVariantLabel(string $variantKey): string {
        if ($variantKey === 'default' || $variantKey === '') return 'Mặc định';

        $ids = array_map('intval', explode('_', $variantKey));
        if (empty($ids)) return '';

        $idList = implode(',', $ids);
        $rows = $this->fetchAll("
            SELECT av.value_name, a.attribute_name
            FROM   attribute_values av
            JOIN   attributes a ON a.attribute_id = av.attribute_id
            WHERE  av.value_id IN ($idList)
            ORDER  BY a.attribute_id
        ");
        return implode(', ', array_column($rows, 'value_name'));
    }

    // ─── Danh mục ─────────────────────────────────────────────────────────────

    public function getAllCategories(): array {
        return $this->fetchAll("
            SELECT category_id, category_name
            FROM   categories WHERE parent_id IS NULL
            ORDER  BY category_name
        ");
    }

    public function getCategoriesWithParent(): array {
        return $this->getAllCategories();
    }

    public function getSubCategories(int $parentId): array {
        $pid = (int)$parentId;
        return $this->fetchAll("
            SELECT category_id, category_name
            FROM   categories WHERE parent_id = $pid
            ORDER  BY category_name
        ");
    }

    public function getCategoryName(int $categoryId): string {
        $result = $this->fetchOne("
            SELECT category_name FROM categories WHERE category_id = $categoryId
        ");
        return $result ? $result['category_name'] : 'Uncategorized';
    }

    public function getAllCategoriesFlat(): array {
        return $this->fetchAll("
            SELECT c.category_id, c.category_name, c.parent_id,
                   pc.category_name AS parent_name
            FROM   categories c
            LEFT JOIN categories pc ON c.parent_id = pc.category_id
            ORDER  BY COALESCE(c.parent_id, c.category_id), c.category_id
        ");
    }

    // ─── Homepage featured ─────────────────────────────────────────────────────

    public function getHomepageProducts(): array {
        return $this->fetchAll("
            SELECT hp.id, hp.product_id, hp.category_id, hp.sort_order,
                   p.product_name, pi.image_url,
                   MIN(pv.price) AS min_price, MAX(pv.price) AS max_price
            FROM   homepage_products hp
            JOIN   products p ON p.product_id = hp.product_id
            LEFT JOIN product_images pi
                   ON pi.product_id = p.product_id AND pi.is_primary = 1
            LEFT JOIN product_variants pv
                   ON pv.product_id = p.product_id AND pv.is_active = 1
            GROUP  BY hp.id
            ORDER  BY hp.sort_order ASC
        ");
    }

    public function getHomepageCategories(): array {
        return $this->fetchAll("
            SELECT hc.id, hc.category_id, hc.sort_order,
                   c.category_name, c.description
            FROM   homepage_categories hc
            JOIN   categories c ON c.category_id = hc.category_id
            WHERE  c.parent_id IS NULL
            ORDER  BY hc.sort_order ASC
        ");
    }

    public function getHomepageCategoriesWithProducts(): array {
        $categories = $this->fetchAll("
            SELECT hc.id, hc.category_id, hc.sort_order,
                   c.category_name, c.description
            FROM   homepage_categories hc
            JOIN   categories c ON c.category_id = hc.category_id
            WHERE  c.parent_id IS NULL
            ORDER  BY hc.sort_order ASC
        ");

        foreach ($categories as &$cat) {
            $catId = (int)$cat['category_id'];
            $cat['products'] = $this->fetchAll("
                SELECT hp.id AS hp_id, hp.product_id, hp.sort_order,
                       p.product_name, p.category_id, pi.image_url,
                       pc.category_name  AS prod_cat_name,
                       pc.parent_id      AS prod_parent_id,
                       ppc.category_name AS parent_cat_name,
                       MIN(pv.price)          AS min_price,
                       MAX(pv.price)          AS max_price,
                       SUM(pv.stock_quantity) AS total_stock
                FROM   homepage_products hp
                JOIN   products p  ON p.product_id = hp.product_id
                LEFT JOIN product_images pi
                       ON pi.product_id = p.product_id AND pi.is_primary = 1
                LEFT JOIN product_variants pv
                       ON pv.product_id = p.product_id AND pv.is_active = 1
                JOIN   categories pc  ON pc.category_id = p.category_id
                LEFT JOIN categories ppc ON ppc.category_id = pc.parent_id
                WHERE  p.is_active = 1 AND hp.category_id = $catId
                GROUP  BY hp.id
                ORDER  BY hp.sort_order ASC
            ");
        }
        return $categories;
    }

    public function addHomepageProduct(int $productId, int $sortOrder = 0): bool {
        $this->query("
            INSERT INTO homepage_products (product_id, sort_order)
            VALUES ($productId, $sortOrder)
            ON DUPLICATE KEY UPDATE sort_order = $sortOrder
        ");
        return true;
    }

    public function addHomepageCategory(int $categoryId, int $sortOrder = 0): bool {
        $this->query("
            INSERT INTO homepage_categories (category_id, sort_order)
            VALUES ($categoryId, $sortOrder)
            ON DUPLICATE KEY UPDATE sort_order = $sortOrder
        ");
        return true;
    }

    public function updateHomepageProductOrder(int $id, int $sortOrder): bool {
        $this->query("UPDATE homepage_products SET sort_order = $sortOrder WHERE id = $id");
        return true;
    }

    public function updateHomepageCategoryOrder(int $id, int $sortOrder): bool {
        $this->query("UPDATE homepage_categories SET sort_order = $sortOrder WHERE id = $id");
        return true;
    }

    public function removeHomepageProduct(int $id): bool {
        $this->query("DELETE FROM homepage_products WHERE id = $id");
        return true;
    }

    public function removeHomepageCategory(int $id): bool {
        $this->query("DELETE FROM homepage_categories WHERE id = $id");
        return true;
    }

    public function getAvailableProducts(): array {
        return $this->fetchAll("
            SELECT p.product_id, p.product_name, pi.image_url,
                   MIN(pv.price) AS min_price, MAX(pv.price) AS max_price
            FROM   products p
            LEFT JOIN product_images pi
                   ON pi.product_id = p.product_id AND pi.is_primary = 1
            LEFT JOIN product_variants pv
                   ON pv.product_id = p.product_id AND pv.is_active = 1
            WHERE  p.is_active = 1
              AND  p.product_id NOT IN (SELECT product_id FROM homepage_products)
            GROUP  BY p.product_id
            ORDER  BY p.product_name ASC
        ");
    }

    public function getAvailableCategories(): array {
        return $this->fetchAll("
            SELECT c.category_id, c.category_name FROM categories c
            WHERE  c.parent_id IS NULL
              AND  c.category_id NOT IN (SELECT category_id FROM homepage_categories)
            ORDER  BY c.category_name ASC
        ");
    }

    // =========================================================================
    // ADMIN — Product CRUD
    // =========================================================================

    /** Tất cả sản phẩm (kể cả inactive) cho trang danh sách admin */
    public function getAllAdmin(): array {
        return $this->fetchAll("
            SELECT p.product_id, p.product_name, p.slug, p.is_active,
                   p.category_id, p.created_at,
                   c.category_name,
                   pc.category_id   AS parent_cat_id,
                   pc.category_name AS parent_cat_name,
                   COUNT(DISTINCT pv.variant_id)       AS variant_count,
                   MIN(pv.price)                       AS min_price,
                   MAX(pv.price)                       AS max_price,
                   COALESCE(SUM(pv.stock_quantity), 0) AS total_stock,
                   (
                       SELECT pi.image_url
                       FROM   product_images pi
                       WHERE  pi.product_id = p.product_id AND pi.is_primary = 1
                       LIMIT  1
                   ) AS image_url
            FROM   products p
            LEFT JOIN categories c  ON c.category_id = p.category_id
            LEFT JOIN categories pc ON pc.category_id = c.parent_id
            LEFT JOIN product_variants pv ON pv.product_id = p.product_id
            GROUP  BY p.product_id
            ORDER  BY p.created_at DESC
        ");
    }

    /** Chi tiết sản phẩm cho admin (kể cả inactive) */
    public function getByIdAdmin(int $id): ?array {
        $row = $this->fetchOne("
            SELECT p.*, c.category_name
            FROM   products p
            LEFT JOIN categories c ON c.category_id = p.category_id
            WHERE  p.product_id = $id
        ");
        return $row ?: null;
    }

    /** Tạo sản phẩm mới — trả về product_id */
    public function createProduct(array $data): int {
        $name     = $this->escape($data['product_name']);
        $slug     = $this->escape($data['slug'] ?? $this->makeSlug($data['product_name']));
        $desc     = $this->escape($data['description'] ?? '');
        $catId    = !empty($data['category_id']) ? (int)$data['category_id'] : 'NULL';
        $isActive = (int)($data['is_active'] ?? 1);

        $this->query("
            INSERT INTO products (category_id, product_name, description, slug, is_active)
            VALUES ($catId, '$name', '$desc', '$slug', $isActive)
        ");
        return (int)$this->lastInsertId();
    }

    /** Cập nhật sản phẩm */
    public function updateProduct(int $id, array $data): void {
        $name     = $this->escape($data['product_name']);
        $slug     = $this->escape($data['slug'] ?? $this->makeSlug($data['product_name']));
        $desc     = $this->escape($data['description'] ?? '');
        $catId    = !empty($data['category_id']) ? (int)$data['category_id'] : 'NULL';
        $isActive = (int)($data['is_active'] ?? 1);

        $this->query("
            UPDATE products
            SET    product_name = '$name',
                   description  = '$desc',
                   slug         = '$slug',
                   category_id  = $catId,
                   is_active    = $isActive
            WHERE  product_id = $id
        ");
    }

    /** Xóa mềm (ẩn sản phẩm) */
    public function softDeleteProduct(int $id): void {
        $this->query("UPDATE products SET is_active = 0 WHERE product_id = $id");
    }

    /** Kiểm tra slug đã tồn tại chưa */
    public function slugExists(string $slug, int $excludeId = 0): bool {
        $slug = $this->escape($slug);
        $row  = $this->fetchOne("
            SELECT COUNT(*) AS cnt FROM products
            WHERE  slug = '$slug' AND product_id != $excludeId
        ");
        return (int)($row['cnt'] ?? 0) > 0;
    }

    // ─── Admin: Variants ──────────────────────────────────────────────────────

    /**
     * Lấy variants của sản phẩm cho trang admin (bao gồm inactive).
     * Trả về attribute_label đọc được để hiển thị trong bảng.
     */
    public function getVariantsAdmin(int $productId): array {
        $pid = (int)$productId;
        $variants = $this->fetchAll("
            SELECT * FROM product_variants
            WHERE  product_id = $pid
            ORDER  BY variant_id
        ");
        foreach ($variants as &$v) {
            $v['attribute_label'] = $this->resolveVariantLabel($v['variant_key']);
        }
        return $variants;
    }

    /**
     * Tạo variant mới.
     *
     * @param array $data  Phải có: product_id, value_ids (mảng), price, stock_quantity
     *                     Tuỳ chọn: sku, is_active
     */
    public function createVariant(array $data): int {
        $pid      = (int)$data['product_id'];
        $key      = $this->escape($this->buildVariantKey($data['value_ids'] ?? []));
        $sku      = !empty($data['sku']) ? "'" . $this->escape($data['sku']) . "'" : 'NULL';
        $price    = (float)($data['price'] ?? 0);
        $stock    = (int)($data['stock_quantity'] ?? 0);
        $isActive = (int)($data['is_active'] ?? 1);

        $this->query("
            INSERT INTO product_variants
                   (product_id, variant_key, sku, price, stock_quantity, is_active)
            VALUES ($pid, '$key', $sku, $price, $stock, $isActive)
        ");
        return (int)$this->lastInsertId();
    }

    /**
     * Cập nhật variant.
     *
     * @param array $data  Có thể có value_ids để rebuild variant_key.
     *                     Nếu không có value_ids thì giữ nguyên variant_key cũ.
     */
    public function updateVariant(int $variantId, array $data): void {
        $vid      = (int)$variantId;
        $sku      = !empty($data['sku']) ? "'" . $this->escape($data['sku']) . "'" : 'NULL';
        $price    = (float)($data['price'] ?? 0);
        $stock    = (int)($data['stock_quantity'] ?? 0);
        $isActive = (int)($data['is_active'] ?? 1);

        // Chỉ rebuild variant_key nếu value_ids được truyền vào
        $keyClause = '';
        if (isset($data['value_ids']) && is_array($data['value_ids'])) {
            $key       = $this->escape($this->buildVariantKey($data['value_ids']));
            $keyClause = "variant_key = '$key',";
        }

        $this->query("
            UPDATE product_variants
            SET    $keyClause
                   sku            = $sku,
                   price          = $price,
                   stock_quantity = $stock,
                   is_active      = $isActive
            WHERE  variant_id = $vid
        ");
    }

    /** Xóa variant */
    public function deleteVariant(int $variantId): void {
        $this->query("DELETE FROM product_variants WHERE variant_id = $variantId");
    }

    // ─── Admin: Images ────────────────────────────────────────────────────────

    public function addProductImage(int $productId, string $url, int $isPrimary = 0): void {
        $url       = $this->escape($url);
        $sortOrder = $isPrimary ? 1 : 99;
        $this->query("
            INSERT INTO product_images (product_id, image_url, is_primary, sort_order)
            VALUES ($productId, '$url', $isPrimary, $sortOrder)
        ");
    }

    public function updatePrimaryImage(int $productId, string $url): void {
        $url = $this->escape($url);
        $this->query("UPDATE product_images SET is_primary = 0 WHERE product_id = $productId");
        $existing = $this->fetchOne("
            SELECT image_id FROM product_images
            WHERE  product_id = $productId AND image_url = '$url'
        ");
        if ($existing) {
            $this->query("
                UPDATE product_images SET is_primary = 1
                WHERE  image_id = {$existing['image_id']}
            ");
        } else {
            $this->addProductImage($productId, $url, 1);
        }
    }

    // ─── Helper: slug ─────────────────────────────────────────────────────────

    public function makeSlug(string $name): string {
        $map = [
            'à'=>'a','á'=>'a','â'=>'a','ã'=>'a','ä'=>'a',
            'è'=>'e','é'=>'e','ê'=>'e','ë'=>'e',
            'ì'=>'i','í'=>'i','î'=>'i','ï'=>'i',
            'ò'=>'o','ó'=>'o','ô'=>'o','õ'=>'o','ö'=>'o',
            'ù'=>'u','ú'=>'u','û'=>'u','ü'=>'u',
            'ý'=>'y','ÿ'=>'y','đ'=>'d',
            'À'=>'a','Á'=>'a','Â'=>'a','Ã'=>'a',
            'È'=>'e','É'=>'e','Ê'=>'e','Ë'=>'e',
            'Ì'=>'i','Í'=>'i','Î'=>'i','Ï'=>'i',
            'Ò'=>'o','Ó'=>'o','Ô'=>'o','Õ'=>'o',
            'Ù'=>'u','Ú'=>'u','Û'=>'u','Ü'=>'u',
            'Ý'=>'y','Đ'=>'d',
            'ắ'=>'a','ặ'=>'a','ằ'=>'a','ẳ'=>'a','ẵ'=>'a',
            'ấ'=>'a','ậ'=>'a','ầ'=>'a','ẩ'=>'a','ẫ'=>'a',
            'ế'=>'e','ệ'=>'e','ề'=>'e','ể'=>'e','ễ'=>'e',
            'ố'=>'o','ộ'=>'o','ồ'=>'o','ổ'=>'o','ỗ'=>'o',
            'ớ'=>'o','ợ'=>'o','ờ'=>'o','ở'=>'o','ỡ'=>'o',
            'ứ'=>'u','ự'=>'u','ừ'=>'u','ử'=>'u','ữ'=>'u',
            'ị'=>'i','ỉ'=>'i','ĩ'=>'i',
            'ỳ'=>'y','ỵ'=>'y','ỷ'=>'y','ỹ'=>'y',
            'ạ'=>'a','ả'=>'a','ọ'=>'o','ỏ'=>'o',
            'ụ'=>'u','ủ'=>'u','ẹ'=>'e','ẻ'=>'e','ẽ'=>'e',
        ];
        $name = strtr(mb_strtolower($name, 'UTF-8'), $map);
        $name = preg_replace('/[^a-z0-9\s-]/', '', $name);
        $name = preg_replace('/[\s-]+/', '-', trim($name));
        return $name;
    }
}