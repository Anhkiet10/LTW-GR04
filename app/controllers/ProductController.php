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
        $categoryName = $model->getCategoryName($product['category_id']);

        $this->render('products/detail', [
            'pageTitle' => $product['product_name'],
            'product'   => $product,
            'images'    => $images,
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
            $suggestions[] = [
                'id'    => $row['product_id'],
                'name'  => $row['product_name'],
                'price' => number_format($row['price'], 0, ',', '.') . 'đ',
                'image' => $row['image_url'] ? $row['image_url'] : ''
            ];
        }

        echo json_encode($suggestions, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
?>