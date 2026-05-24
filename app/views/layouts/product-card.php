<!-- Dùng biến $p từ vòng lặp ngoài -->
<div class="product-card">
    <a href="/WEB_GR4/products/<?php echo $p['product_id']; ?>">
        <?php if (!empty($p['image_url'])): ?>
            <img src="/WEB_GR4<?php echo htmlspecialchars($p['image_url']); ?>"
                 alt="<?php echo htmlspecialchars($p['product_name']); ?>" loading="lazy">
        <?php else: ?>
            <div class="no-img">📦</div>
        <?php endif; ?>
        <div class="product-info">
            <h3><?php echo htmlspecialchars($p['product_name']); ?></h3>
            <p class="price"><?php echo number_format($p['price'], 0, ',', '.'); ?>đ</p>
            <?php if (isset($p['stock_quantity']) && $p['stock_quantity'] <= 0): ?>
                <span class="out-of-stock">Hết hàng</span>
            <?php endif; ?>
        </div>
    </a>
    <?php if (!isset($p['stock_quantity']) || $p['stock_quantity'] > 0): ?>
        <button class="btn btn-add-cart"
            onclick="addToCart(<?php echo $p['product_id']; ?>)">+ Thêm vào giỏ</button>
    <?php else: ?>
        <button class="btn btn-disabled" disabled>Hết hàng</button>
    <?php endif; ?>
</div>