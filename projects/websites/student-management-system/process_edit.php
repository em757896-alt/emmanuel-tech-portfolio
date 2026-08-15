<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/students.php');
}
if (!verify_csrf()) {
    flash('error', 'Invalid security token. Please try again.');
    redirect('admin/students.php');
}

$id = (int) ($_POST['id'] ?? 0);
$fields = ['first_name', 'last_name', 'email', 'phone', 'course', 'department'];
$data = [];
foreach ($fields as $f) {
    $data[$f] = trim((string) ($_POST[$f] ?? ''));
}

if ($id < 1 || in_array('', $data, true)) {
    flash('error', 'Please fill in all fields.');
    redirect('admin/students.php');
}
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    flash('error', 'Please provide a valid email address.');
    redirect('edit.php?id=' . $id);
}

$stmt = $pdo->prepare('SELECT code FROM departments WHERE code = ?');
$stmt->execute([$data['department']]);
if (!$stmt->fetch()) {
    flash('error', 'Unknown department selected.');
    redirect('edit.php?id=' . $id);
}

// Duplicate email check (excluding this student)
$stmt = $pdo->prepare('SELECT id FROM students WHERE email = ? AND id <> ?');
$stmt->execute([$data['email'], $id]);
if ($stmt->fetch()) {
    flash('error', 'Another student already uses that email.');
    redirect('edit.php?id=' . $id);
}

$stmt = $pdo->prepare(
    'UPDATE students
     SET first_name = ?, last_name = ?, email = ?, phone = ?, course = ?, department = ?
     WHERE id = ?'
);
$stmt->execute([$data['first_name'], $data['last_name'], $data['email'], $data['phone'], $data['course'], $data['department'], $id]);

flash('success', 'Student updated successfully.');
redirect('admin/students.php');
