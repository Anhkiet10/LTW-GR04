<?php
// Autoload tất cả file cần thiết
// require_once __DIR__ . '/config/database.php';
// require_once __DIR__ . '/core/Model.php';
// require_once __DIR__ . '/core/Controller.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/core/Router.php';

// require_once __DIR__ . '/app/models/ProductModel.php';
// require_once __DIR__ . '/app/controllers/HomeController.php';
// require_once __DIR__ . '/app/controllers/ProductController.php';


$router = new Router();
// Định nghĩa các route: đường dẫn, controller, method
$router->get('/',              'HomeController',    'index');
$router->get('/products',      'ProductController', 'index');
$router->get('/products/search', 'ProductController', 'search');
$router->get('/products/{id}', 'ProductController', 'detail');
$router->get('/cart',          'CartController',    'index');
$router->get('/login',         'AuthController',    'loginPage');
$router->get('/admin',         'AdminController',   'home');
$router->get('/admin/orders',  'AdminController',   'orders');
$router->get('/admin/order-detail', 'AdminController', 'orderDetail');

//
$router->post('/admin/save-homepage', 'AdminController', 'saveHomepage');
$router->post('/admin/update-order-status', 'AdminController', 'updateOrderStatus');
$router->post('/login', 'AuthController', 'loginPage');
$router->post('/register', 'AuthController', 'registerPage');

$router->get('/logout', 'AuthController', 'logout');
$router->get('/register', 'AuthController', 'registerPage');
$router->get('/', 'HomeController', 'index');
$router->post('/admin/save-homepage',        'AdminController', 'saveHomepage');
$router->post('/admin/update-order-status',  'AdminController', 'updateOrderStatus');


$router->get('/admin/products',                    'AdminProductController', 'index');
$router->get('/admin/products/getProduct',         'AdminProductController', 'getProduct');
$router->post('/admin/products/store',             'AdminProductController', 'store');
 
$router->post('/admin/products/update',            'AdminProductController', 'update');
 
$router->post('/admin/products/delete',            'AdminProductController', 'delete');
 
$router->post('/admin/products/deleteVariant',     'AdminProductController', 'deleteVariant');

$router->get('/admin/products/getAttributes',      'AdminProductController', 'getAttributes');
$router->post('/admin/products/createAttribute',   'AdminProductController', 'createAttribute');
$router->post('/admin/products/createAttributeValue','AdminProductController', 'createAttributeValue');

$router->post('/admin/products/deleteAttribute',      'AdminProductController', 'deleteAttribute');
$router->post('/admin/products/deleteAttributeValue', 'AdminProductController', 'deleteAttributeValue');

$router->get('/cart',              'CartController',    'index');
$router->post('/cart/update',              'CartController',    'update');
$router->post('/cart/delete',              'CartController',    'delete');
$router->post('/cart/add','CartController','addAjax');


$router->get('/checkout', 'OrderController', 'checkout');
$router->post('/place-order', 'OrderController', 'placeOrder');

$router->get('/orders', 'OrderController', 'history');
$router->get('/orders/{id}', 'OrderController', 'detail');


// Quản lý người dùng
$router->get('/admin/users',               'AdminUserController', 'index');
$router->get('/admin/users/create',        'AdminUserController', 'create');
$router->post('/admin/users/store',        'AdminUserController', 'store');
$router->get('/admin/users/edit',          'AdminUserController', 'edit');
$router->post('/admin/users/update',       'AdminUserController', 'update');
$router->post('/admin/users/delete',       'AdminUserController', 'delete');
$router->post('/admin/users/toggle-status','AdminUserController', 'toggleStatus');
$router->get('/admin/users/detail',        'AdminUserController', 'detail');
$router->dispatch();
?>