<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/OrderModel.php';

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

    public function orders() {
        $orderModel = new OrderModel();

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $status = isset($_GET['status']) ? $_GET['status'] : null;
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        $orders = $orderModel->getAllOrders($perPage, $offset, $status);
        $totalOrders = $orderModel->getTotalOrders($status);
        $stats = $orderModel->getOrderStats();
        $totalPages = ceil($totalOrders / $perPage);

        $this->render('admin/Orders', [
            'pageTitle' => 'Quản lý đơn hàng',
            'orders' => $orders,
            'stats' => $stats,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalOrders' => $totalOrders,
            'currentStatus' => $status,
        ]);
    }

    public function orderDetail() {
        $orderModel = new OrderModel();

        if (!isset($_GET['id'])) {
            echo "Order not found";
            return;
        }

        $orderId = (int)$_GET['id'];
        $order = $orderModel->getOrderById($orderId);
        $items = $orderModel->getOrderItems($orderId);

        if (!$order) {
            echo "Order not found";
            return;
        }

        $this->render('admin/OrderDetail', [
            'pageTitle' => 'Chi tiết đơn hàng #' . $orderId,
            'order' => $order,
            'items' => $items,
        ]);
    }

    public function updateOrderStatus() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['orderId']) || !isset($input['status'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                exit;
            }

            $orderModel = new OrderModel();
            $orderId = (int)$input['orderId'];
            $status = $input['status'];

            $validStatuses = ['pending', 'paid', 'shipping', 'completed', 'cancelled'];
            if (!in_array($status, $validStatuses)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid status']);
                exit;
            }

            if ($orderModel->updateOrderStatus($orderId, $status)) {
                echo json_encode(['success' => true, 'message' => 'Order status updated successfully']);
            } else {
                throw new Exception('Failed to update order status');
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
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
