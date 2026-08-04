<?php
/**
 * modules/knowledge-hub/article.php
 * Single Article View â€” Knowledge Hub
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

$db = Database::getInstance()->getConnection();

$articleId = intval($_GET['id'] ?? 0);
$language  = sanitizeInput($_GET['lang'] ?? 'en');

if (!$articleId) {
    header('Location: index.php');
    exit;
}

// â”€â”€ Fetch Article â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$stmt = $db->prepare("
    SELECT a.*
    FROM knowledge_articles a
    WHERE a.id = :id AND a.is_published = 1
");
$stmt->execute([':id' => $articleId]);
$article = $stmt->fetch();

if (!$article) {
    header('Location: index.php?error=not_found');
    exit;
}

// â”€â”€ Increment View Count â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$db->prepare("UPDATE knowledge_articles SET view_count = view_count + 1 WHERE id = :id")
   ->execute([':id' => $articleId]);

// Track page view
$clientIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
try {
    $db->prepare("
        INSERT INTO page_views (page_path, ip_hash, device_type, visited_at)
        VALUES (:path, :ip, :device, NOW())
    ")->execute([
        ':path'   => '/modules/knowledge-hub/article.php?id=' . $articleId,
        ':ip'     => md5($clientIp),
        ':device' => (strpos($_SERVER['HTTP_USER_AGENT'] ?? '', 'Mobile') !== false ? 'mobile' : 'desktop'),
    ]);
} catch(Exception $e) { /* Non-blocking */ }

// â”€â”€ Related Articles â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$stmt = $db->prepare("
    SELECT id, title_en AS title, title_sw, summary_en AS summary,
           view_count
    FROM knowledge_articles
    WHERE category = :cat
      AND id != :id
      AND is_published = 1
    ORDER BY view_count DESC
    LIMIT 4
");
$stmt->execute([':cat' => $article['category'], ':id' => $articleId]);
$relatedArticles = $stmt->fetchAll();

// â”€â”€ Prev / Next Navigation â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$prevStmt = $db->prepare("
    SELECT id, title_en AS title FROM knowledge_articles
    WHERE id < :id AND is_published=1 AND category=:cat
    ORDER BY id DESC LIMIT 1
");
$prevStmt->execute([':id'=>$articleId, ':cat'=>$article['category']]);
$prevArticle = $prevStmt->fetch();

