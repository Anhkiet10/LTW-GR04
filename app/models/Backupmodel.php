<?php
require_once __DIR__ . '/../../core/Model.php';

class BackupModel extends Model {

    /**
     * Trả về danh sách tất cả các bảng trong database.
     */
    public function getAllTables(): array {
        $rows = $this->fetchAll("SHOW TABLES");
        $tables = [];
        foreach ($rows as $row) {
            $tables[] = array_values($row)[0];
        }
        return $tables;
    }

    /**
     * Tạo nội dung SQL dump cho toàn bộ database (hoặc các bảng được chọn).
     * Không dùng mysqldump CLI — thuần PHP qua mysqli.
     *
     * @param array|null $selectedTables  null = tất cả bảng
     * @return string  Nội dung file .sql
     */
    public function generateSqlDump(?array $selectedTables = null): string {
        $db = \Database::getConnection();

        $tables = $selectedTables ?? $this->getAllTables();

        $output  = "-- W4Shop Database Backup\n";
        $output .= "-- Thời gian: " . date('Y-m-d H:i:s') . "\n";
        $output .= "-- Server: MariaDB / MySQL\n";
        $output .= "-- -----------------------------------------------\n\n";
        $output .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $output .= "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n";
        $output .= "SET NAMES utf8mb4;\n\n";

        foreach ($tables as $table) {
            $table = $db->real_escape_string($table);

            // --- CREATE TABLE ---
            $createRes = $db->query("SHOW CREATE TABLE `$table`");
            if (!$createRes) continue;
            $createRow = $createRes->fetch_assoc();
            $createSql = $createRow['Create Table'] ?? $createRow[array_keys($createRow)[1]];

            $output .= "-- -----------------------------------------------\n";
            $output .= "-- Cấu trúc bảng `$table`\n";
            $output .= "-- -----------------------------------------------\n\n";
            $output .= "DROP TABLE IF EXISTS `$table`;\n";
            $output .= $createSql . ";\n\n";

            // --- INSERT DATA ---
            $dataRes = $db->query("SELECT * FROM `$table`");
            if (!$dataRes || $dataRes->num_rows === 0) {
                $output .= "-- (Không có dữ liệu)\n\n";
                continue;
            }

            $output .= "-- Dữ liệu bảng `$table`\n\n";

            $columns = [];
            $fieldInfo = $dataRes->fetch_fields();
            foreach ($fieldInfo as $field) {
                $columns[] = "`" . $field->name . "`";
            }
            $colList = implode(', ', $columns);

            $rows = [];
            while ($row = $dataRes->fetch_assoc()) {
                $values = [];
                foreach ($row as $val) {
                    if ($val === null) {
                        $values[] = 'NULL';
                    } else {
                        $values[] = "'" . $db->real_escape_string($val) . "'";
                    }
                }
                $rows[] = '(' . implode(', ', $values) . ')';
            }

            // Chia thành các batch INSERT 100 dòng để file không quá nặng
            foreach (array_chunk($rows, 100) as $batch) {
                $output .= "INSERT INTO `$table` ($colList) VALUES\n";
                $output .= implode(",\n", $batch) . ";\n";
            }
            $output .= "\n";
        }

        $output .= "SET FOREIGN_KEY_CHECKS=1;\n";
        $output .= "-- Kết thúc backup\n";

        return $output;
    }

    /**
     * Trả về thông tin tổng quan của database để hiển thị trên dashboard.
     */
    public function getDatabaseInfo(): array {
        $tables = $this->getAllTables();
        $info   = [];

        foreach ($tables as $table) {
            $row = $this->fetchOne("SELECT COUNT(*) as cnt FROM `$table`");
            $info[] = [
                'table' => $table,
                'rows'  => $row['cnt'] ?? 0,
            ];
        }

        return $info;
    }
}
?>