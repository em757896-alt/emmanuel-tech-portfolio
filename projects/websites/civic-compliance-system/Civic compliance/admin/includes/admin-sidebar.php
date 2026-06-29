<?php
if (!isset($stats)) {
    $stats = [];
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT COUNT(*) FROM monitoring_reports WHERE status='submitted'");
        $stats['pending_reports'] = $stmt->fetchColumn();
        $stmt = $db->query("SELECT COUNT(*) FROM incident_reports WHERE urgency_level IN ('high','urgent')");
        $stats['critical_incidents'] = $stmt->fetchColumn();
    } catch (Exception $e) {
        $stats['pending_reports'] = 0;
        $stats['critical_incidents'] = 0;
    }
}
?>
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
            <a href="../dashboard.php" class="nav-item <?php echo $currentPage==='dashboard'?'active':''; ?>">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
            <a href="../monitoring/index.php" class="nav-item <?php echo $currentPage==='monitoring'?'active':''; ?>">
                <i class="fas fa-satellite-dish"></i>
                <span>Monitoring Reports</span>
                <?php if(($stats['pending_reports']??0) > 0): ?>
                <span class="nav-badge"><?php echo $stats['pending_reports']; ?></span>
                <?php endif; ?>
            </a>
            <a href="../incidents/index.php" class="nav-item <?php echo $currentPage==='incidents'?'active':''; ?>">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Incidents</span>
                <?php if(($stats['critical_incidents']??0) > 0): ?>
                <span class="nav-badge nav-badge-red"><?php echo $stats['critical_incidents']; ?></span>
                <?php endif; ?>
            </a>
        </div>

        <div class="nav-group">
            <span class="nav-label">Content</span>
            <a href="../knowledge/index.php" class="nav-item <?php echo $currentPage==='knowledge'?'active':''; ?>">
                <i class="fas fa-book-open"></i>
                <span>Knowledge Hub</span>
            </a>
            <a href="../resources/index.php" class="nav-item <?php echo $currentPage==='resources'?'active':''; ?>">
                <i class="fas fa-file-download"></i>
                <span>Resources</span>
            </a>
            <a href="../faqs/index.php" class="nav-item <?php echo $currentPage==='faqs'?'active':''; ?>">
                <i class="fas fa-question-circle"></i>
                <span>FAQs</span>
            </a>
            <a href="../chatbot/index.php" class="nav-item <?php echo $currentPage==='chatbot'?'active':''; ?>">
                <i class="fas fa-robot"></i>
                <span>Chatbot</span>
            </a>
        </div>

        <div class="nav-group">
            <span class="nav-label">Analytics</span>
            <a href="../analytics/index.php" class="nav-item <?php echo $currentPage==='analytics'?'active':''; ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Analytics</span>
            </a>
            <a href="../reports/export.php" class="nav-item <?php echo $currentPage==='export'?'active':''; ?>">
                <i class="fas fa-file-export"></i>
                <span>Export Data</span>
            </a>
        </div>

        <div class="nav-group">
            <span class="nav-label">System</span>
            <a href="../users/index.php" class="nav-item <?php echo $currentPage==='users'?'active':''; ?>">
                <i class="fas fa-users-cog"></i>
                <span>Users</span>
            </a>
            <a href="../settings/index.php" class="nav-item <?php echo $currentPage==='settings'?'active':''; ?>">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
            <a href="../../auth/logout.php" class="nav-item nav-item-danger">
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
