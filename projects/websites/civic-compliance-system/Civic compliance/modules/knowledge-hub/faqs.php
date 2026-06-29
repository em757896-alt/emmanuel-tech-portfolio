<?php
require_once '../../config/config.php';
require_once '../../config/database.php';

$db = Database::getInstance();
$category = sanitizeInput($_GET['category'] ?? '');

$conditions = ['is_published = 1'];
$params = [];
if ($category) {
    $conditions[] = 'category = :cat';
    $params[':cat'] = $category;
}
$where = implode(' AND ', $conditions);

$faqs = $db->fetchAll("SELECT * FROM faqs WHERE $where ORDER BY sort_order ASC, created_at DESC", $params);
$categories = $db->fetchAll("SELECT DISTINCT category FROM faqs WHERE is_published = 1 ORDER BY category");

$pageTitle = 'FAQs - PBO Kenya';
$currentPage = 'faqs';
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
                <span class="section-badge bg-white text-primary">FAQ</span>
                <h1 class="fw-bold display-5">Frequently Asked Questions</h1>
                <p class="lead mb-0">Common questions about the PBO Act 2013 and compliance requirements.</p>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex flex-wrap gap-2">
                    <a href="faqs.php" class="btn btn-sm <?= !$category ? 'btn-primary' : 'btn-outline-primary' ?>">All</a>
                    <?php foreach ($categories as $c): ?>
                    <a href="?category=<?= urlencode($c['category']) ?>" class="btn btn-sm <?= $category === $c['category'] ? 'btn-primary' : 'btn-outline-primary' ?>"><?= htmlspecialchars($c['category']) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php if (empty($faqs)): ?>
        <div class="text-center py-5">
            <i class="fas fa-question-circle fa-4x text-muted mb-3"></i>
            <h5>No FAQs yet</h5>
            <p class="text-muted">Check back soon for answers to common questions.</p>
        </div>
        <?php else: ?>
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="accordion" id="faqAccordion">
                    <?php foreach ($faqs as $i => $f): ?>
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-3" data-aos="fade-up">
                        <h2 class="accordion-header">
                            <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?>" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#faq<?= $f['id'] ?>">
                                <span class="me-2 text-primary fw-bold">Q:</span>
                                <?= htmlspecialchars($f['question_en']) ?>
                            </button>
                        </h2>
                        <div id="faq<?= $f['id'] ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>"
                             data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p><?= nl2br(htmlspecialchars($f['answer_en'])) ?></p>
                                <?php if ($f['pbo_act_reference']): ?>
                                <small class="text-muted">
                                    <i class="fas fa-gavel me-1"></i>Reference: <?= htmlspecialchars($f['pbo_act_reference']) ?>
                                </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
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
