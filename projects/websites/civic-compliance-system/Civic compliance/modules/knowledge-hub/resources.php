<?php
require_once '../../config/config.php';
require_once '../../config/database.php';

$db = Database::getInstance();
$type = $_GET['type'] ?? '';
$category = sanitizeInput($_GET['category'] ?? '');

$conditions = ['is_active = 1', 'is_public = 1'];
$params = [];
if ($type && in_array($type, ['toolkit','guide','template','report','infographic','video','faq'])) {
    $conditions[] = 'resource_type = :type';
    $params[':type'] = $type;
}
if ($category) {
    $conditions[] = 'category = :cat';
    $params[':cat'] = $category;
}
$where = implode(' AND ', $conditions);

$resources = $db->fetchAll("SELECT * FROM resources WHERE $where ORDER BY created_at DESC", $params);
$categories = $db->fetchAll("SELECT DISTINCT category FROM resources WHERE is_active = 1 AND category IS NOT NULL ORDER BY category");

$pageTitle = 'Resources & Toolkits - PBO Kenya';
$currentPage = 'resources';
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
                <span class="section-badge bg-white text-primary">Library</span>
                <h1 class="fw-bold display-5">Resources & Toolkits</h1>
                <p class="lead mb-0">Download guides, templates, toolkits, and infographics for your PBO.</p>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex flex-wrap gap-2">
                    <a href="resources.php" class="btn btn-sm <?= !$type ? 'btn-primary' : 'btn-outline-primary' ?>">All</a>
                    <?php foreach (['toolkit'=>'Toolkits','guide'=>'Guides','template'=>'Templates','report'=>'Reports','infographic'=>'Infographics','video'=>'Videos'] as $k => $v): ?>
                    <a href="?type=<?= $k ?>" class="btn btn-sm <?= $type === $k ? 'btn-primary' : 'btn-outline-primary' ?>"><?= $v ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php if (empty($resources)): ?>
        <div class="text-center py-5">
            <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
            <h5>No resources found</h5>
            <p class="text-muted">Check back soon for downloadable resources.</p>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($resources as $r): ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="resource-icon">
                                <?php
                                $icons = ['toolkit'=>'fa-tools','guide'=>'fa-book','template'=>'fa-file-alt','report'=>'fa-chart-bar','infographic'=>'fa-image','video'=>'fa-video','faq'=>'fa-question'];
                                $icon = $icons[$r['resource_type']] ?? 'fa-file';
                                $colors = ['toolkit'=>'primary','guide'=>'success','template'=>'warning','report'=>'danger','infographic'=>'info','video'=>'secondary','faq'=>'dark'];
                                $color = $colors[$r['resource_type']] ?? 'primary';
                                ?>
                                <i class="fas <?= $icon ?> fa-2x text-<?= $color ?>"></i>
                            </div>
                            <div>
                                <h6 class="mb-1"><?= htmlspecialchars($r['title']) ?></h6>
                                <span class="badge bg-<?= $color ?>"><?= ucfirst($r['resource_type']) ?></span>
                            </div>
                        </div>
                        <p class="small text-muted mb-3"><?= htmlspecialchars(substr($r['description'] ?? '', 0, 150)) ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted"><i class="fas fa-download me-1"></i><?= number_format($r['download_count']) ?> downloads</small>
                            <a href="../../<?= htmlspecialchars($r['file_path']) ?>" class="btn btn-sm btn-outline-primary" download>
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
