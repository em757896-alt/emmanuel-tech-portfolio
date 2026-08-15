<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('contact.php');
}
if (!verify_csrf()) {
    flash('error', 'Invalid security token. Please try again.');
    redirect('contact.php');
}

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$body    = trim($_POST['body'] ?? '');

if ($name === '' || $email === '' || $subject === '' || $body === '') {
    flash('error', 'Please fill in all fields.');
    redirect('contact.php');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('error', 'Please provide a valid email address.');
    redirect('contact.php');
}
if (strlen($body) < 10) {
    flash('error', 'Your message is too short.');
    redirect('contact.php');
}

$stmt = $pdo->prepare('INSERT INTO messages (name, email, subject, body) VALUES (?, ?, ?, ?)');
$stmt->execute([$name, $email, $subject, $body]);

flash('success', 'Thank you! Your message has been received.');
redirect('contact.php');
