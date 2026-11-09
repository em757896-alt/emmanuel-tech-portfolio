<?php
declare(strict_types=1);
/**
 * Elevate Retail POS — secure application bootstrap.
 * Expects ../db.php returning host, port, dbname, user, pass, charset.
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(['httponly'=>true,'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off','samesite'=>'Lax']);
    session_start();
}
date_default_timezone_set('Africa/Nairobi');
define('POS_NAME', 'Elevate Retail POS');
define('POS_BASE', __DIR__);
define('POS_URL', 'index.php');
if (!is_file(dirname(__DIR__) . '/db.php')) {
    http_response_code(503);
    exit('Database configuration is missing. Copy db.example.php to db.php and enter your hosting database details.');
}
$db = require dirname(__DIR__) . '/db.php';
$dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $db['host'], (int)($db['port'] ?? 3306), $db['dbname'], $db['charset'] ?? 'utf8mb4');
try {
    $pdo = new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES=>false,
    ]);
} catch (PDOException $e) {
    error_log('[Elevate POS] Database connection failed: '.$e->getMessage());
    http_response_code(503);
    exit('The database is temporarily unavailable. Check the private database configuration.');
}
function h(?string $v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function money(float|int|string $v): string { return 'KSh '.number_format((float)$v, 2); }
function csrf(): string { if (empty($_SESSION['pos_csrf'])) $_SESSION['pos_csrf']=bin2hex(random_bytes(32)); return $_SESSION['pos_csrf']; }
function verify_csrf(): void {
    if (!hash_equals($_SESSION['pos_csrf'] ?? '', (string)($_POST['csrf'] ?? ''))) {
        http_response_code(419); exit('Your session token expired. Refresh and try again.');
    }
}
function user(): ?array { return $_SESSION['pos_user'] ?? null; }
function require_login(): void { if (!user()) { header('Location: index.php?page=login'); exit; } }
function require_role(array $roles): void { require_login(); if (!in_array(user()['role'], $roles, true)) { http_response_code(403); exit('You do not have permission to perform this action.'); } }
function flash(string $type,string $message): void { $_SESSION['pos_flash']=['type'=>$type,'message'=>$message]; }
function go(string $page='dashboard'): never { header('Location: index.php?page='.rawurlencode($page)); exit; }
function audit(PDO $pdo,string $action,string $entity,?int $entityId=null,array $details=[]): void {
    $q=$pdo->prepare('INSERT INTO audit_logs(user_id,action,entity,entity_id,details,ip_address) VALUES(?,?,?,?,?,?)');
    $q->execute([user()['id']??null,$action,$entity,$entityId,json_encode($details,JSON_UNESCAPED_SLASHES),substr($_SERVER['REMOTE_ADDR']??'',0,45)]);
}
