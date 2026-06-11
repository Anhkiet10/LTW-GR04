<?php 
$pageTitle = "Lịch sử đơn hàng";
require_once __DIR__ . '/../layouts/header.php'; 
?>

<!-- Link CSS đúng đường dẫn dự án -->
<link rel="stylesheet" href="/WEB_GR4/public/assets/css/orders.css">

<div class="orders-wrapper">
    <h2 class="page-title">Lịch Sử Đơn Hàng Của Bạn</h2>
    
    <div class="orders-list">
        <!-- Đơn hàng 1 -->
        <div class="order-card">
            <div class="order-header">
                <span class="order-id">Mã đơn: <strong>#DH-10025</strong></span>
                <span class="order-date">Ngày đặt: 26/05/2026</span>
                <span class="order-status status-pending">Chờ xử lý</span>
            </div>
            
            <div class="order-body">
                <div class="product-preview">
                    <img src="https://via.placeholder.com/80" alt="Sản phẩm" class="product-img">
                    <div class="product-info">
                        <h4 class="product-name">Áo Sơ Mi Nam Tay Dài Premium - Trắng</h4>
                        <p class="product-variant">Phân loại: Size L</p>
                        <p class="product-qty">x2</p>
                    </div>
                </div>
            </div>
            
            <div class="order-footer">
                <div class="order-total">
                    Tổng tiền: <span class="price">730.000đ</span>
                </div>
                <div class="order-actions">
                    <!-- Sửa đường dẫn theo chuẩn MVC Router -->
                    <a href="/WEB_GR4/orders/10025" class="btn btn-primary">Xem chi tiết</a>
                </div>
            </div>
        </div>

        <!-- Đơn hàng 2 -->
        <div class="order-card">
            <div class="order-header">
                <span class="order-id">Mã đơn: <strong>#DH-10024</strong></span>
                <span class="order-date">Ngày đặt: 20/05/2026</span>
                <span class="order-status status-completed">Đã giao hàng</span>
            </div>
            
            <div class="order-body">
                <div class="product-preview">
                    <img src="https://via.placeholder.com/80" alt="Sản phẩm" class="product-img">
                    <div class="product-info">
                        <h4 class="product-name">Quần Tây Nam Slimfit Cao Cấp - Đen</h4>
                        <p class="product-variant">Phân loại: Size 32</p>
                        <p class="product-qty">x1</p>
                    </div>
                </div>
            </div>
            
            <div class="order-footer">
                <div class="order-total">
                    Tổng tiền: <span class="price">450.000đ</span>
                </div>
                <div class="order-actions">
                    <!-- Sửa đường dẫn theo chuẩn MVC Router -->
                    <a href="/WEB_GR4/orders/10024" class="btn btn-outline">Xem chi tiết</a>
                    <button class="btn btn-primary">Mua lại</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
