<?php
require_once __DIR__ . '/../app/classes/Auth.php';
$auth = new Auth();

if ($auth->check()) {
    header('Location: dashboard_new.php');
    exit;
}

header('Location: login.php');
exit;
