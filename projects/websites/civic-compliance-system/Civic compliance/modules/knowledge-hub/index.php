<?php
/**
 * modules/knowledge-hub/index.php
 * Legal Knowledge Hub - PBO Compliance Hub
 * CRECO Kenya
 *
 * DB: if0_42280606_if0_42280606_
 * User: if0_42280606
 * Password: (Your vPanel Password)
 * Host: sql303.infinityfree.com
 */

require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../config/auth.php';

$pageTitle = 'Knowledge Hub - PBO Compliance Hub';
$currentPage = 'knowledge';

$db = Database::getInstance()->getConnection();

// ── Query Parameters ────────────────────────────────────────────
$search     = sanitizeInput($_GET['q'] ?? '');
$category   = sanitizeInput($_GET['category'] ?? '');
$language   = sanitizeInput($_GET['lang'] ?? 'en');
$contentType = sanitizeInput($_GET['type'] ?? '');
$page       = max(1, intval($_GET['page'] ?? 1));
$perPage    = 12;
$offset     = ($page - 1) * $perPage;

// ── Category ENUM values ──────────────────────────────────────────
$categoryLabels = [
    'pbo_act' => ['name'=>'PBO Act','color'=>'#3b82f6','icon'=>'fa-gavel'],
    'registration' => ['name'=>'Registration','color'=>'#10b981','icon'=>'fa-registered'],
    'compliance' => ['name'=>'Compliance','color'=>'#f59e0b','icon'=>'fa-clipboard-check'],
    'governance' => ['name'=>'Governance','color'=>'#8b5cf6','icon'=>'fa-users'],
    'finance' => ['name'=>'Finance','color'=>'#14b8a6','icon'=>'fa-coins'],
    'advocacy' => ['name'=>'Advocacy','color'=>'#f97316','icon'=>'fa-bullhorn'],
    'rights' => ['name'=>'Rights','color'=>'#ef4444','icon'=>'fa-scale-balanced'],
];

// ── Build Article Query ─────────────────────────────────────────
$conditions = ["a.is_published = 1"];
$params = [];

if ($search) {
    $conditions[] = "(a.title_en LIKE :search OR a.summary_en LIKE :search2 OR a.content_en LIKE :search3)";
    $params[':search']  = "%$search%";
    $params[':search2'] = "%$search%";
    $params[':search3'] = "%$search%";
}

if ($category && isset($categoryLabels[$category])) {
    $conditions[] = "a.category = :category";
    $params[':category'] = $category;
}

if ($language === 'sw') {
    $conditions[] = "a.title_sw IS NOT NULL";
}

$whereClause = implode(' AND ', $conditions);

