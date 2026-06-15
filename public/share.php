<?php
require_once __DIR__ . '/../app/classes/ShareManager.php';
require_once __DIR__ . '/../app/classes/Database.php';

$token = $_GET['token'] ?? null;
$share = null;
if ($token) {
    $shareManager = new ShareManager();
    $share = $shareManager->findByToken($token);
}

if (!$share) {
    http_response_code(404);
    echo 'Invalid or expired sharing link.';
    exit;
}

$filePath = realpath(__DIR__ . '/../storage/uploads/' . $share['file_path']);
if (!$filePath || !file_exists($filePath)) {
    http_response_code(404);
    echo 'File not found.';
    exit;
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($share['file_name']) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit;
