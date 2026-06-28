<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<link rel="stylesheet" href="/WEB_GR4/public/assets/css/user/Payment.css">
<style></style>

<div class="payment-container" id="paymentContainer" data-total="<?php echo (int)$total; ?>" data-buynow="<?php echo !empty($isBuyNow) ? '1' : '0'; ?>" data-order-id="<?php echo !empty($order['order_id']) ? (int)$order['order_id'] : 0; ?>">

    <h1>Xác nhận thanh toán</h1>

    <div class="order-summary">
        <h2>Chi tiết đơn hàng</h2>
        <table class="summary-table">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Biến thể</th>
                    <th>Số lượng</th>
                    <th>Đơn giá</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td><?php echo htmlspecialchars(!empty($item['variant_label']) ? $item['variant_label'] : ($item['attributes'] ?? $item['variant_key'] ?? '')); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo number_format($item['price_snapshot'], 0, ',', '.'); ?>đ</td>
                    <td><?php echo number_format($item['price_snapshot'] * $item['quantity'], 0, ',', '.'); ?>đ</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="order-total">
            Tổng cộng: <strong><?php echo number_format($total, 0, ',', '.'); ?>đ</strong>
        </div>

        <div class="order-address">
            <i class="fa-solid fa-location-dot"></i>
            Giao đến: <?php echo htmlspecialchars($address['full_address'] . ', ' . $address['city']); ?>
        </div>
    </div>

    <div class="payment-methods">
        <h2>Chọn phương thức thanh toán</h2>

        <div class="method-tabs">
            <button class="method-tab active" data-method="bank_transfer">
                <i class="fa-solid fa-qrcode"></i> Chuyển khoản / QR
            </button>
            <button class="method-tab" data-method="cod">
                <i class="fa-solid fa-truck"></i> Thanh toán khi nhận hàng (COD)
            </button>
        </div>

        <div class="method-panel" id="panel-bank_transfer">
            <p class="method-desc">Tạo đơn hàng.</p>

            <div class="qr-wrapper">
                <img 
                    src="https://img.vietqr.io/image/MB-0973469734-print.png?amount=<?php echo (int)$total; ?>&addInfo=PREVIEW"
                    alt="QR thanh toán"
                    class="qr-image"
                    id="qrImage"
                >
                <div class="qr-info">
                    <div class="qr-info-row">
                        <span class="qr-label">Ngân hàng</span>
                        <span class="qr-value">MB Bank</span>
                    </div>
                    <div class="qr-info-row">
                        <span class="qr-label">Số tài khoản</span>
                        <span class="qr-value">0973469734</span>
                    </div>
                    <div class="qr-info-row">
                        <span class="qr-label">Số tiền</span>
                        <span class="qr-value highlight"><?php echo number_format($total, 0, ',', '.'); ?>đ</span>
                    </div>
                    <div class="qr-info-row">
                        <span class="qr-label">Nội dung CK</span>
                        <span class="qr-value highlight" id="qrNote">Sẽ hiện sau khi đặt hàng</span>
                    </div>
                </div>
            </div>

            <p class="qr-note">
                <i class="fa-solid fa-circle-info"></i>
                Nhấn "Xác nhận đặt hàng" bên dưới để tạo đơn, sau đó dùng mã QR để chuyển khoản.
            </p>

            <button class="btn-confirm" id="btnConfirmQR">
                Xác nhận đặt hàng & Chuyển khoản
            </button>
        </div>

        <div class="method-panel hidden" id="panel-cod">
            <div class="cod-info">
                <i class="fa-solid fa-circle-check"></i>
                <div>
                    <h3>Thanh toán khi nhận hàng</h3>
                    <p>Bạn sẽ thanh toán <strong><?php echo number_format($total, 0, ',', '.'); ?>đ</strong> khi nhân viên giao hàng đến nơi.</p>
                    <p>Đơn hàng sẽ được xác nhận và giao trong thời gian sớm nhất.</p>
                </div>
            </div>

            <button class="btn-confirm" id="btnConfirmCOD">
                Xác nhận đặt hàng COD
            </button>
        </div>
    </div>

    <div class="payment-cancel">
        <a href="/WEB_GR4/cart" class="btn-cancel" id="btnCancel">
            <i class="fa-solid fa-arrow-left"></i> Hủy & Quay về giỏ hàng
        </a>
    </div>

</div>

<script src="/WEB_GR4/public/assets/js/user/payment1.js"></script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>