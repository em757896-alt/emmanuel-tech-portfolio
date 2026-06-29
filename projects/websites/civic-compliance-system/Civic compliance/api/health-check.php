<?php
/**
 * api/health-check.php
 * System Health Check Endpoint
 * PBO Compliance Hub | CRECO Kenya
 *
 * DB: if0_42280606_if0_42280606_
 * User: if0_42280606
 * Password: AES256:4m0deNaMM0HA+yKw/HIgbYzFLvAjq8o1cD7cfheTaOSB8M/MqTc/Edx85mfbuzOL
 * Host: sql303.infinityfree.com
 */

require_once '../config/config.php';
require_once '../config/database.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-cache, no-store, must-revalidate');

$startTime = microtime(true);
$checks    = [];
$overallOk = true;

// ── 1. Database Connection ────────────────────────────────────────
try {
    $db   = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT 1 AS ping");
    $row  = $stmt->fetch();

    $checks['database'] = [
        'status'  => $row['ping'] === 1 ? 'ok' : 'error',
        'message' => $row['ping'] === 1 ? 'Connected' : 'Query failed',
    ];
} catch (Exception $e) {
    $checks['database'] = [
        'status'  => 'error',
        'message' => 'Connection failed',
    ];
    $overallOk = false;
}

// ── 2. Database Tables ────────────────────────────────────────────
if ($checks['database']['status'] === 'ok') {
    $requiredTables = [
        'users', 'monitoring_reports', 'monitoring_attachments',
        'knowledge_articles',
        'chatbot_conversations', 'chatbot_knowledge_base',
        'resources', 'faqs', 'page_views', 'audit_log',
    ];

    try {
        $stmt       = $db->query("SHOW TABLES");
        $existing   = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $missing    = array_diff($requiredTables, $existing);

        $checks['tables'] = [
            'status'  => empty($missing) ? 'ok' : 'warning',
            'message' => empty($missing)
                ? count($requiredTables) . ' required tables present'
                : 'Missing tables: ' . implode(', ', $missing),
            'total'   => count($existing),
        ];

        if (!empty($missing)) {
            $overallOk = false;
        }
    } catch (Exception $e) {
        $checks['tables'] = ['status' => 'error', 'message' => 'Could not check tables'];
        $overallOk = false;
    }
}

// ── 3. PHP Version ────────────────────────────────────────────────
$phpVersion      = PHP_VERSION;
$phpVersionOk    = version_compare($phpVersion, '7.4.0', '>=');

$checks['php'] = [
    'status'  => $phpVersionOk ? 'ok' : 'warning',
    'version' => $phpVersion,
    'message' => $phpVersionOk
        ? 'PHP ' . $phpVersion . ' (compatible)'
        : 'PHP ' . $phpVersion . ' — upgrade recommended (7.4+ required)',
];

// ── 4. Required PHP Extensions ────────────────────────────────────
$requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'mbstring', 'openssl', 'session'];
$missingExtensions  = [];

foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExtensions[] = $ext;
    }
}

$checks['extensions'] = [
    'status'  => empty($missingExtensions) ? 'ok' : 'error',
    'message' => empty($missingExtensions)
        ? 'All required extensions loaded'
        : 'Missing: ' . implode(', ', $missingExtensions),
    'checked' => $requiredExtensions,
];

if (!empty($missingExtensions)) {
    $overallOk = false;
}

// ── 5. Upload Directory ───────────────────────────────────────────
$uploadDir = '../uploads/';
$uploadsOk = is_dir($uploadDir) && is_writable($uploadDir);

$checks['uploads'] = [
    'status'  => $uploadsOk ? 'ok' : 'warning',
    'message' => $uploadsOk
        ? 'Upload directory writable'
        : 'Upload directory missing or not writable — create ../uploads/ with 755',
    'path'    => realpath($uploadDir) ?: $uploadDir,
];

// ── 6. Session ────────────────────────────────────────────────────
$sessionOk = session_status() !== PHP_SESSION_DISABLED;

$checks['session'] = [
    'status'  => $sessionOk ? 'ok' : 'error',
    'message' => $sessionOk ? 'Sessions enabled' : 'Sessions are disabled',
];

if (!$sessionOk) {
    $overallOk = false;
}

// ── 7. Disk Space ─────────────────────────────────────────────────
$freeBytes  = disk_free_space('../');
$totalBytes = disk_total_space('../');

if ($freeBytes !== false && $totalBytes !== false) {
    $freeMB    = round($freeBytes / 1024 / 1024, 1);
    $totalMB   = round($totalBytes / 1024 / 1024, 1);
    $usedPct   = round((($totalBytes - $freeBytes) / $totalBytes) * 100, 1);
    $diskOk    = $usedPct < 90;

    $checks['disk'] = [
        'status'    => $diskOk ? 'ok' : 'warning',
        'message'   => $usedPct . '% used (' . $freeMB . 'MB free of ' . $totalMB . 'MB)',
        'free_mb'   => $freeMB,
        'total_mb'  => $totalMB,
        'used_pct'  => $usedPct,
    ];
} else {
    $checks['disk'] = ['status' => 'unknown', 'message' => 'Could not determine disk space'];
}

// ── 8. Response Time ──────────────────────────────────────────────
$responseMs = round((microtime(true) - $startTime) * 1000, 2);

$checks['performance'] = [
    'status'      => $responseMs < 500 ? 'ok' : 'warning',
    'response_ms' => $responseMs,
    'message'     => 'Health check completed in ' . $responseMs . 'ms',
];

// ── 9. Platform Stats Snapshot ────────────────────────────────────
if ($checks['database']['status'] === 'ok') {
    try {
        $snapStmt = $db->query("
            SELECT
                (SELECT COUNT(*) FROM users) as users,
                (SELECT COUNT(*) FROM monitoring_reports) as reports,
                (SELECT COUNT(*) FROM monitoring_reports WHERE status='pending') as pending,
                (SELECT COUNT(*) FROM knowledge_articles WHERE is_published=1) as articles,
                (SELECT COUNT(*) FROM chatbot_conversations WHERE DATE(created_at)=CURDATE()) as chatbot_today
        ");
        $snap = $snapStmt->fetch(PDO::FETCH_ASSOC);

        $checks['platform_stats'] = [
            'status'  => 'ok',
            'data'    => $snap,
        ];
    } catch (Exception $e) {
        $checks['platform_stats'] = ['status' => 'error', 'message' => 'Could not fetch stats'];
    }
}

// ── Build Response ────────────────────────────────────────────────
$statusCounts = array_count_values(array_column($checks, 'status'));

$response = [
    'status'      => $overallOk ? 'ok' : 'degraded',
    'platform'    => 'PBO Compliance Hub',
    'version'     => defined('APP_VERSION') ? APP_VERSION : '1.0.0',
    'environment' => defined('APP_ENV') ? APP_ENV : 'production',
    'timestamp'   => date('Y-m-d H:i:s'),
    'server_time' => date('c'),
    'uptime'      => [
        'checks_ok'      => $statusCounts['ok'] ?? 0,
        'checks_warning' => $statusCounts['warning'] ?? 0,
        'checks_error'   => $statusCounts['error'] ?? 0,
    ],
    'checks' => $checks,
];

// HTTP status code
http_response_code($overallOk ? 200 : 503);

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);