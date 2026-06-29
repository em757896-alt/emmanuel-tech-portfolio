<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/auth.php';

$auth = new Auth();
$auth->requireAuth();

$db = Database::getInstance();
$user = $auth->currentUser();

// Welcome messages
$welcomeMsg = '';
if (!empty($_SESSION['welcome_new'])) {
    $welcomeMsg = 'Welcome to PBO Kenya Platform! Your account has been created successfully.';
    unset($_SESSION['welcome_new']);
} elseif (!empty($_SESSION['welcome_back'])) {
    $welcomeMsg = 'Welcome back, ' . htmlspecialchars(explode(' ', $user['full_name'])[0]) . '!';
    unset($_SESSION['welcome_back']);
} else {
    // Check if this is the user's first login today
    $today = date('Y-m-d');
    $lastLogin = $user['last_login'] ? date('Y-m-d', strtotime($user['last_login'])) : '';
    if ($lastLogin !== $today) {
        $welcomeMsg = 'Good to see you, ' . htmlspecialchars(explode(' ', $user['full_name'])[0]) . '!';
    }
}

$assessments = [];
$reports = [];
try {
    $assessments = $db->fetchAll("SELECT uca.*, cc.title as checklist_title FROM user_compliance_assessments uca LEFT JOIN compliance_checklists cc ON cc.id = uca.checklist_id WHERE uca.user_id = :uid ORDER BY uca.created_at DESC LIMIT 5", ['uid' => $user['id']]);
} catch (Exception $e) {
    error_log("Dashboard assessments query failed: " . $e->getMessage());
}
try {
    $reports = $db->fetchAll("SELECT id, report_number, report_type, county, severity, status, created_at FROM monitoring_reports WHERE user_id = :uid ORDER BY created_at DESC LIMIT 5", ['uid' => $user['id']]);
} catch (Exception $e) {
    error_log("Dashboard reports query failed: " . $e->getMessage());
}

$pageTitle = 'My Dashboard - PBO Kenya';
$currentPage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .dash-header { background: linear-gradient(135deg,#1a56db,#1e40af); color: white; padding: 2rem 0; }
        .dash-card { border: none; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,.06); transition: transform .2s; height: 100%; }
        .dash-card:hover { transform: translateY(-2px); }
        .stat-circle { width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
    </style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<?php if ($welcomeMsg): ?>
<div class="py-2" style="background:linear-gradient(135deg,#059669,#10b981);color:#fff">
    <div class="container d-flex align-items-center gap-2" style="font-size:0.9rem">
        <i class="fas fa-smile"></i>
        <span><?= $welcomeMsg ?></span>
    </div>
</div>
<?php endif; ?>

<div class="dash-header">
    <div class="container">
        <div class="d-flex align-items-center gap-3">
            <div class="stat-circle bg-white text-primary">
                <i class="fas fa-user"></i>
            </div>
            <div>
                <h3 class="mb-0 fw-bold">Welcome, <?= htmlspecialchars(explode(' ', $user['full_name'])[0]) ?></h3>
                <p class="mb-0 opacity-75"><?= htmlspecialchars($user['email']) ?></p>
            </div>
            <div class="ms-auto">
                <a href="modules/compliance-tools/" class="btn btn-light btn-sm me-2"><i class="fas fa-clipboard-check me-1"></i>Compliance Check</a>
                <a href="modules/monitoring/" class="btn btn-outline-light btn-sm"><i class="fas fa-file-alt me-1"></i>Submit Report</a>
            </div>
        </div>
    </div>
</div>

<div class="py-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="dash-card card">
                    <div class="card-header bg-white border-bottom-0 pt-3">
                        <h5 class="mb-0"><i class="fas fa-file-alt text-primary me-2"></i>My Monitoring Reports</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($reports)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                            <p>No reports yet. <a href="modules/monitoring/">Submit your first report</a></p>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Type</th>
                                        <th>County</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reports as $r): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($r['report_number']) ?></td>
                                        <td><?= ucfirst(str_replace('_', ' ', $r['report_type'])) ?></td>
                                        <td><?= htmlspecialchars($r['county']) ?></td>
                                        <td><span class="badge bg-<?= $r['status'] === 'submitted' ? 'warning' : ($r['status'] === 'verified' ? 'success' : 'secondary') ?>"><?= ucfirst($r['status']) ?></span></td>
                                        <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="dash-card card mt-4">
                    <div class="card-header bg-white border-bottom-0 pt-3">
                        <h5 class="mb-0"><i class="fas fa-tasks text-success me-2"></i>My Compliance Assessments</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($assessments)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-clipboard-list fa-3x mb-3 d-block"></i>
                            <p>No assessments yet. <a href="modules/compliance-tools/">Start a compliance check</a></p>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Checklist</th>
                                        <th>Score</th>
                                        <th>Level</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assessments as $a): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($a['checklist_title']) ?></td>
                                        <td><?= $a['score_percentage'] ?>%</td>
                                        <td><span class="badge bg-<?= $a['compliance_level'] === 'excellent' ? 'success' : ($a['compliance_level'] === 'high' ? 'info' : ($a['compliance_level'] === 'medium' ? 'warning' : 'danger')) ?>"><?= ucfirst($a['compliance_level']) ?></span></td>
                                        <td><?= date('d M Y', strtotime($a['created_at'])) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="dash-card card">
                    <div class="card-body text-center p-4">
                        <div class="stat-circle bg-primary text-white mx-auto mb-3" style="width:64px;height:64px;font-size:1.5rem;">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <h5><?= htmlspecialchars($user['full_name']) ?></h5>
                        <p class="text-muted small mb-2"><?= htmlspecialchars($user['email']) ?></p>
                        <span class="badge bg-primary mb-3"><?= ucfirst(str_replace('_', ' ', $user['role'])) ?></span>
                        <hr>
                        <div class="text-start small">
                            <p class="mb-1"><strong>Organization:</strong> <?= htmlspecialchars($user['organization_name'] ?? 'N/A') ?></p>
                            <p class="mb-1"><strong>County:</strong> <?= htmlspecialchars($user['county'] ?? 'N/A') ?></p>
                            <p class="mb-0"><strong>Member since:</strong> <?= date('M Y', strtotime($user['created_at'])) ?></p>
                        </div>
                        <a href="profile.php" class="btn btn-outline-primary btn-sm w-100 mt-3"><i class="fas fa-edit me-1"></i>Edit Profile</a>
                    </div>
                </div>

                <div class="dash-card card mt-4">
                    <div class="card-body p-4">
                        <h5><i class="fas fa-link text-info me-2"></i>Quick Links</h5>
                        <div class="list-group list-group-flush">
                            <a href="modules/knowledge-hub/" class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-0 border-0">
                                <i class="fas fa-book text-primary"></i> Knowledge Hub
                            </a>
                            <a href="modules/compliance-tools/" class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-0 border-0">
                                <i class="fas fa-clipboard-check text-success"></i> Compliance Tools
                            </a>
                            <a href="modules/chatbot/" class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-0 border-0">
                                <i class="fas fa-robot text-danger"></i> AI Legal Assistant
                            </a>
                            <a href="modules/monitoring/" class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-0 border-0">
                                <i class="fas fa-shield-alt text-warning"></i> Civic Space Monitor
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init({duration:700,once:true});</script>
</body>
</html>
