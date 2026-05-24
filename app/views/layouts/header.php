<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . " - W4Shop" : "W4Shop"; ?></title>
    <link rel="stylesheet" href="/WEB_GR4/public/assets/css/style.css">
</head>
<body>

<header class="navbar" id="navbar">
    <div class="container nav-inner">

        <a href="/WEB_GR4/" class="logo">🌸 W4Shop</a>

        <!-- SEARCH -->
        <div class="search-wrap">
            <input type="text" id="searchInput" placeholder="Tìm kiếm sản phẩm..." autocomplete="off">
            <!-- <button onclick=""><i class="fa-solid fa-filter" style="color: rgb(228, 14, 14);"></i></button> -->
            <button onclick="doSearch()">🔍</button>
            <div class="search-suggestions" id="searchSuggestions"></div>
        </div>

        <!-- NAV -->
        <nav>
            <ul class="nav-links">
                <li><a href="/WEB_GR4/">Trang chủ</a></li>
                <li class="cart-link"><a href="/WEB_GR4/cart">🛒 Giỏ hàng</a></li>
                <li class="order-link"><a href="/WEB_GR4/orders"> Đơn hàng</a></li>
                <li><a href="/WEB_GR4/login">Đăng nhập</a></li>
            </ul>
        </nav>

    </div>

    <div class="categories-bar">
    <div class="container">
        <div class="categories-list">
            <a href="/WEB_GR4/products" class="category-link all-products">Tất cả sản phẩm</a>
            <?php
            if (isset($categories) && !empty($categories)) {
                foreach ($categories as $cat) {
                    $catId = (int)$cat['category_id'];
                    $catName = htmlspecialchars($cat['category_name']);
                    echo "<a href='/WEB_GR4/products?category=$catId' class='category-link'>$catName</a>";
                }
            }
            ?>
        </div>
    </div>
</div>
</header>

<!-- CATEGORIES BAR -->
