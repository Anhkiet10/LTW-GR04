<?php
// Autoload tất cả file cần thiết
// require_once __DIR__ . '/config/database.php';
// require_once __DIR__ . '/core/Model.php';
// require_once __DIR__ . '/core/Controller.php';

require_once __DIR__ . '/core/Router.php';

// require_once __DIR__ . '/app/models/ProductModel.php';
// require_once __DIR__ . '/app/controllers/HomeController.php';
// require_once __DIR__ . '/app/controllers/ProductController.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);
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

$router->post('/admin/save-homepage', 'AdminController', 'saveHomepage');
$router->post('/admin/update-order-status', 'AdminController', 'updateOrderStatus');
$router->post('/login', 'AuthController', 'loginPage');
$router->post('/register', 'AuthController', 'registerPage');

$router->get('/logout', 'AuthController', 'logout');
$router->get('/register', 'AuthController', 'registerPage');

$router->dispatch();
?>