<?php
require_once __DIR__ . '/includes/config.php';

$q = isset($_GET['q']) ? trim((string) $_GET['q']) : '';

$sql = "SELECT * FROM books WHERE 1=1";
$params = [];
if ($q !== '') {
    $sql .= ' AND (title LIKE ? OR author LIKE ? OR category LIKE ? OR isbn LIKE ?)';
    $like = '%' . $q . '%';
    $params = array_fill(0, 4, $like);
}
$sql .= ' ORDER BY title';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll();

$stats = [
    'total'   => (int) $pdo->query('SELECT COUNT(*) FROM books')->fetchColumn(),
    'titles'  => count($books),
    'onLoan'  => (int) $pdo->query('SELECT COUNT(*) FROM loans WHERE return_date IS NULL')->fetchColumn(),
];

$page_title  = 'Library';
$active_page = 'library';
include __DIR__ . '/includes/header.php';
?>
<section class="page-head">
    <div class="container">
        <p class="crumb"><a href="home.php">Home</a> &rsaquo; Library</p>
        <h1>College <span class="grad-text">Library</span></h1>
        <p class="lead" style="max-width:620px">Browse the catalog, check availability and track what is on loan. The library building is also on the 3D campus.</p>
    </div>
</section>

<section class="section-sm">
    <div class="container">
        <div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr))">
            <div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-book"></i></div><div class="stat-num" data-count="<?= $stats['total'] ?>">0</div><div class="stat-label">Catalogued Titles</div></div>
            <div class="stat-card dark-gold"><div class="stat-icon"><i class="fa-solid fa-bookmark"></i></div><div class="stat-num" data-count="<?= $stats['onLoan'] ?>">0</div><div class="stat-label">Currently on Loan</div></div>
            <div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-magnifying-glass"></i></div><div class="stat-num"><?= $stats['titles'] ?></div><div class="stat-label">Results Shown</div></div>
        </div>

        <div class="toolbar mt-2">
            <form class="search-bar" method="get" action="library.php">
                <input class="input" type="text" name="q" placeholder="Search by title, author, category or ISBN&hellip;" value="<?= e($q) ?>">
                <button class="btn btn-primary" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
            </form>
        </div>

        <div class="book-grid">
            <?php if ($books): foreach ($books as $i => $b): ?>
                <?php
                $pct = $b['available'] > 0 ? ($b['available'] / max(1, $b['copies'])) : 0;
                $status = $b['available'] > 0 ? 'Available' : 'All on loan';
                $badge  = $b['available'] > 0 ? 'green' : 'red';
                $icons  = ['fa-book', 'fa-newspaper', 'fa-microchip', 'fa-briefcase', 'fa-graduation-cap', 'fa-lightbulb'];
                $icon   = $icons[array_rand($icons)];
                ?>
                <div class="card book-card reveal <?= $i ? 'reveal-delay-' . min($i, 3) : '' ?>">
                    <div class="book-cover"><i class="fa-solid <?= $icon ?>"></i></div>
                    <span class="badge <?= $badge ?>" style="align-self:flex-start; margin-bottom:10px"><?= $status ?></span>
                    <h3><?= e($b['title']) ?></h3>
                    <p class="book-author"><?= e($b['author']) ?></p>
                    <p class="book-meta"><?= e($b['category']) ?><?= $b['year'] ? ' &middot; ' . e((string) $b['year']) : '' ?><?= $b['isbn'] ? ' &middot; ISBN ' . e($b['isbn']) : '' ?></p>
                    <div class="flex-between" style="margin-top:auto">
                        <span class="chip"><i class="fa-solid fa-layer-group"></i> <?= (int) $b['available'] ?>/<?= (int) $b['copies'] ?> available</span>
                        <span class="chip"><i class="fa-solid fa-shelves"></i> <?= e($b['shelf']) ?></span>
                    </div>
                </div>
            <?php endforeach; else: ?>
                <div class="card" style="grid-column:1/-1; text-align:center; padding:44px">
                    <h3>No books found</h3>
                    <p class="text-dim">Try a different search term.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="card reveal mt-3" style="display:flex; gap:14px; align-items:center; justify-content:space-between; flex-wrap:wrap">
            <div>
                <h3 style="margin:0 0 2px">Find the library on campus</h3>
                <p class="text-dim" style="margin:0">Click the library building in the 3D campus scene to jump straight here.</p>
            </div>
            <a class="btn btn-primary" href="campus.php"><i class="fa-solid fa-cubes"></i> Open 3D Campus</a>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php';
