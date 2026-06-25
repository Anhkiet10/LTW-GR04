<?php
require_once __DIR__ . '/../../core/Model.php';

/**
 * ProductModelAdmin — Dành riêng cho trang quản trị
 *
 * Thay đổi so với phiên bản cũ (schema v2):
 *  - upsertVariant() nhận thêm value_ids[] để build/rebuild variant_key
 *  - Bỏ toàn bộ tham chiếu đến bảng variant_attribute_values
 *  - Thêm getVariantAttributes() để admin xem được tên attribute của từng variant
 *  - Thêm getAllAttributeValues() để admin có thể chọn attribute khi tạo/sửa variant
 *  - delete() chỉ xóa product_images + product_variants trước (variant_attribute_values không còn)
 */
class ProductModelAdmin extends Model {

    // ─── Helper: build variant_key ────────────────────────────────────────────

    /**
     * Nhận vào mảng value_id bất kỳ thứ tự, trả về chuỗi đã sort tăng dần.
     * Đây là quy tắc duy nhất để tạo key — phải nhất quán ở mọi nơi.
     * Ví dụ: [16, 7, 13] → '7_13_16'
     */
    private function buildVariantKey(array $valueIds): string {
        if (empty($valueIds)) return 'default';
        $ids = array_map('intval', $valueIds);
        sort($ids, SORT_NUMERIC);
        return implode('_', $ids);
    }

    // ─── Danh sách sản phẩm admin (có lọc + phân trang) ───────────────────────

