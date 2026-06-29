<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/OrderModel.php';

class OrderController extends Controller
{
    // -------------------------------------------------------
    // Trạng thái đơn hàng hợp lệ theo DB
    // pending | confirmed | shipping | completed | cancelled
    // -------------------------------------------------------

    public function checkout()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /WEB_GR4/login');
            exit;
        }

        $model     = new OrderModel();
        $addresses = $model->getAddressesByUser($_SESSION['user_id']);

        $this->render('orders/checkout', [
            'pageTitle' => 'Thanh toán',
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
            die('Giỏ hàng trống');
        }

        $addressId = $_POST['address_id'] ?? null;
        $total     = 0;

        foreach ($cart as $item) {
            $price  = $item['price'] ?? $item['price_snapshot'] ?? 0;
            $total += $price * $item['quantity'];
        }

        $model   = new OrderModel();
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

        // Chuyển sang trang chọn phương thức thanh toán
        header("Location: /WEB_GR4/orders/pay/$orderId");
        exit;
    }

    public function history()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /WEB_GR4/login');
            exit;
        }

        $model  = new OrderModel();
        $orders = $model->getOrdersByUser($_SESSION['user_id']);

        $this->render('orders/history', [
            'pageTitle' => 'Đơn hàng của tôi',
            'orders'    => $orders
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
            http_response_code(404);
            die('Không tìm thấy đơn hàng');
        }

        $items = $model->getOrderItems($id);

        $this->render('orders/detail', [
            'pageTitle' => 'Chi tiết đơn hàng #' . $id,
            'order'     => $order,
            'items'     => $items
        ]);
    }

    // -------------------------------------------------------
    // Trang chọn phương thức thanh toán (GET /orders/pay/{id})
    // -------------------------------------------------------
    public function pay($orderId)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user_id'])) {
            header('Location: /WEB_GR4/login');
            exit;
        }

        $orderId = (int)$orderId;
        $model   = new OrderModel();
        $order   = $model->getOrderByIdForUser($orderId, $_SESSION['user_id']);

        if (!$order) {
            http_response_code(404);
            die('Không tìm thấy đơn hàng');
        }

        // Đã có payment → chuyển thẳng sang detail
        if (!empty($order['payment_method'])) {
            header("Location: /WEB_GR4/orders/$orderId");
            exit;
        }

        $items = $model->getOrderItems($orderId);

        $this->render('orders/payment', [
            'pageTitle' => 'Thanh toán đơn hàng #' . $orderId,
            'order'     => $order,
            'items'     => $items,
        ]);
    }

    // -------------------------------------------------------
    // Xác nhận thanh toán (POST /orders/confirmPayment)
    // Nhận JSON: { order_id, method }
    // -------------------------------------------------------
    public function confirmPayment() {
        header('Content-Type: application/json');

        $input   = json_decode(file_get_contents('php://input'), true);
        $orderId = isset($input['order_id']) ? (int)$input['order_id'] : 0;
        $method  = $input['method'] ?? null;

        if (!$orderId || !$method) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ!']);
            return;
        }

        $model = new OrderModel();

        if ($method === 'bank_transfer') {
            // Khách vừa bấm "Đã chuyển khoản" → chờ admin xác nhận
            $ok = $model->updatePaymentStatus($orderId, 'processing', 'bank_transfer');
        } elseif ($method === 'cod') {
            // COD: ghi nhận phương thức, giữ payment_status=pending, chờ giao hàng
            $ok = $model->updatePaymentStatus($orderId, 'pending', 'cod');
        } else {
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ!']);
            return;
        }

        if ($ok) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật thanh toán!']);
        }
    }

    // -------------------------------------------------------
    // Admin duyệt / từ chối thanh toán
    // POST /orders/updatePaymentStatus
    // JSON: { order_id, payment_status }   (paid | failed)
    // -------------------------------------------------------
    public function updatePaymentStatus()
    {
        header('Content-Type: application/json');

        $input   = json_decode(file_get_contents('php://input'), true);
        $orderId = isset($input['order_id']) ? (int)$input['order_id'] : 0;
        $status  = $input['payment_status'] ?? '';

        $allowed = ['paid', 'failed'];
        if (!$orderId || !in_array($status, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ!']);
            return;
        }

        $model = new OrderModel();
        $ok    = $model->updatePaymentStatus($orderId, $status);

        // Nếu admin duyệt paid → tự động chuyển đơn hàng sang confirmed
        if ($ok && $status === 'paid') {
            $model->updateOrderStatus($orderId, 'confirmed');
        }

        // Nếu admin từ chối → đơn hàng chuyển sang cancelled
        if ($ok && $status === 'failed') {
            $model->updateOrderStatus($orderId, 'cancelled');
        }

        echo json_encode(['success' => $ok ? true : false]);
    }
    public function guestCheckout()
{
    // Khách đã đăng nhập → chuyển sang luồng bình thường
    if (isset($_SESSION['user_id'])) {
        header('Location: /WEB_GR4/cart');
        exit;
    }

    // Lấy dữ liệu giỏ hàng tạm thời từ session guest cart
    // (được lưu ở ProductController khi bấm "Mua ngay")
    $items = $_SESSION['guest_cart'] ?? [];

    if (empty($items)) {
        header('Location: /WEB_GR4/products');
        exit;
    }

    // Tính tổng tiền
    $total = 0;
    foreach ($items as $item) {
        $total += $item['price_snapshot'] * $item['quantity'];
    }

    // Tạo CSRF token
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    // Truyền dữ liệu sang view
    $cartJson = json_encode($items, JSON_UNESCAPED_UNICODE);

    $this->render('orders/guest_checkout', [
        'items'    => $items,
        'total'    => $total,
        'cartJson' => $cartJson,
        'errors'   => [],
        'old'      => [],
    ]);
}


// ----------------------------------------------------------------
// 2. Xử lý đặt hàng từ guest (POST /orders/guest-place)
//    Nhận JSON từ JS, tạo đơn hàng, trả JSON response
// ----------------------------------------------------------------
public function guestPlace()
{
    header('Content-Type: application/json; charset=utf-8');

    // Chỉ chấp nhận AJAX POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST'
        || ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest'
    ) {
        echo json_encode(['success' => false, 'message' => 'Bad request']);
        exit;
    }

    $body = json_decode(file_get_contents('php://input'), true);

    if (!$body) {
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        exit;
    }

    // --- Validate ---
    $guestName    = trim($body['guest_name']    ?? '');
    $guestPhone   = trim($body['guest_phone']   ?? '');
    $guestEmail   = trim($body['guest_email']   ?? '');
    $guestCity    = trim($body['guest_city']    ?? '');
    $guestAddress = trim($body['guest_address'] ?? '');
    $note         = trim($body['note']          ?? '');
    $method       = in_array($body['payment_method'] ?? '', ['cod', 'bank_transfer', 'momo', 'vnpay', 'zalopay'])
                    ? $body['payment_method'] : 'cod';
    $items        = $body['items'] ?? [];
    $total        = (float)($body['total'] ?? 0);

    if (!$guestName || !$guestPhone || !$guestCity || !$guestAddress || empty($items) || $total <= 0) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
        exit;
    }

    // --- Lưu đơn hàng ---
    require_once __DIR__ . '/../models/OrderModel.php';
    $orderModel = new OrderModel();

    try {
        // Tạo đơn với user_id = NULL (guest), address_id = NULL
        $orderId = $orderModel->createGuestOrder(
            $guestName, $guestPhone, $guestEmail,
            $guestAddress, $guestCity,
            $total, $note
        );

        if (!$orderId) {
            throw new \Exception('Không tạo được đơn hàng');
        }

        // Thêm từng sản phẩm vào order_items
        foreach ($items as $item) {
            $orderModel->addOrderItem(
                $orderId,
                (int)($item['product_id']  ?? 0),
                !empty($item['variant_id']) ? (int)$item['variant_id'] : null,
                (int)($item['quantity']    ?? 1),
                (float)($item['price_snapshot'] ?? 0)
            );
        }

        // Tạo bản ghi payment
        $payStatus = ($method === 'bank_transfer') ? 'pending' : 'pending';
        $orderModel->createPayment($orderId, $method, $payStatus);

        // Xóa guest cart trong session
        unset($_SESSION['guest_cart']);

        echo json_encode([
            'success'  => true,
            'order_id' => $orderId,
            'message'  => 'Đặt hàng thành công',
        ]);

    } catch (\Exception $e) {
        error_log('guestPlace error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống, vui lòng thử lại']);
    }

    exit;
}
    // -------------------------------------------------------
    // Trang xem trước đơn hàng Mua ngay (member)
    // GET /orders/buynow-preview
    // -------------------------------------------------------
    public function buyNowPreview()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /WEB_GR4/login');
            exit;
        }

        $item = $_SESSION['buynow'] ?? null;
        if (!$item) {
            header('Location: /WEB_GR4/products');
            exit;
        }

        // Lấy địa chỉ mặc định giống preview() của CartController
        require_once __DIR__ . '/../models/CartModel.php';
        $cartModel = new CartModel();
        $address   = $cartModel->getDefaultAddress($_SESSION['user_id']);

        if (!$address) {
            header('Location: /WEB_GR4/profile/addAddress');
            exit;
        }

        $items = [$item];
        $total = $item['price_snapshot'] * $item['quantity'];

        $this->render('orders/payment', [
            'pageTitle' => 'Xác nhận đặt hàng',
            'items'     => $items,
            'total'     => $total,
            'address'   => $address,
            'order'     => null,
            'isBuyNow'  => true,
        ]);
    }

    // -------------------------------------------------------
    // Tạo đơn hàng từ Mua ngay (member)
    // POST /orders/buynow-place  (JSON)
    // -------------------------------------------------------
    public function buyNowPlace()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
            exit;
        }

        $item = $_SESSION['buynow'] ?? null;
        if (!$item) {
            echo json_encode(['success' => false, 'message' => 'Không có sản phẩm']);
            exit;
        }

        require_once __DIR__ . '/../models/CartModel.php';
        $cartModel = new CartModel();
        $address   = $cartModel->getDefaultAddress($_SESSION['user_id']);

        if (!$address) {
            echo json_encode(['success' => false, 'message' => 'Chưa có địa chỉ giao hàng']);
            exit;
        }

        // Kiểm tra tồn kho
        $stock = $cartModel->getStockByVariant($item['variant_id']);
        if ($item['quantity'] > $stock) {
            echo json_encode([
                'success' => false,
                'message' => "Sản phẩm chỉ còn $stock trong kho"
            ]);
            exit;
        }

        $total = $item['price_snapshot'] * $item['quantity'];

        $model   = new OrderModel();
        $orderId = $model->createOrder($_SESSION['user_id'], $address['address_id'], $total);

        $model->addOrderItem(
            $orderId,
            $item['product_id'],
            $item['variant_id'],
            $item['quantity'],
            $item['price_snapshot']
        );

        // Trừ tồn kho
        $cartModel->reduceStock($item['variant_id'], $item['quantity']);

        // Tạo bản ghi payment pending — phương thức xác nhận sau
        $model->createPayment($orderId, null, 'pending');

        // Xóa session buynow
        unset($_SESSION['buynow']);

        echo json_encode(['success' => true, 'order_id' => $orderId]);
    }

}