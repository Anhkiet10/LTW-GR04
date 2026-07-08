<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<link rel="stylesheet" href="/WEB_GR4/public/assets/css/user/orders.css">
<?php

$totalOrders = count($orders);
$pending   = 0;
$confirmed = 0;
$shipping  = 0;
$completed = 0;
$cancelled = 0;

foreach ($orders as $o) {
    switch ($o['status']) {
        case 'pending':   $pending++;   break;
        case 'confirmed': $confirmed++; break;
        case 'shipping':  $shipping++;  break;
        case 'completed': $completed++; break;
        case 'cancelled': $cancelled++; break;
    }
}

// Label map dùng cho badge trong bảng
$statusLabelMap = [
    'pending'   => 'Chờ xác nhận',
    'confirmed' => 'Đã xác nhận',
    'shipping'  => 'Đang giao',
    'completed' => 'Hoàn thành',
    'cancelled' => 'Đã hủy',
];

// Label map phương thức thanh toán
$paymentMethodMap = [
    'cod'           => 'COD',
    'bank_transfer' => 'Chuyển khoản',
    'momo'          => 'MoMo',
    'vnpay'         => 'VNPay',
    'zalopay'       => 'ZaloPay',
    'credit_card'   => 'Thẻ tín dụng',
];

$paymentStatusMap = [
    'pending'  => ['label' => 'Chưa TT',    'class' => 'payment-pending'],
    'paid'     => ['label' => 'Đã TT',      'class' => 'payment-paid'],
    'failed'   => ['label' => 'Lỗi TT',     'class' => 'payment-failed'],
    'refunded' => ['label' => 'Hoàn tiền',  'class' => 'payment-refunded'],
];
?>

