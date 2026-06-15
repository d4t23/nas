<?php

require_once __DIR__ . '/Database.php';

class FileManager
{
    private PDO $db;
    private string $uploadDir;

    public function __construct()
    {
        $this->db = Database::getConnection();

        $this->uploadDir =
            realpath(__DIR__ . '/../../storage/uploads');
    }

    // =========================
    // UPLOAD FILE
    // =========================
    public function uploadFile(
        int $userId,
        array $file,
        ?int $folderId = null
    ): array {

        if ($file['error'] !== UPLOAD_ERR_OK) {

            return [
                'success' => false,
                'message' => 'Upload failed'
            ];
        }

        $allowed = [
            'jpg',
            'jpeg',
            'png',
            'gif',
            'pdf',
            'doc',
            'docx',
            'xls',
            'xlsx',
            'txt',
            'zip',
            'rar',
            'mp4',
            'mp3'
        ];

        $file['name'] = str_replace('\\', '/', $file['name']);
        $extension = strtolower(
            pathinfo($file['name'], PATHINFO_EXTENSION)
        );

        if (!in_array($extension, $allowed, true)) {

            return [
                'success' => false,
                'message' => 'File type not allowed'
            ];
        }

        $storedName =
            uniqid('f_', true) . '.' . $extension;

        $destination =
            $this->uploadDir .
            DIRECTORY_SEPARATOR .
            $storedName;

        if (!move_uploaded_file(
            $file['tmp_name'],
            $destination
        )) {

            return [
                'success' => false,
                'message' => 'Unable to save file'
            ];
        }

        $stmt = $this->db->prepare("
            INSERT INTO files (
                user_id,
                file_name,
                file_path,
                file_size,
                file_type,
                folder_id,
                is_starred,
                created_at
            )
            VALUES (
                :user_id,
                :file_name,
                :file_path,
                :file_size,
                :file_type,
                :folder_id,
                0,
                NOW()
            )
        ");

        $stmt->execute([

            ':user_id' => $userId,
            ':file_name' => $file['name'],
            ':file_path' => $storedName,
            ':file_size' => $file['size'],
            ':file_type' => $file['type'],
            ':folder_id' => $folderId,

        ]);

        return [
            'success' => true,
            'message' => 'File uploaded'
        ];
    }


    // =========================
    // GET FILES
    // =========================
    public function getFiles(
        int $userId,
        ?int $folderId = null
    ): array {

        $sql = "
            SELECT *
            FROM files
            WHERE user_id = :user_id
            AND deleted_at IS NULL
        ";

        $params = [
            ':user_id' => $userId
        ];

        if ($folderId !== null) {

            $sql .= " AND folder_id = :folder_id";

            $params[':folder_id'] = $folderId;
        } else {

            $sql .= " AND folder_id IS NULL";
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->db->prepare($sql);

        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    // =========================
    // FOLDER SIZES
    public function getFolderSizes(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT folder_id, SUM(file_size) AS total_size, COUNT(*) AS file_count
            FROM files
            WHERE user_id = :user_id
            AND deleted_at IS NULL
            AND folder_id IS NOT NULL
            GROUP BY folder_id"
        );

        $stmt->execute([':user_id' => $userId]);

        $sizes = [];
        while ($row = $stmt->fetch()) {
            $sizes[$row['folder_id']] = [
                'total_size' => (int) $row['total_size'],
                'file_count' => (int) $row['file_count'],
            ];
        }

        return $sizes;
    }


    // =========================
    // RECENT FILES
    // =========================
    public function getRecentFiles(
        int $userId,
        int $limit = 8
    ): array {

        $stmt = $this->db->prepare("
            SELECT *
            FROM files
            WHERE user_id = :user_id
            AND deleted_at IS NULL
            ORDER BY created_at DESC
            LIMIT :limit
        ");

        $stmt->bindValue(
            ':user_id',
            $userId,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':limit',
            $limit,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll();
    }


    // =========================
    // GET SINGLE FILE
    // =========================
    public function getFile(
        int $id,
        int $userId
    ): ?array {

        $stmt = $this->db->prepare("
            SELECT *
            FROM files
            WHERE id = :id
            AND user_id = :user_id
            LIMIT 1
        ");

        $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId
        ]);

        $file = $stmt->fetch();

        return $file ?: null;
    }


    // =========================
    // SEARCH FILES
    // =========================
    public function searchFiles(
        int $userId,
        string $term
    ): array {

        $stmt = $this->db->prepare("
            SELECT *
            FROM files
            WHERE user_id = :user_id
            AND deleted_at IS NULL
            AND file_name LIKE :term
            ORDER BY created_at DESC
        ");

        $stmt->execute([
            ':user_id' => $userId,
            ':term' => '%' . $term . '%'
        ]);

        return $stmt->fetchAll();
    }


    // =========================
    // SOFT DELETE
    // =========================
    public function deleteFile(
        int $id,
        int $userId
    ): bool {

        $stmt = $this->db->prepare("
            UPDATE files
            SET deleted_at = NOW()
            WHERE id = :id
            AND user_id = :user_id
        ");

        return $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId
        ]);
    }


    // =========================
    // RESTORE FILE
    // =========================
    public function restoreFile(
        int $id,
        int $userId
    ): bool {

        $stmt = $this->db->prepare("
            UPDATE files
            SET deleted_at = NULL
            WHERE id = :id
            AND user_id = :user_id
        ");

        return $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId
        ]);
    }


    // =========================
    // DELETE PERMANENT
    // =========================
    public function permanentDeleteFile(
        int $id,
        int $userId
    ): bool {

        $file = $this->getFile($id, $userId);

        if (!$file) return false;

        $path =
            $this->uploadDir .
            DIRECTORY_SEPARATOR .
            $file['file_path'];

        if (file_exists($path)) {

            unlink($path);
        }

        $stmt = $this->db->prepare("
            DELETE FROM files
            WHERE id = :id
            AND user_id = :user_id
        ");

        return $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId
        ]);
    }


    // =========================
    // RENAME FILE
    // =========================
    public function renameFile(
        int $id,
        int $userId,
        string $newName
    ): bool {

        $stmt = $this->db->prepare("
            UPDATE files
            SET file_name = :file_name
            WHERE id = :id
            AND user_id = :user_id
        ");

        return $stmt->execute([
            ':file_name' => $newName,
            ':id' => $id,
            ':user_id' => $userId
        ]);
    }


    // =========================
    // MOVE FILE
    // =========================
    public function moveFile(
        int $id,
        int $userId,
        ?int $folderId
    ): bool {

        $stmt = $this->db->prepare("
            UPDATE files
            SET folder_id = :folder_id
            WHERE id = :id
            AND user_id = :user_id
        ");

        return $stmt->execute([
            ':folder_id' => $folderId,
            ':id' => $id,
            ':user_id' => $userId
        ]);
    }


    // =========================
    // STAR FILE
    // =========================
    public function toggleStar(
        int $id,
        int $userId
    ): bool {

        $stmt = $this->db->prepare("
            UPDATE files
            SET is_starred = NOT is_starred
            WHERE id = :id
            AND user_id = :user_id
        ");

        return $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId
        ]);
    }


    // =========================
    // STARRED FILES
    // =========================
    public function getStarredFiles(
        int $userId
    ): array {

        $stmt = $this->db->prepare("
            SELECT *
            FROM files
            WHERE user_id = :user_id
            AND is_starred = 1
            AND deleted_at IS NULL
            ORDER BY created_at DESC
        ");

        $stmt->execute([
            ':user_id' => $userId
        ]);

        return $stmt->fetchAll();
    }


    // =========================
    // RECYCLE BIN
    // =========================
    public function getDeletedFiles(
        int $userId
    ): array {

        $stmt = $this->db->prepare("
            SELECT *
            FROM files
            WHERE user_id = :user_id
            AND deleted_at IS NOT NULL
            ORDER BY deleted_at DESC
        ");

        $stmt->execute([
            ':user_id' => $userId
        ]);

        return $stmt->fetchAll();
    }


    // =========================
    // STORAGE SUMMARY
    // =========================
    public function getStorageSummary(
        int $userId
    ): array {

        $stmt = $this->db->prepare("
            SELECT
                storage_limit,
                storage_used
            FROM users
            WHERE id = :id
        ");

        $stmt->execute([
            ':id' => $userId
        ]);

        return $stmt->fetch() ?: [
            'storage_limit' => 0,
            'storage_used' => 0
        ];
    }
}
