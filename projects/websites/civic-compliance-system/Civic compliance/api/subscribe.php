<?php
/**
 * api/subscribe.php
 * Newsletter subscription endpoint
 * PBO Compliance Hub | CRECO Kenya
 */

require_once '../config/config.php';
require_once '../config/database.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if (defined('ALLOWED_ORIGIN')) {
    header('Access-Control-Allow-Origin: ' . ALLOWED_ORIGIN);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$name  = sanitizeInput($_POST['name'] ?? '');
$consent = isset($_POST['consent']);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Please provide a valid email address.']);
    exit;
}

if (!$consent) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'You must consent to receive communications.']);
    exit;
}

$clientIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
if (!checkRateLimit('subscribe_' . md5($clientIp), 3, 3600)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'error' => 'Too many attempts. Please try again later.']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("SELECT COUNT(*) FROM subscribers WHERE email = :email");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'error' => 'This email is already subscribed.']);
        exit;
    }

    $stmt = $db->prepare("
        INSERT INTO subscribers (email, name, status, subscribed_at, ip_address)
        VALUES (:email, :name, 'active', NOW(), :ip)
    ");
    $stmt->execute([
        ':email' => $email,
        ':name'  => $name,
        ':ip'    => md5($clientIp),
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Thank you for subscribing! You will receive updates from CRECO Kenya.',
    ]);

} catch (Exception $e) {
    error_log('Subscribe API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'A server error occurred. Please try again later.']);
}
