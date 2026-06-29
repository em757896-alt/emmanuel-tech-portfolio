<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../config/auth.php';

requireAdmin();

$pageTitle   = 'FAQs - Admin';
$currentPage = 'faqs';

$db = Database::getInstance()->getConnection();

$actionMsg  = '';
$actionType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $actionMsg  = 'Invalid security token.';
        $actionType = 'error';
    } else {
        $action = sanitizeInput($_POST['action'] ?? '');
        $id     = intval($_POST['id'] ?? 0);

        if ($action === 'delete' && $id) {
            $db->prepare("DELETE FROM faqs WHERE id=:id")->execute([':id'=>$id]);
            $actionMsg  = 'FAQ deleted.';
            $actionType = 'success';
        } elseif ($action === 'toggle_active' && $id) {
            $db->prepare("UPDATE faqs SET is_active = NOT is_active WHERE id=:id")->execute([':id'=>$id]);
            $actionMsg  = 'FAQ status toggled.';
            $actionType = 'success';
        } elseif ($action === 'save') {
            $question = sanitizeInput($_POST['question'] ?? '');
            $answer   = sanitizeInput($_POST['answer'] ?? '');
            $category = sanitizeInput($_POST['category'] ?? 'general');
            $lang     = sanitizeInput($_POST['language'] ?? 'en');
            $sort     = intval($_POST['sort_order'] ?? 0);

            if ($question && $answer) {
                if ($id) {
                    $db->prepare("UPDATE faqs SET question=:q, answer=:a, category=:c, language=:l, sort_order=:s WHERE id=:id")
                       ->execute([':q'=>$question,':a'=>$answer,':c'=>$category,':l'=>$lang,':s'=>$sort,':id'=>$id]);
                } else {
                    $db->prepare("INSERT INTO faqs (question, answer, category, language, sort_order, created_at) VALUES (:q,:a,:c,:l,:s,NOW())")
                       ->execute([':q'=>$question,':a'=>$answer,':c'=>$category,':l'=>$lang,':s'=>$sort]);
                }
                $actionMsg  = $id ? 'FAQ updated.' : 'FAQ created.';
                $actionType = 'success';
            } else {
                $actionMsg  = 'Question and answer are required.';
                $actionType = 'error';
            }
        }
    }
}

$filterCat  = sanitizeInput($_GET['cat'] ?? '');
$search     = sanitizeInput($_GET['q'] ?? '');
$page       = max(1, intval($_GET['page'] ?? 1));
$perPage    = 20;
$offset     = ($page - 1) * $perPage;

$conditions = ['1=1'];
$params = [];

if ($filterCat) { $conditions[] = "category = :c"; $params[':c'] = $filterCat; }
if ($search)    { $conditions[] = "(question LIKE :q OR answer LIKE :q2)"; $params[':q']="%$search%"; $params[':q2']="%$search%"; }

$where = implode(' AND ', $conditions);

$countStmt = $db->prepare("SELECT COUNT(*) FROM faqs WHERE $where");
$countStmt->execute($params);
$totalItems = (int)$countStmt->fetchColumn();
$totalPages = ceil($totalItems / $perPage);

$params[':limit']  = $perPage;
$params[':offset'] = $offset;

$stmt = $db->prepare("SELECT * FROM faqs WHERE $where ORDER BY sort_order ASC, created_at DESC LIMIT :limit OFFSET :offset");
$stmt->execute($params);
$items = $stmt->fetchAll();

