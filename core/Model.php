<?php
require_once __DIR__ . '/../config/database.php';

class Model {
    protected $conn;

    public function __construct() {
        $this->conn = getDB();
    }

    protected function query($sql) {
        $result = mysqli_query($this->conn, $sql);
        if (!$result) die("Query lỗi: " . mysqli_error($this->conn));
        return $result;
    }

    protected function fetchAll($sql) {
        $result = $this->query($sql);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
        return $rows;
    }

    protected function fetchOne($sql) {
        $result = $this->query($sql);
        return mysqli_fetch_assoc($result);
    }

    protected function escape($str) {
        return mysqli_real_escape_string($this->conn, $str);
    }
}
?>