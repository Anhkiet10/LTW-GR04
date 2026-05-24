<?php
class Controller {
    // Render view và truyền data vào
    protected function render($view, $data = []) {
        extract($data); // biến $data['products'] thành $products
        $viewPath = __DIR__ . '/../app/views/' . $view . '.php';
        if (!file_exists($viewPath)) die("View không tồn tại: $view");
        require $viewPath;
    }

    // Redirect
    protected function redirect($url) {
        header("Location: $url");
        exit();
    }
}
?>