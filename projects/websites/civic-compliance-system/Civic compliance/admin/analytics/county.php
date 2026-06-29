<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../config/auth.php';

requireAdmin();

$pageTitle   = 'County Analytics - Admin';
$currentPage = 'analytics';

$db = Database::getInstance()->getConnection();

$period = sanitizeInput($_GET['period'] ?? 'all');

$intervalClause = match($period) {
    '30days'  => "AND r.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
    '90days'  => "AND r.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)",
    default   => "",
};

$stmt = $db->query("
    SELECT
        r.county,
        COUNT(*) as total_reports,
        SUM(r.status='verified') as verified,
        SUM(r.status='submitted') as pending,
        SUM(r.severity IN ('high','critical')) as critical,
        ROUND(AVG(CASE WHEN r.severity='high' OR r.severity='critical' THEN 1 WHEN r.severity='medium' THEN 0.5 ELSE 0 END), 2) as severity_score
    FROM monitoring_reports r
    WHERE r.county IS NOT NULL AND r.county != '' $intervalClause
    GROUP BY r.county
    ORDER BY total_reports DESC
");
$countyData = $stmt->fetchAll();

$totalReports = array_sum(array_column($countyData, 'total_reports'));
$totalVerified = array_sum(array_column($countyData, 'verified'));

$stmt = $db->query("
    SELECT
        r.county,
        r.report_type,
        COUNT(*) as count
    FROM monitoring_reports r
    WHERE r.county IS NOT NULL AND r.county != '' $intervalClause
    GROUP BY r.county, r.report_type
    ORDER BY r.county, r.report_type
");
$typeBreakdown = $stmt->fetchAll();

$countyTypes = [];
foreach ($typeBreakdown as $row) {
    $countyTypes[$row['county']][$row['report_type']] = $row['count'];
}

$typeLabels = ['compliance'=>'Compliance','barrier'=>'Barrier','incident'=>'Incident','enabling'=>'Enabling'];

$stmt = $db->query("
    SELECT r.county, r.severity, COUNT(*) as count
    FROM monitoring_reports r
    WHERE r.county IS NOT NULL AND r.county != '' $intervalClause
    GROUP BY r.county, r.severity
    ORDER BY r.county, FIELD(r.severity,'critical','high','medium','low')
");
$severityBreakdown = $stmt->fetchAll();

$countySeverity = [];
foreach ($severityBreakdown as $row) {
    $countySeverity[$row['county']][$row['severity']] = $row['count'];
}
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="admin-body">

<?php include '../includes/admin-sidebar.php'; ?>

<main class="admin-main" id="adminMain">

    <header class="admin-topbar">
        <div class="topbar-left">
            <button class="topbar-menu-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
            <div class="topbar-breadcrumb">
                <a href="../dashboard.php" class="bc-item"><i class="fas fa-home"></i></a>
                <span class="bc-sep">/</span>
                <a href="index.php" class="bc-item">Analytics</a>
                <span class="bc-sep">/</span>
                <span class="bc-item active">County Map</span>
            </div>
        </div>
    </header>

    <div class="dashboard-content">

        <div class="page-header">
            <div>
                <h1>County-Level Analytics</h1>
                <p>Detailed breakdown of monitoring reports by county.</p>
            </div>
            <div class="period-selector">
                <?php foreach(['all'=>'All Time','30days'=>'30 Days','90days'=>'90 Days'] as $val=>$label): ?>
                <a href="?period=<?php echo $val; ?>" class="period-btn <?php echo $period===$val?'active':''; ?>"><?php echo $label; ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="kpi-grid">
            <div class="kpi-card kpi-blue">
                <div class="kpi-icon"><i class="fas fa-map-marker-alt"></i></div>
                <div class="kpi-body">
                    <span class="kpi-value"><?php echo count($countyData); ?></span>
                    <span class="kpi-label">Counties Reporting</span>
                </div>
            </div>
            <div class="kpi-card kpi-green">
                <div class="kpi-icon"><i class="fas fa-file-alt"></i></div>
                <div class="kpi-body">
                    <span class="kpi-value"><?php echo number_format($totalReports); ?></span>
                    <span class="kpi-label">Total Reports</span>
                </div>
            </div>
            <div class="kpi-card kpi-purple">
                <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
                <div class="kpi-body">
                    <span class="kpi-value"><?php echo number_format($totalVerified); ?></span>
                    <span class="kpi-label">Verified Reports</span>
                </div>
            </div>
            <div class="kpi-card kpi-orange">
                <div class="kpi-icon"><i class="fas fa-chart-line"></i></div>
                <div class="kpi-body">
                    <span class="kpi-value"><?php echo $totalReports>0?round($totalVerified/$totalReports*100):0; ?>%</span>
                    <span class="kpi-label">Verification Rate</span>
                </div>
            </div>
        </div>

        <div class="charts-row">
            <div class="chart-card chart-wide">
                <div class="chart-header"><div><h3>Reports by County</h3><p>Total submissions per county</p></div></div>
                <div class="chart-body">
                    <canvas id="countyChart" height="300"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-header"><div><h3>Severity Distribution</h3><p>Across all counties</p></div></div>
                <div class="chart-body chart-doughnut-wrap">
                    <canvas id="severityChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header">
                <div><h3>County Detail Table</h3><p>All counties with report breakdowns</p></div>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>County</th>
                            <th>Total Reports</th>
                            <th>Verified</th>
                            <th>Pending</th>
                            <th>Critical</th>
                            <th>Score</th>
                            <th>By Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($countyData)): ?>
                        <tr><td colspan="7" class="table-empty"><i class="fas fa-inbox"></i><span>No county data available.</span></td></tr>
                        <?php else: ?>
                        <?php foreach($countyData as $row): ?>
                        <tr>
                            <td><strong><i class="fas fa-map-marker-alt" style="color:#ef4444;font-size:0.75rem;margin-right:5px"></i><?php echo htmlspecialchars($row['county']); ?></strong></td>
                            <td><strong><?php echo number_format($row['total_reports']); ?></strong></td>
                            <td><span class="severity-badge sev-low"><?php echo $row['verified']; ?></span></td>
                            <td><span class="severity-badge sev-medium"><?php echo $row['pending']; ?></span></td>
                            <td><?php if($row['critical']>0): ?><span class="severity-badge sev-critical"><?php echo $row['critical']; ?></span><?php else: ?><span style="color:#d1d5db">—</span><?php endif; ?></td>
                            <td>
                                <div class="mini-bar-wrap" style="width:80px">
                                    <div class="mini-bar" style="width:<?php echo min(100,$row['severity_score']*100); ?>%;background:<?php echo $row['severity_score']>0.5?'#ef4444':($row['severity_score']>0.2?'#f59e0b':'#10b981'); ?>"></div>
                                </div>
                            </td>
                            <td>
                                <div style="display:flex;gap:3px;flex-wrap:wrap">
                                    <?php foreach($countyTypes[$row['county']]??[] as $type=>$count): ?>
                                    <span class="cat-pill"><?php echo $typeLabels[$type]??ucfirst($type); ?>: <?php echo $count; ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<script>
Chart.defaults.font.family = 'Inter, sans-serif';
Chart.defaults.color = '#6b7280';

const counties = <?php echo json_encode(array_column($countyData,'county')); ?>;
const totals   = <?php echo json_encode(array_map('intval',array_column($countyData,'total_reports'))); ?>;
const verified = <?php echo json_encode(array_map('intval',array_column($countyData,'verified'))); ?>;
const colors   = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#14b8a6','#f97316','#ec4899','#6366f1','#84cc16'];

new Chart(document.getElementById('countyChart'), {
    type: 'bar',
    data: {
        labels: counties,
        datasets: [
            {
                label: 'Total Reports',
                data: totals,
                backgroundColor: 'rgba(59,130,246,0.12)',
                borderColor: '#3b82f6',
                borderWidth: 2,
                borderRadius: 4,
            },
            {
                label: 'Verified',
                data: verified,
                backgroundColor: 'rgba(16,185,129,0.12)',
                borderColor: '#10b981',
                borderWidth: 2,
                borderRadius: 4,
            }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'top' } },
        scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true, grid: { color: '#f3f4f6' } }
        }
    }
});

