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

                <?php if ($product['min_price'] && $product['max_price']): ?>
                    <p class="price">
                        <?php
                            if ($product['min_price'] == $product['max_price']) {
                                echo number_format($product['min_price'], 0, ',', '.');
                            } else {
                                echo number_format($product['min_price'], 0, ',', '.') . ' - ' . number_format($product['max_price'], 0, ',', '.');
                            }
                        ?>đ
                    </p>
                <?php endif; ?>

                <?php if (!empty($product['description'])): ?>
                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                <?php endif; ?>

                <?php if (!empty($variants)): ?>
                    <div class="variants-section">
                        <h3>Chọn phiên bản:</h3>
                        <div id="variantList">
                            <?php foreach ($variants as $v): ?>
                                <div class="variant-option" data-variant-id="<?php echo $v['variant_id']; ?>"
                                     data-stock="<?php echo $v['stock_quantity']; ?>"
                                     data-price="<?php echo $v['price']; ?>">
                                    <span class="variant-sku"><?php echo htmlspecialchars($v['sku'] ?? ''); ?></span>
                                    <span class="variant-attrs"><?php echo htmlspecialchars($v['attributes'] ?? 'Mặc định'); ?></span>
                                    <span class="variant-price"><?php echo number_format($v['price'], 0, ',', '.'); ?>đ</span>
                                    <span class="variant-stock">
                                        <?php echo $v['stock_quantity'] > 0 ? 'Còn: ' . $v['stock_quantity'] : 'Hết hàng'; ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($product['total_stock'] > 0): ?>
                    <button class="btn btn-add-cart"
                        onclick="addToCart(<?php echo $product['product_id']; ?>, getSelectedVariantId())">
                        <i class="fas fa-shopping-cart" style="color: rgb(255, 255, 255);"></i> Thêm vào giỏ hàng
                    </button>
                <?php else: ?>
                    <button class="btn btn-disabled" disabled><i class="fas fa-exclamation-circle" style="color: rgb(255, 255, 255);"></i> Hết hàng</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
function getSelectedVariantId() {
    const selected = document.querySelector('.variant-option.selected');
    return selected ? selected.dataset.variantId : (document.querySelector('.variant-option')?.dataset.variantId || '');
}

document.addEventListener('DOMContentLoaded', function() {
    const variants = document.querySelectorAll('.variant-option');
    if (variants.length > 0) {
        variants[0].classList.add('selected');
    }

    variants.forEach(v => {
        v.addEventListener('click', function() {
            variants.forEach(item => item.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>