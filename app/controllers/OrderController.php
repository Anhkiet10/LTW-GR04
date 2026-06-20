<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/OrderModel2.php';

class OrderController extends Controller
{
    public function checkout()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /WEB_GR4/login");
            exit;
        }

        $this->render('orders/checkout', [
            'pageTitle' => 'Thanh toán'
        ]);
    }

    public function placeOrder()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /WEB_GR4/login");
            exit;
        }

        $cart = $_SESSION['cart'] ?? [];

        if (empty($cart)) {
            die("Giỏ hàng trống");
        }

        $addressId = $_POST['address_id'];

        $total = 0;

        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $model = new OrderModel2();

        $orderId = $model->createOrder(
            $_SESSION['user_id'] , // Đã sửa
            $addressId,
            $total
        );

        foreach ($cart as $item) {
            $model->addOrderItem(
                $orderId,
                $item['product_id'],
                $item['variant_id'] ?? null,
                $item['quantity'],
                $item['price']
            );
        }

        unset($_SESSION['cart']);

        header("Location: /WEB_GR4/orders/$orderId");
        exit;
    }

    public function history()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /WEB_GR4/login");
            exit;
        }

        $model = new OrderModel2();

        $orders = $model->getOrdersByUser(
            $_SESSION['user_id']  // Đã sửa
        );

        $this->render('orders/history', [
            'pageTitle' => 'Đơn hàng của tôi',
            'orders' => $orders
        ]);
    }

    public function detail($id)
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /WEB_GR4/login");
            exit;
        }

        // Bổ sung thêm dòng này để fake user_id nếu view hoặc hệ thống cần dùng ở trang chi tiết
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['user_id'] = 1; 
        }

        $model = new OrderModel2();

        $order = $model->getOrderById($id);

        if (!$order) {
            die("Không tìm thấy đơn hàng");
        }

        $items = $model->getOrderItems($id);

        $this->render('orders/detail', [
            'pageTitle' => 'Chi tiết đơn hàng',
            'order' => $order,
            'items' => $items
        ]);
    }
}