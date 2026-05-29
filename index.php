<?php
// Autoload tất cả file cần thiết
// require_once __DIR__ . '/config/database.php';
// require_once __DIR__ . '/core/Model.php';
// require_once __DIR__ . '/core/Controller.php';
require_once __DIR__ . '/core/Router.php';
// require_once __DIR__ . '/app/models/ProductModel.php';
// require_once __DIR__ . '/app/controllers/HomeController.php';
// require_once __DIR__ . '/app/controllers/ProductController.php';

$router = new Router();

$router->get('/',              'HomeController',    'index');
$router->get('/products',      'ProductController', 'index');
$router->get('/products/search', 'ProductController', 'search');
$router->get('/products/{id}', 'ProductController', 'detail');
$router->get('/cart',          'CartController',    'index');
$router->get('/login',         'AuthController',    'loginPage');
$router->get('/admin',         'AdminController',   'home');

$router->post('/admin/save-homepage', 'AdminController', 'saveHomepage');

$router->dispatch();
?>