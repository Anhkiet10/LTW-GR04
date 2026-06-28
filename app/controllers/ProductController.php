<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/ProductModel.php';

class ProductController extends Controller {

    public function index() {
        $model = new ProductModel();
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $category = isset($_GET['category']) ? (int)$_GET['category'] : 0;

        $categoryName = '';
        if (!empty($search)) {
            $products = $model->search($search, 100);
            $pageTitle = "Kết quả tìm kiếm: " . htmlspecialchars($search);
        } elseif ($category > 0) {
            $products = $model->getByCategory($category, 100);
            $categoryName = $model->getCategoryName($category);
            $pageTitle = htmlspecialchars($categoryName);
        } else {
            $products = $model->getAll(100);
            $pageTitle = 'Tất cả sản phẩm';
        }

        $categories = $model->getCategoriesWithParent();

        $this->render('products/index', [
            'pageTitle' => $pageTitle,
            'products'  => $products,
            'search'    => $search,
            'category'  => $category,
            'categoryName' => $categoryName,
            'categories' => $categories,
        ]);
    }

    public function detail($id) {
        $model = new ProductModel();
        $product = $model->getById($id);
        if (!$product) {
            $this->redirect('/WEB_GR4/');
        }

        $images = $model->getImages($product['product_id']);
        $variants = $model->getVariants($product['product_id']);
        $attributes = $model->getAttributesForProduct($product['product_id']);
        $categoryName = $model->getCategoryName($product['category_id']);

        $this->render('products/detail', [
            'pageTitle' => $product['product_name'],
            'product'   => $product,
            'images'    => $images,
            'variants'  => $variants,
            'attributes'=> $attributes,
            'categoryName' => $categoryName,
            'categories' => $model->getCategoriesWithParent(),
        ]);
    }

    public function search() {
        header('Content-Type: application/json; charset=utf-8');

        $q = isset($_GET['q']) ? trim($_GET['q']) : '';

        if (strlen($q) < 1) {
            echo json_encode([]);
            exit;
        }

        $model = new ProductModel();
        $results = $model->search($q, 6);
        $suggestions = [];

        foreach ($results as $row) {
            $price = $row['min_price'];
            if ($row['min_price'] != $row['max_price']) {
                $price = $row['min_price'] . ' - ' . $row['max_price'];
            }
            $suggestions[] = [
                'id'    => $row['product_id'],
                'name'  => $row['product_name'],
                'price' => number_format($price, 0, ',', '.') . 'đ',
                'image' => $row['image_url'] ? $row['image_url'] : ''
            ];
        }

        echo json_encode($suggestions, JSON_UNESCAPED_UNICODE);
        exit;
    }
    // ----------------------------------------------------------------
    public function buyNow()
    {
        header('Content-Type: application/json; charset=utf-8');
    
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }
    
        $body = json_decode(file_get_contents('php://input'), true);
    
        $productId  = (int)($body['product_id']  ?? 0);
        $variantId  = !empty($body['variant_id']) ? (int)$body['variant_id'] : null;
        $quantity   = max(1, (int)($body['quantity'] ?? 1));
        $price      = (float)($body['price'] ?? 0);
        $productName = trim($body['product_name'] ?? '');
        $variantKey  = trim($body['variant_key']  ?? '');
        $imageUrl    = trim($body['image_url']    ?? '');
    
        if (!$productId || $price <= 0) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            exit;
        }
    
        // Nếu đã đăng nhập → lưu vào session rồi chuyển thẳng sang trang preview
        if (isset($_SESSION['user_id'])) {
            // Lưu tạm vào session để preview() đọc
            $_SESSION['buynow'] = [
                'product_id'    => $productId,
                'variant_id'    => $variantId,
                'quantity'      => $quantity,
                'price_snapshot'=> $price,
                'product_name'  => $productName,
                'variant_key'   => $variantKey,
                'variant_label' => trim($body['variant_label'] ?? ''),
                'image_url'     => $imageUrl,
            ];
            echo json_encode(['success' => true, 'redirect' => '/WEB_GR4/orders/buynow-preview']);
            exit;
        }
    
        // Khách vãng lai → lưu vào session guest_cart (chỉ 1 sản phẩm)
        $_SESSION['guest_cart'] = [[
            'product_id'     => $productId,
            'variant_id'     => $variantId,
            'quantity'       => $quantity,
            'price_snapshot' => $price,
            'product_name'   => $productName,
            'variant_key'    => $variantKey,
            'image_url'      => $imageUrl,
        ]];
    
        echo json_encode([
            'success'  => true,
            'redirect' => '/WEB_GR4/orders/guest-checkout',
        ]);
        exit;
    }
}
?>