<?php
/**
 * api/admin-dashboard.php
 * Admin Dashboard API — real-time data, moderation, export
 * PBO Compliance Hub | CRECO Kenya
 *
 * DB: if0_42280606_if0_42280606_
 * User: if0_42280606
 * Password: (Your vPanel Password)
 * Host: sql303.infinityfree.com
 */

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/auth.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// Must be admin
if(!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success'=>false, 'error'=>'Unauthorized']);
    exit;
}

$db     = Database::getInstance()->getConnection();
$action = sanitizeInput($_GET['action'] ?? $_POST['action'] ?? '');

// ── Route Actions ───────────────────────────────────────────────
switch($action) {

    case 'get_stats':
        getStats($db);
        break;

    case 'get_trend':
        getTrend($db);
        break;

    case 'get_county_data':
        getCountyData($db);
        break;

    case 'get_reports':
        getReports($db);
        break;

    case 'export':
        exportData($db);
        break;

    case 'get_chatbot_stats':
        getChatbotStats($db);
        break;

    case 'get_platform_usage':
        getPlatformUsage($db);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success'=>false, 'error'=>'Unknown action']);
        break;
}

// ── Function: Get Dashboard Stats ────────────────────────────────
function getStats(PDO $db): void {
    try {
        $stats = [];

        // Users
        $stmt = $db->query("SELECT COUNT(*) FROM users");
        $stats['total_users'] = (int)$stmt->fetchColumn();

        $stmt = $db->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stats['new_users_30d'] = (int)$stmt->fetchColumn();

        // Reports
        $stmt = $db->query("SELECT COUNT(*), COUNT(CASE WHEN status='pending' THEN 1 END), COUNT(CASE WHEN status='approved' THEN 1 END)
                            FROM monitoring_reports");
        $row = $stmt->fetch(PDO::FETCH_NUM);
        $stats['total_reports']    = (int)$row[0];
        $stats['pending_reports']  = (int)$row[1];
        $stats['approved_reports'] = (int)$row[2];

        // Reports by type
        $stmt = $db->query("SELECT report_type, COUNT(*) as count FROM monitoring_reports GROUP BY report_type");
        $stats['reports_by_type'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Incidents
        $stmt = $db->query("SELECT COUNT(*) FROM monitoring_reports WHERE report_type='incident' AND severity IN ('high','critical')");
        $stats['critical_incidents'] = (int)$stmt->fetchColumn();

        // Content
        $stmt = $db->query("SELECT COUNT(*) FROM knowledge_articles WHERE is_published=1");
        $stats['published_articles'] = (int)$stmt->fetchColumn();

        $stmt = $db->query("SELECT COALESCE(SUM(download_count),0) FROM resources");
        $stats['total_downloads'] = (int)$stmt->fetchColumn();

        // Chatbot
        $stmt = $db->query("SELECT COUNT(*) FROM chatbot_conversations WHERE DATE(created_at)=CURDATE()");
        $stats['chatbot_queries_today'] = (int)$stmt->fetchColumn();

        $stmt = $db->query("SELECT COUNT(*) FROM chatbot_conversations WHERE feedback='negative'");
        $stats['chatbot_negative_feedback'] = (int)$stmt->fetchColumn();

        // Page views
        $stmt = $db->query("SELECT COUNT(*) FROM page_views WHERE DATE(visited_at)=CURDATE()");
        $stats['page_views_today'] = (int)$stmt->fetchColumn();

        $stmt = $db->query("SELECT COUNT(*) FROM page_views WHERE visited_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $stats['page_views_7d'] = (int)$stmt->fetchColumn();

        // Timestamp
        $stats['generated_at'] = date('Y-m-d H:i:s');

        echo json_encode(['success'=>true, 'stats'=>$stats]);

    } catch(Exception $e) {
        error_log('Dashboard Stats Error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success'=>false, 'error'=>'Failed to fetch statistics']);
    }
}

// ── Function: Reports Trend ──────────────────────────────────────
function getTrend(PDO $db): void {
    $period = sanitizeInput($_GET['period'] ?? '6months');
    $metric = sanitizeInput($_GET['metric'] ?? 'reports');

    $allowed = ['reports','users','views'];
    if(!in_array($metric, $allowed)) {
        echo json_encode(['success'=>false, 'error'=>'Invalid metric']);
        return;
    }

    try {
        switch($period) {
            case '30days':
                $groupBy   = "DATE(created_at)";
                $labelFmt  = "DATE_FORMAT(created_at, '%d %b')";
                $whereClause = "created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                break;
            case '12months':
                $groupBy   = "DATE_FORMAT(created_at, '%Y-%m')";
                $labelFmt  = "DATE_FORMAT(created_at, '%b %Y')";
                $whereClause = "created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)";
                break;
            default: // 6months
                $groupBy   = "DATE_FORMAT(created_at, '%Y-%m')";
                $labelFmt  = "DATE_FORMAT(created_at, '%b %Y')";
                $whereClause = "created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)";
        }

        $tableMap = [
            'reports' => 'monitoring_reports',
            'users'   => 'users',
            'views'   => 'page_views',
        ];

        $dateCol = $metric === 'views' ? 'visited_at' : 'created_at';
        $table   = $tableMap[$metric];

        $stmt = $db->query("
            SELECT
                $labelFmt as label,
                $groupBy as period_key,
                COUNT(*) as count
            FROM $table
            WHERE $dateCol >= DATE_SUB(NOW(), INTERVAL " . ($period === '12months' ? '12' : ($period === '30days' ? '30' : '6')) . " " . ($period === '30days' ? 'DAY' : 'MONTH') . ")
            GROUP BY period_key, label
            ORDER BY period_key ASC
        ");

        $rows = $stmt->fetchAll();

        echo json_encode([
            'success' => true,
            'metric'  => $metric,
            'period'  => $period,
            'labels'  => array_column($rows, 'label'),
            'data'    => array_map('intval', array_column($rows, 'count')),
        ]);

    } catch(Exception $e) {
        error_log('Trend Error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success'=>false,'error'=>'Failed to fetch trend data']);
    }
}

// ── Function: County Data ────────────────────────────────────────
function getCountyData(PDO $db): void {
    try {
        $reportType = sanitizeInput($_GET['report_type'] ?? '');
        $startDate  = sanitizeInput($_GET['start_date'] ?? '');
        $endDate    = sanitizeInput($_GET['end_date'] ?? '');

        $conditions = ["submitter_county IS NOT NULL AND submitter_county != ''"];
        $params = [];

        if($reportType) {
            $conditions[] = "report_type = :report_type";
            $params[':report_type'] = $reportType;
        }
        if($startDate) {
            $conditions[] = "DATE(created_at) >= :start_date";
            $params[':start_date'] = $startDate;
        }
        if($endDate) {
            $conditions[] = "DATE(created_at) <= :end_date";
            $params[':end_date'] = $endDate;
        }

        $where = implode(' AND ', $conditions);

        $stmt = $db->prepare("
            SELECT
                submitter_county as county,
                COUNT(*) as total,
                COUNT(CASE WHEN report_type='incident' THEN 1 END) as incidents,
                COUNT(CASE WHEN report_type='barrier' THEN 1 END) as barriers,
                COUNT(CASE WHEN report_type='compliance' THEN 1 END) as compliance,
                COUNT(CASE WHEN report_type='enabling' THEN 1 END) as enabling,
                COUNT(CASE WHEN severity IN ('high','critical') THEN 1 END) as high_severity
            FROM monitoring_reports
            WHERE $where
            GROUP BY submitter_county
            ORDER BY total DESC
        ");
        $stmt->execute($params);
        $data = $stmt->fetchAll();

        echo json_encode([
            'success' => true,
            'data'    => $data,
            'total_counties' => count($data),
            'total_reports'  => array_sum(array_column($data, 'total')),
        ]);

    } catch(Exception $e) {
        error_log('County Data Error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success'=>false,'error'=>'Failed to fetch county data']);
    }
}

// ── Function: Get Reports (filtered) ────────────────────────────
function getReports(PDO $db): void {
    $status     = sanitizeInput($_GET['status'] ?? '');
    $reportType = sanitizeInput($_GET['report_type'] ?? '');
    $county     = sanitizeInput($_GET['county'] ?? '');
    $severity   = sanitizeInput($_GET['severity'] ?? '');
    $search     = sanitizeInput($_GET['q'] ?? '');
    $pageNum    = max(1, intval($_GET['page'] ?? 1));
    $perPage    = min(50, max(10, intval($_GET['per_page'] ?? 20)));
    $offset     = ($pageNum - 1) * $perPage;

    $conditions = ['1=1'];
    $params = [];

    if($status) {
        $conditions[] = "status = :status";
        $params[':status'] = $status;
    }
    if($reportType) {
        $conditions[] = "report_type = :report_type";
        $params[':report_type'] = $reportType;
    }
    if($county) {
        $conditions[] = "submitter_county = :county";
        $params[':county'] = $county;
    }
    if($severity) {
        $conditions[] = "severity = :severity";
        $params[':severity'] = $severity;
    }
    if($search) {
        $conditions[] = "(org_name LIKE :search OR description LIKE :search2)";
        $params[':search']  = "%$search%";
        $params[':search2'] = "%$search%";
    }

    $where = implode(' AND ', $conditions);

    try {
        // Count
        $countStmt = $db->prepare("SELECT COUNT(*) FROM monitoring_reports WHERE $where");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Fetch
        $params[':limit']  = $perPage;
        $params[':offset'] = $offset;

        $stmt = $db->prepare("
            SELECT
                id, report_type, org_name, org_type, submitter_county,
                severity, status, created_at,
                SUBSTRING(description, 1, 150) as description_preview,
                (SELECT COUNT(*) FROM monitoring_attachments WHERE report_id = monitoring_reports.id) as attachment_count
            FROM monitoring_reports
            WHERE $where
            ORDER BY
                FIELD(status,'pending','flagged','approved','rejected'),
                FIELD(severity,'critical','high','medium','low'),
                created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->execute($params);
        $reports = $stmt->fetchAll();

        echo json_encode([
            'success'     => true,
            'reports'     => $reports,
            'total'       => $total,
            'page'        => $pageNum,
            'per_page'    => $perPage,
            'total_pages' => ceil($total / $perPage),
        ]);

    } catch(Exception $e) {
        error_log('Get Reports Error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success'=>false,'error'=>'Failed to fetch reports']);
    }
}

// ── Function: Export Data ────────────────────────────────────────
function exportData(PDO $db): void {
    $format     = sanitizeInput($_GET['format'] ?? 'csv');
    $reportType = sanitizeInput($_GET['report_type'] ?? '');
    $startDate  = sanitizeInput($_GET['start_date'] ?? '');
    $endDate    = sanitizeInput($_GET['end_date'] ?? '');
    $county     = sanitizeInput($_GET['county'] ?? '');

    $allowedFormats = ['csv','json'];
    if(!in_array($format, $allowedFormats)) {
        echo json_encode(['success'=>false,'error'=>'Invalid export format']);
        return;
    }

    $conditions = ['1=1'];
    $params = [];

    if($reportType) {
        $conditions[] = "report_type = :report_type";
        $params[':report_type'] = $reportType;
    }
    if($startDate) {
        $conditions[] = "DATE(created_at) >= :start_date";
        $params[':start_date'] = $startDate;
    }
    if($endDate) {
        $conditions[] = "DATE(created_at) <= :end_date";
        $params[':end_date'] = $endDate;
    }
    if($county) {
        $conditions[] = "submitter_county = :county";
        $params[':county'] = $county;
    }

    $where = implode(' AND ', $conditions);

    try {
        $stmt = $db->prepare("
            SELECT
                id,
                report_type,
                org_name,
                org_type,
                submitter_county,
                severity,
                status,
                description,
                report_data,
                created_at,
                updated_at
            FROM monitoring_reports
            WHERE $where
            ORDER BY created_at DESC
            LIMIT 5000
        ");
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $filename = 'pbo-hub-export-' . date('Y-m-d');

        if($format === 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

            $output = fopen('php://output', 'w');
            if(!empty($rows)) {
                // Headers
                fputcsv($output, array_keys($rows[0]));
                foreach($rows as $row) {
                    // Decode JSON report_data for readability
                    if(isset($row['report_data'])) {
                        $decoded = json_decode($row['report_data'], true);
                        $row['report_data'] = is_array($decoded)
                            ? implode('; ', array_map(fn($k,$v) => "$k: $v", array_keys($decoded), $decoded))
                            : $row['report_data'];
                    }
                    fputcsv($output, $row);
                }
            } else {
                fputcsv($output, ['No data found for the selected filters']);
            }
            fclose($output);
            exit;

        } else {
            // JSON export
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="' . $filename . '.json"');

            echo json_encode([
                'exported_at'    => date('Y-m-d H:i:s'),
                'exported_by'    => $_SESSION['user_email'] ?? 'admin',
                'total_records'  => count($rows),
                'filters_applied'=> [
                    'report_type' => $reportType ?: 'all',
                    'county'      => $county ?: 'all',
                    'start_date'  => $startDate ?: 'all',
                    'end_date'    => $endDate ?: 'all',
                ],
                'data' => $rows,
            ], JSON_PRETTY_PRINT);
            exit;
        }

    } catch(Exception $e) {
        error_log('Export Error: ' . $e->getMessage());
        echo json_encode(['success'=>false,'error'=>'Export failed. Please try again.']);
    }
}

// ── Function: Chatbot Stats ──────────────────────────────────────
function getChatbotStats(PDO $db): void {
    try {
        $period = sanitizeInput($_GET['period'] ?? '30days');
        $interval = $period === '7days' ? '7 DAY' : '30 DAY';

        // Volume by day
        $stmt = $db->query("
            SELECT
                DATE_FORMAT(created_at, '%a %d') as label,
                DATE(created_at) as day,
                COUNT(*) as queries,
                SUM(CASE WHEN feedback='positive' THEN 1 ELSE 0 END) as positive,
                SUM(CASE WHEN feedback='negative' THEN 1 ELSE 0 END) as negative,
                SUM(CASE WHEN flagged_for_review=1 THEN 1 ELSE 0 END) as flagged_for_review
            FROM chatbot_conversations
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)
            GROUP BY day, label
            ORDER BY day ASC
        ");
        $daily = $stmt->fetchAll();

        // Top questions (by keyword frequency)
        $stmt = $db->query("
            SELECT user_message, COUNT(*) as count
            FROM chatbot_conversations
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)
            GROUP BY user_message
            ORDER BY count DESC
            LIMIT 10
        ");
        $topQueries = $stmt->fetchAll();

        // Flagged responses needing review
        $stmt = $db->query("
            SELECT id, user_message, bot_response, feedback_note, created_at
            FROM chatbot_conversations
            WHERE flagged_for_review=1
            ORDER BY created_at DESC
            LIMIT 20
        ");
        $flagged = $stmt->fetchAll();

        // Overall stats
        $stmt = $db->query("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN feedback='positive' THEN 1 ELSE 0 END) as positive,
                SUM(CASE WHEN feedback='negative' THEN 1 ELSE 0 END) as negative,
                SUM(CASE WHEN flagged_for_review=1 THEN 1 ELSE 0 END) as total_flagged
            FROM chatbot_conversations
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)
        ");
        $overall = $stmt->fetch();

        echo json_encode([
            'success'     => true,
            'period'      => $period,
            'overall'     => $overall,
            'daily'       => $daily,
            'top_queries' => $topQueries,
            'flagged'     => $flagged,
        ]);

    } catch(Exception $e) {
        error_log('Chatbot Stats Error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success'=>false,'error'=>'Failed to fetch chatbot statistics']);
    }
}

// ── Function: Platform Usage ─────────────────────────────────────
function getPlatformUsage(PDO $db): void {
    try {
        $period = sanitizeInput($_GET['period'] ?? '7days');
        $interval = $period === '30days' ? '30 DAY' : '7 DAY';

        // Page views by page
        $stmt = $db->query("
            SELECT page_path, COUNT(*) as views
            FROM page_views
            WHERE visited_at >= DATE_SUB(NOW(), INTERVAL $interval)
            GROUP BY page_path
            ORDER BY views DESC
            LIMIT 10
        ");
        $topPages = $stmt->fetchAll();

        // Unique visitors estimate (by hashed IP)
        $stmt = $db->query("
            SELECT COUNT(DISTINCT ip_hash) as unique_visitors
            FROM page_views
            WHERE visited_at >= DATE_SUB(NOW(), INTERVAL $interval)
        ");
        $uniqueVisitors = (int)$stmt->fetchColumn();

        // Device types
        $stmt = $db->query("
            SELECT device_type, COUNT(*) as count
            FROM page_views
            WHERE visited_at >= DATE_SUB(NOW(), INTERVAL $interval)
            GROUP BY device_type
        ");
        $deviceTypes = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Module usage
        $moduleUsage = [];
        $modules = ['knowledge-hub','compliance-tools','monitoring','chatbot'];
        foreach($modules as $mod) {
            $stmt = $db->prepare("
                SELECT COUNT(*) FROM page_views
                WHERE page_path LIKE :module
                AND visited_at >= DATE_SUB(NOW(), INTERVAL $interval)
            ");
            $stmt->execute([':module' => "%/modules/$mod%"]);
            $moduleUsage[$mod] = (int)$stmt->fetchColumn();
        }

        echo json_encode([
            'success'         => true,
            'period'          => $period,
            'top_pages'       => $topPages,
            'unique_visitors' => $uniqueVisitors,
            'device_types'    => $deviceTypes,
            'module_usage'    => $moduleUsage,
        ]);

    } catch(Exception $e) {
        error_log('Platform Usage Error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success'=>false,'error'=>'Failed to fetch usage data']);
    }
}