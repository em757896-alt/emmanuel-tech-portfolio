<?php
/**
 * admin/users/index.php
 * User Management — Admin Panel
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

$pageTitle   = 'User Management - Admin';
$currentPage = 'users';

$db = Database::getInstance()->getConnection();

// ── Handle Quick Actions ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $actionMsg  = 'Invalid security token.';
        $actionType = 'error';
    } else {
        $action = sanitizeInput($_POST['action'] ?? '');
        $userId = intval($_POST['user_id'] ?? 0);

        if ($userId && $userId !== (int)$_SESSION['user_id']) {
            switch ($action) {
                case 'activate':
                    $db->prepare("UPDATE users SET is_active=1, updated_at=NOW() WHERE id=:id")
                       ->execute([':id' => $userId]);
                    $actionMsg  = 'User activated successfully.';
                    $actionType = 'success';
                    break;

                case 'deactivate':
                    $db->prepare("UPDATE users SET is_active=0, updated_at=NOW() WHERE id=:id")
                       ->execute([':id' => $userId]);
                    $actionMsg  = 'User deactivated.';
                    $actionType = 'success';
                    break;

                case 'make_admin':
                    $db->prepare("UPDATE users SET role='admin', updated_at=NOW() WHERE id=:id")
                       ->execute([':id' => $userId]);
                    $actionMsg  = 'User promoted to Administrator.';
                    $actionType = 'success';
                    break;

                case 'make_moderator':
                    $db->prepare("UPDATE users SET role='moderator', updated_at=NOW() WHERE id=:id")
                       ->execute([':id' => $userId]);
                    $actionMsg  = 'User set as Moderator.';
                    $actionType = 'success';
                    break;

                case 'make_user':
                    $db->prepare("UPDATE users SET role='user', updated_at=NOW() WHERE id=:id")
                       ->execute([':id' => $userId]);
                    $actionMsg  = 'User role reset to Standard User.';
                    $actionType = 'success';
                    break;

                case 'verify_email':
                    $db->prepare("UPDATE users SET email_verified=1, updated_at=NOW() WHERE id=:id")
                       ->execute([':id' => $userId]);
                    $actionMsg  = 'Email manually verified.';
                    $actionType = 'success';
                    break;

                case 'delete':
                    // Soft delete — anonymize rather than remove
                    $db->prepare("
                        UPDATE users
                        SET name='[Deleted User]',
                            email=CONCAT('deleted_', id, '@removed.invalid'),
                            password='',
                            organization=NULL,
                            is_active=0,
                            updated_at=NOW()
                        WHERE id=:id
                    ")->execute([':id' => $userId]);
                    $actionMsg  = 'User account anonymized.';
                    $actionType = 'success';
                    break;

                default:
                    $actionMsg  = 'Unknown action.';
                    $actionType = 'error';
            }

            // Audit log
            $db->prepare("
                INSERT INTO audit_log (table_name, record_id, action, user_id, created_at)
                VALUES ('users', :rid, :action, :uid, NOW())
            ")->execute([
                ':rid'    => $userId,
                ':action' => $action,
                ':uid'    => $_SESSION['user_id'],
            ]);
        } else {
            $actionMsg  = 'Cannot perform action on your own account or invalid user.';
            $actionType = 'error';
        }
    }
}

// ── Filters ──────────────────────────────────────────────────────
$filterRole   = sanitizeInput($_GET['role'] ?? '');
$filterStatus = sanitizeInput($_GET['status'] ?? '');
$search       = sanitizeInput($_GET['q'] ?? '');
$page         = max(1, intval($_GET['page'] ?? 1));
$perPage      = 25;
$offset       = ($page - 1) * $perPage;

$conditions = ['1=1'];
$params     = [];

if ($filterRole) {
    $conditions[] = "role = :role";
    $params[':role'] = $filterRole;
}
if ($filterStatus === 'active') {
    $conditions[] = "is_active = 1";
} elseif ($filterStatus === 'inactive') {
    $conditions[] = "is_active = 0";
} elseif ($filterStatus === 'unverified') {
    $conditions[] = "email_verified = 0";
}
if ($search) {
    $conditions[] = "(name LIKE :q OR email LIKE :q2 OR organization LIKE :q3)";
    $params[':q']  = "%$search%";
    $params[':q2'] = "%$search%";
    $params[':q3'] = "%$search%";
}

$where = implode(' AND ', $conditions);

// Count
$countStmt = $db->prepare("SELECT COUNT(*) FROM users WHERE $where");
$countStmt->execute($params);
$totalUsers = (int)$countStmt->fetchColumn();
$totalPages = ceil($totalUsers / $perPage);

// Fetch
$params[':limit']  = $perPage;
$params[':offset'] = $offset;

$stmt = $db->prepare("
    SELECT
        id, name, email, role, organization, county,
        role_type, is_active, email_verified,
        mfa_enabled, last_login, created_at,
        (SELECT COUNT(*) FROM monitoring_reports WHERE contact_email=users.email) as report_count
    FROM users
    WHERE $where
    ORDER BY created_at DESC
    LIMIT :limit OFFSET :offset
");
$stmt->execute($params);
$users = $stmt->fetchAll();

// Summary
$summaryStmt = $db->query("
    SELECT
        COUNT(*) as total,
        SUM(role='admin') as admins,
        SUM(role='moderator') as moderators,
        SUM(role='user') as standard,
        SUM(is_active=1) as active,
        SUM(email_verified=0) as unverified,
        SUM(created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as new_30d
    FROM users
");
$summary = $summaryStmt->fetch();
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
                <span class="bc-item active">Users</span>
            </div>
        </div>
        <div class="topbar-right">
            <a href="../reports/export.php?type=users&format=csv" class="topbar-btn" title="Export users">
                <i class="fas fa-download"></i>
            </a>
        </div>
    </header>

    <div class="dashboard-content">

        <!-- Action Message -->
        <?php if (!empty($actionMsg)): ?>
        <div class="action-message action-<?php echo $actionType ?? 'info'; ?>">
            <i class="fas fa-<?php echo ($actionType ?? '') === 'success' ? 'check-circle' : 'times-circle'; ?>"></i>
            <?php echo htmlspecialchars($actionMsg); ?>
        </div>
        <?php endif; ?>

        <div class="page-header">
            <div>
                <h1>User Management</h1>
                <p>Manage registered users, roles, and account status.</p>
            </div>
        </div>

        <!-- Summary Strip -->
        <div class="summary-strip">
            <a href="?" class="strip-card <?php echo !$filterRole && !$filterStatus ? 'strip-active' : ''; ?>">
                <span class="strip-num"><?php echo number_format($summary['total']); ?></span>
                <span class="strip-label">Total Users</span>
            </a>
            <a href="?status=active" class="strip-card strip-success <?php echo $filterStatus === 'active' ? 'strip-active' : ''; ?>">
                <span class="strip-num"><?php echo $summary['active']; ?></span>
                <span class="strip-label">Active</span>
            </a>
            <a href="?role=admin" class="strip-card strip-purple <?php echo $filterRole === 'admin' ? 'strip-active' : ''; ?>">
                <span class="strip-num"><?php echo $summary['admins']; ?></span>
                <span class="strip-label">Admins</span>
            </a>
            <a href="?role=moderator" class="strip-card <?php echo $filterRole === 'moderator' ? 'strip-active' : ''; ?>">
                <span class="strip-num"><?php echo $summary['moderators']; ?></span>
                <span class="strip-label">Moderators</span>
            </a>
            <a href="?status=unverified" class="strip-card strip-warning <?php echo $filterStatus === 'unverified' ? 'strip-active' : ''; ?>">
                <span class="strip-num"><?php echo $summary['unverified']; ?></span>
                <span class="strip-label">Unverified</span>
            </a>
            <div class="strip-card">
                <span class="strip-num" style="color:#10b981"><?php echo $summary['new_30d']; ?></span>
                <span class="strip-label">New (30d)</span>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-bar">
            <form method="GET" class="filters-form">
                <div class="filter-search">
                    <i class="fas fa-search"></i>
                    <input type="text" name="q"
                           value="<?php echo htmlspecialchars($search); ?>"
                           placeholder="Search by name, email, or organization...">
                    <?php if ($search): ?>
                    <a href="?" class="filter-clear-search"><i class="fas fa-times"></i></a>
                    <?php endif; ?>
                </div>

                <select name="role" onchange="this.form.submit()">
                    <option value="">All Roles</option>
                    <?php foreach (['admin', 'moderator', 'user'] as $r): ?>
                    <option value="<?php echo $r; ?>" <?php echo $filterRole === $r ? 'selected' : ''; ?>>
                        <?php echo ucfirst($r); ?>
                    </option>
                    <?php endforeach; ?>
                </select>

                <select name="status" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="active"     <?php echo $filterStatus === 'active'     ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive"   <?php echo $filterStatus === 'inactive'   ? 'selected' : ''; ?>>Inactive</option>
                    <option value="unverified" <?php echo $filterStatus === 'unverified' ? 'selected' : ''; ?>>Unverified Email</option>
                </select>

                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <?php if ($filterRole || $filterStatus || $search): ?>
                <a href="?" class="btn btn-outline btn-sm">
                    <i class="fas fa-times"></i> Clear
                </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Results Info -->
        <div class="results-info-bar">
            <span>
                Showing <strong><?php echo number_format($offset + 1); ?></strong>–
                <strong><?php echo number_format(min($offset + $perPage, $totalUsers)); ?></strong>
                of <strong><?php echo number_format($totalUsers); ?></strong> users
            </span>
        </div>

        <!-- Users Table -->
        <div class="table-card">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Organization</th>
                            <th>County</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Verified</th>
                            <th>MFA</th>
                            <th>Reports</th>
                            <th>Last Login</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="12" class="table-empty">
                                <i class="fas fa-users"></i>
                                <span>No users found<?php echo $search ? ' matching your search' : ''; ?>.</span>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($users as $user): ?>
                        <tr id="user-row-<?php echo $user['id']; ?>">
                            <td style="font-family:monospace;font-size:0.75rem;color:#9ca3af">
                                #<?php echo str_pad($user['id'], 4, '0', STR_PAD_LEFT); ?>
                            </td>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar-sm" style="background:<?php
                                        $colors = ['#3b82f6','#10b981','#f59e0b','#8b5cf6','#ef4444'];
                                        echo $colors[$user['id'] % 5];
                                    ?>">
                                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <span class="user-name-sm"><?php echo htmlspecialchars($user['name']); ?></span>
                                        <span class="user-email-sm"><?php echo htmlspecialchars($user['email']); ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span style="font-size:0.82rem;color:#6b7280">
                                    <?php echo htmlspecialchars($user['organization'] ?? '—'); ?>
                                </span>
                            </td>
                            <td>
                                <span style="font-size:0.82rem;color:#374151">
                                    <?php echo htmlspecialchars($user['county'] ?? '—'); ?>
                                </span>
                            </td>
                            <td>
                                <span class="role-badge role-<?php echo $user['role']; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($user['is_active']): ?>
                                <span class="status-pill stat-approved">Active</span>
                                <?php else: ?>
                                <span class="status-pill stat-rejected">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['email_verified']): ?>
                                <i class="fas fa-check-circle" style="color:#10b981" title="Verified"></i>
                                <?php else: ?>
                                <i class="fas fa-times-circle" style="color:#ef4444" title="Unverified"></i>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['mfa_enabled']): ?>
                                <i class="fas fa-shield-alt" style="color:#0d6efd" title="MFA Enabled"></i>
                                <?php else: ?>
                                <span style="color:#d1d5db;font-size:0.75rem">Off</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="font-size:0.85rem;font-weight:600;color:#374151">
                                    <?php echo (int)$user['report_count']; ?>
                                </span>
                            </td>
                            <td class="date-cell">
                                <?php echo $user['last_login']
                                    ? date('d M Y', strtotime($user['last_login']))
                                    : '<span style="color:#d1d5db">Never</span>'; ?>
                            </td>
                            <td class="date-cell">
                                <?php echo date('d M Y', strtotime($user['created_at'])); ?>
                            </td>
                            <td>
                                <?php if ($user['id'] !== (int)$_SESSION['user_id']): ?>
                                <div class="action-btns">
                                    <button class="action-btn"
                                            onclick="openUserModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars(addslashes($user['name'])); ?>', '<?php echo $user['role']; ?>', <?php echo $user['is_active']; ?>, <?php echo $user['email_verified']; ?>)"
                                            title="Manage user">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                </div>
                                <?php else: ?>
                                <span style="font-size:0.72rem;color:#9ca3af">You</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="admin-pagination">
            <?php if ($page > 1): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>"
               class="page-btn"><i class="fas fa-chevron-left"></i></a>
            <?php endif; ?>

            <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $p])); ?>"
               class="page-num <?php echo $p === $page ? 'active' : ''; ?>">
                <?php echo $p; ?>
            </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>"
               class="page-btn"><i class="fas fa-chevron-right"></i></a>
            <?php endif; ?>

            <span class="page-info">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
        </div>
        <?php endif; ?>

    </div>
</main>

<!-- User Action Modal -->
<div class="modal-overlay" id="userModal">
    <div class="modal" style="max-width:460px">
        <div class="modal-header">
            <h3 id="modalUserName">Manage User</h3>
            <button class="modal-close" onclick="closeUserModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" id="userActionForm">
                <?php echo generateCSRFField(); ?>
                <input type="hidden" name="user_id" id="modalUserId">

                <div class="modal-action-grid">
                    <p style="font-size:0.85rem;color:#6b7280;margin-bottom:16px;grid-column:1/-1">
                        Select an action to perform on this user account.
                    </p>

                    <button type="submit" name="action" value="activate"
                            class="modal-action-btn modal-btn-green" id="btnActivate">
                        <i class="fas fa-check-circle"></i> Activate Account
                    </button>
                    <button type="submit" name="action" value="deactivate"
                            class="modal-action-btn modal-btn-orange" id="btnDeactivate"
                            onclick="return confirm('Deactivate this user?')">
                        <i class="fas fa-ban"></i> Deactivate Account
                    </button>
                    <button type="submit" name="action" value="verify_email"
                            class="modal-action-btn modal-btn-blue" id="btnVerify">
                        <i class="fas fa-envelope-check"></i> Verify Email
                    </button>
                    <button type="submit" name="action" value="make_admin"
                            class="modal-action-btn modal-btn-purple"
                            onclick="return confirm('Promote this user to Administrator?')">
                        <i class="fas fa-user-shield"></i> Make Admin
                    </button>
                    <button type="submit" name="action" value="make_moderator"
                            class="modal-action-btn modal-btn-teal">
                        <i class="fas fa-user-check"></i> Make Moderator
                    </button>
                    <button type="submit" name="action" value="make_user"
                            class="modal-action-btn modal-btn-gray">
                        <i class="fas fa-user"></i> Reset to User
                    </button>
                    <button type="submit" name="action" value="delete"
                            class="modal-action-btn modal-btn-red"
                            onclick="return confirm('This will anonymize the account. This cannot be undone. Continue?')"
                            style="grid-column:1/-1">
                        <i class="fas fa-trash"></i> Delete / Anonymize Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="drawer-overlay" id="modalOverlay" onclick="closeUserModal()"></div>

<script>
function openUserModal(id, name, role, isActive, isVerified) {
    document.getElementById('modalUserId').value = id;
    document.getElementById('modalUserName').textContent = 'Manage: ' + name;

    // Show/hide relevant buttons
    document.getElementById('btnActivate').style.display   = isActive   ? 'none' : '';
    document.getElementById('btnDeactivate').style.display = isActive   ? '' : 'none';
    document.getElementById('btnVerify').style.display     = isVerified ? 'none' : '';

    document.getElementById('userModal').classList.add('active');
    document.getElementById('modalOverlay').classList.add('active');
}

function closeUserModal() {
    document.getElementById('userModal').classList.remove('active');
    document.getElementById('modalOverlay').classList.remove('active');
}

// Auto-dismiss action message
const msg = document.querySelector('.action-message');
if(msg) setTimeout(() => {
    msg.style.opacity = '0';
    setTimeout(() => msg.remove(), 400);
}, 4000);
</script>

<style>
.user-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}

.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 400;
    align-items: center;
    justify-content: center;
}

.modal-overlay.active { display: flex; }

.modal {
    background: #fff;
    border-radius: 16px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 24px;
    border-bottom: 1px solid #e5e7eb;
}

.modal-header h3 { font-size: 1rem; font-weight: 600; color: #1a3c5e; }

.modal-close {
    background: none;
    border: none;
    font-size: 1.4rem;
    cursor: pointer;
    color: #9ca3af;
    line-height: 1;
}

.modal-body { padding: 20px 24px; }

.modal-action-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.modal-action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 10px 14px;
    border: none;
    border-radius: 9px;
    font-size: 0.83rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.modal-btn-green  { background: #d1fae5; color: #065f46; }
.modal-btn-green:hover  { background: #10b981; color: #fff; }
.modal-btn-orange { background: #fef3c7; color: #92400e; }
.modal-btn-orange:hover { background: #f59e0b; color: #fff; }
.modal-btn-blue   { background: #dbeafe; color: #1d4ed8; }
.modal-btn-blue:hover   { background: #3b82f6; color: #fff; }
.modal-btn-purple { background: #ede9fe; color: #5b21b6; }
.modal-btn-purple:hover { background: #8b5cf6; color: #fff; }
.modal-btn-teal   { background: #ccfbf1; color: #0f766e; }
.modal-btn-teal:hover   { background: #14b8a6; color: #fff; }
.modal-btn-gray   { background: #f3f4f6; color: #374151; }
.modal-btn-gray:hover   { background: #6b7280; color: #fff; }
.modal-btn-red    { background: #fee2e2; color: #991b1b; }
.modal-btn-red:hover    { background: #ef4444; color: #fff; }

.action-message {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 13px 18px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-size: 0.88rem;
    font-weight: 500;
    opacity: 1;
    transition: opacity 0.4s;
}

.action-success { background: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; }
.action-error   { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; }
</style>
</body>
</html>