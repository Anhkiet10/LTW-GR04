
<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<link rel="stylesheet" href="/WEB_GR4/public/assets/css/user/Cart.css">


    <div class="cart-container">
        <h1>Giỏ hàng</h1>
        <?php if(!empty($items)):?>
            <?php $total =0; ?>
            <table class="cart-table" >
                <thead>
                    <tr>
                        <th></th>
                        <th>Sản phẩm </th>
                        <th>Mô tả</th>
                        <th>Biến thể</th>
                        <th>Đơn giá</th>
                        <th>Số lượng</th>
                        <th>Thành tiền</th>
                        <th>Xóa</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach($items as $item):?>
                        <?php $subtotal = $item['price_snapshot']* $item['quantity'];?>
                        <tr>
                            <td>
                                <input type="checkbox" class="cart-check" 
                                    data-id="<?php echo $item['cart_item_id']; ?>">
                            </td>
                            <td><?php echo $item['product_name'];?></td>
                            <td><?php echo $item['description'];?></td>
                            <td><?php echo $item['variant_key'];?></td>
                            <td class="price-cart" data-price="<?php echo $item['price_snapshot']; ?>">
                                <?php echo number_format($item['price_snapshot'],0,',','.');?>đ</td>
                            <td>                     
                                <button class="btn-min" 
                                    data-id="<?php echo $item['cart_item_id'];?>">
                                    -
                                </button>

                                <span class="quantity">
                                    <?php echo $item['quantity'];?>
                                </span>

                                <button class="plus" 
                                    data-id="<?php echo $item['cart_item_id'];?>">
                                    +
                                </button>
                            </td>

                            <td class="subtotal">
                                <?php echo number_format($subtotal,0,',','.');?>đ                           
                            </td>

                            <td>
                                <button class="btn-delete" data-id="<?php
                                    echo $item['cart_item_id'];?>">
                                     Xóa
                                    <i class="fa-solid fa-delete-left"></i>
                                </button>

                            </td>
                        </tr>
                    <?php endforeach;?>
                </tbody>
            </table>

            <div class="cart-total">
                <h2>
                    Tổng thanh toán:
                    <span id="total-price">
                        0đ
                    </span>
                </h2>
            </div>

            <div class="cart-action">
                <a href="/WEB_GR4/products" class="btn">
                    Tiếp tục mua sắm
                </a>
                <a id="btnOrder" class="btn">
                    Đặt hàng
                </a>
            </div>
            <div id="addressModal" class="address-modal" style="display: none;">
                <div class="address-content">
                    <h3>Nhập thông tin giao hàng</h3>

                    <form id="addressForm">
                        <input type="text" name="phone" 
                        placeholder="Vui lòng nhập số điện thoại" required>
                        
                        <input type="text" name="label" placeholder="Nhà / Công ty" value="Nhà" required>

                        <input type="text" name="city" 
                        placeholder="Vui lòng nhập tên thành phố" required>

                        <input type="text" name="full_address"
                        placeholder="Vui lòng nhập đầy đủ địa chỉ" required>
                        <button type="submit" class="btn">Lưu thông tin</button>
                    </form>
                </div>
            </div>
    <?php else: ?>
        <h2>Giỏ hàng đang trống</h2>
        <a href="/WEB_GR4/products">
            Mua sắm ngay
        </a>
        <?php endif;?>
    </div>

<script src="/WEB_GR4/public/assets/js/user/Cart.js"></script>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>