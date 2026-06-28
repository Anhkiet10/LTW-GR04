<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<section class="product-detail">
    <div class="container">
        <a href="/WEB_GR4/products" class="btn btn-secondary">Quay lại</a>

        <div class="detail-wrap"
             data-images="<?php echo htmlspecialchars(json_encode(array_map(function ($img) {
                 return [
                     'variant_id' => isset($img['variant_id']) ? (int)$img['variant_id'] : null,
                     'image_url'  => $img['image_url'],
                     'is_primary' => (int)$img['is_primary'],
                 ];
             }, $images ?? []), JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>"
             data-variants="<?php echo htmlspecialchars(json_encode(array_map(function ($v) {
                 $keyIds = ($v['variant_key'] ?? '') === 'default'
                     ? []
                     : array_map('intval', explode('_', $v['variant_key']));
                 return [
                     'variant_id'  => (int)$v['variant_id'],
                     'variant_key' => $v['variant_key'],
                     'key_ids'     => $keyIds,
                     'price'       => (float)$v['price'],
                     'stock'       => (int)$v['stock_quantity'],
                 ];
             }, $variants ?? []), JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>"
             data-has-attributes="<?php echo !empty($attributes) ? '1' : '0'; ?>"
             data-attribute-row-count="<?php echo count($attributes ?? []); ?>"
             data-default-price="<?php echo htmlspecialchars(
                 ($product['min_price'] && $product['max_price'])
                     ? ($product['min_price'] == $product['max_price']
                         ? number_format($product['min_price'], 0, ',', '.') . 'đ'
                         : number_format($product['min_price'], 0, ',', '.') . ' - ' . number_format($product['max_price'], 0, ',', '.') . 'đ')
                     : '—'
             , ENT_QUOTES, 'UTF-8'); ?>"
             data-product-id="<?php echo (int)$product['product_id']; ?>">

            <?php if (!empty($product['image_url'])): ?>
                <img id="mainProductImage"
                     src="/WEB_GR4/public<?php echo htmlspecialchars($product['image_url']); ?>"
                     alt="<?php echo htmlspecialchars($product['product_name']); ?>">
            <?php else: ?>
                <div class="no-img-large" id="mainProductImage"><i class="fa-solid fa-box-open" style="color: rgb(177, 151, 252);"></i></div>
            <?php endif; ?>

            <div class="detail-info">
                <h1><?php echo htmlspecialchars($product['product_name']); ?></h1>

                <p class="price" id="productPrice">
                    <?php if ($product['min_price'] && $product['max_price']): ?>
                        <?php
                            if ($product['min_price'] == $product['max_price']) {
                                echo number_format($product['min_price'], 0, ',', '.');
                            } else {
                                echo number_format($product['min_price'], 0, ',', '.') . ' - ' . number_format($product['max_price'], 0, ',', '.');
                            }
                        ?>đ
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </p>

                <p class="variant-stock-info" id="variantStockInfo"></p>

                <?php if (!empty($attributes)): ?>
                    <div class="attributes-section" id="attributesSection">
                        <h3>Chọn cấu hình:</h3>
                        <?php foreach ($attributes as $attr): ?>
                            <div class="attribute-row" data-attribute-id="<?php echo (int)$attr['attribute_id']; ?>">
                                <span class="attribute-label"><?php echo htmlspecialchars($attr['attribute_name']); ?>:</span>
                                <div class="attribute-values">
                                    <?php foreach ($attr['values'] as $val): ?>
                                        <button type="button"
                                                class="attr-value-btn"
                                                data-value-id="<?php echo (int)$val['value_id']; ?>"
                                                data-attribute-id="<?php echo (int)$attr['attribute_id']; ?>">
                                            <?php echo htmlspecialchars($val['value_name']); ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($product['description'])): ?>
                    <p class="product-desc"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                <?php endif; ?>

                <?php if ($product['total_stock'] > 0): ?>
                    <div class="detail-btn-group">
                        <button class="btn-add-cart" id="btnAddCart">
                            <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
                        </button>
                        <button class="btn-buy" id="btnBuyNow"
                            data-product-id="<?php echo (int)$product['product_id']; ?>"
                            data-product-name="<?php echo htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-image-url="<?php echo htmlspecialchars($product['image_url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="fas fa-bolt"></i> Mua ngay
                        </button>
                    </div>
                <?php else: ?>
                    <button class="btn-disabled" disabled>
                        <i class="fas fa-exclamation-circle"></i> Hết hàng
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script src="/WEB_GR4/public/assets/js/user/product_detail.js"></script>
<script src="/WEB_GR4/public/assets/js/user/Detailbuynow.js"></script>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>