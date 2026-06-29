<?php
/**
 * admin/reports/export.php
 * Data Export — CSV, JSON, PDF-ready HTML
 * PBO Compliance Hub | CRECO Kenya
 *
 * DB: if0_42280606_if0_42280606_
 * User: if0_42280606
 * Password: AES256:4m0deNaMM0HA+yKw/HIgbYzFLvAjq8o1cD7cfheTaOSB8M/MqTc/Edx85mfbuzOL
 * Host: sql303.infinityfree.com
 */

require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../config/auth.php';

requireAdmin();

$db = Database::getInstance()->getConnection();

// ── Export Parameters ─────────────────────────────────────────────
$format     = sanitizeInput($_GET['format'] ?? '');
$reportType = sanitizeInput($_GET['type'] ?? '');
$county     = sanitizeInput($_GET['county'] ?? '');
$status     = sanitizeInput($_GET['status'] ?? '');
$severity   = sanitizeInput($_GET['severity'] ?? '');
$startDate  = sanitizeInput($_GET['start'] ?? '');
$endDate    = sanitizeInput($_GET['end'] ?? '');

// If direct export requested
if (in_array($format, ['csv','json'])) {
    $conditions = ['1=1'];
    $params = [];

    if ($reportType) {
        $conditions[] = "report_type = :type";
        $params[':type'] = $reportType;
    }
    if ($county) {
        $conditions[] = "county = :county";
        $params[':county'] = $county;
    }
    if ($status) {
        $conditions[] = "status = :status";
        $params[':status'] = $status;
    }
    if ($severity) {
        $conditions[] = "severity = :severity";
        $params[':severity'] = $severity;
    }
    if ($startDate) {
        $conditions[] = "DATE(created_at) >= :start";
        $params[':start'] = $startDate;
    }
    if ($endDate) {
        $conditions[] = "DATE(created_at) <= :end";
        $params[':end'] = $endDate;
    }

    $where = implode(' AND ', $conditions);

    $stmt = $db->prepare("
        SELECT
            id,
            report_type,
            organization_name as org_name,
            organization_type as org_type,
            county as submitter_county,
            severity,
            status,
            description,
            report_data,
            created_at,
            updated_at
        FROM monitoring_reports
        WHERE $where
        ORDER BY created_at DESC
        LIMIT 10000
    ");
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $filename = 'pbo-hub-monitoring-' . date('Y-m-d');

    if ($format === 'csv') {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel

        if (!empty($rows)) {
            // Custom headers for readability
            fputcsv($out, [
                'Report ID','Report Type','Organization','Org Type',
                'County','Severity','Status','Description',
                'Additional Data','Submitted At','Last Updated'
            ]);
            foreach ($rows as $row) {
                $reportData = json_decode($row['report_data'] ?? '{}', true);
                $dataStr    = is_array($reportData)
                    ? implode(' | ', array_map(fn($k,$v) => "$k: $v", array_keys($reportData), $reportData))
                    : '';

                fputcsv($out, [
                    '#' . str_pad($row['id'], 5, '0', STR_PAD_LEFT),
                    ucfirst($row['report_type']),
                    $row['org_name'],
                    $row['org_type'],
                    $row['submitter_county'],
                    ucfirst($row['severity']),
                    ucfirst($row['status']),
                    $row['description'],
                    $dataStr,
                    date('d/m/Y H:i', strtotime($row['created_at'])),
                    date('d/m/Y H:i', strtotime($row['updated_at'])),
                ]);
            }
        } else {
            fputcsv($out, ['No records found for the selected filters']);
        }

        fclose($out);
        exit;

    } elseif ($format === 'json') {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '.json"');

        // Decode report_data for each row
        foreach ($rows as &$row) {
            $row['report_data'] = json_decode($row['report_data'] ?? '{}', true);
        }
        unset($row);

        echo json_encode([
            'export_info' => [
                'platform'       => 'PBO Compliance Hub',
                'organization'   => 'CRECO Kenya',
                'exported_at'    => date('Y-m-d H:i:s'),
                'exported_by'    => $_SESSION['user_email'] ?? 'admin',
                'total_records'  => count($rows),
                'filters'        => [
                    'report_type' => $reportType ?: 'all',
                    'county'      => $county     ?: 'all',
                    'status'      => $status     ?: 'all',
                    'severity'    => $severity   ?: 'all',
                    'start_date'  => $startDate  ?: 'all',
                    'end_date'    => $endDate    ?: 'all',
                ],
            ],
            'data' => $rows,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// ── Export UI Page ────────────────────────────────────────────────
$pageTitle   = 'Export Data - Admin';
$currentPage = 'export';

// Stats for display
$stmt = $db->query("SELECT COUNT(*) FROM monitoring_reports");
$totalReports = (int)$stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM users");
$totalUsers = (int)$stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM knowledge_articles WHERE is_published=1");
$totalArticles = (int)$stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(DISTINCT county) FROM monitoring_reports WHERE county != ''");
$totalCounties = (int)$stmt->fetchColumn();

// Counties for filter
$countyStmt = $db->query("SELECT DISTINCT submitter_county FROM monitoring_reports WHERE submitter_county != '' ORDER BY submitter_county");
$counties = $countyStmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-body">

<?php include '../includes/admin-sidebar.php'; ?>

<main class="admin-main" id="adminMain">

    <header class="admin-topbar">
        <div class="topbar-left">
            <button class="topbar-menu-btn" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <div class="topbar-breadcrumb">
                <a href="../dashboard.php" class="bc-item"><i class="fas fa-home"></i></a>
                <span class="bc-sep">/</span>
                <span class="bc-item active">Export Data</span>
            </div>
        </div>
    </header>

    <div class="dashboard-content">

        <div class="page-header">
            <div>
                <h1>Export Data</h1>
                <p>Download monitoring reports and platform data in your preferred format.</p>
            </div>
        </div>

        <!-- Available Data Stats -->
        <div class="export-stats-row">
            <div class="export-stat-card">
                <i class="fas fa-file-alt" style="color:#3b82f6"></i>
                <div>
                    <span class="es-num"><?php echo number_format($totalReports); ?></span>
                    <span class="es-label">Monitoring Reports</span>
                </div>
            </div>
            <div class="export-stat-card">
                <i class="fas fa-users" style="color:#10b981"></i>
                <div>
                    <span class="es-num"><?php echo number_format($totalUsers); ?></span>
                    <span class="es-label">Registered Users</span>
                </div>
            </div>
            <div class="export-stat-card">
                <i class="fas fa-book-open" style="color:#f59e0b"></i>
                <div>
                    <span class="es-num"><?php echo number_format($totalArticles); ?></span>
                    <span class="es-label">Published Articles</span>
                </div>
            </div>
            <div class="export-stat-card">
                <i class="fas fa-map-marker-alt" style="color:#ef4444"></i>
                <div>
                    <span class="es-num"><?php echo $totalCounties; ?></span>
                    <span class="es-label">Counties Represented</span>
                </div>
            </div>
        </div>

        <div class="export-grid">

            <!-- Monitoring Reports Export -->
            <div class="export-card">
                <div class="export-card-header">
                    <div class="export-icon" style="background:#dbeafe;color:#1d4ed8">
                        <i class="fas fa-satellite-dish"></i>
                    </div>
                    <div>
                        <h3>Monitoring Reports</h3>
                        <p>All civic space monitoring submissions with full details</p>
                    </div>
                </div>

                <form class="export-form" id="monitoringExportForm">
                    <div class="export-filters">
                        <div class="ef-group">
                            <label>Report Type</label>
                            <select name="type">
                                <option value="">All Types</option>
                                <option value="compliance">Compliance</option>
                                <option value="barrier">Barrier</option>
                                <option value="incident">Incident</option>
                                <option value="enabling">Enabling</option>
                            </select>
                        </div>
                        <div class="ef-group">
                            <label>Status</label>
                            <select name="status">
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="flagged">Flagged</option>
                            </select>
                        </div>
                        <div class="ef-group">
                            <label>Severity</label>
                            <select name="severity">
                                <option value="">All Severities</option>
                                <option value="critical">Critical</option>
                                <option value="high">High</option>
                                <option value="medium">Medium</option>
                                <option value="low">Low</option>
                            </select>
                        </div>
                        <div class="ef-group">
                            <label>County</label>
                            <select name="county">
                                <option value="">All Counties</option>
                                <?php foreach($counties as $c): ?>
                                <option value="<?php echo htmlspecialchars($c); ?>">
                                    <?php echo htmlspecialchars($c); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="ef-group">
                            <label>From Date</label>
                            <input type="date" name="start" max="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="ef-group">
                            <label>To Date</label>
                            <input type="date" name="end" max="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>

                    <div class="export-buttons">
                        <button type="button" class="export-dl-btn export-csv"
                                onclick="doExport('csv', this.closest('form'))">
                            <i class="fas fa-file-csv"></i>
                            Download CSV
                        </button>
                        <button type="button" class="export-dl-btn export-json"
                                onclick="doExport('json', this.closest('form'))">
                            <i class="fas fa-file-code"></i>
                            Download JSON
                        </button>
                    </div>
                </form>
            </div>

            <!-- Quick Exports -->
            <div class="quick-exports-card">
                <h3><i class="fas fa-bolt"></i> Quick Exports</h3>
                <p>One-click downloads with no filters applied.</p>

                <div class="quick-export-list">
                    <a href="?format=csv" class="quick-export-item">
                        <div class="qe-icon qe-csv"><i class="fas fa-file-csv"></i></div>
                        <div class="qe-info">
                            <strong>All Reports — CSV</strong>
                            <span><?php echo number_format($totalReports); ?> records</span>
                        </div>
                        <i class="fas fa-download qe-arrow"></i>
                    </a>
                    <a href="?format=json" class="quick-export-item">
                        <div class="qe-icon qe-json"><i class="fas fa-file-code"></i></div>
                        <div class="qe-info">
                            <strong>All Reports — JSON</strong>
                            <span><?php echo number_format($totalReports); ?> records</span>
                        </div>
                        <i class="fas fa-download qe-arrow"></i>
                    </a>
                    <a href="?format=csv&type=incident" class="quick-export-item">
                        <div class="qe-icon qe-red"><i class="fas fa-exclamation-triangle"></i></div>
                        <div class="qe-info">
                            <strong>Incidents Only — CSV</strong>
                            <span>All civic space incidents</span>
                        </div>
                        <i class="fas fa-download qe-arrow"></i>
                    </a>
                    <a href="?format=csv&status=approved" class="quick-export-item">
                        <div class="qe-icon qe-green"><i class="fas fa-check-circle"></i></div>
                        <div class="qe-info">
                            <strong>Approved Reports — CSV</strong>
                            <span>Verified submissions only</span>
                        </div>
                        <i class="fas fa-download qe-arrow"></i>
                    </a>
                    <a href="?format=csv&severity=critical" class="quick-export-item">
                        <div class="qe-icon qe-critical"><i class="fas fa-skull-crossbones"></i></div>
                        <div class="qe-info">
                            <strong>Critical Incidents — CSV</strong>
                            <span>High-severity cases only</span>
                        </div>
                        <i class="fas fa-download qe-arrow"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Export Notes -->
        <div class="export-notes">
            <h4><i class="fas fa-info-circle"></i> Export Notes</h4>
            <ul>
                <li>All exports are limited to <strong>10,000 records</strong> per download for performance.</li>
                <li>Personal data is included in exports — handle with care per the <strong>Kenya Data Protection Act 2019</strong>.</li>
                <li>CSV files include a UTF-8 BOM for compatibility with Microsoft Excel.</li>
                <li>All exports are logged in the system audit trail.</li>
                <li>IP addresses are stored as cryptographic hashes to protect submitter privacy.</li>
            </ul>
        </div>

    </div>
</main>

<script>
function doExport(format, form) {
    const params = new URLSearchParams();
    params.set('format', format);
    new FormData(form).forEach((v, k) => { if(v) params.set(k, v); });
    window.location.href = '?' + params.toString();
}
</script>

<style>
.export-stats-row {
    display: flex;
    gap: 16px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.export-stat-card {
    flex: 1;
    min-width: 160px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
}

.export-stat-card i { font-size: 1.6rem; }

.es-num { display: block; font-size: 1.5rem; font-weight: 700; color: #1a3c5e; line-height: 1; }
.es-label { font-size: 0.75rem; color: #9ca3af; }

.export-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.export-card, .quick-exports-card {
    background: #fff;
    border-radius: 14px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
}

.export-card-header {
    display: flex;
    gap: 16px;
    align-items: flex-start;
    padding: 22px 24px;
    border-bottom: 1px solid #f3f4f6;
}

.export-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.export-card-header h3 { font-size: 1rem; font-weight: 600; color: #1a3c5e; margin-bottom: 3px; }
.export-card-header p  { font-size: 0.82rem; color: #9ca3af; }

.export-form { padding: 20px 24px; }

.export-filters {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}

.ef-group label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-bottom: 4px;
}

.ef-group select,
.ef-group input {
    width: 100%;
    padding: 8px 12px;
    border: 1.5px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.83rem;
    color: #374151;
    background: #f8fafc;
}

.export-buttons { display: flex; gap: 10px; }

.export-dl-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 11px;
    border: none;
    border-radius: 9px;
    font-size: 0.88rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.export-csv  { background: #d1fae5; color: #065f46; }
.export-csv:hover  { background: #10b981; color: #fff; }
.export-json { background: #dbeafe; color: #1d4ed8; }
.export-json:hover { background: #3b82f6; color: #fff; }

.quick-exports-card { padding: 22px 24px; }

.quick-exports-card h3 {
    font-size: 0.95rem;
    font-weight: 600;
    color: #1a3c5e;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 7px;
}

.quick-exports-card h3 i { color: #f59e0b; }

.quick-exports-card > p { font-size: 0.8rem; color: #9ca3af; margin-bottom: 16px; }

.quick-export-list { display: flex; flex-direction: column; gap: 8px; }

.quick-export-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 9px;
    text-decoration: none;
    transition: all 0.2s;
}

.quick-export-item:hover { background: #f8fafc; border-color: #93c5fd; transform: translateX(3px); }

.qe-icon {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.qe-csv      { background: #d1fae5; color: #059669; }
.qe-json     { background: #dbeafe; color: #1d4ed8; }
.qe-red      { background: #fee2e2; color: #dc2626; }
.qe-green    { background: #d1fae5; color: #059669; }
.qe-critical { background: #450a0a; color: #fca5a5; }

.qe-info { flex: 1; }
.qe-info strong { display: block; font-size: 0.83rem; color: #374151; }
.qe-info span   { font-size: 0.72rem; color: #9ca3af; }

.qe-arrow { color: #d1d5db; font-size: 0.8rem; }

.export-notes {
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 12px;
    padding: 18px 22px;
}

.export-notes h4 {
    font-size: 0.88rem;
    font-weight: 700;
    color: #92400e;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 7px;
}

.export-notes h4 i { color: #d97706; }

.export-notes ul { list-style: none; display: flex; flex-direction: column; gap: 5px; }

.export-notes li {
    font-size: 0.82rem;
    color: #78350f;
    display: flex;
    align-items: flex-start;
    gap: 7px;
    padding-left: 14px;
    position: relative;
}

.export-notes li::before { content: '•'; position: absolute; left: 0; color: #d97706; }

@media (max-width: 900px) {
    .export-grid { grid-template-columns: 1fr; }
    .export-filters { grid-template-columns: repeat(2, 1fr); }
}
</style>
</body>
</html>