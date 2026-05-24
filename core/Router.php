<?php
class Router {
    private $routes = [];

    public function get($path, $controller, $method) {
        $this->routes[] = ['path' => $path, 'controller' => $controller, 'method' => $method];
    }

    public function dispatch() {
        // Lấy URL hiện tại, bỏ query string
        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Bỏ prefix /WEB_GR4 nếu có
        $base = '/WEB_GR4';
        if (strpos($url, $base) === 0) {
            $url = substr($url, strlen($base));
        }

        if ($url === '' || $url === '/') $url = '/';

        foreach ($this->routes as $route) {
            if ($this->match($route['path'], $url, $params)) {
                $controllerFile = __DIR__ . '/../app/controllers/' . $route['controller'] . '.php';
                require_once $controllerFile;
                $ctrl = new $route['controller']();
                call_user_func_array([$ctrl, $route['method']], $params);
                return;
            }
        }

        // 404
        http_response_code(404);
        echo "<h2>404 - Không tìm thấy trang</h2>";
    }

    private function match($routePath, $url, &$params) {
        $params = [];
        $pattern = preg_replace('/\{(\w+)\}/', '([^/]+)', $routePath);// Biến {id} thành regex để bắt tham số 
        //(\w+)tìm + lưu dữ liệu để lấy ra sau
        $pattern = '#^' . $pattern . '$#';
        if (preg_match($pattern, $url, $matches)) {
            array_shift($matches);
            $params = $matches;
            return true;
        }
        return false;
    }
}
?>