<?php
/**
 * api/admin-moderation.php
 * Admin Moderation API — approve, reject, flag, bulk actions
 * PBO Compliance Hub | CRECO Kenya
 *
 * DB: if0_42280606_if0_42280606_
 * User: if0_42280606
 * Password: AES256:4m0deNaMM0HA+yKw/HIgbYzFLvAjq8o1cD7cfheTaOSB8M/MqTc/Edx85mfbuzOL
 * Host: sql303.infinityfree.com
 */

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/auth.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// Must be admin or moderator
if (!isLoggedIn() || !in_array($_SESSION['user_role'] ?? '', ['admin', 'moderator'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

// CSRF
if (!validateCSRFToken($input['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid security token']);
    exit;
}

$db      = Database::getInstance()->getConnection();
$action  = sanitizeInput($input['action'] ?? '');
$adminId = (int)$_SESSION['user_id'];

// ── Route Actions ─────────────────────────────────────────────────
switch ($action) {

    // ── Single Report Actions ─────────────────────────────────────
    case 'approve':
    case 'reject':
    case 'flag':
    case 'unflag':
    case 'archive':
        handleSingleAction($db, $action, $input, $adminId);
        break;

    // ── Bulk Actions ──────────────────────────────────────────────
    case 'bulk_approve':
    case 'bulk_reject':
    case 'bulk_flag':
    case 'bulk_archive':
        handleBulkAction($db, $action, $input, $adminId);
        break;

    // ── Update Severity ───────────────────────────────────────────
    case 'update_severity':
        updateSeverity($db, $input, $adminId);
        break;

    // ── Chatbot: Mark reviewed / dismiss flag ─────────────────────
    case 'chatbot_review':
        handleChatbotReview($db, $input, $adminId);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Unknown moderation action']);
}

// ── Single Report Action ──────────────────────────────────────────
function handleSingleAction(PDO $db, string $action, array $input, int $adminId): void {
    $reportId = intval($input['report_id'] ?? 0);
    $note     = sanitizeInput($input['reason'] ?? $input['note'] ?? '');

    if (!$reportId) {
        echo json_encode(['success' => false, 'error' => 'Invalid report ID']);
        return;
    }

    // Confirm report exists
    $stmt = $db->prepare("SELECT id, status, report_type FROM monitoring_reports WHERE id = :id");
    $stmt->execute([':id' => $reportId]);
    $report = $stmt->fetch();

    if (!$report) {
        echo json_encode(['success' => false, 'error' => 'Report not found']);
        return;
    }

    $statusMap = [
        'approve'  => 'approved',
        'reject'   => 'rejected',
        'flag'     => 'flagged',
        'unflag'   => 'pending',
        'archive'  => 'archived',
    ];

    $newStatus = $statusMap[$action];
    $oldStatus = $report['status'];

    try {
        $db->prepare("
            UPDATE monitoring_reports
            SET status = :status,
                moderation_note = :note,
                reviewed_by     = :reviewer,
                reviewed_at     = NOW(),
                updated_at      = NOW()
            WHERE id = :id
        ")->execute([
            ':status'   => $newStatus,
            ':note'     => $note ?: null,
            ':reviewer' => $adminId,
            ':id'       => $reportId,
        ]);

        // Audit log
        $db->prepare("
            INSERT INTO audit_log
                (table_name, record_id, action, old_value, new_value, user_id, created_at)
            VALUES
                ('monitoring_reports', :rid, :action, :old, :new, :uid, NOW())
        ")->execute([
            ':rid'    => $reportId,
            ':action' => $action,
            ':old'    => $oldStatus,
            ':new'    => $newStatus,
            ':uid'    => $adminId,
        ]);

        // For critical incidents, send admin notification on approve
        if ($action === 'approve' && $report['report_type'] === 'incident') {
            $adminEmail = defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'admin@crecokenya.org';
            @mail(
                $adminEmail,
                '[PBO Hub] Incident Report Approved #' . str_pad($reportId, 5, '0', STR_PAD_LEFT),
                "An incident report has been approved and is now public.\n\nReport ID: #" . $reportId . "\nView: " . BASE_URL . "/admin/monitoring/view.php?id=" . $reportId,
                'From: noreply@crecokenya.org'
            );
        }

        echo json_encode([
            'success'    => true,
            'message'    => 'Report ' . $newStatus . ' successfully.',
            'report_id'  => $reportId,
            'new_status' => $newStatus,
        ]);

    } catch (Exception $e) {
        error_log('Moderation Error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error. Please try again.']);
    }
}

