<?php
$pageTitle = "Sản phẩm";
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

// Tìm kiếm
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

if (!empty($search)) {
    $sql = "SELECT * FROM products WHERE name LIKE '%$search%' ORDER BY created_at DESC";
} else {
    $sql = "SELECT * FROM products ORDER BY created_at DESC";
}

$result = mysqli_query($conn, $sql);
?>

<section class="products-page">
    <div class="container">
        <h2>Tất cả sản phẩm</h2>

        <!-- Ô tìm kiếm -->
        <form method="GET" action="" class="search-form">
            <input
                type="text"
                name="search"
                placeholder="Tìm kiếm sản phẩm..."
                value="<?php echo htmlspecialchars($search); ?>"
            >
            <button type="submit" class="btn btn-primary">Tìm</button>
            <?php if (!empty($search)): ?>
                <a href="/shop/pages/products.php" class="btn btn-secondary">Xóa lọc</a>
            <?php endif; ?>
        </form>

        <?php if (!empty($search)): ?>
            <p>Kết quả tìm kiếm cho: <strong><?php echo htmlspecialchars($search); ?></strong>
               (<?php echo mysqli_num_rows($result); ?> sản phẩm)</p>
        <?php endif; ?>

        <!-- Danh sách sản phẩm -->
        <div class="product-grid">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($product = mysqli_fetch_assoc($result)): ?>
                    <div class="product-card">
                        <a href="/shop/pages/product-detail.php?id=<?php echo $product['id']; ?>">
                            <?php if (!empty($product['image'])): ?>
                                <img src="/shop/assets/upload/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                                <img src="/shop/assets/img/no-image.png" alt="No image">
                            <?php endif; ?>
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="price"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</p>
                                <?php if ($product['stock'] <= 0): ?>
                                    <span class="out-of-stock">Hết hàng</span>
                                <?php endif; ?>
                            </div>
                        </a>
                        <?php if ($product['stock'] > 0): ?>
                            <button class="btn btn-add-cart"
                                onclick="addToCart(<?php echo $product['id']; ?>)">
                                Thêm vào giỏ
                            </button>
                        <?php else: ?>
                            <button class="btn btn-disabled" disabled>Hết hàng</button>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Không tìm thấy sản phẩm nào.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once  __DIR__ . '/../includes/footer.php'; ?>