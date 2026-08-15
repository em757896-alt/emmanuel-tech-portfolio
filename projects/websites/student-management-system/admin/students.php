<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.php';

require_admin();

$perPage = 15;
$page = max(1, (int) ($_GET['page'] ?? 1));
$q = trim((string) ($_GET['q'] ?? ''));

$where = '';
$params = [];
if ($q !== '') {
    $where = ' WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR course LIKE ? OR department LIKE ?';
    $like = '%' . $q . '%';
    $params = array_fill(0, 5, $like);
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM students" . $where);
$stmt->execute($params);
$total = (int) $stmt->fetchColumn();

$pages = max(1, (int) ceil($total / $perPage));
$page = min($page, $pages);
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare(
    "SELECT id, first_name, last_name, email, course, department, avatar_color, created_at
     FROM students" . $where . " ORDER BY created_at DESC, last_name LIMIT $perPage OFFSET $offset"
);
$stmt->execute($params);
$students = $stmt->fetchAll();

$token = csrf_token();

$page_title  = 'Manage Students';
$active_page = 'students';
include __DIR__ . '/../includes/header.php';
?>
<section class="page-head">
    <div class="container">
        <p class="crumb"><a href="<?= $base_url ?>home.php">Home</a> &rsaquo; Admin &rsaquo; Students</p>
        <div class="flex-between">
            <div>
                <h1 style="margin-bottom:6px">Manage <span class="grad-text">Students</span></h1>
                <p class="text-dim" style="margin:0"><?= $total ?> record<?= $total === 1 ? '' : 's' ?>. Use the live search to find students instantly.</p>
            </div>
            <a class="btn btn-primary" href="../register.php"><i class="fa-solid fa-user-plus"></i> Add student</a>
        </div>
    </div>
</section>

<section class="section-sm">
    <div class="container">
        <div class="toolbar">
            <div class="search-bar" style="flex:1">
                <input class="input" type="text" id="liveSearch" placeholder="Live search name, email, course or department&hellip;" value="<?= e($q) ?>" autocomplete="off">
                <button class="btn btn-ghost" type="button" id="searchClear" title="Clear search"><i class="fa-solid fa-rotate-left"></i></button>
            </div>
        </div>

        <div class="table-wrap reveal">
            <div class="table-scroll">
                <table id="studentsTable">
                    <thead>
                        <tr><th>Student</th><th>Email</th><th>Course</th><th>Dept</th><th>Registered</th><th class="text-center">Actions</th></tr>
                    </thead>
                    <tbody id="studentsBody">
                        <?php foreach ($students as $s): ?>
                            <tr>
                                <td>
                                    <div class="avatar-row">
                                        <span class="avatar" style="background:<?= e($s['avatar_color']) ?>"><?= e(avatar_initials($s['first_name'], $s['last_name'])) ?></span>
                                        <span><?= e($s['first_name'] . ' ' . $s['last_name']) ?></span>
                                    </div>
                                </td>
                                <td><?= e($s['email']) ?></td>
                                <td><?= e($s['course']) ?></td>
                                <td><span class="badge cyan"><?= e($s['department']) ?></span></td>
                                <td><span class="tt-meta"><?= e(date('M j, Y', strtotime($s['created_at']))) ?></span></td>
                                <td>
                                    <div class="flex-between" style="gap:8px; justify-content:center">
                                        <a class="btn btn-ghost btn-sm" href="../edit.php?id=<?= (int) $s['id'] ?>"><i class="fa-solid fa-pen"></i></a>
                                        <a class="btn btn-danger btn-sm" data-confirm="Delete this student permanently?" href="../delete_student.php?id=<?= (int) $s['id'] ?>&csrf_token=<?= $token ?>"><i class="fa-solid fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($pages > 1): ?>
            <div class="pagination">
                <?php $qs = $q !== '' ? '&q=' . urlencode($q) : ''; ?>
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="students.php?page=<?= $i ?><?= $qs ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
(function () {
    var input = document.getElementById('liveSearch');
    var tbody = document.getElementById('studentsBody');
    var clear = document.getElementById('searchClear');
    var timer = null;

    function row(s) {
        var initials = (s.first_name.charAt(0) + s.last_name.charAt(0)).toUpperCase();
        return '<tr>' +
            '<td><div class="avatar-row"><span class="avatar" style="background:' + s.avatar_color + '">' + initials + '</span><span>' + s.first_name + ' ' + s.last_name + '</span></div></td>' +
            '<td>' + s.email + '</td>' +
            '<td>' + s.course + '</td>' +
            '<td><span class="badge cyan">' + s.department + '</span></td>' +
            '<td><span class="tt-meta">' + s.created_at + '</span></td>' +
            '<td><div class="flex-between" style="gap:8px; justify-content:center">' +
                '<a class="btn btn-ghost btn-sm" href="../edit.php?id=' + s.id + '"><i class="fa-solid fa-pen"></i></a>' +
                '<a class="btn btn-danger btn-sm" data-confirm="Delete this student permanently?" href="../delete_student.php?id=' + s.id + '&csrf_token=<?= $token ?>"><i class="fa-solid fa-trash"></i></a>' +
            '</div></td></tr>';
    }

    function esc(s) {
        var d = document.createElement('div');
        d.textContent = s == null ? '' : s;
        return d.innerHTML;
    }

    function render(rows) {
        tbody.innerHTML = rows.length
            ? rows.map(row).join('')
            : '<tr class="table-empty"><td colspan="6">No students match your search.</td></tr>';
    }

    input.addEventListener('input', function () {
        clearTimeout(timer);
        var q = input.value.trim();
        timer = setTimeout(function () {
            if (q === '') { window.location.href = 'students.php'; return; }
            fetch('../api/search.php?q=' + encodeURIComponent(q))
                .then(function (r) { return r.json(); })
                .then(render)
                .catch(function () {});
        }, 250);
    });

    clear.addEventListener('click', function () {
        window.location.href = 'students.php';
    });
})();
</script>
<?php include __DIR__ . '/../includes/footer.php';
