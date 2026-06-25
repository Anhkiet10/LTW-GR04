<?php

require_once __DIR__ . '/../../core/Model.php';

class UserModelAdmin extends Model
{
    protected string $table = 'users';

    // ------------------------------------------------------------------ //
    //  READ                                                                //
    // ------------------------------------------------------------------ //

    /**
     * Lấy danh sách user có lọc + phân trang
     *
     * @param array $filters  ['search' => '', 'role' => '']
     * @param int   $limit
     * @param int   $offset
     * @return array
     */
    public function getAll(array $filters = [], int $limit = 10, int $offset = 0): array
    {
        [$where, $params] = $this->buildWhereClause($filters);

        $sql = "SELECT user_id, full_name, email, phone, role, created_at
                FROM {$this->table}
                {$where}
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset";

        $params[':limit']  = $limit;
        $params[':offset'] = $offset;

        return $this->queryAll($sql, $params);
    }

    /**
     * Đếm tổng user theo bộ lọc (dùng cho phân trang)
     */
    public function countAll(array $filters = []): int
    {
        [$where, $params] = $this->buildWhereClause($filters);

        $sql = "SELECT COUNT(*) AS total FROM {$this->table} {$where}";
        $row = $this->queryOne($sql, $params);

        return (int)($row['total'] ?? 0);
    }

    /**
     * Tìm user theo ID
     */
    public function findById(int $id): array|false
    {
        $sql = "SELECT user_id, full_name, email, phone, role, created_at
                FROM {$this->table}
                WHERE user_id = :id
                LIMIT 1";

        return $this->queryOne($sql, [':id' => $id]);
    }

    /**
     * Kiểm tra email đã tồn tại chưa (khi tạo mới)
     */
    public function emailExists(string $email): bool
    {
        $sql = "SELECT COUNT(*) AS cnt FROM {$this->table} WHERE email = :email";
        $row = $this->queryOne($sql, [':email' => $email]);
        return (int)($row['cnt'] ?? 0) > 0;
    }

    /**
     * Kiểm tra email đã tồn tại ở user khác (khi cập nhật)
     */
    public function emailExistsExcept(string $email, int $excludeId): bool
    {
        $sql = "SELECT COUNT(*) AS cnt
                FROM {$this->table}
                WHERE email = :email AND user_id != :id";
        $row = $this->queryOne($sql, [':email' => $email, ':id' => $excludeId]);
        return (int)($row['cnt'] ?? 0) > 0;
    }

    /**
     * Đếm số đơn hàng của user (dùng ở trang detail)
     */
    public function countOrders(int $userId): int
    {
        $sql = "SELECT COUNT(*) AS cnt FROM orders WHERE user_id = :uid";
        $row = $this->queryOne($sql, [':uid' => $userId]);
        return (int)($row['cnt'] ?? 0);
    }

    // ------------------------------------------------------------------ //
    //  WRITE                                                               //
    // ------------------------------------------------------------------ //

    /**
     * Tạo user mới
     *
     * @param array $data  ['full_name', 'email', 'password_hash', 'phone', 'role']
     * @return int  ID vừa insert
     */
  public function create(array $data): int
{
    $sql = "INSERT INTO {$this->table} (full_name, email, password_hash, phone, role, created_at)
            VALUES (:full_name, :email, :password_hash, :phone, :role, NOW())";

    $this->execute($sql, [
        ':full_name'     => $data['full_name'] ?? $data['name'],        // ✅ Fix Bug 2
        ':email'         => $data['email'],
        ':password_hash' => $data['password'] ?? $data['password_hash'], // ✅ Fix Bug 1
        ':phone'         => $data['phone'] ?? null,
        ':role'          => $data['role']  ?? 'customer',
    ]);

    return (int)$this->lastInsertId();
}

public function update(int $id, array $data): void
{
    // ✅ Fix Bug 3a: map 'name' → 'full_name' nếu cần
    if (isset($data['name']) && !isset($data['full_name'])) {
        $data['full_name'] = $data['name'];
    }

    $fields = ['full_name', 'email', 'phone', 'role'];
    $set    = [];
    $params = [':id' => $id];

    foreach ($fields as $field) {
        if (array_key_exists($field, $data)) {
            $set[]               = "{$field} = :{$field}";
            $params[":{$field}"] = $data[$field];
        }
    }

    // ✅ Fix Bug 3b: check key 'password' (từ controller) thay vì 'password_hash'
    if (!empty($data['password'])) {
        $set[]                    = 'password_hash = :password_hash';
        $params[':password_hash'] = $data['password'];
    }

    if (empty($set)) return;

    $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . " WHERE user_id = :id";
    $this->execute($sql, $params);
}

private function buildWhereClause(array $filters): array
{
    $conditions = [];
    $params     = [];

    if (!empty($filters['search'])) {
        $conditions[] = "(full_name LIKE :search OR email LIKE :search OR phone LIKE :search)";
        $params[':search'] = '%' . $filters['search'] . '%';
    }

    if (!empty($filters['role'])) {
        $conditions[] = "role = :role";
        $params[':role'] = $filters['role'];
    }

    $where = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);
    return [$where, $params];
}


    public function delete(int $id): void
    {
        $sql = "DELETE FROM {$this->table} WHERE user_id = :id";
        $this->execute($sql, [':id' => $id]);
    }

    // ✅ Fix Bug 5: method này bị thiếu, Controller gọi nhưng không có
    public function updateStatus(int $id, string $status): void
    {
        $sql = "UPDATE {$this->table} SET status = :status WHERE user_id = :id";
        $this->execute($sql, [':status' => $status, ':id' => $id]);
    }
}