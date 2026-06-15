<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../app/classes/Auth.php";

$userId = $user['id'] ?? null;

if (!$userId) {
    header("Location: login.php");
    exit;
}

$folderId = $_POST['folder_id'] ?? null;

if (!$folderId || !isset($_FILES['file'])) {
    die("Invalid request");
}

$file = $_FILES['file'];

/* CREATE UPLOAD FOLDER IF NOT EXISTS */
$uploadDir = __DIR__ . "/../uploads/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

/* GENERATE UNIQUE FILE NAME */
$fileName = time() . "_" . basename($file["name"]);
$targetPath = $uploadDir . $fileName;

/* MOVE FILE */
if (move_uploaded_file($file["tmp_name"], $targetPath)) {

    /* SAVE TO DATABASE */
    $stmt = $db->prepare("
        INSERT INTO files (user_id, folder_id, file_name, file_path, created_at)
        VALUES (:user_id, :folder_id, :file_name, :file_path, NOW())
    ");

    $stmt->execute([
        ':user_id' => $userId,
        ':folder_id' => $folderId,
        ':file_name' => $file["name"],
        ':file_path' => "uploads/" . $fileName
    ]);

    header("Location: folder.php?id=" . $folderId);
    exit;
} else {
    die("File upload failed");
}
