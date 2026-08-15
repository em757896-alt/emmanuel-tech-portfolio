<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';

require_admin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Simple CSRF: require the token to be passed (admin pages embed it in the delete link).
if (!isset($_GET['csrf_token']) || !verify_csrf((string) $_GET['csrf_token'])) {
    flash('error', 'Invalid security token. Please try again.');
    redirect('admin/students.php');
}

if ($id > 0) {
    $stmt = $pdo->prepare('DELETE FROM students WHERE id = ?');
    $stmt->execute([$id]);
    flash('success', 'Student record deleted.');
} else {
    flash('error', 'No student specified.');
}
redirect('admin/students.php');
