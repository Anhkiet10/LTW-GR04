<?php require_once __DIR__ . '/../layouts/admin_sidebar.php'; ?>
<link rel="stylesheet" href="/WEB_GR4/public/assets/css/admin/style_admin.css">
<link rel="stylesheet" href="/WEB_GR4/public/assets/css/admin/review.css">

<main class="admin-content">
    <div class="page-header">
        <h1><i class="fas fa-comments" style="color:#7c3aed; margin-right:8px;"></i>Quản lý đánh giá & bình luận</h1>
        <p>Danh sách các đánh giá và bình luận từ khách hàng đã mua sản phẩm.</p>
    </div>

    <?php
        $reviewCount = count($reviews ?? []);
        $avgRating = $reviewCount > 0 ? round(array_sum(array_map(function($item){ return (int)$item['rating']; }, $reviews ?? [])) / $reviewCount, 1) : 0;
        $latestReview = !empty($reviews) ? $reviews[0] : null;
    ?>

    <div class="review-summary-grid">
        <div class="review-stat-card review-stat-card-purple">
            <div class="review-stat-icon"><i class="fas fa-comment-dots"></i></div>
            <div>
                <div class="review-stat-value"><?php echo (int)$reviewCount; ?></div>
                <div class="review-stat-label">Tổng đánh giá</div>
            </div>
        </div>
        <div class="review-stat-card review-stat-card-gold">
            <div class="review-stat-icon"><i class="fas fa-star"></i></div>
            <div>
                <div class="review-stat-value"><?php echo number_format($avgRating, 1); ?>/5</div>
                <div class="review-stat-label">Điểm trung bình</div>
            </div>
        </div>
        <div class="review-stat-card review-stat-card-blue">
            <div class="review-stat-icon"><i class="fas fa-clock"></i></div>
            <div>
                <div class="review-stat-value"><?php echo $latestReview ? htmlspecialchars(substr($latestReview['created_at'], 0, 10)) : '—'; ?></div>
                <div class="review-stat-label">Đánh giá mới nhất</div>
            </div>
        </div>
    </div>

    <div class="review-card">
        <div class="review-toolbar">
            <div>
                <h3>Danh sách phản hồi</h3>
                <p>Hiển thị theo thứ tự mới nhất trước.</p>
            </div>
        </div>

        <form method="get" action="/WEB_GR4/admin/reviews" class="review-filter-form">
            <div class="review-filter-group">
                <label for="from_date">Từ ngày</label>
                <input type="date" id="from_date" name="from_date" value="<?php echo htmlspecialchars($fromDate ?? ''); ?>">
            </div>
            <div class="review-filter-group">
                <label for="to_date">Đến ngày</label>
                <input type="date" id="to_date" name="to_date" value="<?php echo htmlspecialchars($toDate ?? ''); ?>">
            </div>
            <div class="review-filter-group">
                <label for="order_id">Mã đơn hàng</label>
                <input type="number" id="order_id" name="order_id" min="1" placeholder="Nhập ID đơn hàng" value="<?php echo ($orderId ?? 0) > 0 ? (int)($orderId ?? 0) : ''; ?>">
            </div>
            <div class="review-filter-group">
                <label for="sort_date">Sắp xếp ngày</label>
                <select id="sort_date" name="sort_date">
                    <option value="desc" <?php echo (($sortDate ?? 'desc') === 'desc') ? 'selected' : ''; ?>>Mới nhất trước</option>
                    <option value="asc" <?php echo (($sortDate ?? 'desc') === 'asc') ? 'selected' : ''; ?>>Cũ nhất trước</option>
                </select>
            </div>
            <div class="review-filter-group">
                <label for="sort_order_id">Sắp xếp mã đơn hàng</label>
                <select id="sort_order_id" name="sort_order_id">
                    <option value="desc" <?php echo (($sortOrderId ?? 'desc') === 'desc') ? 'selected' : ''; ?>>Giảm dần</option>
                    <option value="asc" <?php echo (($sortOrderId ?? 'desc') === 'asc') ? 'selected' : ''; ?>>Tăng dần</option>
                </select>
            </div>
            <div class="review-filter-actions">
                <button type="submit" class="btn btn-primary">Lọc</button>
                <a href="/WEB_GR4/admin/reviews" class="btn btn-secondary">Đặt lại</a>
            </div>
        </form>

        <?php if (!empty($reviews)): ?>
            <div class="review-table-wrapper">
                <table class="review-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Người dùng</th>
                            <th>Sản phẩm</th>
                            <th>Mã đơn hàng</th>
                            <th>Sao</th>
                            <th>Bình luận</th>
                            <th>Ngày gửi</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reviews as $index => $review): ?>
                            <tr>
                                <td><span class="review-index"><?php echo (int)($index + 1); ?></span></td>
                                <td>
                                    <div class="review-user-cell">
                                        <div class="review-avatar">
                                            <?php echo strtoupper(substr($review['user_name'], 0, 1)); ?>
                                        </div>
                                        <div><?php echo htmlspecialchars($review['user_name']); ?></div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($review['product_name']); ?></td>
                                <td><?php echo !empty($review['order_id']) && (int)$review['order_id'] > 0 ? '#' . (int)$review['order_id'] : '—'; ?></td>
                                <td>
                                    <span class="review-stars">
                                        <?php echo str_repeat('★', (int)$review['rating']); ?>
                                    </span>
                                </td>
                                <td class="review-comment">
                                    <?php echo !empty($review['comment']) ? htmlspecialchars($review['comment']) : '—'; ?>
                                </td>
                                <td><?php echo htmlspecialchars($review['created_at']); ?></td>
                                <td>
                                    <form method="post" action="/WEB_GR4/admin/reviews/delete" onsubmit="return confirm('Xóa bình luận này?');">
                                        <input type="hidden" name="review_id" value="<?php echo (int)$review['review_id']; ?>">
                                        <button type="submit" class="review-delete-btn">Xóa</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="review-empty-state">
                <i class="fas fa-comment-slash"></i>
                <p>Chưa có đánh giá nào.</p>
            </div>
        <?php endif; ?>
    </div>
</main>
</div>

</body>
</html>
