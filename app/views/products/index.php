

<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<section class="products-page">
    <div class="container">
        <h2>
            <?php if (!empty($categoryName)): ?>
                <?php echo htmlspecialchars($categoryName); ?>
            <?php elseif (!empty($search)): ?>
                Kết quả: "<?php echo htmlspecialchars($search); ?>"
            <?php else: ?>
                Tất cả sản phẩm
            <?php endif; ?>
        </h2>

        <!-- SEARCH FORM -->
        <form method="GET" action="/WEB_GR4/products" class="search-form-page">
            <input type="text" name="search"
                   placeholder="Tìm kiếm sản phẩm..."
                   value="<?php echo htmlspecialchars($search ?? ''); ?>">
            <button type="submit" class="btn btn-primary">Tìm</button>
            <?php if (!empty($search) || !empty($category)): ?>
                <a href="/WEB_GR4/products" class="btn btn-secondary">Xóa lọc</a>
            <?php endif; ?>
        </form>

        <!-- PRODUCT GRID -->
        <div class="product-grid">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $p): ?>
                    <?php require __DIR__ . '/../layouts/product-card.php'; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="padding:20px 0;color:var(--text-muted)">Không tìm thấy sản phẩm nào.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>