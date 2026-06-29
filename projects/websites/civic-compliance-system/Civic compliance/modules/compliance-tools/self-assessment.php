<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../config/auth.php';

$auth = new Auth();
$db = Database::getInstance();
$checklists = [];
$error = '';
$success = '';
try {
    $checklists = $db->fetchAll("SELECT * FROM compliance_checklists WHERE is_active = 1 ORDER BY category, title");
} catch (Exception $e) {
    error_log("Self-assessment checklists query failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $auth->isAuthenticated()) {
    $checklistId = intval($_POST['checklist_id'] ?? 0);
    $responses = $_POST['items'] ?? [];

    if ($checklistId && !empty($responses)) {
        $totalItems = count($responses);
        $completedItems = 0;
        $responseData = [];

        foreach ($responses as $itemId => $value) {
            $completed = ($value === 'yes') ? 1 : 0;
            $completedItems += $completed;
            $responseData[$itemId] = $completed;
        }

        $score = $totalItems > 0 ? round(($completedItems / $totalItems) * 100, 2) : 0;
        $level = $score >= 80 ? 'excellent' : ($score >= 60 ? 'high' : ($score >= 40 ? 'medium' : 'low'));

        $db->insert('user_compliance_assessments', [
            'user_id' => $_SESSION['user_id'],
            'checklist_id' => $checklistId,
            'responses' => json_encode($responseData),
            'total_items' => $totalItems,
            'completed_items' => $completedItems,
            'score_percentage' => $score,
            'compliance_level' => $level,
            'completed_at' => date('Y-m-d H:i:s'),
        ]);

        $success = "Assessment completed! Your score: $score% ($level compliance)";
    } else {
        $error = 'Please select a checklist and complete all items.';
    }
}

$pageTitle = 'Self-Assessment - PBO Kenya';
$currentPage = 'compliance';
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
    <link href="../../assets/css/style.css" rel="stylesheet">
    <link href="../../assets/css/compliance.css" rel="stylesheet">
</head>
<body>
<?php include '../../includes/navbar.php'; ?>

<section class="page-hero py-5 bg-info text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8" data-aos="fade-right">
                <span class="section-badge bg-white text-info">Assessment</span>
                <h1 class="fw-bold display-5">Compliance Self-Assessment</h1>
                <p class="lead mb-0">Evaluate your organization's compliance with the PBO Act 2013 requirements.</p>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <?php if (!$auth->isAuthenticated()): ?>
        <div class="alert alert-warning">
            <i class="fas fa-info-circle me-2"></i>
            Please <a href="../../auth/login.php" class="alert-link">login</a> to use the self-assessment tool.
        </div>
        <?php endif; ?>

        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

        <?php if (empty($checklists)): ?>
        <div class="text-center py-5">
            <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
            <h5>No checklists available yet</h5>
            <p class="text-muted">Compliance checklists are being prepared.</p>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($checklists as $cl): ?>
            <div class="col-lg-6" data-aos="fade-up">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom-0 pt-3">
                        <h5 class="mb-0"><?= htmlspecialchars($cl['title']) ?></h5>
                        <small class="text-muted"><?= htmlspecialchars($cl['description']) ?></small>
                    </div>
                    <div class="card-body">
                        <?php
                        $items = [];
                        try {
                            $items = $db->fetchAll("SELECT * FROM checklist_items WHERE checklist_id = :cid ORDER BY sort_order ASC", ['cid' => $cl['id']]);
                        } catch (Exception $e) {
                            error_log("Checklist items query failed: " . $e->getMessage());
                        }
                        if (empty($items)): ?>
                        <p class="text-muted small">No items in this checklist yet.</p>
                        <?php else: ?>
                        <form method="POST" action="">
                            <input type="hidden" name="checklist_id" value="<?= $cl['id'] ?>">
                            <?php foreach ($items as $item): ?>
                            <div class="mb-3">
                                <label class="d-flex align-items-start gap-2">
                                    <select name="items[<?= $item['id'] ?>]" class="form-select form-select-sm" style="width:auto;">
                                        <option value="">--</option>
                                        <option value="yes">Yes</option>
                                        <option value="no">No</option>
                                        <option value="na">N/A</option>
                                    </select>
                                    <span class="small"><?= htmlspecialchars($item['item_text']) ?>
                                        <?php if ($item['is_mandatory']): ?><span class="text-danger">*</span><?php endif; ?>
                                        <?php if ($item['pbo_act_reference']): ?>
                                        <br><small class="text-muted">Reference: <?= htmlspecialchars($item['pbo_act_reference']) ?></small>
                                        <?php endif; ?>
                                    </span>
                                </label>
                            </div>
                            <?php endforeach; ?>
                            <?php if ($auth->isAuthenticated()): ?>
                            <button type="submit" class="btn btn-primary btn-sm w-100 mt-3">
                                <i class="fas fa-check-circle me-1"></i>Submit Assessment
                            </button>
                            <?php endif; ?>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init({duration:700,once:true});</script>
</body>
</html>
