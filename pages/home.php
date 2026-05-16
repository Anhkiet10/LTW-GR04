<?php
$pageTitle = "Trang chủ";
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

// Lấy danh sách category
$catResult = mysqli_query($conn, "SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != '' ORDER BY category");
$categories = [];
if ($catResult) {
    while ($row = mysqli_fetch_assoc($catResult)) {
        $categories[] = $row['category'];
    }
}
// Fallback nếu chưa có cột category
if (empty($categories)) {
    $categories = ['Nổi bật'];
}
?>

<!-- HERO -->
<section class="hero">
    <div class="container">
        <h1>Chào mừng đến W4Shop</h1>
        <p>Mua sắm dễ dàng – Giao hàng nhanh chóng – Giá tốt mỗi ngày</p>
        <a href="/WEB_GR4/pages/products.php" class="btn btn-white">Xem tất cả sản phẩm</a>
    </div>
</section>

<!-- PHÂN LOẠI SẢN PHẨM -->
<div class="container">

<?php if (!empty($categories) && $categories[0] !== 'Nổi bật'): ?>
    <?php foreach ($categories as $cat): ?>
        <?php
        $catSafe = mysqli_real_escape_string($conn, $cat);
        $sql = "SELECT * FROM products WHERE category = '$catSafe' ORDER BY created_at DESC LIMIT 4";
        $products = mysqli_query($conn, $sql);
        if (!$products || mysqli_num_rows($products) === 0) continue;
        ?>
        <section class="category-section">
            <div class="category-header">
                <h2><?php echo htmlspecialchars($cat); ?></h2>
                <a href="/WEB_GR4/pages/products.php?category=<?php echo urlencode($cat); ?>" class="btn btn-outline">Xem thêm →</a>
            </div>
            <div class="product-grid">
                <?php while ($p = mysqli_fetch_assoc($products)): ?>
                    <div class="product-card">
                    <a href="/WEB_GR4/pages/product-detail.php?id=<?php echo $p['id']; ?>">
                        <?php if (!empty($p['image'])): ?>
                            <img src="/WEB_GR4/assets/upload/<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                        <?php else: ?>
                            <div class="no-img">📦</div>
                        <?php endif; ?>
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($p['name']); ?></h3>
                            <p class="price"><?php echo number_format($p['price'], 0, ',', '.'); ?>đ</p>
                        </div>
                    </a>
                    <button class="btn btn-add-cart" onclick="addToCart(<?php echo $p['id']; ?>)">+ Thêm vào giỏ</button>
                </div>
                <?php endwhile; ?>
            </div>
        </section>
    <?php endforeach; ?>

<?php else: ?>
    <!-- Fallback: hiện tất cả sản phẩm mới nhất -->
    <section class="category-section">
        <div class="category-header">
            <h2>Sản phẩm nổi bật</h2>
            <a href="/WEB_GR4/pages/products.php" class="btn btn-outline">Xem thêm →</a>
        </div>
        <div class="product-grid">
            <?php
            $all = mysqli_query($conn, "SELECT * FROM products ORDER BY created_at DESC LIMIT 8");
            if ($all && mysqli_num_rows($all) > 0):
                while ($p = mysqli_fetch_assoc($all)): ?>
                    <div class="product-card">
                        <a href="/WEB_GR4/pages/product-detail.php?id=<?php echo $p['id']; ?>">
                            <?php if (!empty($p['image'])): ?>
                                <img src="/WEB_GR4/assets/upload/<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                            <?php else: ?>
                                <div class="no-img">📦</div>
                            <?php endif; ?>
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($p['name']); ?></h3>
                                <p class="price"><?php echo number_format($p['price'], 0, ',', '.'); ?>đ</p>
                            </div>
                        </a>
                        <button class="btn btn-add-cart" onclick="addToCart(<?php echo $p['id']; ?>)">+ Thêm vào giỏ</button>
                    </div>
                <?php endwhile;
            else: ?>
                <p style="padding:20px 0;color:#888">Chưa có sản phẩm nào. Hãy thêm sản phẩm trong trang admin.</p>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>