<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/OrderModel.php';

class OrderController extends Controller
{
    public function checkout()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /WEB_GR4/login');
            exit;
        }

        $model = new OrderModel();
        $addresses = $model->getAddressesByUser($_SESSION['user_id']);

        $this->render('orders/checkout', [
            'pageTitle' => 'Thanh toan',
            'addresses' => $addresses
        ]);
    }

    public function placeOrder()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /WEB_GR4/login');
            exit;
        }

        $cart = $_SESSION['cart'] ?? [];

        if (empty($cart)) {
            die('Gio hang trong');
        }

        $addressId = $_POST['address_id'] ?? null;
        $total = 0;

        foreach ($cart as $item) {
            $price = $item['price'] ?? $item['price_snapshot'] ?? 0;
            $total += $price * $item['quantity'];
        }

        $model = new OrderModel();
        $orderId = $model->createOrder($_SESSION['user_id'], $addressId, $total);

        foreach ($cart as $item) {
            $model->addOrderItem(
                $orderId,
                $item['product_id'],
                $item['variant_id'] ?? null,
                $item['quantity'],
                $item['price'] ?? $item['price_snapshot'] ?? 0
            );
        }

        unset($_SESSION['cart']);

        header("Location: /WEB_GR4/orders/$orderId");
        exit;
    }

    public function history()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /WEB_GR4/login');
            exit;
        }

        $model = new OrderModel();
        $orders = $model->getOrdersByUser($_SESSION['user_id']);

        $this->render('orders/history', [
            'pageTitle' => 'Don hang cua toi',
            'orders' => $orders
        ]);
    }

    public function detail($id)
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /WEB_GR4/login');
            exit;
        }

        $model = new OrderModel();
        $order = $model->getOrderByIdForUser($id, $_SESSION['user_id']);

        if (!$order) {
            die('Khong tim thay don hang');
        }

        $items = $model->getOrderItems($id);

        $this->render('orders/detail', [
            'pageTitle' => 'Chi tiet don hang',
            'order' => $order,
            'items' => $items
        ]);
    }
}