<?php require_once __DIR__ . '/../layouts/admin_sidebar.php'; ?>

    <main class="admin-content">
        <div class="page-header">
            <h1><i class="fas fa-shopping-cart" style="color:#7c3aed; margin-right:8px;"></i>Quản lý đơn hàng</h1>
            <p>Xem và quản lý tất cả đơn hàng từ khách hàng</p>
        </div>

        <div class="orders-container">
            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['total_orders'] ?? 0; ?></div>
                    <div class="stat-label">Tổng đơn hàng</div>
                </div>
                <div class="stat-card pending">
                    <div class="stat-value"><?php echo $stats['pending'] ?? 0; ?></div>
                    <div class="stat-label">Chờ xử lý</div>
                </div>
                <div class="stat-card paid">
                    <div class="stat-value"><?php echo $stats['paid'] ?? 0; ?></div>
                    <div class="stat-label">Đã thanh toán</div>
                </div>
                <div class="stat-card shipping">
                    <div class="stat-value"><?php echo $stats['shipping'] ?? 0; ?></div>
                    <div class="stat-label">Đang giao</div>
                </div>
                <div class="stat-card completed">
                    <div class="stat-value"><?php echo $stats['completed'] ?? 0; ?></div>
                    <div class="stat-label">Hoàn thành</div>
                </div>
                <div class="stat-card cancelled">
                    <div class="stat-value"><?php echo $stats['cancelled'] ?? 0; ?></div>
                    <div class="stat-label">Đã hủy</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-bar">
                <a href="/WEB_GR4/admin/orders" class="filter-btn <?php echo !$currentStatus ? 'active' : ''; ?>">
                    <i class="fas fa-list"></i> Tất cả
                </a>
                <a href="/WEB_GR4/admin/orders?status=pending" class="filter-btn <?php echo $currentStatus === 'pending' ? 'active' : ''; ?>">
                    <i class="fas fa-clock"></i> Chờ xử lý
                </a>
                <a href="/WEB_GR4/admin/orders?status=paid" class="filter-btn <?php echo $currentStatus === 'paid' ? 'active' : ''; ?>">
                    <i class="fas fa-check-circle"></i> Đã thanh toán
                </a>
                <a href="/WEB_GR4/admin/orders?status=shipping" class="filter-btn <?php echo $currentStatus === 'shipping' ? 'active' : ''; ?>">
                    <i class="fas fa-truck"></i> Đang giao
                </a>
                <a href="/WEB_GR4/admin/orders?status=completed" class="filter-btn <?php echo $currentStatus === 'completed' ? 'active' : ''; ?>">
                    <i class="fas fa-check-double"></i> Hoàn thành
                </a>
                <a href="/WEB_GR4/admin/orders?status=cancelled" class="filter-btn <?php echo $currentStatus === 'cancelled' ? 'active' : ''; ?>">
                    <i class="fas fa-times-circle"></i> Đã hủy
                </a>
            </div>

            <!-- Orders Table -->
            <div class="orders-table-wrapper">
                <?php if (empty($orders)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="fas fa-inbox"></i></div>
                        <h3>Không có đơn hàng</h3>
                        <p>Hiện tại không có đơn hàng nào để hiển thị</p>
                    </div>
                <?php else: ?>
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Sản phẩm</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Ngày đặt</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td class="order-id">#<?php echo $order['order_id']; ?></td>
                                    <td>
                                        <div class="customer-name"><?php echo htmlspecialchars($order['full_name'] ?? 'N/A'); ?></div>
                                        <div style="font-size: 12px; color: #6b7280;"><?php echo htmlspecialchars($order['email'] ?? ''); ?></div>
                                    </td>
                                    <td><?php echo $order['item_count'] ?? 0; ?> sản phẩm</td>
                                    <td class="order-amount"><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> ₫</td>
                                    <td>
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
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($order['order_date'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="/WEB_GR4/admin/order-detail?id=<?php echo $order['order_id']; ?>" class="btn-icon" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button class="btn-icon" onclick="openStatusModal(<?php echo $order['order_id']; ?>, '<?php echo $order['status']; ?>')" title="Cập nhật trạng thái">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($currentPage > 1): ?>
                                <a href="?page=<?php echo $currentPage - 1; ?><?php echo $currentStatus ? '&status=' . $currentStatus : ''; ?>">
                                    <i class="fas fa-chevron-left"></i> Trước
                                </a>
                            <?php else: ?>
                                <span class="disabled"><i class="fas fa-chevron-left"></i> Trước</span>
                            <?php endif; ?>

                            <?php
                                $start = max(1, $currentPage - 2);
                                $end = min($totalPages, $currentPage + 2);

                                for ($i = $start; $i <= $end; $i++) {
                                    $active = ($i === $currentPage) ? 'active' : '';
                                    echo "<a href=\"?page=$i" . ($currentStatus ? "&status=" . $currentStatus : "") . "\" class=\"$active\">$i</a>";
                                }
                            ?>

                            <?php if ($currentPage < $totalPages): ?>
                                <a href="?page=<?php echo $currentPage + 1; ?><?php echo $currentStatus ? '&status=' . $currentStatus : ''; ?>">
                                    Sau <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php else: ?>
                                <span class="disabled">Sau <i class="fas fa-chevron-right"></i></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- Status Update Modal -->
<div class="modal-overlay" id="statusModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-edit" style="color:#7c3aed; margin-right:6px;"></i>Cập nhật trạng thái đơn hàng</h3>
            <button class="modal-close" onclick="closeStatusModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="statusSelect">Trạng thái:</label>
                <select id="statusSelect">
                    <option value="">-- Chọn trạng thái --</option>
                    <option value="pending">Chờ xử lý</option>
                    <option value="paid">Đã thanh toán</option>
                    <option value="shipping">Đang giao</option>
                    <option value="completed">Hoàn thành</option>
                    <option value="cancelled">Đã hủy</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeStatusModal()">Hủy</button>
            <button class="btn-primary" onclick="updateOrderStatus()">Cập nhật</button>
        </div>
    </div>
</div>

<script src="/WEB_GR4/public/assets/js/admin/order_admin.js"></script>

</body>
</html>
