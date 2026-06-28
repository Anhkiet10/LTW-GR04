<?php require_once __DIR__ . '/../layouts/admin_sidebar.php'; ?>
<link rel="stylesheet" href="/WEB_GR4/public/assets/css/admin/dashboard.css">
<main class="admin-content home-builder">
    <div class="page-header">
        <!-- ═══ DASHBOARD STATS - CHỈ HIỂN THỊ NẾU CÓ DATA ═══ -->
        <?php
            $stats = $dashboardStats ?? null;
            $chartData = $chartData ?? null;
        ?>

        <?php if ($stats && $chartData): ?>
            <div class="dashboard-header">
                <div>
                    <h1><i class="fas fa-chart-line" style="color:#7c3aed; margin-right:8px;"></i>Bảng điều khiển</h1>
                    <p>Tổng quan về doanh số, đơn hàng và sản phẩm bán chạy</p>
                </div>

                <!-- Filter theo ngày -->
                <div class="filter-date">
                    <form method="GET" action="/WEB_GR4/admin" class="date-range-form">
                        <input type="date" name="from" value="<?= htmlspecialchars($from ?? '') ?>" class="date-input">
                        <span class="date-separator">đến</span>
                        <input type="date" name="to" value="<?= htmlspecialchars($to ?? '') ?>" class="date-input">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-filter"></i> Lọc
                        </button>
                    </form>
                </div>
            </div>

            <!-- ═══ STAT CARDS ═══ -->
            <div class="dashboard-stats">

                <div class="stat-card stat-revenue">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Tổng doanh thu</h3>
                        <div class="value">
                            <?= number_format($stats['total_revenue'] ?? 0, 0, ',', '.') ?> ₫
                        </div>
                        <small class="stat-period">
                            Hôm nay: <?= number_format($stats['today_revenue'] ?? 0, 0, ',', '.') ?> ₫
                        </small>
                    </div>
                </div>

                <div class="stat-card stat-orders">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Số đơn hàng</h3>
                        <div class="value">
                            <?= $stats['total_orders'] ?? 0 ?>
                        </div>
                        <small class="stat-period">
                            Tuần này: <?= number_format($stats['week_revenue'] ?? 0, 0, ',', '.') ?> ₫
                        </small>
                    </div>
                </div>

                <div class="stat-card stat-bestseller">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Sản phẩm bán chạy</h3>
                        <div class="value">
                            <?= htmlspecialchars($stats['best_seller']['product_name'] ?? 'Chưa có dữ liệu') ?>
                        </div>
                        <small class="stat-period">
                            Bán: <?= $stats['best_seller']['total_sold'] ?? 0 ?> sản phẩm
                        </small>
                    </div>
                </div>

            </div>

            <!-- ═══ CHARTS SECTION ═══ -->
            <div class="charts-grid">

                <!-- Biểu đồ doanh thu theo tháng -->
                <div class="chart-container">
                    <div class="chart-header">
                        <h3>Doanh thu theo tháng (năm <?= date('Y') ?>)</h3>
                    </div>
                    <canvas id="monthlyRevenueChart"></canvas>
                </div>

                <!-- Biểu đồ sản phẩm bán chạy -->
                <div class="chart-container">
                    <div class="chart-header">
                        <h3>Top 5 sản phẩm bán chạy</h3>
                    </div>
                    <canvas id="topProductsChart"></canvas>
                </div>

                <!-- Biểu đồ doanh thu theo danh mục -->
                <div class="chart-container">
                    <div class="chart-header">
                        <h3>Doanh thu theo danh mục</h3>
                    </div>
                    <canvas id="categoryRevenueChart"></canvas>
                </div>

                <!-- Biểu đồ trạng thái đơn hàng -->
                <div class="chart-container">
                    <div class="chart-header">
                        <h3>Trạng thái đơn hàng</h3>
                    </div>
                    <canvas id="orderStatusChart"></canvas>
                </div>

            </div>

            <!-- ═══ DIVIDER ═══ -->
            <hr style="margin: 40px 0; border: none; border-top: 1px solid #e5e7eb;">
        <?php endif; ?>
    </div>

    <!-- ═══ HOMEPAGE BUILDER ═══ -->
    <div class="page-header">
        <h1><i class="fas fa-edit" style="color:#7c3aed; margin-right:8px;"></i>Chỉnh sửa trang chủ</h1>
        <p>Thêm danh mục và sản phẩm sẽ hiển thị với khách hàng. Kéo thả để sắp xếp thứ tự.</p>
    </div>

    <!-- Builder canvas: categories + products, mirrors the homepage layout -->
    <div class="builder-canvas" id="builderCanvas">
        <!-- Rendered by JS from PHP data -->
    </div>

    <!-- Add category trigger -->
    <div class="add-cat-row" id="addCatTrigger" onclick="openCatModal()" style="margin-top:16px;">
        <i class="fas fa-plus-circle"></i>
        <span>Thêm danh mục</span>
    </div>

    <!-- Save bar -->
    <div class="save-bar">
        <span class="save-bar-hint"><strong id="changeCount">0</strong> thay đổi chưa lưu</span>
        <button class="btn-save" id="saveBtn" onclick="saveAll()">
            <i class="fas fa-save"></i> Lưu thay đổi
        </button>
    </div>
</main>
</div>

<!-- ── Modal: chọn danh mục ── -->
<div class="modal-overlay" id="catModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-folder-plus" style="color:#7c3aed; margin-right:6px;"></i>Chọn danh mục</h3>
            <button class="modal-close" onclick="closeCatModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <input type="text" class="modal-search" id="catSearch" placeholder="Tìm danh mục..." oninput="filterCats()">
            <div id="catList"></div>
        </div>
    </div>
</div>

<!-- ── Modal: chọn sản phẩm ── -->
<div class="modal-overlay" id="prodModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-box" style="color:#7c3aed; margin-right:6px;"></i>Thêm sản phẩm</h3>
            <button class="modal-close" onclick="closeProdModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <input type="text" class="modal-search" id="prodSearch" placeholder="Tìm sản phẩm..." oninput="filterProds()">
            <div id="prodList"></div>
        </div>
    </div>
</div>

<script>
    // Dữ liệu cho biểu đồ từ PHP (chỉ khởi tạo nếu có chartData)
    <?php if ($chartData): ?>
        window.chartData = <?php echo json_encode($chartData); ?>;
    <?php endif; ?>
    
    // Ghim trực tiếp dữ liệu từ PHP vào window để file JS bên ngoài có thể đọc được
    window.W4ShopData = {
        INIT_CATEGORIES: <?php echo $homepageCategoriesJson ?? '[]'; ?>,
        INIT_PRODUCTS: <?php echo $homepageProductsJson ?? '[]'; ?>,
        AVAIL_PRODUCTS: <?php echo $availableProductsJson ?? '[]'; ?>,
        AVAIL_CATEGORIES: <?php echo $availableCategoriesJson ?? '[]'; ?>
    };
</script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<!-- Admin Dashboard JS -->
<script src="/WEB_GR4/public/assets/js/admin/admin_dashboard.js"></script>

<!-- Admin Home Builder JS -->
<script src="/WEB_GR4/public/assets/js/admin/admin_home.js"></script>

</body>
</html>