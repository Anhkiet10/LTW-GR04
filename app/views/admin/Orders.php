<?php require_once __DIR__ . '/../layouts/admin_sidebar.php'; ?>
    <link rel="stylesheet" href="/WEB_GR4/public/assets/css/admin/order_admin.css">
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
                <div class="stat-card" style="background:#fef9c3;border-left:4px solid #eab308;">
                    <div class="stat-value"><?php echo $stats['awaiting_approval'] ?? 0; ?></div>
                    <div class="stat-label">Chờ duyệt CK</div>
                </div>
                <div class="stat-card" style="background:#ede9fe;border-left:4px solid #7c3aed;">
                    <div class="stat-value"><?php echo $stats['confirmed'] ?? 0; ?></div>
                    <div class="stat-label">Đã xác nhận</div>
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
                <div class="stat-card" style="background:#fefce8;border-left:4px solid #ca8a04;">
                    <div class="stat-value"><?php echo $stats['guest_orders'] ?? 0; ?></div>
                    <div class="stat-label">Khách vãng lai</div>
                </div>
            </div>

            <!-- Search bar -->
            <div style="margin-bottom:16px;">
                <form method="GET" action="/WEB_GR4/admin/orders" id="searchForm"
                      style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    <?php if ($currentStatus): ?>
                        <input type="hidden" name="status" value="<?php echo htmlspecialchars($currentStatus); ?>">
                    <?php endif; ?>
                    <?php if ($guestOnly): ?>
                        <input type="hidden" name="guest" value="1">
                    <?php endif; ?>
                    <div style="position:relative;flex:1;min-width:240px;">
                        <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#9ca3af;"></i>
                        <input type="text" name="search" id="searchInput"
                               value="<?php echo htmlspecialchars($search ?? ''); ?>"
                               placeholder="Tìm theo tên, email, SĐT hoặc mã đơn..."
                               style="width:100%;padding:10px 12px 10px 36px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:14px;box-sizing:border-box;outline:none;"
                               onfocus="this.style.borderColor='#7c3aed'"
                               onblur="this.style.borderColor='#e5e7eb'">
                    </div>
                    <button type="submit"
                            style="padding:10px 20px;background:#7c3aed;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                    <?php if (!empty($search)): ?>
                    <a href="/WEB_GR4/admin/orders<?php echo $currentStatus ? '?status='.$currentStatus : ''; ?>"
                       style="padding:10px 16px;background:#f3f4f6;color:#6b7280;border-radius:8px;font-size:14px;text-decoration:none;">
                        <i class="fas fa-times"></i> Xóa
                    </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Filters -->
            <div class="filters-bar">
                <a href="/WEB_GR4/admin/orders<?php echo !empty($search) ? '?search='.urlencode($search) : ''; ?>" class="filter-btn <?php echo !$currentStatus && empty($guestOnly) ? 'active' : ''; ?>">
                    <i class="fas fa-list"></i> Tất cả
                </a>
                <a href="/WEB_GR4/admin/orders?status=pending<?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" class="filter-btn <?php echo $currentStatus === 'pending' ? 'active' : ''; ?>">
                    <i class="fas fa-clock"></i> Chờ xử lý
                </a>
                <a href="/WEB_GR4/admin/orders?status=confirmed<?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" class="filter-btn <?php echo $currentStatus === 'confirmed' ? 'active' : ''; ?>">
                    <i class="fas fa-check-circle"></i> Đã xác nhận
                </a>
                <a href="/WEB_GR4/admin/orders?status=shipping<?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" class="filter-btn <?php echo $currentStatus === 'shipping' ? 'active' : ''; ?>">
                    <i class="fas fa-truck"></i> Đang giao
                </a>
                <a href="/WEB_GR4/admin/orders?status=completed<?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" class="filter-btn <?php echo $currentStatus === 'completed' ? 'active' : ''; ?>">
                    <i class="fas fa-check-double"></i> Hoàn thành
                </a>
                <a href="/WEB_GR4/admin/orders?status=cancelled<?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" class="filter-btn <?php echo $currentStatus === 'cancelled' ? 'active' : ''; ?>">
                    <i class="fas fa-times-circle"></i> Đã hủy
                </a>
                <a href="/WEB_GR4/admin/orders?guest=1<?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>"
                   class="filter-btn <?php echo !empty($guestOnly) ? 'active' : ''; ?>"
                   style="background:<?php echo !empty($guestOnly) ? '#fef3c7' : ''; ?>;color:<?php echo !empty($guestOnly) ? '#92400e' : ''; ?>;border-color:<?php echo !empty($guestOnly) ? '#d97706' : ''; ?>;">
                    <i class="fas fa-user-clock"></i> Khách vãng lai
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
                                <th>Trạng thái đơn</th>
                                <th>Thanh toán</th>
                                <th>Ngày đặt</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td class="order-id">#<?php echo $order['order_id']; ?></td>
                                    <td>
                                        <div class="customer-name">
                                            <?php echo htmlspecialchars($order['full_name'] ?? 'N/A'); ?>
                                            <?php if (!empty($order['is_guest'])): ?>
                                                <span style="font-size:10px;background:#fef3c7;color:#92400e;padding:1px 6px;border-radius:10px;margin-left:4px;font-weight:600;">Vãng lai</span>
                                            <?php endif; ?>
                                        </div>
                                        <div style="font-size: 12px; color: #6b7280;"><?php echo htmlspecialchars($order['email'] ?? ''); ?></div>
                                        <div style="font-size: 12px; color: #6b7280;"><?php echo htmlspecialchars($order['phone'] ?? ''); ?></div>
                                    </td>
                                    <td><?php echo $order['item_count'] ?? 0; ?> sản phẩm</td>
                                    <td class="order-amount"><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> ₫</td>
                                    <td>
                                        <span class="status-badge status-<?php echo $order['status']; ?>">
                                            <?php
                                                $statusMap = [
                                                    'pending'   => 'Chờ xử lý',
                                                    'confirmed' => 'Đã xác nhận',
                                                    'shipping'  => 'Đang giao',
                                                    'completed' => 'Hoàn thành',
                                                    'cancelled' => 'Đã hủy'
                                                ];
                                                echo $statusMap[$order['status']] ?? $order['status'];
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                            $ps = $order['payment_status'] ?? '';
                                            $pm = $order['payment_method'] ?? '';
                                            $psMap = [
                                                'pending'    => ['label' => 'Chờ TT'],
                                                'processing' => ['label' => 'Chờ duyệt CK'],
                                                'paid'       => ['label' => 'Đã thanh toán'],
                                                'failed'     => ['label' => 'Từ chối'],
                                                'refunded'   => ['label' => 'Hoàn tiền'],
                                            ];
                                            $info = $psMap[$ps] ?? ['label' => $ps ?: '—'];
                                        ?>
                                        <span class="payment-status-label <?php echo $ps; ?>">
                                            <?php echo $info['label']; ?>
                                        </span>
                                        <?php if ($pm): ?>
                                            <div class="payment-method-label">
                                                <?php echo $pm === 'bank_transfer' ? 'Chuyển khoản' : strtoupper($pm); ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($ps === 'processing'): ?>
                                            <div class="payment-action-btns">
                                                <button class="btn-approve"
                                                    onclick="approvePayment(<?php echo $order['order_id']; ?>)">
                                                    <i class="fas fa-check"></i> Duyệt
                                                </button>
                                                <button class="btn-reject"
                                                    onclick="rejectPayment(<?php echo $order['order_id']; ?>)">
                                                    <i class="fas fa-times"></i> Từ chối
                                                </button>
                                            </div>
                                        <?php endif; ?>
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
                                <a href="?page=<?php echo $currentPage - 1; ?><?php echo $currentStatus ? '&status=' . $currentStatus : ''; ?><?php echo !empty($guestOnly) ? '&guest=1' : ''; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>">
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
                                <a href="?page=<?php echo $currentPage + 1; ?><?php echo $currentStatus ? '&status=' . $currentStatus : ''; ?><?php echo !empty($guestOnly) ? '&guest=1' : ''; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>">
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
                <label for="statusSelect">Trạng thái đơn hàng:</label>
                <select id="statusSelect">
                    <option value="">-- Chọn trạng thái --</option>
                    <option value="pending">Chờ xử lý</option>
                    <option value="confirmed">Đã xác nhận</option>
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