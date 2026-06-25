<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/core/Router.php';

$router = new Router();

// ===== TRANG CHỦ =====
$router->get('/', 'HomeController', 'index');

// ===== SẢN PHẨM =====
$router->get('/products',          'ProductController', 'index');
$router->get('/products/search',   'ProductController', 'search');
$router->get('/products/{id}',     'ProductController', 'detail');

// ===== AUTH =====
$router->get('/login',    'AuthController', 'loginPage');
$router->post('/login',   'AuthController', 'loginPage');
$router->get('/register', 'AuthController', 'registerPage');
$router->post('/register','AuthController', 'registerPage');
$router->get('/logout',   'AuthController', 'logout');
$router->get('/profile',          'ProfileController', 'index');
$router->get('/profile/edit',           'ProfileController', 'edit');
$router->post('/profile/update',        'ProfileController', 'update');

// Địa chỉ - CÒN THIẾU
$router->get('/profile/addAddress',         'ProfileController', 'addAddress');
$router->post('/profile/store-address',      'ProfileController', 'storeAddress');
$router->get('/profile/editAddress/{id}',   'ProfileController', 'editAddress');
$router->post('/profile/update-address',     'ProfileController', 'updateAddress');
$router->post('/profile/delete-address',     'ProfileController', 'deleteAddress');
$router->post('/profile/set-default-address','ProfileController', 'setDefaultAddress');

// ===== GIỎ HÀNG =====
$router->get('/cart',          'CartController', 'index');
$router->post('/cart/update',  'CartController', 'update');
$router->post('/cart/delete',  'CartController', 'delete');
$router->post('/cart/add',     'CartController', 'add');
$router->get('/cart/checkInfo', 'CartController', 'checkInfo');
$router->post('/cart/saveAddress', 'CartController', 'saveAddress');

$router->post('/cart/placeOrder', 'CartController', 'placeOrder');

// ===== ĐƠN HÀNG =====
$router->get('/checkout',       'OrderController', 'checkout');
$router->post('/place-order',   'OrderController', 'placeOrder');
$router->get('/orders',         'OrderController', 'history');
$router->get('/orders/{id}',    'OrderController', 'detail');

// ===== ADMIN =====
$router->get('/admin',              'AdminController', 'home');
$router->get('/admin/orders',       'AdminController', 'orders');
$router->get('/admin/order-detail', 'AdminController', 'orderDetail');
$router->post('/admin/save-homepage',       'AdminController', 'saveHomepage');
$router->post('/admin/update-order-status', 'AdminController', 'updateOrderStatus');
// thể loại admin
$router->get('/admin/categories',         'AdminCategoryController', 'index');
$router->get('/admin/categories/create',  'AdminCategoryController', 'create');
$router->get('/admin/categories/edit',    'AdminCategoryController', 'edit');
$router->post('/admin/categories/store',  'AdminCategoryController', 'store');
$router->post('/admin/categories/update', 'AdminCategoryController', 'update');
$router->post('/admin/categories/delete', 'AdminCategoryController', 'delete');
// ===== ADMIN - SẢN PHẨM =====
$router->get('/admin/products',                      'AdminProductController', 'index');
$router->get('/admin/products/getProduct',           'AdminProductController', 'getProduct');
$router->get('/admin/products/getAttributes',        'AdminProductController', 'getAttributes');
$router->post('/admin/products/store',               'AdminProductController', 'store');
$router->post('/admin/products/update',              'AdminProductController', 'update');
$router->post('/admin/products/delete',              'AdminProductController', 'delete');
$router->post('/admin/products/deleteVariant',       'AdminProductController', 'deleteVariant');
$router->post('/admin/products/createAttribute',     'AdminProductController', 'createAttribute');
$router->post('/admin/products/createAttributeValue','AdminProductController', 'createAttributeValue');
$router->post('/admin/products/deleteAttribute',     'AdminProductController', 'deleteAttribute');
$router->post('/admin/products/deleteAttributeValue','AdminProductController', 'deleteAttributeValue');

// ===== ADMIN - NGƯỜI DÙNG =====
$router->get('/admin/users',                'AdminUserController', 'index');
$router->get('/admin/users/create',         'AdminUserController', 'create');
$router->get('/admin/users/edit',           'AdminUserController', 'edit');
$router->get('/admin/users/detail',         'AdminUserController', 'detail');
$router->post('/admin/users/store',         'AdminUserController', 'store');
$router->post('/admin/users/update',        'AdminUserController', 'update');
$router->post('/admin/users/delete',        'AdminUserController', 'delete');
$router->post('/admin/users/toggle-status', 'AdminUserController', 'toggleStatus');

$router->get('/admin/backup',          'AdminController', 'backup');
$router->post('/admin/backup/download','AdminController', 'downloadBackup');

$router->dispatch();
?>