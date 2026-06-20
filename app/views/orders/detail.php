<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php
// $order
// $items
?>


<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chi tiết đơn hàng</title>
<link rel="stylesheet" href="/WEB_GR4/public/assets/css/user/style.css">

<style>

body{
    font-family: "Be Vietnam Pro", sans-serif;
    background: var(--cream);
    color: var(--soil);
    margin:0;
}

.container{
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 16px;
}

.card{
    background: #fff;
    border: 1px solid var(--mint);
    border-radius: 16px;
    margin-bottom: 24px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(155, 142, 199, 0.08);
}

.card-header{
    background: #fbf9f5;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    border-bottom: 1px solid var(--mint);
}

.card-header small {
    color: var(--wood);
    font-weight: 600;
}

.card-header strong {
    color: var(--forest);
}

.card-body{
    padding: 24px;
}

.btn{
    display: inline-block;
    padding: 10px 24px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    font-family: "Lato", sans-serif;
    font-weight: 700;
    text-decoration: none;
    letter-spacing: 0.04em;
    background: var(--plum);
    color: #fff;
    transition: transform 0.2s, box-shadow 0.2s;
    box-shadow: 0 2px 8px rgba(155, 142, 199, 0.15);
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(155, 142, 199, 0.25);
}

.product{
    display: flex;
    gap: 20px;
    border-top: 1px solid var(--mint);
    padding: 20px;
    align-items: center;
}

.product:first-child{
    border-top:none;
}

.product img{
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid var(--mint);
    background: #fff;
}

.product-info{
    flex:1;
}

.product-name{
    font-size: 16px;
    font-weight: 700;
    color: var(--bark);
    margin-bottom: 6px;
}

.price{
    color: var(--plum);
    font-weight: 700;
    font-size: 15px;
}

.status{
    font-size: 20px;
    font-weight: 700;
    color: var(--forest);
    margin-bottom: 20px;
}

/* Định dạng thanh tiến trình mượt mà theo cấu trúc cũ của bạn */
.progress{
    display: flex;
    justify-content: space-between;
    position: relative;
    margin-top: 25px;
}

.progress::before {
    content: "";
    position: absolute;
    top: 16px;
    left: 10%;
    right: 10%;
    height: 4px;
    background: var(--accent);
    z-index: 1;
}

.step{
    flex: 1;
    text-align: center;
    position: relative;
    z-index: 2;
}

.step p {
    font-size: 13px;
    font-weight: 600;
    color: var(--soil);
    margin-top: 8px;
}

.circle{
    width: 36px;
    height: 36px;
    line-height: 34px;
    border-radius: 50%;
    background: #fff;
    border: 2px solid var(--accent);
    color: var(--wood);
    margin: 0 auto;
    font-weight: bold;
    transition: all 0.3s;
}

/* Kế thừa lớp class active cũ để đồng bộ sang màu của dự án */
.active{
    background: var(--plum) !important;
    border-color: var(--plum) !important;
    color: #fff !important;
    box-shadow: 0 0 10px rgba(155, 142, 199, 0.4);
}

.summary{
    background: #fff;
    border-radius: 16px;
    border: 1px solid var(--mint);
    padding: 24px;
    box-shadow: 0 4px 16px rgba(155, 142, 199, 0.08);
}

.summary h3{
    font-family: "Playfair Display", serif;
    font-size: 20px;
    color: var(--forest);
    margin-bottom: 16px;
    border-bottom: 2px solid var(--mist);
    padding-bottom: 8px;
    margin-top: 0;
}

.summary p {
    font-size: 14px;
    line-height: 1.8;
    color: var(--soil);
    margin-bottom: 8px;
}

/* Tối ưu hiển thị responsive cho khối các bước trạng thái trên màn hình di động */
@media (max-width: 768px) {
    .progress { flex-direction: column; gap: 20px; align-items: flex-start; padding-left: 30px; }
    .progress::before { display: none; }
    .step { display: flex; align-items: center; gap: 15px; text-align: left; }
    .circle { margin: 0; }
    .product { flex-direction: column; align-items: flex-start; gap: 12px; }
}

</style>

</head>
<body>