const sevData = <?php 
$sevLabels = ['critical','high','medium','low'];
$sevCounts = array_fill_keys($sevLabels, 0);
foreach ($countyData as $row) {
    $ctySev = $countySeverity[$row['county']]??[];
    foreach ($ctySev as $s=>$c) { if(isset($sevCounts[$s])) $sevCounts[$s] += $c; }
}
echo json_encode(array_values($sevCounts));
?>;
new Chart(document.getElementById('severityChart'), {
    type: 'doughnut',
    data: {
        labels: ['Critical','High','Medium','Low'],
        datasets: [{ data: sevData, backgroundColor: ['#ef4444','#f97316','#f59e0b','#10b981'], borderWidth: 3, borderColor: '#fff' }]
    },
    options: { cutout: '65%', plugins: { legend: { position: 'bottom' } } }
});
</script>

<style>
.period-selector { display: flex; gap: 4px; }
.period-btn {
    padding: 7px 14px;
    border: 1px solid #e5e7eb;
    border-radius: 7px;
    font-size: 0.8rem;
    font-weight: 500;
    color: #6b7280;
    text-decoration: none;
    background: #fff;
    transition: all 0.2s;
}
.period-btn.active, .period-btn:hover { background: #1a3c5e; color: #fff; border-color: #1a3c5e; }
.cat-pill {
    background: #eff6ff;
    color: #1d4ed8;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 500;
    white-space: nowrap;
}
.mini-bar-wrap {
    background: #f3f4f6;
    border-radius: 50px;
    height: 6px;
    overflow: hidden;
    display: inline-block;
}
.mini-bar { height: 100%; border-radius: 50px; }
</style>
</body>
</html>
