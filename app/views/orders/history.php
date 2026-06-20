<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php

$totalOrders = count($orders);

$pending = 0;
$shipping = 0;
$completed = 0;
$cancelled = 0;

foreach ($orders as $o) {

    switch ($o['status']) {

        case 'pending':
            $pending++;
            break;

        case 'shipping':
            $shipping++;
            break;

        case 'completed':
            $completed++;
            break;

        case 'cancelled':
            $cancelled++;
            break;
    }
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/WEB_GR4/public/assets/css/user/style.css">
<title>Đơn hàng của tôi</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family: "Be Vietnam Pro", sans-serif;
    background: var(--cream);
    color: var(--soil);
}

.page{
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 16px;
    position: relative;
    z-index: 1;
}

.header{
    margin-bottom: 30px;
}

.header h1{
    font-family: "Playfair Display", serif;
    font-size: 32px;
    color: var(--forest);
    margin-bottom: 8px;
}

.header p{
    font-family: "Lato", sans-serif;
    color: var(--wood);
    font-size: 14px;
}

.stats{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
    margin-bottom:35px;
}

.card{
    background: #fff;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 16px rgba(155, 142, 199, 0.08);
    border: 1px solid var(--mint);
    transition: transform 0.3s, box-shadow 0.3s;
}

.card:hover{
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(155, 142, 199, 0.15);
}

.number{
    font-size: 36px;
    font-weight: 700;
    color: var(--plum);
}

.label{
    color: var(--soil);
    font-size: 14px;
    margin-top: 6px;
    font-weight: 500;
}

.orders-box{
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(155, 142, 199, 0.08);
    border: 1px solid var(--mint);
}

/* Thêm thuộc tính cuộn ngang trên điện thoại để bảng không bị tràn khung */
.table-responsive {
    width: 100%;
    overflow-x: auto;
}

table{
    width:100%;
    border-collapse:collapse;
}

thead{
    background: #fdfbf7;
}

th{
    text-align:left;
    padding: 18px 20px;
    color: var(--forest);
    font-weight: 700;
    font-size: 14px;
    border-bottom: 2px solid var(--mint);
}

td{
    padding: 18px 20px;
    border-top: none;
    border-bottom: 1px solid var(--mint);
    color: var(--soil);
    font-size: 14px;
}

tr:hover{
    background: #faf7fd;
    cursor: pointer;
}

.order-id{
    font-weight:700;
    color: var(--forest);
}

.money{
    color: var(--plum);
    font-weight:700;
}

.badge{
    display:inline-block;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    text-align: center;
    letter-spacing: 0.03em;
}

.pending{
    background: #fff5e6;
    color: #d97706;
}

.shipping{
    background: var(--mint);
    color: var(--forest);
}

.completed{
    background: #e6f7ed;
    color: #15803d;
}

.cancelled{
    background: #fee2e2;
    color: #b91c1c;
}

.view-btn{
    display: inline-block;
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 12px;
    font-family: "Lato", sans-serif;
    font-weight: 700;
    text-decoration: none;
    letter-spacing: 0.04em;
    background: var(--plum);
    color: #fff;
    transition: transform 0.2s, box-shadow 0.2s, opacity 0.2s;
}

.view-btn:hover{
    opacity: 1;
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(155, 142, 199, 0.25);
}

.empty{
    background: #fff;
    border-radius: 16px;
    padding: 50px 20px;
    text-align: center;
    border: 1px solid var(--mint);
    box-shadow: 0 4px 16px rgba(155, 142, 199, 0.08);
}

.shop-btn{
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
}

.shop-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(155, 142, 199, 0.25);
}

</style>

</head>

<body>

<div class="page">

    <div class="header">
        <h1>📦 Đơn hàng của tôi</h1>

        <p>
            Theo dõi các đơn hàng đã đặt
        </p>

    </div>

    <div class="stats">

        <div class="card">
            <div class="number">
                <?= $totalOrders ?>
            </div>
            <div class="label">
                Tổng đơn hàng
            </div>
        </div>

        <div class="card">
            <div class="number">
                <?= $pending ?>
            </div>
            <div class="label">
                Chờ xử lý
            </div>
        </div>

        <div class="card">
            <div class="number">
                <?= $shipping ?>
            </div>
            <div class="label">
                Đang giao
            </div>
        </div>

        <div class="card">
            <div class="number">
                <?= $completed ?>
            </div>
            <div class="label">
                Hoàn thành
            </div>
        </div>

    </div>

    <?php if(empty($orders)): ?>

        <div class="empty">

            <h2>
                Bạn chưa có đơn hàng nào
            </h2>

            <p style="margin-top:10px;color: var(--wood);">
                Hãy mua sắm để tạo đơn hàng đầu tiên
            </p>

            <a class="shop-btn"
               href="/WEB_GR4/products">
                Mua sắm ngay
            </a>

        </div>

    <?php else: ?>

        <div class="table-responsive">
            <div class="orders-box">

                <table>

                    <thead>

                    <tr>
                        <th>Mã đơn</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Ngày đặt</th>
                        <th></th>
                    </tr>

                    </thead>

                    <tbody>

            <?php foreach($orders as $order): ?>

                <tr class="clickable-row" data-href="/WEB_GR4/orders/<?= $order['order_id'] ?>">

                    <td class="order-id">
                        #<?= $order['order_id'] ?>
                    </td>

                    <td class="money">
                        <?= number_format($order['total_amount']) ?> đ
                    </td>

                    <td>
                        <?php
                        $statusClass = $order['status'];
                        if($statusClass == 'paid'){
                            $statusClass = 'completed';
                        }
                        ?>
                        <span class="badge <?= $statusClass ?>">
                            <?= strtoupper($order['status']) ?>
                        </span>
                    </td>

                    <td>
                        <?= date('d/m/Y', strtotime($order['order_date'])) ?>
                    </td>

                    <td>
                        <a class="view-btn" href="/WEB_GR4/orders/<?= $order['order_id'] ?>">
                            Chi tiết
                        </a>
                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

                </table>

            </div>
        </div>

    <?php endif; ?>

</div>
<script src="/WEB_GR4/public/assets/js/user/orders.js"></script>
</body>
</html>