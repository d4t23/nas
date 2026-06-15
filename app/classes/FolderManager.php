<?php

require_once __DIR__ . '/Database.php';

class FolderManager
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // CREATE FOLDER (WITH PARENT + UNIQUE NAME)
    public function createFolder(int $userId, string $name, ?int $parentId = null): bool
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

        return $stmt->execute([
            ':user_id' => $userId,
            ':name' => $name,
            ':parent_id' => $parentId
        ]);
    }

    // GET ROOT FOLDERS
    public function getFolders(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM folders 
            WHERE user_id = :user_id 
            AND parent_id IS NULL
            AND deleted_at IS NULL 
            ORDER BY created_at DESC
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    // GET CHILD FOLDERS
    public function getSubFolders(int $parentId, int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM folders 
            WHERE parent_id = :parent_id 
            AND user_id = :user_id 
            AND deleted_at IS NULL
            ORDER BY created_at DESC
        ");
        $stmt->execute([
            ':parent_id' => $parentId,
            ':user_id' => $userId
        ]);
        return $stmt->fetchAll();
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
        // Delete current
        $stmt = $this->db->prepare("
            UPDATE folders 
            SET deleted_at = NOW() 
            WHERE id = :id AND user_id = :user_id
        ");

        $deleted = $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId
        ]);

        // Delete children recursively
        $this->deleteChildren($id, $userId);

        return $deleted;
    }

    private function deleteChildren(int $parentId, int $userId)
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
