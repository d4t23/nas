<?php

require_once __DIR__ . '/Database.php';

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(array $data): bool
    {
        $sql = 'INSERT INTO users (name, email, password, role, storage_limit, storage_used, profile_picture, created_at) VALUES (:name, :email, :password, :role, :storage_limit, 0, :profile_picture, NOW())';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
            ':role' => $data['role'] ?? 'user',
            ':storage_limit' => $data['storage_limit'] ?? 5 * 1024 * 1024 * 1024,
            ':profile_picture' => $data['profile_picture'] ?? null,
        ]);
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function updateStorageUsed(int $userId, int $sizeChange): bool
    {
        $sql = 'UPDATE users SET storage_used = storage_used + :sizeChange WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':sizeChange' => $sizeChange, ':id' => $userId]);
    }

    public function update(array $data): bool
    {
        $sql = 'UPDATE users SET name = :name, email = :email, profile_picture = :profile_picture WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':name' => $data['name'], ':email' => $data['email'], ':profile_picture' => $data['profile_picture'] ?? null, ':id' => $data['id']]);
    }

    public function changePassword(int $userId, string $password): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET password = :password WHERE id = :id');
        return $stmt->execute([':password' => password_hash($password, PASSWORD_DEFAULT), ':id' => $userId]);
    }
}