// ── Bulk Action ───────────────────────────────────────────────────
function handleBulkAction(PDO $db, string $action, array $input, int $adminId): void {
    $reportIds = $input['report_ids'] ?? [];

    if (empty($reportIds) || !is_array($reportIds)) {
        echo json_encode(['success' => false, 'error' => 'No report IDs provided']);
        return;
    }

    // Sanitize IDs — integers only
    $ids = array_filter(array_map('intval', $reportIds));
    if (empty($ids)) {
        echo json_encode(['success' => false, 'error' => 'Invalid report IDs']);
        return;
    }

    // Safety limit
    if (count($ids) > 100) {
        echo json_encode(['success' => false, 'error' => 'Maximum 100 records per bulk action']);
        return;
    }

    $singleAction = str_replace('bulk_', '', $action);
    $statusMap    = [
        'approve' => 'approved',
        'reject'  => 'rejected',
        'flag'    => 'flagged',
        'archive' => 'archived',
    ];

    $newStatus = $statusMap[$singleAction] ?? null;
    if (!$newStatus) {
        echo json_encode(['success' => false, 'error' => 'Invalid bulk action']);
        return;
    }

    try {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $stmt = $db->prepare("
            UPDATE monitoring_reports
            SET status      = ?,
                reviewed_by = ?,
                reviewed_at = NOW(),
                updated_at  = NOW()
            WHERE id IN ($placeholders)
        ");

        $params = array_merge([$newStatus, $adminId], $ids);
        $stmt->execute($params);

        $affected = $stmt->rowCount();

        // Bulk audit log
        foreach ($ids as $rid) {
            $db->prepare("
                INSERT INTO audit_log
                    (table_name, record_id, action, new_value, user_id, created_at)
                VALUES
                    ('monitoring_reports', :rid, :action, :new, :uid, NOW())
            ")->execute([
                ':rid'    => $rid,
                ':action' => 'bulk_' . $singleAction,
                ':new'    => $newStatus,
                ':uid'    => $adminId,
            ]);
        }

        echo json_encode([
            'success'  => true,
            'message'  => $affected . ' report(s) ' . $newStatus . ' successfully.',
            'affected' => $affected,
        ]);

    } catch (Exception $e) {
        error_log('Bulk Moderation Error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error during bulk action.']);
    }
}

// ── Update Severity ───────────────────────────────────────────────
function updateSeverity(PDO $db, array $input, int $adminId): void {
    $reportId    = intval($input['report_id'] ?? 0);
    $newSeverity = sanitizeInput($input['severity'] ?? '');

    $allowed = ['low', 'medium', 'high', 'critical'];
    if (!$reportId || !in_array($newSeverity, $allowed)) {
        echo json_encode(['success' => false, 'error' => 'Invalid report ID or severity level']);
        return;
    }

    try {
        $db->prepare("
            UPDATE monitoring_reports
            SET severity   = :severity,
                reviewed_by = :reviewer,
                updated_at  = NOW()
            WHERE id = :id
        ")->execute([
            ':severity' => $newSeverity,
            ':reviewer' => $adminId,
            ':id'       => $reportId,
        ]);

        $db->prepare("
            INSERT INTO audit_log
                (table_name, record_id, action, new_value, user_id, created_at)
            VALUES
                ('monitoring_reports', :rid, 'update_severity', :sev, :uid, NOW())
        ")->execute([
            ':rid' => $reportId,
            ':sev' => $newSeverity,
            ':uid' => $adminId,
        ]);

        echo json_encode([
            'success'      => true,
            'message'      => 'Severity updated to ' . ucfirst($newSeverity),
            'new_severity' => $newSeverity,
        ]);

    } catch (Exception $e) {
        error_log('Severity Update Error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to update severity']);
    }
}

// ── Chatbot Review ────────────────────────────────────────────────
function handleChatbotReview(PDO $db, array $input, int $adminId): void {
    $logId         = intval($input['log_id'] ?? 0);
    $reviewAction  = sanitizeInput($input['review_action'] ?? 'dismiss');
    $approvedAnswer = sanitizeInput($input['approved_answer'] ?? '');

    if (!$logId) {
        echo json_encode(['success' => false, 'error' => 'Invalid log ID']);
        return;
    }

    try {
        $db->prepare("UPDATE chatbot_conversations SET flagged_for_review=0, reviewed_by=:uid WHERE id=:id")
           ->execute([':uid' => $adminId, ':id' => $logId]);

        if ($reviewAction === 'add_to_kb' && $approvedAnswer) {
            $stmt = $db->prepare("SELECT user_message FROM chatbot_conversations WHERE id=:id");
            $stmt->execute([':id' => $logId]);
            $log = $stmt->fetch();

            if ($log) {
                $db->prepare("
                    INSERT INTO chatbot_knowledge_base
                        (question_pattern, keywords, answer_en, is_active, created_by, created_at, updated_at)
                    VALUES
                        (:q, '', :a, 1, :uid, NOW(), NOW())
                ")->execute([':q' => $log['user_message'], ':a' => $approvedAnswer, ':uid' => $adminId]);
            }
        }

        echo json_encode([
            'success' => true,
            'message' => $reviewAction === 'add_to_kb'
                ? 'Added to knowledge base and marked reviewed.'
                : 'Marked as reviewed.',
        ]);

    } catch (Exception $e) {
        error_log('Chatbot Review Error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to process review']);
    }
}