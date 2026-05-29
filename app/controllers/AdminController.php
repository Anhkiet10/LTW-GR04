<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/ProductModel.php';

class AdminController extends Controller {

    public function home() {
        $productModel = new ProductModel();

        $homepageProducts = $productModel->getHomepageProducts() ?: [];
        $homepageCategories = $productModel->getHomepageCategories() ?: [];
        $availableProducts = $productModel->getAvailableProducts() ?: [];
        $availableCategories = $productModel->getAvailableCategories() ?: [];
        $allCategories = $productModel->getAllCategories() ?: [];

        $this->render('admin/home', [
            'pageTitle' => 'Admin Dashboard - Quản lý trang chủ',
            'homepageCategoriesJson' => json_encode($homepageCategories, JSON_UNESCAPED_UNICODE),
            'homepageProductsJson'   => json_encode($homepageProducts, JSON_UNESCAPED_UNICODE),
            'availableProductsJson'  => json_encode($availableProducts, JSON_UNESCAPED_UNICODE),
            'availableCategoriesJson'=> json_encode($availableCategories, JSON_UNESCAPED_UNICODE),
            'allCategoriesJson'      => json_encode($allCategories, JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function saveHomepage() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input || !isset($input['categories']) || !isset($input['products'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid data format']);
                exit;
            }

            $db = Database::getConnection();

            if (!$db) {
                throw new Exception('Database connection failed');
            }

            $db->begin_transaction();

            $db->query("DELETE FROM homepage_categories");
            $stmtCat = $db->prepare("INSERT INTO homepage_categories (category_id, sort_order) VALUES (?, ?)");
            if (!$stmtCat) {
                throw new Exception("Prepare failed: " . $db->error);
            }

            foreach ($input['categories'] as $cat) {
                $catId = (int)$cat['category_id'];
                $sortOrder = (int)$cat['sort_order'];
                $stmtCat->bind_param('ii', $catId, $sortOrder);
                if (!$stmtCat->execute()) {
                    throw new Exception("Category insert failed: " . $stmtCat->error);
                }
            }
            $stmtCat->close();

            $db->query("DELETE FROM homepage_products");
            $stmtProd = $db->prepare("INSERT INTO homepage_products (product_id, category_id, sort_order) VALUES (?, ?, ?)");
            if (!$stmtProd) {
                throw new Exception("Prepare failed: " . $db->error);
            }

            foreach ($input['products'] as $prod) {
                $prodId = (int)$prod['product_id'];
                $catId = (int)$prod['category_id'];
                $sortOrder = (int)$prod['sort_order'];
                $stmtProd->bind_param('iii', $prodId, $catId, $sortOrder);
                if (!$stmtProd->execute()) {
                    throw new Exception("Product insert failed: " . $stmtProd->error);
                }
            }
            $stmtProd->close();

            $db->commit();
            echo json_encode(['success' => true, 'message' => 'Homepage configuration updated successfully']);

        } catch (Exception $e) {
            if (isset($db)) {
                $db->rollback();
            }
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}
?>
