<?php require_once __DIR__ . '/../layouts/admin_sidebar.php'; ?>


    <main class="admin-content home-builder">
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
    // Ghim trực tiếp dữ liệu từ PHP vào window để file JS bên ngoài có thể đọc được
    window.W4ShopData = {
        INIT_CATEGORIES: <?php echo $homepageCategoriesJson; ?>,
        INIT_PRODUCTS: <?php echo $homepageProductsJson; ?>,
        AVAIL_PRODUCTS: <?php echo $availableProductsJson; ?>,
        AVAIL_CATEGORIES: <?php echo $availableCategoriesJson; ?>
    };
</script>
<script src="/WEB_GR4/public/assets/js/admin/admin_home.js"></script>
</body>
</html>