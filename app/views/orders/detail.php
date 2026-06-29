<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<link rel="stylesheet" href="/WEB_GR4/public/assets/css/user/orders.css">
<style>
</style>
<?php

$statusMap = [
    'pending'   => ['label' => 'Chờ xác nhận',     'step' => 1, 'badge' => 'pending'],
    'confirmed' => ['label' => 'Đã xác nhận',       'step' => 2, 'badge' => 'confirmed'],
    'shipping'  => ['label' => 'Đang giao hàng',    'step' => 3, 'badge' => 'shipping'],
    'completed' => ['label' => 'Giao thành công',   'step' => 4, 'badge' => 'completed'],
    'cancelled' => ['label' => 'Đã hủy',            'step' => 0, 'badge' => 'cancelled'],
];

$orderStatus = $order['status'] ?? 'pending';
$statusInfo  = $statusMap[$orderStatus] ?? ['label' => ucfirst($orderStatus), 'step' => 1, 'badge' => 'pending'];
$statusText  = $statusInfo['label'];
$step        = $statusInfo['step'];


$paymentStatusMap = [
    'pending'    => ['label' => 'Chờ thanh toán',          'class' => 'payment-pending'],
    'processing' => ['label' => 'Chờ xác nhận chuyển khoản','class' => 'payment-processing'],
    'paid'       => ['label' => 'Đã thanh toán',            'class' => 'payment-paid'],
    'failed'     => ['label' => 'Thanh toán thất bại',      'class' => 'payment-failed'],
    'refunded'   => ['label' => 'Đã hoàn tiền',             'class' => 'payment-refunded'],
];

$paymentMethodMap = [
    'cod'         => 'Thanh toán khi nhận hàng (COD)',
    'bank_transfer' => 'Chuyển khoản ngân hàng',
    'momo'        => 'Ví MoMo',
    'vnpay'       => 'VNPay',
    'zalopay'     => 'ZaloPay',
    'credit_card' => 'Thẻ tín dụng / Ghi nợ',
];

$payStatus     = $order['payment_status'] ?? null;
$payMethod     = $order['payment_method'] ?? null;
$payInfo       = $payStatus ? ($paymentStatusMap[$payStatus] ?? ['label' => ucfirst($payStatus), 'class' => 'payment-pending']) : null;
$payMethodLabel = $payMethod ? ($paymentMethodMap[$payMethod] ?? ucfirst($payMethod)) : null;
?>

