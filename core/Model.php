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
    protected function lastInsertId(): int {
    return (int)mysqli_insert_id($this->conn);
}

    // ------------------------------------------------------------------ //
    //  NAMED-PARAM HELPERS (mysqli prepared statements)                    //
    //  Hỗ trợ cú pháp :name giống PDO cho các Model viết theo style mới    //
    //  (queryAll / queryOne / execute). Không đụng tới query()/fetchAll()/ //
    //  fetchOne() ở trên để không phá vỡ các Model cũ đang dùng raw SQL.   //
    // ------------------------------------------------------------------ //

    /**
     * Chuyển SQL có placeholder :name thành SQL dùng ? và mảng giá trị
     * theo đúng thứ tự xuất hiện (hỗ trợ 1 tên dùng nhiều lần, ví dụ :search)
     */
    private function compileNamedParams(string $sql, array $params): array
    {
        $values = [];
        $compiled = preg_replace_callback('/:([a-zA-Z_][a-zA-Z0-9_]*)/', function ($m) use ($params, &$values) {
            $key = ':' . $m[1];
            if (!array_key_exists($key, $params)) {
                die("Thiếu tham số {$key} khi build query");
            }
            $values[] = $params[$key];
            return '?';
        }, $sql);

        return [$compiled, $values];
    }

    /**
     * Prepare + bind + execute, trả về mysqli_stmt đã execute xong
     */
    private function prepareExecute(string $sql, array $params = []) {
        [$compiledSql, $values] = $this->compileNamedParams($sql, $params);

        $stmt = mysqli_prepare($this->conn, $compiledSql);
        if (!$stmt) {
            die("Prepare lỗi: " . mysqli_error($this->conn) . " | SQL: " . $compiledSql);
        }

        if (!empty($values)) {
            $types = '';
            $refs  = [];
            foreach ($values as $i => $v) {
                if (is_int($v)) {
                    $types .= 'i';
                } elseif (is_float($v)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
                $refs[] = &$values[$i];
            }
            array_unshift($refs, $types);
            call_user_func_array([$stmt, 'bind_param'], $refs);
        }

        if (!mysqli_stmt_execute($stmt)) {
            die("Execute lỗi: " . mysqli_stmt_error($stmt) . " | SQL: " . $compiledSql);
        }

        return $stmt;
    }

    /**
     * SELECT nhiều dòng, có hỗ trợ :name params. Trả về array các dòng.
     */
    protected function queryAll(string $sql, array $params = []): array
    {
        $stmt   = $this->prepareExecute($sql, $params);
        $result = mysqli_stmt_get_result($stmt);

        $rows = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = $row;
            }
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    /**
     * SELECT 1 dòng, có hỗ trợ :name params. Trả về array hoặc false nếu không có.
     */
    protected function queryOne(string $sql, array $params = []): array|false
    {
        $rows = $this->queryAll($sql, $params);
        return $rows[0] ?? false;
    }

    /**
     * INSERT / UPDATE / DELETE, có hỗ trợ :name params.
     */
    protected function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->prepareExecute($sql, $params);
        mysqli_stmt_close($stmt);
        return true;
    }
}
?>