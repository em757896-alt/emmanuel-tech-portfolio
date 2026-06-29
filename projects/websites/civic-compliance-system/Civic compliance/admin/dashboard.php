<?php
/**
 * admin/dashboard.php
 * Administrator Dashboard - PBO Compliance Hub
 * CRECO Kenya
 *
 * DB: if0_42280606_if0_42280606_
 * User: if0_42280606
 * Password: (Your vPanel Password)
 * Host: sql303.infinityfree.com
 */

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/auth.php';

// Enforce admin authentication
requireAdmin();

$pageTitle = 'Admin Dashboard - PBO Compliance Hub';
$currentPage = 'dashboard';

$db = Database::getInstance()->getConnection();

// ── Summary Statistics ──────────────────────────────────────────
$stats = [];

// Total users
$stmt = $db->query("SELECT COUNT(*) FROM users");
$stats['total_users'] = $stmt->fetchColumn();

// New users this month
$stmt = $db->query("SELECT COUNT(*) FROM users WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())");
$stats['new_users_month'] = $stmt->fetchColumn();

// Total monitoring reports
$stmt = $db->query("SELECT COUNT(*) FROM monitoring_reports");
$stats['total_reports'] = $stmt->fetchColumn();

// Pending moderation
$stmt = $db->query("SELECT COUNT(*) FROM monitoring_reports WHERE status='submitted'");
$stats['pending_reports'] = $stmt->fetchColumn();

// Incidents (high/critical)
$stmt = $db->query("SELECT COUNT(*) FROM incident_reports WHERE urgency_level IN ('high','urgent')");
$stats['critical_incidents'] = $stmt->fetchColumn();

// Knowledge hub articles
$stmt = $db->query("SELECT COUNT(*) FROM knowledge_articles WHERE is_published = 1");
$stats['published_articles'] = $stmt->fetchColumn();

// Chatbot queries today
$stmt = $db->query("SELECT COUNT(*) FROM chatbot_conversations WHERE DATE(created_at)=CURDATE()");
$stats['chatbot_today'] = $stmt->fetchColumn();

// Total downloads
$stmt = $db->query("SELECT COALESCE(SUM(download_count),0) FROM resources");
$stats['total_downloads'] = $stmt->fetchColumn();