$nextStmt = $db->prepare("
    SELECT id, title_en AS title FROM knowledge_articles
    WHERE id > :id AND is_published=1 AND category=:cat
    ORDER BY id ASC LIMIT 1
");
$nextStmt->execute([':id'=>$articleId, ':cat'=>$article['category']]);
$nextArticle = $nextStmt->fetch();

// â”€â”€ FAQs linked to this article â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$faqStmt = $db->prepare("
    SELECT question, answer, question_sw, answer_sw
    FROM faqs
    WHERE linked_article_id = :id AND is_active = 1
    ORDER BY sort_order ASC
");
$faqStmt->execute([':id' => $articleId]);
$linkedFaqs = $faqStmt->fetchAll();

// Determine display language
$displayTitle   = ($language === 'sw' && !empty($article['title_sw']))
                  ? $article['title_sw'] : $article['title_en'];
$displayContent = ($language === 'sw' && !empty($article['content_sw']))
                  ? $article['content_sw'] : $article['content_en'];

// Category labels
$catLabels = [
    'pbo_act' => 'PBO Act',
    'ngo_regulations' => 'NGO Regulations',
    'compliance' => 'Compliance',
    'governance' => 'Governance',
    'financial' => 'Financial Management',
    'reporting' => 'Reporting',
    'legal_updates' => 'Legal Updates',
    'resources' => 'Resources',
];
$categoryLabel = $catLabels[$article['category']] ?? $article['category'];

$pageTitle = htmlspecialchars($displayTitle) . ' - PBO Knowledge Hub';
$currentPage = 'knowledge';
?>
<!DOCTYPE html>
<html lang="<?php echo $language === 'sw' ? 'sw' : 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($article['summary_en']); ?>">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/knowledge.css">
    <link rel="stylesheet" href="../../assets/css/article.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
<?php include '../../includes/navbar.php'; ?>

<!-- Breadcrumb -->
<div class="article-breadcrumb">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Breadcrumb">
            <a href="../../index.php"><i class="fas fa-home"></i></a>
            <span class="bc-sep"><i class="fas fa-chevron-right"></i></span>
            <a href="index.php">Knowledge Hub</a>
            <span class="bc-sep"><i class="fas fa-chevron-right"></i></span>
            <?php if($article['category']): ?>
            <a href="index.php?category=<?php echo urlencode($article['category']); ?>">
                <?php echo htmlspecialchars($categoryLabel); ?>
            </a>
            <span class="bc-sep"><i class="fas fa-chevron-right"></i></span>
            <?php endif; ?>
            <span class="bc-current"><?php echo htmlspecialchars($displayTitle); ?></span>
        </nav>
    </div>
</div>

<!-- Article Layout -->
<div class="article-layout">
    <div class="container">
        <div class="article-grid">

            <!-- Main Article -->
            <article class="article-main">

                <!-- Article Header -->
                <header class="article-header">
                    <div class="article-meta-top">
                        <?php if($article['category']): ?>
                        <span class="article-category">
                            <i class="fas fa-folder"></i>
                            <?php echo htmlspecialchars($categoryLabel); ?>
                        </span>
                        <?php endif; ?>

                        <?php if($article['pbo_act_section']): ?>
                        <span class="article-act-ref">
                            <i class="fas fa-gavel"></i>
                            PBO Act <?php echo htmlspecialchars($article['pbo_act_section']); ?>
                        </span>
                        <?php endif; ?>

                        <?php if(!empty($article['title_sw'])): ?>
                        <div class="lang-switch-inline">
                            <a href="?id=<?php echo $articleId; ?>&lang=en"
                               class="lang-sw-btn <?php echo $language!=='sw'?'active':''; ?>">EN</a>
                            <a href="?id=<?php echo $articleId; ?>&lang=sw"
                               class="lang-sw-btn <?php echo $language==='sw'?'active':''; ?>">SW</a>
                        </div>
                        <?php endif; ?>
                    </div>

                    <h1 class="article-title"><?php echo htmlspecialchars($displayTitle); ?></h1>

                    <p class="article-summary"><?php echo htmlspecialchars($article['summary_en']); ?></p>

                    <div class="article-meta-bar">
                        <div class="meta-items">
                            <span class="meta-item">
                                <i class="fas fa-clock"></i>
                                <?php echo max(1, round(str_word_count(strip_tags($article['content_en'])) / 200)); ?> min read
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-eye"></i>
                                <?php echo number_format($article['view_count']); ?> views
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-calendar-alt"></i>
                                Updated <?php echo date('d M Y', strtotime($article['updated_at'])); ?>
                            </span>
                        </div>
                        <div class="share-buttons">
                            <button class="share-btn" onclick="shareArticle('copy')" title="Copy link">
                                <i class="fas fa-link"></i>
                            </button>
                            <button class="share-btn" onclick="shareArticle('twitter')" title="Share on Twitter">
                                <i class="fab fa-twitter"></i>
                            </button>
                            <button class="share-btn" onclick="shareArticle('whatsapp')" title="Share on WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </button>
                            <button class="share-btn" onclick="window.print()" title="Print">
                                <i class="fas fa-print"></i>
                            </button>
                        </div>
                    </div>
                </header>

                <!-- Legal Disclaimer -->
                <div class="article-disclaimer">
                    <i class="fas fa-info-circle"></i>
                    <p>This article provides general legal information only and does not constitute legal advice.
                       For specific matters, consult a qualified legal professional.</p>
                </div>

                <!-- Reading Progress Bar -->
                <div class="reading-progress" id="readingProgress">
                    <div class="progress-fill" id="progressFill"></div>
                </div>

                <!-- Article Content -->
                <div class="article-content" id="articleContent">
                    <?php echo $displayContent; // Content stored as HTML in DB ?>
                </div>



                <!-- Linked FAQs -->
                <?php if(!empty($linkedFaqs)): ?>
                <div class="article-faqs">
                    <h3><i class="fas fa-question-circle"></i> Frequently Asked Questions</h3>
                    <div class="faq-accordion">
                        <?php foreach($linkedFaqs as $i => $faq):
                            $q = ($language === 'sw' && !empty($faq['question_sw'])) ? $faq['question_sw'] : $faq['question'];
                            $a = ($language === 'sw' && !empty($faq['answer_sw']))   ? $faq['answer_sw']   : $faq['answer'];
                        ?>
                        <div class="faq-item" id="faq-<?php echo $i; ?>">
                            <button class="faq-question" onclick="toggleFAQ(<?php echo $i; ?>)">
                                <span><?php echo htmlspecialchars($q); ?></span>
                                <i class="fas fa-chevron-down faq-arrow"></i>
                            </button>
                            <div class="faq-answer" id="faq-answer-<?php echo $i; ?>">
                                <div class="faq-answer-inner">
                                    <?php echo nl2br(htmlspecialchars($a)); ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Article Actions -->
                <div class="article-actions">
                    <a href="../../api/download.php?article_id=<?php echo $articleId; ?>"
                       class="action-btn action-download" download>
                        <i class="fas fa-download"></i> Download PDF
                    </a>
                    <a href="../chatbot/?prefill=<?php echo urlencode('Tell me more about ' . $displayTitle); ?>"
                       class="action-btn action-chat">
                        <i class="fas fa-robot"></i> Ask AI Assistant
                    </a>
                    <a href="../compliance-tools/" class="action-btn action-compliance">
                        <i class="fas fa-tasks"></i> Check Compliance
                    </a>
                </div>

                <!-- Feedback -->
                <div class="article-feedback">
                    <p>Was this article helpful?</p>
                    <div class="feedback-buttons">
                        <button class="feedback-btn feedback-yes" onclick="submitFeedback('helpful', <?php echo $articleId; ?>)">
                            <i class="fas fa-thumbs-up"></i> Yes, helpful
                        </button>
                        <button class="feedback-btn feedback-no" onclick="submitFeedback('not_helpful', <?php echo $articleId; ?>)">
                            <i class="fas fa-thumbs-down"></i> Not helpful
                        </button>
                    </div>
                    <div class="feedback-thanks" id="feedbackThanks" style="display:none">
                        <i class="fas fa-heart" style="color:#10b981"></i>
                        Thank you for your feedback!
                    </div>
                </div>

                <!-- Prev/Next Navigation -->
                <div class="article-nav">
                    <?php if($prevArticle): ?>
                    <a href="article.php?id=<?php echo $prevArticle['id']; ?>&lang=<?php echo $language; ?>"
                       class="article-nav-btn article-nav-prev">
                        <span class="nav-direction"><i class="fas fa-arrow-left"></i> Previous</span>
                        <span class="nav-title"><?php echo htmlspecialchars($prevArticle['title']); ?></span>
                    </a>
                    <?php else: ?>
                    <div></div>
                    <?php endif; ?>

                    <?php if($nextArticle): ?>
                    <a href="article.php?id=<?php echo $nextArticle['id']; ?>&lang=<?php echo $language; ?>"
                       class="article-nav-btn article-nav-next">
                        <span class="nav-direction">Next <i class="fas fa-arrow-right"></i></span>
                        <span class="nav-title"><?php echo htmlspecialchars($nextArticle['title']); ?></span>
                    </a>
                    <?php endif; ?>
                </div>

            </article>

            <!-- Sidebar -->
            <aside class="article-sidebar">

                <!-- Table of Contents -->
                <div class="sidebar-widget toc-widget">
                    <h4><i class="fas fa-list"></i> Table of Contents</h4>
                    <nav id="tableOfContents" class="toc-nav">
                        <p class="toc-loading"><i class="fas fa-spinner fa-spin"></i> Loading...</p>
                    </nav>
                </div>

                <!-- Act Reference -->
                <?php if($article['pbo_act_section']): ?>
                <div class="sidebar-widget act-ref-widget">
                    <h4><i class="fas fa-gavel"></i> Legal Reference</h4>
                    <div class="act-ref-card">
                        <div class="act-ref-section">
                            <?php echo htmlspecialchars($article['pbo_act_section']); ?>
                        </div>
                        <p>Public Benefit Organizations Act, 2013</p>
                        <a href="../../downloads/resources/pbo-act-2013.pdf" download class="act-download-btn">
                            <i class="fas fa-download"></i> Download Full Act
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Related Articles -->
                <?php if(!empty($relatedArticles)): ?>
                <div class="sidebar-widget related-widget">
                    <h4><i class="fas fa-link"></i> Related Articles</h4>
                    <div class="related-articles-list">
                        <?php foreach($relatedArticles as $rel):
                            $relTitle = ($language==='sw' && !empty($rel['title_sw'])) ? $rel['title_sw'] : $rel['title'];
                        ?>
                        <a href="article.php?id=<?php echo $rel['id']; ?>&lang=<?php echo $language; ?>"
                           class="related-article-item">
                            <div class="rel-content">
                                <span class="rel-title"><?php echo htmlspecialchars($relTitle); ?></span>
                                <span class="rel-meta">
                                    <i class="fas fa-eye"></i> <?php echo number_format($rel['view_count']); ?>
                                </span>
                            </div>
                            <i class="fas fa-chevron-right rel-arrow"></i>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Quick Tools CTA -->
                <div class="sidebar-widget cta-widget">
                    <div class="cta-widget-icon"><i class="fas fa-tasks"></i></div>
                    <h4>Check Your Compliance</h4>
                    <p>Use our interactive checklist to assess your organization's compliance with this section of the PBO Act.</p>
                    <a href="../compliance-tools/" class="cta-widget-btn">
                        Open Compliance Tools <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

            </aside>
        </div><!-- /.article-grid -->
    </div>
</div><!-- /.article-layout -->

<!-- Related Articles Full Row -->
<?php if(!empty($relatedArticles)): ?>
<section class="related-section">
    <div class="container">
        <h2><i class="fas fa-book-open"></i> More in
            <?php echo htmlspecialchars($categoryLabel ?? 'Knowledge Hub'); ?>
        </h2>
        <div class="related-grid">
            <?php foreach($relatedArticles as $rel):
                $relTitle = ($language==='sw' && !empty($rel['title_sw'])) ? $rel['title_sw'] : $rel['title'];
            ?>
            <a href="article.php?id=<?php echo $rel['id']; ?>&lang=<?php echo $language; ?>"
               class="related-card">
                <h3><?php echo htmlspecialchars($relTitle); ?></h3>
                <p><?php echo htmlspecialchars($rel['summary']); ?></p>
                <div class="related-card-meta">
                    <span class="read-more-link">Read More <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>

<script>
// â”€â”€ Reading Progress Bar â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
window.addEventListener('scroll', () => {
    const article = document.getElementById('articleContent');
    const fill    = document.getElementById('progressFill');
    if(!article || !fill) return;

    const top    = article.getBoundingClientRect().top + window.scrollY;
    const height = article.offsetHeight;
    const scrolled = window.scrollY - top;
    const pct    = Math.min(100, Math.max(0, (scrolled / height) * 100));
    fill.style.width = pct + '%';
});

// â”€â”€ Table of Contents Generator â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
document.addEventListener('DOMContentLoaded', () => {
    const content = document.getElementById('articleContent');
    const toc     = document.getElementById('tableOfContents');
    if(!content || !toc) return;

    const headings = content.querySelectorAll('h2, h3');
    if(headings.length === 0) {
        toc.innerHTML = '<p style="color:#9ca3af;font-size:0.8rem">No sections found.</p>';
        return;
    }

    let tocHtml = '<ul class="toc-list">';
    headings.forEach((h, i) => {
        const id  = 'section-' + i;
        h.id      = id;
        const cls = h.tagName === 'H3' ? 'toc-h3' : 'toc-h2';
        tocHtml  += `<li class="${cls}"><a href="#${id}">${h.textContent}</a></li>`;
    });
    tocHtml += '</ul>';
    toc.innerHTML = tocHtml;

    // Highlight active section on scroll
    const tocLinks = toc.querySelectorAll('a');
    window.addEventListener('scroll', () => {
        let current = '';
        headings.forEach(h => {
            if(window.scrollY >= h.offsetTop - 120) current = h.id;
        });
        tocLinks.forEach(a => {
            a.classList.toggle('toc-active', a.getAttribute('href') === '#' + current);
        });
    });
});

// â”€â”€ FAQ Accordion â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function toggleFAQ(i) {
    const answer = document.getElementById('faq-answer-' + i);
    const arrow  = document.querySelector('#faq-' + i + ' .faq-arrow');
    const isOpen = answer.classList.contains('open');

    document.querySelectorAll('.faq-answer').forEach(a => a.classList.remove('open'));
    document.querySelectorAll('.faq-arrow').forEach(a => a.style.transform = '');

    if(!isOpen) {
        answer.classList.add('open');
        if(arrow) arrow.style.transform = 'rotate(180deg)';
    }
}

