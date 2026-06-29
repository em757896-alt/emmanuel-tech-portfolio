<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../config/auth.php';

requireAdmin();

$pageTitle   = 'Incident Reports - Admin';
$currentPage = 'incidents';

$db = Database::getInstance()->getConnection();

$actionMsg  = '';
$actionType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitizeInput($_POST['action'] ?? '');
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $actionMsg  = 'Invalid security token.';
        $actionType = 'error';
    } elseif ($action === 'update_status') {
        $id     = intval($_POST['id'] ?? 0);
        $status = sanitizeInput($_POST['status'] ?? '');
        $notes  = sanitizeInput($_POST['mod_notes'] ?? '');
        if ($id && in_array($status, ['reported','under_review','investigating','resolved','dismissed'])) {
            $db->prepare("UPDATE incident_reports SET status=:s, moderated_by=:u, moderated_at=NOW() WHERE id=:id")
               ->execute([':s'=>$status, ':u'=>$_SESSION['user_id'], ':id'=>$id]);
            $actionMsg  = 'Incident status updated.';
            $actionType = 'success';
        }
    }
}

$filterStatus  = sanitizeInput($_GET['status'] ?? '');
$filterType    = sanitizeInput($_GET['etype'] ?? '');
$filterUrgency = sanitizeInput($_GET['urgency'] ?? '');
$search        = sanitizeInput($_GET['q'] ?? '');
$page          = max(1, intval($_GET['page'] ?? 1));
$perPage       = 20;
$offset        = ($page - 1) * $perPage;

$conditions = ['1=1'];
$params     = [];

if ($filterStatus) { $conditions[] = "status = :s"; $params[':s'] = $filterStatus; }
if ($filterType)   { $conditions[] = "incident_type = :t"; $params[':t'] = $filterType; }
if ($filterUrgency){ $conditions[] = "urgency_level = :u"; $params[':u'] = $filterUrgency; }
if ($search)       { $conditions[] = "(location LIKE :q OR description LIKE :q2)"; $params[':q']="%$search%"; $params[':q2']="%$search%"; }

$where = implode(' AND ', $conditions);

$countStmt = $db->prepare("SELECT COUNT(*) FROM incident_reports WHERE $where");
$countStmt->execute($params);
$totalItems = (int)$countStmt->fetchColumn();
$totalPages = ceil($totalItems / $perPage);

$params[':limit']  = $perPage;
$params[':offset'] = $offset;

