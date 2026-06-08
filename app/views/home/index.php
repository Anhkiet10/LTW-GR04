<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<section class="hero">
    <div class="container">
        <h1>Chào mừng đến W4Shop</h1>
        <p>Giao hàng nhanh, Giá tốt mỗi ngày</p>
        <a href="/WEB_GR4/products" class="btn btn-primary">Xem sản phẩm</a>
    </div>
</section>

<?php if (!empty($categoryProducts)): ?>
    <?php foreach ($categoryProducts as $catName => $catData): ?>
        <section class="category-section">
            <div class="container">
                <div class="category-header">
                    <h2><i class="fa-solid fa-diamond" style="color: rgb(177, 151, 252);"></i> <?php echo htmlspecialchars($catName); ?></h2>
                    <a href="/WEB_GR4/products?category=<?php echo (int)$catData['id']; ?>" class="view-more-link">
                        Xem thêm <i class="fas fa-arrow-right" style="color: rgb(177, 151, 252);"></i>
                    </a>
                </div>
                <?php if (!empty($catData['products'])): ?>
                    <div class="product-grid">
                        <?php foreach ($catData['products'] as $p): ?>
                            <?php $categoryContext = ['parent_id' => $catData['id'], 'parent_name' => $catName]; ?>
                            <?php require __DIR__ . '/../layouts/product-card.php'; ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-products">
                        <p>Chưa có sản phẩm trong danh mục này.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>