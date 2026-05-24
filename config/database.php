<?php
class Database {
    private static $instance = null;
    private static $conn = null;

    const DB_HOST = 'localhost:3307';
    const DB_USER = 'root';
    const DB_PASS = '';
    const DB_NAME = 'W4SHOPDB';

    public static function getConnection() {
        if (self::$conn === null) {
            self::$conn = new mysqli(self::DB_HOST, self::DB_USER, self::DB_PASS, self::DB_NAME);

            if (self::$conn->connect_error) {
                die("Lỗi kết nối database: " . self::$conn->connect_error);
            }

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

// Backward compatibility
function getDB() {
    return Database::getConnection();
}
?>