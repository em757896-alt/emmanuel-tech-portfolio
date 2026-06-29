<?php
require_once '../../config/config.php';
require_once '../../config/database.php';

$db = Database::getInstance();
$resources = $db->fetchAll("SELECT * FROM resources WHERE is_active = 1 AND resource_type IN ('infographic','video') ORDER BY created_at DESC");

$pageTitle = 'Multimedia - PBO Kenya';
$currentPage = 'multimedia';
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
</head>
<body>
<?php include '../../includes/navbar.php'; ?>

<section class="page-hero py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8" data-aos="fade-right">
                <span class="section-badge bg-white text-primary">Multimedia</span>
                <h1 class="fw-bold display-5">Infographics & Videos</h1>
                <p class="lead mb-0">Visual resources to help you understand the PBO Act 2013.</p>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <?php if (empty($resources)): ?>
        <div class="text-center py-5">
            <i class="fas fa-play-circle fa-4x text-muted mb-3"></i>
            <h5>No multimedia content yet</h5>
            <p class="text-muted">Check back soon for infographics and videos explaining the PBO Act.</p>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($resources as $r): ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up">
                <div class="card border-0 shadow-sm h-100">
                    <?php if ($r['resource_type'] === 'video'): ?>
                    <div class="card-img-top bg-dark d-flex align-items-center justify-content-center" style="height:200px;">
                        <i class="fas fa-play-circle fa-4x text-white opacity-75"></i>
                    </div>
                    <?php elseif ($r['resource_type'] === 'infographic'): ?>
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height:200px;">
                        <i class="fas fa-image fa-4x text-muted opacity-50"></i>
                    </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <span class="badge bg-<?= $r['resource_type'] === 'video' ? 'danger' : 'info' ?> mb-2">
                            <?= ucfirst($r['resource_type']) ?>
                        </span>
                        <h6><?= htmlspecialchars($r['title']) ?></h6>
                        <p class="small text-muted"><?= htmlspecialchars(substr($r['description'] ?? '', 0, 120)) ?></p>
                        <a href="../../<?= htmlspecialchars($r['file_path']) ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                            <i class="fas fa-external-link-alt me-1"></i>View
                        </a>
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
