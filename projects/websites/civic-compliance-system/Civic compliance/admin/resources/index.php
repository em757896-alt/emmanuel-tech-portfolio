<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../config/auth.php';

requireAdmin();

$pageTitle   = 'Resources - Admin';
$currentPage = 'resources';

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
            $db->prepare("DELETE FROM resources WHERE id=:id")->execute([':id'=>$id]);
            $actionMsg  = 'Resource deleted.';
            $actionType = 'success';
        } elseif ($action === 'toggle_active' && $id) {
            $stmt = $db->prepare("UPDATE resources SET is_active = NOT is_active WHERE id=:id");
            $stmt->execute([':id'=>$id]);
            $actionMsg  = 'Resource status toggled.';
            $actionType = 'success';
        } elseif ($action === 'save') {
            $title    = sanitizeInput($_POST['title'] ?? '');
            $desc     = sanitizeInput($_POST['description'] ?? '');
            $type     = sanitizeInput($_POST['resource_type'] ?? '');
            $url      = sanitizeInput($_POST['resource_url'] ?? '');
            $cat      = sanitizeInput($_POST['category'] ?? '');
            $lang     = sanitizeInput($_POST['language'] ?? 'en');
            $featured = isset($_POST['featured']) ? 1 : 0;

            if ($title && $type) {
                if ($id) {
                    $db->prepare("UPDATE resources SET title=:t, description=:d, resource_type=:rt, resource_url=:ru, category=:c, language=:l, is_featured=:f WHERE id=:id")
                       ->execute([':t'=>$title,':d'=>$desc,':rt'=>$type,':ru'=>$url,':c'=>$cat,':l'=>$lang,':f'=>$featured,':id'=>$id]);
                } else {
                    $db->prepare("INSERT INTO resources (title, description, resource_type, resource_url, category, language, is_featured, created_at) VALUES (:t,:d,:rt,:ru,:c,:l,:f,NOW())")
                       ->execute([':t'=>$title,':d'=>$desc,':rt'=>$type,':ru'=>$url,':c'=>$cat,':l'=>$lang,':f'=>$featured]);
                }
                $actionMsg  = $id ? 'Resource updated.' : 'Resource created.';
                $actionType = 'success';
            } else {
                $actionMsg  = 'Title and type are required.';
                $actionType = 'error';
            }
        }
    }
}

$filterType = sanitizeInput($_GET['type'] ?? '');
$filterCat  = sanitizeInput($_GET['cat'] ?? '');
$search     = sanitizeInput($_GET['q'] ?? '');
$page       = max(1, intval($_GET['page'] ?? 1));
$perPage    = 20;
$offset     = ($page - 1) * $perPage;

$conditions = ['1=1'];
$params = [];

if ($filterType) { $conditions[] = "resource_type = :rt"; $params[':rt'] = $filterType; }
if ($filterCat)  { $conditions[] = "category = :cat"; $params[':cat'] = $filterCat; }
if ($search)     { $conditions[] = "(title LIKE :q OR description LIKE :q2)"; $params[':q']="%$search%"; $params[':q2']="%$search%"; }

$where = implode(' AND ', $conditions);

$countStmt = $db->prepare("SELECT COUNT(*) FROM resources WHERE $where");
$countStmt->execute($params);
$totalItems = (int)$countStmt->fetchColumn();
$totalPages = ceil($totalItems / $perPage);

$params[':limit']  = $perPage;
$params[':offset'] = $offset;

