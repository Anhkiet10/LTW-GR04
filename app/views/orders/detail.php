<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<link rel="stylesheet" href="/WEB_GR4/public/assets/css/user/orders.css">
<?php
$statusText = 'Đang xử lý';
$step = 1;


if ($order['status'] === 'paid') {
    $statusText = 'Đã thanh toán';
    $step = 2;
} elseif ($order['status'] === 'shipping') {
    $statusText = 'Đang giao hàng';
    $step = 3;
} elseif ($order['status'] === 'completed') {
    $statusText = 'Đã giao thành công';
    $step = 4;
} elseif ($order['status'] === 'cancelled') {
    $statusText = 'Đã hủy';
    $step = 0;
}
?>

<div class="order-detail-page">
    <div class="order-detail-card">
        <div class="order-detail-header">
            <div>
                <small>ĐẶT NGÀY</small><br>
                <strong><?= htmlspecialchars($order['order_date']) ?></strong>
            </div>

            <div>
                <small>TỔNG TIỀN</small><br>
                <strong class="price"><?= number_format($order['total_amount']) ?>đ</strong>
            </div>

            <div>
                <small>MÃ ĐƠN</small><br>
                <strong>#<?= htmlspecialchars($order['order_id']) ?></strong>
            </div>
        </div>

        <div class="order-detail-body">
            <div class="order-status">
                Trạng thái hiện tại: <?= htmlspecialchars($statusText) ?>
            </div>

            <div class="order-progress">
                <div class="order-step step-order">
                    <div class="order-step-circle <?= $step >= 1 ? 'active' : '' ?>">✓</div>
                    <p>Đặt hàng</p>
                </div>

                <div class="order-step step-order">
                    <div class="order-step-circle <?= $step >= 2 ? 'active' : '' ?>">✓</div>
                    <p>Thanh toán</p>
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
    </div>

    <div class="order-summary">
        <h3>📍 Thông tin giao hàng</h3>
        <p><strong>Khách hàng:</strong> <?= htmlspecialchars($order['full_name'] ?? '') ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($order['email'] ?? '') ?></p>
        <p><strong>SĐT:</strong> <?= htmlspecialchars($order['phone'] ?? '') ?></p>
        <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($order['full_address'] ?? '') ?></p>
    </div>

    <div class="order-detail-card">
        <div class="order-products-title">
            <h2>🛍️ Sản phẩm trong đơn hàng</h2>
        </div>

        <?php foreach ($items as $item): ?>
            <div class="order-product">
                <div>
                    <?php if (!empty($item['image_url'])): ?>
                        <img src="/WEB_GR4/public/<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['product_name'] ?? '') ?>">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/150" alt="No image">
                    <?php endif; ?>
                </div>

                <div class="order-product-info">
                    <div class="order-product-name">
                        <?= htmlspecialchars($item['product_name'] ?? '') ?>
                    </div>

                    <?php if (!empty($item['sku'])): ?>
                        <p class="order-product-line">SKU: <strong><?= htmlspecialchars($item['sku']) ?></strong></p>
                    <?php endif; ?>

                    <?php if (!empty($item['attributes'])): ?>
                        <p class="order-product-line">  <strong><?= htmlspecialchars($item['attributes']) ?></strong></p>
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
    </div>

    <a class="order-detail-back" href="/WEB_GR4/orders">← Quay lại đơn hàng</a>
</div>

<script src="/WEB_GR4/public/assets/js/user/orders.js"></script>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>