<div class="orders-page">
    <div class="orders-page-header">
        <h1>Đơn hàng của tôi</h1>
        <p>Theo dõi các đơn hàng đã đặt</p>
    </div>

    <div class="orders-filter-box">
        <form method="get" action="/WEB_GR4/orders" class="orders-filter-form">
            <div class="orders-filter-group">
                <label for="sort_date">Sắp xếp ngày</label>
                <select id="sort_date" name="sort_date">
                    <option value="desc" <?= (($sortDate ?? 'desc') === 'desc') ? 'selected' : '' ?>>Mới nhất trước</option>
                    <option value="asc" <?= (($sortDate ?? 'desc') === 'asc') ? 'selected' : '' ?>>Cũ nhất trước</option>
                </select>
            </div>
            <div class="orders-filter-group">
                <label for="sort_order_id">Sắp xếp mã đơn</label>
                <select id="sort_order_id" name="sort_order_id">
                    <option value="desc" <?= (($sortOrderId ?? 'desc') === 'desc') ? 'selected' : '' ?>>Giảm dần</option>
                    <option value="asc" <?= (($sortOrderId ?? 'desc') === 'asc') ? 'selected' : '' ?>>Tăng dần</option>
                </select>
            </div>
            <div class="orders-filter-group">
                <label for="status">Trạng thái</label>
                <select id="status" name="status">
                    <option value="" <?= empty($status ?? '') ? 'selected' : '' ?>>Tất cả</option>
                    <option value="pending" <?= (($status ?? '') === 'pending') ? 'selected' : '' ?>>Chờ xử lý</option>
                    <option value="paid" <?= (($status ?? '') === 'paid') ? 'selected' : '' ?>>Đã thanh toán</option>
                    <option value="shipping" <?= (($status ?? '') === 'shipping') ? 'selected' : '' ?>>Đang giao</option>
                    <option value="completed" <?= (($status ?? '') === 'completed') ? 'selected' : '' ?>>Hoàn thành</option>
                    <option value="cancelled" <?= (($status ?? '') === 'cancelled') ? 'selected' : '' ?>>Đã hủy</option>
                </select>
            </div>
            <div class="orders-filter-actions">
                <button type="submit" class="shop-btn">Lọc</button>
                <a href="/WEB_GR4/orders" class="shop-btn shop-btn-secondary">Đặt lại</a>
            </div>
        </form>
    </div>

    <!-- ===== STATS CARDS ===== -->
    <div class="orders-stats">
        <div class="orders-stat-card stat-total">
            <div class="orders-stat-number"><?= $totalOrders ?></div>
            <div class="orders-stat-label">Tổng đơn hàng</div>
        </div>
        <div class="orders-stat-card stat-pending">
            <div class="orders-stat-number"><?= $pending ?></div>
            <div class="orders-stat-label">Chờ xác nhận</div>
        </div>
        <div class="orders-stat-card stat-confirmed">
            <div class="orders-stat-number"><?= $confirmed ?></div>
            <div class="orders-stat-label">Đã xác nhận</div>
        </div>
        <div class="orders-stat-card stat-shipping">
            <div class="orders-stat-number"><?= $shipping ?></div>
            <div class="orders-stat-label">Đang giao</div>
        </div>
        <div class="orders-stat-card stat-completed">
            <div class="orders-stat-number"><?= $completed ?></div>
            <div class="orders-stat-label">Hoàn thành</div>
        </div>
        <div class="orders-stat-card stat-cancelled">
            <div class="orders-stat-number"><?= $cancelled ?></div>
            <div class="orders-stat-label">Đã hủy</div>
        </div>
    </div>

    <?php if (empty($orders)): ?>
        <div class="orders-empty">
            <h2>Bạn chưa có đơn hàng nào</h2>
            <p class="orders-empty-note">Hãy mua sắm để tạo đơn hàng đầu tiên</p>
            <a class="shop-btn" href="/WEB_GR4/products">Mua sắm ngay</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
        <div class="orders-table-wrap">
            <div class="orders-box">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái đơn</th>
                            <th>Thanh toán</th>
                            <th>Ngày đặt</th>
                            <th>Sản phẩm</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <?php
                                $oStatus  = $order['status'] ?? 'pending';
                                $oLabel   = $statusLabelMap[$oStatus] ?? ucfirst($oStatus);
                                $pStatus  = $order['payment_status'] ?? null;
                                $pMethod  = $order['payment_method'] ?? null;
                                $pInfo    = $pStatus ? ($paymentStatusMap[$pStatus] ?? null) : null;
                                $pMethodLabel = $pMethod ? ($paymentMethodMap[$pMethod] ?? ucfirst($pMethod)) : '—';
                            ?>
                            <tr class="clickable-row" data-href="/WEB_GR4/orders/<?= $order['order_id'] ?>">
                                <td class="order-id">#<?= $order['order_id'] ?></td>
                                <td class="money"><?= number_format($order['total_amount']) ?> đ</td>
                                <td>
                                    <span class="badge <?= htmlspecialchars($oStatus) ?>">
                                        <?= htmlspecialchars($oLabel) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($pInfo): ?>
                                        <span class="payment-badge <?= htmlspecialchars($pInfo['class']) ?>">
                                            <?= htmlspecialchars($pInfo['label']) ?>
                                        </span>
                                        <br><small><?= htmlspecialchars($pMethodLabel) ?></small>
                                    <?php elseif ($oStatus === 'pending'): ?>
                                        <a class="pay-now-btn" href="/WEB_GR4/orders/pay/<?= $order['order_id'] ?>"
                                           onclick="event.stopPropagation()">
                                            Thanh toán
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y', strtotime($order['order_date'])) ?></td>
                                <td class="product-names-cell">
                                    <?= htmlspecialchars($order['product_names'] ?? 'Chưa có sản phẩm') ?>
                                </td>
                                <td>
                                    <a class="view-btn" href="/WEB_GR4/orders/<?= $order['order_id'] ?>">
                                        Chi tiết
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="/WEB_GR4/public/assets/js/user/orders.js"></script>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>