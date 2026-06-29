<?php
/**
 * admin/knowledge/index.php
 * Knowledge Hub Content Management
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

$pageTitle   = 'Knowledge Hub Management - Admin';
$currentPage = 'knowledge';

$db = Database::getInstance()->getConnection();

// ── Handle Actions ────────────────────────────────────────────────
$actionMsg  = '';
$actionType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $actionMsg  = 'Invalid security token.';
        $actionType = 'error';
    } else {
        $action    = sanitizeInput($_POST['action'] ?? '');
        $articleId = intval($_POST['article_id'] ?? 0);

        if ($articleId) {
            switch ($action) {
                case 'publish':
                    $db->prepare("UPDATE knowledge_articles SET is_published=1, published_at=NOW(), updated_at=NOW() WHERE id=:id")
                       ->execute([':id' => $articleId]);
                    $actionMsg = 'Article published.';
                    $actionType = 'success';
                    break;
                case 'unpublish':
                    $db->prepare("UPDATE knowledge_articles SET is_published=0, updated_at=NOW() WHERE id=:id")
                       ->execute([':id' => $articleId]);
                    $actionMsg = 'Article set to draft.';
                    $actionType = 'success';
                    break;
                case 'feature':
                    $db->prepare("UPDATE knowledge_articles SET is_featured=1, updated_at=NOW() WHERE id=:id")
                       ->execute([':id' => $articleId]);
                    $actionMsg = 'Article featured on homepage.';
                    $actionType = 'success';
                    break;
                case 'unfeature':
                    $db->prepare("UPDATE knowledge_articles SET is_featured=0, updated_at=NOW() WHERE id=:id")
                       ->execute([':id' => $articleId]);
                    $actionMsg = 'Article unfeatured.';
                    $actionType = 'success';
                    break;
                case 'delete':
                    $db->prepare("DELETE FROM knowledge_articles WHERE id=:id")
                       ->execute([':id' => $articleId]);
                    $actionMsg = 'Article deleted.';
                    $actionType = 'success';
                    break;
            }
        }

        // New / Edit Article
        if ($action === 'save_article') {
            $editId      = intval($_POST['edit_id'] ?? 0);
            $title       = sanitizeInput($_POST['title'] ?? '');
            $titleSw     = sanitizeInput($_POST['title_sw'] ?? '');
            $summary     = sanitizeInput($_POST['summary'] ?? '');
            $content     = $_POST['content'] ?? ''; // HTML content — not sanitized here for rich text
            $contentSw   = $_POST['content_sw'] ?? '';
            $categoryId  = sanitizeInput($_POST['category'] ?? 'pbo_act');
            $actSection  = sanitizeInput($_POST['pbo_act_section'] ?? '');
            $publishedStatus = (sanitizeInput($_POST['status'] ?? 'draft') === 'published') ? 1 : 0;
            $featuredStatus  = isset($_POST['featured']) ? 1 : 0;

            if (empty($title)) {
                $actionMsg  = 'Article title is required.';
                $actionType = 'error';
            } else {
                if ($editId) {
                        $db->prepare("
                            UPDATE knowledge_articles SET
                                title_en=:title, title_sw=:title_sw, summary_en=:summary,
                                content_en=:content, content_sw=:content_sw,
                                category=:cat,
                                pbo_act_section=:section,
                                is_published=:pub, is_featured=:feat,
                                updated_at=NOW()
                            WHERE id=:id
                        ")->execute([
                            ':title'=>$title,':title_sw'=>$titleSw ?: null,
                            ':summary'=>$summary,':content'=>$content,
                            ':content_sw'=>$contentSw ?: null,':cat'=>$categoryId,
                            ':section'=>$actSection ?: null,
                            ':pub'=>$publishedStatus,':feat'=>$featuredStatus,
                            ':id'=>$editId,
                        ]);
                        $actionMsg = 'Article updated successfully.';
                    } else {
                        $db->prepare("
                            INSERT INTO knowledge_articles
                                (title_en, title_sw, summary_en, content_en, content_sw,
                                 slug, category, pbo_act_section,
                                 is_published, is_featured,
                                 view_count, created_at, updated_at)
                            VALUES
                                (:title, :title_sw, :summary, :content, :content_sw,
                                 :slug, :cat, :section, :pub, :feat, 0, NOW(), NOW())
                        ")->execute([
                            ':title'=>$title,':title_sw'=>$titleSw ?: null,
                            ':summary'=>$summary,':content'=>$content,
                            ':content_sw'=>$contentSw ?: null,
                            ':slug'=>strtolower(trim(preg_replace('/[^a-zA-Z0-9-]+/', '-', $title), '-')),
                            ':cat'=>$categoryId,':section'=>$actSection ?: null,
                            ':pub'=>$publishedStatus,':feat'=>$featuredStatus,
                        ]);
                        $actionMsg = 'Article created successfully.';
                    }
                $actionType = 'success';
            }
        }
    }
}

// ── Filters ──────────────────────────────────────────────────────
$filterStatus = sanitizeInput($_GET['status'] ?? '');
$filterCat    = sanitizeInput($_GET['category'] ?? '');
$search       = sanitizeInput($_GET['q'] ?? '');
$page         = max(1, intval($_GET['page'] ?? 1));
$perPage      = 20;
$offset       = ($page - 1) * $perPage;

$conditions = ['1=1'];
$params     = [];

if ($filterStatus === 'published') {
    $conditions[] = "a.is_published=1";
} elseif ($filterStatus === 'draft') {
    $conditions[] = "a.is_published=0";
}
if ($filterCat) {
    $conditions[] = "a.category=:cat";
    $params[':cat'] = $filterCat;
}
if ($search) {
    $conditions[] = "(a.title_en LIKE :q OR a.summary_en LIKE :q2)";
    $params[':q']  = "%$search%";
    $params[':q2'] = "%$search%";
}

$where = implode(' AND ', $conditions);

$countStmt = $db->prepare("SELECT COUNT(*) FROM knowledge_articles a WHERE $where");
$countStmt->execute($params);
$totalArticles = (int)$countStmt->fetchColumn();
$totalPages    = ceil($totalArticles / $perPage);

$params[':limit']  = $perPage;
$params[':offset'] = $offset;

$stmt = $db->prepare("
    SELECT a.*
    FROM knowledge_articles a
    WHERE $where
    ORDER BY a.is_featured DESC, a.updated_at DESC
    LIMIT :limit OFFSET :offset
");
$stmt->execute($params);
$articles = $stmt->fetchAll();

// Categories (ENUM values)
$categoryOptions = [
    '' => '— Select Category —',
    'pbo_act' => 'PBO Act',
    'registration' => 'Registration',
    'compliance' => 'Compliance',
    'governance' => 'Governance',
    'finance' => 'Finance',
    'advocacy' => 'Advocacy',
    'rights' => 'Rights',
];

// Summary
$summaryStmt = $db->query("
    SELECT
        COUNT(*) as total,
        SUM(is_published=1) as published,
        SUM(is_published=0) as drafts,
        SUM(is_featured=1 AND is_published=1) as featured,
        SUM(title_sw IS NOT NULL) as kiswahili,
        SUM(view_count) as total_views
    FROM knowledge_articles
");
$summary = $summaryStmt->fetch();

// Article for editing (if requested)
$editArticle = null;
if (!empty($_GET['edit'])) {
    $editStmt = $db->prepare("SELECT * FROM knowledge_articles WHERE id=:id");
    $editStmt->execute([':id' => intval($_GET['edit'])]);
    $editArticle = $editStmt->fetch();
}
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
                <span class="bc-item active">Knowledge Hub</span>
            </div>
        </div>
        <div class="topbar-right">
            <button class="btn btn-primary btn-sm" onclick="toggleEditor(true)">
                <i class="fas fa-plus"></i> New Article
            </button>
        </div>
    </header>

    <div class="dashboard-content">

        <?php if ($actionMsg): ?>
        <div class="action-message action-<?php echo $actionType; ?>">
            <i class="fas fa-<?php echo $actionType === 'success' ? 'check-circle' : 'times-circle'; ?>"></i>
            <?php echo htmlspecialchars($actionMsg); ?>
        </div>
        <?php endif; ?>

        <div class="page-header">
            <div>
                <h1>Knowledge Hub</h1>
                <p>Create and manage legal knowledge articles, guides, FAQs, and multimedia content.</p>
            </div>
            <button class="btn btn-primary" onclick="toggleEditor(true)">
                <i class="fas fa-plus"></i> New Article
            </button>
        </div>

        <!-- Summary -->
        <div class="summary-strip">
            <a href="?" class="strip-card <?php echo !$filterStatus ? 'strip-active' : ''; ?>">
                <span class="strip-num"><?php echo number_format($summary['total']); ?></span>
                <span class="strip-label">Total</span>
            </a>
            <a href="?status=published" class="strip-card strip-success <?php echo $filterStatus === 'published' ? 'strip-active' : ''; ?>">
                <span class="strip-num"><?php echo $summary['published']; ?></span>
                <span class="strip-label">Published</span>
            </a>
            <a href="?status=draft" class="strip-card strip-warning <?php echo $filterStatus === 'draft' ? 'strip-active' : ''; ?>">
                <span class="strip-num"><?php echo $summary['drafts']; ?></span>
                <span class="strip-label">Drafts</span>
            </a>
            <div class="strip-card">
                <span class="strip-num" style="color:#f59e0b"><?php echo $summary['featured']; ?></span>
                <span class="strip-label">Featured</span>
            </div>
            <div class="strip-card">
                <span class="strip-num" style="color:#10b981"><?php echo $summary['kiswahili']; ?></span>
                <span class="strip-label">In Kiswahili</span>
            </div>
            <div class="strip-card">
                <span class="strip-num" style="color:#8b5cf6"><?php echo number_format($summary['total_views']); ?></span>
                <span class="strip-label">Total Views</span>
            </div>
        </div>

        <!-- Article Editor Panel -->
        <div class="editor-panel" id="editorPanel" style="display:none">
            <div class="editor-header">
                <h3 id="editorTitle">
                    <i class="fas fa-pen"></i>
                    <?php echo $editArticle ? 'Edit Article' : 'New Article'; ?>
                </h3>
                <button class="editor-close" onclick="toggleEditor(false)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" id="articleForm">
                <?php echo generateCSRFField(); ?>
                <input type="hidden" name="action" value="save_article">
                <input type="hidden" name="edit_id" id="editArticleId"
                       value="<?php echo $editArticle ? $editArticle['id'] : 0; ?>">

                <div class="editor-grid">
                    <div class="editor-main">
                        <div class="form-group">
                            <label>Title (English) <span class="req">*</span></label>
                            <input type="text" name="title" required
                                   value="<?php echo htmlspecialchars($editArticle['title_en'] ?? ''); ?>"
                                   placeholder="Article title in English">
                        </div>
                        <div class="form-group">
                            <label>Title (Kiswahili)</label>
                            <input type="text" name="title_sw"
                                   value="<?php echo htmlspecialchars($editArticle['title_sw'] ?? ''); ?>"
                                   placeholder="Kichwa cha makala kwa Kiswahili (optional)">
                        </div>
                        <div class="form-group">
                            <label>Summary / Excerpt <span class="req">*</span></label>
                            <textarea name="summary" rows="2" required
                                      placeholder="Brief description (shown in article cards)"><?php echo htmlspecialchars($editArticle['summary_en'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Content (English) — HTML supported</label>
                            <textarea name="content" rows="12" class="content-editor"
                                      placeholder="Full article content. Use &lt;h2&gt;, &lt;h3&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;strong&gt;, &lt;blockquote&gt; tags."><?php echo htmlspecialchars($editArticle['content_en'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Content (Kiswahili) — optional</label>
                            <textarea name="content_sw" rows="8" class="content-editor"
                                      placeholder="Maudhui ya makala kwa Kiswahili..."><?php echo htmlspecialchars($editArticle['content_sw'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="editor-sidebar">
                        <div class="editor-meta-card">
                            <h4>Publish Settings</h4>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status">
                                    <option value="draft" <?php echo empty($editArticle['is_published']) ? 'selected' : ''; ?>>Draft</option>
                                    <option value="published" <?php echo !empty($editArticle['is_published']) ? 'selected' : ''; ?>>Published</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Category</label>
                                <select name="category">
                                    <?php foreach ($categoryOptions as $val => $label): ?>
                                    <option value="<?php echo $val; ?>"
                                        <?php echo ($editArticle['category'] ?? '') === $val ? 'selected' : ''; ?>>
                                        <?php echo $label; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>PBO Act Section Reference</label>
                                <input type="text" name="pbo_act_section"
                                       value="<?php echo htmlspecialchars($editArticle['pbo_act_section'] ?? ''); ?>"
                                       placeholder="e.g. Section 10, Part II">
                            </div>
                            <div class="form-group">
                                <label class="checkbox-row">
                                    <input type="checkbox" name="featured" value="1"
                                        <?php echo !empty($editArticle['is_featured']) ? 'checked' : ''; ?>>
                                    <span>Feature on homepage</span>
                                </label>
                            </div>
                        </div>

                        <div class="editor-save-btns">
                            <button type="submit" class="btn btn-primary" style="width:100%">
                                <i class="fas fa-save"></i>
                                <?php echo $editArticle ? 'Update Article' : 'Save Article'; ?>
                            </button>
                            <button type="button" class="btn btn-outline" style="width:100%"
                                    onclick="toggleEditor(false)">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Filters -->
        <div class="filters-bar">
            <form method="GET" class="filters-form">
                <div class="filter-search">
                    <i class="fas fa-search"></i>
                    <input type="text" name="q"
                           value="<?php echo htmlspecialchars($search); ?>"
                           placeholder="Search articles...">
                    <?php if ($search): ?>
                    <a href="?" class="filter-clear-search"><i class="fas fa-times"></i></a>
                    <?php endif; ?>
                </div>
                <select name="status" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="published" <?php echo $filterStatus === 'published' ? 'selected' : ''; ?>>Published</option>
                    <option value="draft"     <?php echo $filterStatus === 'draft'     ? 'selected' : ''; ?>>Draft</option>
                </select>
                <select name="category" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php foreach ($categoryOptions as $val => $label): if ($val === '') continue; ?>
                    <option value="<?php echo $val; ?>"
                            <?php echo $filterCat === $val ? 'selected' : ''; ?>>
                        <?php echo $label; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <?php if ($filterStatus || $filterCat || $search): ?>
                <a href="?" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Articles Table -->
        <div class="table-card">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Views</th>
                            <th>Status</th>
                            <th>SW</th>
                            <th>Featured</th>
                            <th>Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($articles)): ?>
                        <tr>
                            <td colspan="9" class="table-empty">
                                <i class="fas fa-book-open"></i>
                                <span>No articles found. <button onclick="toggleEditor(true)" style="background:none;border:none;color:#0d6efd;cursor:pointer;font-size:inherit">Create the first one.</button></span>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($articles as $art):
                            $catInfo = $categoryOptions[$art['category']] ?? $art['category'];
                        ?>
                        <tr>
                            <td style="font-family:monospace;font-size:0.75rem;color:#9ca3af">
                                #<?php echo $art['id']; ?>
                            </td>
                            <td>
                                <div style="max-width:280px">
                                    <a href="../../modules/knowledge-hub/article.php?id=<?php echo $art['id']; ?>"
                                       target="_blank"
                                       style="font-size:0.88rem;font-weight:500;color:#1a3c5e;text-decoration:none">
                                        <?php echo htmlspecialchars($art['title_en']); ?>
                                        <i class="fas fa-external-link-alt" style="font-size:0.65rem;color:#9ca3af;margin-left:4px"></i>
                                    </a>
                                    <?php if ($art['summary_en']): ?>
                                    <p style="font-size:0.72rem;color:#9ca3af;margin:2px 0 0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                        <?php echo htmlspecialchars($art['summary_en']); ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="cat-pill">
                                    <?php echo htmlspecialchars(is_string($catInfo) ? $catInfo : $catInfo); ?>
                                </span>
                            </td>
                            <td>
                                <strong style="color:#374151"><?php echo number_format($art['view_count']); ?></strong>
                            </td>
                            <td>
                                <span class="status-pill <?php echo $art['is_published'] ? 'stat-approved' : 'stat-pending'; ?>">
                                    <?php echo $art['is_published'] ? 'Published' : 'Draft'; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($art['title_sw']): ?>
                                <span style="font-size:0.85rem">🇰🇪</span>
                                <?php else: ?>
                                <span style="color:#d1d5db">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($art['is_featured']): ?>
                                <i class="fas fa-star" style="color:#f59e0b"></i>
                                <?php else: ?>
                                <span style="color:#d1d5db">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="date-cell">
                                <?php echo date('d M Y', strtotime($art['updated_at'])); ?>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <button class="action-btn" title="Edit"
                                            onclick="location.href='?edit=<?php echo $art['id']; ?>'">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <form method="POST" style="display:inline">
                                        <?php echo generateCSRFField(); ?>
                                        <input type="hidden" name="article_id" value="<?php echo $art['id']; ?>">
                                        <?php if ($art['is_published']): ?>
                                        <button type="submit" name="action" value="unpublish"
                                                class="action-btn" title="Unpublish">
                                            <i class="fas fa-eye-slash"></i>
                                        </button>
                                        <?php else: ?>
                                        <button type="submit" name="action" value="publish"
                                                class="action-btn action-approve" title="Publish">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php endif; ?>
                                        <?php if (!$art['is_featured']): ?>
                                        <button type="submit" name="action" value="feature"
                                                class="action-btn" title="Feature" style="color:#f59e0b">
                                            <i class="fas fa-star"></i>
                                        </button>
                                        <?php else: ?>
                                        <button type="submit" name="action" value="unfeature"
                                                class="action-btn" title="Unfeature">
                                            <i class="far fa-star"></i>
                                        </button>
                                        <?php endif; ?>
                                        <button type="submit" name="action" value="delete"
                                                class="action-btn action-reject" title="Delete"
                                                onclick="return confirm('Delete this article permanently?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
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

<script>
function toggleEditor(show) {
    const panel = document.getElementById('editorPanel');
    panel.style.display = show ? 'block' : 'none';
    if(show) panel.scrollIntoView({ behavior: 'smooth' });
}

// Auto-open editor if editing
<?php if ($editArticle): ?>
toggleEditor(true);
<?php endif; ?>

// Auto-dismiss message
const msg = document.querySelector('.action-message');
if(msg) setTimeout(() => { msg.style.opacity='0'; setTimeout(()=>msg.remove(),400); }, 4000);
</script>

<style>
.editor-panel {
    background: #fff;
    border: 2px solid #0d6efd;
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 24px;
    box-shadow: 0 4px 24px rgba(13,110,253,0.1);
}

.editor-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 24px;
    background: linear-gradient(135deg, #1a3c5e, #0d6efd);
    color: #fff;
}

.editor-header h3 {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 1rem;
    font-weight: 600;
}

.editor-close {
    background: rgba(255,255,255,0.15);
    border: none;
    color: #fff;
    width: 30px;
    height: 30px;
    border-radius: 7px;
    cursor: pointer;
    font-size: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.editor-grid {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 0;
}

.editor-main { padding: 24px; border-right: 1px solid #f3f4f6; }
.editor-sidebar { padding: 24px; background: #f8fafc; }

.editor-main .form-group,
.editor-sidebar .form-group { margin-bottom: 16px; }

.editor-main .form-group label,
.editor-sidebar .form-group label {
    display: block;
    font-size: 0.8rem;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-bottom: 5px;
}

.editor-main .form-group input,
.editor-main .form-group select,
.editor-main .form-group textarea,
.editor-sidebar .form-group input,
.editor-sidebar .form-group select,
.editor-sidebar .form-group textarea {
    width: 100%;
    padding: 9px 12px;
    border: 1.5px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.87rem;
    color: #374151;
    background: #fff;
    font-family: inherit;
    resize: vertical;
}

.content-editor { font-family: 'Courier New', monospace; font-size: 0.82rem !important; }

.editor-meta-card {
    background: #fff;
    border-radius: 10px;
    padding: 16px;
    border: 1px solid #e5e7eb;
    margin-bottom: 16px;
}

.editor-meta-card h4 {
    font-size: 0.82rem;
    font-weight: 700;
    color: #1a3c5e;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 14px;
}

.checkbox-row {
    display: flex !important;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    text-transform: none !important;
    letter-spacing: 0 !important;
}

.checkbox-row input { width: auto !important; }

.editor-save-btns { display: flex; flex-direction: column; gap: 8px; }

.cat-pill {
    padding: 3px 9px;
    border-radius: 50px;
    font-size: 0.72rem;
    font-weight: 600;
}

@media (max-width: 900px) {
    .editor-grid { grid-template-columns: 1fr; }
    .editor-main { border-right: none; border-bottom: 1px solid #f3f4f6; }
}
</style>
</body>
</html>