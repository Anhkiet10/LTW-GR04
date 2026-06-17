<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/ProductModel.php';

class HomeController extends Controller { // xác định Controller là cha

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