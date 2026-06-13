<?php

class UserModel
{
    private $db;

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }

    public function emailExists($email)
    {
        $sql = "SELECT user_id FROM users WHERE email = ? LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        $exists = $stmt->num_rows > 0;

        $stmt->close();

        return $exists;
    }

    public function register($fullName, $email, $password)
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "
            INSERT INTO users
            (
                full_name,
                email,
                password_hash,
                role,
                created_at
            )
            VALUES
            (
                ?,
                ?,
                ?,
                'admin',
                NOW()
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bind_param(
            "sss",
            $fullName,
            $email,
            $hashedPassword
        );

        $result = $stmt->execute();

        $stmt->close();

        return $result;
    }
    public function findByEmail($email)
    {
        $sql = "SELECT user_id, full_name, email, password_hash, role FROM users WHERE email = ? LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $stmt->close();

        return $user;
    }
}

?>