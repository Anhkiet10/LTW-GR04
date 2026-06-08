
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ Admin</title>
    <link rel="stylesheet" href="/WEB_GR4/public/assets/css/admin/style_admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">

</head>
<body>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="admin-logo"><i class="fas fa-store"></i> W4Shop</div>
        <ul class="admin-menu">
            <li><a href="/WEB_GR4/admin" class="active"><i class="fas fa-home"></i> Trang chủ</a></li>
            <li><a href="#"><i class="fas fa-box"></i> Thể loại</a></li>
            <li><a href="#"><i class="fas fa-box-open"></i> Sản phẩm</a></li>
            <li><a href="/WEB_GR4/admin/orders"><i class="fas fa-shopping-cart"></i> Đơn hàng</a></li>
            <li><a href="#"><i class="fas fa-users"></i> Người dùng</a></li>
            <li><a href="#"><i class="fas fa-chart-bar"></i> Thống kê</a></li>
            <li><a href="/WEB_GR4/login" class="logout-link"><i class="fa-solid fa-sign-out-alt" style="color: rgb(177, 151, 252);"></i> Đăng xuất</a></li>
        </ul>
    </aside>

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