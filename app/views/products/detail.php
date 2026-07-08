<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<?php
$renderStars = function ($rating) {
    $full = max(0, min(5, (int)$rating));
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        $html .= $i <= $full ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
    }
    return $html;
};

$reviewCount = count($reviews ?? []);
$avgRating = 0;
if ($reviewCount > 0) {
    $avgRating = round(array_sum(array_column($reviews, 'rating')) / $reviewCount, 1);
}
?>

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
             data-product-id="<?php echo (int)$product['product_id']; ?>"
             data-logged-in="<?php echo isset($_SESSION['user_id']) ? '1' : '0'; ?>">

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

    <div class="container" style="margin-top: 32px;">
        <div class="product-reviews-section" style="background: #fff; border: 1px solid #e9e9e9; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
            <h3 style="margin-bottom: 12px;">Đánh giá & Bình luận</h3>

            <div style="display: flex; flex-wrap: wrap; gap: 16px; align-items: center; margin-bottom: 16px;">
                <div style="font-size: 1.2rem; font-weight: 600;">
                    <?php echo number_format($avgRating, 1); ?> / 5
                    <span style="margin-left: 6px; color: #f4b400;">
                        <?php echo $renderStars($avgRating); ?>
                    </span>
                </div>
                <div style="color: #666;">
                    <?php echo $reviewCount; ?> đánh giá
                </div>
            </div>

            <?php if (!empty($reviewMessage)): ?>
                <div style="background: #eefbf2; color: #1f7a3b; padding: 10px 12px; border-radius: 8px; margin-bottom: 16px;">
                    <?php echo htmlspecialchars($reviewMessage); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['user_id'])): ?>
                <?php if ($canReview): ?>
                    <form method="post" action="/WEB_GR4/products/<?php echo (int)$product['product_id']; ?>/review" style="margin-bottom: 20px;">
                        <div style="margin-bottom: 10px;">
                            <label for="reviewRating" style="display: block; margin-bottom: 6px; font-weight: 600;">Đánh giá của bạn</label>
                            <select id="reviewRating" name="rating" required style="padding: 8px 10px; border-radius: 6px; border: 1px solid #ccc; min-width: 140px;">
                                <option value="5" <?php echo (!empty($currentUserReview) && $currentUserReview['rating'] == 5) ? 'selected' : ''; ?>>5 sao</option>
                                <option value="4" <?php echo (!empty($currentUserReview) && $currentUserReview['rating'] == 4) ? 'selected' : ''; ?>>4 sao</option>
                                <option value="3" <?php echo (!empty($currentUserReview) && $currentUserReview['rating'] == 3) ? 'selected' : ''; ?>>3 sao</option>
                                <option value="2" <?php echo (!empty($currentUserReview) && $currentUserReview['rating'] == 2) ? 'selected' : ''; ?>>2 sao</option>
                                <option value="1" <?php echo (!empty($currentUserReview) && $currentUserReview['rating'] == 1) ? 'selected' : ''; ?>>1 sao</option>
                            </select>
                        </div>
                        <div style="margin-bottom: 10px;">
                            <label for="reviewComment" style="display: block; margin-bottom: 6px; font-weight: 600;">Bình luận</label>
                            <textarea id="reviewComment" name="comment" rows="4" placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm..." style="width: 100%; max-width: 640px; padding: 10px; border-radius: 8px; border: 1px solid #ccc;"><?php echo !empty($currentUserReview) ? htmlspecialchars($currentUserReview['comment'] ?? '') : ''; ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="border: none;">
                            <?php echo !empty($currentUserReview) ? 'Cập nhật đánh giá' : 'Gửi đánh giá'; ?>
                        </button>
                    </form>
                <?php else: ?>
                    <p style="margin-bottom: 20px; color: #666;">
                        <?php echo htmlspecialchars($reviewRestrictionMessage ?: 'Bạn cần mua sản phẩm này trước khi gửi đánh giá.'); ?>
                    </p>
                <?php endif; ?>
            <?php else: ?>
                <p style="margin-bottom: 20px; color: #666;">
                    Vui lòng <a href="/WEB_GR4/login">đăng nhập</a> để đánh giá sản phẩm.
                </p>
            <?php endif; ?>

            <?php if (!empty($reviews)): ?>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <?php foreach ($reviews as $review): ?>
                        <div style="border-top: 1px solid #eee; padding-top: 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; flex-wrap: wrap; gap: 8px;">
                                <strong><?php echo htmlspecialchars($review['full_name']); ?></strong>
                                <span style="color: #f4b400;">
                                    <?php echo $renderStars($review['rating']); ?>
                                </span>
                            </div>
                            <div style="color: #888; font-size: 0.9rem; margin-bottom: 8px;">
                                <?php echo htmlspecialchars($review['created_at']); ?>
                            </div>
                            <div style="color: #444; line-height: 1.5;">
                                <?php echo !empty($review['comment']) ? nl2br(htmlspecialchars($review['comment'])) : 'Không có bình luận.'; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color: #666; margin: 0;">Chưa có đánh giá nào cho sản phẩm này.</p>
            <?php endif; ?>
        </div>
    </div>

    <div id="addressModal" class="address-modal" style="display: none;">
        <div class="address-content">
            <h3>Nhập thông tin giao hàng</h3>
            <span class="close">X</span>

            <form id="addressForm" class="addressForm">
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
</section>

<link rel="stylesheet" href="/WEB_GR4/public/assets/css/user/Cart.css">
<script src="/WEB_GR4/public/assets/js/user/product_detail.js"></script>
<script src="/WEB_GR4/public/assets/js/user/Detailbuynow.js"></script>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>