    /**
     * @param array $filters ['search' => string, 'category' => int|string, 'status' => '0'|'1'|'']
     * @param int   $page    trang hiện tại (bắt đầu từ 1)
     * @param int   $perPage số sản phẩm mỗi trang
     */
    public function getAllForAdmin(array $filters = [], int $page = 1, int $perPage = 8): array {
        $where   = $this->buildAdminWhere($filters);
        $page    = max(1, $page);
        $perPage = max(1, $perPage);
        $offset  = ($page - 1) * $perPage;

        return $this->fetchAll("
            SELECT
                p.product_id,
                p.product_name,
                p.slug,
                p.is_active,
                p.category_id,
                p.created_at,
                c.category_name,
                pc.category_id   AS parent_cat_id,
                pc.category_name AS parent_cat_name,
                COUNT(DISTINCT pv.variant_id)        AS variant_count,
                MIN(pv.price)                        AS min_price,
                MAX(pv.price)                        AS max_price,
                COALESCE(SUM(pv.stock_quantity), 0)  AS total_stock,
                (
                    SELECT pi.image_url
                    FROM   product_images pi
                    WHERE  pi.product_id = p.product_id AND pi.is_primary = 1
                    LIMIT  1
                ) AS image_url
            FROM   products p
            LEFT JOIN categories c  ON c.category_id  = p.category_id
            LEFT JOIN categories pc ON pc.category_id = c.parent_id
            LEFT JOIN product_variants pv ON pv.product_id = p.product_id
            $where
            GROUP  BY p.product_id
            ORDER  BY p.created_at DESC
            LIMIT  $perPage OFFSET $offset
        ");
    }

    /**
     * Đếm tổng số sản phẩm thỏa điều kiện lọc — dùng để tính $totalPages.
     */
    public function countForAdmin(array $filters = []): int {
        $where = $this->buildAdminWhere($filters);

        $row = $this->fetchOne("
            SELECT COUNT(DISTINCT p.product_id) AS cnt
            FROM   products p
            LEFT JOIN categories c  ON c.category_id  = p.category_id
            LEFT JOIN categories pc ON pc.category_id = c.parent_id
            $where
        ");
        return (int)($row['cnt'] ?? 0);
    }

    /**
     * Build mệnh đề WHERE dùng chung cho getAllForAdmin() và countForAdmin().
     * search   → khớp tên sản phẩm HOẶC sku của bất kỳ biến thể nào.
     * category → khớp category_id của sản phẩm HOẶC category cha của nó
     *            (cho phép chọn 1 danh mục cha để lấy luôn các danh mục con).
     * status   → 0 hoặc 1, để trống = không lọc.
     */
    private function buildAdminWhere(array $filters): string {
        $conditions = [];

        $search = trim($filters['search'] ?? '');
        if ($search !== '') {
            $kw = $this->escape($search);
            $conditions[] = "(
                p.product_name LIKE '%$kw%'
                OR EXISTS (
                    SELECT 1 FROM product_variants pvs
                    WHERE  pvs.product_id = p.product_id AND pvs.sku LIKE '%$kw%'
                )
            )";
        }

        $category = $filters['category'] ?? '';
        if ($category !== '' && $category !== null) {
            $catId = (int)$category;
            $conditions[] = "(p.category_id = $catId OR pc.category_id = $catId)";
        }

        $status = $filters['status'] ?? '';
        if ($status !== '' && $status !== null) {
            $statusVal = (int)$status;
            $conditions[] = "p.is_active = $statusVal";
        }

        return $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
    }

    // ─── Thống kê nhanh ───────────────────────────────────────────────────────

    public function getStats(): array {
        return $this->fetchOne("
            SELECT
                COUNT(*)           AS total,
                SUM(is_active = 1) AS active,
                SUM(is_active = 0) AS hidden,
                SUM((
                    SELECT COALESCE(SUM(stock_quantity), 0)
                    FROM   product_variants pv
                    WHERE  pv.product_id = p.product_id
                ) = 0)             AS no_stock
            FROM products p
        ");
    }

    // ─── Danh mục ─────────────────────────────────────────────────────────────

    public function getAllCategories(): array {
        return $this->fetchAll("
            SELECT category_id, parent_id, category_name
            FROM   categories
            ORDER  BY parent_id IS NOT NULL, parent_id, category_id
        ");
    }

    // ─── Chi tiết sản phẩm ────────────────────────────────────────────────────

    public function getById(int $id): ?array {
        return $this->fetchOne("
            SELECT * FROM products WHERE product_id = $id
        ") ?: null;
    }

    // ─── Variants ─────────────────────────────────────────────────────────────

    /**
     * Lấy danh sách variants của sản phẩm kèm attribute_label đọc được.
     * Không còn JOIN variant_attribute_values — parse variant_key thay thế.
     */
    public function getVariants(int $productId): array {
        $pid      = (int)$productId;
        $variants = $this->fetchAll("
            SELECT * FROM product_variants
            WHERE  product_id = $pid
            ORDER  BY variant_id
        ");

        foreach ($variants as &$v) {
            $v['attribute_label'] = $this->resolveVariantLabel($v['variant_key']);
            $v['attributes']      = $this->getVariantAttributes((int)$v['variant_id']);
        }
        return $variants;
    }

    /**
     * Lấy attribute và value đính kèm một variant cụ thể.
     * Trả về mảng ['attribute_name' => ..., 'value_name' => ...] cho từng attribute.
     * Dùng để hiển thị chi tiết hoặc pre-fill form edit variant.
     */
    public function getVariantAttributes(int $variantId): array {
        // Lấy variant_key trước
        $row = $this->fetchOne("
            SELECT variant_key FROM product_variants WHERE variant_id = $variantId
        ");
        if (!$row || $row['variant_key'] === 'default') return [];

        $ids = array_map('intval', explode('_', $row['variant_key']));
        if (empty($ids)) return [];

        $idList = implode(',', $ids);
        return $this->fetchAll("
            SELECT a.attribute_id, a.attribute_name,
                   av.value_id,    av.value_name
            FROM   attribute_values av
            JOIN   attributes a ON a.attribute_id = av.attribute_id
            WHERE  av.value_id IN ($idList)
            ORDER  BY a.attribute_id
        ");
    }

    // ─── Attributes (cho form tạo/sửa variant) ────────────────────────────────

    /**
     * Lấy tất cả attributes kèm danh sách values.
     * Dùng để render các dropdown/checkbox khi admin tạo biến thể mới.
     *
     * Output:
     * [
     *   ['attribute_id'=>1, 'attribute_name'=>'Màu sắc', 'values'=>[
     *       ['value_id'=>6, 'value_name'=>'Đen'], ...
     *   ]],
     *   ...
     * ]
     */
    public function getAllAttributeValues(): array {
        $attrs = $this->fetchAll("
            SELECT attribute_id, attribute_name
            FROM   attributes
            ORDER  BY attribute_id
        ");

        $values = $this->fetchAll("
            SELECT value_id, attribute_id, value_name
            FROM   attribute_values
            ORDER  BY attribute_id, value_id
        ");

        $grouped = [];
        foreach ($attrs as $a) {
            $grouped[(int)$a['attribute_id']] = [
                'attribute_id'   => (int)$a['attribute_id'],
                'attribute_name' => $a['attribute_name'],
                'values'         => [],
            ];
        }
        foreach ($values as $v) {
            $aid = (int)$v['attribute_id'];
            if (!isset($grouped[$aid])) continue;
            $grouped[$aid]['values'][] = [
                'value_id'   => (int)$v['value_id'],
                'value_name' => $v['value_name'],
            ];
        }
        return array_values($grouped);
    }

    public function createAttribute(string $name): int {
        $nameEsc = $this->escape(trim($name));
        $this->query("
            INSERT INTO attributes (attribute_name)
            VALUES ('$nameEsc')
        ");
        return (int)$this->lastInsertId();
    }

    public function createAttributeValue(int $attributeId, string $valueName): int {
        $aid     = (int)$attributeId;
        $nameEsc = $this->escape(trim($valueName));
        $this->query("
            INSERT INTO attribute_values (attribute_id, value_name)
            VALUES ($aid, '$nameEsc')
        ");
        return (int)$this->lastInsertId();
    }

    public function attributeNameExists(string $name): bool {
        $nameEsc = $this->escape(trim($name));
        $row = $this->fetchOne("
            SELECT attribute_id FROM attributes
            WHERE  attribute_name = '$nameEsc'
            LIMIT  1
        ");
        return (bool)$row;
    }

    public function attributeValueExists(int $attributeId, string $valueName): bool {
        $aid     = (int)$attributeId;
        $nameEsc = $this->escape(trim($valueName));
        $row = $this->fetchOne("
            SELECT value_id FROM attribute_values
            WHERE  attribute_id = $aid AND value_name = '$nameEsc'
            LIMIT  1
        ");
        return (bool)$row;
    }

    // ─── Ảnh sản phẩm ─────────────────────────────────────────────────────────

    public function getImages(int $productId): array {
        return $this->fetchAll("
            SELECT * FROM product_images
            WHERE  product_id = $productId
            ORDER  BY is_primary DESC, sort_order
        ");
    }

    // ─── Tạo sản phẩm ─────────────────────────────────────────────────────────

    public function create(array $data): int {
        $name     = $this->escape($data['product_name']);
        $catId    = !empty($data['category_id']) ? (int)$data['category_id'] : 'NULL';
        $desc     = $this->escape($data['description'] ?? '');
        $slug     = $this->escape($this->makeSlug($data['product_name']));
        $isActive = isset($data['is_active']) ? 1 : 0;

        $this->query("
            INSERT INTO products (product_name, category_id, description, slug, is_active)
            VALUES ('$name', $catId, '$desc', '$slug', $isActive)
        ");
        return (int)$this->lastInsertId();
    }

    // ─── Cập nhật sản phẩm ───────────────────────────────────────────────────

    public function update(int $id, array $data): void {
        $name     = $this->escape($data['product_name']);
        $catId    = !empty($data['category_id']) ? (int)$data['category_id'] : 'NULL';
        $desc     = $this->escape($data['description'] ?? '');
        $slug     = $this->escape($this->makeSlug($data['product_name']));
        $isActive = isset($data['is_active']) ? 1 : 0;

        $this->query("
            UPDATE products
            SET    product_name = '$name',
                   category_id  = $catId,
                   description  = '$desc',
                   slug         = '$slug',
                   is_active    = $isActive
            WHERE  product_id = $id
        ");
    }

    // ─── Xóa sản phẩm ────────────────────────────────────────────────────────

    /**
     * Xóa sản phẩm cùng ảnh và variants.
     * Không còn cần xóa variant_attribute_values (bảng đã bị DROP).
     */
    public function delete(int $id): void {
        $this->query("DELETE FROM product_images   WHERE product_id = $id");
        $this->query("DELETE FROM product_variants WHERE product_id = $id");
        $this->query("DELETE FROM products         WHERE product_id = $id");
    }

    // ─── Upsert variant ───────────────────────────────────────────────────────

    /**
     * Tạo mới hoặc cập nhật variant.
     *
     * Trường bắt buộc trong $v:
     *   - value_ids  : array<int>  — danh sách value_id user chọn
     *   - price      : float
     *   - stock      : int
     * Tuỳ chọn:
     *   - variant_id : int  (> 0 → update, = 0 → insert)
     *   - sku        : string
     *   - is_active  : bool
     *
     * @return int  variant_id sau khi lưu
     */
    public function upsertVariant(int $productId, array $v): int {
        $key      = $this->escape($this->buildVariantKey($v['value_ids'] ?? []));
        $sku      = !empty($v['sku']) ? "'" . $this->escape($v['sku']) . "'" : 'NULL';
        $price    = (float)($v['price']    ?? 0);
        $stock    = (int)  ($v['stock']    ?? 0);
        $isActive = !empty($v['is_active']) ? 1 : 0;
        $vid      = (int)  ($v['variant_id'] ?? 0);

        if ($vid > 0) {
            // Kiểm tra variant_key mới có trùng với variant KHÁC của cùng product không
            $conflict = $this->fetchOne("
                SELECT variant_id FROM product_variants
                WHERE  product_id = $productId
                  AND  variant_key = '$key'
                  AND  variant_id  <> $vid
                LIMIT  1
            ");
            if ($conflict) {
                throw new \RuntimeException(
                    "Tổ hợp thuộc tính này đã tồn tại ở một biến thể khác (ID: {$conflict['variant_id']})."
                );
            }

            $this->query("
                UPDATE product_variants
                SET    variant_key     = '$key',
                       sku             = $sku,
                       price           = $price,
                       stock_quantity  = $stock,
                       is_active       = $isActive
                WHERE  variant_id = $vid AND product_id = $productId
            ");
            return $vid;
        }

        // Kiểm tra duplicate trước khi INSERT
        $exists = $this->fetchOne("
            SELECT variant_id FROM product_variants
            WHERE  product_id = $productId AND variant_key = '$key'
            LIMIT  1
        ");
        if ($exists) {
            throw new \RuntimeException(
                "Tổ hợp thuộc tính này đã tồn tại ở một biến thể khác (ID: {$exists['variant_id']})."
            );
        }

        $this->query("
            INSERT INTO product_variants
                   (product_id, variant_key, sku, price, stock_quantity, is_active)
            VALUES ($productId, '$key', $sku, $price, $stock, $isActive)
        ");
        return (int)$this->lastInsertId();
    }

    // ─── Xóa variant ─────────────────────────────────────────────────────────

    public function deleteVariant(int $variantId): void {
        $this->query("DELETE FROM product_variants WHERE variant_id = $variantId");
    }

    // ─── Xóa thể loại thuộc tính ─────────────────────────────────────────────

    /**
     * Kiểm tra xem attribute có đang được dùng bởi variant nào không.
     * variant_key chứa value_id dạng "7_13_16" — nếu bất kỳ value nào của attribute này
     * xuất hiện trong variant_key thì không cho xóa.
     */
    public function attributeInUse(int $attributeId): bool {
        $aid = (int)$attributeId;

        // Lấy tất cả value_id thuộc attribute này
        $valueRows = $this->fetchAll("
            SELECT value_id FROM attribute_values
            WHERE  attribute_id = $aid
        ");
        if (empty($valueRows)) return false; // không có values → không thể đang dùng

        $valueIds = array_map(fn($r) => (int)$r['value_id'], $valueRows);

        // Kiểm tra từng value_id có xuất hiện trong bất kỳ variant_key nào không
        foreach ($valueIds as $vid) {
            $row = $this->fetchOne("
                SELECT variant_id FROM product_variants
                WHERE  variant_key REGEXP '(^|_){$vid}(_|$)'
                LIMIT  1
            ");
            if ($row) return true;
        }
        return false;
    }

    /**
     * Kiểm tra xem một value cụ thể có đang được dùng không.
     */
    public function attributeValueInUse(int $valueId): bool {
        $vid = (int)$valueId;
        $row = $this->fetchOne("
            SELECT variant_id FROM product_variants
            WHERE  variant_key REGEXP '(^|_){$vid}(_|$)'
            LIMIT  1
        ");
        return (bool)$row;
    }

    public function deleteAttribute(int $attributeId): void {
        $aid = (int)$attributeId;
        $this->query("DELETE FROM attribute_values WHERE attribute_id = $aid");
        $this->query("DELETE FROM attributes       WHERE attribute_id = $aid");
    }

    public function deleteAttributeValue(int $valueId): void {
        $vid = (int)$valueId;
        $this->query("DELETE FROM attribute_values WHERE value_id = $vid");
    }

    // ─── Ảnh đại diện ────────────────────────────────────────────────────────

    public function savePrimaryImage(int $productId, string $url): void {
        $urlEsc = $this->escape($url);
        $this->query("UPDATE product_images SET is_primary = 0 WHERE product_id = $productId");

        $exist = $this->fetchOne("
            SELECT image_id FROM product_images
            WHERE  product_id = $productId AND image_url = '$urlEsc'
        ");
        if ($exist) {
            $this->query("
                UPDATE product_images SET is_primary = 1
                WHERE  image_id = {$exist['image_id']}
            ");
        } else {
            $this->query("
                INSERT INTO product_images (product_id, image_url, is_primary, sort_order)
                VALUES ($productId, '$urlEsc', 1, 0)
            ");
        }
    }

    // ─── Internal helpers ─────────────────────────────────────────────────────

    /**
     * Chuyển variant_key ('7_13_16') thành label đọc được ('Bạc, 256GB SSD, 8GB').
     */
    private function resolveVariantLabel(string $variantKey): string {
        if ($variantKey === 'default' || $variantKey === '') return 'Mặc định';

        $ids = array_map('intval', explode('_', $variantKey));
        if (empty($ids)) return '';

        $idList = implode(',', $ids);
        $rows   = $this->fetchAll("
            SELECT av.value_name
            FROM   attribute_values av
            JOIN   attributes a ON a.attribute_id = av.attribute_id
            WHERE  av.value_id IN ($idList)
            ORDER  BY a.attribute_id
        ");
        return implode(', ', array_column($rows, 'value_name'));
    }

    // ─── Lấy ảnh theo variant_id ─────────────────────────────────────────────
    public function getImagesByVariant(int $productId): array {
        $pid = (int)$productId;
        $rows = $this->fetchAll("
            SELECT image_id, product_id, variant_id, image_url, is_primary, sort_order
            FROM   product_images
            WHERE  product_id = $pid
            ORDER  BY variant_id IS NULL DESC,
                      variant_id,
                      is_primary DESC,
                      sort_order
        ");

        $grouped = ['_common' => []];
        foreach ($rows as $row) {
            $key = $row['variant_id'] !== null ? (int)$row['variant_id'] : '_common';
            $grouped[$key][] = $row;
        }
        return $grouped;
    }

    // ─── Lưu ảnh gắn với variant cụ thể ─────────────────────────────────────
    public function saveVariantImage(int $productId, int $variantId, string $url): void {
        $pid    = (int)$productId;
        $vid    = (int)$variantId;
        $urlEsc = $this->escape($url);

        $this->query("
            UPDATE product_images
            SET    is_primary = 0
            WHERE  product_id = $pid AND variant_id = $vid
        ");

        $exist = $this->fetchOne("
            SELECT image_id FROM product_images
            WHERE  product_id = $pid AND variant_id = $vid AND image_url = '$urlEsc'
        ");

        if ($exist) {
            $this->query("
                UPDATE product_images SET is_primary = 1
                WHERE  image_id = {$exist['image_id']}
            ");
        } else {
            $this->query("
                INSERT INTO product_images (product_id, variant_id, image_url, is_primary, sort_order)
                VALUES ($pid, $vid, '$urlEsc', 1, 0)
            ");
        }
    }

    private function makeSlug(string $name): string {
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