<?php
require_once '../../config/config.php';
require_once '../../config/database.php';

$db = Database::getInstance();
$templates = [];
try {
    $templates = $db->fetchAll("SELECT * FROM resources WHERE is_active = 1 AND resource_type = 'template' ORDER BY created_at DESC");
} catch (Exception $e) {
    error_log("Templates query failed: " . $e->getMessage());
}

$pageTitle = 'Templates - PBO Kenya';
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

<section class="page-hero py-5 bg-warning text-dark">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8" data-aos="fade-right">
                <span class="section-badge bg-white text-warning">Templates</span>
                <h1 class="fw-bold display-5">Downloadable Templates</h1>
                <p class="lead mb-0">Ready-to-use templates for your PBO compliance and governance needs.</p>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <?php if (empty($templates)): ?>
        <div class="text-center py-5">
            <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
            <h5>No templates available yet</h5>
            <p class="text-muted">Templates are being prepared. Check back soon.</p>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($templates as $t): ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-file-alt fa-3x text-warning mb-3"></i>
                        <h6><?= htmlspecialchars($t['title']) ?></h6>
                        <p class="small text-muted"><?= htmlspecialchars(substr($t['description'] ?? '', 0, 100)) ?></p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted">
                                <i class="fas fa-file me-1"></i><?= htmlspecialchars($t['file_name']) ?>
                            </small>
                            <a href="../../<?= htmlspecialchars($t['file_path']) ?>" class="btn btn-sm btn-warning" download>
                                <i class="fas fa-download me-1"></i>Download
                            </a>
                        </div>
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
