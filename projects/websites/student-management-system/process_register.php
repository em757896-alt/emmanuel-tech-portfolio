<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('register.php');
}
if (!verify_csrf()) {
    flash('error', 'Invalid security token. Please try again.');
    redirect('register.php');
}

$fields = ['first_name', 'last_name', 'email', 'phone', 'course', 'department', 'avatar_color'];
$data = [];
foreach ($fields as $f) {
    $data[$f] = trim((string) ($_POST[$f] ?? ''));
}

if (in_array('', $data, true)) {
    flash('error', 'Please fill in all fields.');
    redirect('register.php');
}
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    flash('error', 'Please provide a valid email address.');
    redirect('register.php');
}
if (!preg_match('/^#([0-9a-fA-F]{6})$/', $data['avatar_color'])) {
    $data['avatar_color'] = '#6d5df6';
}

// Enforce a known department code
$stmt = $pdo->prepare('SELECT code FROM departments WHERE code = ?');
$stmt->execute([$data['department']]);
if (!$stmt->fetch()) {
    flash('error', 'Unknown department selected.');
    redirect('register.php');
}

// Duplicate email check
$stmt = $pdo->prepare('SELECT id FROM students WHERE email = ?');
$stmt->execute([$data['email']]);
if ($stmt->fetch()) {
    flash('error', 'A student with that email already exists.');
    redirect('register.php');
}

$stmt = $pdo->prepare(
    'INSERT INTO students (first_name, last_name, email, phone, course, department, avatar_color)
     VALUES (?, ?, ?, ?, ?, ?, ?)'
);
$stmt->execute([
    $data['first_name'],
    $data['last_name'],
    $data['email'],
    $data['phone'],
    $data['course'],
    $data['department'],
    $data['avatar_color'],
]);

$id = (int) $pdo->lastInsertId();
flash('success', 'Student ' . $data['first_name'] . ' ' . $data['last_name'] . ' registered successfully.');
redirect(is_admin() ? 'admin/students.php' : 'students.php?q=' . urlencode($data['last_name']));
