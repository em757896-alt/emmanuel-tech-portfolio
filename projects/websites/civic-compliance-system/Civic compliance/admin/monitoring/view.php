<?php
/**
 * admin/monitoring/view.php
 * View & Moderate Individual Monitoring Report
 * PBO Compliance Hub | CRECO Kenya
 *
 * DB: if0_42280606_if0_42280606_
 * User: if0_42280606
 * Password: (Your vPanel Password)
 * Host: sql303.infinityfree.com
 */

require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../config/auth.php';

requireAdmin();

$db = Database::getInstance()->getConnection();

$reportId = intval($_GET['id'] ?? 0);
if(!$reportId) {
    header('Location: index.php');
    exit;
}

// ── Fetch Report ─────────────────────────────────────────────────
$stmt = $db->prepare("
    SELECT r.*,
           u.full_name as reviewer_name,
           u.email as reviewer_email
    FROM monitoring_reports r
    LEFT JOIN users u ON u.id = r.moderated_by
    WHERE r.id = :id
");
$stmt->execute([':id' => $reportId]);
$report = $stmt->fetch();

if(!$report) {
    header('Location: index.php?error=not_found');
    exit;
}

// ── Fetch Attachments ─────────────────────────────────────────────
$stmt = $db->prepare("
    SELECT * FROM monitoring_attachments
    WHERE report_id = :report_id
    ORDER BY created_at ASC
");
$stmt->execute([':report_id' => $reportId]);
$attachments = $stmt->fetchAll();

// ── Fetch Related Reports (same county/type) ──────────────────────
$stmt = $db->prepare("
    SELECT id, report_type, organization_name as org_name, severity, status, created_at
    FROM monitoring_reports
    WHERE (county = :county OR report_type = :type)
      AND id != :id
    ORDER BY created_at DESC
    LIMIT 5
");
$stmt->execute([
    ':county' => $report['county'] ?? '',
    ':type'   => $report['report_type'],
    ':id'     => $reportId,
]);
$relatedReports = $stmt->fetchAll();

// ── Fetch Audit Log ───────────────────────────────────────────────
$stmt = $db->prepare("
    SELECT al.*, u.name as actor_name
    FROM audit_log al
    LEFT JOIN users u ON u.id = al.user_id
    WHERE al.record_id = :record_id AND al.table_name = 'monitoring_reports'
    ORDER BY al.created_at DESC
");
$stmt->execute([':record_id' => $reportId]);
$auditLog = $stmt->fetchAll();

// ── Process Moderation Action ─────────────────────────────────────
$actionMessage = '';
$actionType    = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $actionMessage = 'Invalid security token.';
        $actionType    = 'error';
    } else {
        $modAction = sanitizeInput($_POST['mod_action'] ?? '');
        $modNote   = sanitizeInput($_POST['moderation_note'] ?? '');
        $adminId   = $_SESSION['user_id'];

        $allowedActions = ['approve','reject','flag','unflag','archive'];
        if(!in_array($modAction, $allowedActions)) {
            $actionMessage = 'Invalid action.';
            $actionType    = 'error';
        } else {
            $statusMap = [
                'approve' => 'approved',
                'reject'  => 'rejected',
                'flag'    => 'flagged',
                'unflag'  => 'pending',
                'archive' => 'archived',
            ];
            $newStatus = $statusMap[$modAction];

            $stmt = $db->prepare("
                UPDATE monitoring_reports
                SET status       = :status,
                    moderation_notes = :note,
                    moderated_by  = :moderator,
                    moderated_at  = NOW(),
                    updated_at   = NOW()
                WHERE id = :id
            ");
            $ok = $stmt->execute([
                ':status'    => $newStatus,
                ':note'      => $modNote,
                ':moderator' => $adminId,
                ':id'        => $reportId,
            ]);

            if($ok) {
                // Log to audit
                $logStmt = $db->prepare("
                    INSERT INTO audit_logs (user_id, action, module, record_id, ip_address, created_at)
                    VALUES (:uid, :action, 'monitoring_reports', :rid, :ip, NOW())
                ");
                $logStmt->execute([
                    ':uid'    => $adminId,
                    ':action' => 'moderate_' . $modAction,
                    ':rid'    => $reportId,
                    ':ip'     => $_SERVER['REMOTE_ADDR'] ?? '',
                ]);

                $actionMessage = 'Report ' . ucfirst($modAction) . 'd successfully.';
                $actionType    = 'success';

                // Refresh report data
                $stmt = $db->prepare("SELECT r.*, u.full_name as reviewer_name FROM monitoring_reports r LEFT JOIN users u ON u.id=r.moderated_by WHERE r.id=:id");
                $stmt->execute([':id'=>$reportId]);
                $report = $stmt->fetch();
            } else {
                $actionMessage = 'Failed to update report status.';
                $actionType    = 'error';
            }
        }
    }
}

// ── Parse JSON Report Data ────────────────────────────────────────
$reportData = [];
if(!empty($report['report_data'])) {
    $reportData = json_decode($report['report_data'], true) ?? [];
}

// ── Type Config ───────────────────────────────────────────────────
$typeConfig = [
    'compliance' => ['label'=>'Compliance Experience','color'=>'#3b82f6','icon'=>'fa-clipboard-list','bg'=>'#dbeafe'],
    'barrier'    => ['label'=>'Administrative Barrier','color'=>'#f59e0b','icon'=>'fa-road-barrier','bg'=>'#fef3c7'],
    'incident'   => ['label'=>'Civic Space Incident','color'=>'#ef4444','icon'=>'fa-exclamation-triangle','bg'=>'#fee2e2'],
    'enabling'   => ['label'=>'Enabling Practice','color'=>'#10b981','icon'=>'fa-thumbs-up','bg'=>'#d1fae5'],
];
$tc = $typeConfig[$report['report_type']] ?? ['label'=>ucfirst($report['report_type']),'color'=>'#6b7280','icon'=>'fa-file','bg'=>'#f3f4f6'];

$pageTitle = 'Report #' . str_pad($reportId, 5, '0', STR_PAD_LEFT) . ' - Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/report-view.css">
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
                <a href="index.php" class="bc-item">Monitoring</a>
                <span class="bc-sep">/</span>
                <span class="bc-item active">Report #<?php echo str_pad($reportId,5,'0',STR_PAD_LEFT); ?></span>
            </div>
        </div>
        <div class="topbar-right">
            <a href="index.php" class="topbar-btn" title="Back to list">
                <i class="fas fa-arrow-left"></i>
            </a>
            <a href="../../api/admin-dashboard.php?action=export&format=json&report_id=<?php echo $reportId; ?>"
               class="topbar-btn" title="Export this report">
                <i class="fas fa-download"></i>
            </a>
            <button class="topbar-btn" title="Print report" onclick="window.print()">
                <i class="fas fa-print"></i>
            </button>
        </div>
    </header>

    <div class="dashboard-content">

        <!-- Action Message -->
        <?php if($actionMessage): ?>
        <div class="action-message action-<?php echo $actionType; ?>">
            <i class="fas fa-<?php echo $actionType==='success'?'check-circle':'times-circle'; ?>"></i>
            <?php echo htmlspecialchars($actionMessage); ?>
        </div>
        <?php endif; ?>

        <!-- Report Header -->
        <div class="report-view-header">
            <div class="report-id-badge">
                <div class="report-type-icon" style="background:<?php echo $tc['bg']; ?>; color:<?php echo $tc['color']; ?>">
                    <i class="fas <?php echo $tc['icon']; ?>"></i>
                </div>
                <div>
                    <span class="report-id-text">#<?php echo str_pad($reportId,5,'0',STR_PAD_LEFT); ?></span>
                    <span class="report-type-label" style="color:<?php echo $tc['color']; ?>">
                        <?php echo $tc['label']; ?>
                    </span>
                </div>
            </div>

            <div class="report-status-block">
                <?php
                $sevClass = [
                    'critical'=>'sev-critical','high'=>'sev-high',
                    'medium'=>'sev-medium','low'=>'sev-low'
                ][$report['severity']] ?? 'sev-low';

                $statClass = [
                    'pending'=>'stat-pending','approved'=>'stat-approved',
                    'rejected'=>'stat-rejected','flagged'=>'stat-flagged','archived'=>'stat-archived'
                ][$report['status']] ?? 'stat-pending';
                ?>
                <span class="severity-badge <?php echo $sevClass; ?>">
                    <i class="fas fa-circle"></i> <?php echo ucfirst($report['severity']); ?> Severity
                </span>
                <span class="status-pill <?php echo $statClass; ?>">
                    <?php echo ucfirst($report['status']); ?>
                </span>
                <span class="report-date">
                    <i class="fas fa-calendar"></i>
                    <?php echo date('d M Y, H:i', strtotime($report['created_at'])); ?>
                </span>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="report-view-grid">

            <!-- Left: Report Details -->
            <div class="report-details-col">

                <!-- Organization Info -->
                <div class="detail-card">
                    <div class="detail-card-header">
                        <i class="fas fa-building"></i>
                        <h3>Organization Information</h3>
                    </div>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Organization Name</label>
                            <value><?php echo htmlspecialchars($report['org_name'] ?? 'Anonymous'); ?></value>
                        </div>
                        <div class="detail-item">
                            <label>Organization Type</label>
                            <value><?php echo htmlspecialchars($report['org_type'] ?? '—'); ?></value>
                        </div>
                        <div class="detail-item">
                            <label>County</label>
                            <value>
                                <i class="fas fa-map-marker-alt" style="color:#ef4444"></i>
                                <?php echo htmlspecialchars($report['submitter_county'] ?? '—'); ?>
                            </value>
                        </div>
                        <div class="detail-item">
                            <label>Contact Email</label>
                            <value>
                                <?php if($report['contact_email']): ?>
                                <a href="mailto:<?php echo htmlspecialchars($report['contact_email']); ?>">
                                    <?php echo htmlspecialchars($report['contact_email']); ?>
                                </a>
                                <?php else: ?>
                                <em>Not provided</em>
                                <?php endif; ?>
                            </value>
                        </div>
                        <div class="detail-item detail-item-full">
                            <label>Submitted (IP Hash)</label>
                            <value style="font-family:monospace;font-size:0.8rem;color:#9ca3af">
                                <?php echo htmlspecialchars($report['ip_address'] ?? '—'); ?>
                            </value>
                        </div>
                    </div>
                </div>

                <!-- Report Description -->
                <div class="detail-card">
                    <div class="detail-card-header">
                        <i class="fas fa-align-left"></i>
                        <h3>Report Description</h3>
                    </div>
                    <div class="description-body">
                        <?php echo nl2br(htmlspecialchars($report['description'] ?? '')); ?>
                    </div>
                </div>

                <!-- Type-Specific Data -->
                <?php if(!empty($reportData)): ?>
                <div class="detail-card">
                    <div class="detail-card-header">
                        <i class="fas fa-list-ul"></i>
                        <h3>Detailed Report Data</h3>
                    </div>
                    <div class="detail-grid">
                        <?php
                        // Human-readable labels for common fields
                        $fieldLabels = [
                            'registration_status'   => 'Registration Status',
                            'registration_duration' => 'Registration Duration',
                            'registration_rating'   => 'Registration Rating',
                            'compliance_challenges' => 'Compliance Challenges',
                            'registration_cost_kes' => 'Registration Cost (KES)',
                            'visits_count'          => 'Follow-up Visits',
                            'documents_count'       => 'Documents Requested',
                            'informal_payments'     => 'Informal Payments Requested',
                            'barrier_type'          => 'Barrier Type',
                            'authority_office'      => 'PBO Authority Office',
                            'barrier_date'          => 'Barrier Date',
                            'impact'                => 'Organizational Impact',
                            'resolved'              => 'Resolution Status',
                            'resolution_details'    => 'Resolution Details',
                            'violation_type'        => 'Violation Type',
                            'severity'              => 'Severity',
                            'incident_date'         => 'Incident Date',
                            'perpetrator_type'      => 'Perpetrator Type',
                            'people_affected'       => 'People Affected',
                            'reported_to_authorities' => 'Reported to Authorities',
                            'authority_reference'   => 'Authority Reference',
                            'witness_info'          => 'Witness Information',
                            'practice_category'     => 'Practice Category',
                            'practice_rating'       => 'Practice Rating',
                            'recommend_replication' => 'Recommend Replication',
                            'reporter_type'         => 'Reporter Type',
                        ];

                        foreach($reportData as $key => $val):
                            if(empty($val) && $val !== 0) continue;
                            $label = $fieldLabels[$key] ?? ucwords(str_replace('_',' ',$key));
                        ?>
                        <div class="detail-item <?php echo strlen((string)$val) > 60 ? 'detail-item-full' : ''; ?>">
                            <label><?php echo htmlspecialchars($label); ?></label>
                            <value>
                                <?php
                                if(is_array($val)) {
                                    echo implode(', ', array_map('htmlspecialchars', $val));
                                } elseif(strpos($key,'_date') !== false) {
                                    echo date('d M Y', strtotime($val));
                                } elseif($key === 'registration_rating' || $key === 'practice_rating') {
                                    echo str_repeat('★', (int)$val) . str_repeat('☆', 5-(int)$val);
                                } else {
                                    echo nl2br(htmlspecialchars($val));
                                }
                                ?>
                            </value>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Attachments -->
                <?php if(!empty($attachments)): ?>
                <div class="detail-card">
                    <div class="detail-card-header">
                        <i class="fas fa-paperclip"></i>
                        <h3>Attachments (<?php echo count($attachments); ?>)</h3>
                    </div>
                    <div class="attachments-list">
                        <?php foreach($attachments as $att):
                            $ext = pathinfo($att['original_name'], PATHINFO_EXTENSION);
                            $iconMap = [
                                'pdf'=>'fa-file-pdf','doc'=>'fa-file-word','docx'=>'fa-file-word',
                                'jpg'=>'fa-file-image','jpeg'=>'fa-file-image','png'=>'fa-file-image',
                                'mp4'=>'fa-file-video','mov'=>'fa-file-video',
                            ];
                            $icon = $iconMap[strtolower($ext)] ?? 'fa-file';
                        ?>
                        <div class="attachment-item">
                            <div class="att-icon">
                                <i class="fas <?php echo $icon; ?>"></i>
                            </div>
                            <div class="att-info">
                                <span class="att-name"><?php echo htmlspecialchars($att['original_name']); ?></span>
                                <span class="att-meta">
                                    <?php echo strtoupper($ext); ?> —
                                    <?php echo number_format($att['file_size']/1024, 1); ?> KB —
                                    <?php echo date('d M Y', strtotime($att['created_at'])); ?>
                                </span>
                            </div>
                            <a href="../../<?php echo htmlspecialchars($att['stored_path']); ?>"
                               class="att-download" download title="Download">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Audit Log -->
                <?php if(!empty($auditLog)): ?>
                <div class="detail-card">
                    <div class="detail-card-header">
                        <i class="fas fa-history"></i>
                        <h3>Audit Log</h3>
                    </div>
                    <div class="audit-log">
                        <?php foreach($auditLog as $log): ?>
                        <div class="audit-item">
                            <div class="audit-icon">
                                <?php
                                $auditIcons = [
                                    'approve'=>'fa-check','reject'=>'fa-times','flag'=>'fa-flag',
                                    'unflag'=>'fa-flag','archive'=>'fa-archive','create'=>'fa-plus',
                                ];
                                echo '<i class="fas '.($auditIcons[$log['action']]??'fa-edit').'"></i>';
                                ?>
                            </div>
                            <div class="audit-content">
                                <strong><?php echo htmlspecialchars($log['actor_name'] ?? 'System'); ?></strong>
                                <span><?php echo ucfirst($log['action']); ?>d report
                                    (<?php echo htmlspecialchars($log['old_value']); ?> →
                                     <?php echo htmlspecialchars($log['new_value']); ?>)</span>
                            </div>
                            <div class="audit-time">
                                <?php echo date('d M Y H:i', strtotime($log['created_at'])); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div><!-- /.report-details-col -->

            <!-- Right: Moderation Panel -->
            <div class="moderation-col">

                <!-- Moderation Actions -->
                <div class="detail-card mod-card">
                    <div class="detail-card-header">
                        <i class="fas fa-gavel"></i>
                        <h3>Moderation Actions</h3>
                    </div>

                    <?php if($report['reviewed_by']): ?>
                    <div class="reviewed-by">
                        <i class="fas fa-user-check"></i>
                        <span>Last reviewed by <strong><?php echo htmlspecialchars($report['reviewer_name']); ?></strong>
                        on <?php echo date('d M Y', strtotime($report['reviewed_at'])); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if($report['moderation_note']): ?>
                    <div class="mod-note-display">
                        <label><i class="fas fa-sticky-note"></i> Previous Note:</label>
                        <p><?php echo htmlspecialchars($report['moderation_note']); ?></p>
                    </div>
                    <?php endif; ?>

                    <form method="POST" id="moderationForm">
                        <?php echo generateCSRFField(); ?>

                        <div class="form-group">
                            <label>Moderation Note (Optional)</label>
                            <textarea name="moderation_note" rows="3"
                                placeholder="Add a note explaining your decision..."><?php echo htmlspecialchars($report['moderation_note'] ?? ''); ?></textarea>
                        </div>

                        <div class="mod-actions">
                            <?php if($report['status'] !== 'approved'): ?>
                            <button type="submit" name="mod_action" value="approve"
                                    class="mod-btn mod-approve"
                                    onclick="return confirm('Approve this report?')">
                                <i class="fas fa-check-circle"></i> Approve
                            </button>
                            <?php endif; ?>

                            <?php if($report['status'] !== 'rejected'): ?>
                            <button type="submit" name="mod_action" value="reject"
                                    class="mod-btn mod-reject"
                                    onclick="return confirm('Reject this report?')">
                                <i class="fas fa-times-circle"></i> Reject
                            </button>
                            <?php endif; ?>

                            <?php if($report['status'] !== 'flagged'): ?>
                            <button type="submit" name="mod_action" value="flag"
                                    class="mod-btn mod-flag">
                                <i class="fas fa-flag"></i> Flag for Review
                            </button>
                            <?php else: ?>
                            <button type="submit" name="mod_action" value="unflag"
                                    class="mod-btn mod-unflag">
                                <i class="fas fa-flag"></i> Remove Flag
                            </button>
                            <?php endif; ?>

                            <?php if($report['status'] !== 'archived'): ?>
                            <button type="submit" name="mod_action" value="archive"
                                    class="mod-btn mod-archive"
                                    onclick="return confirm('Archive this report?')">
                                <i class="fas fa-archive"></i> Archive
                            </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Summary Stats for this Report -->
                <div class="detail-card">
                    <div class="detail-card-header">
                        <i class="fas fa-info-circle"></i>
                        <h3>Report Summary</h3>
                    </div>
                    <ul class="summary-list">
                        <li>
                            <span>Report ID</span>
                            <strong>#<?php echo str_pad($reportId,5,'0',STR_PAD_LEFT); ?></strong>
                        </li>
                        <li>
                            <span>Type</span>
                            <strong style="color:<?php echo $tc['color'];?>"><?php echo $tc['label']; ?></strong>
                        </li>
                        <li>
                            <span>County</span>
                            <strong><?php echo htmlspecialchars($report['submitter_county']); ?></strong>
                        </li>
                        <li>
                            <span>Severity</span>
                            <span class="severity-badge <?php echo $sevClass; ?>">
                                <?php echo ucfirst($report['severity']); ?>
                            </span>
                        </li>
                        <li>
                            <span>Status</span>
                            <span class="status-pill <?php echo $statClass; ?>">
                                <?php echo ucfirst($report['status']); ?>
                            </span>
                        </li>
                        <li>
                            <span>Attachments</span>
                            <strong><?php echo count($attachments); ?></strong>
                        </li>
                        <li>
                            <span>Submitted</span>
                            <strong><?php echo date('d M Y', strtotime($report['created_at'])); ?></strong>
                        </li>
                        <?php if($report['updated_at'] && $report['updated_at'] !== $report['created_at']): ?>
                        <li>
                            <span>Last Updated</span>
                            <strong><?php echo date('d M Y H:i', strtotime($report['updated_at'])); ?></strong>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Related Reports -->
                <?php if(!empty($relatedReports)): ?>
                <div class="detail-card">
                    <div class="detail-card-header">
                        <i class="fas fa-link"></i>
                        <h3>Related Reports</h3>
                    </div>
                    <div class="related-list">
                        <?php foreach($relatedReports as $rel):
                            $relTc = $typeConfig[$rel['report_type']] ?? $tc;
                        ?>
                        <a href="view.php?id=<?php echo $rel['id']; ?>" class="related-item">
                            <span class="rel-type" style="color:<?php echo $relTc['color']; ?>">
                                <i class="fas <?php echo $relTc['icon']; ?>"></i>
                            </span>
                            <div class="rel-info">
                                <span class="rel-id">#<?php echo str_pad($rel['id'],5,'0',STR_PAD_LEFT); ?></span>
                                <span class="rel-org"><?php echo htmlspecialchars($rel['org_name']); ?></span>
                            </div>
                            <span class="rel-date"><?php echo date('d M', strtotime($rel['created_at'])); ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Navigation -->
                <div class="nav-actions">
                    <?php
                    // Prev/next report
                    $prevStmt = $db->prepare("SELECT id FROM monitoring_reports WHERE id < :id ORDER BY id DESC LIMIT 1");
                    $prevStmt->execute([':id'=>$reportId]);
                    $prevId = $prevStmt->fetchColumn();

                    $nextStmt = $db->prepare("SELECT id FROM monitoring_reports WHERE id > :id ORDER BY id ASC LIMIT 1");
                    $nextStmt->execute([':id'=>$reportId]);
                    $nextId = $nextStmt->fetchColumn();
                    ?>
                    <?php if($prevId): ?>
                    <a href="view.php?id=<?php echo $prevId; ?>" class="nav-btn">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                    <?php endif; ?>
                    <a href="index.php" class="nav-btn nav-btn-center">
                        <i class="fas fa-th-list"></i> All Reports
                    </a>
                    <?php if($nextId): ?>
                    <a href="view.php?id=<?php echo $nextId; ?>" class="nav-btn">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                    <?php endif; ?>
                </div>

            </div><!-- /.moderation-col -->

        </div><!-- /.report-view-grid -->
    </div><!-- /.dashboard-content -->
</main>

<script>
function toggleSidebar() {
    document.getElementById('adminSidebar')?.classList.toggle('collapsed');
    document.getElementById('adminMain')?.classList.toggle('expanded');
}

// Auto-dismiss action message
const msg = document.querySelector('.action-message');
if(msg) setTimeout(() => {
    msg.style.opacity = '0';
    msg.style.transform = 'translateY(-10px)';
    setTimeout(() => msg.remove(), 400);
}, 4000);

// Print styles
window.addEventListener('beforeprint', () => {
    document.querySelectorAll('.mod-card, .nav-actions, .topbar-right').forEach(el => {
        el.style.display = 'none';
    });
});
window.addEventListener('afterprint', () => {
    document.querySelectorAll('.mod-card, .nav-actions, .topbar-right').forEach(el => {
        el.style.display = '';
    });
});
</script>

<style>
/* Report View Page Specific Styles */
.report-view-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 20px 28px;
    margin-bottom: 24px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    flex-wrap: wrap;
    gap: 16px;
}

.report-id-badge {
    display: flex;
    align-items: center;
    gap: 16px;
}

.report-type-icon {
    width: 54px;
    height: 54px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
}

.report-id-text {
    display: block;
    font-size: 1.3rem;
    font-weight: 700;
    color: #111827;
    font-family: monospace;
    line-height: 1;
}

.report-type-label {
    display: block;
    font-size: 0.82rem;
    font-weight: 600;
    margin-top: 3px;
}

.report-status-block {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.report-date {
    font-size: 0.82rem;
    color: #9ca3af;
    display: flex;
    align-items: center;
    gap: 5px;
}

.report-view-grid {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 20px;
    align-items: start;
}

.detail-card {
    background: #fff;
    border-radius: 14px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
    margin-bottom: 20px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}

.detail-card-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 16px 22px;
    border-bottom: 1px solid #f3f4f6;
    background: #fafafa;
}

.detail-card-header i { color: #1a3c5e; font-size: 1rem; }
.detail-card-header h3 { font-size: 0.95rem; font-weight: 600; color: #1a3c5e; }

.detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1px;
    background: #f3f4f6;
    padding: 1px;
}

.detail-item {
    background: #fff;
    padding: 14px 20px;
}

.detail-item-full { grid-column: 1 / -1; }

.detail-item label {
    display: block;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #9ca3af;
    font-weight: 600;
    margin-bottom: 4px;
}

.detail-item value {
    display: block;
    font-size: 0.9rem;
    color: #374151;
    line-height: 1.5;
}

.description-body {
    padding: 20px 22px;
    font-size: 0.92rem;
    color: #374151;
    line-height: 1.8;
}

.attachments-list { padding: 16px 22px; display: flex; flex-direction: column; gap: 10px; }

.attachment-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
}

.att-icon { font-size: 1.4rem; color: #6b7280; width: 30px; text-align: center; }
.att-info { flex: 1; }
.att-name { display: block; font-size: 0.85rem; font-weight: 500; color: #374151; }
.att-meta { display: block; font-size: 0.72rem; color: #9ca3af; margin-top: 2px; }
.att-download { color: #0d6efd; font-size: 0.9rem; padding: 6px; }

.audit-log { padding: 8px 22px; }

.audit-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 0;
    border-bottom: 1px solid #f3f4f6;
}

.audit-item:last-child { border-bottom: none; }

.audit-icon {
    width: 28px;
    height: 28px;
    background: #eff6ff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    color: #1d4ed8;
    flex-shrink: 0;
}

.audit-content { flex: 1; font-size: 0.82rem; color: #6b7280; }
.audit-content strong { color: #374151; }
.audit-time { font-size: 0.72rem; color: #9ca3af; white-space: nowrap; }

/* Moderation Card */
.mod-card form { padding: 16px 22px 20px; }

.mod-card .form-group { margin-bottom: 16px; }
.mod-card .form-group label { font-size: 0.82rem; font-weight: 500; color: #374151; display: block; margin-bottom: 5px; }
.mod-card .form-group textarea {
    width: 100%; padding: 8px 12px; border: 1.5px solid #d1d5db;
    border-radius: 8px; font-size: 0.85rem; resize: vertical; min-height: 80px;
    font-family: inherit; color: #374151;
}
.mod-card .form-group textarea:focus { outline: none; border-color: #3b82f6; }

.reviewed-by {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 22px;
    background: #f0fdf4;
    font-size: 0.82rem;
    color: #065f46;
    border-bottom: 1px solid #d1fae5;
}

.mod-note-display {
    padding: 10px 22px;
    background: #fffbeb;
    border-bottom: 1px solid #fde68a;
}

.mod-note-display label {
    font-size: 0.72rem;
    color: #92400e;
    font-weight: 600;
    text-transform: uppercase;
    display: block;
    margin-bottom: 4px;
}

.mod-note-display p { font-size: 0.82rem; color: #78350f; }

.mod-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.mod-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 9px 12px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.82rem;
    font-weight: 600;
    transition: all 0.2s;
}

.mod-approve { background: #d1fae5; color: #065f46; }
.mod-approve:hover { background: #10b981; color: #fff; }

.mod-reject  { background: #fee2e2; color: #991b1b; }
.mod-reject:hover  { background: #ef4444; color: #fff; }

.mod-flag    { background: #fef3c7; color: #92400e; }
.mod-flag:hover    { background: #f59e0b; color: #fff; }

.mod-unflag  { background: #e0e7ff; color: #3730a3; }
.mod-unflag:hover  { background: #6366f1; color: #fff; }

.mod-archive { background: #f3f4f6; color: #6b7280; grid-column: 1 / -1; }
.mod-archive:hover { background: #6b7280; color: #fff; }

.summary-list {
    list-style: none;
    padding: 8px 22px 16px;
}

.summary-list li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f3f4f6;
    font-size: 0.83rem;
    color: #6b7280;
}

.summary-list li:last-child { border-bottom: none; }
.summary-list li strong { color: #374151; }

.related-list { padding: 8px 12px; }

.related-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 10px;
    border-radius: 8px;
    text-decoration: none;
    transition: background 0.2s;
    margin-bottom: 4px;
}

.related-item:hover { background: #f8fafc; }

.rel-type { width: 24px; text-align: center; font-size: 0.9rem; }
.rel-info { flex: 1; }
.rel-id { display: block; font-size: 0.78rem; font-family: monospace; color: #6b7280; }
.rel-org { display: block; font-size: 0.83rem; color: #374151; font-weight: 500; }
.rel-date { font-size: 0.72rem; color: #9ca3af; }

.nav-actions {
    display: flex;
    gap: 8px;
}

.nav-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 9px 12px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.82rem;
    color: #374151;
    text-decoration: none;
    transition: all 0.2s;
}

.nav-btn:hover { background: #1a3c5e; color: #fff; border-color: #1a3c5e; }
.nav-btn-center { background: #1a3c5e; color: #fff; border-color: #1a3c5e; }

.action-message {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.4s ease;
}

.action-success { background: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; }
.action-error   { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; }

@media (max-width: 1024px) {
    .report-view-grid { grid-template-columns: 1fr; }
    .moderation-col { order: -1; }
}

@media print {
    .admin-sidebar, .admin-topbar { display: none !important; }
    .admin-main { margin-left: 0 !important; }
    .report-view-grid { grid-template-columns: 1fr !important; }
}
</style>
</body>
</html>