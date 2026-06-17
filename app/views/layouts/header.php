<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . " - W4Shop" : "W4Shop"; ?></title>
    <link rel="stylesheet" href="/WEB_GR4/public/assets/css/user/style.css">
    <link rel="stylesheet" href="/WEB_GR4/public/assets/css/user/style_product.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">
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
        <nav class="nav-container" id="navContainer"></nav>

        <!-- AUTH BUTTON — luôn hiển thị, không bị ẩn bởi hamburger -->
        <div class="nav-auth-wrap">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/WEB_GR4/logout" class="nav-auth-btn btn-logout-pill" title="Đăng xuất">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Đăng xuất</span>
                </a>
            <?php else: ?>
                <a href="/WEB_GR4/login" class="nav-auth-btn btn-login-pill" title="Đăng nhập">
                    <i class="fa-solid fa-user"></i>
                    <span>Đăng nhập</span>
                </a>
            <?php endif; ?>
        </div>

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