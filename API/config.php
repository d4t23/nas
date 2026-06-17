<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../app/classes/Database.php';

function response($success, $message, $data = null)
{
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}