<?php
class Router {
    private $routes = [];
    // sau khi chạy qua index.php sẽ có 1 mảng $routes chứa tất cả các route đã đăng ký, 
    // mỗi phần tử là 1 mảng con với keys: path, controller, method, httpMethod
    public function get($path, $controller, $method) {
        $this->routes[] = ['path' => $path, 'controller' => $controller, 'method' => $method, 'httpMethod' => 'GET'];
    }

    public function post($path, $controller, $method) {
        $this->routes[] = ['path' => $path, 'controller' => $controller, 'method' => $method, 'httpMethod' => 'POST'];
    }

    public function dispatch() {
        // Lấy URL hiện tại, bỏ query string
        //tự động nhận diện URL hiện tại để so khớp với các route đã đăng ký, 
        // nếu có tham số thì sẽ bắt ra và truyền vào controller method
        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Bỏ prefix /WEB_GR4 nếu có
        // để hỗ trợ chạy trên localhost với đường dẫn có prefix, 
        // ví dụ http://localhost/WEB_GR4/products thì sẽ bỏ đi /WEB_GR4 để 
        // so khớp với route đã đăng ký là /products
        $base = '/WEB_GR4';
        if (strpos($url, $base) === 0) {
            $url = substr($url, strlen($base));// substr(url-đường dẫn,strlen()-lấy chiều dài)-- dùng để cắt đi với số chiều dài 
        }
        // Nếu URL rỗng thì mặc định là /
        // mục đích để khi truy cập vào http://localhost/WEB_GR4/ 
        // hoặc http://localhost/WEB_GR4 thì vẫn được nhận diện là route / và hiển thị trang chủ
        if ($url === '' || $url === '/') $url = '/';

         // Lấy phương thức HTTP hiện tại (GET, POST, etc.)
         // để so khớp với route đã đăng ký, ví dụ nếu truy cập bằng POST thì sẽ chỉ so 
         // khớp với các route có httpMethod là POST
        $httpMethod = $_SERVER['REQUEST_METHOD'];// lấy method được request từ server

        // Duyệt qua tất cả route đã đăng ký để tìm route phù hợp với URL và phương thức HTTP
        foreach ($this->routes as $route) { // mảng của class được tạo và truyền ở trên
        //Với mỗi phần tử nằm trong mảng lớn $this->routes, hãy lôi nó ra và cho nó đóng vai trò là biến $route để xử lý
            if ($route['httpMethod'] === $httpMethod && $this->match($route['path'], $url, $params)) {
                // so sánh httpMethod lúc đăng ký bên index với có trùng khớp hoàn toàn với phương thức HTTP mà người dùng đang gửi lên
                $controllerFile = __DIR__ . '/../app/controllers/' . $route['controller'] . '.php';
                require_once $controllerFile;
                $ctrl = new $route['controller']();// để khởi tạo __construct (PHP được thiết kế)
                call_user_func_array([$ctrl, $route['method']], $params);
                return;
            }
        }

        // 404
        http_response_code(404);
        echo "<h2>404 - Không tìm thấy trang</h2>";
    }

    // Hàm để so khớp URL với route đã đăng ký, đồng thời bắt các tham số nếu có
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