$stmt = $db->prepare("
    SELECT i.*, u.full_name as moderator_name
    FROM incident_reports i
    LEFT JOIN users u ON u.id = i.moderated_by
    WHERE $where
    ORDER BY FIELD(i.urgency_level,'urgent','high','medium','low'),
             i.created_at DESC
    LIMIT :limit OFFSET :offset
");
$stmt->execute($params);
$items = $stmt->fetchAll();

$summaryStmt = $db->query("
    SELECT
        COUNT(*) as total,
        SUM(status='reported') as reported,
        SUM(status='under_review') as under_review,
        SUM(status='resolved') as resolved,
        SUM(urgency_level IN ('high','urgent')) as critical
    FROM incident_reports
");
$summary = $summaryStmt->fetch();

$typeLabels = [
    'harassment' => 'Harassment', 'intimidation' => 'Intimidation',
    'arbitrary_arrest' => 'Arbitrary Arrest', 'deregistration' => 'Deregistration',
    'funding_block' => 'Funding Block', 'assembly_denial' => 'Assembly Denial',
    'other' => 'Other',
];
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
                <span class="bc-item active">Incident Reports</span>
            </div>
        </div>
    </header>

    <div class="dashboard-content">

        <?php if ($actionMsg): ?>
        <div class="action-message action-<?php echo $actionType; ?>">
            <i class="fas fa-<?php echo $actionType==='success'?'check-circle':'times-circle'; ?>"></i>
            <?php echo htmlspecialchars($actionMsg); ?>
        </div>
        <?php endif; ?>

        <div class="page-header">
            <div>
                <h1>Incident Reports</h1>
                <p>Manage civic space violation incident reports submitted through the monitoring module.</p>
            </div>
        </div>

        <div class="summary-strip">
            <a href="?" class="strip-card <?php echo !$filterStatus?'strip-active':''; ?>">
                <span class="strip-num"><?php echo number_format($summary['total']); ?></span>
                <span class="strip-label">Total</span>
            </a>
            <a href="?status=reported" class="strip-card strip-warning <?php echo $filterStatus==='reported'?'strip-active':''; ?>">
                <span class="strip-num"><?php echo $summary['reported']; ?></span>
                <span class="strip-label">Reported</span>
            </a>
            <a href="?status=under_review" class="strip-card strip-purple <?php echo $filterStatus==='under_review'?'strip-active':''; ?>">
                <span class="strip-num"><?php echo $summary['under_review']; ?></span>
                <span class="strip-label">Under Review</span>
            </a>
            <a href="?status=resolved" class="strip-card strip-success <?php echo $filterStatus==='resolved'?'strip-active':''; ?>">
                <span class="strip-num"><?php echo $summary['resolved']; ?></span>
                <span class="strip-label">Resolved</span>
            </a>
            <a href="?urgency=urgent" class="strip-card strip-critical <?php echo $filterUrgency==='urgent'?'strip-active':''; ?>">
                <span class="strip-num"><?php echo $summary['critical']; ?></span>
                <span class="strip-label">Urgent</span>
            </a>
        </div>

        <div class="filters-bar">
            <form method="GET" class="filters-form" id="filtersForm">
                <div class="filter-search">
                    <i class="fas fa-search"></i>
                    <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by location or description...">
                    <?php if($search): ?><a href="?" class="filter-clear-search"><i class="fas fa-times"></i></a><?php endif; ?>
                </div>
                <select name="status" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <?php foreach(['reported','under_review','investigating','resolved','dismissed'] as $s): ?>
                    <option value="<?php echo $s; ?>" <?php echo $filterStatus===$s?'selected':''; ?>><?php echo ucfirst(str_replace('_',' ',$s)); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="etype" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <?php foreach($typeLabels as $val=>$label): ?>
                    <option value="<?php echo $val; ?>" <?php echo $filterType===$val?'selected':''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="urgency" onchange="this.form.submit()">
                    <option value="">All Urgency</option>
                    <?php foreach(['urgent','high','medium','low'] as $u): ?>
                    <option value="<?php echo $u; ?>" <?php echo $filterUrgency===$u?'selected':''; ?>><?php echo ucfirst($u); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
                <?php if($filterStatus||$filterType||$filterUrgency||$search): ?>
                <a href="?" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Type</th>
                            <th>Location</th>
                            <th>Urgency</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($items)): ?>
                        <tr><td colspan="7" class="table-empty"><i class="fas fa-inbox"></i><span>No incidents found.</span></td></tr>
                        <?php else: ?>
                        <?php foreach($items as $row): ?>
                        <tr>
                            <td style="font-family:monospace;font-size:0.72rem;color:#9ca3af">#<?php echo str_pad($row['id'],5,'0',STR_PAD_LEFT); ?></td>
                            <td><span class="type-badge <?php echo $row['severity']==='critical'||$row['urgency_level']==='urgent'?'type-red':'type-blue'; ?>"><?php echo htmlspecialchars($typeLabels[$row['incident_type']]??ucfirst($row['incident_type'])); ?></span></td>
                            <td>
                                <div class="org-cell">
                                    <span class="org-name"><?php echo htmlspecialchars($row['location']); ?></span>
                                    <span class="org-preview"><?php echo htmlspecialchars($row['county']??''); ?></span>
                                </div>
                            </td>
                            <td><span class="severity-badge sev-<?php echo $row['urgency_level']==='urgent'?'critical':$row['urgency_level']; ?>"><?php echo ucfirst($row['urgency_level']); ?></span></td>
                            <td><span class="status-pill stat-<?php echo str_replace('_','-',$row['status']); ?>"><?php echo ucfirst(str_replace('_',' ',$row['status'])); ?></span></td>
                            <td class="date-cell"><?php echo date('d M Y',strtotime($row['created_at'])); ?><span class="date-time"><?php echo date('H:i',strtotime($row['created_at'])); ?></span></td>
                            <td>
                                <form method="POST" style="display:flex;gap:4px;flex-wrap:wrap">
                                    <?php echo generateCSRFField(); ?>
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <select name="status" class="form-select" style="width:auto;font-size:0.75rem;padding:4px 8px;min-width:100px">
                                        <option value="reported" <?php echo $row['status']==='reported'?'selected':''; ?>>Reported</option>
                                        <option value="under_review" <?php echo $row['status']==='under_review'?'selected':''; ?>>Under Review</option>
                                        <option value="investigating" <?php echo $row['status']==='investigating'?'selected':''; ?>>Investigating</option>
                                        <option value="resolved" <?php echo $row['status']==='resolved'?'selected':''; ?>>Resolved</option>
                                        <option value="dismissed" <?php echo $row['status']==='dismissed'?'selected':''; ?>>Dismissed</option>
                                    </select>
                                    <button type="submit" name="action" value="update_status" class="action-btn" title="Update"><i class="fas fa-check"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if($totalPages>1): ?>
        <div class="admin-pagination">
            <?php if($page>1): ?><a href="?<?php echo http_build_query(array_merge($_GET,['page'=>$page-1])); ?>" class="page-btn"><i class="fas fa-chevron-left"></i></a><?php endif; ?>
            <?php for($p=max(1,$page-2);$p<=min($totalPages,$page+2);$p++): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET,['page'=>$p])); ?>" class="page-num <?php echo $p===$page?'active':''; ?>"><?php echo $p; ?></a>
            <?php endfor; ?>
            <?php if($page<$totalPages): ?><a href="?<?php echo http_build_query(array_merge($_GET,['page'=>$page+1])); ?>" class="page-btn"><i class="fas fa-chevron-right"></i></a><?php endif; ?>
            <span class="page-info">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
        </div>
        <?php endif; ?>

    </div>
</main>

<script>
const msg=document.querySelector('.action-message');
if(msg){setTimeout(()=>{msg.style.opacity='0';setTimeout(()=>msg.remove(),400);},4000);}
</script>

<style>
.stat-reported{background:#fef3c7;color:#92400e}
.stat-under_review,.stat-under-review{background:#ede9fe;color:#5b21b6}
.stat-investigating{background:#dbeafe;color:#1e40af}
.stat-resolved{background:#d1fae5;color:#065f46}
.stat-dismissed{background:#f3f4f6;color:#6b7280}
</style>
</body>
</html>
