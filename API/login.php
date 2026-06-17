<?php

require_once '../config.php';

$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');

if(empty($email) || empty($password)){
    response(false,'Email and password required');
}

$conn = Database::getConnection();

$stmt = $conn->prepare("
    SELECT *
    FROM users
    WHERE email = ?
    LIMIT 1
");

$stmt->execute([$email]);
$user = $stmt->fetch()->fetch_assoc();

if(!$user){
    response(false,'User not found');
}

if(!password_verify($password,$user['password'])){
    response(false,'Invalid password');
}

response(true,'Login successful',[
    'id' => $user['id'],
    'name' => $user['name'],
    'email' => $user['email'],
    'role' => $user['role']
]);