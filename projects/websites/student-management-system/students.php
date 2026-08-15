<?php
require_once __DIR__ . '/includes/config.php';

$q = isset($_GET['q']) ? trim((string) $_GET['q']) : '';

$sql = "SELECT s.*, d.name AS dept_name
        FROM students s
        LEFT JOIN departments d ON d.code = s.department
        WHERE 1=1";
$params = [];
if ($q !== '') {
    $sql .= ' AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.course LIKE ? OR s.department LIKE ?)';
    $like = '%' . $q . '%';
    $params = array_fill(0, 4, $like);
}
$sql .= ' ORDER BY s.last_name, s.first_name';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

$page_title  = 'Student Directory';
$active_page = 'students';
include __DIR__ . '/includes/header.php';
?>
<section class="page-head">
    <div class="container">
        <p class="crumb"><a href="home.php">Home</a> &rsaquo; Students</p>
        <h1>Student <span class="grad-text">Directory</span></h1>
        <p class="lead" style="max-width:620px">Meet our community. Click any student to open their profile — their avatar also appears on the 3D student plaza.</p>
    </div>
</section>

<section class="section-sm">
    <div class="container">
        <div class="toolbar">
            <span class="chip"><i class="fa-solid fa-users"></i> <?= count($students) ?> student<?= count($students) === 1 ? '' : 's' ?></span>
            <form class="search-bar" method="get" action="students.php">
                <input class="input" type="text" name="q" placeholder="Search students&hellip;" value="<?= e($q) ?>">
                <button class="btn btn-primary" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
            </form>
        </div>

        <div class="card-grid">
            <?php if ($students): foreach ($students as $i => $s): ?>
                <?php $hue = avatar_hue($s['first_name'] . ' ' . $s['last_name']); ?>
                <div class="card student-card reveal <?= $i ? 'reveal-delay-' . min($i, 3) : '' ?>"
                     data-json='<?= e(json_encode([
                        'name'   => $s['first_name'] . ' ' . $s['last_name'],
                        'email'  => $s['email'],
                        'phone'  => $s['phone'],
                        'course' => $s['course'],
                        'dept'   => $s['dept_name'] ?: $s['department'],
                        'color'  => $s['avatar_color'],
                        'since'  => date('F Y', strtotime($s['created_at'])),
                     ], JSON_UNESCAPED_SLASHES)) ?>'
                     style="cursor:pointer"
                     onclick="openStudentProfile(this)">
                    <div class="avatar" style="width:56px;height:56px;font-size:20px;border-radius:16px;background:<?= e($s['avatar_color']) ?>"><?= e(avatar_initials($s['first_name'], $s['last_name'])) ?></div>
                    <h3 style="margin:12px 0 2px"><?= e($s['first_name']) ?> <?= e($s['last_name']) ?></h3>
                    <p class="text-dim" style="margin:0 0 12px; font-size:13px"><?= e($s['course']) ?></p>
                    <div class="flex-between" style="margin-top:auto">
                        <span class="badge cyan"><?= e($s['dept_name'] ?: $s['department']) ?></span>
                        <span class="card-link" style="font-size:13px">View profile <i class="fa-solid fa-arrow-right"></i></span>
                    </div>
                </div>
            <?php endforeach; else: ?>
                <div class="card" style="grid-column:1/-1; text-align:center; padding:44px">
                    <h3>No students found</h3>
                    <p class="text-dim">Try a different search, or register a new student.</p>
                    <a class="btn btn-primary mt-2" href="register.php"><i class="fa-solid fa-user-plus"></i> Register student</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
function openStudentProfile(el) {
    var d = JSON.parse(el.getAttribute('data-json'));
    window.openModal(
        '<div class="modal-head"><h3>Student profile</h3><button class="modal-close" aria-label="Close"><i class="fa-solid fa-xmark"></i></button></div>' +
        '<div class="avatar avatar-lg" style="background:' + d.color + '; margin-bottom:16px">' +
            d.name.split(' ').map(function (w) { return w.charAt(0).toUpperCase(); }).join('').slice(0, 2) +
        '</div>' +
        '<h3 style="margin:0 0 2px">' + d.name + '</h3>' +
        '<p class="text-dim" style="margin:0 0 18px">' + d.course + ' &middot; ' + d.dept + '</p>' +
        '<div class="cp-meta">' +
            '<div><i class="fa-solid fa-envelope"></i> ' + d.email + '</div>' +
            '<div><i class="fa-solid fa-phone"></i> ' + d.phone + '</div>' +
            '<div><i class="fa-solid fa-calendar-check"></i> Joined ' + d.since + '</div>' +
        '</div>' +
        '<div class="cp-actions"><button class="btn btn-primary btn-sm" data-close-modal>Close</button></div>'
    );
}
</script>
<?php include __DIR__ . '/includes/footer.php';