// â”€â”€ Share Article â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function shareArticle(type) {
    const url   = window.location.href;
    const title = document.title;
    if(type === 'copy') {
        navigator.clipboard.writeText(url).then(() => showToast('Link copied!'));
    } else if(type === 'twitter') {
        window.open('https://twitter.com/intent/tweet?url=' + encodeURIComponent(url) + '&text=' + encodeURIComponent(title));
    } else if(type === 'whatsapp') {
        window.open('https://wa.me/?text=' + encodeURIComponent(title + ' ' + url));
    }
}

// â”€â”€ Feedback â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
async function submitFeedback(type, id) {
    document.querySelectorAll('.feedback-btn').forEach(b => b.disabled = true);
    try {
        await fetch('../../api/track-view.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ type: 'feedback', article_id: id, feedback: type })
        });
    } catch(e) {}
    document.getElementById('feedbackThanks').style.display = 'flex';
    document.querySelector('.feedback-buttons').style.display = 'none';
}

// â”€â”€ Toast â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function showToast(msg) {
    const t = document.createElement('div');
    t.className = 'article-toast';
    t.textContent = msg;
    document.body.appendChild(t);
    requestAnimationFrame(() => t.classList.add('show'));
    setTimeout(() => { t.classList.remove('show'); setTimeout(()=>t.remove(),300); }, 2500);
}
</script>

