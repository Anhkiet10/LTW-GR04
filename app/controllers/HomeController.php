<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/ProductModel.php';

class HomeController extends Controller {

    public function index() {
        $model = new ProductModel();

        $categories = $model->getCategoriesWithParent();

        $categoryProducts = [];
        if (!empty($categories)) {
            foreach ($categories as $cat) {
                $products = $model->getByCategory($cat['category_id'], 5);

                $categoryProducts[$cat['category_name']] = [
                    'id' => $cat['category_id'],
                    'products' => $products ?: []
                ];
            }
        }

        $this->render('home/index', [
            'pageTitle'        => 'Trang chủ',
            'categoryProducts' => $categoryProducts,
            'categories'       => $categories,
            'categoryName'     => '',
        ]);
    }
}
?>