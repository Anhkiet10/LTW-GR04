<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<section class="product-detail">
    <div class="container">
        <a href="/WEB_GR4/products" class="btn btn-secondary">Quay lại</a>

        <div class="detail-wrap">
            <?php if (!empty($product['image_url'])): ?>
                <img src="/WEB_GR4/public<?php echo htmlspecialchars($product['image_url']); ?>"
                     alt="<?php echo htmlspecialchars($product['product_name']); ?>">
            <?php else: ?>
                <div class="no-img-large"><i class="fa-solid fa-box-open" style="color: rgb(177, 151, 252);"></i></div>
            <?php endif; ?>

            <div class="detail-info">
                <h1><?php echo htmlspecialchars($product['product_name']); ?></h1>
                <p class="price"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</p>

                <?php if (!empty($product['description'])): ?>
                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                <?php endif; ?>

                <?php if (isset($product['stock_quantity']) && $product['stock_quantity'] > 0): ?>
                    <p>Còn hàng: <?php echo (int)$product['stock_quantity']; ?></p>
                    <button class="btn btn-add-cart"
                        onclick="addToCart(<?php echo $product['product_id']; ?>)">
                        <i class="fas fa-shopping-cart" style="color: rgb(255, 255, 255);"></i> Thêm vào giỏ hàng
                    </button>
                <?php else: ?>
                    <button class="btn btn-disabled" disabled><i class="fas fa-exclamation-circle" style="color: rgb(255, 255, 255);"></i> Hết hàng</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>