// Count total
$countStmt = $db->prepare("
    SELECT COUNT(a.id)
    FROM knowledge_articles a
    WHERE $whereClause
");
$countStmt->execute($params);
$totalArticles = $countStmt->fetchColumn();
$totalPages = ceil($totalArticles / $perPage);

// Fetch articles
$params[':limit']  = $perPage;
$params[':offset'] = $offset;

$stmt = $db->prepare("
    SELECT
        a.id, a.title_en, a.title_sw, a.summary_en as summary, a.category,
        a.pbo_act_section, a.is_featured, a.view_count, a.created_at, a.updated_at
    FROM knowledge_articles a
    WHERE $whereClause
    ORDER BY a.is_featured DESC, a.created_at DESC
    LIMIT :limit OFFSET :offset
");
$stmt->execute($params);
$articles = $stmt->fetchAll();

// ── Featured Articles ───────────────────────────────────────────
$featuredStmt = $db->query("
    SELECT a.*
    FROM knowledge_articles a
    WHERE a.is_featured = 1 AND a.is_published = 1
    ORDER BY a.updated_at DESC
    LIMIT 3
");
$featured = $featuredStmt->fetchAll();

// ── Stats ───────────────────────────────────────────────────────
$statsStmt = $db->query("
    SELECT
        COUNT(*) as total_articles,
        SUM(CASE WHEN title_sw IS NOT NULL THEN 1 ELSE 0 END) as kiswahili_count,
        SUM(view_count) as total_views
    FROM knowledge_articles WHERE is_published=1
");
$hubStats = $statsStmt->fetch();
?>
<!DOCTYPE html>
<html lang="<?php echo $language === 'sw' ? 'sw' : 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <meta name="description" content="Kenya PBO Act legal resources, guides, FAQs and plain-language summaries in English and Kiswahili.">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/knowledge.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php include '../../includes/navbar.php'; ?>

<!-- Hero -->
<section class="knowledge-hero">
    <div class="container">
        <div class="hero-content">
            <span class="hero-badge">
                <i class="fas fa-book-open"></i> Legal Knowledge Hub
            </span>
            <h1>Understanding the PBO Act</h1>
            <p>Plain-language legal resources, guides, and tools to help Kenyan civil society organizations understand and navigate the Public Benefit Organizations Act — in English and Kiswahili.</p>

            <!-- Search Bar -->
            <form class="hub-search-form" method="GET" action="">
                <div class="search-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>"
                           placeholder="Search articles, guides, FAQs..." autocomplete="off">
                    <?php if($search): ?>
                    <a href="?" class="search-clear"><i class="fas fa-times"></i></a>
                    <?php endif; ?>
                    <button type="submit">Search</button>
                </div>
                <?php if($category): ?>
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                <?php endif; ?>
            </form>

            <!-- Quick Filters -->
            <div class="quick-filters">
                <span class="filter-label">Quick filters:</span>
                <a href="?type=guide" class="qfilter <?php echo $contentType==='guide'?'active':''; ?>">
                    <i class="fas fa-book"></i> Guides
                </a>
                <a href="?type=faq" class="qfilter <?php echo $contentType==='faq'?'active':''; ?>">
                    <i class="fas fa-question-circle"></i> FAQs
                </a>
                <a href="?type=video" class="qfilter <?php echo $contentType==='video'?'active':''; ?>">
                    <i class="fas fa-play-circle"></i> Videos
                </a>
                <a href="?lang=sw" class="qfilter <?php echo $language==='sw'?'active':''; ?>">
                    <i class="fas fa-language"></i> Kiswahili
                </a>
            </div>
        </div>

        <!-- Hub Stats -->
        <div class="hub-stats-row">
            <div class="hub-stat">
                <span class="hstat-num"><?php echo $hubStats['total_articles']; ?></span>
                <span class="hstat-label">Resources</span>
            </div>
            <div class="hub-stat">
                <span class="hstat-num"><?php echo $hubStats['kiswahili_count']; ?></span>
                <span class="hstat-label">In Kiswahili</span>
            </div>
            <div class="hub-stat">
                <span class="hstat-num"><?php echo $hubStats['video_count']; ?></span>
                <span class="hstat-label">Videos</span>
            </div>
            <div class="hub-stat">
                <span class="hstat-num"><?php echo number_format($hubStats['total_views']); ?></span>
                <span class="hstat-label">Total Views</span>
            </div>
        </div>
    </div>
</section>

<!-- Disclaimer -->
<div class="legal-disclaimer">
    <div class="container">
        <i class="fas fa-info-circle"></i>
        <p>
            <strong>Legal Disclaimer:</strong> The resources on this platform provide general legal information only and do not constitute legal advice.
            For specific legal matters, please consult a qualified legal professional.
            Information is based on the Public Benefit Organizations Act, 2013 and related regulations.
        </p>
    </div>
</div>

<!-- Featured Articles -->
<?php if(!$search && !$category && $page === 1 && !empty($featured)): ?>
<section class="featured-section">
    <div class="container">
        <div class="section-title">
            <h2><i class="fas fa-star"></i> Featured Resources</h2>
        </div>
        <div class="featured-grid">
            <?php foreach($featured as $i => $art): ?>
            <article class="featured-card <?php echo $i===0?'featured-main':''; ?>">
                <div class="featured-meta">
                    <span class="cat-tag" style="background:#3b82f620;color:#3b82f6">
                        <?php echo htmlspecialchars($categoryLabels[$art['category']]['name'] ?? 'General'); ?>
                    </span>
                    <?php if($art['title_sw']): ?>
                    <span class="lang-tag">🇰🇪 Kiswahili</span>
                    <?php endif; ?>
                </div>
                <h3>
                    <a href="article.php?id=<?php echo $art['id']; ?>&lang=<?php echo $language; ?>">
                        <?php echo htmlspecialchars($language === 'sw' && $art['title_sw'] ? $art['title_sw'] : ($art['title_en'] ?? $art['title_sw'])); ?>
                    </a>
                </h3>
                <p><?php echo htmlspecialchars($art['summary_en'] ?? ''); ?></p>
                <div class="featured-footer">
                    <a href="article.php?id=<?php echo $art['id']; ?>" class="read-link">
                        Read More <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Main Content: Sidebar + Articles -->
<section class="hub-main">
    <div class="container">
        <div class="hub-layout">

            <!-- Sidebar -->
            <aside class="hub-sidebar">

                <!-- Language Toggle -->
                <div class="sidebar-widget">
                    <h4><i class="fas fa-language"></i> Language / Lugha</h4>
                    <div class="lang-toggle">
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['lang'=>'en'])); ?>"
                           class="lang-btn <?php echo $language!=='sw'?'active':''; ?>">
                            English
                        </a>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['lang'=>'sw'])); ?>"
                           class="lang-btn <?php echo $language==='sw'?'active':''; ?>">
                            Kiswahili
                        </a>
                    </div>
                </div>

                <!-- Categories -->
                <div class="sidebar-widget">
                    <h4><i class="fas fa-folder-open"></i> Categories</h4>
                    <ul class="category-list">
                        <li>
                            <a href="?" class="cat-link <?php echo !$category?'active':''; ?>">
                                <span class="cat-icon"><i class="fas fa-th"></i></span>
                                <span>All Resources</span>
                                <span class="cat-count"><?php echo $hubStats['total_articles']; ?></span>
                            </a>
                        </li>
                        <?php foreach($categoryLabels as $slug => $cat): ?>
                        <li>
                            <a href="?category=<?php echo urlencode($slug); ?><?php echo $language==='sw'?'&lang=sw':''; ?>"
                               class="cat-link <?php echo $category===$slug?'active':''; ?>">
                                <span class="cat-icon" style="color:<?php echo htmlspecialchars($cat['color']); ?>">
                                    <i class="fas <?php echo htmlspecialchars($cat['icon']); ?>"></i>
                                </span>
                                <span><?php echo htmlspecialchars($cat['name']); ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Content Type Filter -->
                <div class="sidebar-widget">
                    <h4><i class="fas fa-filter"></i> Filter by</h4>
                    <ul class="type-filter-list">
                        <li><a href="?" class="type-link <?php echo !$search?'active':''; ?>"><i class="fas fa-th"></i> All Resources</a></li>
                        <li><a href="?q=guide" class="type-link <?php echo $search==='guide'?'active':''; ?>"><i class="fas fa-book"></i> Guides</a></li>
                        <li><a href="?q=registration" class="type-link <?php echo $search==='registration'?'active':''; ?>"><i class="fas fa-registered"></i> Registration</a></li>
                        <li><a href="?q=compliance" class="type-link <?php echo $search==='compliance'?'active':''; ?>"><i class="fas fa-clipboard-check"></i> Compliance</a></li>
                        <li><a href="?q=governance" class="type-link <?php echo $search==='governance'?'active':''; ?>"><i class="fas fa-users"></i> Governance</a></li>
                    </ul>
                </div>

                <!-- PBO Act Quick Reference -->
                <div class="sidebar-widget sidebar-widget-highlight">
                    <h4><i class="fas fa-gavel"></i> PBO Act Sections</h4>
                    <ul class="act-sections">
                        <li><a href="?q=registration+Part+II">Part II — Registration</a></li>
                        <li><a href="?q=governance+Part+IV">Part IV — Governance</a></li>
                        <li><a href="?q=finance+Part+V">Part V — Finance</a></li>
                        <li><a href="?q=reporting+Part+VI">Part VI — Reporting</a></li>
                        <li><a href="?q=PBO+Authority+Part+III">Part III — PBO Authority</a></li>
                        <li><a href="?q=offences+penalties">Part VIII — Offences</a></li>
                    </ul>
                    <a href="../../downloads/resources/pbo-act-2013.pdf" class="download-act-btn" download>
                        <i class="fas fa-download"></i> Download Full PBO Act (PDF)
                    </a>
                </div>

                <!-- Chatbot CTA -->
                <div class="sidebar-widget sidebar-cta">
                    <div class="cta-icon"><i class="fas fa-robot"></i></div>
                    <h4>Ask Our AI Assistant</h4>
                    <p>Get instant answers to your PBO Act questions from our AI assistant trained on official materials.</p>
                    <a href="../chatbot/" class="btn-cta">
                        Ask a Question <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

            </aside>

            <!-- Articles Grid -->
            <div class="hub-articles">

                <!-- Results Header -->
                <div class="results-header">
                    <div class="results-info">
                        <?php if($search): ?>
                        <h2>Search results for "<em><?php echo htmlspecialchars($search); ?></em>"</h2>
                        <span><?php echo $totalArticles; ?> result<?php echo $totalArticles !== 1 ? 's' : ''; ?> found</span>
                        <?php elseif($category): ?>
                        <h2><?php
                            $catName = '';
                            foreach($categories as $c) {
                                if($c['slug'] === $category) { $catName = $c['name']; break; }
                            }
                            echo htmlspecialchars($catName);
                        ?></h2>
                        <span><?php echo $totalArticles; ?> resource<?php echo $totalArticles !== 1 ? 's' : ''; ?></span>
                        <?php else: ?>
                        <h2><?php echo $language === 'sw' ? 'Rasilimali Zote' : 'All Resources'; ?></h2>
                        <span><?php echo $totalArticles; ?> resources available</span>
                        <?php endif; ?>
                    </div>
                    <div class="results-sort">
                        <label>Sort by:</label>
                        <select onchange="location.href='?<?php echo $search?'q='.urlencode($search).'&':''; ?>sort='+this.value">
                            <option value="latest">Latest</option>
                            <option value="popular">Most Viewed</option>
                            <option value="az">A–Z</option>
                        </select>
                    </div>
                </div>

                <!-- Articles -->
                <?php if(empty($articles)): ?>
                <div class="no-results">
                    <div class="no-results-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>No resources found</h3>
                    <p>Try different search terms or browse by category.</p>
                    <a href="?" class="btn btn-primary">Browse All Resources</a>
                </div>
                <?php else: ?>

                <div class="articles-grid">
                    <?php foreach($articles as $article):
                        $catInfo = $categoryLabels[$article['category']] ?? ['name'=>'General','color'=>'#6b7280','icon'=>'fa-file'];
                    ?>
                    <article class="article-card">

                        <div class="card-type-bar"
                             style="background: <?php echo htmlspecialchars($catInfo['color']); ?>">
                        </div>

                        <div class="card-body">
                            <div class="card-meta-top">
                                <span class="card-category"
                                      style="color:<?php echo htmlspecialchars($catInfo['color']); ?>">
                                    <i class="fas <?php echo htmlspecialchars($catInfo['icon']); ?>"></i>
                                    <?php echo htmlspecialchars($catInfo['name']); ?>
                                </span>
                                <span class="content-type-badge ct-article">
                                    <i class="fas fa-file-alt"></i>
                                    Article
                                </span>
                            </div>

                            <h3 class="card-title">
                                <a href="article.php?id=<?php echo $article['id']; ?>&lang=<?php echo $language; ?>">
                                    <?php echo htmlspecialchars($language === 'sw' && $article['title_sw'] ? $article['title_sw'] : $article['title_en']); ?>
                                </a>
                            </h3>

                            <p class="card-summary"><?php echo htmlspecialchars($article['summary']); ?></p>

                            <div class="card-footer">
                                <div class="card-badges">
                                    <?php if($article['pbo_act_section']): ?>
                                    <span class="act-ref">§ <?php echo htmlspecialchars($article['pbo_act_section']); ?></span>
                                    <?php endif; ?>
                                    <?php if($article['title_sw']): ?>
                                    <span class="sw-badge">🇰🇪 KSW</span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-stats">
                                    <span><i class="fas fa-eye"></i> <?php echo number_format($article['view_count']); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="card-actions">
                            <a href="article.php?id=<?php echo $article['id']; ?>&lang=<?php echo $language; ?>"
                               class="card-read-btn">
                                <?php echo $language === 'sw' ? 'Soma Zaidi' : 'Read More'; ?>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if($totalPages > 1): ?>
                <div class="pagination">
                    <?php if($page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page'=>$page-1])); ?>"
                       class="page-btn page-prev">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                    <?php endif; ?>

                    <div class="page-numbers">
                        <?php for($p = max(1, $page-2); $p <= min($totalPages, $page+2); $p++): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page'=>$p])); ?>"
                           class="page-num <?php echo $p === $page ? 'active' : ''; ?>">
                            <?php echo $p; ?>
                        </a>
                        <?php endfor; ?>
                    </div>

                    <?php if($page < $totalPages): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page'=>$page+1])); ?>"
                       class="page-btn page-next">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                    <?php endif; ?>

                    <span class="page-info">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                </div>
                <?php endif; ?>

                <?php endif; ?>

            </div><!-- /.hub-articles -->
        </div><!-- /.hub-layout -->
    </div>
</section>

<!-- Multimedia Section -->
<?php if(!$search && !$category): ?>
<section class="multimedia-section">
    <div class="container">
        <div class="section-title">
            <h2><i class="fas fa-play-circle"></i> Multimedia Resources</h2>
            <p>Infographics, videos, and visual explainers on the PBO Act</p>
        </div>
        <div class="multimedia-grid">
            <div class="media-card media-video">
                <div class="media-thumb">
                    <div class="play-btn"><i class="fas fa-play"></i></div>
                    <div class="media-duration">5:32</div>
                </div>
                <div class="media-info">
                    <span class="media-type"><i class="fas fa-play-circle"></i> Video</span>
                    <h4>Introduction to the PBO Act 2013</h4>
                    <p>An overview of Kenya's Public Benefit Organizations Act and what it means for civil society.</p>
                </div>
            </div>
            <div class="media-card media-infographic">
                <div class="media-thumb media-thumb-infographic">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="media-info">
                    <span class="media-type"><i class="fas fa-image"></i> Infographic</span>
                    <h4>PBO Registration Process — Step by Step</h4>
                    <p>Visual guide to navigating the registration process from application to certificate.</p>
                </div>
            </div>
            <div class="media-card media-video">
                <div class="media-thumb">
                    <div class="play-btn"><i class="fas fa-play"></i></div>
                    <div class="media-duration">8:14</div>
                </div>
                <div class="media-info">
                    <span class="media-type"><i class="fas fa-play-circle"></i> Video</span>
                    <h4>Governance Requirements for PBOs (Kiswahili)</h4>
                    <p>Mwongozo wa mahitaji ya usimamizi kwa mashirika yanayofanya kazi chini ya Sheria ya PBO.</p>
                </div>
            </div>
            <div class="media-card media-infographic">
                <div class="media-thumb media-thumb-infographic" style="background:#8b5cf620">
                    <i class="fas fa-money-bill-wave" style="color:#8b5cf6"></i>
                </div>
                <div class="media-info">
                    <span class="media-type"><i class="fas fa-image"></i> Infographic</span>
                    <h4>Financial Compliance at a Glance</h4>
                    <p>Key financial reporting and audit obligations under the PBO Act visualized.</p>
                </div>
            </div>
        </div>
        <div class="multimedia-cta">
            <a href="?type=video" class="btn btn-outline">
                <i class="fas fa-play-circle"></i> All Videos
            </a>
            <a href="?type=infographic" class="btn btn-outline">
                <i class="fas fa-image"></i> All Infographics
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>

<script>
// Live search with debounce
let searchTimeout;
const searchInput = document.querySelector('.hub-search-form input[name="q"]');
if(searchInput) {
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        if(this.value.length > 2) {
            searchTimeout = setTimeout(() => {
                // Could implement AJAX live search here
            }, 400);
        }
    });
}

// Article card hover effects
document.querySelectorAll('.article-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-4px)';
    });
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
    });
});

// Track article views
document.querySelectorAll('.card-read-btn, .card-title a').forEach(link => {
    link.addEventListener('click', function() {
        const articleId = this.href.match(/id=(\d+)/)?.[1];
        if(articleId) {
            navigator.sendBeacon('../../api/track-view.php', JSON.stringify({ article_id: articleId }));
        }
    });
});
</script>
</body>
</html>