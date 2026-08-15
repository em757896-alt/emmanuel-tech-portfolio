<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('login.php');
}
if (!verify_csrf()) {
    flash('error', 'Invalid security token. Please try again.');
    redirect('login.php');
}

$user = trim($_POST['username'] ?? '');
$pass = (string) ($_POST['password'] ?? '');

if ($user === '' || $pass === '') {
    flash('error', 'Please enter your username and password.');
    redirect('login.php');
}

$stmt = $pdo->prepare('SELECT id, username, password_hash FROM admins WHERE username = ? LIMIT 1');
$stmt->execute([$user]);
$admin = $stmt->fetch();

if ($admin && password_verify($pass, $admin['password_hash'])) {
    session_regenerate_id(true);
    $_SESSION['admin_id']   = (int) $admin['id'];
    $_SESSION['admin_name'] = $admin['username'];
    flash('success', 'Welcome back, ' . $admin['username'] . '!');
    redirect('admin/dashboard.php');
}

flash('error', 'Invalid username or password.');
redirect('login.php');