$cats = $db->query("SELECT DISTINCT category FROM faqs WHERE category IS NOT NULL AND category != '' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

$editItem = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM faqs WHERE id=:id");
    $stmt->execute([':id'=>intval($_GET['edit'])]);
    $editItem = $stmt->fetch();
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
            <button class="topbar-menu-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
            <div class="topbar-breadcrumb">
                <a href="../dashboard.php" class="bc-item"><i class="fas fa-home"></i></a>
                <span class="bc-sep">/</span>
                <span class="bc-item active">FAQs</span>
            </div>
        </div>
    </header>

    <div class="dashboard-content">

        <?php if ($actionMsg): ?>
        <div class="action-message action-<?php echo $actionType; ?>">
            <i class="fas fa-<?php echo $actionType==='success'?'check-circle':'times-circle'; ?>"></i>
            <?php echo htmlspecialchars($actionMsg); ?>
        </div>
        <?php endif; ?>

        <div class="page-header">
            <div>
                <h1>FAQs</h1>
                <p>Manage frequently asked questions for the Knowledge Hub.</p>
            </div>
            <a href="?add=1" class="btn btn-primary"><i class="fas fa-plus"></i> Add FAQ</a>
        </div>

        <?php if (isset($_GET['add']) || $editItem): ?>
        <div class="editor-panel" style="margin-bottom:24px">
            <div class="editor-header"><h3><i class="fas fa-<?php echo $editItem?'edit':'plus'; ?>"></i> <?php echo $editItem?'Edit':'Add'; ?> FAQ</h3><a href="?" class="editor-close"><i class="fas fa-times"></i></a></div>
            <form method="POST" style="padding:24px">
                <?php echo generateCSRFField(); ?>
                <input type="hidden" name="action" value="save">
                <?php if($editItem): ?><input type="hidden" name="id" value="<?php echo $editItem['id']; ?>"><?php endif; ?>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:16px">
                    <div>
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:5px">Question <span class="req">*</span></label>
                        <textarea name="question" rows="2" required style="width:100%;padding:9px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-size:0.87rem;font-family:inherit"><?php echo htmlspecialchars($editItem['question']??''); ?></textarea>
                    </div>
                    <div>
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:5px">Answer <span class="req">*</span></label>
                        <textarea name="answer" rows="2" required style="width:100%;padding:9px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-size:0.87rem;font-family:inherit"><?php echo htmlspecialchars($editItem['answer']??''); ?></textarea>
                    </div>
                    <div>
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:5px">Category</label>
                        <select name="category" style="width:100%;padding:9px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-size:0.87rem">
                            <option value="general" <?php echo ($editItem['category']??'general')==='general'?'selected':''; ?>>General</option>
                            <option value="registration" <?php echo ($editItem['category']??'')==='registration'?'selected':''; ?>>Registration</option>
                            <option value="compliance" <?php echo ($editItem['category']??'')==='compliance'?'selected':''; ?>>Compliance</option>
                            <option value="monitoring" <?php echo ($editItem['category']??'')==='monitoring'?'selected':''; ?>>Monitoring</option>
                            <option value="legal" <?php echo ($editItem['category']??'')==='legal'?'selected':''; ?>>Legal</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:5px">Language</label>
                        <select name="language" style="width:100%;padding:9px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-size:0.87rem">
                            <option value="en" <?php echo ($editItem['language']??'en')==='en'?'selected':''; ?>>English</option>
                            <option value="sw" <?php echo ($editItem['language']??'')==='sw'?'selected':''; ?>>Kiswahili</option>
                        </select>
                    </div>
                </div>
                <div style="margin-bottom:16px">
                    <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:5px">Sort Order</label>
                    <input type="number" name="sort_order" value="<?php echo intval($editItem['sort_order']??0); ?>" style="width:100px;padding:9px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-size:0.87rem">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save FAQ</button>
                <a href="?" class="btn btn-outline">Cancel</a>
            </form>
        </div>
        <?php endif; ?>

        <div class="filters-bar">
            <form method="GET" class="filters-form">
                <div class="filter-search">
                    <i class="fas fa-search"></i>
                    <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search FAQs...">
                    <?php if($search): ?><a href="?" class="filter-clear-search"><i class="fas fa-times"></i></a><?php endif; ?>
                </div>
                <select name="cat" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <option value="general" <?php echo $filterCat==='general'?'selected':''; ?>>General</option>
                    <?php foreach($cats as $c): ?>
                    <option value="<?php echo $c; ?>" <?php echo $filterCat===$c?'selected':''; ?>><?php echo ucfirst($c); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
                <?php if($filterCat||$search): ?><a href="?" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Clear</a><?php endif; ?>
            </form>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr><th>#</th><th>Question</th><th>Category</th><th>Active</th><th>Sort</th><th>Created</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if(empty($items)): ?>
                        <tr><td colspan="7" class="table-empty"><i class="fas fa-inbox"></i><span>No FAQs found.</span></td></tr>
                        <?php else: ?>
                        <?php foreach($items as $row): ?>
                        <tr>
                            <td style="font-family:monospace;font-size:0.72rem;color:#9ca3af">#<?php echo $row['id']; ?></td>
                            <td><div class="org-cell"><span class="org-name"><?php echo htmlspecialchars($row['question']); ?></span><span class="org-preview"><?php echo htmlspecialchars(substr($row['answer'],0,80)); ?>...</span></div></td>
                            <td><span class="status-pill" style="background:#eff6ff;color:#1d4ed8;font-size:0.72rem"><?php echo htmlspecialchars($row['category']); ?></span></td>
                            <td><?php if($row['is_active']??1): ?><span class="severity-badge sev-low">Active</span><?php else: ?><span class="severity-badge sev-high">Inactive</span><?php endif; ?></td>
                            <td><?php echo $row['sort_order']??0; ?></td>
                            <td class="date-cell"><?php echo date('d M Y',strtotime($row['created_at'])); ?></td>
                            <td>
                                <div class="action-btns">
                                    <a href="?edit=<?php echo $row['id']; ?>" class="action-btn" title="Edit"><i class="fas fa-edit"></i></a>
                                    <form method="POST" style="display:inline">
                                        <?php echo generateCSRFField(); ?>
                                        <input type="hidden" name="action" value="toggle_active">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="action-btn" title="Toggle active"><i class="fas fa-<?php echo ($row['is_active']??1)?'pause':'play'; ?>"></i></button>
                                    </form>
                                    <form method="POST" style="display:inline" onsubmit="return confirm('Delete this FAQ?')">
                                        <?php echo generateCSRFField(); ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="action-btn action-reject" title="Delete"><i class="fas fa-trash"></i></button>
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

        <?php if($totalPages>1): ?>
        <div class="admin-pagination">
            <?php if($page>1): ?><a href="?<?php echo http_build_query(array_merge($_GET,['page'=>$page-1])); ?>" class="page-btn"><i class="fas fa-chevron-left"></i></a><?php endif; ?>
            <?php for($p=max(1,$page-2);$p<=min($totalPages,$page+2);$p++): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET,['page'=>$p])); ?>" class="page-num <?php echo $p===$page?'active':''; ?>"><?php echo $p; ?></a>
            <?php endfor; ?>
            <?php if($page<$totalPages): ?><a href="?<?php echo http_build_query(array_merge($_GET,['page'=>$page+1])); ?>" class="page-btn"><i class="fas fa-chevron-right"></i></a><?php endif; ?>
            <span class="page-info">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
        </div>
        <?php endif; ?>

    </div>
</main>

<script>
const msg=document.querySelector('.action-message');
if(msg){setTimeout(()=>{msg.style.opacity='0';setTimeout(()=>msg.remove(),400);},4000);}
</script>
</body>
</html>
