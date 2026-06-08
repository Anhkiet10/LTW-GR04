<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/ProductModel.php';

class HomeController extends Controller {

    public function index() {
        $model = new ProductModel();

        $categoryProducts = [];
        $homepageCategories = $model->getHomepageCategoriesWithProducts();

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