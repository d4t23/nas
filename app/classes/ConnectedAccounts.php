<?php
require_once __DIR__ . '/Database.php';

class ConnectedAccounts
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getByUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM connected_accounts WHERE user_id = :uid');
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isConnected(int $userId, string $provider): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM connected_accounts WHERE user_id = :uid AND provider = :prov');
        $stmt->execute([':uid' => $userId, ':prov' => $provider]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function connectPlaceholder(int $userId, string $provider, array $data): bool
    {
        // Store tokens/metadata after OAuth dance. This is a safe placeholder.
        $stmt = $this->db->prepare('INSERT INTO connected_accounts (user_id, provider, provider_user_id, access_token, refresh_token, expires_at, created_at) VALUES (:uid, :prov, :pid, :access, :refresh, :expires, NOW())');
        return $stmt->execute([
            ':uid' => $userId,
            ':prov' => $provider,
            ':pid' => $data['provider_user_id'] ?? null,
            ':access' => $data['access_token'] ?? null,
            ':refresh' => $data['refresh_token'] ?? null,
            ':expires' => $data['expires_at'] ?? null,
        ]);
    }

    public function disconnect(int $userId, string $provider): bool
    {
        $stmt = $this->db->prepare('DELETE FROM connected_accounts WHERE user_id = :uid AND provider = :prov');
        return $stmt->execute([':uid' => $userId, ':prov' => $provider]);
    }
}
