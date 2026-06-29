<?php
/**
 * admin/monitoring/index.php
 * Admin Monitoring Reports Management
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

$pageTitle   = 'Monitoring Reports - Admin';
$currentPage = 'monitoring';

$db = Database::getInstance()->getConnection();

// ── Filters ──────────────────────────────────────────────────────
$filterStatus  = sanitizeInput($_GET['status'] ?? '');
$filterType    = sanitizeInput($_GET['type'] ?? '');
$filterCounty  = sanitizeInput($_GET['county'] ?? '');
$filterSev     = sanitizeInput($_GET['severity'] ?? '');
$search        = sanitizeInput($_GET['q'] ?? '');
$page          = max(1, intval($_GET['page'] ?? 1));
$perPage       = 20;
$offset        = ($page - 1) * $perPage;

// ── Build Query ───────────────────────────────────────────────────
$conditions = ['1=1'];
$params     = [];

if ($filterStatus) {
    $conditions[] = "status = :status";
    $params[':status'] = $filterStatus;
}
if ($filterType) {
    $conditions[] = "report_type = :type";
    $params[':type'] = $filterType;
}
if ($filterCounty) {
    $conditions[] = "county = :county";
    $params[':county'] = $filterCounty;
}
if ($filterSev) {
    $conditions[] = "severity = :severity";
    $params[':severity'] = $filterSev;
}
if ($search) {
    $conditions[] = "(organization_name LIKE :q OR description LIKE :q2)";
    $params[':q']  = "%$search%";
    $params[':q2'] = "%$search%";
}

$where = implode(' AND ', $conditions);

// Count
$countStmt = $db->prepare("SELECT COUNT(*) FROM monitoring_reports WHERE $where");
$countStmt->execute($params);
$totalReports = (int)$countStmt->fetchColumn();
$totalPages   = ceil($totalReports / $perPage);

// Fetch
$params[':limit']  = $perPage;
$params[':offset'] = $offset;

$stmt = $db->prepare("
    SELECT
        r.id, r.report_type, r.organization_name as org_name, r.organization_type as org_type,
        r.county as submitter_county, r.severity, r.status,
        r.created_at, r.moderated_at as reviewed_at,
        SUBSTRING(r.description, 1, 120) as preview,
        u.full_name as reviewer_name,
        (SELECT COUNT(*) FROM monitoring_attachments WHERE report_id=r.id) as attachments
    FROM monitoring_reports r
    LEFT JOIN users u ON u.id = r.moderated_by
    WHERE $where
    ORDER BY
        FIELD(r.status,'submitted','under_review','verified','rejected','resolved'),
        FIELD(r.severity,'critical','high','medium','low'),
        r.created_at DESC
    LIMIT :limit OFFSET :offset
");
$stmt->execute($params);
$reports = $stmt->fetchAll();

// ── Summary Counts ────────────────────────────────────────────────
$summaryStmt = $db->query("
    SELECT
        COUNT(*) as total,
        SUM(status='submitted')  as pending,
        SUM(status='verified') as approved,
        SUM(status='rejected') as rejected,
        SUM(status='under_review')  as flagged,
        SUM(severity IN ('high','critical')) as critical
    FROM monitoring_reports
");
$summary = $summaryStmt->fetch();

// ── Counties for filter ───────────────────────────────────────────
$countyStmt = $db->query("
    SELECT DISTINCT county
    FROM monitoring_reports
    WHERE county IS NOT NULL AND county != ''
    ORDER BY county
");
$availableCounties = $countyStmt->fetchAll(PDO::FETCH_COLUMN);
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

    <!-- Topbar -->
    <header class="admin-topbar">
        <div class="topbar-left">
            <button class="topbar-menu-btn" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <div class="topbar-breadcrumb">
                <a href="../dashboard.php" class="bc-item"><i class="fas fa-home"></i></a>
                <span class="bc-sep">/</span>
                <span class="bc-item active">Monitoring Reports</span>
            </div>
        </div>
        <div class="topbar-right">
            <a href="../../api/admin-dashboard.php?action=export&format=csv" class="topbar-btn" title="Export CSV">
                <i class="fas fa-file-csv"></i>
            </a>
            <a href="../../api/admin-dashboard.php?action=export&format=json" class="topbar-btn" title="Export JSON">
                <i class="fas fa-file-export"></i>
            </a>
        </div>
    </header>

    <div class="dashboard-content">

        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1>Monitoring Reports</h1>
                <p>Review, moderate, and manage all civic space monitoring submissions.</p>
            </div>
            <div class="header-actions">
                <a href="../../api/admin-dashboard.php?action=export&format=csv" class="btn btn-outline">
                    <i class="fas fa-download"></i> Export CSV
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="summary-strip">
            <a href="?" class="strip-card <?php echo !$filterStatus?'strip-active':''; ?>">
                <span class="strip-num"><?php echo number_format($summary['total']); ?></span>
                <span class="strip-label">Total</span>
            </a>
            <a href="?status=pending" class="strip-card strip-warning <?php echo $filterStatus==='pending'?'strip-active':''; ?>">
                <span class="strip-num"><?php echo $summary['pending']; ?></span>
                <span class="strip-label">Pending</span>
            </a>
            <a href="?status=approved" class="strip-card strip-success <?php echo $filterStatus==='approved'?'strip-active':''; ?>">
                <span class="strip-num"><?php echo $summary['approved']; ?></span>
                <span class="strip-label">Approved</span>
            </a>
            <a href="?status=flagged" class="strip-card strip-purple <?php echo $filterStatus==='flagged'?'strip-active':''; ?>">
                <span class="strip-num"><?php echo $summary['flagged']; ?></span>
                <span class="strip-label">Flagged</span>
            </a>
            <a href="?status=rejected" class="strip-card strip-danger <?php echo $filterStatus==='rejected'?'strip-active':''; ?>">
                <span class="strip-num"><?php echo $summary['rejected']; ?></span>
                <span class="strip-label">Rejected</span>
            </a>
            <a href="?type=incident&severity=critical" class="strip-card strip-critical">
                <span class="strip-num"><?php echo $summary['critical']; ?></span>
                <span class="strip-label">Critical</span>
            </a>
        </div>

        <!-- Filters Bar -->
        <div class="filters-bar">
            <form method="GET" class="filters-form" id="filtersForm">
                <div class="filter-search">
                    <i class="fas fa-search"></i>
                    <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>"
                           placeholder="Search by organization or description...">
                    <?php if($search): ?>
                    <a href="?" class="filter-clear-search"><i class="fas fa-times"></i></a>
                    <?php endif; ?>
                </div>

                <select name="status" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <?php foreach(['pending','approved','rejected','flagged','archived'] as $s): ?>
                    <option value="<?php echo $s; ?>" <?php echo $filterStatus===$s?'selected':''; ?>>
                        <?php echo ucfirst($s); ?>
                    </option>
                    <?php endforeach; ?>
                </select>

                <select name="type" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <?php foreach(['compliance','barrier','incident','enabling'] as $t): ?>
                    <option value="<?php echo $t; ?>" <?php echo $filterType===$t?'selected':''; ?>>
                        <?php echo ucfirst($t); ?>
                    </option>
                    <?php endforeach; ?>
                </select>

                <select name="severity" onchange="this.form.submit()">
                    <option value="">All Severities</option>
                    <?php foreach(['critical','high','medium','low'] as $sv): ?>
                    <option value="<?php echo $sv; ?>" <?php echo $filterSev===$sv?'selected':''; ?>>
                        <?php echo ucfirst($sv); ?>
                    </option>
                    <?php endforeach; ?>
                </select>

                <select name="county" onchange="this.form.submit()">
                    <option value="">All Counties</option>
                    <?php foreach($availableCounties as $c): ?>
                    <option value="<?php echo htmlspecialchars($c); ?>"
                            <?php echo $filterCounty===$c?'selected':''; ?>>
                        <?php echo htmlspecialchars($c); ?>
                    </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-filter"></i> Filter
                </button>

                <?php if($filterStatus||$filterType||$filterCounty||$filterSev||$search): ?>
                <a href="?" class="btn btn-outline btn-sm">
                    <i class="fas fa-times"></i> Clear
                </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Results Info -->
        <div class="results-info-bar">
            <span>
                Showing <strong><?php echo number_format(($offset)+1); ?></strong>–
                <strong><?php echo number_format(min($offset+$perPage,$totalReports)); ?></strong>
                of <strong><?php echo number_format($totalReports); ?></strong> reports
            </span>
            <?php if($filterStatus||$filterType||$search): ?>
            <span class="filter-active-indicator">
                <i class="fas fa-filter"></i> Filters active
            </span>
            <?php endif; ?>
        </div>

        <!-- Reports Table -->
        <div class="table-card">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                            </th>
                            <th>#ID</th>
                            <th>Type</th>
                            <th>Organization</th>
                            <th>County</th>
                            <th>Severity</th>
                            <th>Status</th>
                            <th>Files</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($reports)): ?>
                        <tr>
                            <td colspan="10" class="table-empty">
                                <i class="fas fa-inbox"></i>
                                <span>No reports found<?php echo ($search||$filterStatus) ? ' matching your filters' : ''; ?>.</span>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php
                        $typeConfig = [
                            'compliance' => ['label'=>'Compliance','class'=>'type-blue','icon'=>'fa-clipboard-list'],
                            'barrier'    => ['label'=>'Barrier','class'=>'type-orange','icon'=>'fa-road-barrier'],
                            'incident'   => ['label'=>'Incident','class'=>'type-red','icon'=>'fa-exclamation-triangle'],
                            'enabling'   => ['label'=>'Enabling','class'=>'type-green','icon'=>'fa-thumbs-up'],
                        ];
                        ?>
                        <?php foreach($reports as $report):
                            $tc       = $typeConfig[$report['report_type']] ?? ['label'=>ucfirst($report['report_type']),'class'=>'type-gray','icon'=>'fa-file'];
                            $sevClass = ['critical'=>'sev-critical','high'=>'sev-high','medium'=>'sev-medium','low'=>'sev-low'][$report['severity']] ?? 'sev-low';
                            $statClass = ['pending'=>'stat-pending','approved'=>'stat-approved','rejected'=>'stat-rejected','flagged'=>'stat-flagged','archived'=>'stat-archived'][$report['status']] ?? 'stat-pending';
                        ?>
                        <tr id="row-<?php echo $report['id']; ?>">
                            <td>
                                <input type="checkbox" class="row-select" value="<?php echo $report['id']; ?>">
                            </td>
                            <td>
                                <a href="view.php?id=<?php echo $report['id']; ?>" class="report-id">
                                    #<?php echo str_pad($report['id'],5,'0',STR_PAD_LEFT); ?>
                                </a>
                            </td>
                            <td>
                                <span class="type-badge <?php echo $tc['class']; ?>">
                                    <i class="fas <?php echo $tc['icon']; ?>"></i>
                                    <?php echo $tc['label']; ?>
                                </span>
                            </td>
                            <td>
                                <div class="org-cell">
                                    <span class="org-name"><?php echo htmlspecialchars($report['org_name']); ?></span>
                                    <?php if($report['preview']): ?>
                                    <span class="org-preview"><?php echo htmlspecialchars($report['preview']); ?>...</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="county-cell">
                                    <i class="fas fa-map-marker-alt" style="color:#ef4444;font-size:0.7rem"></i>
                                    <?php echo htmlspecialchars($report['submitter_county'] ?? '—'); ?>
                                </span>
                            </td>
                            <td>
                                <span class="severity-badge <?php echo $sevClass; ?>">
                                    <?php echo ucfirst($report['severity']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-pill <?php echo $statClass; ?>">
                                    <?php echo ucfirst($report['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if($report['attachments'] > 0): ?>
                                <span class="attachment-count">
                                    <i class="fas fa-paperclip"></i> <?php echo $report['attachments']; ?>
                                </span>
                                <?php else: ?>
                                <span style="color:#d1d5db">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="date-cell">
                                <?php echo date('d M Y', strtotime($report['created_at'])); ?>
                                <span class="date-time"><?php echo date('H:i', strtotime($report['created_at'])); ?></span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="view.php?id=<?php echo $report['id']; ?>"
                                       class="action-btn" title="View full report">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if($report['status'] === 'pending' || $report['status'] === 'flagged'): ?>
                                    <button class="action-btn action-approve"
                                            onclick="quickAction('approve', <?php echo $report['id']; ?>)"
                                            title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="action-btn action-reject"
                                            onclick="quickAction('reject', <?php echo $report['id']; ?>)"
                                            title="Reject">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <?php endif; ?>
                                    <?php if($report['status'] !== 'flagged'): ?>
                                    <button class="action-btn"
                                            onclick="quickAction('flag', <?php echo $report['id']; ?>)"
                                            title="Flag for review" style="color:#f59e0b">
                                        <i class="fas fa-flag"></i>
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

            <!-- Bulk Actions -->
            <?php if(!empty($reports)): ?>
            <div class="bulk-actions" id="bulkActions" style="display:none">
                <span class="bulk-count"><span id="selectedCount">0</span> selected</span>
                <button class="bulk-btn bulk-approve" onclick="bulkAction('approve')">
                    <i class="fas fa-check"></i> Approve Selected
                </button>
                <button class="bulk-btn bulk-reject" onclick="bulkAction('reject')">
                    <i class="fas fa-times"></i> Reject Selected
                </button>
                <button class="bulk-btn bulk-flag" onclick="bulkAction('flag')">
                    <i class="fas fa-flag"></i> Flag Selected
                </button>
            </div>
            <?php endif; ?>

        </div><!-- /.table-card -->

        <!-- Pagination -->
        <?php if($totalPages > 1): ?>
        <div class="admin-pagination">
            <?php if($page > 1): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET,['page'=>$page-1])); ?>"
               class="page-btn"><i class="fas fa-chevron-left"></i></a>
            <?php endif; ?>

            <?php for($p = max(1,$page-2); $p <= min($totalPages,$page+2); $p++): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET,['page'=>$p])); ?>"
               class="page-num <?php echo $p===$page?'active':''; ?>">
                <?php echo $p; ?>
            </a>
            <?php endfor; ?>

            <?php if($page < $totalPages): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET,['page'=>$page+1])); ?>"
               class="page-btn"><i class="fas fa-chevron-right"></i></a>
            <?php endif; ?>

            <span class="page-info">
                Page <?php echo $page; ?> of <?php echo $totalPages; ?>
            </span>
        </div>
        <?php endif; ?>

    </div><!-- /.dashboard-content -->
</main>

<script>
const CSRF = '<?php echo generateCSRFTokenValue(); ?>';

// ── Row Selection ────────────────────────────────────────────────
function toggleSelectAll(master) {
    document.querySelectorAll('.row-select').forEach(cb => cb.checked = master.checked);
    updateBulkBar();
}

document.querySelectorAll('.row-select').forEach(cb => {
    cb.addEventListener('change', updateBulkBar);
});

function updateBulkBar() {
    const selected = document.querySelectorAll('.row-select:checked').length;
    const bar      = document.getElementById('bulkActions');
    const countEl  = document.getElementById('selectedCount');
    if(bar) bar.style.display = selected > 0 ? 'flex' : 'none';
    if(countEl) countEl.textContent = selected;
}

// ── Quick Action (single row) ────────────────────────────────────
async function quickAction(action, id) {
    if(action === 'reject' && !confirm('Reject report #' + id + '?')) return;
    if(action === 'approve' && !confirm('Approve report #' + id + '?')) return;

    const resp = await fetch('../../api/admin-moderation.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ action, report_id: id, csrf_token: CSRF })
    });
    const data = await resp.json();

    if(data.success) {
        showToast(data.message || 'Action completed', 'success');
        setTimeout(() => location.reload(), 1200);
    } else {
        showToast(data.error || 'Action failed', 'error');
    }
}

// ── Bulk Action ──────────────────────────────────────────────────
async function bulkAction(action) {
    const ids = [...document.querySelectorAll('.row-select:checked')].map(cb => parseInt(cb.value));
    if(ids.length === 0) return;
    if(!confirm(`${action.charAt(0).toUpperCase()+action.slice(1)} ${ids.length} report(s)?`)) return;

    const resp = await fetch('../../api/admin-moderation.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ action: 'bulk_' + action, report_ids: ids, csrf_token: CSRF })
    });
    const data = await resp.json();

    if(data.success) {
        showToast(data.message || 'Bulk action completed', 'success');
        setTimeout(() => location.reload(), 1200);
    } else {
        showToast(data.error || 'Bulk action failed', 'error');
    }
}

function showToast(msg, type='info') {
    const t = document.createElement('div');
    t.className = `admin-toast toast-${type}`;
    t.innerHTML = `<i class="fas fa-${type==='success'?'check-circle':'times-circle'}"></i> ${msg}`;
    document.body.appendChild(t);
    requestAnimationFrame(() => t.classList.add('show'));
    setTimeout(() => { t.classList.remove('show'); setTimeout(()=>t.remove(),300); }, 3200);
}
</script>

<style>
.summary-strip {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.strip-card {
    flex: 1;
    min-width: 100px;
    background: #fff;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    padding: 14px 18px;
    text-align: center;
    text-decoration: none;
    transition: all 0.2s;
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.strip-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.strip-active { border-color: #0d6efd; background: #eff6ff; }

.strip-num { font-size: 1.6rem; font-weight: 700; color: #1a3c5e; line-height: 1; }
.strip-label { font-size: 0.72rem; color: #9ca3af; font-weight: 500; }

.strip-warning .strip-num { color: #d97706; }
.strip-success .strip-num { color: #059669; }
.strip-purple .strip-num  { color: #7c3aed; }
.strip-danger .strip-num  { color: #dc2626; }
.strip-critical { border-color: #fca5a5; background: #fff5f5; }
.strip-critical .strip-num { color: #dc2626; }

.filters-bar {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 14px;
}

.filters-form {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.filter-search {
    flex: 2;
    min-width: 200px;
    position: relative;
    display: flex;
    align-items: center;
}

.filter-search i { position: absolute; left: 12px; color: #9ca3af; font-size: 0.85rem; pointer-events: none; }

.filter-search input {
    width: 100%;
    padding: 9px 36px 9px 36px;
    border: 1.5px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.85rem;
    color: #374151;
    background: #f8fafc;
}

.filter-search input:focus { outline: none; border-color: #3b82f6; background: #fff; }

.filter-clear-search {
    position: absolute;
    right: 10px;
    color: #9ca3af;
    font-size: 0.8rem;
    text-decoration: none;
}

.filters-form select {
    padding: 9px 14px;
    border: 1.5px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.83rem;
    color: #374151;
    background: #f8fafc;
    cursor: pointer;
    min-width: 130px;
}

.results-info-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.82rem;
    color: #6b7280;
    margin-bottom: 12px;
}

.filter-active-indicator {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #0d6efd;
    font-weight: 500;
}

.org-cell { display: flex; flex-direction: column; gap: 2px; }
.org-name { font-size: 0.85rem; font-weight: 500; color: #374151; }
.org-preview { font-size: 0.72rem; color: #9ca3af; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.county-cell { display: flex; align-items: center; gap: 5px; font-size: 0.83rem; color: #374151; }

.attachment-count {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 0.75rem;
    color: #6b7280;
    background: #f3f4f6;
    padding: 2px 8px;
    border-radius: 4px;
}

.date-cell { display: flex; flex-direction: column; gap: 1px; font-size: 0.8rem; color: #374151; }
.date-time { font-size: 0.7rem; color: #9ca3af; }

.bulk-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 20px;
    background: #eff6ff;
    border-top: 1px solid #bfdbfe;
    flex-wrap: wrap;
}

.bulk-count { font-size: 0.85rem; font-weight: 600; color: #1d4ed8; }

.bulk-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border: none;
    border-radius: 7px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.bulk-approve { background: #d1fae5; color: #065f46; }
.bulk-approve:hover { background: #10b981; color: #fff; }
.bulk-reject  { background: #fee2e2; color: #991b1b; }
.bulk-reject:hover  { background: #ef4444; color: #fff; }
.bulk-flag    { background: #fef3c7; color: #92400e; }
.bulk-flag:hover    { background: #f59e0b; color: #fff; }

.admin-pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 16px 0;
    flex-wrap: wrap;
}

.page-btn, .page-num {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e5e7eb;
    border-radius: 7px;
    font-size: 0.83rem;
    color: #374151;
    text-decoration: none;
    background: #fff;
    transition: all 0.2s;
}

.page-btn:hover, .page-num:hover { background: #0d6efd; color: #fff; border-color: #0d6efd; }
.page-num.active { background: #0d6efd; color: #fff; border-color: #0d6efd; font-weight: 700; }
.page-info { font-size: 0.78rem; color: #9ca3af; margin-left: 8px; }

.stat-archived { background: #f3f4f6; color: #6b7280; }
</style>
</body>
</html>