<div class="container">

    <div class="card">

        <div class="card-header">

            <div>
                <small>ĐẶT NGÀY</small><br>
                <strong><?= $order['order_date'] ?></strong>
            </div>

            <div>
                <small>TỔNG TIỀN</small><br>
                <strong class="price">
                    <?= number_format($order['total_amount']) ?>₫
                </strong>
            </div>

            <div>
                <small>MÃ ĐƠN</small><br>
                <strong>#<?= $order['order_id'] ?></strong>
            </div>

        </div>

        <div class="card-body">

            <?php

            $statusText = 'Đang xử lý';
            $step = 1;

            if($order['status'] == 'pending'){
                $statusText = 'Đang xử lý';
                $step = 1;
            }

            if($order['status'] == 'paid'){
                $statusText = 'Đã thanh toán';
                $step = 2;
            }

            if($order['status'] == 'shipping'){
                $statusText = 'Đang giao hàng';
                $step = 3;
            }

            if($order['status'] == 'completed'){
                $statusText = 'Đã giao thành công';
                $step = 4;
            }

            if($order['status'] == 'cancelled'){
                $statusText = 'Đã hủy';
                $step = 0;
            }

            ?>

            <div class="status">
                Trạng thái hiện tại: <?= $statusText ?>
            </div>

            <div class="progress">

                <div class="step step-order">
                    <div class="circle <?= $step>=1?'active':'' ?>">
                        ✓
                    </div>
                    <p>Đặt hàng</p>
                </div>

                <div class="step step-order">
                    <div class="circle <?= $step>=2?'active':'' ?>">
                        ✓
                    </div>
                    <p>Thanh toán</p>
                </div>

                <div class="step step-order">
                    <div class="circle <?= $step>=3?'active':'' ?>">
                        ✓
                    </div>
                    <p>Giao hàng</p>
                </div>

                <div class="step step-order">
                    <div class="circle <?= $step>=4?'active':'' ?>">
                        ✓
                    </div>
                    <p>Hoàn tất</p>
                </div>

            </div>

        </div>

    </div>

    <div class="summary">

        <h3>📍 Thông tin giao hàng</h3>

        <p>
            <strong>Khách hàng:</strong>
            <?= $order['full_name'] ?? '' ?>
        </p>

        <p>
            <strong>Email:</strong>
            <?= $order['email'] ?? '' ?>
        </p>

        <p>
            <strong>SĐT:</strong>
            <?= $order['phone'] ?? '' ?>
        </p>

        <p>
            <strong>Địa chỉ:</strong>
            <?= $order['full_address'] ?? '' ?>
        </p>

    </div>

    <br>

    <div class="card">

        <div class="card-body" style="border-bottom: 1px solid var(--mint); background: #fbf9f5;">

            <h2 style="margin:0; font-family:'Playfair Display', serif; color: var(--forest); font-size: 20px;">🛍️ Sản phẩm trong đơn hàng</h2>

        </div>

        <?php foreach($items as $item): ?>

        <div class="product">

            <div>

                <?php if(!empty($item['image_url'])): ?>
                    <img src="/WEB_GR4/public/<?= $item['image_url'] ?>">
                <?php else: ?>
                    <img src="https://via.placeholder.com/150" alt="No image">
                <?php endif; ?>

            </div>

            <div class="product-info">

                <div class="product-name">
                    <?= $item['product_name'] ?>
                </div>

                <?php if(!empty($item['sku'])): ?>
                    <p style="font-size: 13px; color: var(--wood); margin-bottom: 4px;">SKU: <strong><?= $item['sku'] ?></strong></p>
                <?php endif; ?>

                <p style="font-size: 13px; color: var(--wood); margin-bottom: 4px;">Số lượng: <strong><?= $item['quantity'] ?></strong></p>

                <p class="price">
                    Đơn giá: <?= number_format($item['unit_price']) ?>₫
                </p>

                <p style="font-size: 13px; color: var(--soil); margin-top: 6px;">
                    Thành tiền:
                    <strong class="price">
                        <?= number_format(
                            $item['unit_price'] * $item['quantity']
                        ) ?>₫
                    </strong>
                </p>

            </div>

        </div>

        <?php endforeach; ?>

    </div>

    <a class="btn" href="/WEB_GR4/orders" style="margin-top: 10px;">
        ← Quay lại đơn hàng
    </a>

</div>
<script src="/WEB_GR4/public/assets/js/user/orders.js"></script>

</body>
</html>