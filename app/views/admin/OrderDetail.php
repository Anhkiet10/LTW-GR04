<?php require_once __DIR__ . '/../layouts/admin_sidebar.php'; ?>
    <link rel="stylesheet" href="/WEB_GR4/public/assets/css/admin/order_detail.css">


    <main class="admin-content">
        <div class="order-detail-container">
            <a href="/WEB_GR4/admin/orders" class="back-link"><i class="fas fa-arrow-left"></i>Quay lại danh sách</a>

            <div class="order-header">
                <div class="order-title">
                    <i class="fas fa-receipt"></i>
                    <h1>Đơn hàng #<?php echo $order['order_id']; ?></h1>
                </div>
                <span class="status-badge status-<?php echo $order['status']; ?>">
                    <?php
                        $statusMap = [
                            'pending' => 'Chờ xử lý',
                            'paid' => 'Đã thanh toán',
                            'shipping' => 'Đang giao',
                            'completed' => 'Hoàn thành',
                            'cancelled' => 'Đã hủy'
                        ];
                        echo $statusMap[$order['status']] ?? $order['status'];
                    ?>
                </span>
            </div>

            <div class="order-info-grid">
                <!-- Customer Info -->
                <div class="info-section">
                    <h3><i class="fas fa-user"></i>Thông tin khách hàng</h3>
                    <div class="info-row">
                        <span class="info-label">Tên:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['full_name'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['email'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Điện thoại:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['phone'] ?? 'N/A'); ?></span>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="info-section">
                    <h3><i class="fas fa-map-marker-alt"></i>Địa chỉ giao hàng</h3>
                    <div class="info-row">
                        <span class="info-label">Thành phố:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['city'] ?? 'N/A'); ?></span>
                    </div>
                    <div style="margin-top: 12px; color: #374151; font-size: 14px;">
                        <strong>Địa chỉ:</strong><br>
                        <?php echo nl2br(htmlspecialchars($order['full_address'] ?? 'N/A')); ?>
                    </div>
                </div>

                <!-- Order Info -->
                <div class="info-section">
                    <h3><i class="fas fa-calendar"></i>Thông tin đơn hàng</h3>
                    <div class="info-row">
                        <span class="info-label">Ngày đặt:</span>
                        <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phương thức:</span>
                        <span class="info-value">
                            <?php
                                $methodMap = [
                                    'cod' => 'Thanh toán khi nhận hàng',
                                    'bank_transfer' => 'Chuyển khoản',
                                    'momo' => 'Ví MoMo',
                                    'vnpay' => 'VNPay',
                                    'zalopay' => 'ZaloPay',
                                    'credit_card' => 'Thẻ tín dụng'
                                ];
                                echo $methodMap[$order['payment_method']] ?? $order['payment_method'] ?? 'N/A';
                            ?>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Trạng thái TT:</span>
                        <span class="info-value"><?php echo ucfirst($order['payment_status'] ?? 'N/A'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="items-section">
                <div class="items-header">
                    <h3><i class="fas fa-box"></i>Sản phẩm trong đơn hàng</h3>
                </div>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Số lượng</th>
                            <th>Giá</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <div class="item-product">
                                        <div class="item-image">
                                            <?php if ($item['image_url']): ?>
                                                <img src="/WEB_GR4/public<?php echo htmlspecialchars($item['image_url']); ?>" alt="">
                                            <?php else: ?>
                                                <div style="display: flex; align-items: center; justify-content: center; height: 100%; background: #f3f4f6; color: #d1d5db;"><i class="fas fa-image"></i></div>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <div class="item-name"><?php echo htmlspecialchars($item['product_name'] ?? 'Sản phẩm đã bị xóa'); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td class="item-price"><?php echo number_format($item['unit_price'], 0, ',', '.'); ?> ₫</td>
                                <td class="item-price"><?php echo number_format($item['quantity'] * $item['unit_price'], 0, ',', '.'); ?> ₫</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Order Summary -->
            <div class="summary-section">
                <div class="summary-row">
                    <span class="summary-label">Tổng sản phẩm:</span>
                    <span class="summary-value">
                        <?php echo count($items); ?> sản phẩm
                    </span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Số lượng:</span>
                    <span class="summary-value">
                        <?php echo array_sum(array_column($items, 'quantity')); ?> cái
                    </span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Giảm giá:</span>
                    <span class="summary-value"><?php echo number_format($order['discount_amount'] ?? 0, 0, ',', '.'); ?> ₫</span>
                </div>
                <div class="summary-row total">
                    <span>Tổng cộng:</span>
                    <span><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> ₫</span>
                </div>
            </div>

            <!-- Actions -->
            <div class="action-bar">
                <button class="btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> In đơn hàng
                </button>
                <a href="/WEB_GR4/admin/orders" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>
    </main>
</div>

</body>
</html>
