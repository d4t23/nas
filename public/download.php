<?php
require_once __DIR__ . '/../app/classes/Auth.php';
require_once __DIR__ . '/../app/classes/FileManager.php';

$auth = new Auth();
$auth->requireAuth();
$fileManager = new FileManager();

$fileId = isset($_GET['file_id']) ? (int) $_GET['file_id'] : 0;
$file = $fileManager->getFile($fileId, $auth->user()['id']);

if (!$file) {
    http_response_code(404);
    echo 'File not found.';
    exit;
}

$filePath = realpath(__DIR__ . '/../storage/uploads/' . $file['file_path']);
if (!$filePath || !file_exists($filePath)) {
    http_response_code(404);
    echo 'File not found on disk.';
    exit;
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file['file_name']) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit;
