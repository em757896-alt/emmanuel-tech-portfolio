<?php
/**
 * admin/chatbot/index.php
 * Chatbot Management & Monitoring
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

$pageTitle   = 'Chatbot Management - Admin';
$currentPage = 'chatbot';

$db = Database::getInstance()->getConnection();

// â”€â”€ Handle Actions â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$actionMsg  = '';
$actionType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $actionMsg  = 'Invalid security token.';
        $actionType = 'error';
    } else {
        $action = sanitizeInput($_POST['action'] ?? '');
        $logId  = intval($_POST['log_id'] ?? 0);

        switch ($action) {
            case 'mark_reviewed':
                $db->prepare("UPDATE chatbot_conversations SET flagged_for_review=0 WHERE id=:id")
                   ->execute([':id' => $logId]);
                $actionMsg  = 'Response marked as reviewed.';
                $actionType = 'success';
                break;

            case 'add_to_kb':
                // Add flagged Q&A pair to knowledge base for training
                $question = sanitizeInput($_POST['question'] ?? '');
                $answer   = sanitizeInput($_POST['approved_answer'] ?? '');
                if ($question && $answer) {
                    $db->prepare("
                        INSERT INTO chatbot_knowledge_base
                            (question_pattern, keywords, answer_en, source_reference, is_active, created_at)
                        VALUES (:q, '', :a, 'admin_approved', 1, NOW())
                    ")->execute([':q' => $question, ':a' => $answer]);
                    $db->prepare("UPDATE chatbot_conversations SET flagged_for_review=0 WHERE id=:id")
                       ->execute([':id' => $logId]);
                    $actionMsg  = 'Added to knowledge base and marked as reviewed.';
                    $actionType = 'success';
                }
                break;

            case 'save_kb_entry':
                // Directly add to knowledge base
                $kbQ = sanitizeInput($_POST['kb_question'] ?? '');
                $kbA = sanitizeInput($_POST['kb_answer'] ?? '');
                $kbS = sanitizeInput($_POST['kb_source'] ?? 'manual');
                if ($kbQ && $kbA) {
                    $db->prepare("
                        INSERT INTO chatbot_knowledge_base
                            (question_pattern, keywords, answer_en, source_reference, is_active, created_at)
                        VALUES (:q, '', :a, :s, 1, NOW())
                    ")->execute([':q' => $kbQ, ':a' => $kbA, ':s' => $kbS]);
                    $actionMsg  = 'Knowledge base entry added.';
                    $actionType = 'success';
                }
                break;
        }
    }
}

// â”€â”€ Stats â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$stmt = $db->query("SELECT COUNT(*) FROM chatbot_conversations");
$totalQueries = (int)$stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM chatbot_conversations WHERE DATE(created_at)=CURDATE()");
$todayQueries = (int)$stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM chatbot_conversations WHERE flagged_for_review=1");
$pendingReview = (int)$stmt->fetchColumn();

$stmt = $db->query("
    SELECT
        SUM(feedback='positive') as positive,
        SUM(feedback='negative') as negative,
        COUNT(CASE WHEN feedback IS NOT NULL THEN 1 END) as with_feedback
    FROM chatbot_conversations
");
$feedbackStats = $stmt->fetch();

$stmt = $db->query("SELECT COUNT(*) FROM chatbot_knowledge_base WHERE is_active=1");
$kbEntries = (int)$stmt->fetchColumn();

// â”€â”€ Flagged Logs â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$stmt = $db->query("
    SELECT *
    FROM chatbot_conversations
    WHERE flagged_for_review=1
    ORDER BY created_at DESC
    LIMIT 20
");
$flaggedLogs = $stmt->fetchAll();

// â”€â”€ Recent Logs â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$page    = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;
$offset  = ($page - 1) * $perPage;

$countStmt = $db->query("SELECT COUNT(*) FROM chatbot_conversations");
$totalLogs = (int)$countStmt->fetchColumn();
$totalPages = ceil($totalLogs / $perPage);

$stmt = $db->prepare("
    SELECT *
    FROM chatbot_conversations
    ORDER BY created_at DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
$stmt->execute();
$recentLogs = $stmt->fetchAll();

// â”€â”€ Knowledge Base â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$kbStmt = $db->query("
    SELECT * FROM chatbot_knowledge_base
    WHERE is_active=1
    ORDER BY created_at DESC
    LIMIT 10
");
$kbEntries_list = $kbStmt->fetchAll();

// â”€â”€ Top Queries â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$stmt = $db->query("
    SELECT user_message, COUNT(*) as count
    FROM chatbot_conversations
    GROUP BY user_message
    ORDER BY count DESC
    LIMIT 10
");
$topQueries = $stmt->fetchAll();

$posRate = ($feedbackStats['with_feedback'] > 0)
    ? round($feedbackStats['positive'] / $feedbackStats['with_feedback'] * 100)
    : 0;
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
                <span class="bc-item active">Chatbot</span>
            </div>
        </div>
    </header>

    <div class="dashboard-content">

        <?php if ($actionMsg): ?>
        <div class="action-message action-<?php echo $actionType; ?>">
            <i class="fas fa-<?php echo $actionType === 'success' ? 'check-circle' : 'times-circle'; ?>"></i>
            <?php echo htmlspecialchars($actionMsg); ?>
        </div>
        <?php endif; ?>

        <div class="page-header">
            <div>
                <h1>AI Chatbot Management</h1>
                <p>Monitor chatbot performance, review flagged responses, and manage the knowledge base.</p>
            </div>
            <button class="btn btn-primary" onclick="toggleKBForm(true)">
                <i class="fas fa-plus"></i> Add to Knowledge Base
            </button>
        </div>

        <!-- KPI Strip -->
        <div class="kpi-grid" style="grid-template-columns:repeat(5,1fr)">
            <div class="kpi-card kpi-blue">
                <div class="kpi-icon"><i class="fas fa-comments"></i></div>
                <div class="kpi-body">
                    <span class="kpi-value"><?php echo number_format($totalQueries); ?></span>
                    <span class="kpi-label">Total Queries</span>
                </div>
            </div>
            <div class="kpi-card kpi-green">
                <div class="kpi-icon"><i class="fas fa-calendar-day"></i></div>
                <div class="kpi-body">
                    <span class="kpi-value"><?php echo $todayQueries; ?></span>
                    <span class="kpi-label">Queries Today</span>
                </div>
            </div>
            <div class="kpi-card kpi-red">
                <div class="kpi-icon"><i class="fas fa-flag"></i></div>
                <div class="kpi-body">
                    <span class="kpi-value"><?php echo $pendingReview; ?></span>
                    <span class="kpi-label">Pending Review</span>
                    <span class="kpi-sub kpi-warning">Flagged responses</span>
                </div>
            </div>
            <div class="kpi-card kpi-teal">
                <div class="kpi-icon"><i class="fas fa-thumbs-up"></i></div>
                <div class="kpi-body">
                    <span class="kpi-value"><?php echo $posRate; ?>%</span>
                    <span class="kpi-label">Positive Feedback</span>
                    <span class="kpi-sub"><?php echo $feedbackStats['with_feedback']; ?> rated</span>
                </div>
            </div>
            <div class="kpi-card kpi-purple">
                <div class="kpi-icon"><i class="fas fa-database"></i></div>
                <div class="kpi-body">
                    <span class="kpi-value"><?php echo $kbEntries; ?></span>
                    <span class="kpi-label">Knowledge Base Entries</span>
                </div>
            </div>
        </div>

        <!-- Add to KB Form -->
        <div class="editor-panel" id="kbForm" style="display:none">
            <div class="editor-header">
                <h3><i class="fas fa-database"></i> Add Knowledge Base Entry</h3>
                <button class="editor-close" onclick="toggleKBForm(false)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" style="padding:24px">
                <?php echo generateCSRFField(); ?>
                <input type="hidden" name="action" value="save_kb_entry">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:16px">
                    <div>
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:5px">
                            Question / User Query <span class="req">*</span>
                        </label>
                        <textarea name="kb_question" rows="3" required
                                  style="width:100%;padding:9px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-size:0.87rem;font-family:inherit"
                                  placeholder="e.g. What documents are needed for PBO registration?"></textarea>
                    </div>
                    <div>
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:5px">
                            Approved Answer <span class="req">*</span>
                        </label>
                        <textarea name="kb_answer" rows="3" required
                                  style="width:100%;padding:9px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-size:0.87rem;font-family:inherit"
                                  placeholder="The approved, accurate answer based on PBO Act materials..."></textarea>
                    </div>
                </div>
                <div style="display:flex;gap:12px;align-items:center">
                    <div>
                        <label style="font-size:0.8rem;font-weight:600;color:#374151">Source Reference</label>
                        <input type="text" name="kb_source"
                               style="padding:8px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-size:0.83rem;margin-left:8px"
                               placeholder="e.g. PBO Act Section 10">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Entry
                    </button>
                    <button type="button" class="btn btn-outline" onclick="toggleKBForm(false)">Cancel</button>
                </div>
            </form>
        </div>

        <!-- Content Grid -->
        <div class="chatbot-admin-grid">

            <!-- Left: Flagged & Recent -->
            <div class="chatbot-main-col">

                <!-- Flagged Responses -->
                <?php if (!empty($flaggedLogs)): ?>
                <div class="table-card" style="margin-bottom:20px">
                    <div class="table-header">
                        <div>
                            <h3><i class="fas fa-flag" style="color:#ef4444"></i> Flagged Responses
                                <span class="badge-count"><?php echo count($flaggedLogs); ?></span>
                            </h3>
                            <p>These responses were flagged as inaccurate and need review.</p>
                        </div>
                    </div>
                    <div style="padding:0 4px">
                        <?php foreach ($flaggedLogs as $log): ?>
                        <div class="flagged-log-item">
                            <div class="log-header">
                                <span class="log-id">#<?php echo $log['id']; ?></span>
                                <span class="log-reason">
                                    <i class="fas fa-exclamation-circle" style="color:#ef4444"></i>
                                    <?php echo htmlspecialchars($log['flag_reason'] ?? 'Flagged by user'); ?>
                                </span>
                                <span class="log-date"><?php echo date('d M Y H:i', strtotime($log['created_at'])); ?></span>
                            </div>
                            <div class="log-qa">
                                <div class="log-q">
                                    <strong>Q:</strong>
<?php echo htmlspecialchars($log['user_message']); ?>
                        </div>
                        <div class="log-a">
                            <strong>A:</strong>
                            <?php echo htmlspecialchars(substr($log['response_text'], 0, 300)); ?>
                                    <?php echo strlen($log['response_text']) > 300 ? '...' : ''; ?>
                                </div>
                            </div>
                            <div class="log-actions">
                                <form method="POST" style="display:inline-flex;gap:8px;flex-wrap:wrap">
                                    <?php echo generateCSRFField(); ?>
                                    <input type="hidden" name="log_id" value="<?php echo $log['id']; ?>">
                                    <input type="hidden" name="question" value="<?php echo htmlspecialchars($log['user_message']); ?>">
                                    <textarea name="approved_answer" rows="1"
                                              placeholder="Enter approved answer to add to KB..."
                                              style="flex:1;min-width:200px;padding:6px 10px;border:1.5px solid #d1d5db;border-radius:7px;font-size:0.8rem;font-family:inherit"></textarea>
                                    <button type="submit" name="action" value="add_to_kb"
                                            class="btn btn-primary btn-sm">
                                        <i class="fas fa-database"></i> Add to KB
                                    </button>
                                    <button type="submit" name="action" value="mark_reviewed"
                                            class="btn btn-outline btn-sm">
                                        <i class="fas fa-check"></i> Mark Reviewed
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Recent Logs Table -->
                <div class="table-card">
                    <div class="table-header">
                        <div>
                            <h3>Recent Queries</h3>
                            <p>All chatbot interactions</p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Query</th>
                                    <th>Feedback</th>
                                    <th>Flagged</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentLogs as $log): ?>
                                <tr style="<?php echo $log['flagged_for_review'] ? 'background:#fff5f5' : ''; ?>">
                                    <td style="font-family:monospace;font-size:0.72rem;color:#9ca3af">#<?php echo $log['id']; ?></td>
                                    <td>
                                        <div style="max-width:320px;font-size:0.83rem;color:#374151;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                            <?php echo htmlspecialchars($log['user_message']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($log['feedback'] === 'positive'): ?>
                                        <i class="fas fa-thumbs-up" style="color:#10b981"></i>
                                        <?php elseif ($log['feedback'] === 'negative'): ?>
                                        <i class="fas fa-thumbs-down" style="color:#ef4444"></i>
                                        <?php else: ?>
                                        <span style="color:#d1d5db;font-size:0.75rem">â€”</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($log['flagged_for_review']): ?>
                                        <span class="severity-badge sev-high">Flagged</span>
                                        <?php else: ?>
                                        <span style="color:#d1d5db;font-size:0.75rem">â€”</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="date-cell">
                                        <?php echo date('d M H:i', strtotime($log['created_at'])); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($recentLogs)): ?>
                                <tr><td colspan="5" class="table-empty"><i class="fas fa-comments"></i> No queries yet</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div><!-- /.chatbot-main-col -->

            <!-- Right: Stats & Top Queries -->
            <div class="chatbot-side-col">

                <!-- Top Queries -->
                <div class="table-card" style="margin-bottom:20px">
                    <div class="table-header">
                        <div>
                            <h3>Top Queries</h3>
                            <p>Most frequently asked questions</p>
                        </div>
                    </div>
                    <div style="padding:8px 16px 12px">
                        <?php foreach ($topQueries as $i => $q): ?>
                        <div style="display:flex;align-items:flex-start;gap:10px;padding:8px 0;border-bottom:1px solid #f3f4f6">
                            <span style="background:#eff6ff;color:#1d4ed8;width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.7rem;font-weight:700;flex-shrink:0">
                                <?php echo $i + 1; ?>
                            </span>
                            <div style="flex:1;min-width:0">
                                <div style="font-size:0.82rem;color:#374151;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                    <?php echo htmlspecialchars($q['user_message']); ?>
                                </div>
                                <span style="font-size:0.7rem;color:#9ca3af"><?php echo $q['count']; ?> times</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($topQueries)): ?>
                        <p style="text-align:center;color:#9ca3af;font-size:0.82rem;padding:16px">No queries yet</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Knowledge Base -->
                <div class="table-card">
                    <div class="table-header">
                        <div>
                            <h3>Knowledge Base</h3>
                            <p>Recent approved entries</p>
                        </div>
                        <button class="btn btn-sm btn-outline" onclick="toggleKBForm(true)">
                            <i class="fas fa-plus"></i> Add
                        </button>
                    </div>
                    <div style="padding:8px 16px 12px">
                        <?php foreach ($kbEntries_list as $entry): ?>
                        <div style="padding:10px 0;border-bottom:1px solid #f3f4f6">
                            <div style="font-size:0.83rem;font-weight:500;color:#374151;margin-bottom:3px">
                                <?php echo htmlspecialchars($entry['question_pattern']); ?>
                            </div>
                            <div style="font-size:0.75rem;color:#9ca3af;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                <?php echo htmlspecialchars($entry['answer_en']); ?>
                            </div>
                            <?php if ($entry['source_reference']): ?>
                            <span style="font-size:0.68rem;background:#eff6ff;color:#1d4ed8;padding:1px 6px;border-radius:4px;margin-top:3px;display:inline-block">
                                <?php echo htmlspecialchars($entry['source_reference']); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($kbEntries_list)): ?>
                        <p style="text-align:center;color:#9ca3af;font-size:0.82rem;padding:16px">
                            No knowledge base entries yet.
                        </p>
                        <?php endif; ?>
                    </div>
                </div>

            </div><!-- /.chatbot-side-col -->

        </div><!-- /.chatbot-admin-grid -->

    </div>
</main>

<script>
function toggleKBForm(show) {
    const form = document.getElementById('kbForm');
    form.style.display = show ? 'block' : 'none';
    if(show) form.scrollIntoView({ behavior:'smooth' });
}

const msg = document.querySelector('.action-message');
if(msg) setTimeout(() => { msg.style.opacity='0'; setTimeout(()=>msg.remove(),400); }, 4000);
</script>

<style>
.chatbot-admin-grid {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 20px;
}

.badge-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #ef4444;
    color: #fff;
    font-size: 0.65rem;
    font-weight: 700;
    padding: 1px 7px;
    border-radius: 50px;
    margin-left: 6px;
    vertical-align: middle;
}

.flagged-log-item {
    border: 1px solid #fca5a5;
    border-radius: 10px;
    padding: 14px 18px;
    margin: 10px;
    background: #fff5f5;
}

.log-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
    flex-wrap: wrap;
}

.log-id { font-family: monospace; font-size: 0.72rem; color: #9ca3af; }

.log-reason {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 0.78rem;
    color: #991b1b;
    font-weight: 500;
    flex: 1;
}

.log-date { font-size: 0.72rem; color: #9ca3af; margin-left: auto; }

.log-qa { margin-bottom: 12px; }

.log-q, .log-a {
    font-size: 0.83rem;
    color: #374151;
    padding: 6px 0;
    line-height: 1.5;
}

.log-q { border-bottom: 1px solid #fee2e2; padding-bottom: 8px; margin-bottom: 6px; }
.log-q strong, .log-a strong { color: #1a3c5e; margin-right: 5px; }

.log-actions { display: flex; gap: 8px; flex-wrap: wrap; }

.editor-panel {
    background: #fff;
    border: 2px solid #0d6efd;
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 24px;
    box-shadow: 0 4px 24px rgba(13,110,253,0.1);
}

.editor-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 24px;
    background: linear-gradient(135deg, #1a3c5e, #0d6efd);
    color: #fff;
}

.editor-header h3 { font-size: 0.95rem; font-weight: 600; display: flex; align-items: center; gap: 8px; }
.editor-close { background: rgba(255,255,255,0.15); border: none; color: #fff; width: 28px; height: 28px; border-radius: 6px; cursor: pointer; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; }

@media (max-width: 1024px) {
    .chatbot-admin-grid { grid-template-columns: 1fr; }
}
</style>
</body>
</html>