<?php
declare(strict_types=1);

/**
 * config.php - application bootstrap
 * Session hardening, PDO connection, shared helpers.
 * Include this FIRST in every page.
 */

if (defined('APP_NAME')) {
    return; // already booted
}

define('APP_NAME', 'Elevate Media College');
define('APP_TAGLINE', 'Student Management System');
define('BASE_PATH', dirname(__DIR__));

// ---- Secure session ----
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

date_default_timezone_set('UTC');

// ---- Database (PDO) ----
$GLOBALS['db_config'] = require BASE_PATH . '/db.php';

$dsn = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
    $GLOBALS['db_config']['host'],
    $GLOBALS['db_config']['port'] ?? 3306,
    $GLOBALS['db_config']['dbname'],
    $GLOBALS['db_config']['charset'] ?? 'utf8mb4'
);

try {
    $pdo = new PDO($dsn, $GLOBALS['db_config']['user'], $GLOBALS['db_config']['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Database connection failed. Please try again later.');
}

require_once BASE_PATH . '/includes/functions.php';
