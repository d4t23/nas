<?php
require_once __DIR__ . '/../app/classes/Auth.php';
$auth = new Auth();
$auth->logout();
$_SESSION['logout_message'] = 'You have logged out successfully.';
header('Location: login.php');
exit;
