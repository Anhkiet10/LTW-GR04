<?php
class Controller {
    // Render view và truyền data vào
    protected function render($view, $data = []) {
        //$view: Chuỗi đường dẫn đến file giao diện cần mở
        //$data = []: Một mảng chứa dữ liệu muốn truyền từ Controller ra ngoài giao diện. Mặc định nếu không truyền gì thì nó là một mảng rỗng
        extract($data); // biến $data['products'] thành $products biến thành biến và có thể hiển thị luôn
        $viewPath = __DIR__ . '/../app/views/' . $view . '.php';//dường dẫn file cần đến
        if (!file_exists($viewPath)) die("View không tồn tại: $view");
        require $viewPath; // nhúng để cho phép views lấy toàn bộ dữ liệu từ controller/
    }

    // Redirect chưa được áp dụng tốt.
    protected function redirect($url) {
        header("Location: $url");
        exit();
    }
}
?>