<style>
/* Inline article-specific styles */
.article-breadcrumb {
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
    padding: 12px 0;
}

.breadcrumb-nav {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8rem;
    flex-wrap: wrap;
}

.breadcrumb-nav a { color: #6b7280; text-decoration: none; }
.breadcrumb-nav a:hover { color: #0d6efd; }
.bc-sep { color: #d1d5db; font-size: 0.65rem; }
.bc-current { color: #374151; font-weight: 500; max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.article-layout { background: #f1f5f9; padding: 40px 0 80px; }

.article-grid {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 28px;
    align-items: start;
}

.article-main {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 1px 6px rgba(0,0,0,0.06);
    border: 1px solid #e5e7eb;
}

.article-header { padding: 32px 36px 0; }

.article-meta-top {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}

.article-category {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
}

.article-act-ref {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #eff6ff;
    color: #1d4ed8;
    padding: 4px 10px;
    border-radius: 50px;
    font-size: 0.72rem;
    font-weight: 700;
}

.lang-switch-inline {
    display: flex;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    overflow: hidden;
    margin-left: auto;
}

.lang-sw-btn {
    padding: 4px 12px;
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    text-decoration: none;
    background: #fff;
    transition: all 0.2s;
}

.lang-sw-btn.active { background: #0d6efd; color: #fff; }

.article-title {
    font-size: 1.9rem;
    font-weight: 700;
    color: #1a3c5e;
    line-height: 1.3;
    margin-bottom: 14px;
    font-family: 'Merriweather', Georgia, serif;
}

.article-summary {
    font-size: 1rem;
    color: #6b7280;
    line-height: 1.7;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f3f4f6;
}

.article-meta-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 20px;
    flex-wrap: wrap;
    gap: 12px;
}

.meta-items { display: flex; gap: 16px; flex-wrap: wrap; }

.meta-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.78rem;
    color: #9ca3af;
}

.meta-item i { font-size: 0.72rem; }

.share-buttons { display: flex; gap: 6px; }

.share-btn {
    width: 32px;
    height: 32px;
    background: #f3f4f6;
    border: none;
    border-radius: 7px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.82rem;
    color: #6b7280;
    transition: all 0.2s;
}

.share-btn:hover { background: #0d6efd; color: #fff; }

.article-disclaimer {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    background: #fffbeb;
    border-top: 1px solid #fde68a;
    border-bottom: 1px solid #fde68a;
    padding: 12px 36px;
    font-size: 0.78rem;
    color: #78350f;
}

.article-disclaimer i { color: #d97706; flex-shrink: 0; margin-top: 2px; }
.article-disclaimer p { margin: 0; }

.reading-progress {
    height: 3px;
    background: #f3f4f6;
    position: sticky;
    top: 64px;
    z-index: 50;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #10b981, #0d6efd);
    width: 0;
    transition: width 0.1s linear;
}

.article-content {
    padding: 32px 36px;
    font-size: 0.97rem;
    line-height: 1.85;
    color: #374151;
    font-family: 'Inter', sans-serif;
}

.article-content h2 {
    font-size: 1.3rem;
    font-weight: 700;
    color: #1a3c5e;
    margin: 32px 0 14px;
    padding-bottom: 8px;
    border-bottom: 2px solid #eff6ff;
}

.article-content h3 {
    font-size: 1.05rem;
    font-weight: 600;
    color: #1a3c5e;
    margin: 24px 0 10px;
}

.article-content p { margin-bottom: 16px; }

.article-content ul, .article-content ol {
    padding-left: 24px;
    margin-bottom: 16px;
}

.article-content li { margin-bottom: 6px; }

.article-content blockquote {
    border-left: 4px solid #0d6efd;
    padding: 12px 20px;
    background: #eff6ff;
    border-radius: 0 8px 8px 0;
    margin: 20px 0;
    color: #1e40af;
    font-style: italic;
}

.article-content table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    font-size: 0.88rem;
}

.article-content th {
    background: #1a3c5e;
    color: #fff;
    padding: 10px 14px;
    text-align: left;
}

.article-content td {
    padding: 9px 14px;
    border-bottom: 1px solid #f3f4f6;
}

.article-content tr:hover td { background: #f8fafc; }

/* Key Takeaways */
.key-takeaways {
    margin: 0 36px 24px;
    background: linear-gradient(135deg, #f0fdf4, #ecfdf5);
    border: 1px solid #a7f3d0;
    border-radius: 12px;
    padding: 20px 24px;
}

.key-takeaways h3 {
    font-size: 0.95rem;
    font-weight: 700;
    color: #065f46;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.key-takeaways h3 i { color: #10b981; }

.key-takeaways ul { list-style: none; padding: 0; }

.key-takeaways li {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    padding: 5px 0;
    font-size: 0.88rem;
    color: #374151;
}

.key-takeaways li::before {
    content: 'âœ“';
    color: #10b981;
    font-weight: 700;
    flex-shrink: 0;
    margin-top: 1px;
}

/* FAQs */
.article-faqs {
    margin: 0 36px 28px;
}

.article-faqs h3 {
    font-size: 1rem;
    font-weight: 700;
    color: #1a3c5e;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.faq-accordion { display: flex; flex-direction: column; gap: 8px; }

.faq-item {
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    overflow: hidden;
}

.faq-question {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    background: #f8fafc;
    border: none;
    cursor: pointer;
    text-align: left;
    font-size: 0.88rem;
    font-weight: 500;
    color: #374151;
    transition: background 0.2s;
}

.faq-question:hover { background: #eff6ff; }

.faq-arrow { transition: transform 0.3s; color: #9ca3af; flex-shrink: 0; }

.faq-answer { display: none; }
.faq-answer.open { display: block; }

.faq-answer-inner {
    padding: 14px 18px;
    font-size: 0.87rem;
    color: #374151;
    line-height: 1.7;
    background: #fff;
    border-top: 1px solid #f3f4f6;
}

/* Article Actions */
.article-actions {
    display: flex;
    gap: 10px;
    padding: 20px 36px;
    border-top: 1px solid #f3f4f6;
    flex-wrap: wrap;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 18px;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
}

.action-download { background: #eff6ff; color: #1d4ed8; }
.action-download:hover { background: #0d6efd; color: #fff; }

.action-chat { background: #f0fdf4; color: #065f46; }
.action-chat:hover { background: #10b981; color: #fff; }

.action-compliance { background: #f3f4f6; color: #374151; }
.action-compliance:hover { background: #1a3c5e; color: #fff; }

/* Feedback */
.article-feedback {
    padding: 20px 36px;
    border-top: 1px solid #f3f4f6;
    background: #fafafa;
    text-align: center;
}

.article-feedback p { font-size: 0.88rem; color: #6b7280; margin-bottom: 12px; }

.feedback-buttons { display: flex; justify-content: center; gap: 10px; }

.feedback-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 20px;
    border-radius: 8px;
    border: 1.5px solid #e5e7eb;
    background: #fff;
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    color: #374151;
}

.feedback-yes:hover  { background: #d1fae5; border-color: #6ee7b7; color: #065f46; }
.feedback-no:hover   { background: #fee2e2; border-color: #fca5a5; color: #991b1b; }

.feedback-thanks {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-size: 0.88rem;
    color: #059669;
    margin-top: 8px;
}

/* Article Navigation */
.article-nav {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1px;
    background: #f3f4f6;
    border-top: 1px solid #f3f4f6;
}

.article-nav-btn {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 18px 24px;
    background: #fff;
    text-decoration: none;
    transition: background 0.2s;
}

.article-nav-btn:hover { background: #eff6ff; }

.article-nav-next { text-align: right; }

.nav-direction {
    font-size: 0.75rem;
    color: #9ca3af;
    display: flex;
    align-items: center;
    gap: 5px;
}

.article-nav-next .nav-direction { justify-content: flex-end; }

.nav-title {
    font-size: 0.88rem;
    font-weight: 500;
    color: #1a3c5e;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Sidebar */
.article-sidebar {
    position: sticky;
    top: 84px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.sidebar-widget {
    background: #fff;
    border-radius: 14px;
    padding: 20px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}

.sidebar-widget h4 {
    font-size: 0.82rem;
    font-weight: 700;
    color: #1a3c5e;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 7px;
}

.sidebar-widget h4 i { color: #0d6efd; }

/* TOC */
.toc-loading { font-size: 0.8rem; color: #9ca3af; }

.toc-list { list-style: none; display: flex; flex-direction: column; gap: 3px; }

.toc-h2 a, .toc-h3 a {
    display: block;
    font-size: 0.82rem;
    color: #6b7280;
    text-decoration: none;
    padding: 4px 8px;
    border-radius: 6px;
    transition: all 0.2s;
    border-left: 2px solid transparent;
}

.toc-h3 { padding-left: 12px; }

.toc-h2 a:hover, .toc-h3 a:hover { background: #eff6ff; color: #0d6efd; }
.toc-active { background: #eff6ff !important; color: #0d6efd !important; border-left-color: #0d6efd !important; }

/* Act Reference Widget */
.act-ref-card { text-align: center; }

.act-ref-section {
    font-size: 1.3rem;
    font-weight: 700;
    color: #1a3c5e;
    background: #eff6ff;
    border-radius: 8px;
    padding: 10px;
    margin-bottom: 6px;
}

.act-ref-card p { font-size: 0.78rem; color: #9ca3af; margin-bottom: 12px; }

.act-download-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    background: #1a3c5e;
    color: #fff;
    padding: 8px 14px;
    border-radius: 7px;
    font-size: 0.78rem;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.2s;
}

.act-download-btn:hover { background: #0f2744; }

/* Related Articles Sidebar */
.related-articles-list { display: flex; flex-direction: column; gap: 4px; }

.related-article-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 9px 10px;
    border-radius: 8px;
    text-decoration: none;
    transition: background 0.2s;
}

.related-article-item:hover { background: #f3f4f6; }

.rel-content { flex: 1; }

.rel-title {
    display: block;
    font-size: 0.82rem;
    font-weight: 500;
    color: #374151;
    line-height: 1.4;
    margin-bottom: 3px;
}

.rel-meta { font-size: 0.7rem; color: #9ca3af; display: flex; align-items: center; gap: 4px; }

.rel-arrow { color: #d1d5db; font-size: 0.7rem; flex-shrink: 0; }

/* CTA Widget */
.cta-widget {
    background: linear-gradient(135deg, #1a3c5e, #0d6efd);
    color: #fff;
    border-color: transparent;
    text-align: center;
}

.cta-widget-icon {
    width: 48px;
    height: 48px;
    background: rgba(255,255,255,0.15);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: #fff;
    margin: 0 auto 12px;
}

.cta-widget h4 { color: #fff; font-size: 0.9rem; justify-content: center; text-transform: none; letter-spacing: 0; margin-bottom: 6px; }

.cta-widget p { font-size: 0.78rem; color: rgba(255,255,255,0.75); line-height: 1.5; margin-bottom: 14px; }

.cta-widget-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.3);
    color: #fff;
    padding: 8px 16px;
    border-radius: 7px;
    font-size: 0.8rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
}

.cta-widget-btn:hover { background: rgba(255,255,255,0.25); }

/* Related Section */
.related-section {
    background: #f8fafc;
    padding: 52px 0;
    border-top: 1px solid #e5e7eb;
}

.related-section h2 {
    font-size: 1.3rem;
    font-weight: 700;
    color: #1a3c5e;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.related-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
}

.related-card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    border: 1px solid #e5e7eb;
    text-decoration: none;
    display: block;
    transition: all 0.25s;
}

.related-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.09); }

.related-card-type { margin-bottom: 10px; }

.related-card h3 {
    font-size: 0.9rem;
    font-weight: 600;
    color: #1a3c5e;
    margin-bottom: 7px;
    line-height: 1.4;
}

.related-card p {
    font-size: 0.78rem;
    color: #9ca3af;
    line-height: 1.5;
    margin-bottom: 14px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.related-card-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.75rem;
    color: #9ca3af;
}

.read-more-link { color: #0d6efd; font-weight: 500; }

/* Toast */
.article-toast {
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%) translateY(80px);
    background: #1a3c5e;
    color: #fff;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 0.85rem;
    opacity: 0;
    transition: all 0.3s;
    z-index: 9999;
    white-space: nowrap;
}

.article-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }

/* Responsive */
@media (max-width: 1024px) {
    .article-grid { grid-template-columns: 1fr; }
    .article-sidebar { position: static; }
    .related-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 640px) {
    .article-title { font-size: 1.4rem; }
    .article-header, .article-content, .article-actions, .article-feedback { padding-left: 20px; padding-right: 20px; }
    .key-takeaways, .article-faqs { margin-left: 20px; margin-right: 20px; }
    .related-grid { grid-template-columns: 1fr; }
    .article-nav { grid-template-columns: 1fr; }
}

@media print {
    .article-sidebar, .article-actions, .article-feedback,
    .article-nav, .related-section, .reading-progress { display: none !important; }
    .article-grid { grid-template-columns: 1fr !important; }
    .article-main { box-shadow: none !important; border: none !important; }
}
</style>
</body>
</html>