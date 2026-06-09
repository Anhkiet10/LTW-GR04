<!-- Dùng biến $p từ vòng lặp ngoài -->
<div class="product-card" data-product-id="<?php echo $p['product_id']; ?>">
    <a href="/WEB_GR4/products/<?php echo $p['product_id']; ?>">
        <?php if (!empty($p['image_url'])): ?>
            <img src="/WEB_GR4/public<?php echo htmlspecialchars($p['image_url']); ?>"
                 alt="<?php echo htmlspecialchars($p['product_name']); ?>" loading="lazy">
        <?php else: ?>
            <div class="no-img"><i class="fa-solid fa-box-open" style="color: rgb(177, 151, 252);"></i></div>
        <?php endif; ?>
        <div class="product-info">
            <!-- Hiển thị danh mục cha và con -->
            <?php if (!empty($p['prod_parent_id']) && !empty($p['parent_cat_name'])): ?>
                <div class="product-categories">
                    <span class="category-parent"><?php echo htmlspecialchars($p['parent_cat_name']); ?></span>
                    <i class="fas fa-chevron-right"></i>
                    <span class="category-child"><?php echo htmlspecialchars($p['prod_cat_name']); ?></span>
                </div>
            <?php else: ?>
                <div class="product-categories">
                    <span class="category-main"><?php echo htmlspecialchars($p['prod_cat_name'] ?? 'Chưa phân loại'); ?></span>
                </div>
            <?php endif; ?>
            <h3><?php echo htmlspecialchars($p['product_name']); ?></h3>
            <p class="price"><?php
                if ($p['min_price'] && $p['max_price']) {
                    if ($p['min_price'] == $p['max_price']) {
                        echo number_format($p['min_price'], 0, ',', '.');
                    } else {
                        echo number_format($p['min_price'], 0, ',', '.') . ' - ' . number_format($p['max_price'], 0, ',', '.');
                    }
                } else {
                    echo 'Liên hệ';
                }
            ?>đ</p>
            <?php if (isset($p['total_stock']) && $p['total_stock'] <= 0): ?>
                <span class="out-of-stock">Hết hàng</span>
            <?php endif; ?>
        </div>
    </a>
    <?php if (!isset($p['total_stock']) || $p['total_stock'] > 0): ?>
        <button class="btn btn-add-cart"
            onclick="window.location.href='/WEB_GR4/products/<?php echo $p['product_id']; ?>'">+ Thêm vào giỏ</button>
    <?php else: ?>
        <button class="btn btn-disabled" disabled>Hết hàng</button>
    <?php endif; ?>
</div>