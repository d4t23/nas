<?php

require_once 'config.php';

$conn = Database::getConnection();

$totalUsers = $conn
    ->query("SELECT COUNT(*) as total FROM users")
    ->fetch()['total'];

$totalFiles = $conn
    ->query("
        SELECT COUNT(*) as total
        FROM files
        WHERE deleted_at IS NULL
    ")
    ->fetch()['total'];

$totalFolders = $conn
    ->query("
        SELECT COUNT(*) as total
        FROM folders
        WHERE deleted_at IS NULL
    ")
    ->fetch()['total'];

$totalStorage = $conn
    ->query("
        SELECT COALESCE(SUM(file_size),0) as total
        FROM files
        WHERE deleted_at IS NULL
    ")
    ->fetch()['total'];

response(true,'Dashboard Statistics',[
    'users' => (int)$totalUsers,
    'files' => (int)$totalFiles,
    'folders' => (int)$totalFolders,
    'storage_used' => (int)$totalStorage
]);