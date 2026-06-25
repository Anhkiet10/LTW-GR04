<?php
require_once __DIR__ . '/../../core/Model.php';

class ProfileModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ── Users ────────────────────────────────────────────────────────────────

    public function getUserById($userId)
    {
        $sql = "
            SELECT user_id, full_name, email, phone, role, created_at
            FROM users
            WHERE user_id = :user_id
            LIMIT 1
        ";

        return $this->queryOne($sql, [':user_id' => $userId]);
    }

    /**
     * Cập nhật thông tin cá nhân.
     * Cột mật khẩu trong DB tên là password_hash.
     */
    public function updateUser($userId, $fullName, $email, $phone, $password = '')
    {
        // Kiểm tra email đã tồn tại ở user khác chưa
        $check = $this->queryOne(
            "SELECT user_id FROM users WHERE email = :email AND user_id != :user_id LIMIT 1",
            [':email' => $email, ':user_id' => $userId]
        );
        if ($check) {
            return 'EMAIL_TAKEN';
        }

        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $sql = "
                UPDATE users
                SET full_name = :full_name,
                    email     = :email,
                    phone     = :phone,
                    password_hash = :password_hash
                WHERE user_id = :user_id
            ";
            return $this->execute($sql, [
                ':full_name'     => $fullName,
                ':email'         => $email,
                ':phone'         => $phone,
                ':password_hash' => $hashed,
                ':user_id'       => $userId,
            ]);
        }

        $sql = "
            UPDATE users
            SET full_name = :full_name,
                email     = :email,
                phone     = :phone
            WHERE user_id = :user_id
        ";
        return $this->execute($sql, [
            ':full_name' => $fullName,
            ':email'     => $email,
            ':phone'     => $phone,
            ':user_id'   => $userId,
        ]);
    }

    // ── Addresses ────────────────────────────────────────────────────────────

    public function getUserAddresses($userId)
    {
        $sql = "
            SELECT address_id, label, full_address, city, is_default
            FROM addresses
            WHERE user_id = :user_id
            ORDER BY is_default DESC, address_id DESC
        ";
        return $this->queryAll($sql, [':user_id' => $userId]);
    }

    public function getAddressById($addressId, $userId)
    {
        // Kèm user_id để tránh user A xem địa chỉ của user B
        $sql = "
            SELECT address_id, label, full_address, city, is_default
            FROM addresses
            WHERE address_id = :address_id AND user_id = :user_id
            LIMIT 1
        ";
        return $this->queryOne($sql, [
            ':address_id' => $addressId,
            ':user_id'    => $userId,
        ]);
    }

    public function addAddress($userId, $label, $fullAddress, $city, $isDefault)
    {
        // Nếu thêm mặc định → bỏ mặc định của tất cả địa chỉ cũ
        if ($isDefault) {
            $this->clearDefault($userId);
        }

        $sql = "
            INSERT INTO addresses (user_id, label, full_address, city, is_default)
            VALUES (:user_id, :label, :full_address, :city, :is_default)
        ";
        return $this->execute($sql, [
            ':user_id'      => $userId,
            ':label'        => $label,
            ':full_address' => $fullAddress,
            ':city'         => $city,
            ':is_default'   => $isDefault ? 1 : 0,
        ]);
    }

    public function updateAddress($addressId, $userId, $label, $fullAddress, $city, $isDefault)
    {
        if ($isDefault) {
            $this->clearDefault($userId);
        }

        $sql = "
            UPDATE addresses
            SET label        = :label,
                full_address = :full_address,
                city         = :city,
                is_default   = :is_default
            WHERE address_id = :address_id AND user_id = :user_id
        ";
        return $this->execute($sql, [
            ':label'        => $label,
            ':full_address' => $fullAddress,
            ':city'         => $city,
            ':is_default'   => $isDefault ? 1 : 0,
            ':address_id'   => $addressId,
            ':user_id'      => $userId,
        ]);
    }

    public function deleteAddress($addressId, $userId)
    {
        $sql = "
            DELETE FROM addresses
            WHERE address_id = :address_id AND user_id = :user_id
        ";
        return $this->execute($sql, [
            ':address_id' => $addressId,
            ':user_id'    => $userId,
        ]);
    }

    public function setDefaultAddress($addressId, $userId)
    {
        $this->clearDefault($userId);

        $sql = "
            UPDATE addresses
            SET is_default = 1
            WHERE address_id = :address_id AND user_id = :user_id
        ";
        return $this->execute($sql, [
            ':address_id' => $addressId,
            ':user_id'    => $userId,
        ]);
    }

    private function clearDefault($userId)
    {
        $this->execute(
            "UPDATE addresses SET is_default = 0 WHERE user_id = :user_id",
            [':user_id' => $userId]
        );
    }
}