<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

$auth = new Auth();
$auth->logout();

$_SESSION = [];
session_destroy();

header('Location: login.php?logged_out=1');
exit;
