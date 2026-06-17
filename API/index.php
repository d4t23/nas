<?php

require_once 'config.php';

$conn = Database::getConnection();

$sql = "
SELECT
    files.id,
    files.file_name,
    files.file_size,
    files.file_type,
    files.created_at,
    users.name AS owner
FROM files
LEFT JOIN users
ON users.id = files.user_id
WHERE files.deleted_at IS NULL
ORDER BY files.created_at DESC
";

$result = $conn->query($sql);

$files = [];

while($row = $result->fetch()){
    $files[] = $row;
}

response(true,'Files Loaded',$files);