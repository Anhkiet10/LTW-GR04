<?php

require_once __DIR__ . '/../../core/Model.php';

class CategoryModelAdmin extends Model
{
    protected string $table = 'categories';

    // ------------------------------------------------------------------ //
    //  READ                                                                //
    // ------------------------------------------------------------------ //

    /**
     * Lấy tất cả danh mục, kèm tên danh mục cha (nếu có)
     * Dùng cho trang danh sách admin
     */
    public function getAll(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        [$where, $params] = $this->buildWhereClause($filters);

        $sql = "SELECT c.category_id,
                       c.category_name,
                       c.description,
                       c.parent_id,
                       p.category_name AS parent_name
                FROM {$this->table} c
                LEFT JOIN {$this->table} p ON c.parent_id = p.category_id
                {$where}
                ORDER BY COALESCE(c.parent_id, c.category_id), c.parent_id IS NOT NULL, c.category_id
                LIMIT :limit OFFSET :offset";

        $params[':limit']  = $limit;
        $params[':offset'] = $offset;

        return $this->queryAll($sql, $params);
    }

    /**
     * Đếm tổng danh mục theo bộ lọc (dùng cho phân trang)
     */
    public function countAll(array $filters = []): int
    {
        [$where, $params] = $this->buildWhereClause($filters);

        $sql = "SELECT COUNT(*) AS total FROM {$this->table} c {$where}";
        $row = $this->queryOne($sql, $params);

        return (int)($row['total'] ?? 0);
    }

    /**
     * Tìm danh mục theo ID
     */
    public function findById(int $id): array|false
    {
        $sql = "SELECT c.category_id,
                       c.category_name,
                       c.description,
                       c.parent_id,
                       p.category_name AS parent_name
                FROM {$this->table} c
                LEFT JOIN {$this->table} p ON c.parent_id = p.category_id
                WHERE c.category_id = :id
                LIMIT 1";

        return $this->queryOne($sql, [':id' => $id]);
    }

    /**
     * Lấy danh sách danh mục gốc (parent_id IS NULL)
     * Dùng cho dropdown chọn danh mục cha khi thêm/sửa
     */
    public function getRootCategories(): array
    {
        $sql = "SELECT category_id, category_name
                FROM {$this->table}
                WHERE parent_id IS NULL
                ORDER BY category_name ASC";

        return $this->queryAll($sql);
    }

    /**
     * Lấy tất cả danh mục để dùng cho dropdown (loại trừ chính nó khi edit)
     */
    public function getAllForSelect(int $excludeId = 0): array
    {
        if ($excludeId > 0) {
            $sql = "SELECT category_id, category_name, parent_id
                    FROM {$this->table}
                    WHERE category_id != :exclude
                    ORDER BY category_name ASC";
            return $this->queryAll($sql, [':exclude' => $excludeId]);
        }

        $sql = "SELECT category_id, category_name, parent_id
                FROM {$this->table}
                ORDER BY category_name ASC";
        return $this->queryAll($sql);
    }

    /**
     * Kiểm tra tên danh mục đã tồn tại chưa (khi tạo mới)
     */
    public function nameExists(string $name): bool
    {
        $sql = "SELECT COUNT(*) AS cnt FROM {$this->table} WHERE category_name = :name";
        $row = $this->queryOne($sql, [':name' => $name]);
        return (int)($row['cnt'] ?? 0) > 0;
    }

    /**
     * Kiểm tra tên danh mục đã tồn tại ở danh mục khác (khi cập nhật)
     */
    public function nameExistsExcept(string $name, int $excludeId): bool
    {
        $sql = "SELECT COUNT(*) AS cnt
                FROM {$this->table}
                WHERE category_name = :name AND category_id != :id";
        $row = $this->queryOne($sql, [':name' => $name, ':id' => $excludeId]);
        return (int)($row['cnt'] ?? 0) > 0;
    }

    /**
     * Kiểm tra danh mục có danh mục con không (trước khi xóa)
     */
    public function hasChildren(int $id): bool
    {
        $sql = "SELECT COUNT(*) AS cnt FROM {$this->table} WHERE parent_id = :id";
        $row = $this->queryOne($sql, [':id' => $id]);
        return (int)($row['cnt'] ?? 0) > 0;
    }

    /**
     * Kiểm tra danh mục có sản phẩm không (trước khi xóa)
     */
    public function hasProducts(int $id): bool
    {
        $sql = "SELECT COUNT(*) AS cnt FROM products WHERE category_id = :id";
        $row = $this->queryOne($sql, [':id' => $id]);
        return (int)($row['cnt'] ?? 0) > 0;
    }

    // ------------------------------------------------------------------ //
    //  WRITE                                                               //
    // ------------------------------------------------------------------ //

    /**
     * Tạo danh mục mới
     * @param array $data ['category_name', 'description', 'parent_id']
     * @return int ID vừa insert
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO {$this->table} (category_name, description, parent_id)
                VALUES (:category_name, :description, :parent_id)";

        $this->execute($sql, [
            ':category_name' => $data['category_name'],
            ':description'   => $data['description'] ?: null,
            ':parent_id'     => $data['parent_id'] ?: null,  // NULL = danh mục gốc
        ]);

        return (int)$this->lastInsertId();
    }

    /**
     * Cập nhật danh mục
     * @param int   $id
     * @param array $data ['category_name', 'description', 'parent_id']
     */
    public function update(int $id, array $data): void
    {
        $sql = "UPDATE {$this->table}
                SET category_name = :category_name,
                    description   = :description,
                    parent_id     = :parent_id
                WHERE category_id = :id";

        $this->execute($sql, [
            ':category_name' => $data['category_name'],
            ':description'   => $data['description'] ?: null,
            ':parent_id'     => $data['parent_id'] ?: null,
            ':id'            => $id,
        ]);
    }

    /**
     * Xóa danh mục
     * Nên kiểm tra hasChildren() và hasProducts() trước khi gọi
     */
    public function delete(int $id): void
    {
        $sql = "DELETE FROM {$this->table} WHERE category_id = :id";
        $this->execute($sql, [':id' => $id]);
    }

    // ------------------------------------------------------------------ //
    //  PRIVATE HELPERS                                                     //
    // ------------------------------------------------------------------ //

    private function buildWhereClause(array $filters): array
    {
        $conditions = [];
        $params     = [];

        if (!empty($filters['search'])) {
            $conditions[]      = "(c.category_name LIKE :search OR c.description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        // Lọc theo loại: root = chỉ danh mục gốc, child = chỉ danh mục con
        if (!empty($filters['type'])) {
            if ($filters['type'] === 'root') {
                $conditions[] = "c.parent_id IS NULL";
            } elseif ($filters['type'] === 'child') {
                $conditions[] = "c.parent_id IS NOT NULL";
            }
        }

        $where = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);
        return [$where, $params];
    }
}