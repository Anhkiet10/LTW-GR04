<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . " - W4Shop" : "W4Shop"; ?></title>
    <link rel="stylesheet" href="/WEB_GR4/public/assets/css/user/style.css">
    <link rel="stylesheet" href="/WEB_GR4/public/assets/css/user/style_product.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>

<header class="navbar" id="navbar">
    <div class="container nav-inner">

        <a href="/WEB_GR4/" class="logo"><i class="fa-brands fa-canadian-maple-leaf" style="color: rgb(255, 59, 113);"></i> W4Shop</a>
        <button class="menu-toggle" id="menuToggle"><i class="fa-solid fa-bars" style="color: rgb(255, 59, 59);"></i></button>
        <!-- SEARCH -->
        <div class="search-wrap">
            <input type="text" id="searchInput" placeholder="Tìm kiếm sản phẩm..." autocomplete="off">
            <button onclick="doSearch()"><i class="fa-solid fa-magnifying-glass" style="color: rgb(9, 9, 9);"></i></button>
            <div class="search-suggestions" id="searchSuggestions"></div>
        </div>

        <!-- NAV -->
        <nav class="nav-container" id="navContainer">
            <ul class="nav-links">
                <li><a href="/WEB_GR4/"><i class="fas fa-home" style="color: rgb(177, 151, 252);"></i>Trang chủ</a></li>
                <li class="cart-link"><a href="/WEB_GR4/cart"><i class="fas fa-shopping-cart" style="color: rgb(177, 151, 252);"></i> Giỏ hàng</a></li>
                <li ><a href="/WEB_GR4/orders"> <i class="fa-solid fa-clipboard-list" style="color: rgb(177, 151, 252);"></i> Đơn hàng</a></li>
                <li><a href="/WEB_GR4/login"><i class="fa-solid fa-user" style="color: rgb(177, 151, 252);"></i> Đăng nhập</a></li>
            </ul>
        </nav>

    </div>

    <div class="categories-bar">
    <div class="container">
        <div class="categories-list" id="idcategoriesList">
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