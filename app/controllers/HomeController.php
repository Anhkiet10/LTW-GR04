<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/ProductModel.php';

class HomeController extends Controller { // xác định Controller là cha
    
        // public function __construct() {
        // $this->requireAdmin();// kiểm tra đã login với admin chưa.
        // ob_start(); // bắt mọi output rác (warning/notice) trước khi echo JSON
        // }
        // private function requireAdmin(): void {
        //     if (session_status() === PHP_SESSION_NONE) {
        //         session_start();
        //     }
        //     if (!isset($_SESSION['user_id'])) {
        //         header('Location: /WEB_GR4/login');
        //         exit;
        //     }
        //     if ($_SESSION['role'] !== 'customer') {
        //         header('Location: /WEB_GR4/logout');
        //         exit;
        //     }
        // }
    public function index() {
        $model = new ProductModel();



        $categoryProducts = []; //Khởi tạo một mảng rỗng. Mảng này sẽ dùng để chứa dữ liệu sau khi đã được gom nhóm và định dạng lại ở vòng lặp phía dưới.
        $homepageCategories = $model->getHomepageCategoriesWithProducts(); // lấy dữ liệu từ model

        if (!empty($homepageCategories)) {
            foreach ($homepageCategories as $cat) {
                $categoryProducts[$cat['category_name']] = [
                    'id' => $cat['category_id'],
                    'products' => $cat['products'] ?: []
                ];
            }
        }

        $allCategories = $model->getCategoriesWithParent();

        $this->render('home/index', [
            'pageTitle'        => 'Trang chủ',
            'categoryProducts' => $categoryProducts,
            'categories'       => $allCategories,
            'categoryName'     => '',
        ]);
    }
}
?>