<div class="order-detail-page">

    <!-- ===== HEADER CARD ===== -->
    <div class="order-detail-card">
        <div class="order-detail-header">
            <div>
                <small>ĐẶT NGÀY</small><br>
                <strong><?= htmlspecialchars(date('d/m/Y H:i', strtotime($order['order_date']))) ?></strong>
            </div>

            <div>
                <small>TỔNG TIỀN</small><br>
                <strong class="price"><?= number_format($order['total_amount']) ?>đ</strong>
            </div>

            <div>
                <small>MÃ ĐƠN</small><br>
                <strong>#<?= htmlspecialchars($order['order_id']) ?></strong>
            </div>

            <div>
                <small>TRẠNG THÁI ĐƠN</small><br>
                <span class="badge <?= htmlspecialchars($statusInfo['badge']) ?>">
                    <?= htmlspecialchars($statusText) ?>
                </span>
            </div>
        </div>

        <!-- ===== PROGRESS STEPS ===== -->
        <?php if ($orderStatus !== 'cancelled'): ?>
        <div class="order-detail-body">
            <div class="order-progress">
                <div class="order-step step-order">
                    <div class="order-step-circle <?= $step >= 1 ? 'active' : '' ?>">✓</div>
                    <p>Đặt hàng</p>
                </div>
                <div class="order-step step-order">
                    <div class="order-step-circle <?= $step >= 2 ? 'active' : '' ?>">✓</div>
                    <p>Xác nhận</p>
                </div>
                <div class="order-step step-order">
                    <div class="order-step-circle <?= $step >= 3 ? 'active' : '' ?>">✓</div>
                    <p>Giao hàng</p>
                </div>
                <div class="order-step step-order">
                    <div class="order-step-circle <?= $step >= 4 ? 'active' : '' ?>">✓</div>
                    <p>Hoàn tất</p>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="order-detail-body">
            <div class="order-cancelled-notice">
                <i class="fa-solid fa-xmark" style="color: rgb(222, 50, 9);"></i> Đơn hàng này đã bị hủy.
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- ===== SHIPPING INFO ===== -->
    <div class="order-summary">
        <h3> Thông tin giao hàng</h3>
        <p><strong>Khách hàng:</strong> <?= htmlspecialchars($order['full_name'] ?? '') ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($order['email'] ?? '') ?></p>
        <p><strong>SĐT:</strong> <?= htmlspecialchars($order['phone'] ?? '') ?></p>
        <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($order['full_address'] ?? '') ?></p>
        <?php if (!empty($order['city'])): ?>
            <p><strong>Thành phố:</strong> <?= htmlspecialchars($order['city']) ?></p>
        <?php endif; ?>
        <?php if (!empty($order['note'])): ?>
            <p><strong>Ghi chú:</strong> <?= htmlspecialchars($order['note']) ?></p>
        <?php endif; ?>
    </div>

    <!-- ===== PAYMENT INFO ===== -->
    <?php if ($payInfo): ?>
    <div class="order-summary order-payment-info">
        <h3><i class="fa-solid fa-credit-card" style="color: rgb(116, 192, 252);"></i> Thông tin thanh toán</h3>
        <?php if ($payMethodLabel): ?>
            <p><strong>Phương thức:</strong> <?= htmlspecialchars($payMethodLabel) ?></p>
        <?php endif; ?>
        <p>
            <strong>Trạng thái thanh toán:</strong>
            <span class="payment-badge <?= htmlspecialchars($payInfo['class']) ?>">
                <?= htmlspecialchars($payInfo['label']) ?>
            </span>
        </p>
        <?php if ($payStatus === 'processing'): ?>
        <div style="margin-top:12px;padding:12px 16px;background:#fef9c3;border:1.5px solid #eab308;border-radius:8px;font-size:14px;color:#92400e;">
             Hệ thống đã nhận yêu cầu chuyển khoản của bạn. Vui lòng chờ admin kiểm tra và xác nhận — thường trong vòng 15–30 phút.
        </div>
        <?php elseif ($payStatus === 'failed'): ?>
        <div style="margin-top:12px;padding:12px 16px;background:#fef2f2;border:1.5px solid #dc2626;border-radius:8px;font-size:14px;color:#991b1b;">
            <i class="fa-solid fa-xmark" style="color: rgb(222, 50, 9);"></i> Thanh toán không được xác nhận. Đơn hàng đã bị hủy. Vui lòng liên hệ shop nếu bạn đã chuyển tiền.
        </div>
        <?php elseif ($payStatus === 'paid'): ?>
        <div style="margin-top:12px;padding:12px 16px;background:#f0fdf4;border:1.5px solid #22c55e;border-radius:8px;font-size:14px;color:#15803d;">
            <i class="fa-solid fa-check" style="color: rgb(99, 230, 190);"></i> Thanh toán đã được xác nhận. Đơn hàng đang được xử lý.
        </div>
        <?php endif; ?>
        <?php if (!empty($order['transaction_id'])): ?>
            <p style="margin-top:8px;"><strong>Mã giao dịch:</strong> <?= htmlspecialchars($order['transaction_id']) ?></p>
        <?php endif; ?>
    </div>
    <?php elseif ($orderStatus === 'pending'): ?>
    <div class="order-summary order-payment-info">
        <h3><i class="fa-solid fa-credit-card" style="color: rgb(116, 192, 252);"></i> Thanh toán</h3>
        <p>Đơn hàng chưa được xác nhận phương thức thanh toán.</p>
        <a class="shop-btn" href="/WEB_GR4/orders/pay/<?= $order['order_id'] ?>">Thanh toán ngay</a>
    </div>
    <?php endif; ?>

    <!-- ===== ORDER ITEMS ===== -->
    <div class="order-detail-card">
        <div class="order-products-title">
            <h2> Sản phẩm trong đơn hàng</h2>
        </div>

        <?php foreach ($items as $item): ?>
            <div class="order-product">
                <div>
                    <?php if (!empty($item['image_url'])): ?>
                        <img src="/WEB_GR4/public/<?= htmlspecialchars($item['image_url']) ?>"
                             alt="<?= htmlspecialchars($item['product_name'] ?? '') ?>">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/150" alt="No image">
                    <?php endif; ?>
                </div>

                <div class="order-product-info">
                    <div class="order-product-name">
                        <?= htmlspecialchars($item['product_name'] ?? 'Sản phẩm không còn tồn tại') ?>
                    </div>

                    <?php if (!empty($item['attributes'])): ?>
                        <p class="order-product-line">Phân loại: <strong><?= htmlspecialchars($item['attributes']) ?></strong></p>
                    <?php endif; ?>

                    <p class="order-product-line">Số lượng: <strong><?= htmlspecialchars($item['quantity']) ?></strong></p>
                    <p class="price">Đơn giá: <?= number_format($item['unit_price']) ?>đ</p>
                    <p class="order-product-total">
                        Thành tiền:
                        <strong class="price">
                            <?= number_format($item['unit_price'] * $item['quantity']) ?>đ
                        </strong>
                    </p>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- ===== ORDER TOTAL ===== -->
        <div class="order-total-row">
            <span>Tổng cộng:</span>
            <strong class="price"><?= number_format($order['total_amount']) ?>đ</strong>
        </div>
    </div>

    <a class="order-detail-back" href="/WEB_GR4/orders">← Quay lại đơn hàng</a>
</div>

<script src="/WEB_GR4/public/assets/js/user/orders.js"></script>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>