<?php

require_once __DIR__ . '/Database.php';

class FolderManager
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->ensureParentIdColumn();
    }

    private function ensureParentIdColumn(): void
    {
        $stmt = $this->db->query("SHOW COLUMNS FROM folders LIKE 'parent_id'");
        if ($stmt && !$stmt->fetch()) {
            $this->db->exec("ALTER TABLE folders ADD COLUMN parent_id INT DEFAULT NULL AFTER folder_name");
            try {
                $this->db->exec("ALTER TABLE folders ADD CONSTRAINT fk_folders_parent FOREIGN KEY (parent_id) REFERENCES folders(id) ON DELETE CASCADE");
            } catch (PDOException $e) {
                // ignore if constraint creation is not possible
            }
        }
    }

    // CREATE FOLDER (WITH PARENT + UNIQUE NAME)
    public function createFolder(int $userId, string $name, ?int $parentId = null): int|false
    {
        $name = trim($name);

        // Prevent empty name
        if (empty($name)) {
            return false;
        }

        // Check duplicate inside same parent
        $stmt = $this->db->prepare("
            SELECT id FROM folders 
            WHERE user_id = :user_id 
            AND folder_name = :name 
            AND parent_id " . ($parentId === null ? "IS NULL" : "= :parent_id") . "
            AND deleted_at IS NULL
        ");

        $params = [
            ':user_id' => $userId,
            ':name' => $name
        ];

        if ($parentId !== null) {
            $params[':parent_id'] = $parentId;
        }

        $stmt->execute($params);

        if ($stmt->fetch()) {
            // Folder already exists
            return false;
        }

        // Insert
        $stmt = $this->db->prepare("
            INSERT INTO folders (user_id, folder_name, parent_id, created_at) 
            VALUES (:user_id, :name, :parent_id, NOW())
        ");

        if (!$stmt->execute([
            ':user_id' => $userId,
            ':name' => $name,
            ':parent_id' => $parentId
        ])) {
            return false;
        }

        return (int)$this->db->lastInsertId();
    }

    // GET ROOT FOLDERS OR CHILD FOLDERS
    public function getFolders(int $userId, ?int $parentId = null): array
    {
        $sql = "
            SELECT * FROM folders 
            WHERE user_id = :user_id 
            AND deleted_at IS NULL
        ";

        if ($parentId === null) {
            $sql .= " AND parent_id IS NULL";
        } else {
            $sql .= " AND parent_id = :parent_id";
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->db->prepare($sql);
        $params = [':user_id' => $userId];
        if ($parentId !== null) {
            $params[':parent_id'] = $parentId;
        }
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // GET CHILD FOLDERS
    public function getSubFolders(int $parentId, int $userId): array
    {
        return $this->getFolders($userId, $parentId);
    }

    public function getFolderByName(int $userId, string $name, ?int $parentId = null): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM folders 
            WHERE user_id = :user_id 
            AND folder_name = :name 
            AND parent_id " . ($parentId === null ? "IS NULL" : "= :parent_id") . "
            AND deleted_at IS NULL
            LIMIT 1
        ");

        $params = [
            ':user_id' => $userId,
            ':name' => $name
        ];

        if ($parentId !== null) {
            $params[':parent_id'] = $parentId;
        }

        $stmt->execute($params);
        $folder = $stmt->fetch();
        return $folder ?: null;
    }

    public function ensureFolderPath(int $userId, ?int $rootParentId, string $relativePath): ?int
    {
        $segments = array_filter(preg_split('#[\\/]#', trim($relativePath, '/\\')), fn($segment) => $segment !== '' && $segment !== '.' && $segment !== '..');
        $parentId = $rootParentId;

        foreach ($segments as $segment) {
            $segment = trim($segment);
            if ($segment === '') {
                continue;
            }

            $existing = $this->getFolderByName($userId, $segment, $parentId);
            if ($existing) {
                $parentId = (int)$existing['id'];
                continue;
            }

            $created = $this->createFolder($userId, $segment, $parentId);
            if ($created === false) {
                return null;
            }

            $parentId = $created;
        }

        return $parentId;
    }

    // GET SINGLE FOLDER
    public function getFolder(int $folderId, int $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM folders 
            WHERE id = :id AND user_id = :user_id LIMIT 1
        ");
        $stmt->execute([
            ':id' => $folderId,
            ':user_id' => $userId
        ]);

        $folder = $stmt->fetch();
        return $folder ?: null;
    }

    // DELETE (SOFT DELETE + CHILDREN)
    public function deleteFolder(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE folders 
            SET deleted_at = NOW() 
            WHERE id = :id AND user_id = :user_id
        ");

        $deleted = $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId
        ]);

        if ($deleted) {
            $this->deleteFilesInFolder($id, $userId);
            $this->deleteChildren($id, $userId);
        }

        return $deleted;
    }

    private function deleteFilesInFolder(int $folderId, int $userId): void
    {
        $stmt = $this->db->prepare("
            UPDATE files 
            SET deleted_at = NOW() 
            WHERE folder_id = :folder_id 
            AND user_id = :user_id
        ");
        $stmt->execute([
            ':folder_id' => $folderId,
            ':user_id' => $userId
        ]);
    }

    private function deleteChildren(int $parentId, int $userId): void
    {
        $stmt = $this->db->prepare("
            SELECT id FROM folders WHERE parent_id = :parent_id AND user_id = :user_id
        ");
        $stmt->execute([
            ':parent_id' => $parentId,
            ':user_id' => $userId
        ]);

        while ($row = $stmt->fetch()) {
            $this->deleteFolder($row['id'], $userId);
        }
    }

    public function getBreadcrumbs(int $folderId, int $userId): array
    {
        $crumbs = [];
        $current = $this->getFolder($folderId, $userId);

        while ($current) {
            array_unshift($crumbs, $current);
            if (empty($current['parent_id'])) {
                break;
            }
            $current = $this->getFolder((int)$current['parent_id'], $userId);
        }

        return $crumbs;
    }

    public function moveFolder(int $folderId, int $userId, ?int $targetParentId): bool
    {
        if ($targetParentId === $folderId) {
            return false;
        }

        $folder = $this->getFolder($folderId, $userId);
        if (!$folder) {
            return false;
        }

        if ($targetParentId !== null) {
            $targetParent = $this->getFolder($targetParentId, $userId);
            if (!$targetParent) {
                return false;
            }
        }

        $stmt = $this->db->prepare("
            SELECT id FROM folders 
            WHERE folder_name = :name 
            AND user_id = :user_id 
            AND parent_id " . ($targetParentId === null ? "IS NULL" : "= :target_parent_id") . "
            AND deleted_at IS NULL
        ");

        $params = [
            ':name' => $folder['folder_name'],
            ':user_id' => $userId
        ];

        if ($targetParentId !== null) {
            $params[':target_parent_id'] = $targetParentId;
        }

        $stmt->execute($params);
        if ($stmt->fetch()) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE folders 
            SET parent_id = :parent_id 
            WHERE id = :id 
            AND user_id = :user_id
        ");

        return $stmt->execute([
            ':parent_id' => $targetParentId,
            ':id' => $folderId,
            ':user_id' => $userId
        ]);
    }

    // RESTORE
    public function restoreFolder(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE folders 
            SET deleted_at = NULL 
            WHERE id = :id AND user_id = :user_id
        ");
        return $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId
        ]);
    }

    // PERMANENT DELETE
    public function permanentDeleteFolder(int $id, int $userId): bool
    {
        // Delete children first
        $this->deleteChildren($id, $userId);

        $stmt = $this->db->prepare("
            DELETE FROM folders 
            WHERE id = :id AND user_id = :user_id
        ");

        return $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId
        ]);
    }


    public function renameFolder(int $folderId, int $userId, string $newName): bool
    {
        $newName = trim($newName);

        // Validate name
        if (empty($newName) || !preg_match("/^[a-zA-Z0-9 _-]+$/", $newName)) {
            return false;
        }

        // Get current folder (to check parent_id)
        $stmt = $this->db->prepare("
        SELECT parent_id FROM folders 
        WHERE id = :id AND user_id = :user_id
    ");
        $stmt->execute([
            ':id' => $folderId,
            ':user_id' => $userId
        ]);

        $folder = $stmt->fetch();
        if (!$folder) {
            return false;
        }

        // Prevent duplicate inside same parent
        $stmt = $this->db->prepare("
        SELECT id FROM folders 
        WHERE folder_name = :name 
        AND user_id = :user_id 
        AND parent_id " . ($folder['parent_id'] === null ? "IS NULL" : "= :parent_id") . "
        AND deleted_at IS NULL
    ");

        $params = [
            ':name' => $newName,
            ':user_id' => $userId
        ];

        if ($folder['parent_id'] !== null) {
            $params[':parent_id'] = $folder['parent_id'];
        }

        $stmt->execute($params);

        if ($stmt->fetch()) {
            return false; // duplicate
        }

        // Update
        $stmt = $this->db->prepare("
        UPDATE folders 
        SET folder_name = :name 
        WHERE id = :id AND user_id = :user_id
    ");

        return $stmt->execute([
            ':name' => $newName,
            ':id' => $folderId,
            ':user_id' => $userId
        ]);
    }
    // GET DELETED (RECYCLE BIN)
    public function getDeletedFolders(int $userId): array
    {
        $stmt = $this->db->prepare("
        SELECT * FROM folders 
        WHERE user_id = :user_id 
        AND deleted_at IS NOT NULL
        ORDER BY deleted_at DESC
    ");

        $stmt->execute([
            ':user_id' => $userId
        ]);

        return $stmt->fetchAll();
    }
}