// ── Reports by Type (for Chart) ────────────────────────────────
$stmt = $db->query("
    SELECT report_type, COUNT(*) as count
    FROM monitoring_reports
    GROUP BY report_type
");
$reportsByType = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// ── Reports by County (Top 10) ─────────────────────────────────
$stmt = $db->query("
    SELECT county, COUNT(*) as count
    FROM monitoring_reports
    WHERE county IS NOT NULL AND county != ''
    GROUP BY county
    ORDER BY count DESC
    LIMIT 10
");
$reportsByCounty = $stmt->fetchAll();

// ── Monthly Reports Trend (last 6 months) ─────────────────────
$stmt = $db->query("
    SELECT
        DATE_FORMAT(created_at, '%b %Y') as month_label,
        DATE_FORMAT(created_at, '%Y-%m') as month_key,
        COUNT(*) as count
    FROM monitoring_reports
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY month_key, month_label
    ORDER BY month_key ASC
");
$monthlyTrend = $stmt->fetchAll();

// ── Recent Reports ─────────────────────────────────────────────
$stmt = $db->query("
    SELECT id, report_type, organization_name as org_name, county as submitter_county, severity, status, created_at
    FROM monitoring_reports
    ORDER BY created_at DESC
    LIMIT 10
");
$recentReports = $stmt->fetchAll();

// ── Recent Users ───────────────────────────────────────────────
$stmt = $db->query("
    SELECT id, full_name as name, email, role, created_at
    FROM users
    ORDER BY created_at DESC
    LIMIT 5
");
$recentUsers = $stmt->fetchAll();

// ── Platform Usage (last 7 days page views) ────────────────────
$stmt = $db->query("
    SELECT
        DATE_FORMAT(created_at, '%a') as day_label,
        DATE(created_at) as day_key,
        COUNT(*) as views
    FROM page_views
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY day_key, day_label
    ORDER BY day_key ASC
");
$weeklyViews = $stmt->fetchAll();

// ── Severity Distribution ──────────────────────────────────────
$stmt = $db->query("
    SELECT severity, COUNT(*) as count
    FROM monitoring_reports
    WHERE severity IS NOT NULL
    GROUP BY severity
");
$severityDist = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// ── Chatbot Feedback Stats ─────────────────────────────────────
$stmt = $db->query("
    SELECT
        SUM(CASE WHEN feedback='helpful' THEN 1 ELSE 0 END) as positive,
        SUM(CASE WHEN feedback='not_helpful' THEN 1 ELSE 0 END) as negative,
        COUNT(*) as total
    FROM chatbot_conversations
    WHERE feedback IS NOT NULL
");
$chatbotFeedback = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="admin-body">

<!-- ── Sidebar ── -->
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
        <div class="brand-logo">
            <i class="fas fa-balance-scale"></i>
        </div>
        <div class="brand-text">
            <span class="brand-name">PBO Hub</span>
            <span class="brand-sub">Admin Panel</span>
        </div>
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-group">
            <span class="nav-label">Main</span>
            <a href="dashboard.php" class="nav-item active">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
            <a href="monitoring/index.php" class="nav-item">
                <i class="fas fa-satellite-dish"></i>
                <span>Monitoring Reports</span>
                <?php if($stats['pending_reports'] > 0): ?>
                <span class="nav-badge"><?php echo $stats['pending_reports']; ?></span>
                <?php endif; ?>
            </a>
            <a href="incidents/index.php" class="nav-item">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Incidents</span>
                <?php if($stats['critical_incidents'] > 0): ?>
                <span class="nav-badge nav-badge-red"><?php echo $stats['critical_incidents']; ?></span>
                <?php endif; ?>
            </a>
        </div>

        <div class="nav-group">
            <span class="nav-label">Content</span>
            <a href="knowledge/index.php" class="nav-item">
                <i class="fas fa-book-open"></i>
                <span>Knowledge Hub</span>
            </a>
            <a href="resources/index.php" class="nav-item">
                <i class="fas fa-file-download"></i>
                <span>Resources</span>
            </a>
            <a href="faqs/index.php" class="nav-item">
                <i class="fas fa-question-circle"></i>
                <span>FAQs</span>
            </a>
            <a href="chatbot/index.php" class="nav-item">
                <i class="fas fa-robot"></i>
                <span>Chatbot</span>
            </a>
        </div>

        <div class="nav-group">
            <span class="nav-label">Analytics</span>
            <a href="analytics/index.php" class="nav-item">
                <i class="fas fa-chart-bar"></i>
                <span>Analytics</span>
            </a>
            <a href="reports/export.php" class="nav-item">
                <i class="fas fa-file-export"></i>
                <span>Export Data</span>
            </a>
        </div>

        <div class="nav-group">
            <span class="nav-label">System</span>
            <a href="users/index.php" class="nav-item">
                <i class="fas fa-users-cog"></i>
                <span>Users</span>
            </a>
            <a href="settings/index.php" class="nav-item">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
            <a href="../auth/logout.php" class="nav-item nav-item-danger">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </nav>

    <div class="sidebar-footer">
        <div class="admin-user">
            <div class="user-avatar">
                <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)); ?>
            </div>
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></span>
                <span class="user-role">Administrator</span>
            </div>
        </div>
    </div>
</aside>

<!-- ── Main Content ── -->
<main class="admin-main" id="adminMain">

    <!-- Top Bar -->
    <header class="admin-topbar">
        <div class="topbar-left">
            <button class="topbar-menu-btn" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <div class="topbar-breadcrumb">
                <span class="bc-item"><i class="fas fa-home"></i></span>
                <span class="bc-sep">/</span>
                <span class="bc-item active">Dashboard</span>
            </div>
        </div>
        <div class="topbar-right">
            <div class="topbar-search">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Quick search...">
            </div>
            <button class="topbar-btn" title="Notifications" onclick="toggleNotifications()">
                <i class="fas fa-bell"></i>
                <?php if($stats['pending_reports'] > 0): ?>
                <span class="btn-badge"><?php echo $stats['pending_reports']; ?></span>
                <?php endif; ?>
            </button>
            <button class="topbar-btn" title="Refresh Data" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt" id="refreshIcon"></i>
            </button>
            <a href="../index.php" class="topbar-btn" title="View Site" target="_blank">
                <i class="fas fa-external-link-alt"></i>
            </a>
        </div>
    </header>

    <!-- Dashboard Content -->
    <div class="dashboard-content">

        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1>Dashboard Overview</h1>
                <p>Welcome back, <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></strong>
                   — <?php echo date('l, d F Y'); ?></p>
            </div>
            <div class="header-actions">
                <a href="reports/export.php" class="btn btn-outline">
                    <i class="fas fa-download"></i> Export Report
                </a>
                <a href="monitoring/index.php?status=pending" class="btn btn-primary">
                    <i class="fas fa-tasks"></i> Review Pending
                    <?php if($stats['pending_reports'] > 0): ?>
                    <span class="btn-count"><?php echo $stats['pending_reports']; ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>

        <!-- Alert: Critical Incidents -->
        <?php if($stats['critical_incidents'] > 0): ?>
        <div class="alert-banner alert-critical">
            <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="alert-text">
                <strong><?php echo $stats['critical_incidents']; ?> critical incident(s) require immediate attention.</strong>
                <p>High-severity civic space violations have been reported and need urgent review.</p>
            </div>
            <a href="incidents/index.php?severity=critical" class="alert-btn">Review Now</a>
        </div>
        <?php endif; ?>

        <!-- KPI Cards -->
        <div class="kpi-grid">
            <div class="kpi-card kpi-blue">
                <div class="kpi-icon"><i class="fas fa-users"></i></div>
                <div class="kpi-body">
                    <span class="kpi-value"><?php echo number_format($stats['total_users']); ?></span>
                    <span class="kpi-label">Registered Users</span>
                    <span class="kpi-sub">
                        <i class="fas fa-arrow-up"></i>
                        +<?php echo $stats['new_users_month']; ?> this month
                    </span>
                </div>
            </div>

            <div class="kpi-card kpi-purple">
                <div class="kpi-icon"><i class="fas fa-file-alt"></i></div>
                <div class="kpi-body">
                    <span class="kpi-value"><?php echo number_format($stats['total_reports']); ?></span>
                    <span class="kpi-label">Monitoring Reports</span>
                    <span class="kpi-sub kpi-warning">
                        <i class="fas fa-clock"></i>
                        <?php echo $stats['pending_reports']; ?> pending review
                    </span>
                </div>
            </div>

            <div class="kpi-card kpi-red">
                <div class="kpi-icon"><i class="fas fa-exclamation-circle"></i></div>
                <div class="kpi-body">
                    <span class="kpi-value"><?php echo $stats['critical_incidents']; ?></span>
                    <span class="kpi-label">Critical Incidents</span>
                    <span class="kpi-sub">High &amp; critical severity</span>
                </div>
            </div>

            <div class="kpi-card kpi-green">
                <div class="kpi-icon"><i class="fas fa-book-open"></i></div>
                <div class="kpi-body">
                    <span class="kpi-value"><?php echo $stats['published_articles']; ?></span>
                    <span class="kpi-label">Published Articles</span>
                    <span class="kpi-sub">Knowledge hub resources</span>
                </div>
            </div>

            <div class="kpi-card kpi-teal">
                <div class="kpi-icon"><i class="fas fa-robot"></i></div>
                <div class="kpi-body">
                    <span class="kpi-value"><?php echo number_format($stats['chatbot_today']); ?></span>
                    <span class="kpi-label">Chatbot Queries Today</span>
                    <span class="kpi-sub">
                        <?php
                        $posRate = $stats['chatbot_today'] > 0 && $chatbotFeedback['total'] > 0
                            ? round(($chatbotFeedback['positive'] / $chatbotFeedback['total']) * 100)
                            : 0;
                        ?>
                        <?php echo $posRate; ?>% positive feedback
                    </span>
                </div>
            </div>

            <div class="kpi-card kpi-orange">
                <div class="kpi-icon"><i class="fas fa-download"></i></div>
                <div class="kpi-body">
                    <span class="kpi-value"><?php echo number_format($stats['total_downloads']); ?></span>
                    <span class="kpi-label">Total Downloads</span>
                    <span class="kpi-sub">Templates &amp; resources</span>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="charts-row">

            <!-- Monthly Trend Chart -->
            <div class="chart-card chart-wide">
                <div class="chart-header">
                    <div>
                        <h3>Reports Trend</h3>
                        <p>Monthly submissions over the last 6 months</p>
                    </div>
                    <div class="chart-actions">
                        <button class="chart-btn active" onclick="switchTrend('reports', this)">Reports</button>
                        <button class="chart-btn" onclick="switchTrend('users', this)">Users</button>
                        <button class="chart-btn" onclick="switchTrend('views', this)">Page Views</button>
                    </div>
                </div>
                <div class="chart-body">
                    <canvas id="trendChart" height="280"></canvas>
                </div>
            </div>

            <!-- Reports by Type -->
            <div class="chart-card">
                <div class="chart-header">
                    <div>
                        <h3>Reports by Type</h3>
                        <p>Distribution of report categories</p>
                    </div>
                </div>
                <div class="chart-body chart-doughnut-wrap">
                    <canvas id="typeChart" height="220"></canvas>
                    <div class="doughnut-legend" id="typeLegend"></div>
                </div>
            </div>

        </div>

        <!-- Charts Row 2 -->
        <div class="charts-row">

            <!-- Weekly Page Views -->
            <div class="chart-card">
                <div class="chart-header">
                    <div>
                        <h3>Platform Activity</h3>
                        <p>Page views — last 7 days</p>
                    </div>
                </div>
                <div class="chart-body">
                    <canvas id="weeklyChart" height="200"></canvas>
                </div>
            </div>

            <!-- Reports by County -->
            <div class="chart-card chart-wide">
                <div class="chart-header">
                    <div>
                        <h3>Reports by County</h3>
                        <p>Top 10 counties by submission volume</p>
                    </div>
                    <a href="analytics/county.php" class="chart-link">
                        View Map <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="chart-body">
                    <canvas id="countyChart" height="200"></canvas>
                </div>
            </div>

        </div>

        <!-- Tables Row -->
        <div class="tables-row">

            <!-- Recent Reports -->
            <div class="table-card table-wide">
                <div class="table-header">
                    <div>
                        <h3>Recent Reports</h3>
                        <p>Latest submissions requiring review</p>
                    </div>
                    <a href="monitoring/index.php" class="btn btn-sm btn-outline">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>Type</th>
                                <th>Organization</th>
                                <th>County</th>
                                <th>Severity</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($recentReports)): ?>
                            <tr>
                                <td colspan="8" class="table-empty">
                                    <i class="fas fa-inbox"></i>
                                    <span>No reports yet</span>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach($recentReports as $report): ?>
                            <tr>
                                <td>
                                    <span class="report-id">#<?php echo str_pad($report['id'], 5, '0', STR_PAD_LEFT); ?></span>
                                </td>
                                <td>
                                    <?php
                                    $typeConfig = [
                                        'compliance' => ['label'=>'Compliance','class'=>'type-blue','icon'=>'fa-clipboard-list'],
                                        'barrier'    => ['label'=>'Barrier','class'=>'type-orange','icon'=>'fa-road-barrier'],
                                        'incident'   => ['label'=>'Incident','class'=>'type-red','icon'=>'fa-exclamation-triangle'],
                                        'enabling'   => ['label'=>'Enabling','class'=>'type-green','icon'=>'fa-thumbs-up'],
                                    ];
                                    $tc = $typeConfig[$report['report_type']] ?? ['label'=>ucfirst($report['report_type']),'class'=>'type-gray','icon'=>'fa-file'];
                                    ?>
                                    <span class="type-badge <?php echo $tc['class']; ?>">
                                        <i class="fas <?php echo $tc['icon']; ?>"></i>
                                        <?php echo $tc['label']; ?>
                                    </span>
                                </td>
                                <td class="org-name"><?php echo htmlspecialchars($report['org_name']); ?></td>
                                <td><?php echo htmlspecialchars($report['submitter_county']); ?></td>
                                <td>
                                    <?php
                                    $sevClass = [
                                        'critical'=>'sev-critical','high'=>'sev-high',
                                        'medium'=>'sev-medium','low'=>'sev-low'
                                    ][$report['severity']] ?? 'sev-low';
                                    ?>
                                    <span class="severity-badge <?php echo $sevClass; ?>">
                                        <?php echo ucfirst($report['severity']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $statClass = [
                                        'pending'=>'stat-pending','approved'=>'stat-approved',
                                        'rejected'=>'stat-rejected','flagged'=>'stat-flagged'
                                    ][$report['status']] ?? 'stat-pending';
                                    ?>
                                    <span class="status-pill <?php echo $statClass; ?>">
                                        <?php echo ucfirst($report['status']); ?>
                                    </span>
                                </td>
                                <td class="date-cell">
                                    <?php echo date('d M Y', strtotime($report['created_at'])); ?>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <a href="monitoring/view.php?id=<?php echo $report['id']; ?>"
                                           class="action-btn" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if($report['status'] === 'pending'): ?>
                                        <button class="action-btn action-approve"
                                                onclick="approveReport(<?php echo $report['id']; ?>)"
                                                title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="action-btn action-reject"
                                                onclick="rejectReport(<?php echo $report['id']; ?>)"
                                                title="Reject">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Users + Chatbot Stats -->
            <div class="side-tables">

                <!-- Recent Users -->
                <div class="table-card">
                    <div class="table-header">
                        <div>
                            <h3>Recent Users</h3>
                            <p>Latest registrations</p>
                        </div>
                        <a href="users/index.php" class="btn btn-sm btn-outline">View All</a>
                    </div>
                    <div class="user-list">
                        <?php foreach($recentUsers as $user): ?>
                        <div class="user-item">
                            <div class="user-avatar-sm">
                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                            </div>
                            <div class="user-details">
                                <span class="user-name-sm"><?php echo htmlspecialchars($user['name']); ?></span>
                                <span class="user-email-sm"><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                            <div class="user-meta">
                                <span class="role-badge role-<?php echo $user['role']; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                                <span class="user-date">
                                    <?php echo date('d M', strtotime($user['created_at'])); ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if(empty($recentUsers)): ?>
                        <div class="empty-state-sm">
                            <i class="fas fa-users"></i> No users yet
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Chatbot Performance -->
                <div class="table-card">
                    <div class="table-header">
                        <div>
                            <h3>Chatbot Performance</h3>
                            <p>Feedback summary</p>
                        </div>
                        <a href="chatbot/index.php" class="btn btn-sm btn-outline">Details</a>
                    </div>
                    <div class="chatbot-stats">
                        <div class="chatbot-stat">
                            <div class="cstat-bar">
                                <div class="cstat-fill cstat-positive"
                                     style="width:<?php echo $posRate; ?>%"></div>
                            </div>
                            <div class="cstat-labels">
                                <span><i class="fas fa-thumbs-up" style="color:#10b981"></i> Positive</span>
                                <span><?php echo $chatbotFeedback['positive'] ?? 0; ?></span>
                            </div>
                        </div>
                        <div class="chatbot-stat">
                            <div class="cstat-bar">
                                <?php $negRate = $chatbotFeedback['total'] > 0
                                    ? round(($chatbotFeedback['negative'] / $chatbotFeedback['total']) * 100) : 0; ?>
                                <div class="cstat-fill cstat-negative"
                                     style="width:<?php echo $negRate; ?>%"></div>
                            </div>
                            <div class="cstat-labels">
                                <span><i class="fas fa-thumbs-down" style="color:#ef4444"></i> Negative</span>
                                <span><?php echo $chatbotFeedback['negative'] ?? 0; ?></span>
                            </div>
                        </div>
                        <div class="chatbot-total">
                            <span>Total Reviewed</span>
                            <strong><?php echo $chatbotFeedback['total'] ?? 0; ?></strong>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Quick Export -->
        <div class="export-bar">
            <span><i class="fas fa-file-export"></i> Quick Export:</span>
            <a href="reports/export.php?type=all&format=csv" class="export-btn">
                <i class="fas fa-file-csv"></i> CSV
            </a>
            <a href="reports/export.php?type=all&format=excel" class="export-btn">
                <i class="fas fa-file-excel"></i> Excel
            </a>
            <a href="reports/export.php?type=all&format=pdf" class="export-btn">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
            <a href="reports/export.php?type=incidents&format=csv" class="export-btn export-btn-red">
                <i class="fas fa-exclamation-triangle"></i> Incidents CSV
            </a>
        </div>

    </div><!-- /.dashboard-content -->
</main>

<!-- Notifications Drawer -->
<div class="notifications-drawer" id="notificationsDrawer">
    <div class="drawer-header">
        <h3>Notifications</h3>
        <button onclick="toggleNotifications()"><i class="fas fa-times"></i></button>
    </div>
    <div class="drawer-body">
        <?php if($stats['pending_reports'] > 0): ?>
        <div class="notif-item notif-warning">
            <i class="fas fa-clock"></i>
            <div>
                <strong><?php echo $stats['pending_reports']; ?> reports pending review</strong>
                <span>Monitoring submissions awaiting moderation</span>
                <a href="monitoring/index.php?status=pending">Review now →</a>
            </div>
        </div>
        <?php endif; ?>
        <?php if($stats['critical_incidents'] > 0): ?>
        <div class="notif-item notif-critical">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong><?php echo $stats['critical_incidents']; ?> critical incidents</strong>
                <span>High-severity civic space violations reported</span>
                <a href="incidents/index.php?severity=critical">View incidents →</a>
            </div>
        </div>
        <?php endif; ?>
        <div class="notif-item notif-info">
            <i class="fas fa-info-circle"></i>
            <div>
                <strong>Platform is running normally</strong>
                <span>All systems operational — <?php echo date('H:i'); ?></span>
            </div>
        </div>
    </div>
</div>
<div class="drawer-overlay" id="drawerOverlay" onclick="toggleNotifications()"></div>

<script>
// ── Chart Data from PHP ─────────────────────────────────────────
const monthlyData = {
    labels: <?php echo json_encode(array_column($monthlyTrend, 'month_label')); ?>,
    data:   <?php echo json_encode(array_map('intval', array_column($monthlyTrend, 'count'))); ?>
};

const typeData = {
    labels: <?php echo json_encode(array_map('ucfirst', array_keys($reportsByType))); ?>,
    data:   <?php echo json_encode(array_values($reportsByType)); ?>
};

const countyData = {
    labels: <?php echo json_encode(array_column($reportsByCounty, 'county')); ?>,
    data:   <?php echo json_encode(array_map('intval', array_column($reportsByCounty, 'count'))); ?>
};

const weeklyData = {
    labels: <?php echo json_encode(array_column($weeklyViews, 'day_label')); ?>,
    data:   <?php echo json_encode(array_map('intval', array_column($weeklyViews, 'views'))); ?>
};

const CHART_COLORS = {
    blue:   '#3b82f6',
    purple: '#8b5cf6',
    green:  '#10b981',
    orange: '#f59e0b',
    red:    '#ef4444',
    teal:   '#14b8a6',
    indigo: '#6366f1',
    pink:   '#ec4899',
};

Chart.defaults.font.family = 'Inter, sans-serif';
Chart.defaults.color = '#6b7280';

// ── Trend Chart ─────────────────────────────────────────────────
const trendCtx = document.getElementById('trendChart').getContext('2d');
const trendChart = new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: monthlyData.labels,
        datasets: [{
            label: 'Reports',
            data: monthlyData.data,
            borderColor: CHART_COLORS.blue,
            backgroundColor: 'rgba(59,130,246,0.08)',
            borderWidth: 2.5,
            tension: 0.4,
            pointBackgroundColor: CHART_COLORS.blue,
            pointRadius: 5,
            pointHoverRadius: 7,
            fill: true,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#1a3c5e',
                titleColor: '#fff',
                bodyColor: '#94a3b8',
                padding: 12,
                cornerRadius: 8,
            }
        },
        scales: {
            x: { grid: { display: false } },
            y: {
                beginAtZero: true,
                grid: { color: '#f3f4f6' },
                ticks: { stepSize: 1 }
            }
        }
    }
});

function switchTrend(type, btn) {
    document.querySelectorAll('.chart-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    // In production: fetch from API
    // For demo, just toggle sample data
    const datasets = {
        reports: { data: monthlyData.data, color: CHART_COLORS.blue },
        users:   { data: monthlyData.data.map(v => Math.round(v * 1.5)), color: CHART_COLORS.purple },
        views:   { data: monthlyData.data.map(v => v * 12), color: CHART_COLORS.green },
    };
    trendChart.data.datasets[0].data = datasets[type].data;
    trendChart.data.datasets[0].borderColor = datasets[type].color;
    trendChart.data.datasets[0].backgroundColor = datasets[type].color + '15';
    trendChart.data.datasets[0].pointBackgroundColor = datasets[type].color;
    trendChart.update();
}

// ── Type Doughnut Chart ─────────────────────────────────────────
const typeCtx = document.getElementById('typeChart').getContext('2d');
const typeColors = [CHART_COLORS.blue, CHART_COLORS.orange, CHART_COLORS.red, CHART_COLORS.green];

new Chart(typeCtx, {
    type: 'doughnut',
    data: {
        labels: typeData.labels,
        datasets: [{
            data: typeData.data,
            backgroundColor: typeColors,
            borderWidth: 3,
            borderColor: '#fff',
            hoverOffset: 6,
        }]
    },
    options: {
        responsive: true,
        cutout: '65%',
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#1a3c5e',
                callbacks: {
                    label: ctx => ` ${ctx.label}: ${ctx.parsed} (${Math.round(ctx.parsed / typeData.data.reduce((a,b)=>a+b,0)*100)}%)`
                }
            }
        }
    }
});

// Build custom legend
const legend = document.getElementById('typeLegend');
if(legend) {
    typeData.labels.forEach((label, i) => {
        const total = typeData.data.reduce((a,b)=>a+b,0);
        const pct   = total > 0 ? Math.round(typeData.data[i]/total*100) : 0;
        legend.innerHTML += `
            <div class="legend-item">
                <span class="legend-dot" style="background:${typeColors[i]}"></span>
                <span class="legend-label">${label}</span>
                <span class="legend-value">${typeData.data[i]} (${pct}%)</span>
            </div>`;
    });
}

// ── Weekly Chart ────────────────────────────────────────────────
const weekCtx = document.getElementById('weeklyChart').getContext('2d');
new Chart(weekCtx, {
    type: 'bar',
    data: {
        labels: weeklyData.labels,
        datasets: [{
            label: 'Page Views',
            data: weeklyData.data,
            backgroundColor: weeklyData.data.map(() => 'rgba(99,102,241,0.15)'),
            borderColor: CHART_COLORS.indigo,
            borderWidth: 2,
            borderRadius: 8,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true, grid: { color: '#f3f4f6' } }
        }
    }
});

// ── County Bar Chart ────────────────────────────────────────────
const countyCtx = document.getElementById('countyChart').getContext('2d');
new Chart(countyCtx, {
    type: 'bar',
    data: {
        labels: countyData.labels,
        datasets: [{
            label: 'Reports',
            data: countyData.data,
            backgroundColor: 'rgba(16,185,129,0.15)',
            borderColor: CHART_COLORS.green,
            borderWidth: 2,
            borderRadius: 6,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: {
            x: { beginAtZero: true, grid: { color: '#f3f4f6' } },
            y: { grid: { display: false } }
        }
    }
});

// ── Sidebar Toggle ──────────────────────────────────────────────
function toggleSidebar() {
    document.getElementById('adminSidebar').classList.toggle('collapsed');
    document.getElementById('adminMain').classList.toggle('expanded');
}

// ── Notifications ────────────────────────────────────────────────
function toggleNotifications() {
    document.getElementById('notificationsDrawer').classList.toggle('open');
    document.getElementById('drawerOverlay').classList.toggle('active');
}

// ── Report Actions ───────────────────────────────────────────────
async function approveReport(id) {
    if(!confirm('Approve report #' + id + '?')) return;
    const resp = await fetch('../api/admin-moderation.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ action: 'approve', report_id: id,
            csrf_token: '<?php echo generateCSRFTokenValue(); ?>' })
    });
    const data = await resp.json();
    if(data.success) { showToast('Report approved successfully', 'success'); setTimeout(()=>location.reload(),1500); }
    else showToast(data.error || 'Failed to approve', 'error');
}

async function rejectReport(id) {
    const reason = prompt('Reason for rejection (optional):');
    const resp = await fetch('../api/admin-moderation.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ action: 'reject', report_id: id, reason: reason,
            csrf_token: '<?php echo generateCSRFTokenValue(); ?>' })
    });
    const data = await resp.json();
    if(data.success) { showToast('Report rejected', 'info'); setTimeout(()=>location.reload(),1500); }
    else showToast(data.error || 'Failed to reject', 'error');
}

// ── Refresh ──────────────────────────────────────────────────────
function refreshDashboard() {
    const icon = document.getElementById('refreshIcon');
    icon.classList.add('fa-spin');
    setTimeout(() => { icon.classList.remove('fa-spin'); location.reload(); }, 800);
}

// ── Toast Notification ───────────────────────────────────────────
function showToast(msg, type = 'info') {
    const t = document.createElement('div');
    t.className = `admin-toast toast-${type}`;
    t.innerHTML = `<i class="fas fa-${type==='success'?'check-circle':type==='error'?'times-circle':'info-circle'}"></i> ${msg}`;
    document.body.appendChild(t);
    requestAnimationFrame(() => t.classList.add('show'));
    setTimeout(() => { t.classList.remove('show'); setTimeout(()=>t.remove(), 300); }, 3500);
}
</script>

</body>
</html>