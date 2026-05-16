<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . " - MyShop" : "MyShop"; ?></title>
    <link rel="stylesheet" href="/WEB_GR4/assets/css/style.css">
</head>
<body>

<header class="navbar" id="navbar">
    <div class="container nav-inner">

        <!-- LOGO -->
        <a href="/WEB_GR4/pages/home.php" class="logo">🛒 W4Shop</a>

        <!-- SEARCH BAR -->
        <div class="search-wrap">
            <input type="text" id="searchInput" placeholder="Tìm kiếm sản phẩm..." autocomplete="off">
            <button onclick="doSearch()">🔍</button>
            <div class="search-suggestions" id="searchSuggestions"></div>
        </div>

        <!-- NAV LINKS -->
        <nav>
            <ul class="nav-links">
                <li><a href="/WEB_GR4/pages/home.php">Trang chủ</a></li>

                <!-- DROPDOWN SẢN PHẨM -->
                <li class="has-dropdown">
                    <a href="/WEB_GR4/pages/products.php">Sản phẩm ▾</a>
                    <ul class="dropdown" id="categoryDropdown">
                        <li><a href="/WEB_GR4/pages/products.php">Tất cả sản phẩm</a></li>
                        <?php
                        if (isset($conn)) {
                            $cats = mysqli_query($conn, "SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != '' ORDER BY category");
                            if ($cats && mysqli_num_rows($cats) > 0) {
                                while ($cat = mysqli_fetch_assoc($cats)) {
                                    $c = htmlspecialchars($cat['category']);
                                    echo "<li><a href='/WEB_GR4/pages/products.php?category=" . urlencode($c) . "'>" . $c . "</a></li>";
                                }
                            } else {
                                $fallback = ['Xe máy','Vật dụng gia đình','Văn phòng phẩm','Thời trang','Điện tử'];
                                foreach ($fallback as $f) {
                                    echo "<li><a href='/WEB_GR4/pages/products.php?category=" . urlencode($f) . "'>" . $f . "</a></li>";
                                }
                            }
                        }
                        ?>
                    </ul>
                </li>

                <li><a href="/WEB_GR4/pages/cart.php">🛒 Giỏ hàng</a></li>
                <li><a href="/WEB_GR4/pages/login.php">Đăng nhập</a></li>
            </ul>
        </nav>

    </div>
</header>