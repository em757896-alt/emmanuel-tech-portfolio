<?php
require_once 'config/config.php';
require_once 'config/database.php';

$db = Database::getInstance();
$query = trim($_GET['q'] ?? '');
$results = ['articles' => [], 'faqs' => [], 'resources' => []];
$totalResults = 0;

if (!empty($query)) {
    $searchTerm = '%' . $query . '%';

    $results['articles'] = $db->fetchAll(
        "SELECT id, title_en, summary_en, slug, category, is_featured FROM knowledge_articles
         WHERE is_published = 1 AND (title_en LIKE :q OR content_en LIKE :q2 OR summary_en LIKE :q3)
         ORDER BY is_featured DESC, view_count DESC LIMIT 10",
        ['q' => $searchTerm, 'q2' => $searchTerm, 'q3' => $searchTerm]
    );

    $results['faqs'] = $db->fetchAll(
        "SELECT id, question_en, answer_en, category FROM faqs
         WHERE is_published = 1 AND (question_en LIKE :q OR answer_en LIKE :q2)
         LIMIT 10",
        ['q' => $searchTerm, 'q2' => $searchTerm]
    );

    $results['resources'] = $db->fetchAll(
        "SELECT id, title, description, file_name, resource_type FROM resources
         WHERE is_active = 1 AND (title LIKE :q OR description LIKE :q2)
         LIMIT 10",
        ['q' => $searchTerm, 'q2' => $searchTerm]
    );

    $totalResults = count($results['articles']) + count($results['faqs']) + count($results['resources']);
}

$pageTitle = !empty($query) ? "Search: \"$query\" - PBO Kenya" : 'Search - PBO Kenya';
$currentPage = 'search';
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
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<section class="page-hero py-4 bg-primary text-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <form action="search.php" method="GET">
                    <div class="input-group input-group-lg">
                        <input type="text" name="q" class="form-control"
                               placeholder="Search articles, FAQs, resources..."
                               value="<?= htmlspecialchars($query) ?>" autofocus>
                        <button type="submit" class="btn btn-light px-4">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="py-4">
    <div class="container">
        <?php if (!empty($query)): ?>
            <?php if ($totalResults === 0): ?>
            <div class="text-center py-5">
                <i class="fas fa-search fa-4x text-muted mb-3"></i>
                <h5>No results found</h5>
                <p class="text-muted">Try different keywords or browse our modules below.</p>
                <div class="d-flex gap-2 justify-content-center mt-3">
                    <a href="modules/knowledge-hub/" class="btn btn-outline-primary">Knowledge Hub</a>
                    <a href="modules/knowledge-hub/faqs.php" class="btn btn-outline-primary">FAQs</a>
                    <a href="modules/knowledge-hub/resources.php" class="btn btn-outline-primary">Resources</a>
                </div>
            </div>
            <?php else: ?>
            <p class="text-muted mb-4">Found <strong><?= $totalResults ?></strong> result(s) for "<?= htmlspecialchars($query) ?>"</p>

            <div class="row g-4">
                <?php if (!empty($results['articles'])): ?>
                <div class="col-12">
                    <h5 class="mb-3"><i class="fas fa-book text-primary me-2"></i>Articles</h5>
                    <?php foreach ($results['articles'] as $a): ?>
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="mb-1">
                                        <a href="modules/knowledge-hub/article.php?slug=<?= $a['slug'] ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($a['title_en']) ?>
                                        </a>
                                    </h6>
                                    <small class="text-muted"><?= htmlspecialchars(substr($a['summary_en'] ?? '', 0, 200)) ?></small>
                                </div>
                                <span class="badge bg-primary align-self-start ms-2"><?= ucfirst(str_replace('_', ' ', $a['category'])) ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($results['faqs'])): ?>
                <div class="col-12">
                    <h5 class="mb-3"><i class="fas fa-question-circle text-warning me-2"></i>FAQs</h5>
                    <?php foreach ($results['faqs'] as $f): ?>
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <h6 class="mb-1"><?= htmlspecialchars($f['question_en']) ?></h6>
                            <small class="text-muted"><?= htmlspecialchars(substr(strip_tags($f['answer_en']), 0, 200)) ?></small>
                            <div><small class="text-muted">Category: <?= htmlspecialchars($f['category']) ?></small></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($results['resources'])): ?>
                <div class="col-12">
                    <h5 class="mb-3"><i class="fas fa-download text-success me-2"></i>Resources</h5>
                    <?php foreach ($results['resources'] as $r): ?>
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1"><?= htmlspecialchars($r['title']) ?></h6>
                                <small class="text-muted"><?= htmlspecialchars($r['file_name']) ?></small>
                            </div>
                            <span class="badge bg-success"><?= ucfirst($r['resource_type']) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-search fa-4x text-muted mb-3"></i>
            <h5>Search the PBO Kenya Platform</h5>
            <p class="text-muted">Find legal articles, FAQs, and resources about the PBO Act 2013.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init({duration:700,once:true});</script>
</body>
</html>