$stmt = $db->prepare("SELECT * FROM resources WHERE $where ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->execute($params);
$items = $stmt->fetchAll();

$types = $db->query("SELECT DISTINCT resource_type FROM resources ORDER BY resource_type")->fetchAll(PDO::FETCH_COLUMN);
$cats  = $db->query("SELECT DISTINCT category FROM resources WHERE category IS NOT NULL AND category != '' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

$editItem = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM resources WHERE id=:id");
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
                <span class="bc-item active">Resources</span>
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
                <h1>Resources</h1>
                <p>Manage downloadable resources, toolkits, guides and templates.</p>
            </div>
            <a href="?add=1" class="btn btn-primary"><i class="fas fa-plus"></i> Add Resource</a>
        </div>

        <?php if (isset($_GET['add']) || $editItem): ?>
        <div class="editor-panel" style="margin-bottom:24px">
            <div class="editor-header"><h3><i class="fas fa-<?php echo $editItem?'edit':'plus'; ?>"></i> <?php echo $editItem?'Edit':'Add'; ?> Resource</h3><a href="?" class="editor-close"><i class="fas fa-times"></i></a></div>
            <form method="POST" style="padding:24px">
                <?php echo generateCSRFField(); ?>
                <input type="hidden" name="action" value="save">
                <?php if($editItem): ?><input type="hidden" name="id" value="<?php echo $editItem['id']; ?>"><?php endif; ?>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:16px">
                    <div>
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:5px">Title <span class="req">*</span></label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($editItem['title']??''); ?>" required style="width:100%;padding:9px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-size:0.87rem">
                    </div>
                    <div>
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:5px">Resource Type <span class="req">*</span></label>
                        <select name="resource_type" required style="width:100%;padding:9px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-size:0.87rem">
                            <option value="">Select...</option>
                            <option value="guide" <?php echo ($editItem['resource_type']??'')==='guide'?'selected':''; ?>>Guide</option>
                            <option value="template" <?php echo ($editItem['resource_type']??'')==='template'?'selected':''; ?>>Template</option>
                            <option value="toolkit" <?php echo ($editItem['resource_type']??'')==='toolkit'?'selected':''; ?>>Toolkit</option>
                            <option value="form" <?php echo ($editItem['resource_type']??'')==='form'?'selected':''; ?>>Form</option>
                            <option value="report" <?php echo ($editItem['resource_type']??'')==='report'?'selected':''; ?>>Report</option>
                            <option value="other" <?php echo ($editItem['resource_type']??'')==='other'?'selected':''; ?>>Other</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:5px">URL / File Path</label>
                        <input type="text" name="resource_url" value="<?php echo htmlspecialchars($editItem['resource_url']??''); ?>" style="width:100%;padding:9px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-size:0.87rem">
                    </div>
                    <div>
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:5px">Category</label>
                        <select name="category" style="width:100%;padding:9px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-size:0.87rem">
                            <option value="">None</option>
                            <option value="registration" <?php echo ($editItem['category']??'')==='registration'?'selected':''; ?>>Registration</option>
                            <option value="compliance" <?php echo ($editItem['category']??'')==='compliance'?'selected':''; ?>>Compliance</option>
                            <option value="governance" <?php echo ($editItem['category']??'')==='governance'?'selected':''; ?>>Governance</option>
                            <option value="financial" <?php echo ($editItem['category']??'')==='financial'?'selected':''; ?>>Financial</option>
                            <option value="reporting" <?php echo ($editItem['category']??'')==='reporting'?'selected':''; ?>>Reporting</option>
                            <option value="advocacy" <?php echo ($editItem['category']??'')==='advocacy'?'selected':''; ?>>Advocacy</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:5px">Language</label>
                        <select name="language" style="width:100%;padding:9px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-size:0.87rem">
                            <option value="en" <?php echo ($editItem['language']??'en')==='en'?'selected':''; ?>>English</option>
                            <option value="sw" <?php echo ($editItem['language']??'')==='sw'?'selected':''; ?>>Kiswahili</option>
                        </select>
                    </div>
                    <div style="display:flex;align-items:center;padding-top:28px">
                        <label><input type="checkbox" name="featured" value="1" <?php echo ($editItem['is_featured']??0)?'checked':''; ?>> Featured</label>
                    </div>
                </div>
                <div style="margin-bottom:16px">
                    <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:5px">Description</label>
                    <textarea name="description" rows="3" style="width:100%;padding:9px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-size:0.87rem;font-family:inherit"><?php echo htmlspecialchars($editItem['description']??''); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Resource</button>
                <a href="?" class="btn btn-outline">Cancel</a>
            </form>
        </div>
        <?php endif; ?>

        <div class="filters-bar">
            <form method="GET" class="filters-form">
                <div class="filter-search">
                    <i class="fas fa-search"></i>
                    <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search resources...">
                    <?php if($search): ?><a href="?" class="filter-clear-search"><i class="fas fa-times"></i></a><?php endif; ?>
                </div>
                <select name="type" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <?php foreach($types as $t): ?>
                    <option value="<?php echo $t; ?>" <?php echo $filterType===$t?'selected':''; ?>><?php echo ucfirst($t); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="cat" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php foreach($cats as $c): ?>
                    <option value="<?php echo $c; ?>" <?php echo $filterCat===$c?'selected':''; ?>><?php echo ucfirst($c); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
                <?php if($filterType||$filterCat||$search): ?>
                <a href="?" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr><th>#</th><th>Title</th><th>Type</th><th>Category</th><th>Language</th><th>Featured</th><th>Active</th><th>Created</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if(empty($items)): ?>
                        <tr><td colspan="9" class="table-empty"><i class="fas fa-inbox"></i><span>No resources found.</span></td></tr>
                        <?php else: ?>
                        <?php foreach($items as $row): ?>
                        <tr>
                            <td style="font-family:monospace;font-size:0.72rem;color:#9ca3af">#<?php echo $row['id']; ?></td>
                            <td><div class="org-cell"><span class="org-name"><?php echo htmlspecialchars($row['title']); ?></span><?php if($row['description']): ?><span class="org-preview"><?php echo htmlspecialchars(substr($row['description'],0,80)); ?>...</span><?php endif; ?></div></td>
                            <td><span class="type-badge type-blue"><?php echo ucfirst($row['resource_type']); ?></span></td>
                            <td><span class="status-pill" style="background:#f3f4f6;color:#374151;font-size:0.72rem"><?php echo htmlspecialchars($row['category']??'—'); ?></span></td>
                            <td><?php echo $row['language']==='sw'?'🇰🇪 SW':'🇬🇧 EN'; ?></td>
                            <td><?php if($row['is_featured']): ?><i class="fas fa-star" style="color:#f59e0b"></i><?php else: ?><span style="color:#d1d5db">—</span><?php endif; ?></td>
                            <td><?php if($row['is_active']): ?><span class="severity-badge sev-low">Active</span><?php else: ?><span class="severity-badge sev-high">Inactive</span><?php endif; ?></td>
                            <td class="date-cell"><?php echo date('d M Y',strtotime($row['created_at'])); ?><span class="date-time"><?php echo date('H:i',strtotime($row['created_at'])); ?></span></td>
                            <td>
                                <div class="action-btns">
                                    <a href="?edit=<?php echo $row['id']; ?>" class="action-btn" title="Edit"><i class="fas fa-edit"></i></a>
                                    <form method="POST" style="display:inline">
                                        <?php echo generateCSRFField(); ?>
                                        <input type="hidden" name="action" value="toggle_active">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="action-btn" title="Toggle active"><i class="fas fa-<?php echo $row['is_active']?'pause':'play'; ?>"></i></button>
                                    </form>
                                    <form method="POST" style="display:inline" onsubmit="return confirm('Delete this resource?')">
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
