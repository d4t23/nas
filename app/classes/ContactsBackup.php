<?php
require_once __DIR__ . '/Database.php';

class ContactsBackup
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function backupToCsv(int $userId, array $contacts): string
    {
        $filename = sprintf('contacts_backup_%d_%s.csv', $userId, date('Ymd_His'));
        $folder = __DIR__ . '/../../storage/uploads/';
        if (!is_dir($folder)) mkdir($folder, 0755, true);
        $path = $folder . $filename;

        $fh = fopen($path, 'w');
        fputcsv($fh, ['Name', 'Email', 'Phone']);
        foreach ($contacts as $c) {
            fputcsv($fh, [$c['name'] ?? '', $c['email'] ?? '', $c['phone'] ?? '']);
        }
        fclose($fh);

        // Record backup
        $stmt = $this->db->prepare('INSERT INTO backups (user_id, type, file_path, items_count, created_at) VALUES (:uid, :type, :path, :count, NOW())');
        $stmt->execute([':uid' => $userId, ':type' => 'contacts', ':path' => $filename, ':count' => count($contacts)]);

        return $path;
    }

    public function exportContactsCsv(int $userId)
    {
        $stmt = $this->db->prepare('SELECT * FROM contacts WHERE user_id = :uid');
        $stmt->execute([':uid' => $userId]);
        $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->backupToCsv($userId, $contacts);
    }

    public function restoreFromCsv(int $userId, string $filePath): bool
    {
        if (!file_exists($filePath)) return false;
        $fh = fopen($filePath, 'r');
        $headers = fgetcsv($fh);
        while (($row = fgetcsv($fh)) !== false) {
            $name = $row[0] ?? '';
            $email = $row[1] ?? '';
            $phone = $row[2] ?? '';
            $stmt = $this->db->prepare('INSERT INTO contacts (user_id, name, email, phone, created_at) VALUES (:uid, :name, :email, :phone, NOW())');
            $stmt->execute([':uid' => $userId, ':name' => $name, ':email' => $email, ':phone' => $phone]);
        }
        fclose($fh);
        return true;
    }
}
