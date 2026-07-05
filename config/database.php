<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!class_exists('Database')) {
class Database {
    private static $conn = null;

    const DB_HOST = '127.0.0.1';
    const DB_PORT = 3307;
    const DB_USER = 'root';
    const DB_PASS = '';
    const DB_NAME = 'W4SHOPDB';

    public static function getConnection() {
        // để lấy được biến $con thì phải self::$conn hoặc Database::$conn vì $conn là biến static, không phải biến instance
        if (self::$conn === null) {
            // mysqli() là một lớp trong PHP để kết nối với cơ sở dữ liệu MySQL.
            // phải truyền đủ 5 tham số: host, user, password, database, port
            self::$conn = new mysqli(self::DB_HOST, self::DB_USER, self::DB_PASS, self::DB_NAME, self::DB_PORT);
            // nếu biến self::$conn gọi thất bại object mysqli thì sẽ có thuộc tính connect_error, nếu kết nối thành công thì connect_error sẽ là null
            if (self::$conn->connect_error) {
                //die() là một hàm trong PHP dùng để in ra và dừng lại lập tức
                // . nối chuỗi, self::$conn->connect_error là thuộc tính của object mysqli trả về thông báo lỗi kết nối
                die("Lỗi kết nối database: " . self::$conn->connect_error);
            }
            // set_charset() là một phương thức của object mysqli để thiết lập bộ ký tự cho kết nối, 
            // "utf8mb4" là bộ ký tự hỗ trợ đầy đủ các ký tự Unicode, bao gồm cả emoji
            //"Từ bây giờ dữ liệu gửi qua lại giữa PHP và MySQL sẽ dùng bảng mã utf8mb4."
            self::$conn->set_charset("utf8mb4");
        }

        return self::$conn;
    }

    public static function closeConnection() {
        if (self::$conn !== null) {
            self::$conn->close();
            self::$conn = null;
        }
    }
}
}

if (!function_exists('getDB')) {
function getDB() {
    return Database::getConnection();
}
}
?> 