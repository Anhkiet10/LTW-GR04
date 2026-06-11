<?php 
$pageTitle = "Chi tiết đơn hàng";
require_once __DIR__ . '/../layouts/header.php'; 
?>

<!-- Link CSS đúng đường dẫn dự án -->
<link rel="stylesheet" href="/WEB_GR4/public/assets/css/orders.css">

<div class="orders-wrapper">
    <div class="detail-header">
        <!-- Quay lại danh sách đơn hàng theo chuẩn Router -->
        <a href="/WEB_GR4/orders" class="btn-back">&#8592; Quay lại danh sách</a>
        <h2 class="page-title">Chi Tiết Đơn Hàng #DH-10025</h2>
    </div>

    <div class="status-banner">
        <p>Trạng thái đơn hàng: <strong class="status-pending">Chờ xử lý</strong></p>
        <p class="sub-text">Đơn hàng của bạn đang chờ hệ thống xác nhận.</p>
    </div>

    <div class="info-grid">
        <div class="info-card">
            <h3>📍 Địa Chỉ Nhận Hàng</h3>
            <p class="info-name">Nguyễn Văn Đức</p>
            <p>SĐT: (+84) 901 234 567</p>
            <p>Địa chỉ: Số 12 Hoàn Kiếm, Phường Hàng Trống, Quận Hoàn Kiếm, Hà Nội</p>
        </div>
        <div class="info-card">
            <h3>💳 Hình Thức Thanh Toán</h3>
            <p>Phương thức: <strong>Thanh toán khi nhận hàng (COD)</strong></p>
            <p>Tình trạng: <span class="status-unpaid">Chưa thanh toán</span></p>
        </div>
    </div>

    <div class="product-list-container">
        <h3>🛒 Sản Phẩm Đã Đặt</h3>
        <table class="detail-table">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Đơn giá</th>
                    <th>Số lượng</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="prod-cell">
                            <img src="https://via.placeholder.com/60" alt="Product">
                            <div class="prod-name-box">
                                <p class="p-name">Áo Sơ Mi Nam Tay Dài Premium</p>
                                <p class="p-var">Phân loại: Trắng, L</p>
                            </div>
                        </div>
                    </td>
                    <td>350.000đ</td>
                    <td>2</td>
                    <td class="price">700.000đ</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="order-summary-box">
        <div class="summary-row">
            <span>Tổng tiền hàng:</span>
            <span>700.000đ</span>
        </div>
        <div class="summary-row">
            <span>Phí vận chuyển:</span>
            <span>30.000đ</span>
        </div>
        <hr>
        <div class="summary-row total-row">
            <span>Thành tiền:</span>
            <span class="price-large">730.000đ</span>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
