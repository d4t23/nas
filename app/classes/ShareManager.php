<?php

require_once __DIR__ . '/Database.php';

class ShareManager
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function createShare(int $fileId): string
    {
        $token = bin2hex(random_bytes(16));
        $stmt = $this->db->prepare('INSERT INTO shares (file_id, token, created_at) VALUES (:file_id, :token, NOW())');
        $stmt->execute([':file_id' => $fileId, ':token' => $token]);
        return $token;
    }

    public function findByToken(string $token): ?array
    {
        $stmt = $this->db->prepare('SELECT s.*, f.file_name, f.file_path FROM shares s JOIN files f ON s.file_id = f.id WHERE s.token = :token LIMIT 1');
        $stmt->execute([':token' => $token]);
        $share = $stmt->fetch();
        return $share ?: null;
    }

    public function getUserShares(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT s.*, f.file_name, f.file_path, f.user_id FROM shares s
            JOIN files f ON s.file_id = f.id
            WHERE f.user_id = :user_id
            ORDER BY s.created_at DESC'
        );
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }
}
