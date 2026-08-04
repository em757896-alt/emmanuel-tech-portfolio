<?php
/**
 * admin/analytics/index.php
 * Analytics Dashboard
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

$pageTitle   = 'Analytics - Admin';
$currentPage = 'analytics';

$db = Database::getInstance()->getConnection();

$period    = sanitizeInput($_GET['period'] ?? '30days');
$startDate = sanitizeInput($_GET['start'] ?? date('Y-m-d', strtotime('-30 days')));
$endDate   = sanitizeInput($_GET['end'] ?? date('Y-m-d'));

$interval = match($period) {
    '7days'   => '7 DAY',
    '90days'  => '90 DAY',
    '12months'=> '12 MONTH',
    default   => '30 DAY',
};

// â”€â”€ Platform Usage Stats â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$stmt = $db->query("SELECT COUNT(*) FROM page_views WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)");
$totalViews = (int)$stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(DISTINCT ip_address) FROM page_views WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)");
$uniqueVisitors = (int)$stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)");
$newUsers = (int)$stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM monitoring_reports WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)");
$newReports = (int)$stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM chatbot_conversations WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)");
$chatbotQueries = (int)$stmt->fetchColumn();

$stmt = $db->query("SELECT COALESCE(SUM(download_count),0) FROM resources");
$downloads = (int)$stmt->fetchColumn();

// â”€â”€ Daily Views Trend â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$stmt = $db->query("
    SELECT DATE_FORMAT(created_at,'%d %b') as label,
           DATE(created_at) as day,
           COUNT(*) as views,
           COUNT(DISTINCT ip_address) as unique_v
    FROM page_views
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)
    GROUP BY day, label
    ORDER BY day ASC
");
$dailyViews = $stmt->fetchAll();

// â”€â”€ Module Usage â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$moduleStats = [];
$modules = [
    'knowledge-hub'    => 'Knowledge Hub',
    'compliance-tools' => 'Compliance Tools',
    'monitoring'       => 'Monitoring',
    'chatbot'          => 'AI Chatbot',
];
foreach($modules as $slug => $label) {
    $s = $db->prepare("SELECT COUNT(*) FROM page_views WHERE page_url LIKE :m AND created_at >= DATE_SUB(NOW(), INTERVAL $interval)");
    $s->execute([':m' => "%/modules/$slug%"]);
    $moduleStats[$label] = (int)$s->fetchColumn();
}

// â”€â”€ Reports by Type â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$stmt = $db->query("
    SELECT report_type, COUNT(*) as count
    FROM monitoring_reports
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)
    GROUP BY report_type
");
$reportsByType = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// â”€â”€ Reports by County (Top 10) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$stmt = $db->query("
    SELECT county, COUNT(*) as count
    FROM monitoring_reports
    WHERE county IS NOT NULL
      AND created_at >= DATE_SUB(NOW(), INTERVAL $interval)
    GROUP BY county
    ORDER BY count DESC
    LIMIT 10
");
$byCounty = $stmt->fetchAll();

// â”€â”€ Top Articles â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$stmt = $db->query("
    SELECT title_en as title, view_count, category
    FROM knowledge_articles
    WHERE is_published=1
    ORDER BY view_count DESC
    LIMIT 8
");
$topArticles = $stmt->fetchAll();

// â”€â”€ Chatbot Feedback Rate â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$stmt = $db->query("
    SELECT
        SUM(feedback='helpful') as positive,
        SUM(feedback='not_helpful') as negative,
        COUNT(*) as total
    FROM chatbot_conversations
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)
    AND feedback IS NOT NULL
");
$chatbotFeedback = $stmt->fetch();

// â”€â”€ Device Breakdown â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$stmt = $db->query("
    SELECT device_type, COUNT(*) as count
    FROM page_views
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)
    GROUP BY device_type
");
$deviceBreakdown = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
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
            <button class="topbar-menu-btn" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <div class="topbar-breadcrumb">
                <a href="../dashboard.php" class="bc-item"><i class="fas fa-home"></i></a>
                <span class="bc-sep">/</span>
                <span class="bc-item active">Analytics</span>
            </div>
        </div>
        <div class="topbar-right">
            <a href="../reports/export.php" class="topbar-btn" title="Export">
                <i class="fas fa-download"></i>
            </a>
        </div>
    </header>

    <div class="dashboard-content">

        <div class="page-header">
            <div>
                <h1>Platform Analytics</h1>
                <p>Usage trends, engagement metrics and civic space monitoring insights.</p>
            </div>
            <!-- Period Selector -->
            <div class="period-selector">
                <?php foreach(['7days'=>'7 Days','30days'=>'30 Days','90days'=>'90 Days','12months'=>'12 Months'] as $val=>$label): ?>
                <a href="?period=<?php echo $val; ?>"
                   class="period-btn <?php echo $period===$val?'active':''; ?>">
                    <?php echo $label; ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- KPI Row -->
        <div class="kpi-grid">
            <div class="kpi-card kpi-blue">
                <div class="kpi-icon"><i class="fas fa-eye"></i></div>
                <div class="kpi-body">
                    <span class="kpi-value"><?php echo number_format($totalViews); ?></span>
                    <span class="kpi-label">Page Views</span>
                    <span class="kpi-sub">Selected period</span>
                </div>
            </div>
            <div class="kpi-card kpi-purple">
                <div class="kpi-icon"><i class="fas fa-user"></i></div>
                <div class="kpi-body">
                    <span class="kpi-value"><?php echo number_format($uniqueVisitors); ?></span>
                    <span class="kpi-label">Unique Visitors</span>
                    <span class="kpi-sub">By IP estimate</span>
                </div>
            </div>
            <div class="kpi-card kpi-green">
                <div class="kpi-icon"><i class="fas fa-user-plus"></i></div>
                <div class="kpi-body">
                    <span class="kpi-value"><?php echo number_format($newUsers); ?></span>
                    <span class="kpi-label">New Registrations</span>
                    <span class="kpi-sub">Selected period</span>
                </div>
            </div>
            <div class="kpi-card kpi-orange">
                <div class="kpi-icon"><i class="fas fa-file-alt"></i></div>
                <div class="kpi-body">
                    <span class="kpi-value"><?php echo number_format($newReports); ?></span>
                    <span class="kpi-label">New Reports</span>
                    <span class="kpi-sub">Monitoring submissions</span>
                </div>
            </div>
            <div class="kpi-card kpi-teal">
                <div class="kpi-icon"><i class="fas fa-robot"></i></div>
                <div class="kpi-body">
                    <span class="kpi-value"><?php echo number_format($chatbotQueries); ?></span>
                    <span class="kpi-label">AI Queries</span>
                    <span class="kpi-sub">Chatbot interactions</span>
                </div>
            </div>
            <div class="kpi-card kpi-red">
                <div class="kpi-icon"><i class="fas fa-download"></i></div>
                <div class="kpi-body">
                    <span class="kpi-value"><?php echo number_format($downloads); ?></span>
                    <span class="kpi-label">Downloads</span>
                    <span class="kpi-sub">Resources & templates</span>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="charts-row">
            <div class="chart-card chart-wide">
                <div class="chart-header">
                    <div>
                        <h3>Daily Traffic</h3>
                        <p>Page views and unique visitors over time</p>
                    </div>
                </div>
                <div class="chart-body">
                    <canvas id="trafficChart" height="260"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-header">
                    <div>
                        <h3>Device Types</h3>
                        <p>Visitor device breakdown</p>
                    </div>
                </div>
                <div class="chart-body chart-doughnut-wrap">
                    <canvas id="deviceChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="charts-row">
            <div class="chart-card">
                <div class="chart-header">
                    <div>
                        <h3>Module Engagement</h3>
                        <p>Usage by platform section</p>
                    </div>
                </div>
                <div class="chart-body">
                    <canvas id="moduleChart" height="220"></canvas>
                </div>
            </div>
            <div class="chart-card chart-wide">
                <div class="chart-header">
                    <div>
                        <h3>Reports by County</h3>
                        <p>Top 10 submitting counties</p>
                    </div>
                    <a href="county.php" class="chart-link">Full Map <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="chart-body">
                    <canvas id="countyChart" height="220"></canvas>
                </div>
            </div>
        </div>

        <!-- Tables Row -->
        <div class="tables-row">
            <!-- Top Articles -->
            <div class="table-card table-wide">
                <div class="table-header">
                    <div>
                        <h3>Most Viewed Articles</h3>
                        <p>Knowledge hub engagement</p>
                    </div>
                    <a href="../knowledge/index.php" class="btn btn-sm btn-outline">Manage</a>
                </div>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Article Title</th>
                                <th>Category</th>
                                <th>Views</th>
                                <th>Bar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $maxViews = !empty($topArticles) ? max(array_column($topArticles,'view_count')) : 1;
                            foreach($topArticles as $i => $art):
                            $pct = $maxViews > 0 ? round(($art['view_count']/$maxViews)*100) : 0;
                            ?>
                            <tr>
                                <td><span class="rank-num"><?php echo $i+1; ?></span></td>
                                <td><?php echo htmlspecialchars($art['title']); ?></td>
                                <td><span class="cat-pill"><?php echo htmlspecialchars($art['category']??'â€”'); ?></span></td>
                                <td><strong><?php echo number_format($art['view_count']); ?></strong></td>
                                <td style="width:120px">
                                    <div class="mini-bar-wrap">
                                        <div class="mini-bar" style="width:<?php echo $pct; ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Chatbot Performance -->
            <div class="table-card">
                <div class="table-header">
                    <div>
                        <h3>Chatbot Performance</h3>
                        <p>AI assistant metrics</p>
                    </div>
                    <a href="../chatbot/index.php" class="btn btn-sm btn-outline">Details</a>
                </div>
                <div class="chatbot-stats" style="padding:16px 20px">
                    <?php
                    $total = (int)($chatbotFeedback['total'] ?? 0);
                    $pos   = (int)($chatbotFeedback['positive'] ?? 0);
                    $neg   = (int)($chatbotFeedback['negative'] ?? 0);
                    $posRate = $total > 0 ? round($pos/$total*100) : 0;
                    $negRate = $total > 0 ? round($neg/$total*100) : 0;
                    ?>
                    <div class="chatbot-stat">
                        <div class="cstat-labels">
                            <span><i class="fas fa-thumbs-up" style="color:#10b981"></i> Helpful responses</span>
                            <span><?php echo $pos; ?> (<?php echo $posRate; ?>%)</span>
                        </div>
                        <div class="cstat-bar">
                            <div class="cstat-fill cstat-positive" style="width:<?php echo $posRate; ?>%"></div>
                        </div>
                    </div>
                    <div class="chatbot-stat" style="margin-top:12px">
                        <div class="cstat-labels">
                            <span><i class="fas fa-thumbs-down" style="color:#ef4444"></i> Unhelpful responses</span>
                            <span><?php echo $neg; ?> (<?php echo $negRate; ?>%)</span>
                        </div>
                        <div class="cstat-bar">
                            <div class="cstat-fill cstat-negative" style="width:<?php echo $negRate; ?>%"></div>
                        </div>
                    </div>
                    <div class="chatbot-total" style="margin-top:16px">
                        <span>Total Feedback Received</span>
                        <strong><?php echo number_format($total); ?></strong>
                    </div>
                    <div class="chatbot-total">
                        <span>Total Queries (period)</span>
                        <strong><?php echo number_format($chatbotQueries); ?></strong>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>

<script>
Chart.defaults.font.family = 'Inter, sans-serif';
Chart.defaults.color = '#6b7280';

const COLORS = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#14b8a6','#f97316','#ec4899'];

// Traffic Chart
new Chart(document.getElementById('trafficChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($dailyViews,'label')); ?>,
        datasets: [
            {
                label: 'Page Views',
                data: <?php echo json_encode(array_map('intval', array_column($dailyViews,'views'))); ?>,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59,130,246,0.08)',
                fill: true, tension: 0.4, borderWidth: 2.5,
                pointRadius: 4, pointBackgroundColor: '#3b82f6',
            },
            {
                label: 'Unique Visitors',
                data: <?php echo json_encode(array_map('intval', array_column($dailyViews,'unique_v'))); ?>,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16,185,129,0.06)',
                fill: true, tension: 0.4, borderWidth: 2,
                pointRadius: 3, pointBackgroundColor: '#10b981', borderDash: [4,3],
            }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'top' }, tooltip: { backgroundColor: '#1a3c5e', padding: 10, cornerRadius: 8 } },
        scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true, grid: { color: '#f3f4f6' } }
        }
    }
});

// Device Chart
const deviceLabels = <?php echo json_encode(array_keys($deviceBreakdown)); ?>;
const deviceData   = <?php echo json_encode(array_values($deviceBreakdown)); ?>;
new Chart(document.getElementById('deviceChart'), {
    type: 'doughnut',
    data: {
        labels: deviceLabels.map(l => l.charAt(0).toUpperCase()+l.slice(1)),
        datasets: [{ data: deviceData, backgroundColor: COLORS, borderWidth: 3, borderColor: '#fff', hoverOffset: 6 }]
    },
    options: { cutout: '65%', plugins: { legend: { position: 'bottom' } } }
});

// Module Chart
new Chart(document.getElementById('moduleChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_keys($moduleStats)); ?>,
        datasets: [{
            label: 'Page Views',
            data: <?php echo json_encode(array_values($moduleStats)); ?>,
            backgroundColor: COLORS.map(c => c+'33'),
            borderColor: COLORS,
            borderWidth: 2,
            borderRadius: 8,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { x: { grid: { display: false } }, y: { beginAtZero: true, grid: { color: '#f3f4f6' } } }
    }
});

// County Chart
new Chart(document.getElementById('countyChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($byCounty,'county')); ?>,
        datasets: [{
            label: 'Reports',
            data: <?php echo json_encode(array_map('intval',array_column($byCounty,'count'))); ?>,
            backgroundColor: 'rgba(16,185,129,0.15)',
            borderColor: '#10b981',
            borderWidth: 2,
            borderRadius: 6,
        }]
    },
    options: {
        indexAxis: 'y', responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true, grid: { color: '#f3f4f6' } }, y: { grid: { display: false } } }
    }
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

.rank-num {
    width: 22px;
    height: 22px;
    background: #f3f4f6;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.72rem;
    font-weight: 700;
    color: #6b7280;
}

.cat-pill {
    background: #eff6ff;
    color: #1d4ed8;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.72rem;
    font-weight: 500;
}

.mini-bar-wrap {
    background: #f3f4f6;
    border-radius: 50px;
    height: 6px;
    overflow: hidden;
}

.mini-bar {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6, #10b981);
    border-radius: 50px;
}
</style>
</body>
</html>