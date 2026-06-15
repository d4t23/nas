<?php
require_once __DIR__ . '/../app/classes/Auth.php';
$auth = new Auth();

if ($auth->check()) {
    header('Location: dashboard.php');
    exit;
}

header('Location: login.php');
exit;
