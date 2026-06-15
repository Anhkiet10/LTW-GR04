<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/ProductModelAdmin.php';

class AdminProductController extends Controller {

    private ProductModelAdmin $model;

    public function __construct() {
        ob_start(); // bắt mọi output rác (warning/notice) trước khi echo JSON
        $this->model = new ProductModelAdmin();
    }

    /**
     * Xóa buffer rác, set header JSON, và echo response — dùng thay cho mọi echo json_encode().
     */
    private function json(array $data, int $status = 200): void {
        ob_end_clean();
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    // ─── GET /admin/products ──────────────────────────────────────────────────────
    public function index(): void {
        $stats      = $this->model->getStats();
        $products   = $this->model->getAllForAdmin();
        $categories = $this->model->getAllCategories();

        $this->render('admin/Products', [
            'total'          => $stats['total']    ?? 0,
            'active'         => $stats['active']   ?? 0,
            'hidden'         => $stats['hidden']   ?? 0,
            'noStock'        => $stats['no_stock'] ?? 0,
            'products'       => $products,
            'categories'     => $categories,
            'attributeTypes' => $this->model->getAllAttributeValues(),
        ]);
    }

    // ─── GET /admin/products/getProduct?id=X ─────────────────────────────────────
    public function getProduct(): void {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'ID không hợp lệ.']);
            return;
        }

        $product = $this->model->getById($id);
        if (!$product) {
            $this->json(['success' => false, 'message' => 'Không tìm thấy sản phẩm.']);
            return;
        }

        $this->json([
            'success'        => true,
            'product'        => $product,
            'variants'       => $this->model->getVariants($id),
            'images'         => $this->model->getImages($id),
            'attributeTypes' => $this->model->getAllAttributeValues(),
        ]);
    }

    // ─── POST /admin/products/store ───────────────────────────────────────────────
    public function store(): void {
        $name = trim($_POST['product_name'] ?? '');
        if ($name === '') {
            $this->json(['success' => false, 'message' => 'Tên sản phẩm không được trống.']);
            return;
        }

        try {
            $productId = $this->model->create($_POST);
            $this->saveVariants($productId);
            $this->handleImageUpload($productId);

            $this->json([
                'success'    => true,
                'message'    => 'Thêm sản phẩm thành công!',
                'product_id' => $productId,
            ]);
        } catch (\Throwable $e) {
            $this->json([
                'success' => false,
                'message' => 'Lỗi khi lưu sản phẩm: ' . $e->getMessage(),
            ]);
        }
    }

    // ─── POST /admin/products/update ─────────────────────────────────────────────
    public function update(): void {
        $id   = (int)($_POST['product_id'] ?? 0);
        $name = trim($_POST['product_name'] ?? '');

        if ($id <= 0 || $name === '') {
            $this->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
            return;
        }
        if (!$this->model->getById($id)) {
            $this->json(['success' => false, 'message' => 'Không tìm thấy sản phẩm.']);
            return;
        }

        try {
            $this->model->update($id, $_POST);
            $this->saveVariants($id);
            $this->handleImageUpload($id);

            $this->json(['success' => true, 'message' => 'Cập nhật sản phẩm thành công!']);
        } catch (\Throwable $e) {
            $this->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật sản phẩm: ' . $e->getMessage(),
            ]);
        }
    }

    // ─── POST /admin/products/delete ─────────────────────────────────────────────
    public function delete(): void {
        $id = (int)($_POST['product_id'] ?? 0);
        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'ID không hợp lệ.']);
            return;
        }
        if (!$this->model->getById($id)) {
            $this->json(['success' => false, 'message' => 'Không tìm thấy sản phẩm.']);
            return;
        }

        try {
            $this->model->delete($id);
            $this->json(['success' => true, 'message' => 'Đã xóa sản phẩm.']);
        } catch (\Throwable $e) {
            $this->json([
                'success' => false,
                'message' => 'Lỗi khi xóa sản phẩm: ' . $e->getMessage(),
            ]);
        }
    }

    // ─── POST /admin/products/deleteVariant ──────────────────────────────────────
    public function deleteVariant(): void {
        $vid = (int)($_POST['variant_id'] ?? 0);
        if ($vid <= 0) {
            $this->json(['success' => false, 'message' => 'ID biến thể không hợp lệ.']);
            return;
        }

        try {
            $this->model->deleteVariant($vid);
            $this->json(['success' => true, 'message' => 'Đã xóa biến thể.']);
        } catch (\Throwable $e) {
            $this->json([
                'success' => false,
                'message' => 'Lỗi khi xóa biến thể: ' . $e->getMessage(),
            ]);
        }
    }

    // ─── GET /admin/products/getAttributes ───────────────────────────────────────
    public function getAttributes(): void {
        $this->json([
            'success'        => true,
            'attributeTypes' => $this->model->getAllAttributeValues(),
        ]);
    }


    // ─── POST /admin/products/deleteAttribute ────────────
    public function deleteAttribute(): void {
        $attrId = (int)($_POST['attribute_id'] ?? 0);
        if ($attrId <= 0) {
            $this->json(['success' => false, 'message' => 'ID không hợp lệ.']);
            return;
        }
        if ($this->model->attributeInUse($attrId)) {
            $this->json(['success' => false, 'message' => 'Không thể xóa: thế loại này đang được dùng bởi một hoặc nhiều biến thể sản phẩm.']);
            return;
        }
        try {
            $this->model->deleteAttribute($attrId);
            $this->json([
                'success'        => true,
                'message'        => 'Đã xóa thế loại thuộc tính.',
                'attributeTypes' => $this->model->getAllAttributeValues(),
            ]);
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'message' => 'Lỗi khi xóa: ' . $e->getMessage()]);
        }
    }

    // ─── POST /admin/products/deleteAttributeValue ───────────
    public function deleteAttributeValue(): void {
        $valueId = (int)($_POST['value_id'] ?? 0);
        if ($valueId <= 0) {
            $this->json(['success' => false, 'message' => 'ID không hợp lệ.']);
            return;
        }
        if ($this->model->attributeValueInUse($valueId)) {
            $this->json(['success' => false, 'message' => 'Không thể xóa: giá trị này đang được dùng bởi một hoặc nhiều biến thể sản phẩm.']);
            return;
        }
        try {
            $this->model->deleteAttributeValue($valueId);
            $this->json([
                'success'        => true,
                'message'        => 'Đã xóa giá trị thuộc tính.',
                'attributeTypes' => $this->model->getAllAttributeValues(),
            ]);
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'message' => 'Lỗi khi xóa: ' . $e->getMessage()]);
        }
    }

    // ─── POST /admin/products/createAttribute ────────────────────────────────────
    public function createAttribute(): void {
        $name = trim($_POST['attribute_name'] ?? '');
        if ($name === '') {
            $this->json(['success' => false, 'message' => 'Tên thể loại không được trống.']);
            return;
        }
        if ($this->model->attributeNameExists($name)) {
            $this->json(['success' => false, 'message' => 'Thể loại thuộc tính này đã tồn tại.']);
            return;
        }

        try {
            $id = $this->model->createAttribute($name);
            $this->json([
                'success'        => true,
                'message'        => 'Đã thêm thể loại thuộc tính.',
                'attribute'      => [
                    'attribute_id'   => $id,
                    'attribute_name' => $name,
                    'values'         => [],
                ],
                'attributeTypes' => $this->model->getAllAttributeValues(),
            ]);
        } catch (\Throwable $e) {
            $this->json([
                'success' => false,
                'message' => 'Lỗi khi thêm thể loại: ' . $e->getMessage(),
            ]);
        }
    }

    // ─── POST /admin/products/createAttributeValue ───────────────────────────────
    public function createAttributeValue(): void {
        $attrId = (int)($_POST['attribute_id'] ?? 0);
        $name   = trim($_POST['value_name'] ?? '');

        if ($attrId <= 0 || $name === '') {
            $this->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
            return;
        }
        if ($this->model->attributeValueExists($attrId, $name)) {
            $this->json(['success' => false, 'message' => 'Giá trị thuộc tính này đã tồn tại.']);
            return;
        }

        try {
            $valueId = $this->model->createAttributeValue($attrId, $name);
            $this->json([
                'success'        => true,
                'message'        => 'Đã thêm giá trị thuộc tính.',
                'value'          => [
                    'value_id'   => $valueId,
                    'value_name' => $name,
                ],
                'attributeTypes' => $this->model->getAllAttributeValues(),
            ]);
        } catch (\Throwable $e) {
            $this->json([
                'success' => false,
                'message' => 'Lỗi khi thêm giá trị: ' . $e->getMessage(),
            ]);
        }
    }

    // ─── Helper: lưu danh sách variants từ POST ───────────────────────────────────
    private function saveVariants(int $productId): void {
        $variantIds = $_POST['variant_id'] ?? [];
        $skus       = $_POST['sku']        ?? [];
        $prices     = $_POST['price']      ?? [];
        $stocks     = $_POST['stock']      ?? [];
        $attrValues = $_POST['attr_value'] ?? [];

        // Checkbox variant_active[] chỉ gửi khi checked VÀ không giữ index gốc.
        // Dùng variant_active_index[] để biết chính xác row nào được check.
        // Fallback: dùng hidden field variant_active_map[] nếu JS đã sửa.
        // Ở đây dùng cách an toàn: JS phải gửi kèm hidden field is_active[]
        $isActives = $_POST['is_active'] ?? [];

        $count = count($prices);
        for ($i = 0; $i < $count; $i++) {
            $valueIds = [];
            foreach ($attrValues as $values) {
                if (isset($values[$i]) && $values[$i] !== '') {
                    $valueIds[] = (int)$values[$i];
                }
            }

            $variantData = [
                'variant_id' => (int)($variantIds[$i] ?? 0),
                'sku'        => $skus[$i]        ?? '',
                'price'      => $prices[$i]      ?? 0,
                'stock'      => $stocks[$i]      ?? 0,
                'is_active'  => isset($isActives[$i]) ? (int)$isActives[$i] : 1,
                'value_ids'  => $valueIds,
            ];
            $this->model->upsertVariant($productId, $variantData);
        }
    }

    // ─── Helper: upload ảnh đại diện ─────────────────────────────────────────────
    private function handleImageUpload(int $productId): void {
        $file = $_FILES['image'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK || $file['size'] === 0) {
            return;
        }

        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file['type'], $allowed)) {
            return;
        }

        $uploadDir = __DIR__ . '/../../public/assets/upload/img-product/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'product_' . $productId . '_' . time() . '.' . $ext;
        $destPath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            $urlPath = '/assets/upload/img-product/' . $fileName;
            $this->model->savePrimaryImage($productId, $urlPath);
        }
    }
}