<?php require_once __DIR__ . '/../layouts/admin_sidebar.php'; ?>
<link rel="stylesheet" href="/WEB_GR4/public/assets/css/admin/dashboard.css">

<main class="admin-content">
    <!-- ═══ PAGE HEADER ═══ -->
    <div class="page-header">
        <div class="statistics-header">
            <h1><i class="fas fa-chart-bar" style="color:#7c3aed; margin-right:10px;"></i>Thống kê</h1>
            <p class="subtitle">Tổng quan doanh số và hiệu suất bán hàng</p>
        </div>

        <!-- ═══ NAVIGATION TABS ═══ -->
        <div class="statistics-tabs">
            <button class="tab-btn active" data-tab="revenue">
                <i class="fas fa-dollar-sign"></i>
                <span>Tổng doanh thu</span>
            </button>
            <button class="tab-btn" data-tab="orders">
                <i class="fas fa-shopping-cart"></i>
                <span>Số đơn hàng</span>
            </button>
            <button class="tab-btn" data-tab="bestseller">
                <i class="fas fa-star"></i>
                <span>Sản phẩm bán chạy</span>
            </button>
            <button class="tab-btn" data-tab="monthly">
                <i class="fas fa-calendar-alt"></i>
                <span>Thống kê theo tháng</span>
            </button>
        </div>

        <!-- ═══ DATE FILTER ═══ -->
        <div class="filter-section">
            <form method="GET" action="/WEB_GR4/admin/statistics" class="date-filter-form">
                <div class="filter-group">
                    <label><i class="fas fa-calendar-day"></i> Từ ngày</label>
                    <input type="date" name="from" value="<?= htmlspecialchars($from ?? '2026-01-01') ?>" class="date-input">
                </div>

                <div class="filter-group">
                    <label><i class="fas fa-calendar-day"></i> Đến ngày</label>
                    <input type="date" name="to" value="<?= htmlspecialchars($to ?? '2026-12-31') ?>" class="date-input">
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Lọc dữ liệu
                    </button>
                    <a href="/WEB_GR4/admin/statistics" class="btn btn-reset">
                        <i class="fas fa-undo"></i> Đặt lại
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- ════════════════════════════════════════════════════════════ -->
    <!-- TAB 1: TỔNG DOANH THU -->
    <!-- ════════════════════════════════════════════════════════════ -->
    <?php $stats = $dashboardStats ?? []; ?>
    
    <div class="tab-content active" id="revenue-tab">
        <div class="stats-overview">
            <!-- Thẻ thống kê chính -->
            <div class="stats-grid">
                <div class="stat-card stat-revenue">
                    <div class="stat-card-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-card-content">
                        <h3>Tổng doanh thu</h3>
                        <div class="stat-number"><?= number_format($stats['total_revenue'] ?? 0, 0, ',', '.') ?> ₫</div>
                        <span class="stat-period">
                            <i class="fas fa-clock"></i> 
                            <?= date('d/m/Y', strtotime($from ?? '2026-01-01')) ?> - 
                            <?= date('d/m/Y', strtotime($to ?? '2026-12-31')) ?>
                        </span>
                    </div>
                </div>

                <div class="stat-card stat-today">
                    <div class="stat-card-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-card-content">
                        <h3>Hôm nay</h3>
                        <div class="stat-number"><?= number_format($stats['today_revenue'] ?? 0, 0, ',', '.') ?> ₫</div>
                        <span class="stat-period">Doanh thu trong ngày</span>
                    </div>
                </div>

                <div class="stat-card stat-week">
                    <div class="stat-card-icon">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                    <div class="stat-card-content">
                        <h3>Tuần này</h3>
                        <div class="stat-number"><?= number_format($stats['week_revenue'] ?? 0, 0, ',', '.') ?> ₫</div>
                        <span class="stat-period">Doanh thu trong tuần</span>
                    </div>
                </div>

                <div class="stat-card stat-month">
                    <div class="stat-card-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-card-content">
                        <h3>Tháng này</h3>
                        <div class="stat-number"><?= number_format($stats['month_revenue'] ?? 0, 0, ',', '.') ?> ₫</div>
                        <span class="stat-period">Doanh thu trong tháng</span>
                    </div>
                </div>
            </div>

            <!-- Biểu đồ -->
            <div class="chart-section">
                <div class="chart-header">
                    <h3><i class="fas fa-chart-line" style="color:#7c3aed;"></i> Biểu đồ doanh thu theo tháng</h3>
                    <span class="chart-period">Năm <?= date('Y') ?></span>
                </div>
                <div class="chart-wrapper">
                    <canvas id="monthlyRevenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ════════════════════════════════════════════════════════════ -->
    <!-- TAB 2: SỐ ĐƠN HÀNG -->
    <!-- ════════════════════════════════════════════════════════════ -->
    <div class="tab-content" id="orders-tab">
        <div class="stats-overview">
            <!-- Thẻ thống kê đơn hàng -->
            <div class="stats-grid">
                <div class="stat-card stat-orders">
                    <div class="stat-card-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-card-content">
                        <h3>Tổng đơn hàng</h3>
                        <div class="stat-number"><?= number_format($stats['total_orders'] ?? 0) ?></div>
                        <span class="stat-period">
                            <i class="fas fa-clock"></i> 
                            <?= date('d/m/Y', strtotime($from ?? '2026-01-01')) ?> - 
                            <?= date('d/m/Y', strtotime($to ?? '2026-12-31')) ?>
                        </span>
                    </div>
                </div>

                <div class="stat-card stat-completed">
                    <div class="stat-card-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-card-content">
                        <h3>Hoàn thành</h3>
                        <div class="stat-number"><?= number_format($stats['completed_orders'] ?? 0) ?></div>
                        <span class="stat-period">Đơn hàng đã giao thành công</span>
                    </div>
                </div>

                <div class="stat-card stat-pending">
                    <div class="stat-card-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-card-content">
                        <h3>Đang xử lý</h3>
                        <div class="stat-number"><?= number_format($stats['pending_orders'] ?? 0) ?></div>
                        <span class="stat-period">Đơn hàng chờ xử lý</span>
                    </div>
                </div>

                <div class="stat-card stat-average">
                    <div class="stat-card-icon">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <div class="stat-card-content">
                        <h3>Trung bình/đơn</h3>
                        <div class="stat-number">
                            <?php 
                                $avg = ($stats['total_orders'] ?? 0) > 0 
                                    ? ($stats['total_revenue'] ?? 0) / ($stats['total_orders'] ?? 1)
                                    : 0;
                                echo number_format($avg, 0, ',', '.');
                            ?> ₫
                        </div>
                        <span class="stat-period">Giá trị đơn hàng trung bình</span>
                    </div>
                </div>
            </div>

            <!-- Biểu đồ trạng thái đơn hàng -->
            <div class="chart-section">
                <div class="chart-header">
                    <h3><i class="fas fa-chart-pie" style="color:#7c3aed;"></i> Phân bố trạng thái đơn hàng</h3>
                    <span class="chart-period">Tổng số: <?= number_format($stats['total_orders'] ?? 0) ?> đơn</span>
                </div>
                <div class="chart-wrapper">
                    <canvas id="orderStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ════════════════════════════════════════════════════════════ -->
    <!-- TAB 3: SẢN PHẨM BÁN CHẠY -->
    <!-- ════════════════════════════════════════════════════════════ -->
    <div class="tab-content" id="bestseller-tab">
        <div class="stats-overview">
            <!-- Sản phẩm bán chạy nhất -->
            <div class="featured-product">
                <div class="featured-badge">
                    <i class="fas fa-crown"></i> Sản phẩm bán chạy nhất
                </div>
                <div class="featured-content">
                    <div class="featured-info">
                        <h2><?= htmlspecialchars($stats['best_seller']['product_name'] ?? 'Chưa có dữ liệu') ?></h2>
                        <div class="featured-stats">
                            <div class="featured-stat">
                                <span class="label">Số lượng bán</span>
                                <span class="value"><?= number_format($stats['best_seller']['total_sold'] ?? 0) ?></span>
                            </div>
                            <div class="featured-stat">
                                <span class="label">Doanh thu</span>
                                <span class="value"><?= number_format($stats['best_seller']['revenue'] ?? 0, 0, ',', '.') ?> ₫</span>
                            </div>
                        </div>
                    </div>
                    <div class="featured-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                </div>
            </div>

            <!-- Top 5 sản phẩm -->
            <div class="chart-section">
                <div class="chart-header">
                    <h3><i class="fas fa-fire" style="color:#7c3aed;"></i> Top 5 sản phẩm bán chạy</h3>
                    <span class="chart-period">Theo số lượng bán</span>
                </div>
                <div class="chart-wrapper">
                    <canvas id="topProductsChart"></canvas>
                </div>
            </div>

            <!-- Bảng chi tiết -->
            <div class="table-section">
                <div class="table-header">
                    <h3><i class="fas fa-list" style="color:#7c3aed;"></i> Danh sách sản phẩm bán chạy</h3>
                </div>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tên sản phẩm</th>
                                <th>Số lượng bán</th>
                                <th>Doanh thu</th>
                                <th>% tổng doanh thu</th>
                            </tr>
                        </thead>
                        <tbody id="topProductsTableBody">
                            <!-- Điền bởi JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ════════════════════════════════════════════════════════════ -->
    <!-- TAB 4: THỐNG KÊ THEO THÁNG -->
    <!-- ════════════════════════════════════════════════════════════ -->
    <div class="tab-content" id="monthly-tab">
        <div class="stats-overview">
            <!-- Biểu đồ chi tiết -->
            <div class="chart-section full-width">
                <div class="chart-header">
                    <h3><i class="fas fa-calendar-alt" style="color:#7c3aed;"></i> Doanh thu theo từng tháng</h3>
                    <span class="chart-period">Năm <?= date('Y') ?></span>
                </div>
                <div class="chart-wrapper" style="height: 400px;">
                    <canvas id="monthlyDetailChart"></canvas>
                </div>
            </div>

            <!-- Bảng dữ liệu -->
            <div class="table-section">
                <div class="table-header">
                    <h3><i class="fas fa-table" style="color:#7c3aed;"></i> Chi tiết doanh thu theo tháng</h3>
                </div>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Tháng</th>
                                <th>Doanh thu</th>
                                <th>Số đơn hàng</th>
                                <th>Trung bình/đơn</th>
                                <th>Xu hướng</th>
                            </tr>
                        </thead>
                        <tbody id="monthlyTableBody">
                            <!-- Điền bởi JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</main>
</div>

<script>
    // Dữ liệu cho biểu đồ từ PHP
    window.chartData = <?php echo json_encode($chartData ?? []); ?>;
    window.statisticsData = {
        from: '<?= htmlspecialchars($from ?? date('Y-01-01')) ?>',
        to: '<?= htmlspecialchars($to ?? date('Y-m-t')) ?>',
        totalRevenue: <?= $dashboardStats['total_revenue'] ?? 0 ?>,
        totalOrders: <?= $dashboardStats['total_orders'] ?? 0 ?>,
    };
</script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<!-- Statistics Dashboard JS -->
<script src="/WEB_GR4/public/assets/js/admin/statistics-dashboard.js"></script>

</body>
</html>